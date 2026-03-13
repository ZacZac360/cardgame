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
$receiverId = (int)($_POST['receiver_id'] ?? 0);
$body = trim((string)($_POST['body'] ?? ''));

$res = send_direct_message($mysqli, $userId, $receiverId, $body);

if (!$res['ok']) {
  http_response_code(400);
  echo json_encode($res);
  exit;
}

echo json_encode($res);