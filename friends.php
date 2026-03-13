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
  header("Location: {$bp}/guest_dashboard.php");
  exit;
}

$userId = (int)$u['id'];
$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = (string)($_POST['action'] ?? '');

  if ($action === 'send_request') {
    $receiverId = (int)($_POST['receiver_id'] ?? 0);
    $res = send_friend_request($mysqli, $userId, $receiverId);
    if ($res['ok']) $msg = $res['msg']; else $err = $res['msg'];
  }

  if ($action === 'accept_request') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $res = accept_friend_request($mysqli, $requestId, $userId);
    if ($res['ok']) $msg = $res['msg']; else $err = $res['msg'];
  }

  if ($action === 'decline_request') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $res = decline_friend_request($mysqli, $requestId, $userId);
    if ($res['ok']) $msg = $res['msg']; else $err = $res['msg'];
  }

  if ($action === 'cancel_request') {
    $requestId = (int)($_POST['request_id'] ?? 0);
    $res = cancel_friend_request($mysqli, $requestId, $userId);
    if ($res['ok']) $msg = $res['msg']; else $err = $res['msg'];
  }

  if ($action === 'remove_friend') {
    $friendId = (int)($_POST['friend_id'] ?? 0);
    $res = remove_friend($mysqli, $userId, $friendId);
    if ($res['ok']) $msg = $res['msg']; else $err = $res['msg'];
  }
}

$q = trim((string)($_GET['q'] ?? ''));
$results = $q !== '' ? search_users_for_friends($mysqli, $userId, $q, 12) : [];
$pendingIncoming = fetch_pending_friend_requests($mysqli, $userId, 20);
$pendingSent     = fetch_sent_friend_requests($mysqli, $userId, 20);
$friends         = fetch_friends($mysqli, $userId, 100);

ui_header("Friends");
?>

