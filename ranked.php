<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/game_helpers.php';
require_once __DIR__ . '/includes/ui.php';

$user = current_user();
if (!$user) {
  header('Location: index.php');
  exit;
}

$bp = defined('BASE_PATH') ? BASE_PATH : '';
$pageTitle = 'Ranked Queue';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="<?= htmlspecialchars($bp) ?>/assets/style.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($bp) ?>/assets/userstyle.css">
  <link rel="stylesheet" href="<?= htmlspecialchars($bp) ?>/assets/game-room.css">
</head>

<body data-appearance="<?= htmlspecialchars((string)($user['appearance_mode'] ?? 'default')) ?>">
<?php if (function_exists('render_topnav')) render_topnav($user); ?>

<main class="game-room-shell ranked-page-shell" data-base-path="<?= htmlspecialchars($bp) ?>">
  <section class="game-room-top ranked-hero">
    <div>
      <div class="game-room-eyebrow">Ranked Mode</div>
      <h1 class="game-room-title">Find a Ranked Match</h1>
      <div class="game-room-sub">
        Pay the entry fee, enter queue, and fight 4-player ranked. No host. No rule edits.
      </div>
    </div>

    <div class="ranked-hero__badge" id="rankedTierBadge">Loading...</div>
  </section>

  <section class="ranked-grid">
    <div class="ranked-card ranked-card--main">
      <div class="ranked-card__label">Your Rank</div>

      <div class="ranked-rankline">
        <div>
          <div class="ranked-rankline__tier" id="rankedTier">Loading</div>
          <div class="ranked-rankline__sub" id="rankedTrophy">0 trophies</div>
        </div>

        <div class="ranked-rankline__fee">
          <span>Entry</span>
          <strong id="rankedEntryFee">0 Zeny</strong>
        </div>
      </div>

      <div class="ranked-stats">
        <div>
          <span>Wins</span>
          <strong id="rankedWins">0</strong>
        </div>
        <div>
          <span>Losses</span>
          <strong id="rankedLosses">0</strong>
        </div>
        <div>
          <span>Streak</span>
          <strong id="rankedStreak">0</strong>
        </div>
        <div>
          <span>EXP Mult.</span>
          <strong id="rankedExpMult">1.00x</strong>
        </div>
      </div>
    </div>

    <div class="ranked-card">
      <div class="ranked-card__label">Queue</div>

      <div class="ranked-queue-status" id="rankedQueueStatus">
        Not queued.
      </div>

      <div class="ranked-timer" id="rankedTimer">00:00</div>

      <div class="ranked-actions">
        <button type="button" class="ui-btn ui-btn--primary" id="rankedJoinBtn">Enter Ranked Queue</button>
        <button type="button" class="ui-btn ui-btn--ghost" id="rankedCancelBtn">Cancel Queue</button>
        <a class="ui-btn ui-btn--ghost ranked-link-btn" href="<?= htmlspecialchars($bp) ?>/play.php">Back</a>
      </div>

      <p class="ranked-note" id="rankedMessage"></p>
    </div>
  </section>
</main>

<script src="<?= htmlspecialchars($bp) ?>/assets/ranked.js"></script>
</body>
</html>