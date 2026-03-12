<?php
// admin/users.php
session_start();

require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";

$bp = base_path();
$user = current_user();
if (!$user || !user_has_role($user, 'admin')) {
  flash_set('err', 'Please sign in as an administrator.');
  header("Location: {$bp}/admin/login.php");
  exit;
}

$err = flash_get('err');
$msg = flash_get('msg');
$adminName = $user['username'] ?? $user['email'] ?? 'Administrator';

$user_id = (int)($_GET['user_id'] ?? 0);
$q = trim((string)($_GET['q'] ?? ''));
$status = trim((string)($_GET['status'] ?? ''));
$type = trim((string)($_GET['type'] ?? ''));

function page_header(string $title, string $bp, string $adminName, string $active = 'users'): void {
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia Admin — <?= h($title) ?></title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/hub.css"/>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/adminstyle.css"/>
</head>
<body class="hub adminhub">
<header class="topnav">
  <div class="topnav__inner">
    <a class="logo" href="<?= h($bp) ?>/admin/index.php">
      <span class="logo__mark">CG</span>
      <span class="logo__text">Logia Administration</span>
    </a>
    <div class="navactions">
      <span class="pill">ADMIN</span>
      <span class="pill"><?= h($adminName) ?></span>
      <a class="btn btn-ghost" href="<?= h($bp) ?>/logout.php">Logout</a>
    </div>
  </div>
</header>

<main class="container admin-page">
  <section class="admin-shell">
    <aside class="admin-side card-soft">
      <div class="admin-side__head">
        <div class="pill">CONTROL PANEL</div>
        <h2>Administration</h2>
        <p>Restricted access</p>
      </div>
      <nav class="admin-menu">
        <a class="admin-menu__item <?= $active === 'overview' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/admin/index.php"><span class="admin-menu__icon">◈</span><span>Overview</span></a>
        <a class="admin-menu__item <?= $active === 'users' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/admin/users.php"><span class="admin-menu__icon">👥</span><span>Users</span></a>
        <a class="admin-menu__item <?= $active === 'reports' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/admin/reports.php"><span class="admin-menu__icon">📝</span><span>Reports</span></a>
        <a class="admin-menu__item" href="<?= h($bp) ?>/admin/game.php"><span class="admin-menu__icon">🎮</span><span>Game</span></a>
        <a class="admin-menu__item" href="<?= h($bp) ?>/admin/notifications.php"><span class="admin-menu__icon">🔔</span><span>Notifications</span></a>
        <a class="admin-menu__item <?= $active === 'settings' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/admin/settings.php"><span class="admin-menu__icon">⚙</span><span>Settings</span></a>
      </nav>
    </aside>

    <section class="admin-main">
<?php
}

function page_footer(string $bp): void {
?>
    </section>

    <aside class="admin-right">
      <article class="admin-panel card-soft">
        <div class="admin-panel__head">
          <h2>Quick Actions</h2>
        </div>
        <div class="quick-actions">
          <a class="btn btn-primary" href="<?= h($bp) ?>/admin/pending-users.php">Pending Queue</a>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/reports.php">Open Reports</a>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/settings.php">Open Settings</a>
        </div>
      </article>
    </aside>
  </section>
</main>

<footer class="sitefooter">
  <div class="sitefooter__inner">
    <div class="footleft">
      <div class="footbrand">Logia Administration</div>
      <div class="footmuted">Users • access • security state</div>
    </div>
    <div class="footright">
      <a href="<?= h($bp) ?>/admin/index.php">Dashboard</a>
      <span class="sep">•</span>
      <a href="<?= h($bp) ?>/admin/users.php">Users</a>
      <span class="sep">•</span>
      <a href="<?= h($bp) ?>/admin/reports.php">Reports</a>
      <span class="sep">•</span>
      <a href="<?= h($bp) ?>/admin/settings.php">Settings</a>
    </div>
  </div>
</footer>
</body>
</html>
<?php
}

function status_badge(string $status): string {
  $safe = h($status);
  $class = 'pill admin-status-badge';
  if ($status === 'approved') $class .= ' is-approved';
  if ($status === 'pending')  $class .= ' is-pending';
  if ($status === 'rejected') $class .= ' is-rejected';
  return '<span class="' . $class . '">' . $safe . '</span>';
}

page_header($user_id > 0 ? 'User Details' : 'Users', $bp, $adminName, 'users');

if ($err) echo '<div class="banner banner--bad">' . h($err) . '</div>';
if ($msg) echo '<div class="banner banner--good">' . h($msg) . '</div>';

