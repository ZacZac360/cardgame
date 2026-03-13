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
if ((int)($u['is_guest'] ?? 0) === 1) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'msg' => 'Guests cannot use chat']);
  exit;
}

$userId = (int)$u['id'];
$threads = fetch_user_conversations($mysqli, $userId, 20);

echo json_encode([
  'ok' => true,
  'threads' => $threads,
]);