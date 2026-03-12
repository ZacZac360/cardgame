<?php
// admin/settings.php
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

$adminName = $user['username'] ?? $user['email'] ?? 'Administrator';

function qcount(mysqli $mysqli, string $sql): int {
  $res = $mysqli->query($sql);
  $row = $res ? $res->fetch_assoc() : null;
  return (int)($row['c'] ?? 0);
}

$settingsRows = [
  ['label' => 'Application environment', 'value' => defined('APP_ENV') ? APP_ENV : 'production-like'],
  ['label' => 'PHP version', 'value' => PHP_VERSION],
  ['label' => 'Session name', 'value' => session_name()],
  ['label' => 'Default timezone', 'value' => date_default_timezone_get()],
  ['label' => 'Upload max filesize', 'value' => ini_get('upload_max_filesize') ?: 'unknown'],
  ['label' => 'Post max size', 'value' => ini_get('post_max_size') ?: 'unknown'],
  ['label' => 'Max execution time', 'value' => (string)(ini_get('max_execution_time') ?: '0') . 's'],
  ['label' => 'Memory limit', 'value' => ini_get('memory_limit') ?: 'unknown'],
  ['label' => 'DB charset', 'value' => $mysqli->character_set_name()],
];

$totals = [
  'users'         => qcount($mysqli, "SELECT COUNT(*) c FROM users"),
  'roles'         => qcount($mysqli, "SELECT COUNT(*) c FROM roles"),
  'sessions'      => qcount($mysqli, "SELECT COUNT(*) c FROM auth_sessions WHERE revoked_at IS NULL AND expires_at > NOW()"),
  'notifications' => qcount($mysqli, "SELECT COUNT(*) c FROM dashboard_notifications WHERE is_read=0"),
  'pending'       => qcount($mysqli, "SELECT COUNT(*) c FROM users WHERE approval_status='pending'"),
  'approved'      => qcount($mysqli, "SELECT COUNT(*) c FROM users WHERE approval_status='approved'"),
  'guests'        => qcount($mysqli, "SELECT COUNT(*) c FROM users WHERE is_guest=1"),
  'audit'         => qcount($mysqli, "SELECT COUNT(*) c FROM audit_logs WHERE created_at >= (NOW() - INTERVAL 30 DAY)"),
];

$healthRows = [
  ['label' => 'Pending approvals', 'value' => $totals['pending']],
  ['label' => 'Approved users', 'value' => $totals['approved']],
  ['label' => 'Guest accounts', 'value' => $totals['guests']],
  ['label' => 'Audit entries (30d)', 'value' => $totals['audit']],
];
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia Admin — Settings</title>
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

<main class="container admin-page admin-settings-page">
  <section class="settings-top">
    <article class="card settings-hero">
      <div class="chip">SETTINGS • SYSTEM • ADMIN</div>

      <h1>Settings</h1>
      <p class="lead">
        System overview, environment values, and administrative controls.
      </p>

      <div class="settings-actions">
        <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/index.php">Back to Dashboard</a>
        <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/reports.php?type=users">Open User Report</a>
        <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/reports.php?type=security">Open Security Report</a>
      </div>

      <div class="settings-pills">
        <span class="pill">Environment</span>
        <span class="pill"><?= h((string)$settingsRows[0]['value']) ?></span>
        <span class="pill">PHP <?= h(PHP_VERSION) ?></span>
        <span class="pill"><?= h($settingsRows[3]['value']) ?></span>
      </div>
    </article>

    <aside class="card-soft summary-card">
      <div class="summary-head">
        <div>
          <h3>Administrative Summary</h3>
          <div class="sub">Current platform state</div>
        </div>
        <span class="pill">LIVE</span>
      </div>

      <div class="statgrid">
        <div class="stat settings-stat-card">
          <div class="stat__label">Users</div>
          <div class="stat__value"><?= h((string)$totals['users']) ?></div>
        </div>
        <div class="stat settings-stat-card">
          <div class="stat__label">Roles</div>
          <div class="stat__value"><?= h((string)$totals['roles']) ?></div>
        </div>
        <div class="stat settings-stat-card">
          <div class="stat__label">Active sessions</div>
          <div class="stat__value"><?= h((string)$totals['sessions']) ?></div>
        </div>
        <div class="stat settings-stat-card">
          <div class="stat__label">Unread notices</div>
          <div class="stat__value"><?= h((string)$totals['notifications']) ?></div>
        </div>
      </div>
    </aside>
  </section>

  <section class="settings-main">
    <article class="card-soft panel">
      <div class="panel-head settings-panel-head">
        <div>
          <h2>System Snapshot</h2>
          <div class="sub">Runtime and environment values</div>
        </div>
        <span class="pill">READOUT</span>
      </div>

      <div class="settings-readout-list">
        <?php foreach ($settingsRows as $row): ?>
          <div class="settings-readout-row">
            <div class="settings-readout-label"><?= h($row['label']) ?></div>
            <div class="settings-readout-value"><?= h((string)$row['value']) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </article>

    <article class="card-soft panel">
      <div class="panel-head settings-panel-head">
        <div>
          <h2>Control Center</h2>
          <div class="sub">Administrative modules</div>
        </div>
        <span class="pill">ADMIN</span>
      </div>

      <div class="stack">
        <div class="metric-card">
          <div class="metric-card__label">Security policies</div>
          <div class="metric-card__value">Session + auth</div>
        </div>
        <div class="metric-card">
          <div class="metric-card__label">Game controls</div>
          <div class="metric-card__value">Queues + ranked</div>
        </div>
        <div class="metric-card">
          <div class="metric-card__label">Messaging</div>
          <div class="metric-card__value">Alerts + templates</div>
        </div>
        <div class="metric-card">
          <div class="metric-card__label">Reports access</div>
          <div class="metric-card__value">CSV + analytics</div>
        </div>
      </div>
    </article>

    <aside class="settings-side">
      <article class="card-soft panel">
        <div class="panel-head settings-panel-head">
          <div>
            <h3>Platform Health</h3>
            <div class="sub">Key account totals</div>
          </div>
          <span class="pill">STATE</span>
        </div>

        <table class="micro-table">
          <?php foreach ($healthRows as $row): ?>
            <tr>
              <td><?= h($row['label']) ?></td>
              <td><?= h((string)$row['value']) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </article>

      <article class="card-soft panel">
        <div class="panel-head settings-panel-head">
          <div>
            <h3>Quick Actions</h3>
            <div class="sub">Administrative navigation</div>
          </div>
          <span class="pill">OPEN</span>
        </div>

        <div class="quick-actions quick-actions--stack">
          <a class="btn btn-primary" href="<?= h($bp) ?>/admin/users.php">Manage Users</a>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/pending-users.php">Pending Approvals</a>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/reports.php?type=audit">Audit Reports</a>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/reports.php?type=security">Security Reports</a>
        </div>
      </article>
    </aside>
  </section>

  <div class="settings-page-spacer" aria-hidden="true"></div>
</main>

<footer class="sitefooter">
  <div class="sitefooter__inner">
    <div>
      <div class="footbrand">Logia Administration</div>
      <div class="footmuted">Administrative settings and system overview</div>
    </div>
  </div>
</footer>
</body>
</html>