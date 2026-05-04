<?php
session_start();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

$bp = base_path();
$u = current_user();

$userId = (int)($u['id'] ?? 0);
$isGuest = ((int)($u['is_guest'] ?? 0) === 1);

if ($isGuest) {
  $_SESSION['flash_error'] = "Guest accounts cannot manage two-factor authentication.";
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

$stmt = $mysqli->prepare("
  UPDATE two_factor_secrets
  SET is_enabled = 0,
      enabled_at = NULL,
      updated_at = CURRENT_TIMESTAMP
  WHERE user_id = ?
  LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

$stmt = $mysqli->prepare("
  DELETE FROM backup_codes
  WHERE user_id = ?
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->close();

$_SESSION['flash_success'] = "Two-factor authentication disabled.";
header("Location: {$bp}/profile.php?tab=security");
exit;