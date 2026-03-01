<?php
// auth_action.php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";

$bp = base_path();

// quick GET for guest
$action = $_POST['action'] ?? $_GET['action'] ?? '';

function pw_policy_err(string $pw): ?string {
  if (strlen($pw) < 16) return "Password must be at least 16 characters.";
  if (!preg_match('/[a-z]/', $pw)) return "Password must include a lowercase letter.";
  if (!preg_match('/[A-Z]/', $pw)) return "Password must include an uppercase letter.";
  if (!preg_match('/[0-9]/', $pw)) return "Password must include a digit.";
  if (!preg_match('/[^A-Za-z0-9]/', $pw)) return "Password must include a special character.";
  return null;
}

function ip_bin(): ?string {
  $ip = $_SERVER['REMOTE_ADDR'] ?? '';
  if ($ip === '') return null;
  // store as binary(16)
  $packed = @inet_pton($ip);
  return $packed ?: null;
}

function log_login_attempt(mysqli $mysqli, ?int $user_id, string $identifier, int $success, ?string $reason): void {
  $ip = ip_bin();
  $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
  $stmt = $mysqli->prepare("
    INSERT INTO login_attempts (user_id, identifier, success, ip_address, user_agent, failure_reason)
    VALUES (?, ?, ?, ?, ?, ?)
  ");
  $stmt->bind_param("isisss", $user_id, $identifier, $success, $ip, $ua, $reason);
  $stmt->execute();
  $stmt->close();
}

function too_many_recent_failures(mysqli $mysqli, string $identifier): bool {
  // simple throttle: 8 failures in last 10 minutes for same identifier OR same IP
  $ip = ip_bin();
  $stmt = $mysqli->prepare("
    SELECT
      SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) AS fails
    FROM login_attempts
    WHERE created_at >= (NOW() - INTERVAL 10 MINUTE)
      AND (
        identifier = ?
        OR (ip_address IS NOT NULL AND ip_address = ?)
      )
  ");
  $stmt->bind_param("ss", $identifier, $ip);
  $stmt->execute();
  $fails = (int)($stmt->get_result()->fetch_assoc()['fails'] ?? 0);
  $stmt->close();
  return $fails >= 8;
}

if ($action === 'guest') {
  // Create an auto-approved guest user
  $guestName = 'guest_' . strtoupper(bin2hex(random_bytes(4)));
  $email = $guestName . '@guest.local'; // placeholder; keeps UNIQUE(email) happy

  $stmt = $mysqli->prepare("
    INSERT INTO users (username, email, password_hash, display_name, email_verified_at,
                       approval_status, is_guest, is_active, bank_link_status)
    VALUES (?, ?, '', ?, NULL,
            'approved', 1, 1, 'none')
  ");
  $display = "Guest";
  $stmt->bind_param("sss", $guestName, $email, $display);
  $stmt->execute();
  $uid = (int)$stmt->insert_id;
  $stmt->close();

  // assign player role
  $stmt = $mysqli->prepare("
    INSERT INTO user_roles (user_id, role_id, assigned_by)
    SELECT ?, id, NULL
    FROM roles
    WHERE name = 'player'
    LIMIT 1
  ");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $stmt->close();

  // audit
  $stmt = $mysqli->prepare("
    INSERT INTO audit_logs (actor_user_id, action, target_type, target_id, metadata_json, ip_address)
    VALUES (NULL, 'GUEST_CREATE', 'user', ?, JSON_OBJECT('username', ?), ?)
  ");
  $ip = ip_bin();
  $stmt->bind_param("iss", $uid, $guestName, $ip);
  $stmt->execute();
  $stmt->close();

  $_SESSION['user_id'] = $uid;
  load_user_into_session($mysqli, $uid);

  flash_set('msg', "Playing as guest: {$guestName}");
  redirect($bp . "/dashboard.php");
}

if (!is_post()) {
  redirect($bp . "/index.php");
}

if ($action === 'register') {
  $username = trim((string)($_POST['username'] ?? ''));
  $email    = trim((string)($_POST['email'] ?? ''));
  $pw       = (string)($_POST['password'] ?? '');
  $pw2      = (string)($_POST['password2'] ?? '');

  if ($username === '' || $email === '' || $pw === '' || $pw2 === '') {
    flash_set('err', "Please fill in all fields.");
    redirect($bp . "/index.php");
  }
  if (!preg_match('/^[A-Za-z0-9_]{3,32}$/', $username)) {
    flash_set('err', "Username must be 3–32 chars and use letters/numbers/underscore only.");
    redirect($bp . "/index.php");
  }
  if ($pw !== $pw2) {
    flash_set('err', "Passwords do not match.");
    redirect($bp . "/index.php");
  }
  if ($pe = pw_policy_err($pw)) {
    flash_set('err', $pe);
    redirect($bp . "/index.php");
  }

  // insert pending user
  $hash = password_hash($pw, PASSWORD_DEFAULT);
  try {
    $stmt = $mysqli->prepare("
      INSERT INTO users (username, email, password_hash, display_name, email_verified_at,
                         approval_status, is_guest, is_active, bank_link_status)
      VALUES (?, ?, ?, ?, NULL,
              'pending', 0, 1, 'none')
    ");
    $display = $username;
    $stmt->bind_param("ssss", $username, $email, $hash, $display);
    $stmt->execute();
    $uid = (int)$stmt->insert_id;
    $stmt->close();
  } catch (mysqli_sql_exception $e) {
    $msg = "Registration failed.";
    if (str_contains($e->getMessage(), 'uq_users_username')) $msg = "Username is already taken.";
    if (str_contains($e->getMessage(), 'uq_users_email')) $msg = "Email is already registered.";
    flash_set('err', $msg);
    redirect($bp . "/index.php");
  }

  // assign player role
  $stmt = $mysqli->prepare("
    INSERT INTO user_roles (user_id, role_id, assigned_by)
    SELECT ?, id, NULL
    FROM roles
    WHERE name = 'player'
    LIMIT 1
  ");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $stmt->close();

  // notify admins (one notification per admin user)
  $stmt = $mysqli->prepare("
    INSERT INTO dashboard_notifications (user_id, type, title, body, link_url)
    SELECT ur.user_id,
           'admin_approval',
           'New account pending approval',
           CONCAT('User: ', ?, ' (', ?, ')'),
           CONCAT('/admin/pending-users.php?user_id=', ?)
    FROM user_roles ur
    JOIN roles r ON r.id = ur.role_id
    WHERE r.name = 'admin'
  ");
  $stmt->bind_param("ssi", $username, $email, $uid);
  $stmt->execute();
  $stmt->close();

  // audit
  $stmt = $mysqli->prepare("
    INSERT INTO audit_logs (actor_user_id, action, target_type, target_id, metadata_json, ip_address)
    VALUES (NULL, 'USER_REGISTER', 'user', ?, JSON_OBJECT('email', ?, 'username', ?), ?)
  ");
  $ip = ip_bin();
  $stmt->bind_param("isss", $uid, $email, $username, $ip);
  $stmt->execute();
  $stmt->close();

  flash_set('msg', "Registered! Your account is pending admin approval.");
  redirect($bp . "/index.php");
}

if ($action === 'login') {
  $identifier = trim((string)($_POST['identifier'] ?? ''));
  $pw = (string)($_POST['password'] ?? '');

  if ($identifier === '' || $pw === '') {
    flash_set('err', "Please enter your credentials.");
    redirect($bp . "/index.php");
  }

  if (too_many_recent_failures($mysqli, $identifier)) {
    flash_set('err', "Too many attempts. Try again in a few minutes.");
    redirect($bp . "/index.php");
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
    log_login_attempt($mysqli, $u ? (int)$u['id'] : null, $identifier, 0, "wrong_credentials");
    flash_set('err', "Invalid credentials.");
    redirect($bp . "/index.php");
  }

  // block if pending approval (your rule)
  if ($msg = assert_can_login($u)) {
    log_login_attempt($mysqli, (int)$u['id'], $identifier, 0, "not_allowed");
    flash_set('err', $msg);
    redirect($bp . "/index.php");
  }

  // success: set session + update last_login_at
  $uid = (int)$u['id'];
  $_SESSION['user_id'] = $uid;
  load_user_into_session($mysqli, $uid);

  $stmt = $mysqli->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $stmt->close();

  log_login_attempt($mysqli, $uid, $identifier, 1, null);

  // Create session record (hash a random refresh token)
  $refresh = bin2hex(random_bytes(32));
  $refresh_hash = hash('sha256', $refresh);
  $ua = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
  $ip = ip_bin();
  $stmt = $mysqli->prepare("
    INSERT INTO auth_sessions (user_id, refresh_token_hash, user_agent, ip_address, expires_at)
    VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 14 DAY))
  ");
  $stmt->bind_param("isss", $uid, $refresh_hash, $ua, $ip);
  $stmt->execute();
  $sid = (int)$stmt->insert_id;
  $stmt->close();

  // Store session id + refresh in PHP session (for MVP; later you’ll set secure cookie)
  $_SESSION['auth_session_id'] = $sid;
  $_SESSION['refresh_token'] = $refresh;

  // Redirect based on role
  $cu = current_user();
  if ($cu && (user_has_role($cu, 'admin') || user_has_role($cu, 'moderator'))) {
    redirect($bp . "/admin/index.php");
  }
  redirect($bp . "/dashboard.php");
}

flash_set('err', "Unknown action.");
redirect($bp . "/index.php");