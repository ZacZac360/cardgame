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
            <a class="card-soft link-card" href="<?= h($bp) ?>/events.php">
              <div class="text-strong">🏆 Tournament Live</div>
              <div class="panel-sub">
                Join brackets, earn rewards, climb the event ladder.
              </div>
            </a>

            <a class="card-soft link-card" href="<?= h($bp) ?>/events.php">
              <div class="text-strong">🧩 Patch Notes</div>
              <div class="panel-sub">
                Balance tweaks + UI updates.
              </div>
            </a>

            <a class="card-soft link-card" href="<?= h($bp) ?>/events.php">
              <div class="text-strong">✨ Cosmetics Drop</div>
              <div class="panel-sub">
                New mats, frames, and skins in the shop.
              </div>
            </a>
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

<?php ui_footer(); ?>