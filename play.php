<?php
// play.php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();

$bp = base_path();
$u  = current_user();

$is_guest = ((int)($u['is_guest'] ?? 0) === 1);

$username = $u['username'] ?? $u['display_name'] ?? 'Player';

$req = ranked_requirements($mysqli, $u);
$rankedUnlocked = (!$is_guest && !empty($req['ranked_unlocked']));
$rankedReady = (!$is_guest && !empty($req['ranked_ok']));

$rankedChecks = [
  [
    'label' => 'Account approved',
    'ok' => !empty($req['approved_ok']),
    'hint' => 'Approval is handled by the admin review queue.',
    'href' => $bp . '/profile.php?tab=overview',
    'action' => 'Open Profile',
  ],
  [
    'label' => 'Email verified',
    'ok' => !empty($req['email_ok']),
    'hint' => 'Verify your email from the security section.',
    'href' => $bp . '/profile.php?tab=security',
    'action' => 'Verify Email',
  ],
  [
    'label' => '2FA enabled',
    'ok' => !empty($req['twofa_ok']),
    'hint' => 'Turn on 2FA from the security section.',
    'href' => $bp . '/profile.php?tab=security',
    'action' => 'Enable 2FA',
  ],
  [
    'label' => 'Minimum Zeny (' . (int)$req['unlock_threshold'] . ')',
    'ok' => !empty($req['credits_ok']),
    'hint' => 'Top up until you reach the ranked unlock threshold.',
    'href' => $bp . '/shop.php?tab=credits',
    'action' => 'Get Zeny',
  ],
];

$browserRooms = [];
$openRoomCount = 0;

if (isset($mysqli) && $mysqli instanceof mysqli) {
  $countSql = "
    SELECT COUNT(*) AS c
    FROM game_rooms r
    WHERE r.visibility = 'public'
      AND r.status = 'waiting'
  ";

  if ($countRes = $mysqli->query($countSql)) {
    $openRoomCount = (int)($countRes->fetch_assoc()['c'] ?? 0);
    $countRes->close();
  }

  $roomSql = "
    SELECT
      r.id,
      r.room_code,
      r.room_name,
      r.room_type,
      r.visibility,
      r.status,
      r.max_players,
      r.password_hash,
      r.created_at,
      host.player_name AS host_name,
      COUNT(p.id) AS player_count
    FROM game_rooms r
    LEFT JOIN game_room_players p ON p.room_id = r.id
    LEFT JOIN game_room_players host
      ON host.room_id = r.id
     AND host.is_host = 1
    WHERE r.visibility = 'public'
      AND r.status = 'waiting'
    GROUP BY
      r.id, r.room_code, r.room_name, r.room_type, r.visibility,
      r.status, r.max_players, r.password_hash, r.created_at, host.player_name
    ORDER BY player_count DESC, r.created_at DESC
    LIMIT 12
  ";

  if ($roomRes = $mysqli->query($roomSql)) {
    $browserRooms = $roomRes->fetch_all(MYSQLI_ASSOC);
    $roomRes->close();
  }
}

function play_mode_art_url(string $bp, string $slug): ?string {
  $candidates = [
    "/assets/modes/{$slug}.png",
    "/assets/modes/{$slug}.jpg",
    "/assets/modes/{$slug}.jpeg",
    "/assets/modes/{$slug}.webp",
  ];

  foreach ($candidates as $rel) {
    $abs = __DIR__ . $rel;
    if (is_file($abs)) {
      return rtrim($bp, '/') . $rel;
    }
  }

  return null;
}

$quickArt  = play_mode_art_url($bp, 'quick-match');
$roomsArt  = play_mode_art_url($bp, 'rooms');
$rankedArt = play_mode_art_url($bp, 'ranked');

ui_header("Play");
?>

