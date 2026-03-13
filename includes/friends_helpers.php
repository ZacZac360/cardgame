<?php
require_once __DIR__ . '/helpers.php';

function friendship_pair(int $a, int $b): array {
  return ($a < $b) ? [$a, $b] : [$b, $a];
}

function are_friends(mysqli $mysqli, int $userId, int $otherUserId): bool {
  [$u1, $u2] = friendship_pair($userId, $otherUserId);

  $stmt = $mysqli->prepare("
    SELECT id
    FROM friends
    WHERE user_one = ? AND user_two = ?
    LIMIT 1
  ");
  $stmt->bind_param("ii", $u1, $u2);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return !empty($row);
}

function get_friendship_id(mysqli $mysqli, int $userId, int $otherUserId): int {
  [$u1, $u2] = friendship_pair($userId, $otherUserId);

  $stmt = $mysqli->prepare("
    SELECT id
    FROM friends
    WHERE user_one = ? AND user_two = ?
    LIMIT 1
  ");
  $stmt->bind_param("ii", $u1, $u2);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return (int)($row['id'] ?? 0);
}

function pending_friend_request_between(mysqli $mysqli, int $userId, int $otherUserId): ?array {
  $stmt = $mysqli->prepare("
    SELECT *
    FROM friend_requests
    WHERE (
      (sender_id = ? AND receiver_id = ?)
      OR
      (sender_id = ? AND receiver_id = ?)
    )
    AND status = 'pending'
    ORDER BY id DESC
    LIMIT 1
  ");
  $stmt->bind_param("iiii", $userId, $otherUserId, $otherUserId, $userId);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  return $row ?: null;
}

function send_friend_request(mysqli $mysqli, int $senderId, int $receiverId): array {
  if ($senderId === $receiverId) {
    return ['ok' => false, 'msg' => 'You cannot add yourself.'];
  }

  if (are_friends($mysqli, $senderId, $receiverId)) {
    return ['ok' => false, 'msg' => 'You are already friends.'];
  }

  $pending = pending_friend_request_between($mysqli, $senderId, $receiverId);
  if ($pending) {
    return ['ok' => false, 'msg' => 'A pending request already exists.'];
  }

  $stmt = $mysqli->prepare("
    INSERT INTO friend_requests (sender_id, receiver_id, status, created_at)
    VALUES (?, ?, 'pending', NOW())
  ");
  $stmt->bind_param("ii", $senderId, $receiverId);
  $ok = $stmt->execute();
  $stmt->close();

  if (!$ok) {
    return ['ok' => false, 'msg' => 'Failed to send friend request.'];
  }

  if (function_exists('create_notification')) {
    create_notification(
      $mysqli,
      $receiverId,
      'friend_request',
      'Friend Request',
      'You received a new friend request.',
      '/friends.php'
    );
  }

  return ['ok' => true, 'msg' => 'Friend request sent.'];
}

function accept_friend_request(mysqli $mysqli, int $requestId, int $receiverId): array {
  $stmt = $mysqli->prepare("
    SELECT *
    FROM friend_requests
    WHERE id = ? AND receiver_id = ? AND status = 'pending'
    LIMIT 1
  ");
  $stmt->bind_param("ii", $requestId, $receiverId);
  $stmt->execute();
  $req = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$req) {
    return ['ok' => false, 'msg' => 'Friend request not found.'];
  }

  $senderId = (int)$req['sender_id'];

  if (are_friends($mysqli, $senderId, $receiverId)) {
    $stmt = $mysqli->prepare("
      UPDATE friend_requests
      SET status = 'accepted', responded_at = NOW()
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $stmt->close();

    return ['ok' => true, 'msg' => 'Already friends.'];
  }

  [$u1, $u2] = friendship_pair($senderId, $receiverId);

  $mysqli->begin_transaction();

  try {
    $stmt = $mysqli->prepare("
      INSERT INTO friends (user_one, user_two, created_at)
      VALUES (?, ?, NOW())
    ");
    $stmt->bind_param("ii", $u1, $u2);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("
      UPDATE friend_requests
      SET status = 'accepted', responded_at = NOW()
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $stmt->close();

    if (function_exists('create_notification')) {
      create_notification(
        $mysqli,
        $senderId,
        'friend_accept',
        'Friend Request Accepted',
        'Your friend request was accepted.',
        '/friends.php'
      );
    }

    $mysqli->commit();
    return ['ok' => true, 'msg' => 'Friend request accepted.'];
  } catch (Throwable $e) {
    $mysqli->rollback();
    return ['ok' => false, 'msg' => 'Failed to accept friend request.'];
  }
}

function decline_friend_request(mysqli $mysqli, int $requestId, int $receiverId): array {
  $stmt = $mysqli->prepare("
    UPDATE friend_requests
    SET status = 'declined', responded_at = NOW()
    WHERE id = ? AND receiver_id = ? AND status = 'pending'
    LIMIT 1
  ");
  $stmt->bind_param("ii", $requestId, $receiverId);
  $stmt->execute();
  $affected = $stmt->affected_rows;
  $stmt->close();

  if ($affected < 1) {
    return ['ok' => false, 'msg' => 'Friend request not found.'];
  }

  return ['ok' => true, 'msg' => 'Friend request declined.'];
}

function cancel_friend_request(mysqli $mysqli, int $requestId, int $senderId): array {
  $stmt = $mysqli->prepare("
    UPDATE friend_requests
    SET status = 'cancelled', responded_at = NOW()
    WHERE id = ? AND sender_id = ? AND status = 'pending'
    LIMIT 1
  ");
  $stmt->bind_param("ii", $requestId, $senderId);
  $stmt->execute();
  $affected = $stmt->affected_rows;
  $stmt->close();

  if ($affected < 1) {
    return ['ok' => false, 'msg' => 'Pending request not found.'];
  }

  return ['ok' => true, 'msg' => 'Friend request cancelled.'];
}

function remove_friend(mysqli $mysqli, int $userId, int $otherUserId): array {
  [$u1, $u2] = friendship_pair($userId, $otherUserId);

  $stmt = $mysqli->prepare("
    DELETE FROM friends
    WHERE user_one = ? AND user_two = ?
    LIMIT 1
  ");
  $stmt->bind_param("ii", $u1, $u2);
  $stmt->execute();
  $affected = $stmt->affected_rows;
  $stmt->close();

  if ($affected < 1) {
    return ['ok' => false, 'msg' => 'Friend not found.'];
  }

  return ['ok' => true, 'msg' => 'Friend removed.'];
}

function count_pending_friend_requests(mysqli $mysqli, int $userId): int {
  $stmt = $mysqli->prepare("
    SELECT COUNT(*) AS c
    FROM friend_requests
    WHERE receiver_id = ? AND status = 'pending'
  ");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $c = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
  $stmt->close();

  return $c;
}

function fetch_pending_friend_requests(mysqli $mysqli, int $userId, int $limit = 8): array {
  $stmt = $mysqli->prepare("
    SELECT
      fr.id,
      fr.sender_id,
      fr.created_at,
      u.username,
      u.display_name,
      u.avatar_path
    FROM friend_requests fr
    JOIN users u ON u.id = fr.sender_id
    WHERE fr.receiver_id = ? AND fr.status = 'pending'
    ORDER BY fr.created_at DESC
    LIMIT ?
  ");
  $stmt->bind_param("ii", $userId, $limit);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return $rows;
}

function fetch_sent_friend_requests(mysqli $mysqli, int $userId, int $limit = 20): array {
  $stmt = $mysqli->prepare("
    SELECT
      fr.id,
      fr.receiver_id,
      fr.created_at,
      u.username,
      u.display_name,
      u.avatar_path
    FROM friend_requests fr
    JOIN users u ON u.id = fr.receiver_id
    WHERE fr.sender_id = ? AND fr.status = 'pending'
    ORDER BY fr.created_at DESC
    LIMIT ?
  ");
  $stmt->bind_param("ii", $userId, $limit);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return $rows;
}

function fetch_friends(mysqli $mysqli, int $userId, int $limit = 50): array {
  $stmt = $mysqli->prepare("
    SELECT
      f.id,
      f.created_at,
      u.id AS friend_id,
      u.username,
      u.display_name,
      u.avatar_path,
      u.tagline,
      u.level
    FROM friends f
    JOIN users u
      ON u.id = CASE
        WHEN f.user_one = ? THEN f.user_two
        ELSE f.user_one
      END
    WHERE f.user_one = ? OR f.user_two = ?
    ORDER BY f.created_at DESC
    LIMIT ?
  ");
  $stmt->bind_param("iiii", $userId, $userId, $userId, $limit);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return $rows;
}

function search_users_for_friends(mysqli $mysqli, int $userId, string $q, int $limit = 12): array {
  $q = trim($q);
  if ($q === '') return [];

  $like = '%' . $q . '%';

  $stmt = $mysqli->prepare("
    SELECT id, username, display_name, avatar_path, tagline, level
    FROM users
    WHERE id <> ?
      AND is_guest = 0
      AND is_active = 1
      AND (
        username LIKE ?
        OR display_name LIKE ?
        OR email LIKE ?
      )
    ORDER BY username ASC
    LIMIT ?
  ");
  $stmt->bind_param("isssi", $userId, $like, $like, $like, $limit);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return $rows;
}