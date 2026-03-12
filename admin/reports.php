<?php
// admin/reports.php
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

$type   = trim((string)($_GET['type'] ?? 'users'));
$q      = trim((string)($_GET['q'] ?? ''));
$action = trim((string)($_GET['action'] ?? ''));
$days   = (int)($_GET['days'] ?? 30);
$export = (string)($_GET['export'] ?? '');

$allowedTypes = ['users', 'security', 'audit', 'game', 'financial'];
if (!in_array($type, $allowedTypes, true)) $type = 'users';
if (!in_array($days, [1, 7, 30, 90], true)) $days = 30;

function csv_out(string $filename, array $header, array $rows): never {
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="' . $filename . '"');
  $out = fopen('php://output', 'w');
  fputcsv($out, $header);
  foreach ($rows as $row) fputcsv($out, $row);
  fclose($out);
  exit;
}

function qv(mysqli $mysqli, string $sql): int {
  $res = $mysqli->query($sql);
  $row = $res ? $res->fetch_assoc() : null;
  return (int)($row['c'] ?? 0);
}

function bind_and_fetch(mysqli $mysqli, string $sql, string $types = '', array $params = []): array {
  $stmt = $mysqli->prepare($sql);
  if ($types !== '') {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $rows;
}

function tab_href(string $bp, string $type, int $days, string $q, string $action = ''): string {
  $params = ['type' => $type, 'days' => $days];
  if ($q !== '') $params['q'] = $q;
  if ($type === 'audit' && $action !== '') $params['action'] = $action;
  return $bp . '/admin/reports.php?' . http_build_query($params);
}

$cutoffSql = "NOW() - INTERVAL {$days} DAY";

$actions = $mysqli->query("SELECT DISTINCT action FROM audit_logs ORDER BY action ASC")->fetch_all(MYSQLI_ASSOC);

$totalUsers       = qv($mysqli, "SELECT COUNT(*) c FROM users");
$newUsers         = qv($mysqli, "SELECT COUNT(*) c FROM users WHERE created_at >= ({$cutoffSql})");
$guestUsers       = qv($mysqli, "SELECT COUNT(*) c FROM users WHERE is_guest = 1");
$approvedUsers    = qv($mysqli, "SELECT COUNT(*) c FROM users WHERE approval_status = 'approved'");
$pendingUsers     = qv($mysqli, "SELECT COUNT(*) c FROM users WHERE approval_status = 'pending'");
$verifiedUsers    = qv($mysqli, "SELECT COUNT(*) c FROM users WHERE email_verified_at IS NOT NULL");
$twofaUsers       = qv($mysqli, "SELECT COUNT(*) c FROM two_factor_secrets WHERE COALESCE(is_enabled,0) = 1");
$linkedBanks      = qv($mysqli, "SELECT COUNT(*) c FROM users WHERE bank_link_status = 'linked'");
$failedLogins     = qv($mysqli, "SELECT COUNT(*) c FROM login_attempts WHERE success = 0 AND created_at >= ({$cutoffSql})");
$successLogins    = qv($mysqli, "SELECT COUNT(*) c FROM login_attempts WHERE success = 1 AND created_at >= ({$cutoffSql})");
$auditCount       = qv($mysqli, "SELECT COUNT(*) c FROM audit_logs WHERE created_at >= ({$cutoffSql})");
$bannedUsers      = qv($mysqli, "SELECT COUNT(*) c FROM users WHERE banned_until IS NOT NULL AND banned_until > NOW()");
$rankedReadyCount = qv($mysqli, "
  SELECT COUNT(*) c
  FROM users u
  LEFT JOIN two_factor_secrets t ON t.user_id = u.id
  WHERE u.is_guest = 0
    AND u.is_active = 1
    AND u.approval_status = 'approved'
    AND u.email_verified_at IS NOT NULL
    AND u.bank_link_status = 'linked'
    AND COALESCE(t.is_enabled, 0) = 1
    AND (u.banned_until IS NULL OR u.banned_until <= NOW())
");

$headline  = 'User Reports';
$subline   = 'Accounts, approvals, verification, and readiness.';
$cards     = [];
$csvHeader = [];
$csvRows   = [];
$reportRows = [];

if ($type === 'users') {
  $headline = 'User Reports';
  $subline  = 'Accounts, approvals, verification, and readiness.';

  $sql = "
    SELECT
      u.id,
      u.username,
      u.display_name,
      u.email,
      u.approval_status,
      u.is_guest,
      u.is_active,
      u.email_verified_at,
      u.bank_link_status,
      u.last_login_at,
      u.created_at,
      COALESCE(t.is_enabled, 0) AS twofa_enabled,
      CASE
        WHEN u.is_guest = 0
         AND u.is_active = 1
         AND u.approval_status = 'approved'
         AND u.email_verified_at IS NOT NULL
         AND u.bank_link_status = 'linked'
         AND COALESCE(t.is_enabled, 0) = 1
         AND (u.banned_until IS NULL OR u.banned_until <= NOW())
        THEN 1 ELSE 0
      END AS ranked_ready
    FROM users u
    LEFT JOIN two_factor_secrets t ON t.user_id = u.id
    WHERE u.created_at >= (NOW() - INTERVAL ? DAY)
  ";
  $params = [$days];
  $types  = 'i';

  if ($q !== '') {
    $sql .= " AND (
      u.username LIKE CONCAT('%', ?, '%')
      OR u.email LIKE CONCAT('%', ?, '%')
      OR COALESCE(u.display_name,'') LIKE CONCAT('%', ?, '%')
      OR u.approval_status LIKE CONCAT('%', ?, '%')
    )";
    array_push($params, $q, $q, $q, $q);
    $types .= 'ssss';
  }

  $sql .= " ORDER BY u.created_at DESC LIMIT 500";
  $reportRows = bind_and_fetch($mysqli, $sql, $types, $params);

  $cards = [
    ['label' => 'Total users',      'value' => $totalUsers],
    ['label' => 'New in window',    'value' => $newUsers],
    ['label' => 'Pending approval', 'value' => $pendingUsers],
    ['label' => 'Ranked ready',     'value' => $rankedReadyCount],
  ];

  $csvHeader = ['ID', 'Username', 'Display Name', 'Email', 'Approval Status', 'Guest', 'Active', 'Email Verified', '2FA Enabled', 'Bank Link Status', 'Ranked Ready', 'Last Login', 'Created At'];
  foreach ($reportRows as $r) {
    $csvRows[] = [
      $r['id'], $r['username'], $r['display_name'], $r['email'], $r['approval_status'],
      $r['is_guest'], $r['is_active'], $r['email_verified_at'], $r['twofa_enabled'],
      $r['bank_link_status'], $r['ranked_ready'], $r['last_login_at'], $r['created_at']
    ];
  }
}

if ($type === 'security') {
  $headline = 'Security Reports';
  $subline  = 'Authentication activity, risk signals, and account state.';

  $sql = "
    SELECT
      la.id,
      la.user_id,
      la.identifier,
      la.success,
      INET6_NTOA(la.ip_address) AS ip,
      la.user_agent,
      la.failure_reason,
      la.created_at
    FROM login_attempts la
    WHERE la.created_at >= (NOW() - INTERVAL ? DAY)
  ";
  $params = [$days];
  $types  = 'i';

  if ($q !== '') {
    $sql .= " AND (
      la.identifier LIKE CONCAT('%', ?, '%')
      OR COALESCE(la.failure_reason,'') LIKE CONCAT('%', ?, '%')
      OR COALESCE(la.user_agent,'') LIKE CONCAT('%', ?, '%')
      OR CAST(la.user_id AS CHAR) LIKE CONCAT('%', ?, '%')
    )";
    array_push($params, $q, $q, $q, $q);
    $types .= 'ssss';
  }

  $sql .= " ORDER BY la.created_at DESC LIMIT 500";
  $reportRows = bind_and_fetch($mysqli, $sql, $types, $params);

  $cards = [
    ['label' => 'Failed logins',  'value' => $failedLogins],
    ['label' => 'Login success',  'value' => $successLogins],
    ['label' => '2FA enabled',    'value' => $twofaUsers],
    ['label' => 'Restricted',     'value' => $bannedUsers],
  ];

  $csvHeader = ['ID', 'User ID', 'Identifier', 'Success', 'IP', 'User Agent', 'Failure Reason', 'Created At'];
  foreach ($reportRows as $r) {
    $csvRows[] = [
      $r['id'], $r['user_id'], $r['identifier'], $r['success'],
      $r['ip'], $r['user_agent'], $r['failure_reason'], $r['created_at']
    ];
  }
}