<section class="section" style="padding-top:0;">
  <div style="display:grid; gap:12px;">

    <?php if ($msg !== ''): ?>
      <div class="card" style="padding:14px; border-color: rgba(57,255,106,.28);">
        <?= h($msg) ?>
      </div>
    <?php endif; ?>

    <?php if ($err !== ''): ?>
      <div class="card" style="padding:14px; border-color: rgba(255,77,109,.28);">
        <?= h($err) ?>
      </div>
    <?php endif; ?>

    <div class="card" style="padding:16px;">
      <div style="font-weight:900; font-size:18px;">Find Players</div>
      <div style="color:var(--muted); margin-top:6px;">Search by username, display name, or email.</div>

      <form method="get" style="display:flex; gap:10px; margin-top:14px; flex-wrap:wrap;">
        <input class="input" type="text" name="q" value="<?= h($q) ?>" placeholder="Search players..." style="flex:1; min-width:220px;">
        <button class="btn" type="submit">Search</button>
      </form>

      <?php if ($q !== ''): ?>
        <div style="display:grid; gap:10px; margin-top:14px;">
          <?php if (!$results): ?>
            <div style="color:var(--muted);">No users found.</div>
          <?php else: ?>
            <?php foreach ($results as $r): ?>
              <?php
                $rid = (int)$r['id'];
                $alreadyFriends = are_friends($mysqli, $userId, $rid);
                $pending = pending_friend_request_between($mysqli, $userId, $rid);
                $displayName = trim((string)($r['display_name'] ?? ''));
                $username = (string)($r['username'] ?? 'Player');
                $showName = $displayName !== '' ? $displayName : $username;
              ?>
              <div class="card-soft" style="padding:12px; display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
                <div style="display:flex; gap:12px; align-items:center; min-width:0;">
                  <div style="width:44px; height:44px; border-radius:14px; overflow:hidden; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.06); display:grid; place-items:center; font-weight:900;">
                    <?php if (!empty($r['avatar_path'])): ?>
                      <img src="<?= h($bp . '/' . ltrim((string)$r['avatar_path'], '/')) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                    <?php else: ?>
                      <?= h(strtoupper(substr($username, 0, 1))) ?>
                    <?php endif; ?>
                  </div>

                  <div style="min-width:0;">
                    <div style="font-weight:900;"><?= h($showName) ?></div>
                    <div style="color:var(--muted); font-size:13px;">@<?= h($username) ?> • Lv. <?= (int)($r['level'] ?? 1) ?></div>
                    <?php if (!empty($r['tagline'])): ?>
                      <div style="color:var(--muted); font-size:12px; margin-top:4px;"><?= h((string)$r['tagline']) ?></div>
                    <?php endif; ?>
                  </div>
                </div>

                <div>
                  <?php if ($alreadyFriends): ?>
                    <span class="pill">Already friends</span>
                  <?php elseif ($pending): ?>
                    <span class="pill">Request pending</span>
                  <?php else: ?>
                    <form method="post">
                      <input type="hidden" name="action" value="send_request">
                      <input type="hidden" name="receiver_id" value="<?= $rid ?>">
                      <button class="btn" type="submit">Add Friend</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
      <div class="card" style="padding:16px;">
        <div style="font-weight:900; font-size:18px;">Incoming Requests</div>
        <div style="display:grid; gap:10px; margin-top:14px;">
          <?php if (!$pendingIncoming): ?>
            <div style="color:var(--muted);">No pending incoming requests.</div>
          <?php else: ?>
            <?php foreach ($pendingIncoming as $fr): ?>
              <div class="card-soft" style="padding:12px;">
                <div style="font-weight:900;"><?= h(($fr['display_name'] ?: $fr['username']) ?? 'Player') ?></div>
                <div style="color:var(--muted); font-size:12px; margin-top:6px;">
                  @<?= h((string)$fr['username']) ?> • <?= h(date("M d, Y g:i A", strtotime((string)$fr['created_at']))) ?>
                </div>
                <div style="display:flex; gap:8px; margin-top:12px; flex-wrap:wrap;">
                  <form method="post">
                    <input type="hidden" name="action" value="accept_request">
                    <input type="hidden" name="request_id" value="<?= (int)$fr['id'] ?>">
                    <button class="btn" type="submit">Accept</button>
                  </form>
                  <form method="post">
                    <input type="hidden" name="action" value="decline_request">
                    <input type="hidden" name="request_id" value="<?= (int)$fr['id'] ?>">
                    <button class="btn btn-ghost" type="submit">Decline</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <div class="card" style="padding:16px;">
        <div style="font-weight:900; font-size:18px;">Sent Requests</div>
        <div style="display:grid; gap:10px; margin-top:14px;">
          <?php if (!$pendingSent): ?>
            <div style="color:var(--muted);">No pending sent requests.</div>
          <?php else: ?>
            <?php foreach ($pendingSent as $fr): ?>
              <div class="card-soft" style="padding:12px;">
                <div style="font-weight:900;"><?= h(($fr['display_name'] ?: $fr['username']) ?? 'Player') ?></div>
                <div style="color:var(--muted); font-size:12px; margin-top:6px;">
                  @<?= h((string)$fr['username']) ?> • <?= h(date("M d, Y g:i A", strtotime((string)$fr['created_at']))) ?>
                </div>
                <div style="display:flex; gap:8px; margin-top:12px; flex-wrap:wrap;">
                  <form method="post">
                    <input type="hidden" name="action" value="cancel_request">
                    <input type="hidden" name="request_id" value="<?= (int)$fr['id'] ?>">
                    <button class="btn btn-ghost" type="submit">Cancel</button>
                  </form>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="card" style="padding:16px;">
      <div style="font-weight:900; font-size:18px;">Friends</div>
      <div style="display:grid; gap:10px; margin-top:14px;">
        <?php if (!$friends): ?>
          <div style="color:var(--muted);">No friends yet.</div>
        <?php else: ?>
          <?php foreach ($friends as $f): ?>
            <?php
              $friendId = (int)$f['friend_id'];
              $displayName = trim((string)($f['display_name'] ?? ''));
              $username = (string)($f['username'] ?? 'Player');
              $showName = $displayName !== '' ? $displayName : $username;
            ?>
            <div class="card-soft" style="padding:12px; display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
              <div style="display:flex; gap:12px; align-items:center; min-width:0;">
                <div style="width:44px; height:44px; border-radius:14px; overflow:hidden; border:1px solid rgba(255,255,255,.12); background:rgba(255,255,255,.06); display:grid; place-items:center; font-weight:900;">
                  <?php if (!empty($f['avatar_path'])): ?>
                    <img src="<?= h($bp . '/' . ltrim((string)$f['avatar_path'], '/')) ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                  <?php else: ?>
                    <?= h(strtoupper(substr($username, 0, 1))) ?>
                  <?php endif; ?>
                </div>

                <div style="min-width:0;">
                  <div style="font-weight:900;"><?= h($showName) ?></div>
                  <div style="color:var(--muted); font-size:13px;">@<?= h($username) ?> • Lv. <?= (int)($f['level'] ?? 1) ?></div>
                  <?php if (!empty($f['tagline'])): ?>
                    <div style="color:var(--muted); font-size:12px; margin-top:4px;"><?= h((string)$f['tagline']) ?></div>
                  <?php endif; ?>
                </div>
              </div>

              <div style="display:flex; gap:8px; flex-wrap:wrap;">
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
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
</section>

<?php ui_footer(); ?>