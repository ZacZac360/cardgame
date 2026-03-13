<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/friends_helpers.php';

function find_direct_conversation(mysqli $mysqli, int $userA, int $userB): ?int {
  $stmt = $mysqli->prepare("
    SELECT cm1.conversation_id
    FROM conversation_members cm1
    JOIN conversation_members cm2
      ON cm2.conversation_id = cm1.conversation_id
    WHERE cm1.user_id = ? AND cm2.user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("ii", $userA, $userB);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $row ? (int)$row['conversation_id'] : null;
}

function get_or_create_direct_conversation(mysqli $mysqli, int $userA, int $userB): int {
  $existing = find_direct_conversation($mysqli, $userA, $userB);
  if ($existing) return $existing;

  $mysqli->begin_transaction();

  try {
    $stmt = $mysqli->prepare("
      INSERT INTO conversations (created_at)
      VALUES (NOW())
    ");
    $stmt->execute();
    $conversationId = (int)$stmt->insert_id;
    $stmt->close();

    $stmt = $mysqli->prepare("
      INSERT INTO conversation_members (conversation_id, user_id, last_read_message_id)
      VALUES (?, ?, NULL), (?, ?, NULL)
    ");
    $stmt->bind_param("iiii", $conversationId, $userA, $conversationId, $userB);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();
    return $conversationId;
  } catch (Throwable $e) {
    $mysqli->rollback();
    throw $e;
  }
}

function send_direct_message(mysqli $mysqli, int $senderId, int $receiverId, string $body): array {
  $body = trim($body);

  if ($body === '') {
    return ['ok' => false, 'msg' => 'Message cannot be empty.'];
  }

  if ($senderId === $receiverId) {
    return ['ok' => false, 'msg' => 'You cannot message yourself.'];
  }

  if (!are_friends($mysqli, $senderId, $receiverId)) {
    return ['ok' => false, 'msg' => 'You can only message friends right now.'];
  }

  $conversationId = get_or_create_direct_conversation($mysqli, $senderId, $receiverId);

  $stmt = $mysqli->prepare("
    INSERT INTO messages (conversation_id, sender_id, body, created_at)
    VALUES (?, ?, ?, NOW())
  ");
  $stmt->bind_param("iis", $conversationId, $senderId, $body);
  $ok = $stmt->execute();
  $messageId = (int)$stmt->insert_id;
  $stmt->close();

  if (!$ok) {
    return ['ok' => false, 'msg' => 'Failed to send message.'];
  }

  if (function_exists('create_notification')) {
    create_notification(
      $mysqli,
      $receiverId,
      'message',
      'New Message',
      'You received a new message.',
      '/messages.php?conversation_id=' . $conversationId
    );
  }

  return [
    'ok' => true,
    'msg' => 'Message sent.',
    'conversation_id' => $conversationId,
    'message_id' => $messageId,
  ];
}

function fetch_user_conversations(mysqli $mysqli, int $userId, int $limit = 20): array {
  $stmt = $mysqli->prepare("
    SELECT
      c.id AS conversation_id,
      MAX(m.id) AS last_message_id,
      MAX(m.created_at) AS last_message_at
    FROM conversation_members cm
    JOIN conversations c ON c.id = cm.conversation_id
    LEFT JOIN messages m ON m.conversation_id = c.id
    WHERE cm.user_id = ?
    GROUP BY c.id
    ORDER BY last_message_at DESC, c.id DESC
    LIMIT ?
  ");
  $stmt->bind_param("ii", $userId, $limit);
  $stmt->execute();
  $baseRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  $rows = [];

  foreach ($baseRows as $row) {
    $conversationId = (int)$row['conversation_id'];

    $stmt = $mysqli->prepare("
      SELECT u.id, u.username, u.display_name, u.avatar_path, u.tagline
      FROM conversation_members cm
      JOIN users u ON u.id = cm.user_id
      WHERE cm.conversation_id = ? AND cm.user_id <> ?
      LIMIT 1
    ");
    $stmt->bind_param("ii", $conversationId, $userId);
    $stmt->execute();
    $other = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $mysqli->prepare("
      SELECT id, sender_id, body, created_at
      FROM messages
      WHERE conversation_id = ?
      ORDER BY id DESC
      LIMIT 1
    ");
    $stmt->bind_param("i", $conversationId);
    $stmt->execute();
    $lastMessage = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $stmt = $mysqli->prepare("
      SELECT cm.last_read_message_id
      FROM conversation_members cm
      WHERE cm.conversation_id = ? AND cm.user_id = ?
      LIMIT 1
    ");
    $stmt->bind_param("ii", $conversationId, $userId);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $lastReadId = (int)($member['last_read_message_id'] ?? 0);

    $stmt = $mysqli->prepare("
      SELECT COUNT(*) AS c
      FROM messages
      WHERE conversation_id = ?
        AND sender_id <> ?
        AND id > ?
    ");
    $stmt->bind_param("iii", $conversationId, $userId, $lastReadId);
    $stmt->execute();
    $unread = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
    $stmt->close();

    $rows[] = [
      'conversation_id' => $conversationId,
      'other_user'      => $other ?: [],
      'last_message'    => $lastMessage ?: [],
      'unread_count'    => $unread,
    ];
  }

  return $rows;
}

function fetch_conversation_messages(mysqli $mysqli, int $conversationId, int $userId, int $limit = 100): array {
  $stmt = $mysqli->prepare("
    SELECT id
    FROM conversation_members
    WHERE conversation_id = ? AND user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("ii", $conversationId, $userId);
  $stmt->execute();
  $member = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$member) return [];

  $stmt = $mysqli->prepare("
    SELECT
      m.id,
      m.sender_id,
      m.body,
      m.created_at,
      u.username,
      u.display_name,
      u.avatar_path
    FROM messages m
    JOIN users u ON u.id = m.sender_id
    WHERE m.conversation_id = ?
    ORDER BY m.id ASC
    LIMIT ?
  ");
  $stmt->bind_param("ii", $conversationId, $limit);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return $rows;
}

function mark_conversation_read(mysqli $mysqli, int $conversationId, int $userId): void {
  $stmt = $mysqli->prepare("
    SELECT MAX(id) AS max_id
    FROM messages
    WHERE conversation_id = ?
  ");
  $stmt->bind_param("i", $conversationId);
  $stmt->execute();
  $maxId = (int)($stmt->get_result()->fetch_assoc()['max_id'] ?? 0);
  $stmt->close();

  $stmt = $mysqli->prepare("
    UPDATE conversation_members
    SET last_read_message_id = ?
    WHERE conversation_id = ? AND user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("iii", $maxId, $conversationId, $userId);
  $stmt->execute();
  $stmt->close();
}

function count_unread_direct_messages(mysqli $mysqli, int $userId): int {
  $stmt = $mysqli->prepare("
    SELECT COALESCE(SUM(x.unread_count), 0) AS total_unread
    FROM (
      SELECT COUNT(m.id) AS unread_count
      FROM conversation_members cm
      JOIN messages m ON m.conversation_id = cm.conversation_id
      WHERE cm.user_id = ?
        AND m.sender_id <> ?
        AND m.id > COALESCE(cm.last_read_message_id, 0)
      GROUP BY cm.conversation_id
    ) x
  ");
  $stmt->bind_param("ii", $userId, $userId);
  $stmt->execute();
  $total = (int)($stmt->get_result()->fetch_assoc()['total_unread'] ?? 0);
  $stmt->close();

  return $total;
}

function user_can_access_conversation(mysqli $mysqli, int $conversationId, int $userId): bool {
  $stmt = $mysqli->prepare("
    SELECT id
    FROM conversation_members
    WHERE conversation_id = ? AND user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("ii", $conversationId, $userId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return !empty($row);
}

function fetch_conversation_other_user(mysqli $mysqli, int $conversationId, int $userId): ?array {
  $stmt = $mysqli->prepare("
    SELECT
      u.id,
      u.username,
      u.display_name,
      u.avatar_path,
      u.tagline,
      u.level
    FROM conversation_members cm
    JOIN users u ON u.id = cm.user_id
    WHERE cm.conversation_id = ? AND cm.user_id <> ?
    LIMIT 1
  ");
  $stmt->bind_param("ii", $conversationId, $userId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $row ?: null;
}