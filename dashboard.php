<?php
// dashboard.php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();
$u  = current_user();
$bp = base_path();

if ($u && user_has_role($u, 'admin')) {
  header("Location: {$bp}/admin/index.php");
  exit;
}

$is_guest  = ((int)($u['is_guest'] ?? 0) === 1);
if ($is_guest) {
  header("Location: {$bp}/guest_dashboard.php");
  exit;
}
$req = ranked_requirements($mysqli, $u);
$ranked_unlocked = (!$is_guest && !empty($req['ranked_unlocked']));
$ranked_ok = (!$is_guest && !empty($req['ranked_ok']));

$notes  = fetch_notifications($mysqli, (int)$u['id'], 6);
$unread = count_unread_notifications($mysqli, (int)$u['id']);

ui_header("Dashboard");
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

        <a class="hub-item" href="<?= h($bp) ?>/solo.php">
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

        <?php if (!$is_guest && !$ranked_unlocked): ?>
          <div class="hub-sidebar__status-block">
            <span class="pill status-pill--bad">Ranked Locked</span>
            <div class="hub-sidebar__hint">
              Finish account checks and reach <?= (int)$req['unlock_threshold'] ?> Zeny to unlock Ranked.
            </div>
          </div>
        <?php elseif (!$is_guest && !$ranked_ok): ?>
          <div class="hub-sidebar__status-block">
            <span class="pill status-pill--warn">Ranked Unlocked</span>
            <div class="hub-sidebar__hint">
              Need <?= (int)$req['entry_fee'] ?> Zeny to queue right now.
            </div>
          </div>
        <?php elseif (!$is_guest && $ranked_ok): ?>
          <div class="hub-sidebar__status-block">
            <span class="pill status-pill--good">Ranked Ready</span>
          </div>
        <?php endif; ?>
      </div>
    </aside>

    <!-- CENTER -->
    <main class="page-main">
      <div class="card hub-hero">
        <div class="hero-top">
          <?php if ($is_guest): ?>
            <span class="pill status-pill--warn">Guest: Casual only</span>
          <?php elseif ($ranked_ok): ?>
            <span class="pill status-pill--good">Ranked Ready</span>
          <?php elseif ($ranked_unlocked): ?>
            <span class="pill status-pill--warn">Need <?= (int)$req['entry_fee'] ?> Zeny</span>
          <?php else: ?>
            <span class="pill status-pill--bad">Ranked Locked</span>
          <?php endif; ?>
        </div>

        <div class="hero-body">
          <h2>Weekend Event: “Clash Circuit”</h2>
          <p class="lead hero-lead">
            Featured tournaments, rotating modes, and limited rewards.
          </p>

          <div class="hero-actions">
            <a class="btn btn-primary btn-lg" href="<?= h($bp) ?>/play.php">Play</a>
            <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/room.php">Rooms</a>
            <?php if (!$is_guest): ?>
              <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/shop.php">Shop</a>
            <?php endif; ?>
          </div>

          <div class="hero-meta">
            <span class="note">User: <b><?= h($u['username']) ?></b></span>
            <span class="note">Unread: <b><?= (int)$unread ?></b></span>
            <span class="note">Mode: <b><?= $is_guest ? "Guest" : "Player" ?></b></span>
          </div>
        </div>
      </div>

      <div class="stack-12">

        <div class="card onboarding-card">
          <div class="onboarding-card__head">
            <div>
              <div class="onboarding-card__eyebrow">START HERE</div>
              <div class="onboarding-card__title">New to Logia?</div>
              <div class="onboarding-card__text">
                Follow this beginner path: learn the rules, play a casual match, then prepare your account for Ranked.
              </div>
            </div>

            <button class="btn btn-ghost" type="button" data-guide-open="getting-started">
              Open Guide
            </button>
          </div>

          <div class="onboarding-checklist">
            <div class="onboarding-check">
              <div class="onboarding-check__copy">
                <strong>1. Complete Solo Training</strong>
                <span>Learn matching, stronger elements, and special cards before live matches.</span>
              </div>
              <div class="onboarding-check__status">Recommended</div>
            </div>

            <div class="onboarding-check">
              <div class="onboarding-check__copy">
                <strong>2. Play Casual</strong>
                <span>Use Quick Match or Rooms to practice without ranked pressure.</span>
              </div>
              <div class="onboarding-check__status">Practice</div>
            </div>

            <div class="onboarding-check">
              <div class="onboarding-check__copy">
                <strong>3. Prepare Ranked</strong>
                <span>Finish account checks, keep enough Zeny, then choose a ranked league.</span>
              </div>
              <div class="onboarding-check__status">
                <?= $ranked_unlocked ? 'Unlocked' : 'Locked' ?>
              </div>
            </div>
          </div>

          <div class="onboarding-actions">
            <a class="btn btn-primary" href="<?= h($bp) ?>/solo.php">Start Solo Training</a>
            <a class="btn btn-ghost" href="<?= h($bp) ?>/play.php">Open Play Menu</a>
            <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=security">Account Setup</a>
          </div>
        </div>

        <!-- Topics -->
        <div class="card panel-card">
          <div class="panel-head">
            <div>
              <div class="panel-title">Topics</div>
              <div class="panel-sub">
                Rotating announcements
              </div>
            </div>
            <div class="pill-row">
              <span class="pill pill-page-dot">1</span>
              <span class="pill pill-page-dot is-dim">2</span>
              <span class="pill pill-page-dot is-dim">3</span>
            </div>
          </div>

          <div class="stack-10 hub-mt-12">
            <button class="card-soft link-card coming-soon-card" type="button" data-coming-soon="Tournament Live">
              <div class="text-strong">🏆 Tournament Live</div>
              <div class="panel-sub">
                Join brackets, earn rewards, climb the event ladder.
              </div>
            </button>

            <button class="card-soft link-card coming-soon-card" type="button" data-coming-soon="Patch Notes">
              <div class="text-strong">🧩 Patch Notes</div>
              <div class="panel-sub">
                Balance tweaks + UI updates.
              </div>
            </button>

            <button class="card-soft link-card coming-soon-card" type="button" data-coming-soon="Cosmetics Drop">
              <div class="text-strong">✨ Cosmetics Drop</div>
              <div class="panel-sub">
                New mats, frames, and skins in the shop.
              </div>
            </button>
          </div>
        </div>
      </div>
    </main>

    <!-- RIGHT -->
    <aside class="hub-right">
      <div class="card panel-card--lg">
        <div class="panel-head-simple">
          <div>
            <div class="panel-title">Activity</div>
            <div class="panel-sub">
              Latest notifications & platform events
            </div>
          </div>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/notifications.php">View all</a>
        </div>

        <div class="stack-10 hub-mt-12">
          <?php if (!$notes): ?>
            <div class="empty-state">
              No notifications yet.
            </div>
          <?php else: ?>
            <?php foreach ($notes as $n): ?>
              <?php
                $icon = notif_icon((string)$n['type']);
                $when = $n['created_at'] ? date("M d • g:i A", strtotime((string)$n['created_at'])) : '';
                $link = trim((string)($n['link_url'] ?? ''));

                /* fix old stored notification links */
                if ($link !== '') {
                  $link = preg_replace('~^/cardgame/~', '/', $link);

                  if (preg_match('~^/messages\.php(\?.*)?$~i', $link, $m)) {
                    $qs = $m[1] ?? '';
                    $link = '/friends.php' . $qs;
                  }
                }

                if ($link === '') {
                  $href = $bp . "/notifications.php";
                } elseif (preg_match('~^https?://~i', $link)) {
                  $href = $link;
                } elseif (strpos($link, '/') === 0) {
                  $href = $bp . $link;
                } else {
                  $href = $bp . '/' . ltrim($link, '/');
                }
              ?>
            <a href="<?= h($href) ?>" class="card-soft activity-link">
              <div class="activity-row">
                <div class="activity-copy">
                  <?= $icon ?>
                </div>

                <div class="activity-main">
                  <div class="activity-kicker">
                    <?= h((string)($n['title'] ?? 'Notification')) ?>
                  </div>

                  <?php if (!empty($n['body'])): ?>
                    <div class="activity-body">
                      <?= h((string)$n['body']) ?>
                    </div>
                  <?php endif; ?>

                  <div class="activity-meta">
                    <?= h($when) ?>
                  </div>
                </div>
              </div>
            </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if (!$is_guest && !$ranked_ok): ?>
          <div class="requirements-card">
            <div class="pill status-pill--bad">
              Ranked requirements
            </div>

            <div class="requirements-list">
              <div class="requirements-row"><span>Account approved</span><span><?= $req['approved_ok'] ? "✅" : "❌" ?></span></div>
              <div class="requirements-row"><span>Email verified</span><span><?= $req['email_ok'] ? "✅" : "❌" ?></span></div>
              <div class="requirements-row"><span>2FA enabled</span><span><?= $req['twofa_ok'] ? "✅" : "❌" ?></span></div>
              <div class="requirements-row"><span>Minimum Zeny (<?= (int)$req['unlock_threshold'] ?>)</span><span><?= $req['credits_ok'] ? "✅" : "❌" ?></span></div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </aside>

  </div>
</section>

<div class="coming-soon-modal" id="comingSoonModal" aria-hidden="true">
  <div class="coming-soon-modal__backdrop" data-coming-soon-close></div>

  <div class="coming-soon-modal__dialog" role="dialog" aria-modal="true" aria-label="Coming Soon">
    <button class="coming-soon-modal__close" type="button" data-coming-soon-close aria-label="Close">×</button>

    <div class="coming-soon-modal__eyebrow">Coming Soon</div>
    <h2 id="comingSoonTitle">Feature Coming Soon</h2>
    <p>
      This section is already planned, but it is not available in the current deployed version yet.
      For now, this button will stay as a preview instead of opening a missing page.
    </p>

    <div class="coming-soon-modal__actions">
      <button class="btn btn-primary" type="button" data-coming-soon-close>Okay</button>
    </div>
  </div>
</div>

<?php ui_footer(); ?>