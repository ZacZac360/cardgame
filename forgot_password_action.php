<?php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/mail_config.php";

$bp = base_path();

if (!is_post()) {
  header("Location: {$bp}/forgot-password.php");
  exit;
}

$action = (string)($_POST['action'] ?? '');

function pw_policy_err_local(string $pw): ?string {
  if (strlen($pw) < 16) return "Password must be at least 16 characters.";
  if (!preg_match('/[a-z]/', $pw)) return "Password must include a lowercase letter.";
  if (!preg_match('/[A-Z]/', $pw)) return "Password must include an uppercase letter.";
  if (!preg_match('/[0-9]/', $pw)) return "Password must include a digit.";
  if (!preg_match('/[^A-Za-z0-9]/', $pw)) return "Password must include a special character.";
  return null;
}

function clear_reset_flow(): void {
  unset(
    $_SESSION['reset_user_id'],
    $_SESSION['reset_email'],
    $_SESSION['reset_password_verified']
  );
}

if ($action === 'send_otp') {
  $email = trim((string)($_POST['email'] ?? ''));

  if ($email === '') {
    flash_set('err', 'Enter your email address.');
    header("Location: {$bp}/forgot-password.php");
    exit;
  }

  $stmt = $mysqli->prepare("
    SELECT id, email, is_guest, is_active
    FROM users
    WHERE email = ?
    LIMIT 1
  ");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $u = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  // Generic message to avoid leaking whether an email exists
  if (!$u || (int)$u['is_guest'] === 1 || (int)$u['is_active'] !== 1) {
    flash_set('msg', 'If that email exists, a verification code has been sent.');
    header("Location: {$bp}/forgot-password.php");
    exit;
  }

  $uid = (int)$u['id'];
  $otp = (string)random_int(100000, 999999);
  $expires = date('Y-m-d H:i:s', time() + 300);

  $stmt = $mysqli->prepare("
    INSERT INTO email_verifications (user_id, email, otp_code, expires_at, purpose)
    VALUES (?, ?, ?, ?, 'password_reset')
  ");
  $stmt->bind_param("isss", $uid, $u['email'], $otp, $expires);
  $stmt->execute();
  $stmt->close();

  $payload = [
    'sender' => [
      'email' => $MAIL_FROM_EMAIL,
      'name'  => $MAIL_FROM_NAME
    ],
    'to' => [[
      'email' => $u['email']
    ]],
    'subject' => 'Logia Password Reset Code',
    'htmlContent' => "
      <h2>Password Reset</h2>
      <p>Your Logia password reset code is:</p>
      <p style='font-size:32px; font-weight:700; letter-spacing:4px;'>{$otp}</p>
      <p>This code expires in 5 minutes.</p>
      <p>If you did not request this, you can ignore this email.</p>
    "
  ];

  $ch = curl_init('https://api.brevo.com/v3/smtp/email');
  curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'accept: application/json',
    'content-type: application/json',
    'api-key: ' . $BREVO_API_KEY
  ]);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
  curl_exec($ch);
  curl_close($ch);

  $_SESSION['reset_user_id'] = $uid;
  $_SESSION['reset_email'] = $u['email'];
  $_SESSION['reset_password_verified'] = false;

  flash_set('msg', 'Verification code sent.');
  header("Location: {$bp}/forgot-password-verify.php");
  exit;
}

if ($action === 'verify_otp') {
  $uid   = (int)($_SESSION['reset_user_id'] ?? 0);
  $email = (string)($_SESSION['reset_email'] ?? '');
  $code  = trim((string)($_POST['code'] ?? ''));

  if ($uid <= 0 || $email === '') {
    flash_set('err', 'Start the password reset process first.');
    header("Location: {$bp}/forgot-password.php");
    exit;
  }

  if ($code === '') {
    flash_set('err', 'Enter the verification code.');
    header("Location: {$bp}/forgot-password-verify.php");
    exit;
  }

  $stmt = $mysqli->prepare("
    SELECT id, otp_code, expires_at, verified_at
    FROM email_verifications
    WHERE user_id = ?
      AND email = ?
      AND purpose = 'password_reset'
    ORDER BY id DESC
    LIMIT 1
  ");
  $stmt->bind_param("is", $uid, $email);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) {
    flash_set('err', 'No verification code found.');
    header("Location: {$bp}/forgot-password.php");
    exit;
  }

  if (!empty($row['verified_at'])) {
    $_SESSION['reset_password_verified'] = true;
    header("Location: {$bp}/reset-password.php");
    exit;
  }

  if (strtotime((string)$row['expires_at']) < time()) {
    flash_set('err', 'That code has expired. Request a new one.');
    header("Location: {$bp}/forgot-password.php");
    exit;
  }

  if ((string)$row['otp_code'] !== $code) {
    flash_set('err', 'Invalid verification code.');
    header("Location: {$bp}/forgot-password-verify.php");
    exit;
  }

  $verId = (int)$row['id'];

  $stmt = $mysqli->prepare("
    UPDATE email_verifications
    SET verified_at = NOW()
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param("i", $verId);
  $stmt->execute();
  $stmt->close();

  $_SESSION['reset_password_verified'] = true;

  header("Location: {$bp}/reset-password.php");
  exit;
}

if ($action === 'reset_password') {
  $uid = (int)($_SESSION['reset_user_id'] ?? 0);
  $verified = !empty($_SESSION['reset_password_verified']);

  if ($uid <= 0 || !$verified) {
    flash_set('err', 'Verify your code first.');
    header("Location: {$bp}/forgot-password.php");
    exit;
  }

  $pw  = (string)($_POST['password'] ?? '');
  $pw2 = (string)($_POST['password2'] ?? '');

  if ($pw === '' || $pw2 === '') {
    flash_set('err', 'Fill in both password fields.');
    header("Location: {$bp}/reset-password.php");
    exit;
  }

  if ($pw !== $pw2) {
    flash_set('err', 'Passwords do not match.');
    header("Location: {$bp}/reset-password.php");
    exit;
  }

  if ($pe = pw_policy_err_local($pw)) {
    flash_set('err', $pe);
    header("Location: {$bp}/reset-password.php");
    exit;
  }

  $hash = password_hash($pw, PASSWORD_DEFAULT);

  $mysqli->begin_transaction();

  try {
    $stmt = $mysqli->prepare("
      UPDATE users
      SET password_hash = ?,
          failed_login_attempts = 0,
          security_challenge_required = 0
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param("si", $hash, $uid);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("
      UPDATE auth_sessions
      SET revoked_at = NOW()
      WHERE user_id = ?
        AND revoked_at IS NULL
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->close();

    $stmt = $mysqli->prepare("
      INSERT INTO audit_logs (actor_user_id, action, target_type, target_id, metadata_json)
      VALUES (NULL, 'PASSWORD_RESET', 'user', ?, JSON_OBJECT('method', 'email_otp'))
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->close();

    $mysqli->commit();
  } catch (Throwable $e) {
    $mysqli->rollback();
    flash_set('err', 'Failed to reset password.');
    header("Location: {$bp}/reset-password.php");
    exit;
  }

  clear_reset_flow();

  unset(
    $_SESSION['user_id'],
    $_SESSION['user'],
    $_SESSION['auth_session_id'],
    $_SESSION['refresh_token']
  );

  session_regenerate_id(true);

  flash_set('msg', 'Password updated. Please log in again.');
  header("Location: {$bp}/index.php");
  exit;
}

flash_set('err', 'Unknown action.');
header("Location: {$bp}/forgot-password.php");
exit;