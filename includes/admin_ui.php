<?php
// includes/admin_ui.php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// simple icon map for admin notification types
function admin_notif_icon(string $type): string {
  return match ($type) {
    'admin_approval'  => '🛡️',
    'security_alert'  => '🔐',
    'card_change'     => '🃏',
    'penalty'         => '⚠️',
    default           => '🔔',
  };
}

function admin_fetch_notifications(mysqli $mysqli, int $user_id, int $limit = 8): array {
  $stmt = $mysqli->prepare("
    SELECT id, type, title, body, link_url, is_read, created_at
    FROM dashboard_notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT ?
  ");
  $stmt->bind_param("ii", $user_id, $limit);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $rows;
}

function admin_count_unread(mysqli $mysqli, int $user_id): int {
  $stmt = $mysqli->prepare("SELECT COUNT(*) c FROM dashboard_notifications WHERE user_id = ? AND is_read = 0");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $c = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
  $stmt->close();
  return $c;
}

// quick admin KPIs (real queries)
function admin_kpis(mysqli $mysqli): array {
  $pending = (int)($mysqli->query("SELECT COUNT(*) c FROM users WHERE approval_status='pending'")->fetch_assoc()['c'] ?? 0);
  $fails24 = (int)($mysqli->query("SELECT COUNT(*) c FROM login_attempts WHERE success=0 AND created_at >= (NOW() - INTERVAL 1 DAY)")->fetch_assoc()['c'] ?? 0);
  $activeSessions = (int)($mysqli->query("
    SELECT COUNT(*) c
    FROM auth_sessions
    WHERE revoked_at IS NULL
      AND expires_at > NOW()
  ")->fetch_assoc()['c'] ?? 0);
  $banned = (int)($mysqli->query("SELECT COUNT(*) c FROM users WHERE banned_until IS NOT NULL AND banned_until > NOW()")->fetch_assoc()['c'] ?? 0);

  return compact('pending','fails24','activeSessions','banned');
}

function admin_ui_header(string $title = 'Admin'): void {
  global $mysqli;

  // RBAC
  require_role('admin');

  $bp = base_path();
  $u = current_user();
  if (!$u) redirect($bp . "/index.php");

  $uid = (int)$u['id'];
  $unread = admin_count_unread($mysqli, $uid);
  $notes = admin_fetch_notifications($mysqli, $uid, 8);

  ?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= h($title) ?> — Admin • Logia</title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
</head>
<body>

<header class="app-topbar">
  <div class="app-topbar__inner">

    <a class="brand" href="<?= h($bp) ?>/admin/index.php">
      <span class="brand__logo">🛡️</span>
      <span class="brand__name">Admin Console</span>
      <span class="pill">RBAC</span>
      <span class="pill pill-warn">Security</span>
    </a>

    <nav class="nav">
      <a class="nav__link" href="<?= h($bp) ?>/admin/index.php">Overview</a>
      <a class="nav__link" href="<?= h($bp) ?>/admin/pending-users.php">Pending</a>
      <a class="nav__link" href="<?= h($bp) ?>/admin/audit.php">Audit Logs</a>
      <a class="nav__link" href="<?= h($bp) ?>/admin/security.php">Security</a>
      <a class="nav__link" href="<?= h($bp) ?>/admin/users.php">Users</a>
      <a class="nav__link" href="<?= h($bp) ?>/admin/game-content.php">Content</a>
    </nav>

    <div class="actions">
      <a class="btn btn-primary" href="<?= h($bp) ?>/admin/pending-users.php">
        Review Pending
      </a>

      <button class="icon-btn" id="notifBtn" type="button" aria-haspopup="true" aria-expanded="false" title="Notifications">
        🔔
        <?php if ($unread > 0): ?>
          <span class="dot"><?= (int)$unread ?></span>
        <?php endif; ?>
      </button>

      <div class="userchip" id="userChip">
        <div class="avatar"><?= h(strtoupper(substr((string)$u['username'], 0, 1))) ?></div>
        <div class="userchip__meta">
          <div class="userchip__name"><?= h($u['username']) ?></div>
          <div class="userchip__sub">Admin</div>
        </div>
        <button class="icon-btn" id="userMenuBtn" type="button" aria-haspopup="true" aria-expanded="false" title="Menu">▾</button>

        <div class="dropdown" id="userMenu" role="menu">
          <a class="dropdown__item" href="<?= h($bp) ?>/dashboard.php">User View</a>
          <div class="dropdown__sep"></div>
          <a class="dropdown__item danger" href="<?= h($bp) ?>/logout.php">Logout</a>
        </div>
      </div>

      <div class="dropdown dropdown--wide" id="notifMenu" aria-label="Notifications">
        <div class="dropdown__head">
          <div>
            <div class="dropdown__title">Admin Notifications</div>
            <div class="dropdown__sub"><?= $unread ?> unread</div>
          </div>
          <div class="dropdown__headActions">
            <button class="mini-btn" id="markAllReadBtn" type="button">Mark all read</button>
            <a class="mini-btn" href="<?= h($bp) ?>/notifications.php">View all</a>
          </div>
        </div>

        <div class="dropdown__list">
          <?php if (!$notes): ?>
            <div class="empty">No notifications yet.</div>
          <?php else: ?>
            <?php foreach ($notes as $n): ?>
              <?php
                $icon = admin_notif_icon((string)$n['type']);
                $is_read = ((int)$n['is_read'] === 1);
                $when = $n['created_at'] ? date("M d • g:i A", strtotime((string)$n['created_at'])) : '';
                $link = (string)($n['link_url'] ?? '');
                $href = $link ? ($bp . $link) : '#';
              ?>
              <a class="notif <?= $is_read ? 'read' : 'unread' ?>"
                 href="<?= h($href) ?>"
                 data-notif-id="<?= (int)$n['id'] ?>"
                 data-has-link="<?= $link ? '1' : '0' ?>">
                <div class="notif__icon"><?= $icon ?></div>
                <div class="notif__body">
                  <div class="notif__title"><?= h($n['title']) ?></div>
                  <?php if (!empty($n['body'])): ?>
                    <div class="notif__text"><?= h($n['body']) ?></div>
                  <?php endif; ?>
                  <div class="notif__time"><?= h($when) ?></div>
                </div>
                <?php if (!$is_read): ?><div class="notif__pill">NEW</div><?php endif; ?>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </div>
</header>

<main class="app-shell">
<?php
}

function admin_ui_footer(): void {
  $bp = base_path();
  ?>
</main>

<footer class="app-footer">
  <div class="app-footer__inner">
    <div class="muted">© <?= date('Y') ?> Logia • Admin Console</div>
    <div class="muted">Ops: Approval • RBAC • Audit Logs • Attempts • Sessions</div>
    <div class="muted"><a href="<?= h($bp) ?>/admin/index.php">Admin Home</a></div>
  </div>
</footer>

<script src="<?= h($bp) ?>/assets/main.js"></script>
</body>
</html>
<?php
}