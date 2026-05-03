<?php
// admin/game.php
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
$err = flash_get('err');
$msg = flash_get('msg');

function admin_game_get_setting(mysqli $mysqli, string $key, $default) {
  $stmt = $mysqli->prepare("
    SELECT setting_value
    FROM admin_game_settings
    WHERE setting_key = ?
    LIMIT 1
  ");
  $stmt->bind_param("s", $key);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$row) return $default;

  $raw = (string)$row['setting_value'];
  $decoded = json_decode($raw, true);

  return json_last_error() === JSON_ERROR_NONE ? $decoded : $raw;
}

function admin_game_set_setting(mysqli $mysqli, string $key, string $value): void {
  $stmt = $mysqli->prepare("
    INSERT INTO admin_game_settings (setting_key, setting_value)
    VALUES (?, ?)
    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
  ");
  $stmt->bind_param("ss", $key, $value);
  $stmt->execute();
  $stmt->close();
}

$entryFees = admin_game_get_setting($mysqli, 'ranked_entry_fees', [
  'Bronze' => 100,
  'Silver' => 300,
  'Gold' => 700,
]);

$expMultipliers = admin_game_get_setting($mysqli, 'ranked_exp_base_multipliers', [
  'Bronze' => 1.00,
  'Silver' => 1.25,
  'Gold' => 1.50,
]);

$leagueRequirements = admin_game_get_setting($mysqli, 'ranked_league_requirements', [
  'Bronze' => ['wins' => 0],
  'Silver' => ['wins' => 3],
  'Gold' => ['wins' => 7],
]);

$streakBonus = (float)admin_game_get_setting($mysqli, 'ranked_streak_bonus', '0.03');
$streakCap = (float)admin_game_get_setting($mysqli, 'ranked_streak_bonus_cap', '0.25');
$shopNotice = (string)admin_game_get_setting($mysqli, 'shop_notice', 'Shop prices and packs can be configured here.');
$eventNotice = (string)admin_game_get_setting($mysqli, 'active_event_notice', 'No active event.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? '');

  if ($action === 'save_ranked') {
    $nextFees = [
      'Bronze' => max(0, (int)($_POST['fee_bronze'] ?? 100)),
      'Silver' => max(0, (int)($_POST['fee_silver'] ?? 300)),
      'Gold' => max(0, (int)($_POST['fee_gold'] ?? 700)),
    ];

    $nextMultipliers = [
      'Bronze' => max(1.0, (float)($_POST['mult_bronze'] ?? 1.00)),
      'Silver' => max(1.0, (float)($_POST['mult_silver'] ?? 1.25)),
      'Gold' => max(1.0, (float)($_POST['mult_gold'] ?? 1.50)),
    ];

    $nextRequirements = [
      'Bronze' => ['wins' => max(0, (int)($_POST['req_bronze_wins'] ?? 0))],
      'Silver' => ['wins' => max(0, (int)($_POST['req_silver_wins'] ?? 3))],
      'Gold' => ['wins' => max(0, (int)($_POST['req_gold_wins'] ?? 7))],
    ];

    $nextStreakBonus = max(0, (float)($_POST['streak_bonus'] ?? 0.03));
    $nextStreakCap = max(0, (float)($_POST['streak_cap'] ?? 0.25));

    admin_game_set_setting($mysqli, 'ranked_entry_fees', json_encode($nextFees, JSON_UNESCAPED_SLASHES));
    admin_game_set_setting($mysqli, 'ranked_exp_base_multipliers', json_encode($nextMultipliers, JSON_UNESCAPED_SLASHES));
    admin_game_set_setting($mysqli, 'ranked_league_requirements', json_encode($nextRequirements, JSON_UNESCAPED_SLASHES));
    admin_game_set_setting($mysqli, 'ranked_streak_bonus', (string)$nextStreakBonus);
    admin_game_set_setting($mysqli, 'ranked_streak_bonus_cap', (string)$nextStreakCap);

    flash_set('msg', 'Ranked league settings updated.');
    header("Location: {$bp}/admin/game.php");
    exit;
  }

  if ($action === 'save_notices') {
    $nextShopNotice = trim((string)($_POST['shop_notice'] ?? ''));
    $nextEventNotice = trim((string)($_POST['active_event_notice'] ?? ''));

    admin_game_set_setting($mysqli, 'shop_notice', $nextShopNotice);
    admin_game_set_setting($mysqli, 'active_event_notice', $nextEventNotice);

    flash_set('msg', 'Shop and event notes updated.');
    header("Location: {$bp}/admin/game.php");
    exit;
  }
}

$rankedQueueCount = 0;
$res = $mysqli->query("SELECT COUNT(*) c FROM ranked_queue");
if ($res) {
  $rankedQueueCount = (int)($res->fetch_assoc()['c'] ?? 0);
}

$rankedMatches = 0;
$res = $mysqli->query("SELECT COUNT(*) c FROM game_rooms WHERE room_type='ranked'");
if ($res) {
  $rankedMatches = (int)($res->fetch_assoc()['c'] ?? 0);
}

$activeRankedRooms = 0;
$res = $mysqli->query("SELECT COUNT(*) c FROM game_rooms WHERE room_type='ranked' AND status IN ('waiting','playing')");
if ($res) {
  $activeRankedRooms = (int)($res->fetch_assoc()['c'] ?? 0);
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia Admin — Game</title>

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

<main class="container admin-page">
  <?php if ($err): ?>
    <div class="banner banner--bad"><?= h($err) ?></div>
  <?php elseif ($msg): ?>
    <div class="banner banner--good"><?= h($msg) ?></div>
  <?php endif; ?>

  <section class="admin-shell">
    <aside class="admin-side card-soft">
      <div class="admin-side__head">
        <div class="pill">CONTROL PANEL</div>
        <h2>Administration</h2>
        <p>Restricted access</p>
      </div>

      <nav class="admin-menu">
        <a class="admin-menu__item" href="<?= h($bp) ?>/admin/index.php"><span class="admin-menu__icon">◈</span><span>Overview</span></a>
        <a class="admin-menu__item" href="<?= h($bp) ?>/admin/users.php"><span class="admin-menu__icon">👥</span><span>Users</span></a>
        <a class="admin-menu__item" href="<?= h($bp) ?>/admin/reports.php"><span class="admin-menu__icon">📝</span><span>Reports</span></a>
        <a class="admin-menu__item is-active" href="<?= h($bp) ?>/admin/game.php"><span class="admin-menu__icon">🎮</span><span>Game</span></a>
        <a class="admin-menu__item" href="<?= h($bp) ?>/admin/settings.php"><span class="admin-menu__icon">⚙</span><span>Settings</span></a>
      </nav>
    </aside>

    <section class="admin-main">
      <article class="admin-hero card admin-hero--compact">
        <div class="admin-hero__copy">
          <div class="chip">GAME • RANKED • SHOP • EVENTS</div>
          <h1>Game Control Center</h1>
          <p class="lead">
            Edit ranked fees, EXP multipliers, shop notes, and event notices from one admin page.
          </p>

          <div class="hero__notes">
            <span class="note">Queue: <?= h((string)$rankedQueueCount) ?></span>
            <span class="note">Ranked rooms: <?= h((string)$rankedMatches) ?></span>
            <span class="note">Active ranked: <?= h((string)$activeRankedRooms) ?></span>
          </div>
        </div>

        <div class="admin-hero__panel">
          <div class="statpanel">
            <div class="statgrid">
              <div class="stat">
                <div class="stat__label">Queue</div>
                <div class="stat__value"><?= h((string)$rankedQueueCount) ?></div>
              </div>
              <div class="stat">
                <div class="stat__label">Rooms</div>
                <div class="stat__value"><?= h((string)$rankedMatches) ?></div>
              </div>
              <div class="stat">
                <div class="stat__label">Active</div>
                <div class="stat__value"><?= h((string)$activeRankedRooms) ?></div>
              </div>
              <div class="stat">
                <div class="stat__label">Streak Bonus</div>
                <div class="stat__value stat__value--small"><?= h((string)$streakBonus) ?>x</div>
              </div>
            </div>
          </div>
        </div>
      </article>

      <section class="admin-blocks admin-blocks--gap">
        <article class="admin-panel card-soft admin-card-pad">
          <div class="admin-panel__head">
            <h2>Ranked Economy</h2>
            <span class="pill">Entry + EXP</span>
          </div>

          <form method="post" class="admin-form-grid">
            <input type="hidden" name="action" value="save_ranked">

            <div class="admin-form-row">
              <label>Bronze Entry Fee</label>
              <input type="number" name="fee_bronze" value="<?= h((string)($entryFees['Bronze'] ?? 100)) ?>" min="0">
            </div>

            <div class="admin-form-row">
              <label>Silver Entry Fee</label>
              <input type="number" name="fee_silver" value="<?= h((string)($entryFees['Silver'] ?? 300)) ?>" min="0">
            </div>

            <div class="admin-form-row">
              <label>Gold Entry Fee</label>
              <input type="number" name="fee_gold" value="<?= h((string)($entryFees['Gold'] ?? 700)) ?>" min="0">
            </div>

            <div class="admin-form-row">
              <label>Bronze Required Wins</label>
              <input type="number" name="req_bronze_wins" value="<?= h((string)($leagueRequirements['Bronze']['wins'] ?? 0)) ?>" min="0">
            </div>

            <div class="admin-form-row">
              <label>Silver Required Wins</label>
              <input type="number" name="req_silver_wins" value="<?= h((string)($leagueRequirements['Silver']['wins'] ?? 3)) ?>" min="0">
            </div>

            <div class="admin-form-row">
              <label>Gold Required Wins</label>
              <input type="number" name="req_gold_wins" value="<?= h((string)($leagueRequirements['Gold']['wins'] ?? 7)) ?>" min="0">
            </div>

            <div class="admin-form-row">
              <label>Bronze EXP Multiplier</label>
              <input type="number" step="0.01" name="mult_bronze" value="<?= h((string)($expMultipliers['Bronze'] ?? 1.00)) ?>" min="1">
            </div>

            <div class="admin-form-row">
              <label>Silver EXP Multiplier</label>
              <input type="number" step="0.01" name="mult_silver" value="<?= h((string)($expMultipliers['Silver'] ?? 1.25)) ?>" min="1">
            </div>

            <div class="admin-form-row">
              <label>Gold EXP Multiplier</label>
              <input type="number" step="0.01" name="mult_gold" value="<?= h((string)($expMultipliers['Gold'] ?? 1.50)) ?>" min="1">
            </div>

            <div class="admin-form-row">
              <label>Win Streak Bonus Per Win</label>
              <input type="number" step="0.01" name="streak_bonus" value="<?= h((string)$streakBonus) ?>" min="0">
            </div>

            <div class="admin-form-row">
              <label>Win Streak Bonus Cap</label>
              <input type="number" step="0.01" name="streak_cap" value="<?= h((string)$streakCap) ?>" min="0">
            </div>

            <div class="admin-form-actions">
              <button class="btn btn-primary" type="submit">Save Ranked Settings</button>
            </div>
          </form>
        </article>

        <article class="admin-panel card-soft admin-card-pad">
          <div class="admin-panel__head">
            <h2>Shop & Events</h2>
            <span class="pill">Notices</span>
          </div>

          <form method="post" class="admin-form-grid admin-form-grid--one">
            <input type="hidden" name="action" value="save_notices">

            <div class="admin-form-row admin-form-row--textarea">
              <label>Shop Notice</label>
              <textarea name="shop_notice" rows="4"><?= h($shopNotice) ?></textarea>
            </div>

            <div class="admin-form-row admin-form-row--textarea">
              <label>Active Event Notice</label>
              <textarea name="active_event_notice" rows="4"><?= h($eventNotice) ?></textarea>
            </div>

            <div class="admin-form-actions">
              <button class="btn btn-primary" type="submit">Save Shop / Event Notes</button>
            </div>
          </form>
        </article>
      </section>
    </section>
  </section>
</main>
</body>
</html>