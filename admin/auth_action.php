<?php
//admin auth
session_start();

require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";

$bp = base_path();

function admin_client_ip_bin(): ?string {
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  $packed = @inet_pton($ip);
  return $packed === false ? null : $packed;
}

function admin_log_login_attempt(mysqli $mysqli, ?int $userId, string $identifier, bool $success, ?string $reason = null): void {
  $ip = admin_client_ip_bin();
  $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
  $ok = $success ? 1 : 0;

  $stmt = $mysqli->prepare("
    INSERT INTO login_attempts (
      user_id, identifier, success, ip_address, user_agent, failure_reason
    )
    VALUES (?, ?, ?, ?, ?, ?)
  ");
  $stmt->bind_param("isisss", $userId, $identifier, $ok, $ip, $ua, $reason);
  $stmt->execute();
  $stmt->close();
}

function admin_audit_login(mysqli $mysqli, int $userId, string $action): void {
  $ip = admin_client_ip_bin();

  $stmt = $mysqli->prepare("
    INSERT INTO audit_logs (
      actor_user_id, action, target_type, target_id, metadata_json, ip_address
    )
    VALUES (?, ?, 'admin_login', ?, JSON_OBJECT('portal','admin'), ?)
  ");
  $stmt->bind_param("isis", $userId, $action, $userId, $ip);
  $stmt->execute();
  $stmt->close();
}

if (!is_post()) redirect($bp . "/admin/login.php");

$identifier = trim((string)($_POST['identifier'] ?? ''));
$pw = (string)($_POST['password'] ?? '');

if ($identifier === '' || $pw === '') {
  flash_set('err', "Enter credentials.");
  redirect($bp . "/admin/login.php");
}

$stmt = $mysqli->prepare("
  SELECT id, username, email, password_hash,
         is_active, is_guest, approval_status, banned_until
  FROM users
  WHERE username = ? OR email = ?
  LIMIT 1
");
$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$u || empty($u['password_hash']) || !password_verify($pw, $u['password_hash'])) {
  admin_log_login_attempt($mysqli, $u ? (int)$u['id'] : null, $identifier, false, 'invalid_admin_credentials');
  flash_set('err', "Invalid credentials.");
  redirect($bp . "/admin/login.php");
}

if ($msg = assert_can_login($u)) {
  admin_log_login_attempt($mysqli, (int)$u['id'], $identifier, false, 'admin_login_blocked');
  flash_set('err', $msg);
  redirect($bp . "/admin/login.php");
}

$uid = (int)$u['id'];

session_regenerate_id(true);

$_SESSION['user_id'] = $uid;
load_user_into_session($mysqli, $uid);

$cu = current_user();
if (!$cu || !user_has_role($cu, 'admin')) {
  admin_log_login_attempt($mysqli, $uid, $identifier, false, 'not_admin');
  unset($_SESSION['user_id'], $_SESSION['user']);
  session_regenerate_id(true);
  flash_set('err', "Admins only.");
  redirect($bp . "/admin/login.php");
}

admin_log_login_attempt($mysqli, $uid, $identifier, true, null);
admin_audit_login($mysqli, $uid, 'ADMIN_LOGIN_SUCCESS');

redirect($bp . "/admin/index.php");