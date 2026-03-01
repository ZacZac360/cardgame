<?php
session_start();
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();
ui_header("Security");
?>
<div class="card">
  <h2>Security</h2>
  <p class="sub">Email verification + 2FA enablement UI goes here (stub first, then logic).</p>
  <div class="alert">Placeholder page (UI first).</div>
</div>
<?php ui_footer(); ?>