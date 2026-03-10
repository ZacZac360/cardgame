<?php
session_start();

require_once __DIR__ . "/../../includes/db.php";
require_once __DIR__ . "/../../includes/helpers.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/mail_config.php";

$redirect = "/cardgame/profile.php?tab=security";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $_SESSION['flash_error'] = 'Invalid request.';
  header('Location: ' . $redirect);
  exit;
}

require_login();
$u = current_user();

$userId = (int)($u['id'] ?? 0);
$code   = trim((string)($_POST['code'] ?? ''));

if ($userId <= 0 || $code === '') {
  $_SESSION['flash_error'] = 'Enter the code first.';
  header('Location: ' . $redirect);
  exit;
}

$stmt = $mysqli->prepare("
  SELECT id, otp_code, expires_at, verified_at
  FROM email_verifications
  WHERE user_id = ?
  ORDER BY id DESC
  LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
  $_SESSION['flash_error'] = 'No code found.';
  header('Location: ' . $redirect);
  exit;
}

if (!empty($row['verified_at'])) {
  $_SESSION['flash_error'] = 'Code already used.';
  header('Location: ' . $redirect);
  exit;
}

if (strtotime((string)$row['expires_at']) < time()) {
  $_SESSION['flash_error'] = 'Code expired.';
  header('Location: ' . $redirect);
  exit;
}

if ($code !== (string)$row['otp_code']) {
  $_SESSION['flash_error'] = 'Incorrect code.';
  header('Location: ' . $redirect);
  exit;
}

$mysqli->begin_transaction();

try {
  $verificationId = (int)$row['id'];

  $stmt = $mysqli->prepare("
    UPDATE email_verifications
    SET verified_at = NOW()
    WHERE id = ?
  ");
  $stmt->bind_param("i", $verificationId);
  $stmt->execute();
  $stmt->close();

  $stmt = $mysqli->prepare("
    UPDATE users
    SET email_verified_at = NOW()
    WHERE id = ?
  ");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $stmt->close();

  $mysqli->commit();

  if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $_SESSION['user']['email_verified_at'] = date('Y-m-d H:i:s');
  }

  $_SESSION['flash_success'] = 'Email verified.';
  header('Location: ' . $redirect);
  exit;

} catch (Throwable $e) {
  $mysqli->rollback();
  $_SESSION['flash_error'] = 'Verification failed.';
  header('Location: ' . $redirect);
  exit;
}