<?php
// dashboard.php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();
$u = current_user();
$bp = base_path();

// ranked requirements (for display)
$req = ranked_requirements($mysqli, $u);
$is_guest = ((int)($u['is_guest'] ?? 0) === 1);

ui_header("Dashboard");
?>
<div class="grid">
  <section class="card">
    <h2>Play</h2>
    <p class="sub">Jump straight in. Ranked is locked until requirements are met.</p>

    <div class="btns">
      <a class="btn btn-primary" href="<?= h($bp) ?>/play.php">Casual Match</a>

      <?php if ($is_guest): ?>
        <span class="btn" style="opacity:.6; cursor:not-allowed;">Ranked (Guest locked)</span>
      <?php else: ?>
        <?php if ($req['ranked_ok']): ?>
          <a class="btn btn-primary" href="<?= h($bp) ?>/play.php?mode=ranked">Ranked Match</a>
        <?php else: ?>
          <span class="btn" style="opacity:.6; cursor:not-allowed;">Ranked Locked</span>
        <?php endif; ?>
      <?php endif; ?>
    </div>

    <?php if (!$is_guest && !$req['ranked_ok']): ?>
      <div class="alert" style="margin-top:12px;">
        <b>To unlock ranked:</b>
        <div style="color:var(--muted); margin-top:6px; line-height:1.6;">
          Email verified: <?= $req['email_ok'] ? "✅" : "❌" ?><br/>
          2FA enabled: <?= $req['twofa_ok'] ? "✅" : "❌" ?><br/>
          Credits linked: <?= $req['bank_ok'] ? "✅" : "❌" ?>
        </div>
        <div style="margin-top:10px;">
          <a class="mini-btn" href="<?= h($bp) ?>/security.php">Fix in Security</a>
          <a class="mini-btn" href="<?= h($bp) ?>/credits.php">Fix in Credits</a>
        </div>
      </div>
    <?php endif; ?>
  </section>

  <section class="card">
    <h2>Rooms</h2>
    <p class="sub">Create or join a lobby (2–4 players). Private codes later.</p>
    <div class="btns">
      <a class="btn" href="<?= h($bp) ?>/rooms.php?action=create">Create Room</a>
      <a class="btn" href="<?= h($bp) ?>/rooms.php">Find Rooms</a>
    </div>
    <small class="hint">Guest accounts can play casual only.</small>
  </section>
</div>

<div class="grid" style="margin-top:18px;">
  <section class="card">
    <h2>Account</h2>
    <p class="sub">
      User: <b><?= h($u['username']) ?></b><br/>
      Email: <?= h($u['email']) ?><br/>
      Mode: <?= $is_guest ? "Guest" : "Player" ?><br/>
      Last login: <?= h($u['last_login_at'] ?? '—') ?>
    </p>
    <div class="btns">
      <a class="btn" href="<?= h($bp) ?>/security.php">Security</a>
      <?php if (!$is_guest): ?>
        <a class="btn" href="<?= h($bp) ?>/credits.php">Credits</a>
      <?php endif; ?>
    </div>
  </section>

  <section class="card">
    <h2>Quick status</h2>
    <p class="sub">This is your security readiness for ranked matchmaking.</p>
    <div class="alert">
      Ranked eligibility: <b><?= ($is_guest ? "Guest locked" : ($req['ranked_ok'] ? "✅ Eligible" : "🔒 Locked")) ?></b>
    </div>
    <small class="hint">Later: match history, rating, and quests live here.</small>
  </section>
</div>
<?php
ui_footer();