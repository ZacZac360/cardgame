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

$type    = trim((string)($_GET['type'] ?? 'users'));
$q       = trim((string)($_GET['q'] ?? ''));
$action  = trim((string)($_GET['action'] ?? ''));
$days    = (int)($_GET['days'] ?? 30);
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = (int)($_GET['per_page'] ?? 10);
$export  = (string)($_GET['export'] ?? '');

$allowedTypes = ['users', 'security', 'audit', 'game', 'financial'];
if (!in_array($type, $allowedTypes, true)) $type = 'users';
if (!in_array($days, [1, 7, 30, 90], true)) $days = 30;
if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 10;

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

function bind_and_count(mysqli $mysqli, string $sql, string $types = '', array $params = []): int {
  $stmt = $mysqli->prepare($sql);
  if ($types !== '') {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return (int)($row['c'] ?? 0);
}

function qscalar(mysqli $mysqli, string $sql): string {
  $res = $mysqli->query($sql);
  $row = $res ? $res->fetch_assoc() : null;
  return (string)($row['v'] ?? '0');
}

function money_fmt($value): string {
  return number_format((float)$value, 2);
}

function report_href(string $bp, string $type, int $days, string $q, string $action, int $page, int $perPage): string {
  $params = [
    'type' => $type,
    'days' => $days,
    'page' => max(1, $page),
    'per_page' => $perPage,
  ];

  if ($q !== '') $params['q'] = $q;
  if ($type === 'audit' && $action !== '') $params['action'] = $action;

  return $bp . '/admin/reports.php?' . http_build_query($params);
}

function tab_href(string $bp, string $type, int $days, string $q, int $perPage, string $action = ''): string {
  $params = [
    'type' => $type,
    'days' => $days,
    'per_page' => $perPage,
  ];

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
$totalRows = 0;
$totalPages = 1;
$offset = 0;

if ($type === 'users') {
  $headline = 'User Reports';
  $subline  = 'Accounts, approvals, verification, and ranked readiness.';

  $baseSql = "
    FROM users u
    LEFT JOIN two_factor_secrets t ON t.user_id = u.id
    WHERE u.created_at >= (NOW() - INTERVAL ? DAY)
  ";

  $params = [$days];
  $types  = 'i';

  if ($q !== '') {
    $baseSql .= " AND (
      u.username LIKE CONCAT('%', ?, '%')
      OR u.email LIKE CONCAT('%', ?, '%')
      OR COALESCE(u.display_name,'') LIKE CONCAT('%', ?, '%')
      OR u.approval_status LIKE CONCAT('%', ?, '%')
      OR COALESCE(u.bank_link_status,'') LIKE CONCAT('%', ?, '%')
    )";
    array_push($params, $q, $q, $q, $q, $q);
    $types .= 'sssss';
  }

  $selectSql = "
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
    {$baseSql}
  ";

  $orderSql = " ORDER BY u.created_at DESC";

  $cards = [
    ['label' => 'Total users',      'value' => $totalUsers],
    ['label' => 'New in window',    'value' => $newUsers],
    ['label' => 'Pending approval', 'value' => $pendingUsers],
    ['label' => 'Ranked ready',     'value' => $rankedReadyCount],
  ];

  $csvHeader = ['ID', 'Username', 'Display Name', 'Email', 'Approval Status', 'Guest', 'Active', 'Email Verified', '2FA Enabled', 'Bank Link Status', 'Ranked Ready', 'Last Login', 'Created At'];
}

if ($type === 'security') {
  $headline = 'Security Reports';
  $subline  = 'Login attempts, failed access, successful access, IPs, and user agents.';

  $baseSql = "
    FROM login_attempts la
    WHERE la.created_at >= (NOW() - INTERVAL ? DAY)
  ";

  $params = [$days];
  $types  = 'i';

  if ($q !== '') {
    $baseSql .= " AND (
      la.identifier LIKE CONCAT('%', ?, '%')
      OR COALESCE(la.failure_reason,'') LIKE CONCAT('%', ?, '%')
      OR COALESCE(la.user_agent,'') LIKE CONCAT('%', ?, '%')
      OR CAST(la.user_id AS CHAR) LIKE CONCAT('%', ?, '%')
      OR INET6_NTOA(la.ip_address) LIKE CONCAT('%', ?, '%')
    )";
    array_push($params, $q, $q, $q, $q, $q);
    $types .= 'sssss';
  }

  $selectSql = "
    SELECT
      la.id,
      la.user_id,
      la.identifier,
      la.success,
      INET6_NTOA(la.ip_address) AS ip,
      la.user_agent,
      la.failure_reason,
      la.created_at
    {$baseSql}
  ";

  $orderSql = " ORDER BY la.created_at DESC";

  $cards = [
    ['label' => 'Failed logins',  'value' => $failedLogins],
    ['label' => 'Login success',  'value' => $successLogins],
    ['label' => '2FA enabled',    'value' => $twofaUsers],
    ['label' => 'Restricted',     'value' => $bannedUsers],
  ];

  $csvHeader = ['ID', 'User ID', 'Identifier', 'Success', 'IP', 'User Agent', 'Failure Reason', 'Created At'];
}

