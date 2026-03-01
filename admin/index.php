<?php
// admin/index.php
session_start();

require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/admin_ui.php";

$bp = base_path();

$k = admin_kpis($mysqli);

// pending preview
$pending = $mysqli->query("
  SELECT id, username, email, created_at
  FROM users
  WHERE approval_status='pending'
  ORDER BY created_at ASC
  LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

// audit preview
$audit = $mysqli->query("
  SELECT id, actor_user_id, action, target_type, target_id, created_at
  FROM audit_logs
  ORDER BY created_at DESC
  LIMIT 12
")->fetch_all(MYSQLI_ASSOC);

admin_ui_header("Overview");
?>
<div class="grid">
  <section class="card">
    <h2>Overview</h2>
    <p class="sub">Operational snapshot of approvals and security signals.</p>

    <div class="grid" style="grid-template-columns: repeat(2, 1fr); gap:12px;">
      <div class="alert">
        <b>Pending approvals</b><br/>
        <span style="color:var(--muted);"><?= (int)$k['pending'] ?> accounts</span>
      </div>
      <div class="alert">
        <b>Failed logins (24h)</b><br/>
        <span style="color:var(--muted);"><?= (int)$k['fails24'] ?> attempts</span>
      </div>
      <div class="alert">
        <b>Active sessions</b><br/>
        <span style="color:var(--muted);"><?= (int)$k['activeSessions'] ?> sessions</span>
      </div>
      <div class="alert">
        <b>Banned users</b><br/>
        <span style="color:var(--muted);"><?= (int)$k['banned'] ?> active bans</span>
      </div>
    </div>

    <div class="btns" style="margin-top:14px;">
      <a class="btn btn-primary" href="<?= h($bp) ?>/admin/pending-users.php">Review Pending</a>
      <a class="btn" href="<?= h($bp) ?>/admin/security.php">Security Monitor</a>
      <a class="btn" href="<?= h($bp) ?>/admin/audit.php">Audit Logs</a>
    </div>
  </section>

  <section class="card">
    <h2>Pending approvals</h2>
    <p class="sub">Oldest requests first (fair queue).</p>

    <?php if (!$pending): ?>
      <div class="alert">No pending users right now.</div>
    <?php else: ?>
      <?php foreach ($pending as $p): ?>
        <div class="alert">
          <b><?= h($p['username']) ?></b>
          <div style="color:var(--muted); margin-top:6px;">
            <?= h($p['email']) ?><br/>
            <small><?= h(date("M d • g:i A", strtotime((string)$p['created_at']))) ?></small>
          </div>
          <div style="margin-top:10px;">
            <a class="mini-btn" href="<?= h($bp) ?>/admin/pending-users.php?user_id=<?= (int)$p['id'] ?>">Open</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</div>

<div class="grid" style="margin-top:18px;">
  <section class="card">
    <h2>Recent audit events</h2>
    <p class="sub">Security-relevant actions recorded for traceability.</p>

    <?php if (!$audit): ?>
      <div class="alert">No audit logs yet.</div>
    <?php else: ?>
      <?php foreach ($audit as $a): ?>
        <div class="alert">
          <b><?= h($a['action']) ?></b>
          <div style="color:var(--muted); margin-top:6px;">
            Target: <?= h($a['target_type']) ?> #<?= h($a['target_id'] ?? '—') ?><br/>
            Actor: <?= h((string)($a['actor_user_id'] ?? 'system')) ?><br/>
            <small><?= h(date("M d • g:i A", strtotime((string)$a['created_at']))) ?></small>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <section class="card">
    <h2>Admin controls</h2>
    <p class="sub">Planned modules (security-first roadmap).</p>
    <div class="alert">
      <b>Coming soon</b>
      <div style="color:var(--muted); margin-top:6px; line-height:1.55;">
        • Player penalties & moderation tools<br/>
        • Card change approvals & patch notes<br/>
        • Automated security alerts (spike detection)<br/>
        • Exportable audit reports
      </div>
    </div>
  </section>
</div>
<?php admin_ui_footer(); ?>