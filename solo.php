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

<section class="section" style="padding-top:0;">
  <div class="hub-grid">

    <!-- LEFT -->
    <aside class="card hub-left" style="padding:14px; position:sticky; top:86px;">
      <div style="font-weight:950; letter-spacing:.02em; opacity:.9; margin-bottom:10px;">
        MENU
      </div>

      <div style="display:grid; gap:10px;">
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
          <div class="hub-item" style="opacity:.55; cursor:not-allowed;">
            <span class="hub-ico">🛒</span>
            <span>Shop (Login)</span>
          </div>
        <?php endif; ?>

        <a class="hub-item" href="<?= h($bp) ?>/profile.php?tab=overview">
          <span class="hub-ico">⚙️</span>
          <span>Options</span>
        </a>
      </div>

      <div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,.08);">
        <span class="pill"><?= $is_guest ? "Guest" : "Player" ?></span>

        <div style="margin-top:10px;">
          <span class="pill" style="border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10);">
            Campaign Path
          </span>
          <div style="margin-top:8px; color: var(--muted); font-size:13px; line-height:1.4;">
            Sharpen fundamentals and clear encounter tracks.
          </div>
        </div>
      </div>
    </aside>

    <!-- CENTER -->
    <main style="min-width:0;">
      <div class="card hub-hero">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; position:relative; z-index:1;">
          <span class="pill" style="border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10);">Solo Progress</span>
        </div>

        <div style="position:relative; z-index:1;">
          <h2>Campaign and tutorial track</h2>
          <p class="lead" style="margin:0; max-width:66ch;">
            Learn rules, master element matchups, and unlock tougher solo encounters over time.
          </p>

          <div class="hero-actions">
            <a class="btn btn-primary btn-lg" href="#chapters">Campaign</a>
            <a class="btn btn-ghost btn-lg" href="#training">Training Path</a>
          </div>

          <div style="margin-top:14px; display:flex; flex-wrap:wrap; gap:8px;">
            <span class="note">User: <b><?= h($username) ?></b></span>
            <span class="note">Mode: <b>Solo</b></span>
            <span class="note">Track: <b>Tutorial + Campaign</b></span>
          </div>
        </div>
      </div>

      <div style="margin-top:12px; display:grid; gap:12px;">
      </div>
    </main>

    <!-- RIGHT -->
    <aside class="hub-right">
      <div class="card" style="padding:16px; border-radius: calc(var(--radius) + 10px);">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
          <div>
            <div style="font-weight:950;">Current Path</div>
            <div style="color: var(--muted); font-size:13px; margin-top:4px;">
              Recommended next steps
            </div>
          </div>
        </div>

        <div style="margin-top:12px; display:grid; gap:10px;">
          <div class="card-soft" style="display:block; padding:12px;">
            <div style="font-weight:900;">📘 Recommended Next</div>
            <div style="color: var(--muted); font-size:13px; margin-top:4px;">
              Continue Foundations to finish the current mission set.
            </div>
          </div>

          <div class="card-soft" style="display:block; padding:12px;">
            <div style="font-weight:900;">🧠 Training Benefit</div>
            <div style="color: var(--muted); font-size:13px; margin-top:4px;">
              Great place to surface rules, AI, rewards, and unlock structure later.
            </div>
          </div>

          <div class="card-soft" style="display:block; padding:12px;">
            <div style="font-weight:900;">⚔️ Long-Term Goal</div>
            <div style="color: var(--muted); font-size:13px; margin-top:4px;">
              Build player confidence before pushing them into live and ranked modes.
            </div>
          </div>
        </div>
      </div>
    </aside>

  </div>
</section>

<?php ui_footer(); ?>