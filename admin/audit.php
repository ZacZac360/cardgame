<?php
// admin/audit.php
session_start();

require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/admin_ui.php";

$bp = base_path();

$q = trim((string)($_GET['q'] ?? ''));
$action = trim((string)($_GET['action'] ?? ''));

// Simple query + filters
$sql = "
  SELECT a.id, a.actor_user_id, a.action, a.target_type, a.target_id, a.metadata_json, a.created_at
  FROM audit_logs a
  WHERE 1=1
";
$params = [];
$types = '';

if ($action !== '') {
  $sql .= " AND a.action = ? ";
  $params[] = $action;
  $types .= 's';
}
if ($q !== '') {
  // search action, target_type, metadata_json, target_id
  $sql .= " AND (a.action LIKE CONCAT('%', ?, '%')
              OR a.target_type LIKE CONCAT('%', ?, '%')
              OR a.metadata_json LIKE CONCAT('%', ?, '%')
              OR CAST(a.target_id AS CHAR) LIKE CONCAT('%', ?, '%')) ";
  $params[] = $q; $params[] = $q; $params[] = $q; $params[] = $q;
  $types .= 'ssss';
}

$sql .= " ORDER BY a.created_at DESC LIMIT 200 ";

$stmt = $mysqli->prepare($sql);
if ($types !== '') {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Distinct actions for dropdown (small)
$actions = $mysqli->query("SELECT DISTINCT action FROM audit_logs ORDER BY action")->fetch_all(MYSQLI_ASSOC);

admin_ui_header("Audit Logs");
?>
<div class="card">
  <h2>Audit logs</h2>
  <p class="sub">Immutable record of security and admin actions (latest 200).</p>

  <form method="get" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
    <div style="flex:1; min-width:220px;">
      <label>Search</label>
      <input name="q" value="<?= h($q) ?>" placeholder="action / target / metadata / id"/>
    </div>
    <div style="min-width:220px;">
      <label>Action</label>
      <select name="action" style="width:100%; padding:12px; border-radius:12px; border:1px solid var(--border); background:rgba(0,0,0,.18); color:var(--text);">
        <option value="">All actions</option>
        <?php foreach ($actions as $a): ?>
          <?php $val = (string)$a['action']; ?>
          <option value="<?= h($val) ?>" <?= ($val === $action) ? 'selected' : '' ?>><?= h($val) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="btns" style="margin-top:0;">
      <button class="btn btn-primary" type="submit">Filter</button>
      <a class="btn" href="<?= h($bp) ?>/admin/audit.php">Reset</a>
    </div>
  </form>

  <div style="margin-top:14px;">
    <?php if (!$rows): ?>
      <div class="alert">No audit entries found.</div>
    <?php else: ?>
      <?php foreach ($rows as $r): ?>
        <div class="alert">
          <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
            <b><?= h($r['action']) ?></b>
            <span class="badge"><?= h(date("M d • g:i A", strtotime((string)$r['created_at']))) ?></span>
          </div>
          <div style="color:var(--muted); margin-top:6px; line-height:1.45;">
            Actor: <?= h((string)($r['actor_user_id'] ?? 'system')) ?><br/>
            Target: <?= h($r['target_type']) ?> #<?= h($r['target_id'] ?? '—') ?><br/>
            <?php if (!empty($r['metadata_json'])): ?>
              <small>Metadata: <?= h((string)$r['metadata_json']) ?></small>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
<?php admin_ui_footer(); ?>