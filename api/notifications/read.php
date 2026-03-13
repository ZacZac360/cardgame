<?php
session_start();

ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!is_logged_in()) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'msg' => 'Unauthorized']);
  exit;
}

$u = current_user();
$userId  = (int)($u['id'] ?? 0);
$notifId = (int)($_POST['notification_id'] ?? 0);

if ($notifId <= 0) {
  http_response_code(400);
  echo json_encode(['ok' => false, 'msg' => 'Invalid notification']);
  exit;
}

$stmt = $mysqli->prepare("
  UPDATE dashboard_notifications
  SET is_read = 1
  WHERE id = ? AND user_id = ?
  LIMIT 1
");

if (!$stmt) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Failed to prepare notification update.']);
  exit;
}

$stmt->bind_param("ii", $notifId, $userId);
$ok = $stmt->execute();
$stmt->close();

if (!$ok) {
  http_response_code(500);
  echo json_encode(['ok' => false, 'msg' => 'Failed to mark notification as read.']);
  exit;
}

echo json_encode(['ok' => true, 'msg' => 'Notification marked as read.']);