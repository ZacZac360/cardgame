<?php
// admin/pending-users.php
session_start();

require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/admin_ui.php";

$bp = base_path();
$admin = current_user();
$admin_id = (int)($admin['id'] ?? 0);

$user_id_focus = (int)($_GET['user_id'] ?? 0);

if (is_post()) {
  $id = (int)($_POST['user_id'] ?? 0);
  $do = (string)($_POST['do'] ?? '');
  $reason = trim((string)($_POST['reason'] ?? ''));

  if ($id > 0 && ($do === 'approve' || $do === 'reject')) {
    $mysqli->begin_transaction();
    try {
      if ($do === 'approve') {
        $stmt = $mysqli->prepare("
          UPDATE users
          SET approval_status='approved',
              approved_by=?,
              approved_at=NOW(),
              rejected_reason=NULL
          WHERE id=? AND approval_status='pending'
        ");
        $stmt->bind_param("ii", $admin_id, $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("
          INSERT INTO audit_logs (actor_user_id, action, target_type, target_id, metadata_json)
          VALUES (?, 'USER_APPROVE', 'user', ?, JSON_OBJECT('note','approved from admin queue'))
        ");
        $stmt->bind_param("ii", $admin_id, $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("
          INSERT INTO dashboard_notifications (user_id, type, title, body, link_url)
          VALUES (?, 'system', 'Account approved', 'You can now log in.', '/index.php')
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

      } else {
        if ($reason === '') $reason = 'Rejected by admin.';

        $stmt = $mysqli->prepare("
          UPDATE users
          SET approval_status='rejected',
              approved_by=?,
              approved_at=NOW(),
              rejected_reason=?
          WHERE id=? AND approval_status='pending'
        ");
        $stmt->bind_param("isi", $admin_id, $reason, $id);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("
          INSERT INTO audit_logs (actor_user_id, action, target_type, target_id, metadata_json)
          VALUES (?, 'USER_REJECT', 'user', ?, JSON_OBJECT('reason', ?))
        ");
        $stmt->bind_param("iis", $admin_id, $id, $reason);
        $stmt->execute();
        $stmt->close();

        $stmt = $mysqli->prepare("
          INSERT INTO dashboard_notifications (user_id, type, title, body, link_url)
          VALUES (?, 'system', 'Account rejected', ?, '/index.php')
        ");
        $stmt->bind_param("is", $id, $reason);
        $stmt->execute();
        $stmt->close();
      }

      $mysqli->commit();
      flash_set('msg', "Action saved.");
    } catch (Throwable $e) {
      $mysqli->rollback();
      flash_set('err', "Failed: " . $e->getMessage());
    }
  }
  redirect($bp . "/admin/pending-users.php");
}

$rows = $mysqli->query("
  SELECT id, username, email, created_at
  FROM users
  WHERE approval_status='pending'
  ORDER BY created_at ASC
  LIMIT 300
")->fetch_all(MYSQLI_ASSOC);

$err = flash_get('err');
$msg = flash_get('msg');

admin_ui_header("Pending Approvals");
?>
<?php if ($err): ?><div class="alert bad"><?= h($err) ?></div><?php endif; ?>
<?php if ($msg): ?><div class="alert good"><?= h($msg) ?></div><?php endif; ?>

<div class="card">
  <h2>Pending approvals</h2>
  <p class="sub">Approve to allow login. Reject records reason and blocks login.</p>

  <?php if (!$rows): ?>
    <div class="alert">No pending users.</div>
  <?php else: ?>
    <?php foreach ($rows as $r): ?>
      <?php $focus = ($user_id_focus > 0 && (int)$r['id'] === $user_id_focus); ?>
      <div class="alert" style="<?= $focus ? 'border-color: rgba(110,168,255,.6);' : '' ?>">
        <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
          <div>
            <b><?= h($r['username']) ?></b>
            <div style="color:var(--muted); margin-top:6px;">
              <?= h($r['email']) ?><br/>
              <small><?= h(date("M d • g:i A", strtotime((string)$r['created_at']))) ?></small>
            </div>
          </div>
          <div style="display:flex; gap:10px; align-items:flex-start; flex-wrap:wrap;">
            <a class="mini-btn" href="<?= h($bp) ?>/admin/users.php?user_id=<?= (int)$r['id'] ?>">View user</a>
          </div>
        </div>

        <form method="post" style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;">
          <input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>"/>
          <button class="btn btn-primary" name="do" value="approve" type="submit">Approve</button>
          <input name="reason" placeholder="Reject reason (optional)" style="flex:1; min-width:240px;"/>
          <button class="btn" style="border-color: rgba(255,107,107,.55); background: rgba(255,107,107,.18);" name="do" value="reject" type="submit">Reject</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php admin_ui_footer(); ?>