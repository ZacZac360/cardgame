<?php
session_start();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/friends_helpers.php';
require_once __DIR__ . '/../../includes/messages_helpers.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
  exit;
}

$u = current_user();
if ((int)($u['is_guest'] ?? 0) === 1) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'msg' => 'Guests cannot use chat']);
  exit;
}

$userId = (int)$u['id'];
$conversationId = (int)($_GET['conversation_id'] ?? 0);
$targetUserId   = (int)($_GET['target_user_id'] ?? 0);

if ($conversationId < 1 && $targetUserId > 0) {
  if (!are_friends($mysqli, $userId, $targetUserId)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'msg' => 'You can only message friends']);
    exit;
  }

  $conversationId = get_or_create_direct_conversation($mysqli, $userId, $targetUserId);
}

if ($conversationId < 1 || !user_can_access_conversation($mysqli, $conversationId, $userId)) {
  http_response_code(404);
  echo json_encode(['ok' => false, 'msg' => 'Conversation not found']);
  exit;
}

$otherUser = fetch_conversation_other_user($mysqli, $conversationId, $userId);
$messages  = fetch_conversation_messages($mysqli, $conversationId, $userId, 100);

echo json_encode([
  'ok' => true,
  'conversation_id' => $conversationId,
  'other_user' => $otherUser,
  'messages' => $messages,
]);