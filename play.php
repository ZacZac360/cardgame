<?php
// play.php
session_start();

require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();

$bp = base_path();
$u  = current_user();

$is_guest = ((int)($u['is_guest'] ?? 0) === 1);

$username = $u['username'] ?? $u['display_name'] ?? 'Player';
$approval = (string)($u['approval_status'] ?? 'pending');
$emailVerified = !empty($u['email_verified_at']);
$bankStatus = (string)($u['bank_link_status'] ?? 'none');

$rankedReady = ($approval === 'approved' && $emailVerified && $bankStatus === 'linked');

$rankedChecks = [
  ['label' => 'Account approved', 'ok' => $approval === 'approved'],
  ['label' => 'Email verified', 'ok' => $emailVerified],
  ['label' => 'Credits linked', 'ok' => $bankStatus === 'linked'],
];

ui_header("Play");
?>

<section class="section" style="padding-top:0;">
  <div class="hub-grid">

    <!-- LEFT -->
    <aside class="card hub-left" style="padding:14px; position:sticky; top:86px;">
      <div style="font-weight:950; letter-spacing:.02em; opacity:.9; margin-bottom:10px;">
        MENU
      </div>

      <div style="display:grid; gap:10px;">
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
          <div class="hub-item" style="opacity:.55; cursor:not-allowed;">
            <span class="hub-ico">🛒</span>
            <span>Shop (Login)</span>
          </div>
        <?php endif; ?>

        <a class="hub-item" href="<?= h($bp) ?>/profile.php?tab=overview">
          <span class="hub-ico">⚙️</span>
          <span>Options</span>
        </a>
      </div>

      <div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,.08);">
        <span class="pill"><?= $is_guest ? "Guest" : "Player" ?></span>

        <div style="margin-top:10px;">
          <span class="pill" style="border-color: <?= $rankedReady ? 'rgba(57,255,106,.35)' : 'rgba(255,77,109,.40)' ?>; background: <?= $rankedReady ? 'rgba(57,255,106,.10)' : 'rgba(255,77,109,.10)' ?>;">
            <?= $rankedReady ? 'Ranked Unlocked' : 'Ranked Locked' ?>
          </span>
          <?php if (!$rankedReady): ?>
            <div style="margin-top:8px; color: var(--muted); font-size:13px; line-height:1.4;">
              Finish account checks to unlock Ranked.
            </div>
          <?php endif; ?>
        </div>
      </div>
    </aside>

    <!-- CENTER -->
    <main style="min-width:0;">

      <div style="margin-top:12px; display:grid; gap:12px;">
        <section class="route-grid">
          <article class="route-card route-card--play">
            <div class="route-card__top">
              <span class="pill">Casual</span>
            </div>
            <h3>Quick Match</h3>
            <p>Fast entry into open matchmaking with the shortest path to a live table.</p>

            <div class="route-card__actions">
              <button class="btn btn-primary" type="button">Find Match</button>
            </div>
          </article>

          <article class="route-card route-card--room" id="rooms">
            <div class="route-card__top">
              <span class="pill">Custom</span>
            </div>
            <h3>Rooms</h3>
            <p>Create your own table or join one directly with a room code and optional password.</p>

            <div class="route-card__actions">
              <a class="btn btn-ghost" href="#room-panel">Go to Rooms</a>
            </div>
          </article>

          <article class="route-card route-card--ranked <?= $rankedReady ? '' : 'is-locked' ?>" id="ranked">
            <div class="route-card__top">
              <span class="pill <?= $rankedReady ? 'pill-good' : 'pill-warn' ?>">
                <?= $rankedReady ? 'Competitive' : 'Locked' ?>
              </span>
            </div>
            <h3>Ranked Queue</h3>
            <p>Competitive matchmaking for verified accounts that have completed access requirements.</p>

            <div class="route-card__actions">
              <button class="btn <?= $rankedReady ? 'btn-primary' : 'btn-ghost' ?>" type="button">
                <?= $rankedReady ? 'Queue Ranked' : 'View Requirements' ?>
              </button>
            </div>
          </article>
        </section>

        <div class="card" style="padding:14px;" id="room-panel">
          <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <div>
              <div style="font-weight:950;">Rooms</div>
              <div style="color: var(--muted); font-size:13px; margin-top:4px;">
                Private and public table entry
              </div>
            </div>
          </div>

          <div class="split-grid" style="margin-top:12px;">
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
                  <input id="room_mode" type="text" placeholder="Casual / Ranked / Custom"/>
                </div>
                <div>
                  <label for="room_slots">Player Slots</label>
                  <input id="room_slots" type="text" placeholder="2 / 4 / 6"/>
                </div>
                <div>
                  <label for="room_pass">Password</label>
                  <input id="room_pass" type="password" placeholder="Optional"/>
                </div>
              </div>

              <div class="formrow">
                <button class="btn btn-primary" type="button">Open Room</button>
              </div>
            </article>

            <article class="info-block">
              <div class="info-block__head">
                <h3>Join Room</h3>
                <span class="pill">Code Entry</span>
              </div>

              <div class="form-grid">
                <div>
                  <label for="join_code">Room Code</label>
                  <input id="join_code" type="text" placeholder="LGA-4821"/>
                </div>
                <div>
                  <label for="join_pass">Password</label>
                  <input id="join_pass" type="password" placeholder="Required if protected"/>
                </div>
              </div>

              <div class="formrow">
                <button class="btn btn-ghost" type="button">Join Room</button>
              </div>
            </article>
          </div>
        </div>
      </div>
    </main>

    <!-- RIGHT -->
    <aside class="hub-right">
      <div class="card" style="padding:16px; border-radius: calc(var(--radius) + 10px);">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
          <div>
            <div style="font-weight:950;">Queue Notes</div>
            <div style="color: var(--muted); font-size:13px; margin-top:4px;">
              Matchmaking and access status
            </div>
          </div>
        </div>

        <div style="margin-top:12px; display:grid; gap:10px;">
          <a class="card-soft" href="<?= h($bp) ?>/play.php" style="display:block; padding:12px; text-decoration:none;">
            <div style="font-weight:900;">⚡ Quick Match</div>
            <div style="color: var(--muted); font-size:13px; margin-top:4px;">
              Fastest path into a casual table.
            </div>
          </a>

          <a class="card-soft" href="#room-panel" style="display:block; padding:12px; text-decoration:none;">
            <div style="font-weight:900;">🚪 Rooms</div>
            <div style="color: var(--muted); font-size:13px; margin-top:4px;">
              Best for friend groups, codes, and direct invites.
            </div>
          </a>

          <div class="card-soft" style="display:block; padding:12px;">
            <div style="font-weight:900;">🏅 Ranked</div>
            <div style="color: var(--muted); font-size:13px; margin-top:4px;">
              <?= $rankedReady ? 'Your account is ready for competitive queue.' : 'Complete access checks before ranked opens.' ?>
            </div>
          </div>
        </div>

        <div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,.08);">
          <div class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,255,255,.06);">
            Ranked requirements
          </div>

          <div style="margin-top:10px; display:grid; gap:6px; color: var(--muted); font-size:13px;">
            <?php foreach ($rankedChecks as $check): ?>
              <div style="display:flex; justify-content:space-between;">
                <span><?= h($check['label']) ?></span>
                <span><?= $check['ok'] ? "✅" : "❌" ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </aside>

  </div>
</section>

<?php ui_footer(); ?>