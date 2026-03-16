<?php
// includes/auth.php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function current_user(): ?array {
  global $mysqli;

  $uid = (int)($_SESSION['user_id'] ?? 0);
  if ($uid <= 0) return null;

  load_user_into_session($mysqli, $uid);
  return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
  return isset($_SESSION['user_id']);
}

function load_user_into_session(mysqli $mysqli, int $uid): void {
  $stmt = $mysqli->prepare("
    SELECT id, username, email, display_name,
          avatar_path, bio, favorite_deck, tagline, appearance_mode,
          is_active, is_guest,
          approval_status, email_verified_at,
          bank_link_status, banned_until, last_login_at,
          level, exp, exp_to_next, credits, matches_played, matches_won,
          created_at
    FROM users
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $u = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$u) {
    unset($_SESSION['user_id'], $_SESSION['user']);
    return;
  }

  // safety default
  $mode = (string)($u['appearance_mode'] ?? 'default');
  if (!in_array($mode, ['default', 'dark', 'light'], true)) {
    $mode = 'default';
  }
  $u['appearance_mode'] = $mode;

  // roles
  $stmt = $mysqli->prepare("
    SELECT r.name
    FROM user_roles ur
    JOIN roles r ON r.id = ur.role_id
    WHERE ur.user_id = ?
    ORDER BY r.name
  ");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $rs = $stmt->get_result();
  $roles = [];
  while ($row = $rs->fetch_assoc()) $roles[] = $row['name'];
  $stmt->close();

  $u['roles'] = $roles;

  $_SESSION['user_id'] = $uid;
  $_SESSION['user'] = $u;
}

function user_has_role(?array $user, string $role): bool {
  if (!$user) return false;
  $roles = $user['roles'] ?? [];
  return in_array($role, $roles, true);
}

function require_login(): void {
  if (!is_logged_in()) {
    $bp = base_path();
    redirect($bp . "/index.php");
  }
}

function require_role(string $role): void {
  require_login();
  $u = current_user();
  if (!user_has_role($u, $role)) {
    $bp = base_path();
    redirect($bp . "/dashboard.php");
  }
}

/**
 * Blocks first-time login until admin approves.
 * Call this right after verifying password.
 */
function assert_can_login(array $u): ?string {
  if (($u['is_active'] ?? 0) != 1) return "Account is inactive.";
  if (!empty($u['banned_until'])) {
    $ts = strtotime($u['banned_until']);
    if ($ts && $ts > time()) return "Account is banned until " . date("M d, Y g:i A", $ts) . ".";
  }
  if (($u['approval_status'] ?? 'pending') !== 'approved') {
    return "Account pending admin approval.";
  }
  return null;
}