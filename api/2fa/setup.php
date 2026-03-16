<?php
session_start();

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

if (!is_logged_in()) {
  http_response_code(401);
  exit('Unauthorized');
}

$u = current_user();
$userId = (int)($u['id'] ?? 0);
$email  = trim((string)($u['email'] ?? ''));
$name   = trim((string)($u['username'] ?? 'user' . $userId));

if ($userId <= 0) {
  http_response_code(400);
  exit('Invalid user.');
}

$google2fa = new Google2FA();

// Generate a fresh secret every time setup is opened.
// This is okay because 2FA is not enabled until verified.
$secret = $google2fa->generateSecretKey();

// Save or replace secret in DB, but keep disabled until verification step.
$stmt = $mysqli->prepare("
  INSERT INTO two_factor_secrets (user_id, secret_key, is_enabled, enabled_at)
  VALUES (?, ?, 0, NULL)
  ON DUPLICATE KEY UPDATE
    secret_key = VALUES(secret_key),
    is_enabled = 0,
    enabled_at = NULL,
    updated_at = CURRENT_TIMESTAMP
");
$stmt->bind_param("is", $userId, $secret);
$stmt->execute();
$stmt->close();

// Build otpauth URI for Google Authenticator
$appName = 'Logia';
$accountName = $email !== '' ? $email : $name;
$qrText = $google2fa->getQRCodeUrl($appName, $accountName, $secret);

// Render QR as inline SVG
$renderer = new ImageRenderer(
  new RendererStyle(260),
  new SvgImageBackEnd()
);
$writer = new Writer($renderer);
$qrSvg = $writer->writeString($qrText);

// store temporary secret in session too, optional but useful later
$_SESSION['twofa_setup_user'] = $userId;

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Set Up 2FA</title>
  <link rel="stylesheet" href="/cardgame/assets/style.css">
  <link rel="stylesheet" href="/cardgame/assets/hub.css">
</head>
<body>
  <section class="section">
    <div class="card" style="max-width:720px; margin:40px auto; padding:20px;">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <div>
          <h2 style="margin:0 0 6px;">Set Up Two-Factor Authentication</h2>
          <div style="color:var(--muted); font-size:14px;">
            Scan this QR code with Google Authenticator, then enter the 6-digit code.
          </div>
        </div>
        <a class="btn btn-ghost" href="/cardgame/profile.php?tab=security">Back</a>
      </div>

      <div style="margin-top:18px; display:grid; grid-template-columns: 280px minmax(0,1fr); gap:18px; align-items:start;">
        <div class="card-soft" style="padding:16px; display:grid; place-items:center;">
          <div style="width:260px; height:260px; background:#fff; padding:10px; border-radius:16px;">
            <?= $qrSvg ?>
          </div>
        </div>

        <div style="display:grid; gap:12px;">
          <div class="card-soft" style="padding:14px;">
            <div style="font-weight:900; margin-bottom:8px;">Manual Key</div>
            <div style="font-family:monospace; word-break:break-all; font-size:14px;">
              <?= h($secret) ?>
            </div>
            <div style="margin-top:8px; color:var(--muted); font-size:13px;">
              Use this only if you can’t scan the QR code.
            </div>
          </div>

          <form method="post" action="/cardgame/api/2fa/verify-setup.php" class="card-soft" style="padding:14px;">
            <label style="display:block; font-size:12px; color:var(--muted); margin-bottom:8px;">Enter 6-digit code</label>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
              <input class="input" type="text" name="code" maxlength="6" inputmode="numeric" placeholder="123456" style="max-width:180px;">
              <button class="btn btn-primary" type="submit">Verify & Enable</button>
            </div>
          </form>

          <div class="card-soft" style="padding:14px;">
            <div style="font-weight:900; margin-bottom:8px;">Steps</div>
            <div style="color:var(--muted); font-size:13px; line-height:1.5;">
              1. Open Google Authenticator<br>
              2. Tap add account<br>
              3. Scan the QR code<br>
              4. Enter the 6-digit code here
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</body>
</html>