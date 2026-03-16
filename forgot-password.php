<?php
session_start();

require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";

$bp = base_path();

if (is_logged_in()) {
  header("Location: {$bp}/dashboard.php");
  exit;
}

$err = flash_get('err');
$msg = flash_get('msg');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia — Forgot Password</title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
</head>
<body>
  <main class="container" style="max-width:560px; padding:48px 18px;">
    <div class="card" style="padding:22px;">
      <div class="chip">ACCOUNT RECOVERY</div>
      <h1 style="margin:14px 0 8px;">Forgot Password</h1>
      <p class="lead">
        Enter your email address. We’ll send a one-time code so you can reset your password.
      </p>

      <?php if ($err): ?>
        <div class="banner banner--bad"><?= h($err) ?></div>
      <?php elseif ($msg): ?>
        <div class="banner banner--good"><?= h($msg) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= h($bp) ?>/forgot_password_action.php" style="margin-top:16px;">
        <input type="hidden" name="action" value="send_otp"/>

        <label for="email">Email</label>
        <input
          id="email"
          name="email"
          type="email"
          required
          autocomplete="email"
          class="input"
          style="margin-bottom:14px;"
        />

        <div class="formrow" style="display:flex; gap:10px; flex-wrap:wrap;">
          <button class="btn btn-primary" type="submit">Send Code</button>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/index.php">Back to Login</a>
        </div>
      </form>
    </div>
  </main>
</body>
</html>