<?php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";

$bp = base_path();

if (!isset($_SESSION['verify_login_user'])) {
  flash_set('err', 'Verification required before login.');
  redirect($bp . "/index.php");
}

$uid = (int)$_SESSION['verify_login_user'];

$stmt = $mysqli->prepare("
  SELECT id, email
  FROM users
  WHERE id = ?
  LIMIT 1
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
  unset($_SESSION['verify_login_user']);
  flash_set('err', 'User not found.');
  redirect($bp . "/index.php");
}

$email = $user['email'];
?>

<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Login Verification — Logia</title>
<link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css">
</head>

<body>

<div class="container" style="max-width:520px;margin-top:120px;">

<h2>Verify Login</h2>

<p>
Because of several failed login attempts, we need to verify your identity.
</p>

<p>
A verification code has been sent to:<br>
<strong><?= h($email) ?></strong>
</p>

<?php if ($err = flash_get('err')): ?>
<div class="banner banner--bad"><?= h($err) ?></div>
<?php endif; ?>

<?php if ($msg = flash_get('msg')): ?>
<div class="banner banner--good"><?= h($msg) ?></div>
<?php endif; ?>

<form method="post" action="<?= h($bp) ?>/api/email/verify-login.php">

<label>Enter verification code</label>

<input
type="text"
name="code"
maxlength="6"
required
style="font-size:22px;letter-spacing:4px;text-align:center;"
>

<div style="margin-top:20px;">
<button class="btn btn-primary" type="submit">
Verify Login
</button>
</div>

</form>

</div>

</body>
</html>