<?php
session_start();

require_once __DIR__ . "/../../includes/db.php";
require_once __DIR__ . "/../../includes/helpers.php";
require_once __DIR__ . "/../../includes/auth.php";

$bp = base_path();

if (!isset($_SESSION['verify_login_user'])) {
  redirect($bp . "/index.php");
}

$uid = (int)$_SESSION['verify_login_user'];
$code = trim((string)($_POST['code'] ?? ''));

if ($code === '') {
  flash_set('err', 'Enter the verification code.');
  redirect($bp . "/verify-login.php");
}

$stmt = $mysqli->prepare("
  SELECT id, otp_code, expires_at, verified_at
  FROM email_verifications
  WHERE user_id = ?
  ORDER BY id DESC
  LIMIT 1
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
  flash_set('err', 'No verification code found.');
  redirect($bp . "/verify-login.php");
}

if (!empty($row['verified_at'])) {
  flash_set('err', 'Code already used.');
  redirect($bp . "/verify-login.php");
}

if (strtotime($row['expires_at']) < time()) {
  flash_set('err', 'Verification code expired.');
  redirect($bp . "/verify-login.php");
}

if ($code !== $row['otp_code']) {
  flash_set('err', 'Incorrect code.');
  redirect($bp . "/verify-login.php");
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
SET
failed_login_attempts = 0,
security_challenge_required = 0
WHERE id = ?
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$stmt->close();

$mysqli->commit();

} catch (Throwable $e) {
$mysqli->rollback();
flash_set('err', 'Verification failed.');
redirect($bp . "/verify-login.php");
}

unset($_SESSION['verify_login_user']);

$_SESSION['user_id'] = $uid;
load_user_into_session($mysqli, $uid);

$stmt = $mysqli->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$stmt->close();

redirect("/cardgame/dashboard.php");