if ($user_id > 0) {
  $stmt = $mysqli->prepare("
    SELECT id, username, email, display_name, created_at, last_login_at,
           approval_status, approved_by, approved_at, rejected_reason,
           is_guest, is_active, banned_until,
           email_verified_at, bank_link_status, bank_linked_at
    FROM users
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $u = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$u) {
    echo '<article class="card admin-card-pad"><h1>User not found</h1><p class="lead">That account does not exist anymore.</p><a class="btn btn-ghost" href="' . h($bp) . '/admin/users.php">Back to users</a></article>';
    page_footer($bp);
    exit;
  }

  $stmt = $mysqli->prepare("
    SELECT r.name
    FROM user_roles ur
    JOIN roles r ON r.id = ur.role_id
    WHERE ur.user_id = ?
    ORDER BY r.name
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $roles = [];
  $rs = $stmt->get_result();
  while ($row = $rs->fetch_assoc()) $roles[] = $row['name'];
  $stmt->close();

  $stmt = $mysqli->prepare("SELECT is_enabled FROM two_factor_secrets WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $twofa = (int)($stmt->get_result()->fetch_assoc()['is_enabled'] ?? 0);
  $stmt->close();

  $stmt = $mysqli->prepare("
    SELECT success, identifier, INET6_NTOA(ip_address) AS ip, failure_reason, created_at
    FROM login_attempts
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 15
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $attempts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  $stmt = $mysqli->prepare("
    SELECT id, INET6_NTOA(ip_address) AS ip, user_agent, created_at, last_seen_at, expires_at, revoked_at
    FROM auth_sessions
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 15
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  $rankedReady = (
    $u['approval_status'] === 'approved' &&
    !empty($u['email_verified_at']) &&
    $twofa === 1 &&
    ($u['bank_link_status'] ?? '') === 'linked' &&
    ((int)$u['is_active'] === 1) &&
    (empty($u['banned_until']) || strtotime((string)$u['banned_until']) <= time())
  );
  ?>
  <article class="admin-hero card admin-hero--compact">
    <div class="admin-hero__copy">
      <div class="chip">USER • SECURITY • ACCESS • SESSIONS</div>
      <h1><?= h($u['username']) ?></h1>
      <p class="lead">
        <?= h($u['email']) ?> •
        <?= ((int)$u['is_guest'] === 1) ? 'Guest' : 'Registered' ?> •
        <?= ((int)$u['is_active'] === 1) ? 'Active' : 'Inactive' ?>
      </p>
      <div class="cta">
        <a class="btn btn-primary" href="<?= h($bp) ?>/admin/pending-users.php?user_id=<?= (int)$u['id'] ?>">Open Queue</a>
        <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/users.php">Back to Users</a>
      </div>
      <div class="hero__notes">
        <span class="note">Approval: <?= h($u['approval_status']) ?></span>
        <span class="note">2FA: <?= $twofa ? 'Enabled' : 'Disabled' ?></span>
        <span class="note">Ranked: <?= $rankedReady ? 'Ready' : 'Blocked' ?></span>
      </div>
    </div>
    <div class="admin-hero__panel">
      <div class="statpanel">
        <div class="statgrid">
          <div class="stat">
            <div class="stat__label">Display Name</div>
            <div class="stat__value stat__value--small"><?= h($u['display_name'] ?: '—') ?></div>
          </div>
          <div class="stat">
            <div class="stat__label">Roles</div>
            <div class="stat__value stat__value--small"><?= h($roles ? implode(', ', $roles) : '—') ?></div>
          </div>
          <div class="stat">
            <div class="stat__label">Email Verified</div>
            <div class="stat__value"><?= $u['email_verified_at'] ? 'Yes' : 'No' ?></div>
          </div>
          <div class="stat">
            <div class="stat__label">Credits Linked</div>
            <div class="stat__value stat__value--small"><?= h($u['bank_link_status'] ?? 'none') ?></div>
          </div>
        </div>
      </div>
    </div>
  </article>

  <section class="admin-blocks admin-blocks--gap">
    <article class="admin-panel card-soft admin-card-pad">
      <div class="admin-panel__head"><h2>Identity & Access</h2></div>
      <div class="note-list">
        <div class="note-row"><div class="note-row__title">Approval status</div><div class="note-row__meta"><?= h($u['approval_status']) ?></div></div>
        <div class="note-row"><div class="note-row__title">Active</div><div class="note-row__meta"><?= ((int)$u['is_active'] === 1) ? 'Yes' : 'No' ?></div></div>
        <div class="note-row"><div class="note-row__title">Guest account</div><div class="note-row__meta"><?= ((int)$u['is_guest'] === 1) ? 'Yes' : 'No' ?></div></div>
        <div class="note-row"><div class="note-row__title">Banned until</div><div class="note-row__meta"><?= h($u['banned_until'] ?? '—') ?></div></div>
      </div>
    </article>

    <article class="admin-panel card-soft admin-card-pad">
      <div class="admin-panel__head"><h2>Timeline</h2></div>
      <div class="note-list">
        <div class="note-row"><div class="note-row__title">Created</div><div class="note-row__meta"><?= h(date('M d, Y • g:i A', strtotime((string)$u['created_at']))) ?></div></div>
        <div class="note-row"><div class="note-row__title">Last login</div><div class="note-row__meta"><?= h($u['last_login_at'] ? date('M d, Y • g:i A', strtotime((string)$u['last_login_at'])) : '—') ?></div></div>
        <div class="note-row"><div class="note-row__title">Approved at</div><div class="note-row__meta"><?= h($u['approved_at'] ? date('M d, Y • g:i A', strtotime((string)$u['approved_at'])) : '—') ?></div></div>
        <div class="note-row"><div class="note-row__title">Rejected reason</div><div class="note-row__meta"><?= h($u['rejected_reason'] ?? '—') ?></div></div>
      </div>
    </article>
  </section>

  <section class="admin-blocks admin-blocks--gap admin-blocks--two">
    <article class="admin-panel card-soft admin-card-pad">
      <div class="admin-panel__head"><h2>Recent Login Attempts</h2></div>
      <div class="activity-list">
        <?php if (!$attempts): ?>
          <div class="activity-item"><div class="activity-item__icon">•</div><div><div class="activity-item__title">No attempts recorded</div></div></div>
        <?php else: foreach ($attempts as $a): ?>
          <div class="activity-item">
            <div class="activity-item__icon"><?= ((int)$a['success'] === 1) ? '✓' : '!' ?></div>
            <div>
              <div class="activity-item__title"><?= ((int)$a['success'] === 1) ? 'Login success' : 'Login failed' ?></div>
              <div class="activity-item__meta">IP: <?= h((string)($a['ip'] ?? '—')) ?> • <?= h(date('M d • g:i A', strtotime((string)$a['created_at']))) ?></div>
              <?php if (!empty($a['failure_reason'])): ?><div class="activity-item__meta">Reason: <?= h((string)$a['failure_reason']) ?></div><?php endif; ?>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </article>

    <article class="admin-panel card-soft admin-card-pad">
      <div class="admin-panel__head"><h2>Sessions</h2></div>
      <div class="activity-list">
        <?php if (!$sessions): ?>
          <div class="activity-item"><div class="activity-item__icon">•</div><div><div class="activity-item__title">No sessions recorded</div></div></div>
        <?php else: foreach ($sessions as $s):
          $active = empty($s['revoked_at']) && strtotime((string)$s['expires_at']) > time(); ?>
          <div class="activity-item">
            <div class="activity-item__icon"><?= $active ? '●' : '○' ?></div>
            <div>
              <div class="activity-item__title"><?= $active ? 'Active session' : 'Inactive session' ?></div>
              <div class="activity-item__meta">IP: <?= h((string)($s['ip'] ?? '—')) ?> • Expires: <?= h(date('M d • g:i A', strtotime((string)$s['expires_at']))) ?></div>
              <div class="activity-item__meta"><?= h((string)$s['user_agent']) ?></div>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </article>
  </section>
  <?php

  page_footer($bp);
  exit;
}

$where = [];
$params = [];
$types = '';

if ($q !== '') {
  $where[] = "(u.username LIKE CONCAT('%', ?, '%') OR u.email LIKE CONCAT('%', ?, '%') OR u.display_name LIKE CONCAT('%', ?, '%'))";
  array_push($params, $q, $q, $q);
  $types .= 'sss';
}
if (in_array($status, ['pending', 'approved', 'rejected'], true)) {
  $where[] = "u.approval_status = ?";
  $params[] = $status;
  $types .= 's';
}
if ($type === 'guest') {
  $where[] = "u.is_guest = 1";
}
if ($type === 'registered') {
  $where[] = "u.is_guest = 0";
}

$sql = "
  SELECT u.id, u.username, u.email, u.display_name, u.approval_status, u.is_guest,
         u.is_active, u.last_login_at, u.created_at, u.email_verified_at, u.bank_link_status,
         COALESCE(t.is_enabled, 0) AS twofa_enabled
  FROM users u
  LEFT JOIN two_factor_secrets t ON t.user_id = u.id
";
if ($where) $sql .= " WHERE " . implode(' AND ', $where);
$sql .= " ORDER BY u.created_at DESC LIMIT 150";

$stmt = $mysqli->prepare($sql);
if ($types !== '') $stmt->bind_param($types, ...$params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pendingCount = (int)($mysqli->query("SELECT COUNT(*) c FROM users WHERE approval_status='pending' AND is_guest=0")->fetch_assoc()['c'] ?? 0);
$approvedCount = (int)($mysqli->query("SELECT COUNT(*) c FROM users WHERE approval_status='approved' AND is_guest=0")->fetch_assoc()['c'] ?? 0);
$guestCount = (int)($mysqli->query("SELECT COUNT(*) c FROM users WHERE is_guest=1")->fetch_assoc()['c'] ?? 0);
$rankedCount = (int)($mysqli->query("SELECT COUNT(*) c FROM users u LEFT JOIN two_factor_secrets t ON t.user_id=u.id WHERE u.is_guest=0 AND u.approval_status='approved' AND u.is_active=1 AND u.email_verified_at IS NOT NULL AND u.bank_link_status='linked' AND COALESCE(t.is_enabled,0)=1 AND (u.banned_until IS NULL OR u.banned_until<=NOW())")->fetch_assoc()['c'] ?? 0);
?>
<article class="admin-hero card admin-hero--compact">
  <div class="admin-hero__copy">
    <div class="chip">USERS • APPROVALS • ACCESS • READINESS</div>
    <h1>Users</h1>
    <p class="lead">Search, inspect, and review player access without adding new tables.</p>
    <form method="get" class="cta users-filter-form">
      <div class="users-filter-field users-filter-field--search">
        <label>Search username / email / display name</label>
        <input name="q" value="<?= h($q) ?>" placeholder="Search users"/>
      </div>
      <div class="users-filter-field">
        <label>Status</label>
        <select name="status">
          <option value="">All</option>
          <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
        </select>
      </div>
      <div class="users-filter-field">
        <label>Type</label>
        <select name="type">
          <option value="">All</option>
          <option value="registered" <?= $type === 'registered' ? 'selected' : '' ?>>Registered</option>
          <option value="guest" <?= $type === 'guest' ? 'selected' : '' ?>>Guest</option>
        </select>
      </div>
      <button class="btn btn-primary" type="submit">Apply</button>
      <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/users.php">Reset</a>
    </form>
  </div>
  <div class="admin-hero__panel">
    <div class="statpanel">
      <div class="statgrid">
        <div class="stat"><div class="stat__label">Pending</div><div class="stat__value"><?= h((string)$pendingCount) ?></div></div>
        <div class="stat"><div class="stat__label">Approved</div><div class="stat__value"><?= h((string)$approvedCount) ?></div></div>
        <div class="stat"><div class="stat__label">Guests</div><div class="stat__value"><?= h((string)$guestCount) ?></div></div>
        <div class="stat"><div class="stat__label">Ranked Ready</div><div class="stat__value"><?= h((string)$rankedCount) ?></div></div>
      </div>
    </div>
  </div>
</article>

<article class="admin-panel card-soft admin-card-pad admin-panel--spaced">
  <div class="admin-panel__head">
    <h2>User Directory</h2>
    <a href="<?= h($bp) ?>/admin/pending-users.php">Open pending queue</a>
  </div>

  <?php if (!$rows): ?>
    <div class="activity-item"><div class="activity-item__icon">•</div><div><div class="activity-item__title">No users found</div><div class="activity-item__meta">Try a broader filter.</div></div></div>
  <?php else: ?>
    <div class="activity-list">
      <?php foreach ($rows as $r):
        $ranked = (
          $r['approval_status'] === 'approved' &&
          !empty($r['email_verified_at']) &&
          (int)$r['twofa_enabled'] === 1 &&
          ($r['bank_link_status'] ?? '') === 'linked' &&
          (int)$r['is_guest'] === 0 &&
          (int)$r['is_active'] === 1
        );
      ?>
        <div class="queue-item queue-item--top">
          <div class="queue-item__main queue-item__main--fluid">
            <div class="queue-item__title queue-item__title--badges">
              <span><?= h($r['username']) ?></span>
              <?= status_badge((string)$r['approval_status']) ?>
              <?php if ((int)$r['is_guest'] === 1): ?><span class="pill">guest</span><?php endif; ?>
              <?php if ($ranked): ?><span class="pill admin-rank-badge">ranked ready</span><?php endif; ?>
            </div>
            <div class="queue-item__meta queue-item__meta--spaced"><?= h($r['email']) ?></div>
            <div class="queue-item__meta">Display: <?= h($r['display_name'] ?: '—') ?> • Last login: <?= h($r['last_login_at'] ?: '—') ?></div>
            <div class="queue-item__meta">2FA: <?= ((int)$r['twofa_enabled'] === 1) ? 'Yes' : 'No' ?> • Email verified: <?= $r['email_verified_at'] ? 'Yes' : 'No' ?> • Credits: <?= h($r['bank_link_status'] ?? 'none') ?></div>
          </div>
          <div class="queue-actions">
            <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/users.php?user_id=<?= (int)$r['id'] ?>">Open</a>
            <?php if ($r['approval_status'] === 'pending'): ?>
              <a class="btn btn-primary" href="<?= h($bp) ?>/admin/pending-users.php?user_id=<?= (int)$r['id'] ?>">Review</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</article>
<?php
page_footer($bp);