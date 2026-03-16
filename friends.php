<?php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";
require_once __DIR__ . "/includes/friends_helpers.php";

require_login();

$u  = current_user();
$bp = base_path();

if ((int)($u['is_guest'] ?? 0) === 1) {
  flash_set('err', 'Guests do not have access to Friends.');
  header("Location: {$bp}/guest_dashboard.php");
  exit;
}

$userId = (int)($u['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? '');
  $redirect = $bp . '/friends.php';

  $qRedirect = trim((string)($_POST['q_redirect'] ?? ''));
  if ($qRedirect !== '') {
    $redirect .= '?q=' . rawurlencode($qRedirect);
  }

  if ($action === 'send_request') {
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $res = send_friend_request($mysqli, $userId, $receiverId);
    if (!empty($res['ok'])) {
      flash_set('msg', (string)$res['msg']);
    } else {
      flash_set('err', (string)$res['msg']);
    }
    header("Location: {$redirect}");
    exit;
  }

  if ($action === 'accept_request') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $res = accept_friend_request($mysqli, $requestId, $userId);
    if (!empty($res['ok'])) {
      flash_set('msg', (string)$res['msg']);
    } else {
      flash_set('err', (string)$res['msg']);
    }
    header("Location: {$redirect}");
    exit;
  }

  if ($action === 'decline_request') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $res = decline_friend_request($mysqli, $requestId, $userId);
    if (!empty($res['ok'])) {
      flash_set('msg', (string)$res['msg']);
    } else {
      flash_set('err', (string)$res['msg']);
    }
    header("Location: {$redirect}");
    exit;
  }

  if ($action === 'cancel_request') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $res = cancel_friend_request($mysqli, $requestId, $userId);
    if (!empty($res['ok'])) {
      flash_set('msg', (string)$res['msg']);
    } else {
      flash_set('err', (string)$res['msg']);
    }
    header("Location: {$redirect}");
    exit;
  }

  if ($action === 'remove_friend') {
    $friendId = (int)($_POST['friend_id'] ?? 0);
    $res = remove_friend($mysqli, $userId, $friendId);
    if (!empty($res['ok'])) {
      flash_set('msg', (string)$res['msg']);
    } else {
      flash_set('err', (string)$res['msg']);
    }
    header("Location: {$redirect}");
    exit;
  }
}

$msg = flash_get('msg');
$err = flash_get('err');

$q = trim((string)($_GET['q'] ?? ''));
$results = $q !== '' ? search_users_for_friends($mysqli, $userId, $q, 12) : [];

$pendingIncoming = fetch_pending_friend_requests($mysqli, $userId, 20);
$pendingSent     = fetch_sent_friend_requests($mysqli, $userId, 20);
$friends         = fetch_friends($mysqli, $userId, 100);

$incomingCount = count($pendingIncoming);
$sentCount     = count($pendingSent);
$friendsCount  = count($friends);
$resultsCount  = count($results);

$req       = ranked_requirements($mysqli, $u);
$ranked_ok = !empty($req['ranked_ok']);

ui_header("Friends");
?>

