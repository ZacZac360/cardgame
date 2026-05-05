<?php
// guest_exit.php
session_start();

require_once __DIR__ . "/includes/helpers.php";

$bp = base_path();

$to = strtolower(trim((string)($_GET['to'] ?? 'register')));
if (!in_array($to, ['register', 'login'], true)) {
  $to = 'register';
}

$post = $_POST;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$_SESSION = [];

if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();

  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
  );
}

session_destroy();

if ($method === 'POST') {
  ?>
  <!doctype html>
  <html>
  <head>
    <meta charset="utf-8">
    <title>Continuing...</title>
  </head>
  <body>
    <form id="forwardAuth" method="post" action="<?= htmlspecialchars($bp, ENT_QUOTES, 'UTF-8') ?>/auth_action.php?next=<?= urlencode($bp . '/dashboard.php') ?>">
      <input type="hidden" name="action" value="<?= htmlspecialchars($to, ENT_QUOTES, 'UTF-8') ?>">
      <?php foreach ($post as $key => $value): ?>
        <?php if (is_array($value)) continue; ?>
        <?php if ($key === 'action') continue; ?>
        <input
          type="hidden"
          name="<?= htmlspecialchars((string)$key, ENT_QUOTES, 'UTF-8') ?>"
          value="<?= htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') ?>"
        >
      <?php endforeach; ?>
    </form>

    <script>
      document.getElementById("forwardAuth").submit();
    </script>

    <noscript>
      <button form="forwardAuth" type="submit">Continue</button>
    </noscript>
  </body>
  </html>
  <?php
  exit;
}

header("Location: {$bp}/index.php?auth={$to}");
exit;