if ($type === 'audit') {
  $headline = 'Audit Reports';
  $subline  = 'Administrative actions and recorded system events.';

  $baseSql = "
    FROM audit_logs a
    WHERE a.created_at >= (NOW() - INTERVAL ? DAY)
  ";

  $params = [$days];
  $types  = 'i';

  if ($action !== '') {
    $baseSql .= " AND a.action = ?";
    $params[] = $action;
    $types .= 's';
  }

  if ($q !== '') {
    $baseSql .= " AND (
      a.action LIKE CONCAT('%', ?, '%')
      OR COALESCE(a.target_type,'') LIKE CONCAT('%', ?, '%')
      OR CAST(a.target_id AS CHAR) LIKE CONCAT('%', ?, '%')
      OR COALESCE(a.metadata_json,'') LIKE CONCAT('%', ?, '%')
      OR CAST(a.actor_user_id AS CHAR) LIKE CONCAT('%', ?, '%')
    )";
    array_push($params, $q, $q, $q, $q, $q);
    $types .= 'sssss';
  }

  $selectSql = "
    SELECT
      a.id,
      a.actor_user_id,
      a.action,
      a.target_type,
      a.target_id,
      a.metadata_json,
      a.created_at
    {$baseSql}
  ";

  $orderSql = " ORDER BY a.created_at DESC";

  $cards = [
    ['label' => 'Audit entries',  'value' => $auditCount],
    ['label' => 'Pending users',  'value' => $pendingUsers],
    ['label' => 'Approved users', 'value' => $approvedUsers],
    ['label' => 'Filtered rows',  'value' => 0],
  ];

  $csvHeader = ['ID', 'Actor User ID', 'Action', 'Target Type', 'Target ID', 'Metadata JSON', 'Created At'];
}

