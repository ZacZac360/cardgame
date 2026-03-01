<?php
session_start();
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();
$u = current_user();
$uid = (int)$u['id'];

$stmt = $mysqli->prepare("
  SELECT id, type, title, body, link_url, is_read, created_at
  FROM dashboard_notifications
  WHERE user_id = ?
  ORDER BY created_at DESC
  LIMIT 200
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

ui_header("Notifications");
?>
<div class="card">
  <h2>All notifications</h2>
  <p class="sub">Full history (latest 200).</p>

  <?php if (!$rows): ?>
    <div class="alert">No notifications yet.</div>
  <?php else: ?>
    <?php foreach ($rows as $n): ?>
      <div class="alert" style="<?= ((int)$n['is_read']===0) ? 'border-color: rgba(110,168,255,.45);' : '' ?>">
        <b><?= h($n['title']) ?></b>
        <div style="color:var(--muted); margin-top:6px; line-height:1.4;">
          <?= h($n['body'] ?? '') ?><br/>
          <small><?= h(date("M d • g:i A", strtotime((string)$n['created_at']))) ?></small>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php ui_footer(); ?>