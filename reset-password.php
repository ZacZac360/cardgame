<?php
session_start();

require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";

$bp = base_path();

if (empty($_SESSION['reset_password_verified']) || empty($_SESSION['reset_user_id'])) {
  flash_set('err', 'Verify your reset code first.');
  header("Location: {$bp}/forgot-password.php");
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
  <title>Logia — Reset Password</title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
</head>
<body>
  <main class="container" style="max-width:720px; padding:48px 18px;">
    <div class="card" style="padding:22px;">
      <div class="chip">ACCOUNT RECOVERY</div>
      <h1 style="margin:14px 0 8px;">Choose New Password</h1>
      <p class="lead">
        Set a new password. After saving, you’ll need to log in again.
      </p>

      <?php if ($err): ?>
        <div class="banner banner--bad"><?= h($err) ?></div>
      <?php elseif ($msg): ?>
        <div class="banner banner--good"><?= h($msg) ?></div>
      <?php endif; ?>

      <form method="post" action="<?= h($bp) ?>/forgot_password_action.php" style="margin-top:16px; display:grid; gap:14px;">
        <input type="hidden" name="action" value="reset_password"/>

        <div>
          <label for="reg_password" style="display:block; margin-bottom:8px;">New Password</label>
          <input
            id="reg_password"
            name="password"
            type="password"
            autocomplete="new-password"
            required
            class="input"
            style="width:100%;"
          />
        </div>

        <div>
          <label for="reg_password2" style="display:block; margin-bottom:8px;">Confirm New Password</label>
          <input
            id="reg_password2"
            name="password2"
            type="password"
            autocomplete="new-password"
            required
            class="input"
            style="width:100%;"
          />
        </div>

        <div>
          <div style="height:10px; border-radius:999px; background:rgba(255,255,255,.08); overflow:hidden; border:1px solid rgba(255,255,255,.08);">
            <div id="pwBar" style="height:100%; width:0%; background:linear-gradient(90deg, rgba(255,77,109,.95), rgba(255,205,102,.95), rgba(57,255,106,.95)); transition:width .18s ease;"></div>
          </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start;">
          <ul id="pwReq" style="margin:0; padding-left:18px; line-height:1.8;">
            <li data-req="len" class="bad">At least 16 characters</li>
            <li data-req="low" class="bad">Lowercase letter</li>
            <li data-req="up" class="bad">Uppercase letter</li>
            <li data-req="num" class="bad">Number</li>
            <li data-req="sym" class="bad">Special character</li>
          </ul>

          <div>
            <div id="pwMatch" style="font-size:14px; font-weight:700; min-height:24px;"></div>
            <div style="color:var(--muted); font-size:13px; line-height:1.5; margin-top:8px;">
              Your new password must meet the same security rules as registration.
            </div>
          </div>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">
          <button id="regBtn" class="btn btn-primary" type="submit" disabled>Save New Password</button>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/index.php">Back to Login</a>
        </div>
      </form>
    </div>
  </main>

  <script src="<?= h($bp) ?>/assets/main.js"></script>
</body>
</html>