if ($type === 'game') {
  $headline = 'Game Reports';
  $subline  = 'Rooms, match status, player count, ranked profile, and gameplay activity.';

  $baseSql = "
    FROM game_rooms gr
    LEFT JOIN users creator ON creator.id = gr.created_by_user_id
    LEFT JOIN users hoster ON hoster.id = gr.host_user_id
    LEFT JOIN ranked_profiles rp ON rp.user_id = gr.created_by_user_id
    WHERE gr.created_at >= (NOW() - INTERVAL ? DAY)
  ";

  $params = [$days];
  $types  = 'i';

  if ($q !== '') {
    $baseSql .= " AND (
      gr.room_code LIKE CONCAT('%', ?, '%')
      OR COALESCE(gr.room_name,'') LIKE CONCAT('%', ?, '%')
      OR gr.room_type LIKE CONCAT('%', ?, '%')
      OR gr.status LIKE CONCAT('%', ?, '%')
      OR COALESCE(creator.username,'') LIKE CONCAT('%', ?, '%')
      OR COALESCE(hoster.username,'') LIKE CONCAT('%', ?, '%')
      OR COALESCE(rp.rank_tier,'') LIKE CONCAT('%', ?, '%')
    )";
    array_push($params, $q, $q, $q, $q, $q, $q, $q);
    $types .= 'sssssss';
  }

  $selectSql = "
    SELECT
      gr.id,
      gr.room_code,
      gr.room_name,
      gr.room_type,
      gr.visibility,
      gr.status,
      gr.max_players,
      gr.winner_seat,
      gr.created_by_user_id,
      COALESCE(creator.username, 'System') AS creator_username,
      gr.host_user_id,
      COALESCE(hoster.username, 'System') AS host_username,
      gr.started_at,
      gr.finished_at,
      gr.created_at,
      gr.updated_at,
      COALESCE(rp.trophy, 0) AS creator_trophy,
      COALESCE(rp.rank_tier, 'Unranked') AS creator_rank_tier,
      COALESCE(rp.wins, 0) AS creator_wins,
      COALESCE(rp.losses, 0) AS creator_losses,
      (
        SELECT COUNT(*)
        FROM game_room_players grp
        WHERE grp.room_id = gr.id
      ) AS player_count,
      (
        SELECT COUNT(*)
        FROM game_logs gl
        WHERE gl.room_id = gr.id
      ) AS log_count
    {$baseSql}
  ";

  $orderSql = " ORDER BY gr.created_at DESC";

  $gameRoomsWindow = qv($mysqli, "SELECT COUNT(*) c FROM game_rooms WHERE created_at >= ({$cutoffSql})");
  $finishedRoomsWindow = qv($mysqli, "SELECT COUNT(*) c FROM game_rooms WHERE status = 'finished' AND created_at >= ({$cutoffSql})");
  $rankedRoomsWindow = qv($mysqli, "SELECT COUNT(*) c FROM game_rooms WHERE room_type = 'ranked' AND created_at >= ({$cutoffSql})");
  $activeRoomsWindow = qv($mysqli, "SELECT COUNT(*) c FROM game_rooms WHERE status IN ('waiting','playing') AND created_at >= ({$cutoffSql})");

  $cards = [
    ['label' => 'Rooms in window', 'value' => $gameRoomsWindow],
    ['label' => 'Finished matches', 'value' => $finishedRoomsWindow],
    ['label' => 'Ranked rooms',     'value' => $rankedRoomsWindow],
    ['label' => 'Active rooms',     'value' => $activeRoomsWindow],
  ];

  $csvHeader = ['Room ID', 'Room Code', 'Room Name', 'Type', 'Visibility', 'Status', 'Players', 'Max Players', 'Winner Seat', 'Creator', 'Host', 'Rank Tier', 'Trophy', 'Wins', 'Losses', 'Log Count', 'Started At', 'Finished At', 'Created At'];
}

if ($type === 'financial') {
  $headline = 'Financial Reports';
  $subline  = 'Credit top-ups, payment status, PayMongo references, and credited Zeny.';

  $baseSql = "
    FROM credit_topups ct
    JOIN users u ON u.id = ct.user_id
    WHERE ct.created_at >= (NOW() - INTERVAL ? DAY)
  ";

  $params = [$days];
  $types  = 'i';

  if ($q !== '') {
    $baseSql .= " AND (
      u.username LIKE CONCAT('%', ?, '%')
      OR u.email LIKE CONCAT('%', ?, '%')
      OR ct.pack_code LIKE CONCAT('%', ?, '%')
      OR ct.pack_name LIKE CONCAT('%', ?, '%')
      OR ct.status LIKE CONCAT('%', ?, '%')
      OR COALESCE(ct.reference_number,'') LIKE CONCAT('%', ?, '%')
      OR COALESCE(ct.paymongo_payment_id,'') LIKE CONCAT('%', ?, '%')
      OR COALESCE(ct.paymongo_checkout_id,'') LIKE CONCAT('%', ?, '%')
    )";
    array_push($params, $q, $q, $q, $q, $q, $q, $q, $q);
    $types .= 'ssssssss';
  }

  $selectSql = "
    SELECT
      ct.id,
      ct.user_id,
      u.username,
      u.email,
      ct.pack_code,
      ct.pack_name,
      ct.amount_php,
      ct.credits_amount,
      ct.bonus_credits,
      ct.total_credits,
      ct.status,
      ct.reference_number,
      ct.paymongo_checkout_id,
      ct.paymongo_payment_id,
      ct.paymongo_payment_intent_id,
      ct.credited_at,
      ct.paid_at,
      ct.created_at,
      ct.updated_at
    {$baseSql}
  ";

  $orderSql = " ORDER BY ct.created_at DESC";

  $paidTopupsWindow = qv($mysqli, "SELECT COUNT(*) c FROM credit_topups WHERE status = 'paid' AND created_at >= ({$cutoffSql})");
  $pendingTopupsWindow = qv($mysqli, "SELECT COUNT(*) c FROM credit_topups WHERE status = 'pending' AND created_at >= ({$cutoffSql})");
  $paidPhpWindow = qscalar($mysqli, "SELECT COALESCE(SUM(amount_php),0) v FROM credit_topups WHERE status = 'paid' AND created_at >= ({$cutoffSql})");
  $creditsSoldWindow = qv($mysqli, "SELECT COALESCE(SUM(total_credits),0) c FROM credit_topups WHERE status = 'paid' AND created_at >= ({$cutoffSql})");

  $cards = [
    ['label' => 'Paid revenue',   'value' => '₱' . money_fmt($paidPhpWindow)],
    ['label' => 'Paid top-ups',   'value' => $paidTopupsWindow],
    ['label' => 'Pending top-ups','value' => $pendingTopupsWindow],
    ['label' => 'Credits sold',   'value' => $creditsSoldWindow],
  ];

  $csvHeader = ['Top-up ID', 'User ID', 'Username', 'Email', 'Pack Code', 'Pack Name', 'Amount PHP', 'Credits', 'Bonus Credits', 'Total Credits', 'Status', 'Reference Number', 'Checkout ID', 'Payment ID', 'Payment Intent ID', 'Credited At', 'Paid At', 'Created At', 'Updated At'];
}

