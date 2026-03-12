<?php
// dashboard.php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();
$u  = current_user();
$bp = base_path();

$is_guest  = ((int)($u['is_guest'] ?? 0) === 1);
if ($is_guest) {
  header("Location: {$bp}/guest_dashboard.php");
  exit;
}
$req       = ranked_requirements($mysqli, $u);
$ranked_ok = (!$is_guest && !empty($req['ranked_ok']));

$notes  = fetch_notifications($mysqli, (int)$u['id'], 6);
$unread = count_unread_notifications($mysqli, (int)$u['id']);

ui_header("Dashboard");
?>

<section class="section" style="padding-top:0;">
  <div class="hub-grid">

    <!-- LEFT -->
    <aside class="card hub-left" style="padding:14px; position:sticky; top:86px;">
      <div style="font-weight:950; letter-spacing:.02em; opacity:.9; margin-bottom:10px;">
        MENU
      </div>

      <div style="display:grid; gap:10px;">
        <a class="hub-item" href="<?= h($bp) ?>/play.php">
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

        <?php if (!$is_guest && !$ranked_ok): ?>
          <div style="margin-top:10px;">
            <span class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">Ranked Locked</span>
            <div style="margin-top:8px; color: var(--muted); font-size:13px; line-height:1.4;">
              Finish security steps to unlock Ranked.
            </div>
          </div>
        <?php elseif (!$is_guest && $ranked_ok): ?>
          <div style="margin-top:10px;">
            <span class="pill" style="border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10);">Ranked Unlocked</span>
          </div>
        <?php endif; ?>
      </div>
    </aside>

    <!-- CENTER -->
    <main style="min-width:0;">
      <div class="card hub-hero">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; position:relative; z-index:1;">
          <?php if ($is_guest): ?>
            <span class="pill" style="border-color: rgba(255,205,102,.45); background: rgba(255,205,102,.10);">Guest: Casual only</span>
          <?php elseif ($ranked_ok): ?>
            <span class="pill" style="border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10);">Ranked Ready</span>
          <?php else: ?>
            <span class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">Ranked Locked</span>
          <?php endif; ?>
        </div>

        <div style="position:relative; z-index:1;">
          <h2>Weekend Event: “Clash Circuit”</h2>
          <p class="lead" style="margin:0; max-width: 66ch;">
            Featured tournaments, rotating modes, and limited rewards.
          </p>

          <div class="hero-actions">
            <a class="btn btn-primary btn-lg" href="<?= h($bp) ?>/play.php">Play</a>
            <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/events.php">View Events</a>
            <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/rooms.php">Rooms</a>
            <?php if (!$is_guest): ?>
              <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/shop.php">Shop</a>
            <?php endif; ?>
          </div>

          <div style="margin-top:14px; display:flex; flex-wrap:wrap; gap:8px;">
            <span class="note">User: <b><?= h($u['username']) ?></b></span>
            <span class="note">Unread: <b><?= (int)$unread ?></b></span>
            <span class="note">Mode: <b><?= $is_guest ? "Guest" : "Player" ?></b></span>
          </div>
        </div>
      </div>

      <div style="margin-top:12px; display:grid; gap:12px;">
        <!-- Topics -->
        <div class="card" style="padding:14px;">
          <div style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
            <div>
              <div style="font-weight:950;">Topics</div>
              <div style="color: var(--muted); font-size:13px; margin-top:4px;">
                Rotating announcements
              </div>
            </div>
            <div style="display:flex; gap:6px; opacity:.75;">
              <span class="pill" style="padding:3px 10px;">1</span>
              <span class="pill" style="padding:3px 10px; opacity:.5;">2</span>
              <span class="pill" style="padding:3px 10px; opacity:.5;">3</span>
            </div>
          </div>

          <div style="margin-top:12px; display:grid; gap:10px;">
            <a class="card-soft" href="<?= h($bp) ?>/events.php" style="display:block; padding:12px; text-decoration:none;">
              <div style="font-weight:900;">🏆 Tournament Live</div>
              <div style="color: var(--muted); font-size:13px; margin-top:4px;">
                Join brackets, earn rewards, climb the event ladder.
              </div>
            </a>

            <a class="card-soft" href="<?= h($bp) ?>/patch.php" style="display:block; padding:12px; text-decoration:none;">
              <div style="font-weight:900;">🧩 Patch Notes</div>
              <div style="color: var(--muted); font-size:13px; margin-top:4px;">
                Balance tweaks + UI updates.
              </div>
            </a>

            <a class="card-soft" href="<?= h($bp) ?>/shop.php" style="display:block; padding:12px; text-decoration:none;">
              <div style="font-weight:900;">✨ Cosmetics Drop</div>
              <div style="color: var(--muted); font-size:13px; margin-top:4px;">
                New mats, frames, and skins in the shop.
              </div>
            </a>
          </div>
        </div>
      </div>
    </main>

    <!-- RIGHT -->
    <aside class="hub-right">
      <div class="card" style="padding:16px; border-radius: calc(var(--radius) + 10px);">
        <div style="display:flex; align-items:center; justify-content:space-between; gap: 10px;">
          <div>
            <div style="font-weight:950;">Activity</div>
            <div style="color: var(--muted); font-size: 13px; margin-top:4px;">
              Latest notifications & platform events
            </div>
          </div>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/notifications.php">View all</a>
        </div>

        <div style="margin-top:12px; display:grid; gap:10px;">
          <?php if (!$notes): ?>
            <div style="color: var(--muted); font-size: 13px; padding: 10px 0;">
              No notifications yet.
            </div>
          <?php else: ?>
            <?php foreach ($notes as $n): ?>
              <?php
                $icon = notif_icon((string)$n['type']);
                $when = $n['created_at'] ? date("M d • g:i A", strtotime((string)$n['created_at'])) : '';
                $link = (string)($n['link_url'] ?? '');
                $href = $link ? ($bp . $link) : ($bp . "/notifications.php");
              ?>
              <a href="<?= h($href) ?>" class="card-soft" style="display:block; padding:12px; text-decoration:none;">
                <div style="display:flex; gap:10px; align-items:flex-start;">
                  <div style="width:38px; height:38px; border-radius: 14px; display:grid; place-items:center; border:1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.06);">
                    <?= $icon ?>
                  </div>
                  <div style="flex:1; min-width:0;">
                    <div style="font-weight:900; font-size: 13px; color: var(--text);">
                      <?= h($n['title']) ?>
                    </div>
                    <?php if (!empty($n['body'])): ?>
                      <div style="color: var(--muted); font-size: 12px; margin-top:4px; line-height:1.35;">
                        <?= h($n['body']) ?>
                      </div>
                    <?php endif; ?>
                    <div style="color: rgba(238,243,255,.55); font-size: 11px; margin-top:6px;">
                      <?= h($when) ?>
                    </div>
                  </div>
                  <?php if (((int)$n['is_read']) === 0): ?>
                    <span class="pill" style="border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10); font-size:11px;">NEW</span>
                  <?php endif; ?>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <?php if (!$is_guest && !$ranked_ok): ?>
          <div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,.08);">
            <div class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">
              Ranked requirements
            </div>

            <div style="margin-top:10px; display:grid; gap:6px; color: var(--muted); font-size: 13px;">
              <div style="display:flex; justify-content:space-between;"><span>Email verified</span><span><?= $req['email_ok'] ? "✅" : "❌" ?></span></div>
              <div style="display:flex; justify-content:space-between;"><span>2FA enabled</span><span><?= $req['twofa_ok'] ? "✅" : "❌" ?></span></div>
              <div style="display:flex; justify-content:space-between;"><span>Credits linked</span><span><?= $req['bank_ok'] ? "✅" : "❌" ?></span></div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </aside>

  </div>
</section>

<?php ui_footer(); ?>