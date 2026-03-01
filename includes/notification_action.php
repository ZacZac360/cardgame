<?php
// notifications_action.php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Not logged in']);
  exit;
}

$u = current_user();
$uid = (int)$u['id'];

if (!is_post()) {
  http_response_code(405);
  echo json_encode(['ok' => false, 'error' => 'POST only']);
  exit;
}

$action = (string)($_POST['action'] ?? '');

if ($action === 'mark_one') {
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Bad id']);
    exit;
  }

  $stmt = $mysqli->prepare("
    UPDATE dashboard_notifications
    SET is_read = 1, read_at = IFNULL(read_at, NOW())
    WHERE id = ? AND user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("ii", $id, $uid);
  $stmt->execute();
  $stmt->close();

  echo json_encode(['ok' => true]);
  exit;
}

if ($action === 'mark_all') {
  $stmt = $mysqli->prepare("
    UPDATE dashboard_notifications
    SET is_read = 1, read_at = IFNULL(read_at, NOW())
    WHERE user_id = ? AND is_read = 0
  ");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $stmt->close();

  echo json_encode(['ok' => true]);
  exit;
}

http_response_code(400);
echo json_encode(['ok' => false, 'error' => 'Unknown action']);