$totalRows = bind_and_count($mysqli, "SELECT COUNT(*) c {$baseSql}", $types, $params);
$totalPages = max(1, (int)ceil($totalRows / $perPage));
$page = min($page, $totalPages);
$offset = ($page - 1) * $perPage;

$reportRows = bind_and_fetch(
  $mysqli,
  $selectSql . $orderSql . " LIMIT ? OFFSET ?",
  $types . 'ii',
  array_merge($params, [$perPage, $offset])
);

$exportRows = bind_and_fetch(
  $mysqli,
  $selectSql . $orderSql,
  $types,
  $params
);

if ($type === 'audit') {
  foreach ($cards as &$card) {
    if ($card['label'] === 'Filtered rows') {
      $card['value'] = $totalRows;
    }
  }
  unset($card);
}

foreach ($exportRows as $r) {
  if ($type === 'users') {
    $csvRows[] = [
      $r['id'], $r['username'], $r['display_name'], $r['email'], $r['approval_status'],
      $r['is_guest'], $r['is_active'], $r['email_verified_at'], $r['twofa_enabled'],
      $r['bank_link_status'], $r['ranked_ready'], $r['last_login_at'], $r['created_at']
    ];
  }

  if ($type === 'security') {
    $csvRows[] = [
      $r['id'], $r['user_id'], $r['identifier'], $r['success'],
      $r['ip'], $r['user_agent'], $r['failure_reason'], $r['created_at']
    ];
  }

  if ($type === 'audit') {
    $csvRows[] = [
      $r['id'], $r['actor_user_id'], $r['action'], $r['target_type'],
      $r['target_id'], $r['metadata_json'], $r['created_at']
    ];
  }

  if ($type === 'game') {
    $csvRows[] = [
      $r['id'], $r['room_code'], $r['room_name'], $r['room_type'], $r['visibility'],
      $r['status'], $r['player_count'], $r['max_players'], $r['winner_seat'],
      $r['creator_username'], $r['host_username'], $r['creator_rank_tier'],
      $r['creator_trophy'], $r['creator_wins'], $r['creator_losses'],
      $r['log_count'], $r['started_at'], $r['finished_at'], $r['created_at']
    ];
  }

  if ($type === 'financial') {
    $csvRows[] = [
      $r['id'], $r['user_id'], $r['username'], $r['email'], $r['pack_code'], $r['pack_name'],
      $r['amount_php'], $r['credits_amount'], $r['bonus_credits'], $r['total_credits'],
      $r['status'], $r['reference_number'], $r['paymongo_checkout_id'], $r['paymongo_payment_id'],
      $r['paymongo_payment_intent_id'], $r['credited_at'], $r['paid_at'], $r['created_at'], $r['updated_at']
    ];
  }
}

if ($export === 'csv') {
  csv_out('report_' . $type . '_' . date('Ymd_His') . '.csv', $csvHeader, $csvRows);
}

$exportHref = $bp . '/admin/reports.php?' . http_build_query([
  'type'     => $type,
  'days'     => $days,
  'q'        => $q,
  'action'   => ($type === 'audit' ? $action : ''),
  'per_page' => $perPage,
  'export'   => 'csv',
]);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia Admin — Reports</title>

  <link rel="icon" type="image/x-icon" href="<?= h($bp) ?>/assets/brand/favicon.ico"/>
  <link rel="shortcut icon" type="image/x-icon" href="<?= h($bp) ?>/assets/brand/favicon.ico"/>
  <link rel="apple-touch-icon" href="<?= h($bp) ?>/assets/brand/logo.png"/>

  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/hub.css"/>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/adminstyle.css"/>