<section class="section notif-page">
  <div class="hub-grid">

    <aside class="card hub-left notif-sidebar">
      <div class="notif-sidebar__title">MENU</div>

      <div class="notif-sidebar__nav">
        <a class="hub-item" href="<?= h($bp) ?>/dashboard.php">
          <span class="hub-ico">🏠</span>
          <span>Dashboard</span>
        </a>

        <a class="hub-item" href="<?= h($bp) ?>/notifications.php">
          <span class="hub-ico">🔔</span>
          <span>Notifications</span>
        </a>

        <a class="hub-item is-active" href="<?= h($bp) ?>/friends.php">
          <span class="hub-ico">👥</span>
          <span>Friends</span>
        </a>

        <a class="hub-item" href="<?= h($bp) ?>/profile.php?tab=overview">
          <span class="hub-ico">⚙️</span>
          <span>Options</span>
        </a>
      </div>

      <div class="notif-sidebar__status">
        <span class="pill">Player</span>

        <?php if (!$ranked_ok): ?>
          <div class="notif-sidebar__rank">
            <span class="pill notif-pill-danger">Ranked Locked</span>
            <div class="notif-sidebar__hint">
              Finish security steps to unlock Ranked.
            </div>
          </div>
        <?php else: ?>
          <div class="notif-sidebar__rank">
            <span class="pill notif-pill-good">Ranked Unlocked</span>
          </div>
        <?php endif; ?>
      </div>
    </aside>

    <main class="notif-main">

      <?php if ($msg): ?>
        <div class="card-soft" style="padding:14px; border-color:rgba(57,255,106,.28);">
          <?= h($msg) ?>
        </div>
      <?php endif; ?>

      <?php if ($err): ?>
        <div class="card-soft" style="padding:14px; border-color:rgba(255,77,109,.28);">
          <?= h($err) ?>
        </div>
      <?php endif; ?>

      <div class="card notif-panel">
        <div class="notif-panel__head">
          <div>
            <div class="notif-panel__title">Find Players</div>
            <div class="notif-panel__sub">Search by username, display name, or email</div>
          </div>
        </div>

        <form method="get" class="friends-search-form">
          <input
            class="input friends-search-input"
            type="text"
            name="q"
            value="<?= h($q) ?>"
            placeholder="Search players..."
          >
          <button class="btn btn-primary" type="submit">Search</button>
        </form>

        <?php if ($q !== ''): ?>
          <div class="notif-list" style="margin-top:14px;">
            <?php if (!$results): ?>
              <div class="card-soft notif-empty">
                <div class="notif-empty__title">No users found.</div>
                <div class="notif-empty__meta">Try a different username, display name, or email.</div>
              </div>
            <?php else: ?>
              <?php foreach ($results as $r): ?>
                <?php
                  $rid = (int)$r['id'];
                  $alreadyFriends = are_friends($mysqli, $userId, $rid);
                  $pending = pending_friend_request_between($mysqli, $userId, $rid);
                  $displayName = trim((string)($r['display_name'] ?? ''));
                  $username = (string)($r['username'] ?? 'Player');
                  $showName = $displayName !== '' ? $displayName : $username;
                  $avatarPath = trim((string)($r['avatar_path'] ?? ''));
                ?>
                <article class="card-soft notif-row notif-row--compact">
                  <div class="notif-row__icon friends-avatar">
                    <?php if ($avatarPath !== ''): ?>
                      <img
                        src="<?= h($bp . '/' . ltrim($avatarPath, '/')) ?>"
                        alt="<?= h($showName) ?>"
                        class="friends-avatar__img"
                      >
                    <?php else: ?>
                      <?= h(strtoupper(substr($username, 0, 1))) ?>
                    <?php endif; ?>
                  </div>

                  <div class="notif-row__content">
                    <div class="notif-row__top">
                      <div class="notif-row__main">
                        <div class="notif-row__titleline">
                          <div class="notif-row__title"><?= h($showName) ?></div>

                          <?php if ($alreadyFriends): ?>
                            <span class="pill notif-pill-soft">Already friends</span>
                          <?php elseif ($pending): ?>
                            <span class="pill notif-pill-soft">Request pending</span>
                          <?php else: ?>
                            <span class="pill">Player</span>
                          <?php endif; ?>
                        </div>

                        <div class="notif-row__body">
                          @<?= h($username) ?> • Lv. <?= (int)($r['level'] ?? 1) ?>
                        </div>

                        <?php if (!empty($r['tagline'])): ?>
                          <div class="notif-row__meta">
                            <span><?= h((string)$r['tagline']) ?></span>
                          </div>
                        <?php endif; ?>
                      </div>

                      <div class="notif-row__actions notif-row__actions--inline">
                        <?php if ($alreadyFriends): ?>
                          <span class="pill notif-pill-soft">Connected</span>
                        <?php elseif ($pending): ?>
                          <span class="pill notif-pill-soft">Pending</span>
                        <?php else: ?>
                          <form method="post">
                            <input type="hidden" name="action" value="send_request">
                            <input type="hidden" name="receiver_id" value="<?= $rid ?>">
                            <input type="hidden" name="q_redirect" value="<?= h($q) ?>">
                            <button class="btn btn-primary" type="submit">Add Friend</button>
                          </form>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="friends-split">
        <div class="card notif-panel">
          <div class="notif-panel__head">
            <div>
              <div class="notif-panel__title">Incoming Requests</div>
              <div class="notif-panel__sub">Players waiting for your response</div>
            </div>
            <span class="pill"><?= (int)$incomingCount ?></span>
          </div>

          <div class="notif-list">
            <?php if (!$pendingIncoming): ?>
              <div class="card-soft notif-empty">
                <div class="notif-empty__title">No incoming requests.</div>
                <div class="notif-empty__meta">You are all caught up right now.</div>
              </div>
            <?php else: ?>
              <?php foreach ($pendingIncoming as $fr): ?>
                <?php
                  $displayName = trim((string)($fr['display_name'] ?? ''));
                  $username = (string)($fr['username'] ?? 'Player');
                  $showName = $displayName !== '' ? $displayName : $username;
                  $when = !empty($fr['created_at']) ? date("M d • g:i A", strtotime((string)$fr['created_at'])) : '';
                ?>
                <article class="card-soft notif-row notif-row--compact is-unread">
                  <div class="notif-row__icon">👥</div>

                  <div class="notif-row__content">
                    <div class="notif-row__top">
                      <div class="notif-row__main">
                        <div class="notif-row__titleline">
                          <div class="notif-row__title"><?= h($showName) ?></div>
                          <span class="pill notif-pill-good">Incoming</span>
                        </div>

                        <div class="notif-row__body">@<?= h($username) ?></div>

                        <div class="notif-row__meta">
                          <span><?= h($when) ?></span>
                        </div>
                      </div>

                      <div class="notif-row__actions notif-row__actions--inline">
                        <form method="post">
                          <input type="hidden" name="action" value="accept_request">
                          <input type="hidden" name="request_id" value="<?= (int)$fr['id'] ?>">
                          <button class="btn btn-primary" type="submit">Accept</button>
                        </form>

                        <form method="post">
                          <input type="hidden" name="action" value="decline_request">
                          <input type="hidden" name="request_id" value="<?= (int)$fr['id'] ?>">
                          <button class="btn btn-ghost" type="submit">Decline</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="card notif-panel">
          <div class="notif-panel__head">
            <div>
              <div class="notif-panel__title">Sent Requests</div>
              <div class="notif-panel__sub">Requests you already sent out</div>
            </div>
            <span class="pill"><?= (int)$sentCount ?></span>
          </div>

          <div class="notif-list">
            <?php if (!$pendingSent): ?>
              <div class="card-soft notif-empty">
                <div class="notif-empty__title">No sent requests.</div>
                <div class="notif-empty__meta">You do not have any pending outgoing requests.</div>
              </div>
            <?php else: ?>
              <?php foreach ($pendingSent as $fr): ?>
                <?php
                  $displayName = trim((string)($fr['display_name'] ?? ''));
                  $username = (string)($fr['username'] ?? 'Player');
                  $showName = $displayName !== '' ? $displayName : $username;
                  $when = !empty($fr['created_at']) ? date("M d • g:i A", strtotime((string)$fr['created_at'])) : '';
                ?>
                <article class="card-soft notif-row notif-row--compact">
                  <div class="notif-row__icon">📨</div>

                  <div class="notif-row__content">
                    <div class="notif-row__top">
                      <div class="notif-row__main">
                        <div class="notif-row__titleline">
                          <div class="notif-row__title"><?= h($showName) ?></div>
                          <span class="pill notif-pill-soft">Pending</span>
                        </div>

                        <div class="notif-row__body">@<?= h($username) ?></div>

                        <div class="notif-row__meta">
                          <span><?= h($when) ?></span>
                        </div>
                      </div>

                      <div class="notif-row__actions notif-row__actions--inline">
                        <form method="post">
                          <input type="hidden" name="action" value="cancel_request">
                          <input type="hidden" name="request_id" value="<?= (int)$fr['id'] ?>">
                          <button class="btn btn-ghost" type="submit">Cancel</button>
                        </form>
                      </div>
                    </div>
                  </div>
                </article>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="card notif-panel">
        <div class="notif-panel__head">
          <div>
            <div class="notif-panel__title">Friends List</div>
            <div class="notif-panel__sub">Your connected players and direct message shortcuts</div>
          </div>
          <span class="pill"><?= (int)$friendsCount ?></span>
        </div>

        <div class="notif-list">
          <?php if (!$friends): ?>
            <div class="card-soft notif-empty">
              <div class="notif-empty__title">No friends yet.</div>
              <div class="notif-empty__meta">Search for players above and start building your list.</div>
            </div>
          <?php else: ?>
            <?php foreach ($friends as $f): ?>
              <?php
                $friendId = (int)$f['friend_id'];
                $displayName = trim((string)($f['display_name'] ?? ''));
                $username = (string)($f['username'] ?? 'Player');
                $showName = $displayName !== '' ? $displayName : $username;
                $avatarPath = trim((string)($f['avatar_path'] ?? ''));
              ?>
              <article class="card-soft notif-row notif-row--compact">
                <div class="notif-row__icon friends-avatar">
                  <?php if ($avatarPath !== ''): ?>
                    <img
                      src="<?= h($bp . '/' . ltrim($avatarPath, '/')) ?>"
                      alt="<?= h($showName) ?>"
                      class="friends-avatar__img"
                    >
                  <?php else: ?>
                    <?= h(strtoupper(substr($username, 0, 1))) ?>
                  <?php endif; ?>
                </div>

                <div class="notif-row__content">
                  <div class="notif-row__top">
                    <div class="notif-row__main">
                      <div class="notif-row__body">
                        @<?= h($username) ?> • Lv. <?= (int)($f['level'] ?? 1) ?>
                      </div>

                      <?php if (!empty($f['tagline'])): ?>
                        <div class="notif-row__meta">
                          <span><?= h((string)$f['tagline']) ?></span>
                        </div>
                      <?php endif; ?>
                    </div>

                    <div class="notif-row__actions notif-row__actions--inline">
                      <button
                        type="button"
                        class="btn btn-ghost"
                        data-chat-open="1"
                        data-user-id="<?= $friendId ?>">
                        Message
                      </button>

                      <form method="post">
                        <input type="hidden" name="action" value="remove_friend">
                        <input type="hidden" name="friend_id" value="<?= $friendId ?>">
                        <button class="btn btn-ghost" type="submit">Remove</button>
                      </form>
                    </div>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </main>

    <aside class="hub-right notif-right">
      <div class="card notif-sidepanel">
        <div class="notif-sidepanel__head">
          <div>
            <div class="notif-sidepanel__title">Summary</div>
            <div class="notif-sidepanel__sub">Quick overview of your friends hub</div>
          </div>
        </div>

        <div class="notif-summary">
          <div class="card-soft notif-summary__item">
            <div class="notif-summary__label">Friends</div>
            <div class="notif-summary__value"><?= (int)$friendsCount ?></div>
          </div>

          <div class="card-soft notif-summary__item">
            <div class="notif-summary__label">Incoming</div>
            <div class="notif-summary__value"><?= (int)$incomingCount ?></div>
          </div>

          <div class="card-soft notif-summary__item">
            <div class="notif-summary__label">Sent</div>
            <div class="notif-summary__value"><?= (int)$sentCount ?></div>
          </div>
    </aside>

  </div>
</section>

<?php ui_footer(); ?>