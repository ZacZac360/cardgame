<?php
// guest_exit.php
session_start();

require_once __DIR__ . "/includes/helpers.php";

$bp = base_path();

$to = trim((string)($_GET['to'] ?? 'register'));

$_SESSION = [];

if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();

  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
  );
}

session_destroy();

if ($to === 'login') {
  header("Location: {$bp}/index.php?auth=login");
  exit;
}

header("Location: {$bp}/index.php?auth=register");
exit;