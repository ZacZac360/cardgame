<?php
// index.php
session_start();
require_once __DIR__ . "/includes/helpers.php";

$bp  = base_path();
$err = flash_get('err');
$msg = flash_get('msg');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>CardGame — Login</title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
</head>
<body>
  <div class="wrap">
    <div class="topbar">
      <div class="brand">CardGame <span class="badge">MVP • Auth + Dashboard</span></div>
      <div class="badge">MySQL 8.0 • RBAC • Approval • 2FA-ready</div>
    </div>

    <?php if ($err): ?>
      <div class="alert bad"><?= h($err) ?></div>
    <?php elseif ($msg): ?>
      <div class="alert good"><?= h($msg) ?></div>
    <?php endif; ?>

    <div class="grid">
      <!-- ===================== LOGIN ===================== -->
      <div class="card">
        <h2>Login</h2>
        <p class="sub">Approved accounts can log in anytime. New registrations must be approved first.</p>

        <form method="post" action="<?= h($bp) ?>/auth_action.php" autocomplete="off">
          <input type="hidden" name="action" value="login"/>

          <label for="login_ident">Email or Username</label>
          <input id="login_ident" name="identifier" autocomplete="username" required />

          <label for="login_pw">Password</label>
          <input id="login_pw" name="password" type="password" autocomplete="current-password" required />

          <div class="btns">
            <button class="primary" type="submit">Login</button>
            <a class="btn" href="<?= h($bp) ?>/auth_action.php?action=guest">Play as Guest</a>
          </div>

          <small class="hint">
            Ranked matchmaking later requires: <span class="kbd">Email Verified</span> + <span class="kbd">2FA</span> + <span class="kbd">Payment/Credits</span>.
          </small>
        </form>
      </div>

      <!-- ===================== REGISTER ===================== -->
      <div class="card">
        <h2>Create account</h2>
        <p class="sub">Security policy: 16+ chars with upper/lower/digit/special + match confirmation. Admin approval required.</p>

        <form method="post" action="<?= h($bp) ?>/auth_action.php" id="regForm" autocomplete="off">
          <input type="hidden" name="action" value="register"/>

          <label for="reg_user">Username</label>
          <input id="reg_user" name="username" minlength="3" maxlength="32" autocomplete="username" required />

          <label for="reg_email">Email</label>
          <input id="reg_email" name="email" type="email" autocomplete="email" required />

          <div class="row">
            <div>
              <label for="reg_password">Password</label>
              <input id="reg_password" name="password" type="password" autocomplete="new-password" required />
              <!-- Strength bar -->
              <div class="pw-meter" aria-hidden="true"><div id="pwBar"></div></div>
              <!-- Requirements -->
              <ul class="pw-req" id="pwReq">
                <li class="bad" data-req="len">At least 16 characters</li>
                <li class="bad" data-req="low">Lowercase letter</li>
                <li class="bad" data-req="up">Uppercase letter</li>
                <li class="bad" data-req="num">Number</li>
                <li class="bad" data-req="sym">Special character (!@#$…)</li>
              </ul>
            </div>

            <div>
              <label for="reg_password2">Confirm</label>
              <input id="reg_password2" name="password2" type="password" autocomplete="new-password" required />
              <small class="hint" id="pwMatch"></small>
            </div>
          </div>

          <div class="btns">
            <button class="primary" id="regBtn" type="submit" disabled>Register</button>
          </div>

          <small class="hint">
            You’ll be notified after admin approval. You can verify email later.
          </small>
        </form>
      </div>
    </div>

    <div class="footer">
      <div>© <?= date('Y') ?> CardGame</div>
      <div>
        <a href="<?= h($bp) ?>/admin/login.php">Admin Login</a>
      </div>
    </div>
  </div>

  <script src="<?= h($bp) ?>/assets/main.js"></script>
</body>
</html>