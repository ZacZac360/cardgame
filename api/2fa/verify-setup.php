<?php
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

use PragmaRX\Google2FA\Google2FA;

$bp = base_path();

if (!is_logged_in()) {
  http_response_code(401);
  exit('Unauthorized');
}

$u = current_user();

$userId = (int)($u['id'] ?? 0);

if ((int)($u['is_guest'] ?? 0) === 1) {
  $_SESSION['flash_error'] = "Guest accounts cannot set up two-factor authentication.";
  header("Location: {$bp}/guest_dashboard.php");
  exit;
}

if ($userId <= 0) {
  $_SESSION['flash_error'] = "Invalid user.";
  header("Location: {$bp}/profile.php?tab=security");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: {$bp}/profile.php?tab=security");
  exit;
}

$code = preg_replace('/\D+/', '', (string)($_POST['code'] ?? ''));

if (strlen($code) !== 6) {
  $_SESSION['flash_error'] = "Enter the 6-digit authentication code.";
  header("Location: {$bp}/api/2fa/setup.php");
  exit;
}

$stmt = $mysqli->prepare("
  SELECT secret_key
  FROM two_factor_secrets
  WHERE user_id = ?
  LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res || empty($res['secret_key'])) {
  $_SESSION['flash_error'] = "2FA setup not found. Please start setup again.";
  header("Location: {$bp}/api/2fa/setup.php");
  exit;
}

$secret = (string)$res['secret_key'];

$google2fa = new Google2FA();

/*
  Third parameter = time window.
  8 allows slight phone/server time drift.
  You can lower this to 4 later if you want stricter verification.
*/
$valid = $google2fa->verifyKey($secret, $code, 8);

if (!$valid) {
  $_SESSION['flash_error'] = "Invalid authentication code. Check your phone time and try again.";
  header("Location: {$bp}/api/2fa/setup.php");
  exit;
}

$stmt = $mysqli->prepare("
  UPDATE two_factor_secrets
  SET is_enabled = 1,
      enabled_at = NOW(),
      updated_at = CURRENT_TIMESTAMP
  WHERE user_id = ?
  LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

$_SESSION['twofa_setup_user'] = null;
unset($_SESSION['twofa_setup_user']);

$_SESSION['flash_success'] = "Two-factor authentication enabled.";

header("Location: {$bp}/profile.php?tab=security");
exit;