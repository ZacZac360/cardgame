<?php
session_start();
require_once __DIR__ . "/../includes/helpers.php";

$bp  = base_path();
$err = flash_get('err');
$msg = flash_get('msg');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia — Admin Login</title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
</head>
<body>
  <!-- Top Nav -->
  <header class="topnav">
    <div class="topnav__inner">
      <a class="logo" href="<?= h($bp) ?>/index.php">
        <span class="logo__mark">CG</span>
        <span class="logo__text">Logia Admin</span>
      </a>

      <div class="navactions">
        <a class="btn btn-ghost" href="<?= h($bp) ?>/index.php">Back to Home</a>
      </div>
    </div>
  </header>

  <!-- Flash banners -->
  <div class="container">
    <?php if ($err): ?>
      <div class="banner banner--bad"><?= h($err) ?></div>
    <?php elseif ($msg): ?>
      <div class="banner banner--good"><?= h($msg) ?></div>
    <?php endif; ?>
  </div>

  <!-- Admin Login -->
  <main class="container">
    <section class="hero admin-login-hero">
      <div class="hero__copy">
        <div class="chip">Restricted Access • Admin Portal</div>
        <h1>Admin sign in</h1>
        <p class="lead">
          Contact administrator for your account details.
        </p>
      </div>

      <div class="hero__panel">
        <div class="card-soft admin-login-card">
          <div class="admin-login-card__head">
            <div>
              <div class="pill">RBAC</div>
              <h2>Sign in</h2>
              <p class="lead admin-login-sub">
                Admins only. Pending or regular user accounts cannot log in here.
              </p>
            </div>
          </div>

          <form method="post" action="<?= h($bp) ?>/admin/auth_action.php" autocomplete="off">
            <label for="identifier">Email or Username</label>
            <input id="identifier" name="identifier" autocomplete="username" required />

            <label for="password">Password</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required />

            <div class="formrow">
              <button class="btn btn-primary" type="submit">Login</button>
              <a class="btn btn-ghost" href="<?= h($bp) ?>/index.php">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </section>
  </main>
</body>
</html>