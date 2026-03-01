<?php
// admin/users.php
session_start();

require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/admin_ui.php";

$bp = base_path();

$user_id = (int)($_GET['user_id'] ?? 0);
$q = trim((string)($_GET['q'] ?? ''));

admin_ui_header("Users");

// If a specific user is requested, show detail
if ($user_id > 0) {
  $stmt = $mysqli->prepare("
    SELECT id, username, email, display_name, created_at, last_login_at,
           approval_status, approved_by, approved_at, rejected_reason,
           is_guest, is_active, banned_until,
           email_verified_at, bank_link_status, bank_linked_at
    FROM users
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $u = $stmt->get_result()->fetch_assoc();
  $stmt->close();

  if (!$u) {
    echo '<div class="alert bad">User not found.</div>';
    admin_ui_footer();
    exit;
  }

  // roles
  $stmt = $mysqli->prepare("
    SELECT r.name
    FROM user_roles ur
    JOIN roles r ON r.id = ur.role_id
    WHERE ur.user_id = ?
    ORDER BY r.name
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $roles = [];
  $rs = $stmt->get_result();
  while ($row = $rs->fetch_assoc()) $roles[] = $row['name'];
  $stmt->close();

  // 2FA enabled
  $stmt = $mysqli->prepare("SELECT is_enabled FROM two_factor_secrets WHERE user_id = ? LIMIT 1");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $twofa = (int)($stmt->get_result()->fetch_assoc()['is_enabled'] ?? 0);
  $stmt->close();

  // recent login attempts for this user
  $stmt = $mysqli->prepare("
    SELECT success, identifier, INET6_NTOA(ip_address) AS ip, failure_reason, created_at
    FROM login_attempts
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 15
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $attempts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();

  // active sessions for this user
  $stmt = $mysqli->prepare("
    SELECT id, INET6_NTOA(ip_address) AS ip, user_agent, created_at, last_seen_at, expires_at, revoked_at
    FROM auth_sessions
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 15
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $sessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
  ?>
  <div class="card">
    <h2>User details</h2>
    <p class="sub">Deep view for approvals, security state, and readiness gates.</p>

    <div class="grid" style="grid-template-columns: 1fr 1fr; gap:12px;">
      <div class="alert">
        <b><?= h($u['username']) ?></b>
        <div style="color:var(--muted); margin-top:6px; line-height:1.45;">
          Email: <?= h($u['email']) ?><br/>
          Display: <?= h($u['display_name'] ?? '—') ?><br/>
          Roles: <?= h(implode(', ', $roles)) ?><br/>
          Guest: <?= ((int)$u['is_guest']===1) ? 'Yes' : 'No' ?><br/>
          Active: <?= ((int)$u['is_active']===1) ? 'Yes' : 'No' ?>
        </div>
      </div>

      <div class="alert">
        <b>Access & Ranked readiness</b>
        <div style="color:var(--muted); margin-top:6px; line-height:1.45;">
          Approval: <?= h($u['approval_status']) ?><br/>
          Email verified: <?= $u['email_verified_at'] ? '✅' : '❌' ?><br/>
          2FA enabled: <?= $twofa ? '✅' : '❌' ?><br/>
          Credits linked: <?= h($u['bank_link_status'] ?? 'none') ?><br/>
          Banned until: <?= h($u['banned_until'] ?? '—') ?>
        </div>
      </div>
    </div>

    <div class="alert" style="margin-top:12px;">
      <b>Timeline</b>
      <div style="color:var(--muted); margin-top:6px; line-height:1.45;">
        Created: <?= h(date("M d, Y • g:i A", strtotime((string)$u['created_at']))) ?><br/>
        Last login: <?= h($u['last_login_at'] ? date("M d, Y • g:i A", strtotime((string)$u['last_login_at'])) : '—') ?><br/>
        Approved at: <?= h($u['approved_at'] ? date("M d, Y • g:i A", strtotime((string)$u['approved_at'])) : '—') ?><br/>
        Rejected reason: <?= h($u['rejected_reason'] ?? '—') ?>
      </div>
    </div>

    <div class="btns">
      <a class="btn" href="<?= h($bp) ?>/admin/pending-users.php?user_id=<?= (int)$u['id'] ?>">Open in queue</a>
      <a class="btn" href="<?= h($bp) ?>/admin/users.php">Back to list</a>
      <span class="btn" style="opacity:.65; cursor:not-allowed;">Ban / Unban (soon)</span>
      <span class="btn" style="opacity:.65; cursor:not-allowed;">Penalty (soon)</span>
    </div>
  </div>

  <div class="grid" style="margin-top:18px;">
    <section class="card">
      <h2>Recent login attempts</h2>
      <p class="sub">Last 15 attempts for this user.</p>
      <?php if (!$attempts): ?>
        <div class="alert">No attempts recorded.</div>
      <?php else: ?>
        <?php foreach ($attempts as $a): ?>
          <div class="alert" style="<?= ((int)$a['success']===0) ? 'border-color: rgba(255,107,107,.45);' : '' ?>">
            <b><?= ((int)$a['success']===1) ? '✅ Success' : '❌ Fail' ?></b>
            <div style="color:var(--muted); margin-top:6px;">
              IP: <?= h((string)($a['ip'] ?? '—')) ?> • <?= h(date("M d • g:i A", strtotime((string)$a['created_at']))) ?><br/>
              <small><?= h((string)($a['failure_reason'] ?? '')) ?></small>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <section class="card">
      <h2>Sessions</h2>
      <p class="sub">Last 15 sessions (active + revoked).</p>
      <?php if (!$sessions): ?>
        <div class="alert">No sessions recorded.</div>
      <?php else: ?>
        <?php foreach ($sessions as $s): ?>
          <?php
            $active = empty($s['revoked_at']) && strtotime((string)$s['expires_at']) > time();
          ?>
          <div class="alert" style="<?= $active ? 'border-color: rgba(61,220,151,.45);' : '' ?>">
            <b><?= $active ? '🟢 Active' : '⚪ Inactive' ?></b>
            <div style="color:var(--muted); margin-top:6px; line-height:1.45;">
              IP: <?= h((string)($s['ip'] ?? '—')) ?><br/>
              Created: <?= h(date("M d • g:i A", strtotime((string)$s['created_at']))) ?><br/>
              Expires: <?= h(date("M d • g:i A", strtotime((string)$s['expires_at']))) ?><br/>
              Last seen: <?= h($s['last_seen_at'] ? date("M d • g:i A", strtotime((string)$s['last_seen_at'])) : '—') ?><br/>
              <small><?= h((string)$s['user_agent']) ?></small>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </div>
  <?php
  admin_ui_footer();
  exit;
}

// Else list/search users
$rows = [];
if ($q !== '') {
  $stmt = $mysqli->prepare("
    SELECT id, username, email, approval_status, is_guest, last_login_at, created_at
    FROM users
    WHERE username LIKE CONCAT('%', ?, '%')
       OR email LIKE CONCAT('%', ?, '%')
    ORDER BY created_at DESC
    LIMIT 200
  ");
  $stmt->bind_param("ss", $q, $q);
  $stmt->execute();
  $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
  $stmt->close();
} else {
  $rows = $mysqli->query("
    SELECT id, username, email, approval_status, is_guest, last_login_at, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 80
  ")->fetch_all(MYSQLI_ASSOC);
}
?>
<div class="card">
  <h2>Users</h2>
  <p class="sub">Search and inspect user access state (approval/guest/ranked readiness).</p>

  <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
    <div style="flex:1; min-width:240px;">
      <label>Search username/email</label>
      <input name="q" value="<?= h($q) ?>" placeholder="e.g. admin or example@email.com"/>
    </div>
    <div class="btns" style="margin-top:0;">
      <button class="btn btn-primary" type="submit">Search</button>
      <a class="btn" href="<?= h($bp) ?>/admin/users.php">Reset</a>
    </div>
  </form>

  <div style="margin-top:14px;">
    <?php if (!$rows): ?>
      <div class="alert">No users found.</div>
    <?php else: ?>
      <?php foreach ($rows as $r): ?>
        <div class="alert">
          <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <b><?= h($r['username']) ?> <?= ((int)$r['is_guest']===1) ? '<span class="badge">guest</span>' : '' ?></b>
            <span class="badge"><?= h($r['approval_status']) ?></span>
          </div>
          <div style="color:var(--muted); margin-top:6px; line-height:1.45;">
            <?= h($r['email']) ?><br/>
            Created: <?= h(date("M d • g:i A", strtotime((string)$r['created_at']))) ?><br/>
            Last login: <?= h($r['last_login_at'] ?? '—') ?>
          </div>
          <div style="margin-top:10px;">
            <a class="mini-btn" href="<?= h($bp) ?>/admin/users.php?user_id=<?= (int)$r['id'] ?>">Open</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php admin_ui_footer(); ?>