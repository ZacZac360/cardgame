<?php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();

$u  = current_user();
$bp = base_path();

$is_guest = ((int)($u['is_guest'] ?? 0) === 1);
if (!$is_guest) {
  header("Location: {$bp}/dashboard.php");
  exit;
}

ui_header("Guest", true);
?>

<section class="section" style="padding-top:0;">
  <div class="container" style="max-width: 980px;">
    <div class="card hub-hero" style="padding:24px;">
      <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap; position:relative; z-index:1;">
        <span class="pill" style="border-color: rgba(255,205,102,.45); background: rgba(255,205,102,.10);">Guest</span>
        <a class="btn btn-ghost" href="<?= h($bp) ?>/index.php">Sign In / Register</a>
      </div>

      <div style="position:relative; z-index:1; margin-top:10px;">
        <h2 style="margin-bottom:8px;">Enter Match</h2>
        <p class="lead" style="margin:0; max-width:52ch;">
          Quick queue or private room.
        </p>
      </div>
    </div>

    <div style="margin-top:14px; display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
      <div class="card" style="padding:18px;">
        <div style="font-weight:950; font-size:20px; margin-bottom:10px;">Random Match</div>
        <div style="color:var(--muted); margin-bottom:14px;">Casual queue</div>
        <a class="btn btn-primary btn-lg" href="<?= h($bp) ?>/play.php?mode=guest-random">Find Match</a>
      </div>

      <div class="card" style="padding:18px;">
        <div style="font-weight:950; font-size:20px; margin-bottom:10px;">Join Room</div>

        <form method="get" action="<?= h($bp) ?>/rooms.php" style="display:grid; gap:10px;">
          <input class="input" type="text" name="room_code" placeholder="Room ID" required>
          <input class="input" type="text" name="room_password" placeholder="Password (if needed)">
          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <button class="btn btn-primary btn-lg" type="submit">Join</button>
          </div>
        </form>
      </div>
    </div>

    <div style="margin-top:14px;">
      <div class="card" style="padding:16px;">
        <div style="display:flex; flex-wrap:wrap; gap:8px;">
          <span class="pill">Guest</span>
          <span class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">Ranked Locked</span>
          <span class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">Shop Locked</span>
          <span class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">Profile Locked</span>
        </div>
      </div>
    </div>
  </div>
</section>

<?php ui_footer(); ?>