if ($type === 'audit') {
  $headline = 'Audit Reports';
  $subline  = 'Administrative actions and recorded system events.';

  $sql = "
    SELECT
      a.id,
      a.actor_user_id,
      a.action,
      a.target_type,
      a.target_id,
      a.metadata_json,
      a.created_at
    FROM audit_logs a
    WHERE a.created_at >= (NOW() - INTERVAL ? DAY)
  ";
  $params = [$days];
  $types  = 'i';

  if ($action !== '') {
    $sql .= " AND a.action = ?";
    $params[] = $action;
    $types .= 's';
  }

  if ($q !== '') {
    $sql .= " AND (
      a.action LIKE CONCAT('%', ?, '%')
      OR COALESCE(a.target_type,'') LIKE CONCAT('%', ?, '%')
      OR CAST(a.target_id AS CHAR) LIKE CONCAT('%', ?, '%')
      OR COALESCE(a.metadata_json,'') LIKE CONCAT('%', ?, '%')
    )";
    array_push($params, $q, $q, $q, $q);
    $types .= 'ssss';
  }

  $sql .= " ORDER BY a.created_at DESC LIMIT 500";
  $reportRows = bind_and_fetch($mysqli, $sql, $types, $params);

  $cards = [
    ['label' => 'Audit entries',  'value' => $auditCount],
    ['label' => 'Pending users',  'value' => $pendingUsers],
    ['label' => 'Approved users', 'value' => $approvedUsers],
    ['label' => 'Rows loaded',    'value' => count($reportRows)],
  ];

  $csvHeader = ['ID', 'Actor User ID', 'Action', 'Target Type', 'Target ID', 'Metadata JSON', 'Created At'];
  foreach ($reportRows as $r) {
    $csvRows[] = [
      $r['id'], $r['actor_user_id'], $r['action'], $r['target_type'],
      $r['target_id'], $r['metadata_json'], $r['created_at']
    ];
  }
}