<section class="section section--flush-top">
  <div class="hub-grid play-mode-layout">

    <!-- LEFT -->
    <aside class="card hub-left hub-sidebar">
      <div class="hub-sidebar__title">
        MENU
      </div>

      <div class="hub-sidebar__nav">
        <a class="hub-item is-active" href="<?= h($bp) ?>/play.php">
          <span class="hub-ico">🎮</span>
          <span>Play</span>
        </a>

        <a class="hub-item" href="<?= h($bp) ?>/solo.php">
          <span class="hub-ico">🧪</span>
          <span>Solo</span>
        </a>

        <?php if (!$is_guest): ?>
          <a class="hub-item" href="<?= h($bp) ?>/shop.php">
            <span class="hub-ico">🛒</span>
            <span>Shop</span>
          </a>
        <?php else: ?>
          <div class="hub-item hub-sidebar__login-lock">
            <span class="hub-ico">🛒</span>
            <span>Shop (Login)</span>
          </div>
        <?php endif; ?>

        <a class="hub-item" href="<?= h($bp) ?>/profile.php?tab=overview">
          <span class="hub-ico">⚙️</span>
          <span>Options</span>
        </a>
      </div>

      <div class="hub-sidebar__status">
        <span class="pill"><?= $is_guest ? "Guest" : "Player" ?></span>

        <div class="hub-sidebar__status-block">
          <span class="pill <?= $rankedReady ? 'status-pill--good' : 'status-pill--bad' ?>">
            <?= $rankedReady ? 'Ranked Unlocked' : 'Ranked Locked' ?>
          </span>
          <?php if (!$rankedReady): ?>
            <div class="hub-sidebar__hint">
              Finish account checks to unlock Ranked.
            </div>
          <?php endif; ?>
        </div>
      </div>
    </aside>

    <!-- CENTER -->
    <main class="play-mode-main">
      <section class="play-mode-shell">
        <div class="play-mode-head">
          <div class="play-mode-head__copy">
            <span class="chip">MODE SELECT</span>
            <h1>Select how you want to play.</h1>
            <p>Three paths. Pick your table and go.</p>
          </div>
        </div>

        <section class="play-mode-grid">
          <article class="play-mode-card play-mode-card--quick <?= $quickArt ? 'has-art' : '' ?>"<?= $quickArt ? ' data-mode-art="' . h($quickArt) . '"' : '' ?>>
            <div class="play-mode-card__backdrop"></div>
            <div class="play-mode-card__content">
              <div class="play-mode-card__top">
                <span class="pill">Casual</span>
              </div>

              <div class="play-mode-card__bottom">
                <h2>Quick Match</h2>
                <p>Fastest way into a casual table.</p>
                <button class="btn btn-primary btn-lg" type="button" id="quickMatchBtn">Play Now</button>
              </div>
            </div>
          </article>

          <article class="play-mode-card play-mode-card--rooms <?= $roomsArt ? 'has-art' : '' ?>"<?= $roomsArt ? ' data-mode-art="' . h($roomsArt) . '"' : '' ?>>
            <div class="play-mode-card__backdrop"></div>
            <div class="play-mode-card__content">
              <div class="play-mode-card__top">
                <span class="pill">Custom</span>
                <span class="pill"><?= (int)$openRoomCount ?> public</span>
              </div>

              <div class="play-mode-card__bottom">
                <h2>Rooms</h2>
                <p>Browse lobbies or host your own room.</p>
                <button class="btn btn-ghost btn-lg" type="button" id="roomsOverlayBtn">Open Rooms</button>
              </div>
            </div>
          </article>

          <article class="play-mode-card play-mode-card--ranked <?= $rankedArt ? 'has-art' : '' ?> <?= $rankedUnlocked ? '' : 'is-locked' ?>"<?= $rankedArt ? ' data-mode-art="' . h($rankedArt) . '"' : '' ?>>
            <div class="play-mode-card__backdrop"></div>
            <div class="play-mode-card__content">
              <div class="play-mode-card__top">
                <span class="pill <?= $rankedReady ? 'pill-good' : 'pill-warn' ?>">
                  <?= $rankedReady ? 'Competitive' : ($rankedUnlocked ? 'Need Zeny' : 'Locked') ?>
                </span>
              </div>

              <div class="play-mode-card__bottom">
                <h2>Ranked</h2>
                <p>
                  <?= $rankedReady
                    ? 'Competitive queue is ready.'
                    : ($rankedUnlocked
                        ? 'Ranked is unlocked, but you need ' . (int)$req['entry_fee'] . ' Zeny to enter right now.'
                        : 'High-stakes play with access requirements.') ?>
                </p>
                <?php if ($rankedReady): ?>

                  <button
                    class="btn btn-primary btn-lg"
                    type="button"
                    id="rankedQueueDirectBtn"
                  >
                    Queue Ranked
                  </button>

                <?php else: ?>

                  <button
                    class="btn btn-ghost btn-lg"
                    type="button"
                    id="rankedQueueBtn"
                  >
                    View Requirements
                  </button>

                <?php endif; ?>
              </div>
            </div>
          </article>
        </section>
      </section>
    </main>

    <!-- RIGHT -->
    <aside class="hub-right play-mode-side">
      <div class="card play-side-panel">
        <div class="play-side-panel__head">
          <div>
            <div class="panel-title">Queue Notes</div>
            <div class="panel-sub">
              Matchmaking and access status
            </div>
          </div>
        </div>

        <div class="play-note-list">
          <div class="card-soft play-note-card">
            <div class="text-strong">⚡ Quick Match</div>
            <div class="panel-sub">
              Best for immediate casual entry.
            </div>
          </div>

          <div class="card-soft play-note-card">
            <div class="text-strong">🚪 Rooms</div>
            <div class="panel-sub">
              Best for friend groups and direct room-code entry.
            </div>
          </div>

          <div class="card-soft play-note-card">
            <div class="text-strong">🏅 Ranked</div>
            <div class="panel-sub">
              <?= $rankedReady
                ? 'Your account is ready for competitive queue.'
                : ($rankedUnlocked
                    ? 'Ranked is unlocked. You only need ' . (int)$req['entry_fee'] . ' Zeny to queue.'
                    : 'Missing items will point you to the right page.') ?>
            </div>
          </div>
        </div>

        <div class="play-check-panel">
          <div class="pill pill-soft-danger">
            Ranked requirements
          </div>

          <div class="play-check-list">
            <?php foreach ($rankedChecks as $check): ?>
              <div class="play-check-row">
                <span><?= h($check['label']) ?></span>
                <span><?= $check['ok'] ? "✅" : "❌" ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </aside>

  </div>

  <div class="play-modal" id="quickMatchModal" aria-hidden="true">
    <div class="play-modal__backdrop" data-close-modal></div>
    <div class="play-modal__dialog card" role="dialog" aria-modal="true" aria-labelledby="quickMatchTitle">
      <button class="play-modal__close" type="button" data-close-modal aria-label="Close">×</button>
      <div class="play-modal__body">
        <div class="play-modal__eyebrow">CASUAL ENTRY</div>
        <h3 id="quickMatchTitle">Quick Match</h3>
        <p class="play-modal__lead">
          This route finds the shortest path into a public casual table. If none is waiting, a new one is created and opened for other players.
        </p>

        <div class="play-modal__grid play-modal__grid--three">
          <div class="play-mini-card">
            <span>Queue style</span>
            <strong>Public casual</strong>
          </div>
          <div class="play-mini-card">
            <span>Target size</span>
            <strong>4 players</strong>
          </div>
          <div class="play-mini-card">
            <span>Current open rooms</span>
            <strong><?= (int)$openRoomCount ?></strong>
          </div>
        </div>

        <div class="play-modal__actions">
          <button class="btn btn-primary" type="button" id="quickMatchStartBtn">Find Match</button>
          <button class="btn btn-ghost" type="button" data-close-modal>Cancel</button>
        </div>
        <div class="play-inline-msg" id="quickMatchMsg"></div>
      </div>
    </div>
  </div>

  <div class="play-modal" id="roomsModal" aria-hidden="true">
    <div class="play-modal__backdrop" data-close-modal></div>
    <div class="play-modal__dialog card play-modal__dialog--wide" role="dialog" aria-modal="true" aria-labelledby="roomsTitle">
      <button class="play-modal__close" type="button" data-close-modal aria-label="Close">×</button>
      <div class="play-modal__body">
        <div class="play-modal__eyebrow">CUSTOM LOBBIES</div>
        <div class="play-modal__header-row">
          <div>
            <h3 id="roomsTitle">Rooms</h3>
            <p class="play-modal__lead">Browse public lobbies, create your own room, or jump directly with a room code.</p>
          </div>
          <div class="play-modal__header-pills">
            <span class="pill"><?= (int)$openRoomCount ?> public waiting</span>
            <span class="pill">Private via code</span>
          </div>
        </div>

        <div class="play-tabbar" role="tablist" aria-label="Room actions">
          <button class="play-tabbar__item is-active" type="button" data-play-tab="browse">Browse Rooms</button>
          <button class="play-tabbar__item" type="button" data-play-tab="create">Create Room</button>
          <button class="play-tabbar__item" type="button" data-play-tab="join">Join by Code</button>
        </div>

        <div class="play-tabpane is-active" data-play-pane="browse">
          <div class="play-room-browser">
            <?php if ($browserRooms): ?>
              <?php foreach ($browserRooms as $room): ?>
                <article class="play-room-row card-soft">
                  <div class="play-room-row__main">
                    <div class="play-room-row__title">
                      <?= h($room['room_name'] ?: ('Room ' . $room['room_code'])) ?>
                    </div>
                    <div class="play-room-row__meta">
                      Host: <b><?= h($room['host_name'] ?: 'Unknown host') ?></b>
                      <span class="sep">•</span>
                      Code: <b><?= h($room['room_code']) ?></b>
                    </div>
                    <div class="play-room-row__chips">
                      <span class="pill"><?= h(ucfirst((string)$room['room_type'])) ?></span>
                      <span class="pill"><?= (int)$room['player_count'] ?>/<?= (int)$room['max_players'] ?> players</span>
                      <?php if (!empty($room['password_hash'])): ?>
                        <span class="pill">Password</span>
                      <?php else: ?>
                        <span class="pill">Open join</span>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="play-room-row__side">
                    <button
                      class="btn btn-primary play-room-join-btn"
                      type="button"
                      data-room-code="<?= h($room['room_code']) ?>"
                      data-room-protected="<?= !empty($room['password_hash']) ? '1' : '0' ?>"
                    >
                      <?= !empty($room['password_hash']) ? 'Enter Password' : 'Join Room' ?>
                    </button>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="play-empty-state card-soft">
                <strong>No public rooms are waiting right now.</strong>
                <span>Create one yourself or use Quick Match to spin up a casual table.</span>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="play-tabpane" data-play-pane="create">
          <div class="split-grid">
            <article class="info-block">
              <div class="info-block__head">
                <h3>Create Room</h3>
                <span class="pill">Host</span>
              </div>

              <div class="form-grid">
                <div>
                  <label for="room_name">Room Name</label>
                  <input id="room_name" type="text" placeholder="Night Lobby"/>
                </div>

                <div>
                  <label for="room_mode">Mode</label>
                  <select id="room_mode" class="input">
                    <option value="custom" selected>Custom</option>
                    <option value="casual">Casual</option>
                    <option value="solo">Solo</option>
                    <?php if ($rankedReady): ?>
                      <option value="ranked">Ranked</option>
                    <?php endif; ?>
                  </select>
                </div>

                <div>
                  <label for="room_slots">Player Slots</label>
                  <select id="room_slots" class="input">
                    <option value="2">2 Players</option>
                    <option value="3">3 Players</option>
                    <option value="4" selected>4 Players</option>
                  </select>
                </div>

                <div>
                  <label for="room_preset">Preset</label>
                  <select id="room_preset" class="input">
                    <option value="classic">Classic</option>
                    <option value="pressure">Pressure</option>
                    <option value="chain_clash">Chain Clash</option>
                    <option value="custom" selected>Custom</option>
                  </select>
                </div>

                <div>
                  <label for="room_visibility">Visibility</label>
                  <select id="room_visibility" class="input">
                    <option value="private" selected>Private</option>
                    <option value="public">Public</option>
                  </select>
                </div>

                <div class="form-grid__full">
                  <label for="room_pass">Password</label>
                  <input id="room_pass" type="password" placeholder="Optional"/>
                </div>
              </div>

              <div class="formrow play-formrow-inline">
                <button class="btn btn-primary" type="button" id="createRoomBtn">Open Room</button>
                <div id="createRoomMsg" class="play-inline-msg"></div>
              </div>
            </article>

            <article class="info-block">
              <div class="info-block__head">
                <h3>Preset Notes</h3>
                <span class="pill">Room Setup</span>
              </div>

              <div class="stack-list">
                <div class="stack-card">
                  <strong>Classic</strong>
                  <span>Pass on +2 / +4, no stacking, draw one when you cannot play.</span>
                </div>
                <div class="stack-card">
                  <strong>Pressure</strong>
                  <span>Keep playing even under draw pressure, no stacking, draw until a playable card appears.</span>
                </div>
                <div class="stack-card">
                  <strong>Chain Clash</strong>
                  <span>Keep playing under pressure, +2 stacking enabled, +4 stacking disabled, draw one when blocked.</span>
                </div>
                <div class="stack-card">
                  <strong>Custom</strong>
                  <span>Open the room first, then tune the rule toggles manually from the room tools panel.</span>
                </div>
              </div>
            </article>
          </div>
        </div>

        <div class="play-tabpane" data-play-pane="join">
          <div class="split-grid">
            <article class="info-block">
              <div class="info-block__head">
                <h3>Join Room</h3>
                <span class="pill">Code Entry</span>
              </div>

              <div class="form-grid">
                <div>
                  <label for="join_code">Room Code</label>
                  <input id="join_code" type="text" placeholder="ABCD1234"/>
                </div>
                <div>
                  <label for="join_pass">Password</label>
                  <input id="join_pass" type="password" placeholder="Required if protected"/>
                </div>
              </div>

              <div class="formrow play-formrow-inline">
                <button class="btn btn-ghost" type="button" id="joinRoomBtn">Join Room</button>
                <div id="joinRoomMsg" class="play-inline-msg"></div>
              </div>
            </article>

            <article class="info-block">
              <div class="info-block__head">
                <h3>Join Notes</h3>
                <span class="pill">Private Access</span>
              </div>

              <div class="stack-list">
                <div class="stack-card">
                  <strong>Public rooms</strong>
                  <span>Visible in the browser when they are waiting for players.</span>
                </div>
                <div class="stack-card">
                  <strong>Private rooms</strong>
                  <span>Usually accessed with a direct code and optional password.</span>
                </div>
                <div class="stack-card">
                  <strong>Passworded rooms</strong>
                  <span>Enter the exact code first, then provide the password to enter.</span>
                </div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="play-modal" id="rankedModal" aria-hidden="true">
    <div class="play-modal__backdrop" data-close-modal></div>
    <div class="play-modal__dialog card" role="dialog" aria-modal="true" aria-labelledby="rankedTitle">
      <button class="play-modal__close" type="button" data-close-modal aria-label="Close">×</button>
      <div class="play-modal__body">
        <div class="play-modal__eyebrow">COMPETITIVE ENTRY</div>
        <h3 id="rankedTitle">Ranked Queue</h3>
        <p class="play-modal__lead">
          Queue for competitive play once your account has completed the required access checks.
        </p>

        <div class="play-modal__grid play-modal__grid--three">
          <div class="play-mini-card">
            <span>Current rank</span>
            <strong>Unranked</strong>
          </div>
          <div class="play-mini-card">
            <span>Next rank</span>
            <strong>Bronze I</strong>
          </div>
          <div class="play-mini-card">
            <span>Entry stake</span>
            <strong><?= (int)$req['entry_fee'] ?> Zeny</strong>
          </div>
        </div>

        <div class="play-check-list play-check-list--modal">
          <?php foreach ($rankedChecks as $check): ?>
            <div class="play-check-row play-check-row--modal">
              <div class="play-check-row__copy">
                <span><?= h($check['label']) ?></span>
                <?php if (!$check['ok']): ?>
                  <small><?= h($check['hint']) ?></small>
                <?php else: ?>
                  <small>Requirement complete.</small>
                <?php endif; ?>
              </div>

              <div class="play-check-row__status">
                <span><?= $check['ok'] ? "✅" : "❌" ?></span>
                <?php if (!$check['ok']): ?>
                  <a class="btn btn-ghost btn-sm" href="<?= h($check['href']) ?>"><?= h($check['action']) ?></a>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <?php if (!$rankedReady): ?>
          <div class="play-requirement-help card-soft">
            <strong><?= $rankedUnlocked ? 'Ranked is unlocked' : 'How to unlock Ranked' ?></strong>
            <span>
              <?= $rankedUnlocked
                ? 'You already unlocked Ranked. You just need ' . (int)$req['entry_fee'] . ' Zeny to enter a match right now.'
                : 'Finish the missing checks below and reach ' . (int)$req['unlock_threshold'] . ' Zeny to unlock Ranked.' ?>
            </span>
            <div class="play-requirement-help__actions">
              <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=security">Security</a>
              <a class="btn btn-ghost" href="<?= h($bp) ?>/shop.php?tab=credits">Get Zeny</a>
              <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=overview">Profile</a>
            </div>
          </div>
        <?php endif; ?>

        <div class="play-modal__actions">
          <button class="btn <?= $rankedReady ? 'btn-primary' : 'btn-ghost' ?>" type="button" id="rankedQueueStartBtn">
            <?= $rankedReady ? 'Queue Ranked' : 'Ranked Locked' ?>
          </button>
          <button class="btn btn-ghost" type="button" data-close-modal>Close</button>
        </div>
        <div class="play-inline-msg" id="rankedQueueMsg"></div>
      </div>
    </div>
  </div>
