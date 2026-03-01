<?php
session_start();
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();
ui_header("Rooms");
?>
<div class="card">
  <h2>Rooms</h2>
  <p class="sub">Create/find lobbies will be implemented next.</p>
  <div class="alert">Placeholder page (UI first).</div>
</div>
<?php ui_footer(); ?>