<?php
session_start();

require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

$u = current_user();
$bp = base_path();

$userId = (int)$u['id'];

if ((int)($u['is_guest'] ?? 0) === 1) {
  $_SESSION['flash_error'] = "Guest accounts cannot manage backup codes.";
  header("Location: {$bp}/guest_dashboard.php");
  exit;
}

/* verify 2FA is enabled */

$stmt = $mysqli->prepare("
  SELECT is_enabled
  FROM two_factor_secrets
  WHERE user_id = ?
  LIMIT 1
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$res || (int)$res['is_enabled'] !== 1) {
  $_SESSION['flash_error'] = "Enable 2FA first.";
  header("Location: {$bp}/profile.php?tab=security");
  exit;
}

/* generate 10 codes */

$codes = [];

for ($i = 0; $i < 10; $i++) {
  $code = strtoupper(bin2hex(random_bytes(4))); // 8-char code
  $codes[] = $code;

  $hash = password_hash($code, PASSWORD_DEFAULT);

  $stmt = $mysqli->prepare("
    INSERT INTO backup_codes (user_id, code_hash)
    VALUES (?, ?)
  ");
  $stmt->bind_param("is", $userId, $hash);
  $stmt->execute();
  $stmt->close();
}

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Backup Codes</title>
<link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css">
<link rel="stylesheet" href="<?= h($bp) ?>/assets/hub.css">
</head>
<body>

<section class="section">
<div class="card" style="max-width:640px;margin:40px auto;padding:20px;">

<h2>Backup Codes</h2>

<p style="color:var(--muted);font-size:14px;">
Save these codes somewhere safe. Each code can only be used once.
</p>

<div class="card-soft" style="padding:14px;margin-top:14px;font-family:monospace;font-size:18px;display:grid;gap:6px;">
<?php foreach ($codes as $c): ?>
<div><?= h($c) ?></div>
<?php endforeach; ?>
</div>

<div style="margin-top:16px;">
<a class="btn btn-primary" href="<?= h($bp) ?>/profile.php?tab=security">Done</a>
</div>

</div>
</section>

</body>
</html>