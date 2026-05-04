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
  $uid = (int)($u['id'] ?? 0);

  $stmt = $mysqli->prepare("
    SELECT id, approval_status, email_verified_at, credits, ranked_unlocked
    FROM users
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $dbUser = $stmt->get_result()->fetch_assoc() ?: [];
  $stmt->close();

  $stmt = $mysqli->prepare("SELECT is_enabled FROM two_factor_secrets WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $twofa = (int)($stmt->get_result()->fetch_assoc()['is_enabled'] ?? 0);
  $stmt->close();

  $approved_ok = (($dbUser['approval_status'] ?? '') === 'approved');
  $email_ok = !empty($dbUser['email_verified_at']);
  $twofa_ok = ($twofa === 1);

  $credits = (int)($dbUser['credits'] ?? 0);
  $unlock_threshold = 250;
  $entry_fee = 250;

  $ranked_unlocked = ((int)($dbUser['ranked_unlocked'] ?? 0) === 1);

  $can_unlock_now = $approved_ok && $email_ok && $twofa_ok && $credits >= $unlock_threshold;

  if (!$ranked_unlocked && $can_unlock_now && $uid > 0) {
    $stmt = $mysqli->prepare("
      UPDATE users
      SET ranked_unlocked = 1
      WHERE id = ?
      LIMIT 1
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->close();

    $ranked_unlocked = true;

    $stmt = $mysqli->prepare("
      SELECT id
      FROM dashboard_notifications
      WHERE user_id = ? AND type = 'ranked_unlock'
      LIMIT 1
    ");
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $existingUnlockNotif = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$existingUnlockNotif) {
      $stmt = $mysqli->prepare("
        INSERT INTO dashboard_notifications (user_id, type, title, body, link_url, is_read)
        VALUES (?, 'ranked_unlock', 'Ranked Unlocked', ?, '/play.php', 0)
      ");
      $body = 'You unlocked Ranked. Keep at least ' . number_format($entry_fee) . ' Zeny ready to enter a match.';
      $stmt->bind_param("is", $uid, $body);
      $stmt->execute();
      $stmt->close();
    }
  }

  $credits_ok = ($credits >= $unlock_threshold);
  $can_afford_queue = ($credits >= $entry_fee);

  return [
    'approved_ok' => $approved_ok,
    'email_ok' => $email_ok,
    'twofa_ok' => $twofa_ok,
    'credits_ok' => $credits_ok,
    'credits' => $credits,
    'unlock_threshold' => $unlock_threshold,
    'entry_fee' => $entry_fee,
    'ranked_unlocked' => $ranked_unlocked,
    'can_afford_queue' => $can_afford_queue,
    'ranked_ok' => $ranked_unlocked && $can_afford_queue,
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

  $stmt = $mysqli->prepare("
    SELECT *
    FROM users
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param("i", $uid);
  $stmt->execute();
  $freshUser = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if ($freshUser) {
    $u = $freshUser;
  }

  $unread = count_unread_notifications($mysqli, $uid);

  $is_guest = ((int)($u['is_guest'] ?? 0) === 1);
  $role_label = $is_guest ? 'Guest' : 'Player';

  $level   = (int)($u['level'] ?? 1);
  $exp     = (int)($u['exp'] ?? 0);
  $expNext = (int)($u['exp_to_next'] ?? 100);

  $exp_pct = $expNext > 0 ? min(100, round(($exp / $expNext) * 100)) : 0;

  $req = ranked_requirements($mysqli, $u);
  $ranked_unlocked = (!$is_guest && !empty($req['ranked_unlocked']));
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

  <link rel="icon" type="image/x-icon" href="<?= h($bp) ?>/assets/brand/favicon.ico"/>
  <link rel="shortcut icon" type="image/x-icon" href="<?= h($bp) ?>/assets/brand/favicon.ico"/>
  <link rel="apple-touch-icon" href="<?= h($bp) ?>/assets/brand/logo.png"/>

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
  <div class="topnav__inner topnav__inner--hub">

    <a href="<?= h($bp) ?>/profile.php" title="Profile & Stats" class="topnav-profile">
      <?php $navAvatar = trim((string)($u['avatar_path'] ?? '')); ?>

      <?php if ($navAvatar !== ''): ?>
        <img
          src="<?= h($bp . '/' . ltrim($navAvatar, '/')) ?>"
          alt="Profile Avatar"
          class="topnav-profile__avatarimg"
        >
      <?php else: ?>
        <img
          src="<?= h($bp) ?>/assets/brand/logo.png"
          alt="Logia"
          class="topnav-profile__avatarimg topnav-profile__avatarimg--brand"
        >
      <?php endif; ?>

      <div class="topnav-profile__meta">
        <div class="topnav-profile__row">
          <div class="topnav-profile__name">
            <?= h($u['display_name'] ?? $u['username']) ?>
          </div>

          <span class="pill"><?= h($role_label) ?></span>
          <span class="pill pill-soft" id="topnavLevelPill">Lv. <?= (int)$level ?></span>

          <?php if ($is_guest): ?>
            <span class="pill status-pill--warn">Casual Only</span>
          <?php elseif (!$ranked_unlocked): ?>
            <span class="pill status-pill--bad">Ranked Locked</span>
          <?php elseif (!$ranked_ok): ?>
            <span class="pill status-pill--warn">Need <?= (int)$req['entry_fee'] ?> Zeny</span>
          <?php endif; ?>
        </div>

        <div class="topnav-profile__subrow">
          <div class="topnav-profile__xpbar">
            <i
              class="topnav-profile__xpfill"
              id="topnavXpFill"
              data-progress="<?= max(0, min(100, $exp_pct)) ?>"
            ></i>
          </div>
          <div class="topnav-profile__subtext" id="topnavXpText">
            <?= number_format($exp) ?> / <?= number_format($expNext) ?> EXP
          </div>
        </div>
      </div>
    </a>

    <div class="md-icons">

      <div class="md-ico-wrap" title="Guide">
        <button
          class="md-ico md-ico--guide"
          type="button"
          data-guide-open="getting-started"
          aria-label="Open guide"
        >
          ❔
        </button>
      </div>

          <div
          class="md-ico-wrap md-balance-wrap"
          title="Zeny Balance"
        >
          <a
            href="<?= h($bp) ?>/shop.php?tab=credits"
            class="md-balance"
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

            <div class="dd__actions">
              <button type="button" class="btn btn-ghost" data-notif-read-all="1">Mark all read</button>
              <a class="btn btn-ghost" href="<?= h($bp) ?>/notifications.php">View all</a>
            </div>
          </div>

          <div class="dd__body">
            <?php if (!$dd_notes): ?>
              <div class="dd__empty">No notifications yet.</div>
            <?php else: ?>
              <?php foreach ($dd_notes as $n): ?>
                <?php
                  $notifId = (int)($n['id'] ?? 0);
                  $icon = notif_icon((string)$n['type']);
                  $when = $n['created_at'] ? date("M d • g:i A", strtotime((string)$n['created_at'])) : '';
                  $link = trim((string)($n['link_url'] ?? ''));
                  $notifType = (string)($n['type'] ?? '');
                  $isUnread = ((int)($n['is_read'] ?? 0) === 0);

                  $openableTypes = [
                    'admin_approval',
                    'security_alert',
                    'friend_request',
                    'friend_accept',
                    'message',
                    'ranked_unlock',
                  ];

                  $canOpenNotif = ($link !== '' && in_array($notifType, $openableTypes, true));
                ?>
                <div
                  class="card-soft dd-card"
                  data-notif-card
                  data-notif-id="<?= $notifId ?>"
                  data-notif-type="<?= h($notifType) ?>"
                  data-link-url="<?= h($link) ?>"
                >
                  <div class="dd-card__row">
                    <div class="dd-card__icon">
                      <?= $icon ?>
                    </div>

                    <div class="dd-card__body">
                      <div class="dd-card__title">
                        <?= h($n['title']) ?>
                      </div>

                      <?php if (!empty($n['body'])): ?>
                        <div class="dd-card__text">
                          <?= h($n['body']) ?>
                        </div>
                      <?php endif; ?>

                      <div class="dd-card__time">
                        <?= h($when) ?>
                      </div>

                      <div class="dd-card__actions">
                        <?php if ($canOpenNotif): ?>
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
                      <span class="pill notif-new-pill">NEW</span>
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
                <div class="dd__empty">No pending friend requests.</div>
              <?php else: ?>
                <?php foreach ($friend_requests as $fr): ?>
                  <?php
                    $requestId = (int)($fr['id'] ?? 0);
                    $senderId  = (int)($fr['sender_id'] ?? 0);
                    $friendName = (string)(($fr['display_name'] ?: $fr['username']) ?? 'Player');
                  ?>
                  <div
                    class="card-soft dd-card"
                    data-friend-request-card
                    data-request-id="<?= $requestId ?>"
                  >
                    <div class="dd-card__row dd-card__row--center">
                      <div class="dd-card__avatar">
                        <?php if (!empty($fr['avatar_path'])): ?>
                          <img src="<?= h($bp . '/' . ltrim((string)$fr['avatar_path'], '/')) ?>" alt="" class="dd-card__avatarimg">
                        <?php else: ?>
                          <?= h(strtoupper(substr((string)($fr['username'] ?? 'U'), 0, 1))) ?>
                        <?php endif; ?>
                      </div>

                      <div class="dd-card__body">
                        <div class="dd-card__title">
                          <?= h($friendName) ?>
                        </div>
                        <div class="dd-card__text">
                          Sent you a friend request
                        </div>

                        <div class="dd-card__actions">
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
          <div class="md-ico md-ico--disabled">👥</div>
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

            <div class="dd__body dd__body--spaced">
              <?php if (!$mail_threads): ?>
                <div class="dd__empty">No direct messages yet.</div>
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
                    class="card-soft chat-thread-launch dd-thread"
                    data-chat-open="1"
                    data-conversation-id="<?= $conversationId ?>"
                  >
                    <div class="dd-thread__top">
                      <div class="dd-thread__name">
                        <?= h($name) ?>
                      </div>
                      <?php if ((int)($thread['unread_count'] ?? 0) > 0): ?>
                        <span class="pill dd-thread__pill"><?= (int)$thread['unread_count'] ?> NEW</span>
                      <?php endif; ?>
                    </div>
                    <div class="dd-thread__preview">
                      <?= h((string)($last['body'] ?? 'No messages yet.')) ?>
                    </div>
                  </button>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        <?php else: ?>
          <div class="md-ico md-ico--disabled" title="Messages (Guest)">✉️</div>
        <?php endif; ?>
      </div>

      <a class="md-ico-wrap" href="<?= h($bp) ?>/logout.php" title="Logout">
        <div class="md-ico">⎋</div>
      </a>

    </div>
  </div>
</header>

<main class="container container--topspaced">

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

  $$("[data-progress]").forEach((el) => {
    const value = Math.max(0, Math.min(100, Number(el.getAttribute("data-progress") || 0)));
    el.style.width = value + "%";
  });
})();
</script>

<?php
}

function ui_footer(): void {
  $bp = base_path();
  ?>
</main>

<div class="guide-modal" id="globalGuideModal" aria-hidden="true">
  <div class="guide-modal__backdrop" data-guide-close></div>

  <div class="guide-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="globalGuideTitle">
    <button class="guide-modal__close" type="button" data-guide-close aria-label="Close guide">×</button>

    <div class="guide-modal__head">
      <div>
        <div class="guide-modal__eyebrow">PLAYER ONBOARDING</div>
        <h2 id="globalGuideTitle">Logia Guide</h2>
        <p>
          New here? This guide explains what each area does and what to do first.
        </p>
      </div>
    </div>

    <div class="guide-modal__layout">
      <div class="guide-modal__tabs" role="tablist" aria-label="Guide sections">
        <button class="guide-tab is-active" type="button" data-guide-tab="getting-started">Getting Started</button>
        <button class="guide-tab" type="button" data-guide-tab="game-rules">Game Rules</button>
        <button class="guide-tab" type="button" data-guide-tab="modes">Game Modes</button>
        <button class="guide-tab" type="button" data-guide-tab="ranked">Ranked</button>
        <button class="guide-tab" type="button" data-guide-tab="zeny">Zeny / Shop</button>
        <button class="guide-tab" type="button" data-guide-tab="account">Account Setup</button>
      </div>

      <div class="guide-modal__content">
        <section class="guide-pane is-active" data-guide-pane="getting-started">
          <h3>What should I do first?</h3>
          <p>
            Start with Solo Mode first. It teaches the rules safely before you enter Casual or Ranked matches.
          </p>

          <div class="guide-steps">
            <div class="guide-step">
              <strong>1. Learn</strong>
              <span>Go to Solo and clear the Training path.</span>
            </div>
            <div class="guide-step">
              <strong>2. Practice</strong>
              <span>Use Quick Match or Rooms to play casual matches.</span>
            </div>
            <div class="guide-step">
              <strong>3. Prepare</strong>
              <span>Set up your account, earn or buy Zeny, then unlock Ranked.</span>
            </div>
          </div>
        </section>

        <section class="guide-pane" data-guide-pane="game-rules">
          <h3>Basic rules</h3>
          <p>
            Logia is an elemental card game. On your turn, play a card that matches the active element, or play an element that beats it.
          </p>

          <div class="guide-rule-grid">
            <div class="guide-rule-card">🔥 Fire beats Wood</div>
            <div class="guide-rule-card">💧 Water beats Fire</div>
            <div class="guide-rule-card">⚡ Lightning beats Water</div>
            <div class="guide-rule-card">🪨 Earth beats Lightning</div>
            <div class="guide-rule-card">🌪️ Wind beats Earth</div>
            <div class="guide-rule-card">🌳 Wood beats Wind</div>
          </div>

          <p>
            If you cannot play a valid card, use Pass. Special cards like +2 and +4 add pressure or change the active element.
          </p>
        </section>

        <section class="guide-pane" data-guide-pane="modes">
          <h3>Game modes</h3>

          <div class="guide-card-list">
            <div class="guide-info-card">
              <strong>Solo</strong>
              <span>Best for learning. Training levels explain the rules step by step.</span>
            </div>
            <div class="guide-info-card">
              <strong>Quick Match</strong>
              <span>Fast casual match. Best when you just want to play immediately.</span>
            </div>
            <div class="guide-info-card">
              <strong>Rooms</strong>
              <span>Create or join a custom lobby. Best for friend groups or demos.</span>
            </div>
            <div class="guide-info-card">
              <strong>Ranked</strong>
              <span>Competitive mode with leagues, entry fees, and better rewards.</span>
            </div>
          </div>
        </section>

        <section class="guide-pane" data-guide-pane="ranked">
          <h3>Ranked leagues</h3>
          <p>
            Ranked is the competitive path. You pick a league before queueing. Higher leagues cost more Zeny and may require more ranked wins.
          </p>

          <div class="guide-card-list">
            <div class="guide-info-card">
              <strong>Bronze</strong>
              <span>Starter league. Lowest entry fee and easiest access.</span>
            </div>
            <div class="guide-info-card">
              <strong>Silver</strong>
              <span>Mid league. Requires more wins and a higher entry fee.</span>
            </div>
            <div class="guide-info-card">
              <strong>Gold</strong>
              <span>Higher-risk league. Bigger cost, better reward multiplier.</span>
            </div>
          </div>
        </section>

        <section class="guide-pane" data-guide-pane="zeny">
          <h3>Zeny and shop</h3>
          <p>
            Zeny is the game currency. It is used for ranked entry and shop-related features.
          </p>

          <div class="guide-steps">
            <div class="guide-step">
              <strong>Where to check it</strong>
              <span>Your Zeny balance appears in the top navigation bar.</span>
            </div>
            <div class="guide-step">
              <strong>Where to get it</strong>
              <span>Open the Shop page and choose a top-up package.</span>
            </div>
            <div class="guide-step">
              <strong>Why it matters</strong>
              <span>Ranked leagues require enough Zeny before you can queue.</span>
            </div>
          </div>
        </section>

        <section class="guide-pane" data-guide-pane="account">
          <h3>Account setup</h3>
          <p>
            Some features require a registered and secured account. Guests can try basic play, but competitive and social features are limited.
          </p>

          <div class="guide-card-list">
            <div class="guide-info-card">
              <strong>Email verification</strong>
              <span>Confirms the account belongs to you.</span>
            </div>
            <div class="guide-info-card">
              <strong>2FA</strong>
              <span>Adds another security layer before unlocking stronger features.</span>
            </div>
            <div class="guide-info-card">
              <strong>Profile</strong>
              <span>Shows your level, EXP, account status, and setup progress.</span>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</div>

<footer class="sitefooter">
  <div class="sitefooter__inner sitefooter__inner--hubshell">
    <div>
      <div class="footbrand">Logia</div>
      <div class="footmuted">© <?= date('Y') ?> • Platform shell</div>
    </div>

    <div class="footmuted footmuted--links">
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