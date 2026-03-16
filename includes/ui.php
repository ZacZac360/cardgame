<?php
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/friends_helpers.php';
require_once __DIR__ . '/messages_helpers.php';

function notif_icon(string $type): string {
  return match ($type) {
    'admin_approval'  => '🛡️',
    'security_alert'  => '🔐',
    'credit_update'   => '💳',
    'match_result'    => '🏁',
    'ranked_unlock'   => '🏆',
    'friend_request'  => '👥',
    'friend_accept'   => '✅',
    'message'         => '✉️',
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

  $level   = (int)($u['level'] ?? 1);
  $exp     = (int)($u['exp'] ?? 0);
  $expNext = (int)($u['exp_to_next'] ?? 100);

  $exp_pct = $expNext > 0 ? min(100, round(($exp / $expNext) * 100)) : 0;

  $req = ranked_requirements($mysqli, $u);
  $ranked_ok = (!$is_guest && !empty($req['ranked_ok']));

  $dd_notes       = fetch_notifications($mysqli, $uid, 6);
  $friend_requests = $is_guest ? [] : fetch_pending_friend_requests($mysqli, $uid, 6);
  $friend_unread   = $is_guest ? 0 : count_pending_friend_requests($mysqli, $uid);
  $mail_threads    = $is_guest ? [] : fetch_user_conversations($mysqli, $uid, 6);
  $mail_unread     = $is_guest ? 0 : count_unread_direct_messages($mysqli, $uid);
  $zenyBalance = (int)($u['credits'] ?? 0);
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

  <script>
  window.LOGIA_BASE_PATH = <?= json_encode($bp) ?>;
  window.LOGIA_USER_ID = <?= (int)$uid ?>;
</script>
</head>

<body class="<?= $is_hub ? 'hub' : '' ?>" data-appearance="<?= h(ui_appearance_mode($u)) ?>">

<header class="topnav">
  <div class="topnav__inner" style="display:flex; align-items:center; justify-content:space-between; gap:14px;">

    <a href="<?= h($bp) ?>/profile.php" title="Profile & Stats" style="display:flex; align-items:center; gap:12px; text-decoration:none;">
      <?php $navAvatar = trim((string)($u['avatar_path'] ?? '')); ?>

      <?php if ($navAvatar !== ''): ?>
        <img
          src="<?= h($bp . '/' . ltrim($navAvatar, '/')) ?>"
          alt="Profile Avatar"
          style="width:44px; height:44px; border-radius:14px; object-fit:cover; border:1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.06); display:block;"
        >
      <?php else: ?>
        <div style="width:44px; height:44px; border-radius:14px; display:grid; place-items:center; border:1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.06); font-weight:950;">
          <?= h(strtoupper(substr((string)$u['username'], 0, 1))) ?>
        </div>
      <?php endif; ?>

      <div style="display:grid; gap:4px; min-width:0;">
        <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; min-width:0;">
          <div style="font-weight:950; letter-spacing:.01em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:240px;">
            <?= h($u['display_name'] ?? $u['username']) ?>
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
          <div style="width:180px; max-width:30vw; height:8px; border-radius:999px; background: rgba(255,255,255,.10); overflow:hidden; border:1px solid rgba(255,255,255,.10);">
            <i style="display:block; height:100%; width: <?= max(0, min(100, $exp_pct)) ?>%; background: rgba(139,92,255,.55);"></i>
          </div>
          <div style="color: var(--muted); font-size:12px;">Profile & Stats</div>
        </div>
      </div>
    </a>

    <div class="md-icons">

          <div
          class="md-ico-wrap"
          title="Zeny Balance"
          style="display:flex; align-items:center; gap:8px; padding:0 2px;"
        >
          <a
            href="<?= h($bp) ?>/shop.php?tab=credits"
            style="
              display:inline-flex;
              align-items:center;
              gap:8px;
              text-decoration:none;
              color:var(--text);
              padding:10px 12px;
              border-radius:14px;
              border:1px solid rgba(255,255,255,.10);
              background:rgba(255,255,255,.05);
              font-weight:900;
              line-height:1;
            "
          >
            <span> <?= number_format($zenyBalance) ?> Zeny</span>
          </a>

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

            <div style="display:flex; gap:8px; flex-wrap:wrap; justify-content:flex-end;">
              <button type="button" class="btn btn-ghost" data-notif-read-all="1">Mark all read</button>
              <a class="btn btn-ghost" href="<?= h($bp) ?>/notifications.php">View all</a>
            </div>
          </div>

          <div class="dd__body">
            <?php if (!$dd_notes): ?>
              <div style="color: var(--muted); font-size:13px; padding:6px 2px;">No notifications yet.</div>
            <?php else: ?>
              <?php foreach ($dd_notes as $n): ?>
                <?php
                  $notifId = (int)($n['id'] ?? 0);
                  $icon = notif_icon((string)$n['type']);
                  $when = $n['created_at'] ? date("M d • g:i A", strtotime((string)$n['created_at'])) : '';
                  $link = trim((string)($n['link_url'] ?? ''));
                  $notifType = (string)($n['type'] ?? '');
                  $isUnread = ((int)($n['is_read'] ?? 0) === 0);
                ?>
                <div
                  class="card-soft"
                  data-notif-card
                  data-notif-id="<?= $notifId ?>"
                  data-notif-type="<?= h($notifType) ?>"
                  data-link-url="<?= h($link) ?>"
                  style="display:block; padding:12px;"
                >
                  <div style="display:flex; gap:10px; align-items:flex-start;">
                    <div style="width:36px; height:36px; border-radius:14px; display:grid; place-items:center; border:1px solid rgba(255,255,255,.12); background: rgba(255,255,255,.06);">
                      <?= $icon ?>
                    </div>

                    <div style="flex:1; min-width:0;">
                      <div style="font-weight:900; font-size:13px; color: var(--text);">
                        <?= h($n['title']) ?>
                      </div>

                      <?php if (!empty($n['body'])): ?>
                        <div style="color: var(--muted); font-size:12px; margin-top:4px; line-height:1.35;">
                          <?= h($n['body']) ?>
                        </div>
                      <?php endif; ?>

                      <div style="color: rgba(238,243,255,.55); font-size:11px; margin-top:6px;">
                        <?= h($when) ?>
                      </div>

                      <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
                        <?php if ($link !== ''): ?>
                          <button
                            type="button"
                            class="btn btn-ghost"
                            data-notif-open="1"
                            data-notif-id="<?= $notifId ?>"
                            data-notif-type="<?= h($notifType) ?>"
                            data-link-url="<?= h($link) ?>"
                          >
                            Open
                          </button>
                        <?php endif; ?>

                        <?php if ($isUnread): ?>
                          <button
                            type="button"
                            class="btn"
                            data-notif-read="1"
                            data-notif-id="<?= $notifId ?>"
                          >
                            Mark read
                          </button>
                        <?php endif; ?>
                      </div>
                    </div>

                    <?php if ($isUnread): ?>
                      <span class="pill notif-new-pill" style="border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10); font-size:11px;">NEW</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (!$is_guest): ?>
        <div class="md-ico-wrap" data-dd-wrap>
          <button class="md-ico" type="button" data-dd-btn="friends" title="Friends">👥</button>
          <?php if ($friend_unread > 0): ?>
            <span class="md-badge"><?= (int)$friend_unread ?></span>
          <?php endif; ?>

          <div class="dd" id="dd-friends" role="menu" aria-hidden="true">
            <div class="dd__head">
              <div>
                <div class="dd__title">Friends</div>
                <div class="dd__sub">Requests and connections</div>
              </div>
              <a class="btn btn-ghost" href="<?= h($bp) ?>/friends.php">View all</a>
            </div>

            <div class="dd__body">
              <?php if (!$friend_requests): ?>
                <div style="color: var(--muted); font-size:13px; padding:6px 2px;">No pending friend requests.</div>
              <?php else: ?>
                <?php foreach ($friend_requests as $fr): ?>
                  <?php
                    $requestId = (int)($fr['id'] ?? 0);
                    $senderId  = (int)($fr['sender_id'] ?? 0);
                    $friendName = (string)(($fr['display_name'] ?: $fr['username']) ?? 'Player');
                  ?>
                  <div
                    class="card-soft"
                    data-friend-request-card
                    data-request-id="<?= $requestId ?>"
                    style="display:block; padding:12px;"
                  >
                    <div style="display:flex; gap:10px; align-items:center;">
                      <div style="width:36px; height:36px; border-radius:14px; overflow:hidden; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.06); display:grid; place-items:center;">
                        <?php if (!empty($fr['avatar_path'])): ?>
                          <img src="<?= h($bp . '/' . ltrim((string)$fr['avatar_path'], '/')) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                        <?php else: ?>
                          <?= h(strtoupper(substr((string)($fr['username'] ?? 'U'), 0, 1))) ?>
                        <?php endif; ?>
                      </div>

                      <div style="flex:1; min-width:0;">
                        <div style="font-weight:900; font-size:13px;">
                          <?= h($friendName) ?>
                        </div>
                        <div style="color: var(--muted); font-size:12px; margin-top:4px;">
                          Sent you a friend request
                        </div>

                        <div style="display:flex; gap:8px; margin-top:10px; flex-wrap:wrap;">
                          <button
                            type="button"
                            class="btn"
                            data-friend-accept="1"
                            data-request-id="<?= $requestId ?>"
                            data-sender-id="<?= $senderId ?>"
                          >
                            Accept
                          </button>

                          <button
                            type="button"
                            class="btn btn-ghost"
                            data-friend-reject="1"
                            data-request-id="<?= $requestId ?>"
                            data-sender-id="<?= $senderId ?>"
                          >
                            Reject
                          </button>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="md-ico-wrap" title="Friends (Guest)">
          <div class="md-ico" style="opacity:.55; cursor:not-allowed;">👥</div>
        </div>
      <?php endif; ?>

      <div class="md-ico-wrap" data-dd-wrap>
        <?php if (!$is_guest): ?>
          <button class="md-ico" type="button" data-dd-btn="mail" title="Messages">✉️</button>
          <?php if ($mail_unread > 0): ?>
            <span class="md-badge"><?= (int)$mail_unread ?></span>
          <?php endif; ?>

          <div class="dd" id="dd-mail" role="menu" aria-hidden="true">
            <div class="dd__head">
              <div>
                <div class="dd__title">Messages</div>
                <div class="dd__sub">Recent direct conversations</div>
              </div>
              <a class="btn btn-ghost" href="<?= h($bp) ?>/friends.php">Friends</a>
            </div>

            <div class="dd__body" style="gap:12px;">
              <?php if (!$mail_threads): ?>
                <div style="color: var(--muted); font-size:13px;">No direct messages yet.</div>
              <?php else: ?>
                <?php foreach ($mail_threads as $thread): ?>
                  <?php
                    $other = $thread['other_user'] ?? [];
                    $last  = $thread['last_message'] ?? [];
                    $conversationId = (int)($thread['conversation_id'] ?? 0);
                    $name = (string)($other['display_name'] ?? '');
                    if ($name === '') $name = (string)($other['username'] ?? 'Unknown User');
                  ?>
                  <button
                    type="button"
                    class="card-soft chat-thread-launch"
                    data-chat-open="1"
                    data-conversation-id="<?= $conversationId ?>"
                    style="display:block; width:100%; padding:12px; text-decoration:none; text-align:left; cursor:pointer;"
                  >
                    <div style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                      <div style="font-weight:900; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        <?= h($name) ?>
                      </div>
                      <?php if ((int)($thread['unread_count'] ?? 0) > 0): ?>
                        <span class="pill" style="font-size:11px;"><?= (int)$thread['unread_count'] ?> NEW</span>
                      <?php endif; ?>
                    </div>
                    <div style="color: var(--muted); font-size:13px; margin-top:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                      <?= h((string)($last['body'] ?? 'No messages yet.')) ?>
                    </div>
                  </button>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php else: ?>
          <div class="md-ico" style="opacity:.55; cursor:not-allowed;" title="Messages (Guest)">✉️</div>
        <?php endif; ?>
      </div>

      <a class="md-ico-wrap" href="<?= h($bp) ?>/logout.php" title="Logout">
        <div class="md-ico">⎋</div>
      </a>

    </div>
  </div>
</header>

<main class="container" style="padding-top:18px;">

<?php if (!$is_guest): ?>
  <div id="chatPopup" class="chat-pop" hidden>
    <div class="chat-pop__head">
      <div class="chat-pop__person">
        <div class="chat-pop__avatar" id="chatPopupAvatar">
          <img id="chatPopupAvatarImg" class="chat-pop__avatarimg" src="" alt="" hidden>
          <span id="chatPopupAvatarFallback" class="chat-pop__avatarfallback">C</span>
        </div>

        <div class="chat-pop__identity">
          <div id="chatPopupName" class="chat-pop__name">Chat</div>
          <div id="chatPopupMeta" class="chat-pop__meta">Direct message</div>
        </div>
      </div>

      <div class="chat-pop__actions">
        <button type="button" class="chat-pop__icon" id="chatPopupMinBtn" aria-label="Minimize chat">—</button>
        <button type="button" class="chat-pop__icon" id="chatPopupCloseBtn" aria-label="Close chat">✕</button>
      </div>
    </div>

    <div id="chatPopupBody" class="chat-pop__body"></div>

    <form id="chatPopupForm" class="chat-pop__form">
      <input type="hidden" id="chatPopupReceiverId" value="">
      <input type="hidden" id="chatPopupConversationId" value="">

      <div class="chat-pop__composer">
        <textarea
          id="chatPopupInput"
          class="chat-pop__input"
          rows="1"
          placeholder="Type a message..."
        ></textarea>

        <button type="submit" class="chat-pop__send" aria-label="Send message">➤</button>
      </div>
    </form>
  </div>
<?php endif; ?>

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
})();
</script>

<?php
}

function ui_footer(): void {
  $bp = base_path();
  ?>
</main>

<footer class="sitefooter">
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
      <a href="<?= h($bp) ?>/friends.php">Friends</a>
      <span class="sep">•</span>
      <a href="<?= h($bp) ?>/logout.php">Logout</a>
    </div>
  </div>
</footer>

<?php $u = current_user(); ?>
<script>
window.LOGIA_USER_ID = <?= (int)($u['id'] ?? 0) ?>;
</script>

<script src="<?= h($bp) ?>/assets/main.js"></script>

</body>
</html>
<?php
}