if ($type === 'game') {
  $headline = 'Game Reports';
  $subline  = 'Match access, ranked eligibility, and participation signals.';

  $sql = "
    SELECT
      u.id,
      u.username,
      u.email,
      u.is_guest,
      u.approval_status,
      u.is_active,
      u.email_verified_at,
      u.bank_link_status,
      COALESCE(t.is_enabled, 0) AS twofa_enabled,
      CASE
        WHEN u.is_guest = 0
         AND u.is_active = 1
         AND u.approval_status = 'approved'
         AND u.email_verified_at IS NOT NULL
         AND u.bank_link_status = 'linked'
         AND COALESCE(t.is_enabled, 0) = 1
         AND (u.banned_until IS NULL OR u.banned_until <= NOW())
        THEN 1 ELSE 0
      END AS ranked_ready,
      u.last_login_at,
      u.created_at
    FROM users u
    LEFT JOIN two_factor_secrets t ON t.user_id = u.id
    WHERE u.created_at >= (NOW() - INTERVAL ? DAY)
  ";
  $params = [$days];
  $types  = 'i';

  if ($q !== '') {
    $sql .= " AND (
      u.username LIKE CONCAT('%', ?, '%')
      OR u.email LIKE CONCAT('%', ?, '%')
      OR u.approval_status LIKE CONCAT('%', ?, '%')
    )";
    array_push($params, $q, $q, $q);
    $types .= 'sss';
  }

  $sql .= " ORDER BY ranked_ready DESC, u.last_login_at DESC, u.created_at DESC LIMIT 500";
  $reportRows = bind_and_fetch($mysqli, $sql, $types, $params);

  $cards = [
    ['label' => 'Ranked ready', 'value' => $rankedReadyCount],
    ['label' => 'Guests',       'value' => $guestUsers],
    ['label' => 'Approved',     'value' => $approvedUsers],
    ['label' => 'Recent logins','value' => $successLogins],
  ];

  $csvHeader = ['User ID', 'Username', 'Email', 'Guest', 'Approval', 'Active', 'Email Verified', '2FA Enabled', 'Bank Link', 'Ranked Ready', 'Last Login', 'Created At'];
  foreach ($reportRows as $r) {
    $csvRows[] = [
      $r['id'], $r['username'], $r['email'], $r['is_guest'], $r['approval_status'],
      $r['is_active'], $r['email_verified_at'], $r['twofa_enabled'], $r['bank_link_status'],
      $r['ranked_ready'], $r['last_login_at'], $r['created_at']
    ];
  }
}

