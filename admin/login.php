<?php
session_start();
require_once __DIR__ . "/../includes/helpers.php";
$bp = base_path();
$err = flash_get('err');
$msg = flash_get('msg');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin Login</title>
  <link rel="stylesheet" href="<?= h(base_path()) ?>/assets/style.css"/>
</head>
<body>
  <div class="wrap">
    <div class="topbar">
      <div class="brand">Admin Login <span class="badge">RBAC</span></div>
      <div><a href="<?= h($bp) ?>/index.php">Back to Home</a></div>
    </div>

    <?php if ($err): ?><div class="alert bad"><?= h($err) ?></div><?php endif; ?>
    <?php if ($msg): ?><div class="alert good"><?= h($msg) ?></div><?php endif; ?>

    <div class="card" style="max-width:520px;">
      <h2>Sign in</h2>
      <p class="sub">Admins only. Pending users cannot log in.</p>

      <form method="post" action="<?= h($bp) ?>/admin/auth_action.php">
        <label>Email or Username</label>
        <input name="identifier" required />

        <label>Password</label>
        <input name="password" type="password" required />

        <div class="btns">
          <button class="primary" type="submit">Login</button>
        </div>
      </form>
    </div>
  </div>
</body>
</html>