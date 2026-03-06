<?php
session_start();

require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";

$bp = base_path();

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
  flash_set('err', "Invalid credentials.");
  redirect($bp . "/admin/login.php");
}

if ($msg = assert_can_login($u)) {
  flash_set('err', $msg);
  redirect($bp . "/admin/login.php");
}

$uid = (int)$u['id'];
$_SESSION['user_id'] = $uid;
load_user_into_session($mysqli, $uid);

$cu = current_user();
if (!$cu || !user_has_role($cu, 'admin')) {
  unset($_SESSION['user_id'], $_SESSION['user']);
  flash_set('err', "Admins only.");
  redirect($bp . "/admin/login.php");
}

redirect($bp . "/admin/index.php");