if ($type === 'financial') {
  $headline = 'Financial & Access Reports';
  $subline  = 'Link status, account access state, and monetization-facing signals.';

  $sql = "
    SELECT
      u.id,
      u.username,
      u.email,
      u.bank_link_status,
      u.approval_status,
      u.is_active,
      u.email_verified_at,
      COALESCE(t.is_enabled, 0) AS twofa_enabled,
      CASE
        WHEN u.is_guest = 0
         AND u.is_active = 1
         AND u.approval_status = 'approved'
         AND u.email_verified_at IS NOT NULL
         AND u.bank_link_status = 'linked'
         AND COALESCE(t.is_enabled, 0) = 1
         AND (u.banned_until IS NULL OR u.banned_until <= NOW())
        THEN 1 ELSE 0
      END AS ranked_ready,
      u.last_login_at,
      u.created_at
    FROM users u
    LEFT JOIN two_factor_secrets t ON t.user_id = u.id
    WHERE u.created_at >= (NOW() - INTERVAL ? DAY)
  ";
  $params = [$days];
  $types  = 'i';

  if ($q !== '') {
    $sql .= " AND (
      u.username LIKE CONCAT('%', ?, '%')
      OR u.email LIKE CONCAT('%', ?, '%')
      OR COALESCE(u.bank_link_status,'') LIKE CONCAT('%', ?, '%')
      OR u.approval_status LIKE CONCAT('%', ?, '%')
    )";
    array_push($params, $q, $q, $q, $q);
    $types .= 'ssss';
  }

  $sql .= " ORDER BY (u.bank_link_status = 'linked') DESC, u.created_at DESC LIMIT 500";
  $reportRows = bind_and_fetch($mysqli, $sql, $types, $params);

  $cards = [
    ['label' => 'Linked banks', 'value' => $linkedBanks],
    ['label' => 'Verified',     'value' => $verifiedUsers],
    ['label' => '2FA enabled',  'value' => $twofaUsers],
    ['label' => 'Ranked ready', 'value' => $rankedReadyCount],
  ];

  $csvHeader = ['User ID', 'Username', 'Email', 'Bank Link Status', 'Approval', 'Active', 'Email Verified', '2FA Enabled', 'Ranked Ready', 'Last Login', 'Created At'];
  foreach ($reportRows as $r) {
    $csvRows[] = [
      $r['id'], $r['username'], $r['email'], $r['bank_link_status'], $r['approval_status'],
      $r['is_active'], $r['email_verified_at'], $r['twofa_enabled'],
      $r['ranked_ready'], $r['last_login_at'], $r['created_at']
    ];
  }
}

if ($export === 'csv') {
  csv_out('report_' . $type . '_' . date('Ymd_His') . '.csv', $csvHeader, $csvRows);
}

$exportHref = $bp . '/admin/reports.php?' . http_build_query([
  'type'   => $type,
  'days'   => $days,
  'q'      => $q,
  'action' => ($type === 'audit' ? $action : ''),
  'export' => 'csv',
]);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia Admin — Reports</title>
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

