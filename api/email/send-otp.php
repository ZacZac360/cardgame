<?php
session_start();

require_once __DIR__ . "/../../includes/db.php";
require_once __DIR__ . "/../../includes/helpers.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/mail_config.php";

$redirect = "/cardgame/profile.php?tab=security";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $_SESSION['flash_error'] = 'Invalid request.';
  header('Location: ' . $redirect);
  exit;
}

require_login();
$u = current_user();

$userId = (int)($u['id'] ?? 0);
$email  = trim((string)($u['email'] ?? ''));

if ($userId <= 0 || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  $_SESSION['flash_error'] = 'Invalid account email.';
  header('Location: ' . $redirect);
  exit;
}

if (!empty($u['email_verified_at'])) {
  $_SESSION['flash_error'] = 'Email already verified.';
  header('Location: ' . $redirect);
  exit;
}

$stmt = $mysqli->prepare("
  SELECT created_at
  FROM email_verifications
  WHERE user_id = ?
  ORDER BY id DESC
  LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res  = $stmt->get_result();
$last = $res->fetch_assoc();
$stmt->close();

if ($last && strtotime((string)$last['created_at']) > (time() - 60)) {
  $_SESSION['flash_error'] = 'Please wait before requesting another code.';
  header('Location: ' . $redirect);
  exit;
}

try {
  $otp = (string)random_int(100000, 999999);
} catch (Exception $e) {
  $otp = (string)mt_rand(100000, 999999);
}

$expiresAt = date('Y-m-d H:i:s', time() + 300);

$mysqli->begin_transaction();

try {
  $stmt = $mysqli->prepare("
    DELETE FROM email_verifications
    WHERE user_id = ? AND verified_at IS NULL
  ");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $stmt->close();

  $stmt = $mysqli->prepare("
    INSERT INTO email_verifications (user_id, email, otp_code, expires_at)
    VALUES (?, ?, ?, ?)
  ");
  $stmt->bind_param("isss", $userId, $email, $otp, $expiresAt);
  $stmt->execute();
  $stmt->close();

  $mysqli->commit();
} catch (Throwable $e) {
  $mysqli->rollback();
  $_SESSION['flash_error'] = 'Could not create code.';
  header('Location: ' . $redirect);
  exit;
}

$payload = [
  'sender' => [
    'email' => $MAIL_FROM_EMAIL,
    'name'  => $MAIL_FROM_NAME
  ],
  'to' => [
    ['email' => $email]
  ],
  'subject' => 'Your verification code',
  'htmlContent' =>
    '<html><body style="font-family:Arial,sans-serif;">' .
    '<h2>Email Verification</h2>' .
    '<p>Your code is:</p>' .
    '<div style="font-size:28px;font-weight:bold;letter-spacing:4px;">' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</div>' .
    '<p>This code expires in 5 minutes.</p>' .
    '</body></html>'
];

$ch = curl_init('https://api.brevo.com/v3/smtp/email');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'accept: application/json',
  'content-type: application/json',
  'api-key: ' . trim($BREVO_API_KEY)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($response === false || $httpCode < 200 || $httpCode >= 300) {
  error_log("BREVO OTP ERROR HTTP={$httpCode} CURL={$curlErr} RESPONSE={$response}");

  $_SESSION['flash_error'] = $response === false
    ? ('Mail service failed: ' . $curlErr)
    : ('Mail service error. HTTP ' . $httpCode . ': ' . $response);

  header('Location: ' . $redirect);
  exit;
}

$_SESSION['flash_success'] = 'Verification code sent.';
header('Location: ' . $redirect);
exit;