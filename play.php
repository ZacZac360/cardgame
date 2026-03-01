<?php
session_start();
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();
ui_header("Play");
?>
<div class="card">
  <h2>Play</h2>
  <p class="sub">Casual/Ranked matchmaking will be wired after rooms + server validation.</p>
  <div class="alert">Placeholder page (UI first).</div>
</div>
<?php ui_footer(); ?>