<main class="container admin-reports-page">
  <section class="reports-top">
    <article class="reports-hero card">
      <div class="chip">REPORTS • ANALYTICS • CSV</div>
      <h1><?= h($headline) ?></h1>
      <p class="lead"><?= h($subline) ?></p>

      <div class="tabbar">
        <a class="tab <?= $type === 'users' ? 'is-active' : '' ?>" href="<?= h(tab_href($bp, 'users', $days, $q)) ?>">Users</a>
        <a class="tab <?= $type === 'security' ? 'is-active' : '' ?>" href="<?= h(tab_href($bp, 'security', $days, $q)) ?>">Security</a>
        <a class="tab <?= $type === 'audit' ? 'is-active' : '' ?>" href="<?= h(tab_href($bp, 'audit', $days, $q, $action)) ?>">Audit</a>
        <a class="tab <?= $type === 'game' ? 'is-active' : '' ?>" href="<?= h(tab_href($bp, 'game', $days, $q)) ?>">Game</a>
        <a class="tab <?= $type === 'financial' ? 'is-active' : '' ?>" href="<?= h(tab_href($bp, 'financial', $days, $q)) ?>">Financial</a>
      </div>

      <form method="get" class="reports-toolbar">
        <input type="hidden" name="type" value="<?= h($type) ?>"/>

        <div class="field">
          <label>Window</label>
          <select name="days">
            <option value="1" <?= $days === 1 ? 'selected' : '' ?>>24 hours</option>
            <option value="7" <?= $days === 7 ? 'selected' : '' ?>>7 days</option>
            <option value="30" <?= $days === 30 ? 'selected' : '' ?>>30 days</option>
            <option value="90" <?= $days === 90 ? 'selected' : '' ?>>90 days</option>
          </select>
        </div>

        <?php if ($type === 'audit'): ?>
          <div class="field">
            <label>Action</label>
            <select name="action">
              <option value="">All actions</option>
              <?php foreach ($actions as $a): $val = (string)$a['action']; ?>
                <option value="<?= h($val) ?>" <?= $action === $val ? 'selected' : '' ?>><?= h($val) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        <?php else: ?>
          <div class="field">
            <label>Mode</label>
            <select disabled>
              <option>Active tab</option>
            </select>
          </div>
        <?php endif; ?>

        <div class="field field-search">
          <label>Search</label>
          <input name="q" value="<?= h($q) ?>" placeholder="Search current report"/>
        </div>

        <div class="field actions">
          <button class="btn btn-primary" type="submit">Run</button>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/reports.php?type=<?= h($type) ?>">Reset</a>
          <a class="btn btn-ghost" href="<?= h($exportHref) ?>">Export CSV</a>
        </div>
      </form>
    </article>

    <aside class="reports-summary">
      <div class="summary-card card-soft">
        <div class="summary-head">
          <div>
            <h3>Administrative Summary</h3>
            <div class="sub">Current reporting window</div>
          </div>
          <span class="pill">LIVE</span>
        </div>
        <div class="statgrid">
          <?php foreach ($cards as $c): ?>
            <div class="stat">
              <div class="stat__label"><?= h($c['label']) ?></div>
              <div class="stat__value"><?= h((string)$c['value']) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </aside>
  </section>

  <section class="reports-main">
    <article class="panel panel-list card-soft">
      <div class="panel-head">
        <div>
          <h2><?= h($headline) ?></h2>
          <div class="sub"><?= h($subline) ?></div>
        </div>
        <div class="panel-tools">
          <span class="pill"><?= h((string)count($reportRows)) ?> rows</span>
          <a href="<?= h($exportHref) ?>">Download CSV</a>
        </div>
      </div>

      <?php if (!$reportRows): ?>
        <div class="activity-item">
          <div class="activity-item__icon">•</div>
          <div>
            <div class="activity-item__title">No rows found</div>
            <div class="activity-item__meta">Try a wider date range or fewer filters.</div>
          </div>
        </div>
      <?php else: ?>
        <div class="activity-list">
          <?php foreach ($reportRows as $r): ?>
            <div class="activity-item">
              <div class="activity-item__icon">•</div>
              <div class="reports-row-body">
                <?php if ($type === 'users'): ?>
                  <div class="activity-item__title"><?= h((string)$r['username']) ?><?= ((int)$r['is_guest'] === 1) ? ' (guest)' : '' ?></div>
                  <div class="activity-item__meta">
                    <?= h((string)$r['email']) ?>
                    <?php if (!empty($r['display_name'])): ?> • <?= h((string)$r['display_name']) ?><?php endif; ?>
                    • Approval: <?= h((string)$r['approval_status']) ?>
                    • Active: <?= ((int)$r['is_active'] === 1) ? 'Yes' : 'No' ?>
                  </div>
                  <div class="activity-item__meta">
                    Verified: <?= !empty($r['email_verified_at']) ? 'Yes' : 'No' ?>
                    • 2FA: <?= ((int)$r['twofa_enabled'] === 1) ? 'Yes' : 'No' ?>
                    • Bank: <?= h((string)$r['bank_link_status']) ?>
                    • Ranked: <?= ((int)$r['ranked_ready'] === 1) ? 'Ready' : 'Blocked' ?>
                    • Created: <?= h((string)$r['created_at']) ?>
                  </div>
                <?php elseif ($type === 'security'): ?>
                  <div class="activity-item__title"><?= ((int)$r['success'] === 1) ? 'Login success' : 'Login failed' ?> — <?= h((string)$r['identifier']) ?></div>
                  <div class="activity-item__meta">
                    User ID: <?= h((string)($r['user_id'] ?? '—')) ?>
                    • IP: <?= h((string)($r['ip'] ?? '—')) ?>
                    • At: <?= h((string)$r['created_at']) ?>
                  </div>
                  <div class="activity-item__meta">
                    Reason: <?= h((string)($r['failure_reason'] ?? '—')) ?>
                    <?php if (!empty($r['user_agent'])): ?> • Agent: <?= h((string)$r['user_agent']) ?><?php endif; ?>
                  </div>
                <?php elseif ($type === 'audit'): ?>
                  <div class="activity-item__title"><?= h((string)$r['action']) ?></div>
                  <div class="activity-item__meta">
                    Actor: <?= h((string)($r['actor_user_id'] ?? 'system')) ?>
                    • Target: <?= h((string)$r['target_type']) ?> #<?= h((string)($r['target_id'] ?? '—')) ?>
                    • At: <?= h((string)$r['created_at']) ?>
                  </div>
                  <?php if (!empty($r['metadata_json'])): ?>
                    <div class="activity-item__meta">Metadata: <?= h((string)$r['metadata_json']) ?></div>
                  <?php endif; ?>
                <?php elseif ($type === 'game'): ?>
                  <div class="activity-item__title"><?= h((string)$r['username']) ?><?= ((int)$r['ranked_ready'] === 1) ? ' — Ranked Ready' : ' — Access Review' ?></div>
                  <div class="activity-item__meta">
                    <?= h((string)$r['email']) ?>
                    • Guest: <?= ((int)$r['is_guest'] === 1) ? 'Yes' : 'No' ?>
                    • Approval: <?= h((string)$r['approval_status']) ?>
                    • Active: <?= ((int)$r['is_active'] === 1) ? 'Yes' : 'No' ?>
                  </div>
                  <div class="activity-item__meta">
                    Verified: <?= !empty($r['email_verified_at']) ? 'Yes' : 'No' ?>
                    • 2FA: <?= ((int)$r['twofa_enabled'] === 1) ? 'Yes' : 'No' ?>
                    • Bank: <?= h((string)$r['bank_link_status']) ?>
                    • Last login: <?= h((string)($r['last_login_at'] ?? '—')) ?>
                  </div>
                <?php else: ?>
                  <div class="activity-item__title"><?= h((string)$r['username']) ?> — <?= h((string)$r['bank_link_status']) ?></div>
                  <div class="activity-item__meta">
                    <?= h((string)$r['email']) ?>
                    • Approval: <?= h((string)$r['approval_status']) ?>
                    • Active: <?= ((int)$r['is_active'] === 1) ? 'Yes' : 'No' ?>
                    • Verified: <?= !empty($r['email_verified_at']) ? 'Yes' : 'No' ?>
                  </div>
                  <div class="activity-item__meta">
                    2FA: <?= ((int)$r['twofa_enabled'] === 1) ? 'Yes' : 'No' ?>
                    • Ranked: <?= ((int)$r['ranked_ready'] === 1) ? 'Ready' : 'Blocked' ?>
                    • Last login: <?= h((string)($r['last_login_at'] ?? '—')) ?>
                    • Created: <?= h((string)$r['created_at']) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </article>

    <aside class="reports-side">
      <div class="panel card-soft">
        <div class="panel-head">
          <div>
            <h3>Global Totals</h3>
            <div class="sub">Cross-dashboard metrics</div>
          </div>
          <a href="<?= h($exportHref) ?>">Open export</a>
        </div>
        <table class="micro-table">
          <tr><td>All users</td><td><?= h((string)$totalUsers) ?></td></tr>
          <tr><td>Guest accounts</td><td><?= h((string)$guestUsers) ?></td></tr>
          <tr><td>Verified email</td><td><?= h((string)$verifiedUsers) ?></td></tr>
          <tr><td>2FA enabled</td><td><?= h((string)$twofaUsers) ?></td></tr>
          <tr><td>Bank linked</td><td><?= h((string)$linkedBanks) ?></td></tr>
          <tr><td>Audit in window</td><td><?= h((string)$auditCount) ?></td></tr>
        </table>
      </div>

      <div class="panel card-soft">
        <div class="panel-head">
          <div>
            <h3>Report Focus</h3>
            <div class="sub"><?= h($headline) ?></div>
          </div>
          <span class="pill">CSV</span>
        </div>

        <div class="stack">
          <?php if ($type === 'users'): ?>
            <div class="metric-card"><div class="metric-card__label">Approved users</div><div class="metric-card__value"><?= h((string)$approvedUsers) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Pending approvals</div><div class="metric-card__value"><?= h((string)$pendingUsers) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Verified email</div><div class="metric-card__value"><?= h((string)$verifiedUsers) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Rows loaded</div><div class="metric-card__value"><?= h((string)count($reportRows)) ?></div></div>
          <?php elseif ($type === 'security'): ?>
            <div class="metric-card"><div class="metric-card__label">Failed logins</div><div class="metric-card__value"><?= h((string)$failedLogins) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Successful logins</div><div class="metric-card__value"><?= h((string)$successLogins) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">2FA enabled</div><div class="metric-card__value"><?= h((string)$twofaUsers) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Restricted accounts</div><div class="metric-card__value"><?= h((string)$bannedUsers) ?></div></div>
          <?php elseif ($type === 'audit'): ?>
            <div class="metric-card"><div class="metric-card__label">Audit entries</div><div class="metric-card__value"><?= h((string)$auditCount) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Failed logins</div><div class="metric-card__value"><?= h((string)$failedLogins) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">New users</div><div class="metric-card__value"><?= h((string)$newUsers) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Rows loaded</div><div class="metric-card__value"><?= h((string)count($reportRows)) ?></div></div>
          <?php elseif ($type === 'game'): ?>
            <div class="metric-card"><div class="metric-card__label">Ranked ready</div><div class="metric-card__value"><?= h((string)$rankedReadyCount) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Guest accounts</div><div class="metric-card__value"><?= h((string)$guestUsers) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Approved users</div><div class="metric-card__value"><?= h((string)$approvedUsers) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Recent successful logins</div><div class="metric-card__value"><?= h((string)$successLogins) ?></div></div>
          <?php else: ?>
            <div class="metric-card"><div class="metric-card__label">Bank linked</div><div class="metric-card__value"><?= h((string)$linkedBanks) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Verified accounts</div><div class="metric-card__value"><?= h((string)$verifiedUsers) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">2FA enabled</div><div class="metric-card__value"><?= h((string)$twofaUsers) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Ranked ready</div><div class="metric-card__value"><?= h((string)$rankedReadyCount) ?></div></div>
          <?php endif; ?>
        </div>
      </div>
    </aside>
  </section>
</main>

<footer class="sitefooter">
  <div class="sitefooter__inner">
    <div>
      <div class="footbrand">Logia Administration</div>
      <div class="footmuted">Reports • Analytics • CSV export</div>
    </div>
  </div>
</footer>
</body>
</html>