<?php
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

use PragmaRX\Google2FA\Google2FA;

if (!is_logged_in()) {
  http_response_code(401);
  exit('Unauthorized');
}

$u = current_user();
$userId = (int)($u['id'] ?? 0);

$code = trim((string)($_POST['code'] ?? ''));

if ($code === '') {
  $_SESSION['flash_error'] = "Enter the 6-digit code.";
  header("Location: /cardgame/api/2fa/setup.php");
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

if (!$res) {
  $_SESSION['flash_error'] = "2FA setup not found.";
  header("Location: /cardgame/profile.php?tab=security");
  exit;
}

$secret = $res['secret_key'];

$google2fa = new Google2FA();

$valid = $google2fa->verifyKey($secret, $code);

if (!$valid) {
  $_SESSION['flash_error'] = "Invalid authentication code.";
  header("Location: /cardgame/api/2fa/setup.php");
  exit;
}

/* Enable 2FA */

$stmt = $mysqli->prepare("
  UPDATE two_factor_secrets
  SET is_enabled = 1,
      enabled_at = NOW()
  WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

$_SESSION['flash_success'] = "Two-factor authentication enabled.";

header("Location: /cardgame/profile.php?tab=security");
exit;