</head>
<body class="hub adminhub">
<header class="topnav">
  <div class="topnav__inner">
    <a class="logo" href="<?= h($bp) ?>/admin/index.php">
      <img
        src="<?= h($bp) ?>/assets/brand/favicon.ico"
        alt="Logia"
        class="logo__mark logo__mark--image"
      >
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
        <a class="tab <?= $type === 'users' ? 'is-active' : '' ?>" href="<?= h(tab_href($bp, 'users', $days, $q, $perPage)) ?>">Users</a>
        <a class="tab <?= $type === 'security' ? 'is-active' : '' ?>" href="<?= h(tab_href($bp, 'security', $days, $q, $perPage)) ?>">Security</a>
        <a class="tab <?= $type === 'audit' ? 'is-active' : '' ?>" href="<?= h(tab_href($bp, 'audit', $days, $q, $perPage, $action)) ?>">Audit</a>
        <a class="tab <?= $type === 'game' ? 'is-active' : '' ?>" href="<?= h(tab_href($bp, 'game', $days, $q, $perPage)) ?>">Game</a>
        <a class="tab <?= $type === 'financial' ? 'is-active' : '' ?>" href="<?= h(tab_href($bp, 'financial', $days, $q, $perPage)) ?>">Financial</a>
      </div>

    <form method="get" class="reports-toolbar reports-toolbar--<?= h($type) ?>">
        <input type="hidden" name="type" value="<?= h($type) ?>"/>
        <input type="hidden" name="page" value="1"/>

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
        <?php endif; ?>

        <div class="field">
          <label>Rows</label>
          <select name="per_page">
            <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10 / page</option>
            <option value="25" <?= $perPage === 25 ? 'selected' : '' ?>>25 / page</option>
            <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50 / page</option>
            <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100 / page</option>
          </select>
        </div>

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
          <span class="pill">
            Page <?= h((string)$page) ?> of <?= h((string)$totalPages) ?>
          </span>
          <span class="pill">
            <?= h((string)$totalRows) ?> total row<?= $totalRows === 1 ? '' : 's' ?>
          </span>
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
                  <div class="activity-item__title">
                    Room <?= h((string)$r['room_code']) ?> — <?= h(ucfirst((string)$r['room_type'])) ?> / <?= h(ucfirst((string)$r['status'])) ?>
                  </div>
                  <div class="activity-item__meta">
                    Name: <?= h((string)($r['room_name'] ?? 'Untitled room')) ?>
                    • Players: <?= h((string)$r['player_count']) ?>/<?= h((string)$r['max_players']) ?>
                    • Visibility: <?= h((string)$r['visibility']) ?>
                    • Winner Seat: <?= h((string)($r['winner_seat'] ?? '—')) ?>
                  </div>
                  <div class="activity-item__meta">
                    Creator: <?= h((string)$r['creator_username']) ?>
                    • Host: <?= h((string)$r['host_username']) ?>
                    • Rank: <?= h((string)$r['creator_rank_tier']) ?>
                    • Trophy: <?= h((string)$r['creator_trophy']) ?>
                    • W/L: <?= h((string)$r['creator_wins']) ?>/<?= h((string)$r['creator_losses']) ?>
                    • Logs: <?= h((string)$r['log_count']) ?>
                    • Created: <?= h((string)$r['created_at']) ?>
                  </div>
                <?php else: ?>
                  <div class="activity-item__title">
                    <?= h((string)$r['pack_name']) ?> — <?= h(strtoupper((string)$r['status'])) ?>
                  </div>
                  <div class="activity-item__meta">
                    User: <?= h((string)$r['username']) ?> #<?= h((string)$r['user_id']) ?>
                    • Email: <?= h((string)$r['email']) ?>
                    • Amount: ₱<?= h(money_fmt($r['amount_php'])) ?>
                    • Credits: <?= h((string)$r['total_credits']) ?>
                  </div>
                  <div class="activity-item__meta">
                    Ref: <?= h((string)($r['reference_number'] ?? '—')) ?>
                    • Checkout: <?= h((string)($r['paymongo_checkout_id'] ?? '—')) ?>
                    • Payment: <?= h((string)($r['paymongo_payment_id'] ?? '—')) ?>
                    • Paid: <?= h((string)($r['paid_at'] ?? '—')) ?>
                    • Created: <?= h((string)$r['created_at']) ?>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if ($totalRows > 0): ?>
        <div class="reports-pagination">
          <div class="reports-pagination__info">
            Showing
            <?= h((string)($offset + 1)) ?>
            –
            <?= h((string)min($offset + $perPage, $totalRows)) ?>
            of
            <?= h((string)$totalRows) ?>
          </div>

          <div class="reports-pagination__actions">
            <a
              class="btn btn-ghost <?= $page <= 1 ? 'is-disabled' : '' ?>"
              href="<?= h(report_href($bp, $type, $days, $q, $action, max(1, $page - 1), $perPage)) ?>"
            >
              Previous
            </a>

            <?php
              $startPage = max(1, $page - 2);
              $endPage = min($totalPages, $page + 2);
            ?>

            <?php if ($startPage > 1): ?>
              <a class="btn btn-ghost" href="<?= h(report_href($bp, $type, $days, $q, $action, 1, $perPage)) ?>">1</a>
              <?php if ($startPage > 2): ?>
                <span class="pill">…</span>
              <?php endif; ?>
            <?php endif; ?>

            <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
              <a
                class="btn btn-ghost <?= $p === $page ? 'is-active-page' : '' ?>"
                href="<?= h(report_href($bp, $type, $days, $q, $action, $p, $perPage)) ?>"
              >
                <?= h((string)$p) ?>
              </a>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): ?>
              <?php if ($endPage < $totalPages - 1): ?>
                <span class="pill">…</span>
              <?php endif; ?>
              <a class="btn btn-ghost" href="<?= h(report_href($bp, $type, $days, $q, $action, $totalPages, $perPage)) ?>"><?= h((string)$totalPages) ?></a>
            <?php endif; ?>

            <a
              class="btn btn-ghost <?= $page >= $totalPages ? 'is-disabled' : '' ?>"
              href="<?= h(report_href($bp, $type, $days, $q, $action, min($totalPages, $page + 1), $perPage)) ?>"
            >
              Next
            </a>
          </div>
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
            <div class="metric-card"><div class="metric-card__label">Filtered rows</div><div class="metric-card__value"><?= h((string)$totalRows) ?></div></div>
          <?php elseif ($type === 'security'): ?>
            <div class="metric-card"><div class="metric-card__label">Failed logins</div><div class="metric-card__value"><?= h((string)$failedLogins) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Successful logins</div><div class="metric-card__value"><?= h((string)$successLogins) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">2FA enabled</div><div class="metric-card__value"><?= h((string)$twofaUsers) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Restricted accounts</div><div class="metric-card__value"><?= h((string)$bannedUsers) ?></div></div>
          <?php elseif ($type === 'audit'): ?>
            <div class="metric-card"><div class="metric-card__label">Audit entries</div><div class="metric-card__value"><?= h((string)$auditCount) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Filtered rows</div><div class="metric-card__value"><?= h((string)$totalRows) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Current page</div><div class="metric-card__value"><?= h((string)$page) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Total pages</div><div class="metric-card__value"><?= h((string)$totalPages) ?></div></div>
          <?php elseif ($type === 'game'): ?>
            <div class="metric-card"><div class="metric-card__label">Rooms in window</div><div class="metric-card__value"><?= h((string)($gameRoomsWindow ?? 0)) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Finished matches</div><div class="metric-card__value"><?= h((string)($finishedRoomsWindow ?? 0)) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Ranked rooms</div><div class="metric-card__value"><?= h((string)($rankedRoomsWindow ?? 0)) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Active rooms</div><div class="metric-card__value"><?= h((string)($activeRoomsWindow ?? 0)) ?></div></div>
          <?php else: ?>
            <div class="metric-card"><div class="metric-card__label">Paid revenue</div><div class="metric-card__value">₱<?= h(money_fmt($paidPhpWindow ?? 0)) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Paid top-ups</div><div class="metric-card__value"><?= h((string)($paidTopupsWindow ?? 0)) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Pending top-ups</div><div class="metric-card__value"><?= h((string)($pendingTopupsWindow ?? 0)) ?></div></div>
            <div class="metric-card"><div class="metric-card__label">Credits sold</div><div class="metric-card__value"><?= h((string)($creditsSoldWindow ?? 0)) ?></div></div>
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