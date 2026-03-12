<?php
// admin/pending-users.php
session_start();

require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/helpers.php";
require_once __DIR__ . "/../includes/auth.php";

$bp = base_path();
$admin = current_user();
if (!$admin || !user_has_role($admin, 'admin')) {
  flash_set('err', 'Please sign in as an administrator.');
  header("Location: {$bp}/admin/login.php");
  exit;
}

$admin_id = (int)($admin['id'] ?? 0);
$adminName = $admin['username'] ?? $admin['email'] ?? 'Administrator';
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
          SET approval_status='approved', approved_by=?, approved_at=NOW(), rejected_reason=NULL
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
          SET approval_status='rejected', approved_by=?, approved_at=NOW(), rejected_reason=?
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
      flash_set('msg', 'Action saved.');
    } catch (Throwable $e) {
      $mysqli->rollback();
      flash_set('err', 'Failed: ' . $e->getMessage());
    }
  }

  header('Location: ' . $bp . '/admin/pending-users.php' . ($id > 0 ? ('?user_id=' . $id) : ''));
  exit;
}

$rows = $mysqli->query("
  SELECT id, username, email, created_at
  FROM users
  WHERE approval_status='pending' AND is_guest=0
  ORDER BY created_at ASC
  LIMIT 300
")->fetch_all(MYSQLI_ASSOC);

$err = flash_get('err');
$msg = flash_get('msg');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia Admin — Pending Approvals</title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/hub.css"/>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/adminstyle.css"/>
</head>
<body class="hub adminhub">
<header class="topnav">
  <div class="topnav__inner">
    <a class="logo" href="<?= h($bp) ?>/admin/index.php">
      <span class="logo__mark">CG</span>
      <span class="logo__text">Logia Administration</span>
    </a>
    <div class="navactions">
      <span class="pill">ADMIN</span>
      <span class="pill"><?= h($adminName) ?></span>
      <a class="btn btn-ghost" href="<?= h($bp) ?>/logout.php">Logout</a>
    </div>
  </div>
</header>

<main class="container admin-page">
  <?php if ($err): ?><div class="banner banner--bad"><?= h($err) ?></div><?php endif; ?>
  <?php if ($msg): ?><div class="banner banner--good"><?= h($msg) ?></div><?php endif; ?>

  <article class="admin-hero card admin-hero--compact">
    <div class="admin-hero__copy">
      <div class="chip">APPROVAL QUEUE • REGISTERED USERS ONLY</div>
      <h1>Pending Approvals</h1>
      <p class="lead">Approve to allow login. Reject to keep a reason on file and notify the user.</p>
      <div class="cta">
        <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/users.php">Back to Users</a>
        <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/reports.php">Open Reports</a>
      </div>
    </div>

    <div class="admin-hero__panel">
      <div class="statpanel">
        <div class="statgrid">
          <div class="stat">
            <div class="stat__label">Pending accounts</div>
            <div class="stat__value"><?= h((string)count($rows)) ?></div>
          </div>
          <div class="stat">
            <div class="stat__label">Queue scope</div>
            <div class="stat__value stat__value--small">Registered only</div>
          </div>
        </div>
      </div>
    </div>
  </article>

  <article class="admin-panel card-soft admin-panel--spaced">
    <div class="admin-panel__head">
      <h2>Approval Queue</h2>
    </div>

    <?php if (!$rows): ?>
      <div class="activity-item">
        <div class="activity-item__icon">•</div>
        <div>
          <div class="activity-item__title">No pending users</div>
        </div>
      </div>
    <?php else: ?>
      <div class="activity-list">
        <?php foreach ($rows as $r): $focus = ($user_id_focus > 0 && (int)$r['id'] === $user_id_focus); ?>
          <div class="queue-item queue-item--top <?= $focus ? 'queue-item--focus' : '' ?>">
            <div class="queue-item__main">
              <div class="queue-item__title"><?= h($r['username']) ?></div>
              <div class="queue-item__meta"><?= h($r['email']) ?></div>
              <div class="queue-item__meta">Created: <?= h(date('M d, Y • g:i A', strtotime((string)$r['created_at']))) ?></div>

              <div class="queue-item__actions">
                <a class="btn btn-ghost" href="<?= h($bp) ?>/admin/users.php?user_id=<?= (int)$r['id'] ?>">View full user</a>
              </div>
            </div>

            <form method="post" class="queue-form">
              <input type="hidden" name="user_id" value="<?= (int)$r['id'] ?>"/>
              <input name="reason" placeholder="Reject reason (optional)"/>
              <div class="queue-form__actions">
                <button class="btn btn-primary" name="do" value="approve" type="submit">Approve</button>
                <button class="btn btn-danger-soft" name="do" value="reject" type="submit">Reject</button>
              </div>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </article>
</main>

<footer class="sitefooter">
  <div class="sitefooter__inner">
    <div>
      <div class="footbrand">Logia Administration</div>
      <div class="footmuted">Pending approvals queue</div>
    </div>
  </div>
</footer>
</body>
</html>