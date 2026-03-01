<?php
// admin/security.php
session_start();

require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/admin_ui.php";

$bp = base_path();

// recent login attempts
$attempts = $mysqli->query("
  SELECT id, user_id, identifier, success,
         INET6_NTOA(ip_address) AS ip,
         user_agent, failure_reason, created_at
  FROM login_attempts
  ORDER BY created_at DESC
  LIMIT 60
")->fetch_all(MYSQLI_ASSOC);

// active sessions
$sessions = $mysqli->query("
  SELECT s.id, s.user_id,
         INET6_NTOA(s.ip_address) AS ip,
         s.user_agent, s.created_at, s.last_seen_at, s.expires_at, s.revoked_at,
         u.username
  FROM auth_sessions s
  JOIN users u ON u.id = s.user_id
  WHERE s.revoked_at IS NULL
    AND s.expires_at > NOW()
  ORDER BY s.created_at DESC
  LIMIT 60
")->fetch_all(MYSQLI_ASSOC);

admin_ui_header("Security");
?>
<div class="grid">
  <section class="card">
    <h2>Login attempts</h2>
    <p class="sub">Recent authentication attempts (success/fail) for monitoring brute-force behavior.</p>

    <?php if (!$attempts): ?>
      <div class="alert">No login attempts yet.</div>
    <?php else: ?>
      <?php foreach ($attempts as $a): ?>
        <div class="alert" style="<?= ((int)$a['success']===0) ? 'border-color: rgba(255,107,107,.45);' : '' ?>">
          <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <b><?= ((int)$a['success']===1) ? "✅ Success" : "❌ Fail" ?></b>
            <span class="badge"><?= h(date("M d • g:i A", strtotime((string)$a['created_at']))) ?></span>
          </div>
          <div style="color:var(--muted); margin-top:6px; line-height:1.45;">
            Identifier: <?= h((string)$a['identifier']) ?><br/>
            User ID: <?= h((string)($a['user_id'] ?? '—')) ?><br/>
            IP: <?= h((string)($a['ip'] ?? '—')) ?><br/>
            <?php if (!empty($a['failure_reason'])): ?>
              Reason: <?= h((string)$a['failure_reason']) ?><br/>
            <?php endif; ?>
            <small>User-Agent: <?= h((string)($a['user_agent'] ?? '')) ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <section class="card">
    <h2>Active sessions</h2>
    <p class="sub">Currently active refresh sessions (revocation-ready).</p>

    <?php if (!$sessions): ?>
      <div class="alert">No active sessions.</div>
    <?php else: ?>
      <?php foreach ($sessions as $s): ?>
        <div class="alert">
          <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <b><?= h($s['username']) ?> <span class="badge">#<?= h((string)$s['user_id']) ?></span></b>
            <span class="badge">exp <?= h(date("M d • g:i A", strtotime((string)$s['expires_at']))) ?></span>
          </div>
          <div style="color:var(--muted); margin-top:6px; line-height:1.45;">
            IP: <?= h((string)($s['ip'] ?? '—')) ?><br/>
            Created: <?= h(date("M d • g:i A", strtotime((string)$s['created_at']))) ?><br/>
            Last seen: <?= h($s['last_seen_at'] ? date("M d • g:i A", strtotime((string)$s['last_seen_at'])) : '—') ?><br/>
            <small>User-Agent: <?= h((string)$s['user_agent']) ?></small>
          </div>
          <div style="margin-top:10px;">
            <a class="mini-btn" href="<?= h($bp) ?>/admin/users.php?user_id=<?= (int)$s['user_id'] ?>">Open user</a>
            <span class="mini-btn" style="opacity:.65; cursor:not-allowed;">Revoke (soon)</span>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</div>
<?php admin_ui_footer(); ?>