<?php
// logout.php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";

$bp = base_path();

$sid = (int)($_SESSION['auth_session_id'] ?? 0);
if ($sid > 0) {
  $stmt = $mysqli->prepare("UPDATE auth_sessions SET revoked_at = NOW() WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $sid);
  $stmt->execute();
  $stmt->close();
}

$_SESSION = [];
session_destroy();

redirect($bp . "/index.php");