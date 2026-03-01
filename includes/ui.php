<?php
// includes/ui.php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function notif_icon(string $type): string {
  return match ($type) {
    'admin_approval'  => '🛡️',
    'security_alert'  => '🔐',
    'credit_update'   => '💳',
    'match_result'    => '🏁',
    'ranked_unlock'   => '🏆',
    default           => '🔔',
  };
}

function fetch_notifications(mysqli $mysqli, int $user_id, int $limit = 8): array {
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

function count_unread_notifications(mysqli $mysqli, int $user_id): int {
  $stmt = $mysqli->prepare("
    SELECT COUNT(*) c
    FROM dashboard_notifications
    WHERE user_id = ? AND is_read = 0
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $c = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
  $stmt->close();
  return $c;
}

function ranked_requirements(mysqli $mysqli, array $u): array {
  // 2FA
  $uid = (int)$u['id'];
  $stmt = $mysqli->prepare("SELECT is_enabled FROM two_factor_secrets WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $twofa = (int)($stmt->get_result()->fetch_assoc()['is_enabled'] ?? 0);
  $stmt->close();

  $email_ok = !empty($u['email_verified_at']);
  $bank_ok  = (($u['bank_link_status'] ?? 'none') === 'linked');
  $twofa_ok = ($twofa === 1);

  return [
    'email_ok' => $email_ok,
    'bank_ok'  => $bank_ok,
    'twofa_ok' => $twofa_ok,
    'ranked_ok' => ($u['approval_status'] === 'approved') && $email_ok && $bank_ok && $twofa_ok
  ];
}

function ui_header(string $title = 'Dashboard'): void {
  global $mysqli;

  $bp = base_path();
  $u = current_user();
  if (!$u) {
    redirect($bp . "/index.php");
  }

  $uid = (int)$u['id'];
  $unread = count_unread_notifications($mysqli, $uid);
  $notes = fetch_notifications($mysqli, $uid, 8);

  $is_guest = ((int)($u['is_guest'] ?? 0) === 1);
  $roles = $u['roles'] ?? [];
  $role_label = $is_guest ? 'Guest' : (in_array('admin', $roles, true) ? 'Admin' : 'Player');

  // ranked gate status (for navbar badge)
  $req = ranked_requirements($mysqli, $u);

  ?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= h($title) ?> — CardGame</title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
</head>
<body>

<header class="app-topbar">
  <div class="app-topbar__inner">

    <a class="brand" href="<?= h($bp) ?>/dashboard.php">
      <span class="brand__logo">🂡</span>
      <span class="brand__name">CardGame</span>
      <span class="pill"><?= h($role_label) ?></span>
      <?php if ($is_guest): ?>
        <span class="pill pill-warn">Casual Only</span>
      <?php endif; ?>
    </a>

    <nav class="nav">
      <a class="nav__link" href="<?= h($bp) ?>/dashboard.php">Dashboard</a>
      <a class="nav__link" href="<?= h($bp) ?>/play.php">Play</a>
      <a class="nav__link" href="<?= h($bp) ?>/rooms.php">Rooms</a>
      <a class="nav__link" href="<?= h($bp) ?>/security.php">Security</a>
      <?php if (!$is_guest): ?>
        <a class="nav__link" href="<?= h($bp) ?>/credits.php">Credits</a>
      <?php endif; ?>
    </nav>

    <div class="actions">
      <a class="btn btn-primary" href="<?= h($bp) ?>/play.php">
        Play Now
        <?php if (!$is_guest && !$req['ranked_ok']): ?>
          <span class="btn-badge">Ranked Locked</span>
        <?php endif; ?>
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
          <div class="userchip__sub"><?= h($u['email']) ?></div>
        </div>
        <button class="icon-btn" id="userMenuBtn" type="button" aria-haspopup="true" aria-expanded="false" title="Menu">▾</button>

        <div class="dropdown" id="userMenu" role="menu">
          <a class="dropdown__item" href="<?= h($bp) ?>/profile.php">Profile</a>
          <a class="dropdown__item" href="<?= h($bp) ?>/security.php">Security</a>
          <?php if (!$is_guest): ?>
            <a class="dropdown__item" href="<?= h($bp) ?>/credits.php">Credits</a>
          <?php endif; ?>
          <div class="dropdown__sep"></div>
          <a class="dropdown__item danger" href="<?= h($bp) ?>/logout.php">Logout</a>
        </div>
      </div>

      <div class="dropdown dropdown--wide" id="notifMenu" aria-label="Notifications">
        <div class="dropdown__head">
          <div>
            <div class="dropdown__title">Notifications</div>
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
                $icon = notif_icon((string)$n['type']);
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

function ui_footer(): void {
  $bp = base_path();
  ?>
</main>

<footer class="app-footer">
  <div class="app-footer__inner">
    <div class="muted">© <?= date('Y') ?> CardGame • MVP</div>
    <div class="muted">Security: RBAC • Audit Logs • Admin Approval • Session Tracking</div>
    <div class="muted"><a href="<?= h($bp) ?>/dashboard.php">Home</a></div>
  </div>
</footer>

<script src="<?= h($bp) ?>/assets/main.js"></script>
</body>
</html>
<?php
}