</section>

<script>
(() => {
  document.querySelectorAll('.play-mode-card[data-mode-art]').forEach((card) => {
    const art = card.getAttribute('data-mode-art');
    if (art) card.style.setProperty('--mode-art', `url("${art}")`);
  });

  const BP = <?= json_encode(rtrim($bp, '/')) ?>;
  const rankedUnlocked = <?= $rankedUnlocked ? 'true' : 'false' ?>;
  const rankedReady = <?= $rankedReady ? 'true' : 'false' ?>;
  const rankedEntryFee = <?= (int)$req['entry_fee'] ?>;

  const createBtn = document.getElementById('createRoomBtn');
  const joinBtn = document.getElementById('joinRoomBtn');
  const quickMatchBtn = document.getElementById('quickMatchBtn');
  const quickMatchStartBtn = document.getElementById('quickMatchStartBtn');
  const rankedQueueBtn = document.getElementById('rankedQueueBtn');
  const rankedQueueStartBtn = document.getElementById('rankedQueueStartBtn');
  const roomsOverlayBtn = document.getElementById('roomsOverlayBtn');

  const roomNameEl = document.getElementById('room_name');
  const roomModeEl = document.getElementById('room_mode');
  const roomSlotsEl = document.getElementById('room_slots');
  const roomPresetEl = document.getElementById('room_preset');
  const roomVisibilityEl = document.getElementById('room_visibility');
  const roomPassEl = document.getElementById('room_pass');

  const joinCodeEl = document.getElementById('join_code');
  const joinPassEl = document.getElementById('join_pass');

  const createMsgEl = document.getElementById('createRoomMsg');
  const joinMsgEl = document.getElementById('joinRoomMsg');
  const quickMatchMsgEl = document.getElementById('quickMatchMsg');
  const rankedQueueMsgEl = document.getElementById('rankedQueueMsg');

  const quickMatchModal = document.getElementById('quickMatchModal');
  const roomsModal = document.getElementById('roomsModal');
  const rankedModal = document.getElementById('rankedModal');
  const tabButtons = Array.from(document.querySelectorAll('[data-play-tab]'));
  const tabPanes = Array.from(document.querySelectorAll('[data-play-pane]'));
  const roomJoinButtons = Array.from(document.querySelectorAll('.play-room-join-btn'));

  function setMsg(el, text, isError = false) {
    if (!el) return;
    el.textContent = text || '';
    el.classList.toggle('is-error', !!isError);
    el.classList.toggle('is-good', !isError && !!text);
  }

  async function postJson(url, payload) {
    const res = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload || {})
    });

    let data = {};
    try {
      data = await res.json();
    } catch (e) {
      data = { ok: false, msg: 'Invalid server response.' };
    }

    if (!res.ok || !data.ok) {
      throw new Error(data.msg || 'Request failed.');
    }

    return data;
  }

  function goToRoomFromPayload(data) {
    const url = data?.redirect_url;
    if (url) {
      window.location.href = url;
      return;
    }

    const code = data?.room?.room_code;
    if (code) {
      window.location.href = `${BP}/room.php?code=${encodeURIComponent(code)}`;
      return;
    }

    throw new Error('Room redirect target missing.');
  }

  function openModal(modal) {
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    if (!document.querySelector('.play-modal.is-open')) {
      document.body.style.overflow = '';
    }
  }

  function closeAllModals() {
    document.querySelectorAll('.play-modal.is-open').forEach(closeModal);
  }

  function setTab(which) {
    tabButtons.forEach((btn) => btn.classList.toggle('is-active', btn.dataset.playTab === which));
    tabPanes.forEach((pane) => pane.classList.toggle('is-active', pane.dataset.playPane === which));
  }

  async function createRoom(payload, msgEl) {
    const data = await postJson(`${BP}/api/game/create_room.php`, payload);
    setMsg(msgEl, data.msg || 'Room created.');
    goToRoomFromPayload(data);
  }

  async function joinRoom(payload, msgEl) {
    const data = await postJson(`${BP}/api/game/join_room.php`, payload);
    setMsg(msgEl, data.msg || 'Joined room.');
    goToRoomFromPayload(data);
  }

  document.querySelectorAll('[data-close-modal]').forEach((el) => {
    el.addEventListener('click', () => {
      const modal = el.closest('.play-modal');
      closeModal(modal);
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAllModals();
  });

  tabButtons.forEach((btn) => {
    btn.addEventListener('click', () => setTab(btn.dataset.playTab));
  });

  if (quickMatchBtn) {
    quickMatchBtn.addEventListener('click', () => {
      setMsg(quickMatchMsgEl, '');
      openModal(quickMatchModal);
    });
  }

  if (roomsOverlayBtn) {
    roomsOverlayBtn.addEventListener('click', () => {
      setTab('browse');
      setMsg(createMsgEl, '');
      setMsg(joinMsgEl, '');
      openModal(roomsModal);
    });
  }

  if (rankedQueueBtn) {
    rankedQueueBtn.addEventListener('click', () => {
      setMsg(rankedQueueMsgEl, '');
      openModal(rankedModal);
    });
  }

  roomJoinButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const code = (btn.dataset.roomCode || '').trim().toUpperCase();
      const protectedRoom = btn.dataset.roomProtected === '1';
      if (joinCodeEl) joinCodeEl.value = code;
      if (joinPassEl && !protectedRoom) joinPassEl.value = '';
      setTab('join');
      openModal(roomsModal);
      if (joinPassEl) joinPassEl.focus();
    });
  });

  if (createBtn) {
    createBtn.addEventListener('click', async () => {
      createBtn.disabled = true;
      setMsg(createMsgEl, '');

      try {
        const roomName = (roomNameEl?.value || '').trim();
        const roomType = (roomModeEl?.value || 'custom').trim();
        const maxPlayers = parseInt(roomSlotsEl?.value || '4', 10);
        const presetKey = (roomPresetEl?.value || 'custom').trim();
        const visibility = (roomVisibilityEl?.value || 'private').trim();
        const password = (roomPassEl?.value || '').trim();

        await createRoom({
          room_name: roomName,
          room_type: roomType,
          max_players: maxPlayers,
          preset_key: presetKey,
          visibility,
          password
        }, createMsgEl);
      } catch (err) {
        setMsg(createMsgEl, err.message || 'Failed to create room.', true);
      } finally {
        createBtn.disabled = false;
      }
    });
  }

  if (joinBtn) {
    joinBtn.addEventListener('click', async () => {
      joinBtn.disabled = true;
      setMsg(joinMsgEl, '');

      try {
        const roomCode = (joinCodeEl?.value || '').trim().toUpperCase();
        const password = (joinPassEl?.value || '').trim();

        if (!roomCode) {
          throw new Error('Enter a room code first.');
        }

        await joinRoom({
          room_code: roomCode,
          password
        }, joinMsgEl);
      } catch (err) {
        setMsg(joinMsgEl, err.message || 'Failed to join room.', true);
      } finally {
        joinBtn.disabled = false;
      }
    });
  }

  if (quickMatchStartBtn) {
    quickMatchStartBtn.addEventListener('click', async () => {
      quickMatchStartBtn.disabled = true;
      setMsg(quickMatchMsgEl, 'Searching for an open casual table...');

      try {
        await createRoom({
          room_name: 'Quick Match',
          room_type: 'casual',
          max_players: 4,
          preset_key: 'classic',
          visibility: 'public',
          password: ''
        }, quickMatchMsgEl);
      } catch (err) {
        setMsg(quickMatchMsgEl, err.message || 'Failed to start quick match.', true);
      } finally {
        quickMatchStartBtn.disabled = false;
      }
    });
  }

  async function enterRankedQueue(buttonEl, msgEl) {
    if (!rankedUnlocked) {
      setMsg(msgEl, 'Ranked is still locked for this account.', true);
      return;
    }

    if (!rankedReady) {
      setMsg(msgEl, `You need at least ${rankedEntryFee} Zeny to enter ranked right now.`, true);
      return;
    }

    if (buttonEl) buttonEl.disabled = true;
    setMsg(msgEl, 'Entering ranked queue...');

    try {
      const res = await fetch(`${BP}/api/game/ranked_join.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({})
      });

      const data = await res.json();

      if (!res.ok || !data.ok) {
        throw new Error(data.msg || 'Failed to queue ranked.');
      }

      const roomCode = data?.status?.match?.room_code;
      if (roomCode) {
        window.location.href = `${BP}/room.php?code=${encodeURIComponent(roomCode)}`;
        return;
      }

      window.location.href = `${BP}/ranked.php`;
    } catch (err) {
      setMsg(msgEl, err.message || 'Failed to queue ranked.', true);
      if (buttonEl) buttonEl.disabled = false;
    }
  }

  const rankedQueueDirectBtn = document.getElementById('rankedQueueDirectBtn');

  if (rankedQueueDirectBtn) {
    rankedQueueDirectBtn.addEventListener('click', async () => {
      await enterRankedQueue(rankedQueueDirectBtn, null);
    });
  }

  if (rankedQueueStartBtn) {
    rankedQueueStartBtn.addEventListener('click', async () => {
      await enterRankedQueue(rankedQueueStartBtn, rankedQueueMsgEl);
    });
  }
})();
</script>

<?php ui_footer(); ?>
