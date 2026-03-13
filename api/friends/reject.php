<?php
session_start();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/friends_helpers.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
  exit;
}

$u = current_user();
if ((int)($u['is_guest'] ?? 0) === 1) {
  http_response_code(403);
  echo json_encode(['ok' => false, 'msg' => 'Guests cannot manage friend requests']);
  exit;
}

$userId    = (int)($u['id'] ?? 0);
$requestId = (int)($_POST['request_id'] ?? 0);

if ($requestId <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => 'Invalid friend request']);
  exit;
}

$res = decline_friend_request($mysqli, $requestId, $userId);

if (!$res['ok']) {
  http_response_code(400);
  echo json_encode($res);
  exit;
}

echo json_encode($res);