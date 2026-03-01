<?php
session_start();
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();
$u = current_user();
if ((int)($u['is_guest'] ?? 0) === 1) {
  redirect(base_path() . "/dashboard.php");
}

ui_header("Credits");
?>
<div class="card">
  <h2>Credits</h2>
  <p class="sub">Payment/credits linking skeleton goes here.</p>
  <div class="alert">Placeholder page (UI first).</div>
</div>
<?php ui_footer(); ?>