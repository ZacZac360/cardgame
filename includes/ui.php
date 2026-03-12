<?php
// includes/ui.php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

function notif_icon(string $type): string {
  return match ($type) {
    'admin_approval'  => '🛡️',
    'security_alert'  => '🔐',
    'credit_update'   => '💳',
    'match_result'    => '🏁',
    'ranked_unlock'   => '🏆',
    default           => '🔔',
  };
}

function fetch_notifications(mysqli $mysqli, int $user_id, int $limit = 8): array {
  $stmt = $mysqli->prepare("
    SELECT id, type, title, body, link_url, is_read, created_at
    FROM dashboard_notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT ?
  ");
  $stmt->bind_param("ii", $user_id, $limit);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  return $rows;
}

function count_unread_notifications(mysqli $mysqli, int $user_id): int {
  $stmt = $mysqli->prepare("
    SELECT COUNT(*) c
    FROM dashboard_notifications
    WHERE user_id = ? AND is_read = 0
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $c = (int)($stmt->get_result()->fetch_assoc()['c'] ?? 0);
  $stmt->close();
  return $c;
}

function ranked_requirements(mysqli $mysqli, array $u): array {
  $uid = (int)$u['id'];

  $stmt = $mysqli->prepare("SELECT is_enabled FROM two_factor_secrets WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $twofa = (int)($stmt->get_result()->fetch_assoc()['is_enabled'] ?? 0);
  $stmt->close();

  $email_ok = !empty($u['email_verified_at']);
  $bank_ok  = (($u['bank_link_status'] ?? 'none') === 'linked');
  $twofa_ok = ($twofa === 1);

  return [
    'email_ok'  => $email_ok,
    'bank_ok'   => $bank_ok,
    'twofa_ok'  => $twofa_ok,
    'ranked_ok' => (($u['approval_status'] ?? '') === 'approved') && $email_ok && $bank_ok && $twofa_ok
  ];
}

function ui_appearance_mode(array $u): string {
  $mode = (string)($u['appearance_mode'] ?? 'default');

  if (!in_array($mode, ['default', 'dark', 'light'], true)) {
    $mode = 'default';
  }

  return $mode;
}

function ui_header(string $title = 'Dashboard', bool $is_hub = true): void {
  global $mysqli;

  $bp = base_path();
  $u  = current_user();
  if (!$u) redirect($bp . "/index.php");

  $uid = (int)$u['id'];
  $unread = count_unread_notifications($mysqli, $uid);

  $is_guest = ((int)($u['is_guest'] ?? 0) === 1);
  $role_label = $is_guest ? 'Guest' : 'Player';

  $level   = (int)($u['level'] ?? 12);
  $exp_pct = (int)($u['exp_pct'] ?? 55);

  $req = ranked_requirements($mysqli, $u);
  $ranked_ok = (!$is_guest && !empty($req['ranked_ok']));

  $dd_notes = fetch_notifications($mysqli, $uid, 6);
  ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title><?= h($title) ?> — Logia</title>

  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/hub.css"/>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/userstyle.css"/>
</head>

<body
  class="<?= $is_hub ? 'hub' : '' ?>"
  data-appearance="<?= h(ui_appearance_mode($u)) ?>"
>

<header class="topnav">
  <div class="topnav__inner" style="display:flex; align-items:center; justify-content:space-between; gap:14px;">

    <!-- Player block -->
    <a href="<?= h($bp) ?>/profile.php" title="Profile & Stats" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
      <div style="
        width:44px; height:44px; border-radius:14px;
        display:grid; place-items:center;
        border:1px solid rgba(255,255,255,.12);
        background: rgba(255,255,255,.06);
        font-weight:950;
      "><?= h(strtoupper(substr((string)$u['username'], 0, 1))) ?></div>

      <div style="display:grid; gap:4px; min-width:0;">
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; min-width:0;">
          <div style="font-weight:950; letter-spacing:.01em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:240px;">
            <?= h($u['username']) ?>
          </div>

          <span class="pill"><?= h($role_label) ?></span>
          <span class="pill" style="opacity:.9;">Lv. <?= (int)$level ?></span>

          <?php if ($is_guest): ?>
            <span class="pill" style="border-color: rgba(255,205,102,.45); background: rgba(255,205,102,.10);">Casual Only</span>
          <?php elseif (!$ranked_ok): ?>
            <span class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">Ranked Locked</span>
          <?php endif; ?>
        </div>

        <div style="display:flex; align-items:center; gap:10px; opacity:.9;">
          <div style="
            width:180px; max-width: 30vw;
            height:8px; border-radius:999px;
            background: rgba(255,255,255,.10);
            overflow:hidden;
            border:1px solid rgba(255,255,255,.10);
          ">
            <i style="display:block; height:100%; width: <?= max(0, min(100, $exp_pct)) ?>%; background: rgba(139,92,255,.55);"></i>
          </div>
          <div style="color: var(--muted); font-size:12px;">Profile & Stats</div>
        </div>
      </div>
    </a>

    <!-- Icons -->
    <div class="md-icons">

      <!-- Notifications dropdown -->
      <div class="md-ico-wrap" data-dd-wrap>
        <button class="md-ico" type="button" data-dd-btn="notif" title="Notifications">🔔</button>
        <?php if ($unread > 0): ?>
          <span class="md-badge"><?= (int)$unread ?></span>
        <?php endif; ?>

        <div class="dd" id="dd-notif" role="menu" aria-hidden="true">
          <div class="dd__head">
            <div>
              <div class="dd__title">Notifications</div>
              <div class="dd__sub">Latest updates</div>
            </div>
            <a class="btn btn-ghost" href="<?= h($bp) ?>/notifications.php">View all</a>
          </div>

          <div class="dd__body">
            <?php if (!$dd_notes): ?>
              <div style="color: var(--muted); font-size: 13px; padding: 6px 2px;">No notifications yet.</div>
            <?php else: ?>
              <?php foreach ($dd_notes as $n): ?>
                <?php
                  $icon = notif_icon((string)$n['type']);
                  $when = $n['created_at'] ? date("M d • g:i A", strtotime((string)$n['created_at'])) : '';
                  $link = (string)($n['link_url'] ?? '');
                  $href = $link ? ($bp . $link) : ($bp . "/notifications.php");
                ?>
                <a href="<?= h($href) ?>" class="card-soft" style="display:block; padding:12px; text-decoration:none;">
                  <div style="display:flex; gap:10px; align-items:flex-start;">
                    <div style="width:36px; height:36px; border-radius: 14px; display:grid; place-items:center; border:1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.06);">
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
        </div>
      </div>

      <!-- Friends placeholder -->
      <?php if (!$is_guest): ?>
        <div class="md-ico-wrap">
          <button class="md-ico" type="button" title="Friends (placeholder)">👥</button>
        </div>
      <?php else: ?>
        <div class="md-ico-wrap" title="Friends (Guest)">
          <div class="md-ico" style="opacity:.55; cursor:not-allowed;">👥</div>
        </div>
      <?php endif; ?>

      <!-- Mail dropdown -->
      <div class="md-ico-wrap" data-dd-wrap>
        <?php if (!$is_guest): ?>
          <button class="md-ico" type="button" data-dd-btn="mail" title="Mailbox">✉️</button>

          <div class="dd" id="dd-mail" role="menu" aria-hidden="true">
            <div class="dd__head">
              <div>
                <div class="dd__title">Mailbox</div>
                <div class="dd__sub">System vs Friends</div>
              </div>
              <a class="btn btn-ghost" href="<?= h($bp) ?>/mailbox.php">View all</a>
            </div>

            <div class="dd__body" style="gap:12px;">
              <div class="ddtabs">
                <span class="pill ddtab is-active" data-mailtab="system">System</span>
                <span class="pill ddtab" data-mailtab="friends">Friends</span>
              </div>

              <div data-mailpanel="system" style="display:grid; gap:10px;">
                <?php if (!$dd_notes): ?>
                  <div style="color: var(--muted); font-size:13px;">No system messages yet.</div>
                <?php else: ?>
                  <?php foreach ($dd_notes as $n): ?>
                    <?php
                      $icon = notif_icon((string)$n['type']);
                      $when = $n['created_at'] ? date("M d • g:i A", strtotime((string)$n['created_at'])) : '';
                    ?>
                    <div class="card-soft" style="padding:12px;">
                      <div style="display:flex; gap:10px; align-items:flex-start;">
                        <div style="width:36px; height:36px; border-radius: 14px; display:grid; place-items:center; border:1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.06);">
                          <?= $icon ?>
                        </div>
                        <div style="flex:1; min-width:0;">
                          <div style="font-weight:900; font-size: 13px;"><?= h($n['title']) ?></div>
                          <div style="color: rgba(238,243,255,.55); font-size: 11px; margin-top:6px;"><?= h($when) ?></div>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </div>

              <div data-mailpanel="friends" style="display:none; gap:10px;">
                <div class="card-soft" style="padding:12px;">
                  <div style="font-weight:900;">DocSeven</div>
                  <div style="color: var(--muted); font-size:13px; margin-top:6px;">“yo queue later?” (placeholder)</div>
                </div>
                <div class="card-soft" style="padding:12px;">
                  <div style="font-weight:900;">PlayerTwo</div>
                  <div style="color: var(--muted); font-size:13px; margin-top:6px;">“gg earlier” (placeholder)</div>
                </div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="md-ico" style="opacity:.55; cursor:not-allowed;" title="Mailbox (Guest)">✉️</div>
        <?php endif; ?>
      </div>

      <!-- Logout -->
      <a class="md-ico-wrap" href="<?= h($bp) ?>/logout.php" title="Logout">
        <div class="md-ico">⎋</div>
      </a>

    </div>
  </div>
</header>

<main class="container" style="padding-top: 18px;">

<script>
(function(){
  "use strict";
  const $  = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

  function closeAllDropdowns(){
    $$(".dd.is-open").forEach(dd => dd.classList.remove("is-open"));
  }

  $$("[data-dd-btn]").forEach(btn => {
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();

      const key = btn.getAttribute("data-dd-btn");
      const dd  = document.getElementById("dd-" + key);
      if(!dd) return;

      const isOpen = dd.classList.contains("is-open");
      closeAllDropdowns();
      if(!isOpen) dd.classList.add("is-open");
    });
  });

  document.addEventListener("click", () => closeAllDropdowns());
  $$(".dd").forEach(dd => dd.addEventListener("click", (e) => e.stopPropagation()));
  document.addEventListener("keydown", (e) => { if(e.key === "Escape") closeAllDropdowns(); });

  // Mail tabs
  const mail = $("#dd-mail");
  if(mail){
    const tabs = $$(".ddtab", mail);
    const panels = {
      system: $("[data-mailpanel='system']", mail),
      friends: $("[data-mailpanel='friends']", mail),
    };

    function setTab(which){
      tabs.forEach(t => t.classList.remove("is-active"));
      const active = tabs.find(t => t.getAttribute("data-mailtab") === which);
      if(active) active.classList.add("is-active");

      if(panels.system)  panels.system.style.display  = (which === "system") ? "grid" : "none";
      if(panels.friends) panels.friends.style.display = (which === "friends") ? "grid" : "none";
    }

    tabs.forEach(t => t.addEventListener("click", () => setTab(t.getAttribute("data-mailtab"))));
    setTab("system");
  }
})();
</script>

<?php
}

function ui_footer(): void {
  $bp = base_path();
  ?>
</main>

<footer class="sitefooter">
  <!-- IMPORTANT: no .container here. hub.css centers this -->
  <div class="sitefooter__inner" style="display:flex; align-items:center; justify-content:space-between; gap:14px;">
    <div>
      <div class="footbrand">Logia</div>
      <div class="footmuted">© <?= date('Y') ?> • Platform shell</div>
    </div>

    <div class="footmuted" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
      <a href="<?= h($bp) ?>/dashboard.php">Dashboard</a>
      <span class="sep">•</span>
      <a href="<?= h($bp) ?>/notifications.php">Notifications</a>
      <span class="sep">•</span>
      <a href="<?= h($bp) ?>/mailbox.php">Mailbox</a>
      <span class="sep">•</span>
      <a href="<?= h($bp) ?>/logout.php">Logout</a>
    </div>
  </div>
</footer>

<script src="<?= h($bp) ?>/assets/main.js"></script>
</body>
</html>
<?php
}