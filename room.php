<?php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";
require_once __DIR__ . "/includes/game_helpers.php";

require_login();

$u = current_user();
$bp = base_path();

$isGuest = ((int)($u['is_guest'] ?? 0) === 1);
$roomBackUrl = $bp . ($isGuest ? '/guest_dashboard.php' : '/play.php');
$roomBackLabel = $isGuest ? 'Back to Guest Dashboard' : 'Back to Play';

$roomCode = strtoupper(trim((string)($_GET['code'] ?? '')));
if ($roomCode === '') {
  $_SESSION['flash_error'] = 'Room code is required.';
  header("Location: {$roomBackUrl}");
  exit;
}

$room = game_get_room_by_code($mysqli, $roomCode);
if (!$room) {
  $_SESSION['flash_error'] = 'Room not found.';
  header("Location: {$roomBackUrl}");
  exit;
}

$me = game_get_room_player_by_user($mysqli, (int)$room['id'], (int)$u['id']);
if (!$me) {
  $_SESSION['flash_error'] = 'You are not part of that room.';
  header("Location: {$roomBackUrl}");
  exit;
}

ui_header("Room " . $roomCode);
?>

<link rel="stylesheet" href="<?= h($bp) ?>/assets/game-room.css">

<section class="section section--flush-top">
  <div class="game-room-shell"
       data-room-code="<?= h($roomCode) ?>"
       data-base-path="<?= h($bp) ?>"
       data-is-guest="<?= $isGuest ? '1' : '0' ?>">

    <div class="game-room-top card-soft">
      <div>
        <div class="game-room-eyebrow">Live Match</div>
        <h1 class="game-room-title">
          <?= h($room['room_name'] ?: ('Room ' . $roomCode)) ?>
        </h1>
        <div class="game-room-sub">
          Code: <strong><?= h($roomCode) ?></strong>
          · Type: <strong><?= h((string)$room['room_type']) ?></strong>
        </div>
      </div>

      <div class="game-room-top__actions">
        <button class="btn btn-ghost" type="button" data-guide-open="game-rules">
          Guide
        </button>
        <a class="btn btn-ghost" href="<?= h($roomBackUrl) ?>">
          <?= h($roomBackLabel) ?>
        </a>
      </div>
    </div>

    <main class="game-table-wrap">
      <header class="top-strip">
        <div class="top-strip__brand">
          <div class="eyebrow">Logia Match</div>
          <h1>Elemental Table</h1>
        </div>

        <div class="status-strip">
          <div class="status-chip">
            <span class="status-chip__label">Room</span>
            <span class="status-chip__value"><?= h($roomCode) ?></span>
          </div>

          <div class="status-chip">
            <span class="status-chip__label">Status</span>
            <span class="status-chip__value" id="roomStatusValue">Loading</span>
          </div>

          <div class="status-chip">
            <span class="status-chip__label">Mode</span>
            <span class="status-chip__value" id="roomModeValue">-</span>
          </div>

          <div class="status-chip status-chip--turn">
            <span class="status-chip__label">Turn</span>
            <span class="status-chip__value" id="turnValue">-</span>
          </div>

          <div class="status-chip">
            <span class="status-chip__label">You</span>
            <span class="status-chip__value" id="meValue"><?= h($u['username'] ?? 'Player') ?></span>
          </div>
        </div>

        <div class="top-strip__actions">
          <button id="refreshBtn" type="button" class="ui-btn ui-btn--ghost">Refresh</button>
          <button id="logToggleBtn" type="button" class="ui-btn ui-btn--ghost">Log</button>
          <button id="toolsToggleBtn" type="button" class="ui-btn ui-btn--ghost">Tools</button>
        </div>
      </header>

      <section class="table-shell">
        <div class="seat seat-top" id="seat-top"></div>
        <div class="seat seat-left" id="seat-left"></div>
        <div class="seat seat-right" id="seat-right"></div>

        <section class="play-surface">
          <div class="play-surface__glow"></div>
          <div class="play-surface__rim"></div>
          <div id="tableArea" class="table-area"></div>

          <div id="wildChooser" class="wild-chooser hidden">
            <div class="wild-chooser__title">Choose element for +4</div>
            <div class="wild-chooser__buttons">
              <button class="wild-btn" data-wild-element="Fire" type="button">Fire</button>
              <button class="wild-btn" data-wild-element="Water" type="button">Water</button>
              <button class="wild-btn" data-wild-element="Lightning" type="button">Lightning</button>
              <button class="wild-btn" data-wild-element="Earth" type="button">Earth</button>
              <button class="wild-btn" data-wild-element="Wind" type="button">Wind</button>
              <button class="wild-btn" data-wild-element="Wood" type="button">Wood</button>
            </div>
          </div>
        </section>

        <aside id="toolsPanel" class="floating-panel tools-panel hidden">
          <div class="floating-panel__head">
            <h2>Room Tools</h2>
            <button id="toolsCloseBtn" type="button" class="icon-btn" aria-label="Close tools">✕</button>
          </div>

          <div class="tools-section" id="hostControlsSection">
            <div class="tools-section__label">Host Controls</div>

            <div class="mode-buttons">
              <button class="mode-btn" data-mode="2" type="button">2P</button>
              <button class="mode-btn" data-mode="3" type="button">3P</button>
              <button class="mode-btn" data-mode="4" type="button">4P</button>
            </div>

            <div class="tools-section__label" style="margin-top:12px;">Room Rules</div>

            <div id="rulesEditor" class="rules-editor">
              <label class="rule-row">
                <span>Allow AI Fill</span>
                <input id="ruleAllowAiFill" type="checkbox">
              </label>

              <label class="rule-row">
                <span>Starting Hand Size</span>
                <input id="ruleStartingHandSize" type="number" min="3" max="10" step="1" value="5">
              </label>

              <label class="rule-row">
                <span>Allow Stack +2</span>
                <input id="ruleAllowStackPlus2" type="checkbox">
              </label>

              <label class="rule-row">
                <span>Allow Stack +4</span>
                <input id="ruleAllowStackPlus4" type="checkbox">
              </label>

              <label class="rule-row">
                <span>Draw Until Playable</span>
                <input id="ruleDrawUntilPlayable" type="checkbox">
              </label>

              <div class="rules-note">
                Fixed Match EXP:
                Casual / Custom / Solo = 500 / 400 / 300 / 200 by placement,
                Ranked = 1.5x multiplier.
              </div>

              <div class="host-buttons" style="margin-top:10px;">
                <button id="saveRulesBtn" type="button" class="ui-btn">Save Rules</button>
              </div>
            </div>

            <div class="host-buttons">
              <button id="startGameBtn" type="button" class="ui-btn">Start Game</button>
              <button id="resetRoomBtn" type="button" class="ui-btn ui-btn--danger">Reset Room</button>
              <button id="destroyRoomBtn" type="button" class="ui-btn ui-btn--danger">Destroy Room</button>
            </div>

            <div id="actionMsg" class="inline-msg"></div>
          </div>

          <div class="tools-section tools-section--hint">
            <div class="tools-section__label">How to Play</div>
            <div class="hint-list">
              <div>Play a card that matches the active element.</div>
              <div>Or play an element that beats the active element.</div>
              <div>Use +2 to pressure the next player.</div>
              <div>Use +4 to choose the next active element.</div>
              <div>If nothing works, press Pass.</div>
            </div>
          </div>

          <div class="tools-section tools-section--hint">
            <div class="tools-section__label">Controls</div>
            <div class="hint-list">
              <div>Desktop: click = select, double click = play</div>
              <div>Phone: tap = select, tap selected card again = play</div>
              <div>Enter = play selected</div>
              <div>Space = pass</div>
              <div>Esc = clear selection</div>
            </div>
          </div>
        </aside>

        <aside id="logPanel" class="floating-panel log-panel hidden">
          <div class="floating-panel__head">
            <h2>Game Log</h2>
            <button id="logCloseBtn" type="button" class="icon-btn" aria-label="Close log">✕</button>
          </div>
          <div id="logArea" class="log-area"></div>
        </aside>
      </section>

      <section class="hand-dock">
        <div class="hand-dock__top">
          <div class="hand-title-wrap">
            <div class="eyebrow">Your Seat</div>
            <h2>Your Hand</h2>
          </div>

          <div id="humanSummary" class="human-summary"></div>

          <div class="hand-actions">
            <button id="playBtn" type="button" class="ui-btn">Play</button>
            <button id="passBtn" type="button" class="ui-btn ui-btn--primary">Pass</button>
            <button id="leaveRoomBtn" type="button" class="ui-btn ui-btn--ghost">Leave Room</button>
          </div>
          
        </div>

        <div id="handArea" class="hand-area"></div>
      </section>
    </main>
  </div>
</section>

<script src="<?= h($bp) ?>/assets/cards.js"></script>
<script src="<?= h($bp) ?>/assets/game-room.js"></script>

<?php ui_footer(); ?>