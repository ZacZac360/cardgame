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
if ($is_guest) {
  flash_set('err', 'Guests do not have notifications.');
  header("Location: {$bp}/guest_dashboard.php");
  exit;
}

$uid = (int)($u['id'] ?? 0);
$tab = trim((string)($_GET['tab'] ?? 'all'));
if (!in_array($tab, ['all', 'unread'], true)) {
  $tab = 'all';
}

$readId  = (int)($_GET['read'] ?? 0);
$readAll = (int)($_GET['read_all'] ?? 0);
$next    = trim((string)($_GET['next'] ?? ''));

if ($readId > 0) {
  $stmt = $mysqli->prepare("
    UPDATE dashboard_notifications
    SET is_read = 1
    WHERE id = ? AND user_id = ?
    LIMIT 1
  ");
  $stmt->bind_param("ii", $readId, $uid);
  $stmt->execute();
  $stmt->close();

  if ($next !== '') {
    $dest = $next;
    if (!preg_match('~^https?://~i', $dest)) {
      $dest = $bp . '/' . ltrim($dest, '/');
    }
    header("Location: {$dest}");
    exit;
  }

  header("Location: {$bp}/notifications.php");
  exit;
}

if ($readAll === 1) {
  $stmt = $mysqli->prepare("
    UPDATE dashboard_notifications
    SET is_read = 1
    WHERE user_id = ? AND is_read = 0
  ");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $stmt->close();

  flash_set('msg', 'All notifications marked as read.');
  header("Location: {$bp}/notifications.php");
  exit;
}

function fetch_all_notifications_for_user(mysqli $mysqli, int $userId, string $tab = 'all', int $limit = 50): array {
  if ($tab === 'unread') {
    $stmt = $mysqli->prepare("
      SELECT id, type, title, body, link_url, is_read, created_at
      FROM dashboard_notifications
      WHERE user_id = ? AND is_read = 0
      ORDER BY created_at DESC
      LIMIT ?
    ");
    $stmt->bind_param("ii", $userId, $limit);
  } else {
    $stmt = $mysqli->prepare("
      SELECT id, type, title, body, link_url, is_read, created_at
      FROM dashboard_notifications
      WHERE user_id = ?
      ORDER BY created_at DESC
      LIMIT ?
    ");
    $stmt->bind_param("ii", $userId, $limit);
  }

  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  return $rows;
}

$notes       = fetch_all_notifications_for_user($mysqli, $uid, $tab, 60);
$unread      = count_unread_notifications($mysqli, $uid);
$totalShown  = count($notes);
$req         = ranked_requirements($mysqli, $u);
$ranked_ok   = !empty($req['ranked_ok']);

ui_header("Notifications");
?>

<section class="section notif-page">
  <div class="hub-grid">

    <aside class="card hub-left notif-sidebar">
      <div class="notif-sidebar__title">MENU</div>

      <div class="notif-sidebar__nav">
        <a class="hub-item" href="<?= h($bp) ?>/dashboard.php">
          <span class="hub-ico">🏠</span>
          <span>Dashboard</span>
        </a>

        <a class="hub-item is-active" href="<?= h($bp) ?>/notifications.php">
          <span class="hub-ico">🔔</span>
          <span>Notifications</span>
        </a>

        <a class="hub-item" href="<?= h($bp) ?>/friends.php">
          <span class="hub-ico">👥</span>
          <span>Friends</span>
        </a>

        <a class="hub-item" href="<?= h($bp) ?>/profile.php?tab=overview">
          <span class="hub-ico">⚙️</span>
          <span>Options</span>
        </a>
      </div>

      <div class="notif-sidebar__status">
        <span class="pill">Player</span>

        <?php if (!$ranked_ok): ?>
          <div class="notif-sidebar__rank">
            <span class="pill notif-pill-danger">Ranked Locked</span>
            <div class="notif-sidebar__hint">
              Finish security steps to unlock Ranked.
            </div>
          </div>
        <?php else: ?>
          <div class="notif-sidebar__rank">
            <span class="pill notif-pill-good">Ranked Unlocked</span>
          </div>
        <?php endif; ?>
      </div>
    </aside>

    <main class="notif-main">
      <div class="card hub-hero notif-hero">
        <div class="notif-hero__top">
          <span class="pill">Notification Center</span>
          <?php if ($unread > 0): ?>
            <span class="pill notif-pill-good"><?= (int)$unread ?> unread</span>
          <?php else: ?>
            <span class="pill">All caught up</span>
          <?php endif; ?>
        </div>

        <div class="notif-hero__content">
          <h2>Your Notifications</h2>
          <p class="lead">
            Review platform updates, friend activity, match results, and direct message alerts in one place.
          </p>

          <div class="hero-actions">
            <a class="btn btn-primary btn-lg" href="<?= h($bp) ?>/notifications.php?tab=unread">Unread only</a>
            <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/notifications.php">View all</a>
            <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/notifications.php?read_all=1">Mark all read</a>
          </div>

          <div class="notif-hero__stats">
            <span class="note">Unread: <b><?= (int)$unread ?></b></span>
            <span class="note">Showing: <b><?= (int)$totalShown ?></b></span>
            <span class="note">Filter: <b><?= h(ucfirst($tab)) ?></b></span>
          </div>
        </div>
      </div>

      <div class="card notif-panel">
        <div class="notif-panel__head">
          <div>
            <div class="notif-panel__title">Activity Feed</div>
            <div class="notif-panel__sub">Recent alerts, requests, and system updates</div>
          </div>

          <div class="notif-tabs">
            <a class="pill notif-tab<?= $tab === 'all' ? ' is-active' : '' ?>" href="<?= h($bp) ?>/notifications.php">All</a>
            <a class="pill notif-tab<?= $tab === 'unread' ? ' is-active' : '' ?>" href="<?= h($bp) ?>/notifications.php?tab=unread">Unread</a>
          </div>
        </div>

        <div class="notif-list">
          <?php if (!$notes): ?>
            <div class="card-soft notif-empty">
              <div class="notif-empty__title">No notifications here.</div>
              <div class="notif-empty__meta">
                <?= $tab === 'unread'
                  ? 'You do not have any unread notifications right now.'
                  : 'Your notification feed is empty for now.' ?>
              </div>
            </div>
          <?php else: ?>
            <?php foreach ($notes as $n): ?>
              <?php
                $icon = notif_icon((string)$n['type']);
                $when = !empty($n['created_at']) ? date("M d • g:i A", strtotime((string)$n['created_at'])) : '';
                $link = trim((string)($n['link_url'] ?? ''));
                $href = $bp . "/notifications.php?read=" . (int)$n['id'];

                if ($link !== '') {
                  $href .= "&next=" . rawurlencode($link);
                }

                $isUnread = ((int)($n['is_read'] ?? 0) === 0);
              ?>
              <article class="card-soft notif-row<?= $isUnread ? ' is-unread' : '' ?>">
                <div class="notif-row__icon">
                  <?= $icon ?>
                </div>

                <div class="notif-row__content">
                  <div class="notif-row__top">
                    <div>
                      <div class="notif-row__title"><?= h((string)$n['title']) ?></div>
                      <?php if (!empty($n['body'])): ?>
                        <div class="notif-row__body"><?= h((string)$n['body']) ?></div>
                      <?php endif; ?>
                    </div>

                    <?php if ($isUnread): ?>
                      <span class="pill notif-pill-good">NEW</span>
                    <?php else: ?>
                      <span class="pill notif-pill-soft">Read</span>
                    <?php endif; ?>
                  </div>

                  <div class="notif-row__meta">
                    <span><?= h($when) ?></span>
                    <span class="notif-row__dot">•</span>
                    <span><?= h((string)ucwords(str_replace('_', ' ', (string)$n['type']))) ?></span>
                  </div>

                  <div class="notif-row__actions">
                    <?php if ($link !== ''): ?>
                      <a class="btn btn-primary" href="<?= h($href) ?>">Open</a>
                    <?php endif; ?>

                    <?php if ($isUnread): ?>
                      <a class="btn btn-ghost" href="<?= h($bp) ?>/notifications.php?read=<?= (int)$n['id'] ?>">Mark read</a>
                    <?php endif; ?>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </main>

    <aside class="hub-right notif-right">
      <div class="card notif-sidepanel">
        <div class="notif-sidepanel__head">
          <div>
            <div class="notif-sidepanel__title">Summary</div>
            <div class="notif-sidepanel__sub">Quick overview of your feed</div>
          </div>
        </div>

        <div class="notif-summary">
          <div class="card-soft notif-summary__item">
            <div class="notif-summary__label">Unread</div>
            <div class="notif-summary__value"><?= (int)$unread ?></div>
          </div>

          <div class="card-soft notif-summary__item">
            <div class="notif-summary__label">Visible</div>
            <div class="notif-summary__value"><?= (int)$totalShown ?></div>
          </div>

          <div class="card-soft notif-summary__item">
            <div class="notif-summary__label">Current Filter</div>
            <div class="notif-summary__value notif-summary__value--small"><?= h(ucfirst($tab)) ?></div>
          </div>
        </div>

        <div class="notif-minihead">Shortcuts</div>
        <div class="notif-links">
          <a class="card-soft notif-link" href="<?= h($bp) ?>/friends.php">
            <div class="notif-link__title">Friend Requests</div>
            <div class="notif-link__meta">Review incoming requests and connections.</div>
          </a>

          <a class="card-soft notif-link" href="<?= h($bp) ?>/profile.php?tab=security">
            <div class="notif-link__title">Security</div>
            <div class="notif-link__meta">Verify requirements for ranked access.</div>
          </a>

          <a class="card-soft notif-link" href="<?= h($bp) ?>/dashboard.php">
            <div class="notif-link__title">Back to Dashboard</div>
            <div class="notif-link__meta">Return to your main player hub.</div>
          </a>
        </div>
      </div>
    </aside>

  </div>
</section>

<?php ui_footer(); ?>