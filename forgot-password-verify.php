<?php
session_start();

require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";

$bp = base_path();

if (empty($_SESSION['reset_user_id']) || empty($_SESSION['reset_email'])) {
  flash_set('err', 'Start the password reset process first.');
  header("Location: {$bp}/forgot-password.php");
  exit;
}

$err = flash_get('err');
$msg = flash_get('msg');
$email = (string)$_SESSION['reset_email'];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia — Verify Reset Code</title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
</head>
<body>
  <main class="container" style="max-width:560px; padding:48px 18px;">
    <div class="card" style="padding:22px;">
      <div class="chip">ACCOUNT RECOVERY</div>
      <h1 style="margin:14px 0 8px;">Verify Code</h1>
      <p class="lead">
        Enter the 6-digit code sent to <b><?= h($email) ?></b>.
      </p>

      <?php if ($err): ?>
        <div class="banner banner--bad"><?= h($err) ?></div>
      <?php elseif ($msg): ?>
        <div class="banner banner--good"><?= h($msg) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= h($bp) ?>/forgot_password_action.php" style="margin-top:16px;">
        <input type="hidden" name="action" value="verify_otp"/>

        <label for="code">Verification Code</label>
        <input
          id="code"
          name="code"
          type="text"
          inputmode="numeric"
          maxlength="6"
          required
          class="input"
          style="margin-bottom:14px;"
        />

        <div class="formrow" style="display:flex; gap:10px; flex-wrap:wrap;">
          <button class="btn btn-primary" type="submit">Verify Code</button>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/forgot-password.php">Start Over</a>
        </div>
      </form>
    </div>
  </main>
</body>
</html>