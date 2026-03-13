<?php
session_start();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/messages_helpers.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
  exit;
}

$u = current_user();
$userId = (int)$u['id'];
$conversationId = (int)($_POST['conversation_id'] ?? 0);

if ($conversationId < 1 || !user_can_access_conversation($mysqli, $conversationId, $userId)) {
  http_response_code(404);
  echo json_encode(['ok' => false, 'msg' => 'Conversation not found']);
  exit;
}

mark_conversation_read($mysqli, $conversationId, $userId);

echo json_encode(['ok' => true]);