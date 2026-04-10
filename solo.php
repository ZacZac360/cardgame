<?php
// solo.php
session_start();

require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();

$bp = base_path();
$u  = current_user();

$is_guest = ((int)($u['is_guest'] ?? 0) === 1);
$username = $u['username'] ?? $u['display_name'] ?? 'Player';

ui_header("Solo");
?>

<section class="section section--flush-top">
  <div class="hub-grid">

    <!-- LEFT -->
    <aside class="card hub-left hub-sidebar">
      <div class="hub-sidebar__title">
        MENU
      </div>

      <div class="hub-sidebar__nav">
        <a class="hub-item" href="<?= h($bp) ?>/play.php">
          <span class="hub-ico">🎮</span>
          <span>Play</span>
        </a>

        <a class="hub-item is-active" href="<?= h($bp) ?>/solo.php">
          <span class="hub-ico">🧪</span>
          <span>Solo</span>
        </a>

        <?php if (!$is_guest): ?>
          <a class="hub-item" href="<?= h($bp) ?>/shop.php">
            <span class="hub-ico">🛒</span>
            <span>Shop</span>
          </a>
        <?php else: ?>
          <div class="hub-item hub-sidebar__login-lock">
            <span class="hub-ico">🛒</span>
            <span>Shop (Login)</span>
          </div>
        <?php endif; ?>

        <a class="hub-item" href="<?= h($bp) ?>/profile.php?tab=overview">
          <span class="hub-ico">⚙️</span>
          <span>Options</span>
        </a>
      </div>

      <div class="hub-sidebar__status">
        <span class="pill"><?= $is_guest ? "Guest" : "Player" ?></span>

        <div class="hub-sidebar__status-block">
          <span class="pill status-pill--good">
            Campaign Path
          </span>
          <div class="hub-sidebar__hint">
            Sharpen fundamentals and clear encounter tracks.
          </div>
        </div>
      </div>
    </aside>

    <!-- CENTER -->
    <main class="page-main">
      <div class="card hub-hero">
        <div class="hero-top">
          <span class="pill status-pill--good">Solo Progress</span>
        </div>

        <div class="hero-body">
          <h2>Campaign and tutorial track</h2>
          <p class="lead hero-lead hero-lead--wide">
            Learn rules, master element matchups, and unlock tougher solo encounters over time.
          </p>

          <div class="hero-actions">
            <a class="btn btn-primary btn-lg" href="#chapters">Campaign</a>
            <a class="btn btn-ghost btn-lg" href="#training">Training Path</a>
          </div>

          <div class="hero-meta">
            <span class="note">User: <b><?= h($username) ?></b></span>
            <span class="note">Mode: <b>Solo</b></span>
            <span class="note">Track: <b>Tutorial + Campaign</b></span>
          </div>
        </div>
      </div>

      <div class="stack-12">
      </div>
    </main>

    <!-- RIGHT -->
    <aside class="hub-right">
      <div class="card panel-card--lg">
        <div class="panel-head-simple">
          <div>
            <div class="panel-title">Current Path</div>
            <div class="panel-sub">
              Recommended next steps
            </div>
          </div>
        </div>

        <div class="stack-10 hub-mt-12">
          <div class="card-soft link-card link-card--block">
            <div class="text-strong">📘 Recommended Next</div>
            <div class="panel-sub">
              Continue Foundations to finish the current mission set.
            </div>
          </div>

          <div class="card-soft link-card link-card--block">
            <div class="text-strong">🧠 Training Benefit</div>
            <div class="panel-sub">
              Great place to surface rules, AI, rewards, and unlock structure later.
            </div>
          </div>

          <div class="card-soft link-card link-card--block">
            <div class="text-strong">⚔️ Long-Term Goal</div>
            <div class="panel-sub">
              Build player confidence before pushing them into live and ranked modes.
            </div>
          </div>
        </div>
      </div>
    </aside>

  </div>
</section>

<?php ui_footer(); ?>