<?php
// admin/index.php
session_start();
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";

$user = current_user();
if (!$user || !user_has_role($user, 'admin')) {
  flash_set('err', 'Please sign in as an administrator.');
  header("Location: {$bp}/admin/login.php");
  exit;
}

$bp  = base_path();
$err = flash_get('err');
$msg = flash_get('msg');

$user = current_user();
if (!$user || !user_has_role($user, 'admin')) {
  flash_set('err', 'Please sign in as an administrator.');
  header("Location: {$bp}/admin/login.php");
  exit;
}

$adminId   = (int)($user['id'] ?? 0);
$adminName = $user['username'] ?? $user['email'] ?? 'Administrator';

function scalar_query(mysqli $mysqli, string $sql, string $types = '', ...$params): int {
  $stmt = $mysqli->prepare($sql);
  if (!$stmt) return 0;

  if ($types !== '') {
    $stmt->bind_param($types, ...$params);
  }

  $stmt->execute();
  $stmt->bind_result($value);
  $stmt->fetch();
  $stmt->close();

  return (int)$value;
}

function recent_audit_rows(mysqli $mysqli, int $limit = 4): array {
  $sql = "
    SELECT
      a.action,
      a.target_type,
      a.target_id,
      a.created_at,
      u.username AS actor_name
    FROM audit_logs a
    LEFT JOIN users u ON u.id = a.actor_user_id
    ORDER BY a.created_at DESC
    LIMIT ?
  ";
  $stmt = $mysqli->prepare($sql);
  if (!$stmt) return [];

  $stmt->bind_param("i", $limit);
  $stmt->execute();
  $res = $stmt->get_result();
  $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
  $stmt->close();

  return $rows;
}

function nice_action_label(?string $action): string {
  $action = strtoupper(trim((string)$action));
  if ($action === '') return 'System activity';

  return match ($action) {
    'ROLE_GRANT'        => 'Role granted',
    'ROLE_REVOKE'       => 'Role revoked',
    'LOGIN_SUCCESS'     => 'Login success',
    'LOGIN_FAILED'      => 'Login failed',
    'APPROVAL_APPROVE'  => 'User approved',
    'APPROVAL_REJECT'   => 'User rejected',
    'PENALTY_APPLY'     => 'Penalty applied',
    'PENALTY_REMOVE'    => 'Penalty removed',
    default             => ucwords(strtolower(str_replace('_', ' ', $action))),
  };
}

function nice_target_label(?string $targetType, $targetId): string {
  $targetType = trim((string)$targetType);
  if ($targetType === '') return 'No target';
  if ($targetId === null || $targetId === '') return $targetType;
  return $targetType . ' #' . $targetId;
}

/* =========================
   Live DB-backed stats
   ========================= */

$pendingApprovals = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM users WHERE approval_status='pending' AND is_guest=0"
);

$approvedUsers = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM users WHERE approval_status='approved' AND is_guest=0"
);

$guestUsers = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM users WHERE is_guest=1"
);

$bannedUsers = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM users WHERE banned_until IS NOT NULL AND banned_until > NOW()"
);

$failedLogins24h = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM login_attempts WHERE success=0 AND created_at >= (NOW() - INTERVAL 1 DAY)"
);

$activeSessions = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM auth_sessions WHERE revoked_at IS NULL AND expires_at > NOW()"
);

$audit24h = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM audit_logs WHERE created_at >= (NOW() - INTERVAL 1 DAY)"
);

$unreadAdminNotifications = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM dashboard_notifications WHERE user_id=? AND is_read=0",
  "i",
  $adminId
);

$emailVerifiedUsers = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM users WHERE email_verified_at IS NOT NULL AND approval_status='approved' AND is_guest=0"
);

$twoFactorEnabled = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM two_factor_secrets WHERE is_enabled=1"
);

$bankLinkedUsers = scalar_query(
  $mysqli,
  "SELECT COUNT(*) FROM users WHERE bank_link_status='linked' AND approval_status='approved' AND is_guest=0"
);

$rankedReadyUsers = scalar_query(
  $mysqli,
  "SELECT COUNT(*)
   FROM users u
   INNER JOIN two_factor_secrets t ON t.user_id = u.id
   WHERE u.is_guest=0
     AND u.is_active=1
     AND u.approval_status='approved'
     AND u.email_verified_at IS NOT NULL
     AND u.bank_link_status='linked'
     AND t.is_enabled=1
     AND (u.banned_until IS NULL OR u.banned_until <= NOW())"
);

$recentAudit = recent_audit_rows($mysqli, 4);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia — Administration</title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/hub.css"/>
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

  <div class="container">
    <?php if ($err): ?>
      <div class="banner banner--bad"><?= h($err) ?></div>
    <?php elseif ($msg): ?>
      <div class="banner banner--good"><?= h($msg) ?></div>
    <?php endif; ?>
  </div>

  <main class="container">
    <section class="admin-shell">

      <!-- Left Rail -->
      <aside class="admin-side card-soft">
        <div class="admin-side__head">
          <div class="pill">CONTROL PANEL</div>
          <h2>Administration</h2>
          <p>Restricted access</p>
        </div>

        <nav class="admin-menu">
          <a class="admin-menu__item is-active" href="<?= h($bp) ?>/admin/index.php">
            <span class="admin-menu__icon">◈</span>
            <span>Overview</span>
          </a>

          <a class="admin-menu__item" href="<?= h($bp) ?>/admin/users.php">
            <span class="admin-menu__icon">👥</span>
            <span>Users</span>
          </a>

          <a class="admin-menu__item" href="<?= h($bp) ?>/admin/reports.php">
            <span class="admin-menu__icon">📝</span>
            <span>Reports</span>
          </a>

          <a class="admin-menu__item" href="<?= h($bp) ?>/admin/game.php">
            <span class="admin-menu__icon">🎮</span>
            <span>Game</span>
          </a>

          <a class="admin-menu__item" href="<?= h($bp) ?>/admin/settings.php">
            <span class="admin-menu__icon">⚙</span>
            <span>Settings</span>
          </a>
        </nav>
      </aside>

      <!-- Main -->
      <section class="admin-main">
        <article class="admin-hero card">
          <div class="admin-hero__copy">
            <div class="chip">OVERVIEW • USERS • SECURITY • GAME ACCESS</div>
            <h1>Overview</h1>
            <p class="lead">
              <?= h((string)$pendingApprovals) ?> approvals pending •
              <?= h((string)$failedLogins24h) ?> failed logins today •
              <?= h((string)$rankedReadyUsers) ?> ranked-ready users
            </p>

            <div class="cta">
              <a class="btn btn-primary btn-lg" href="<?= h($bp) ?>/admin/users.php">Open Users</a>
              <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/admin/reports.php">Open Reports</a>
              <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/admin/game.php">Open Game</a>
            </div>

            <div class="hero__notes">
              <span class="note">Portal: Active</span>
              <span class="note">Sessions: <?= h((string)$activeSessions) ?></span>
              <span class="note">Unread Notices: <?= h((string)$unreadAdminNotifications) ?></span>
            </div>
          </div>

          <div class="admin-hero__panel">
            <div class="statpanel">
              <div class="statpanel__top">
                <span class="dot d1"></span>
                <span class="dot d2"></span>
                <span class="dot d3"></span>
                <span class="statpanel__title">Administrative Summary</span>
                <span class="statpanel__pill">LIVE</span>
              </div>

              <div class="statgrid">
                <div class="stat">
                  <div class="stat__label">Pending Approvals</div>
                  <div class="stat__value"><?= h((string)$pendingApprovals) ?></div>
                  <div class="stat__delta good">Users tab</div>
                </div>

                <div class="stat">
                  <div class="stat__label">Approved Users</div>
                  <div class="stat__value"><?= h((string)$approvedUsers) ?></div>
                  <div class="stat__delta">Non-guest</div>
                </div>

                <div class="stat">
                  <div class="stat__label">Failed Logins</div>
                  <div class="stat__value"><?= h((string)$failedLogins24h) ?></div>
                  <div class="stat__delta">Last 24 hours</div>
                </div>

                <div class="stat">
                  <div class="stat__label">Ranked Ready</div>
                  <div class="stat__value"><?= h((string)$rankedReadyUsers) ?></div>
                  <div class="stat__delta">Verified + 2FA + bank linked</div>
                </div>
              </div>

              <div class="spark">
                <div class="spark__head">
                  <div class="spark__title">Audit Volume</div>
                  <div class="spark__hint">Last 24 hours: <?= h((string)$audit24h) ?></div>
                </div>
                <div class="spark__bars">
                  <span style="--h:38%"></span>
                  <span style="--h:52%"></span>
                  <span style="--h:44%"></span>
                  <span style="--h:71%"></span>
                  <span style="--h:58%"></span>
                  <span style="--h:82%"></span>
                  <span style="--h:64%"></span>
                </div>
              </div>
            </div>
          </div>
        </article>

        <section class="admin-blocks">
          <article class="admin-panel card-soft">
            <div class="admin-panel__head">
              <h2>Priority Queue</h2>
              <a href="<?= h($bp) ?>/admin/users.php">Open users</a>
            </div>

            <div class="queue-list">
              <div class="queue-item">
                <div class="queue-item__main">
                  <div class="queue-item__title">Pending approvals</div>
                  <div class="queue-item__meta"><?= h((string)$pendingApprovals) ?> pending accounts</div>
                </div>
                <span class="pill">HIGH</span>
              </div>

              <div class="queue-item">
                <div class="queue-item__main">
                  <div class="queue-item__title">Banned accounts</div>
                  <div class="queue-item__meta"><?= h((string)$bannedUsers) ?> currently restricted</div>
                </div>
                <span class="pill">WATCH</span>
              </div>

              <div class="queue-item">
                <div class="queue-item__main">
                  <div class="queue-item__title">Failed logins</div>
                  <div class="queue-item__meta"><?= h((string)$failedLogins24h) ?> in the last 24 hours</div>
                </div>
                <span class="pill">OPEN</span>
              </div>

              <div class="queue-item">
                <div class="queue-item__main">
                  <div class="queue-item__title">Unread admin notifications</div>
                  <div class="queue-item__meta"><?= h((string)$unreadAdminNotifications) ?> unread notices</div>
                </div>
                <span class="pill">INFO</span>
              </div>
            </div>
          </article>

          <article class="admin-panel card-soft">
            <div class="admin-panel__head">
              <h2>Game Access</h2>
              <a href="<?= h($bp) ?>/admin/game.php">Open game</a>
            </div>

            <div class="note-list">
              <div class="note-row">
                <div class="note-row__title">Email verified</div>
                <div class="note-row__meta"><?= h((string)$emailVerifiedUsers) ?> approved users</div>
              </div>

              <div class="note-row">
                <div class="note-row__title">2FA enabled</div>
                <div class="note-row__meta"><?= h((string)$twoFactorEnabled) ?> users</div>
              </div>

              <div class="note-row">
                <div class="note-row__title">Bank linked</div>
                <div class="note-row__meta"><?= h((string)$bankLinkedUsers) ?> approved users</div>
              </div>

              <div class="note-row">
                <div class="note-row__title">Ranked ready</div>
                <div class="note-row__meta"><?= h((string)$rankedReadyUsers) ?> users meet requirements</div>
              </div>
            </div>
          </article>
        </section>
      </section>

      <!-- Right Rail -->
      <aside class="admin-right">
        <article class="admin-panel card-soft">
          <div class="admin-panel__head">
            <h2>Recent Activity</h2>
            <a href="<?= h($bp) ?>/admin/reports.php">Open reports</a>
          </div>

          <div class="activity-list">
            <?php if (!$recentAudit): ?>
              <div class="activity-item">
                <div class="activity-item__icon">•</div>
                <div>
                  <div class="activity-item__title">No recent audit activity</div>
                  <div class="activity-item__meta">New actions will appear here.</div>
                </div>
              </div>
            <?php else: ?>
              <?php foreach ($recentAudit as $row): ?>
                <div class="activity-item">
                  <div class="activity-item__icon">•</div>
                  <div>
                    <div class="activity-item__title">
                      <?= h(nice_action_label($row['action'] ?? '')) ?>
                    </div>
                    <div class="activity-item__meta">
                      <?= h(($row['actor_name'] ?: 'System') . ' • ' . nice_target_label($row['target_type'] ?? '', $row['target_id'] ?? null)) ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </article>

        <article class="admin-panel card-soft">
          <div class="admin-panel__head">
            <h2>Attention Needed</h2>
          </div>

          <div class="attention-list">
            <div class="attention-item">
              <span>Pending approvals</span>
              <strong><?= h((string)$pendingApprovals) ?></strong>
            </div>

            <div class="attention-item">
              <span>Failed logins</span>
              <strong><?= h((string)$failedLogins24h) ?></strong>
            </div>

            <div class="attention-item">
              <span>Unread notifications</span>
              <strong><?= h((string)$unreadAdminNotifications) ?></strong>
            </div>

            <div class="attention-item">
              <span>Audit entries (24h)</span>
              <strong><?= h((string)$audit24h) ?></strong>
            </div>
          </div>
        </article>

        <article class="admin-panel card-soft">
          <div class="admin-panel__head">
            <h2>Quick Actions</h2>
          </div>

          <div class="quick-actions">
            <a class="btn btn-primary" href="<?= h($bp) ?>/admin/users.php">Review Users</a>
            <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/reports.php">Security Reports</a>
            <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/game.php">Game Access</a>
          </div>
        </article>
      </aside>
    </section>
  </main>

  <footer class="sitefooter">
    <div class="sitefooter__inner">
      <div class="footleft">
        <div class="footbrand">Logia Administration</div>
        <div class="footmuted">© <?= date('Y') ?> • Restricted access</div>
      </div>
      <div class="footright">
        <a href="<?= h($bp) ?>/admin/index.php">Dashboard</a>
        <span class="sep">•</span>
        <a href="<?= h($bp) ?>/admin/users.php">Users</a>
        <span class="sep">•</span>
        <a href="<?= h($bp) ?>/admin/reports.php">Reports</a>
        <span class="sep">•</span>
        <a href="<?= h($bp) ?>/logout.php">Logout</a>
      </div>
    </div>
  </footer>
</body>
</html>