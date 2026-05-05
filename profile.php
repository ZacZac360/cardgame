<?php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";
require_once __DIR__ . "/includes/profile_helpers.php";

require_login();

$u  = current_user();
$bp = base_path();

$is_guest = ((int)($u['is_guest'] ?? 0) === 1);
if ($is_guest) {
  $_SESSION['flash_error'] = "Guest accounts cannot access profile, account, or security settings. Create an account to unlock these options.";
  header("Location: {$bp}/guest_dashboard.php");
  exit;
}

$flashSuccess = $_SESSION['flash_success'] ?? '';
$flashError   = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$tab = (string)($_GET['tab'] ?? 'overview');
$allowed_tabs = ['overview', 'avatar', 'bio', 'appearance', 'security', 'account', 'stats'];
if (!in_array($tab, $allowed_tabs, true)) {
  $tab = 'overview';
}

profile_handle_post($mysqli, $u, $bp, $tab);

$profile = profile_load_state($mysqli, $u);
extract($profile);

$recentMatches = [];

$stmt = $mysqli->prepare("
  SELECT
    id,
    title,
    body,
    link_url,
    created_at
  FROM dashboard_notifications
  WHERE user_id = ?
    AND type = 'match_result'
  ORDER BY created_at DESC
  LIMIT 8
");
$stmt->bind_param("i", $u['id']);
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
  $body = (string)($row['body'] ?? '');

  $place = null;
  if (preg_match('/Placed\\s+#(\\d+)/i', $body, $m)) {
    $place = (int)$m[1];
  }

  $xp = null;
  if (preg_match('/earned\\s+([\\d,]+)\\s+EXP/i', $body, $m)) {
    $xp = (int)str_replace(',', '', $m[1]);
  }

  $mode = 'Match';
  if (preg_match('/in a\\s+(.+?)\\s+match/i', $body, $m)) {
    $mode = ucfirst(trim((string)$m[1])) . ' Match';
  }

  $recentMatches[] = [
    'title' => (string)($row['title'] ?? 'Match Result'),
    'body' => $body,
    'place' => $place,
    'xp' => $xp,
    'mode' => $mode,
    'link_url' => (string)($row['link_url'] ?? ''),
    'created_at' => (string)($row['created_at'] ?? ''),
  ];
}
$stmt->close();

ui_header("Profile");
?>

<section class="section section--flush-top">
  <div class="hub-grid userpage-grid">

    <aside class="card userpanel userpanel--nav">
      <div class="userpanel__eyebrow">PROFILE</div>

      <nav class="usernav usernav--better">
        <a class="hub-item" href="<?= h($bp) ?>/dashboard.php">
          <span class="hub-ico">🏠</span>
          <span>Home</span>
        </a>

        <a class="hub-item <?= $tab === 'overview' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/profile.php?tab=overview">
          <span class="hub-ico">👤</span>
          <span>Overview</span>
        </a>

        <a class="hub-item <?= $tab === 'avatar' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/profile.php?tab=avatar">
          <span class="hub-ico">🖼️</span>
          <span>Avatar</span>
        </a>

        <a class="hub-item <?= $tab === 'bio' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/profile.php?tab=bio">
          <span class="hub-ico">📝</span>
          <span>Bio</span>
        </a>

        <a class="hub-item <?= $tab === 'appearance' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/profile.php?tab=appearance">
          <span class="hub-ico">🎨</span>
          <span>Appearance</span>
        </a>

        <a class="hub-item <?= $tab === 'security' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/profile.php?tab=security">
          <span class="hub-ico">🔐</span>
          <span>Security</span>
        </a>

        <a class="hub-item <?= $tab === 'account' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/profile.php?tab=account">
          <span class="hub-ico">⚙️</span>
          <span>Account</span>
        </a>

        <a class="hub-item <?= $tab === 'stats' ? 'is-active' : '' ?>" href="<?= h($bp) ?>/profile.php?tab=stats">
          <span class="hub-ico">📊</span>
          <span>Stats</span>
        </a>
      </nav>

      <div class="userpanel__stack userpanel__stack--block profile-nav-meta">
        <span class="pill"><?= h($roleLabel) ?></span>
        <span class="pill"><?= h($playerId) ?></span>
        <?php if ($approved): ?>
          <span class="pill pill-good">Approved</span>
        <?php else: ?>
          <span class="pill profile-pill-pending">Pending</span>
        <?php endif; ?>
      </div>
    </aside>

    <main class="user-maincol profile-maincol">
      <?php if ($flashSuccess): ?>
        <div class="card-soft alert-spaced profile-alert profile-alert--good">
          <?= h($flashSuccess) ?>
        </div>
      <?php endif; ?>

      <?php if ($flashError): ?>
        <div class="card-soft alert-spaced profile-alert profile-alert--bad">
          <?= h($flashError) ?>
        </div>
      <?php endif; ?>

      <div class="card hub-hero userhero profile-hero-card">
        <div class="profile-hero-layout">
          <div class="profile-hero-main">
            <?php if ($avatar !== ''): ?>
              <img
                src="<?= h($bp . '/' . ltrim($avatar, '/')) ?>"
                alt="Avatar"
                class="profile-avatar"
              >
            <?php else: ?>
              <div class="profile-avatar-fallback">
                <?= h($avatarInitial) ?>
              </div>
            <?php endif; ?>

            <div class="profile-hero-copy">
              <div class="profile-badge-row">
                <span class="pill"><?= h($roleLabel) ?></span>
                <span class="pill"><?= h($playerId) ?></span>
                <?php if ($emailVerified): ?>
                  <span class="pill pill-good">Email Verified</span>
                <?php else: ?>
                  <span class="pill profile-pill-danger">Email Pending</span>
                <?php endif; ?>
              </div>

              <h2 class="profile-name"><?= h($displayName !== '' ? $displayName : $username) ?></h2>

              <div class="profile-meta-row">
                <span class="note">Joined: <b><?= h($joinedAt) ?></b></span>
                <span class="note">Appearance: <b><?= h(ucfirst($appearanceMode)) ?></b></span>
                <span class="note">Completion: <b><?= (int)$profileCompletion ?>%</b></span>
              </div>
            </div>
          </div>

          <div class="profile-action-row">
            <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=avatar">Avatar</a>
            <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=appearance">Appearance</a>
            <a class="btn btn-primary" href="<?= h($bp) ?>/profile.php?tab=security">Security</a>
          </div>
        </div>
      </div>

      <?php if ($tab === 'overview'): ?>
        <section class="card profile-section-card">
          <div class="profile-section-head">
            <div>
              <h3 class="profile-section-title">Overview</h3>
            </div>
            <div class="profile-overview-actions">
              <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=bio">Edit Bio</a>
              <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=account">Account</a>
            </div>
          </div>

          <div class="profile-overview-grid">
            <div class="card-soft profile-stat-card">
              <div class="profile-stat-label">Username</div>
              <div class="profile-stat-value"><?= h($username) ?></div>
            </div>

            <div class="card-soft profile-stat-card">
              <div class="profile-stat-label">Player ID</div>
              <div class="profile-stat-value"><?= h($playerId) ?></div>
            </div>

            <div class="card-soft profile-stat-card">
              <div class="profile-stat-label">Email</div>
              <div class="profile-stat-value profile-stat-value--break"><?= h($email !== '' ? $email : '—') ?></div>
            </div>

            <div class="card-soft profile-stat-card">
              <div class="profile-stat-label">Joined</div>
              <div class="profile-stat-value"><?= h($joinedAt) ?></div>
            </div>
          </div>

          <div class="card-soft profile-bio-wrap">
            <div class="profile-bio-pad">
              <div class="profile-bio-label">Bio</div>
              <div class="profile-bio-text">
                <?= $bio !== '' ? nl2br(h($bio)) : '<span class="profile-bio-empty">—</span>' ?>
              </div>
            </div>
          </div>
        </section>

      <?php elseif ($tab === 'avatar'): ?>
        <section class="card profile-section-card">
          <div class="profile-section-head">
            <div>
              <h3 class="profile-section-title">Avatar</h3>
            </div>
            <span class="pill">Square</span>
          </div>

          <div class="profile-avatar-grid">
            <div class="card-soft profile-avatar-preview">
              <?php if ($avatar !== ''): ?>
                <img
                  src="<?= h($bp . '/' . ltrim($avatar, '/')) ?>"
                  alt="Avatar Preview"
                  class="profile-avatar-preview__img"
                >
              <?php else: ?>
                <div class="profile-avatar-preview__fallback">
                  <?= h($avatarInitial) ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="profile-form-stack">
              <form class="card-soft profile-form-card" method="post" enctype="multipart/form-data" action="">
                <div class="profile-form-title">Upload</div>
                <input type="file" name="avatar_file" accept="image/*">
                <div class="profile-form-actions">
                  <button class="btn btn-primary" type="submit">Save</button>
                  <button class="btn btn-ghost" type="reset">Reset</button>
                </div>
              </form>
            </div>
          </div>
        </section>

      <?php elseif ($tab === 'bio'): ?>
        <section class="card profile-section-card">
          <div class="profile-section-head profile-section-head--solo">
            <div>
              <h3 class="profile-section-title">Bio</h3>
            </div>
          </div>

          <form method="post" action="" class="profile-form-stack">
            <div class="card-soft profile-form-card">
              <label class="profile-field-label" for="display_name">Display Name</label>
              <input id="display_name" type="text" name="display_name" value="<?= h($displayName) ?>" maxlength="40">
            </div>

            <div class="card-soft profile-form-card">
              <label class="profile-field-label" for="bio">Bio</label>
              <textarea
                id="bio"
                name="bio"
                rows="6"
                maxlength="280"
                placeholder="Tell other players about yourself..."
                class="profile-textarea"
              ><?= h($bio) ?></textarea>

              <div id="bioCount" class="profile-counter">0 / 280</div>
            </div>

            <div class="profile-form-actions">
              <button class="btn btn-primary" type="submit">Save Changes</button>
              <button class="btn btn-ghost" type="reset">Reset</button>
            </div>
          </form>
        </section>

      <?php elseif ($tab === 'appearance'): ?>
        <section class="card profile-section-card">
          <div class="profile-section-head">
            <div>
              <h3 class="profile-section-title">Appearance</h3>
            </div>
            <span class="pill"><?= h(ucfirst($appearanceMode)) ?></span>
          </div>

          <form method="post" action="<?= h($bp) ?>/profile.php?tab=appearance" class="profile-form-stack">
            <div class="card-soft profile-form-card">
              <label class="profile-field-label">Choose Mode</label>

              <div class="profile-choice-grid">
                <label class="card-soft profile-choice-card">
                  <span class="profile-choice-card__top">
                    <input type="radio" name="appearance_mode" value="default" <?= $appearanceMode === 'default' ? 'checked' : '' ?>>
                    <strong>Default</strong>
                  </span>
                  <span class="profile-choice-card__meta">Current site look. Blue/green base style.</span>
                </label>

                <label class="card-soft profile-choice-card">
                  <span class="profile-choice-card__top">
                    <input type="radio" name="appearance_mode" value="light" <?= $appearanceMode === 'light' ? 'checked' : '' ?>>
                    <strong>Light</strong>
                  </span>
                  <span class="profile-choice-card__meta">Brighter look, still blue/green.</span>
                </label>
              </div>
            </div>

            <div class="card-soft profile-form-card">
              <div class="profile-copy-muted">
                This setting is saved to your account and applies across the platform while logged in.
              </div>
            </div>

            <div class="profile-form-actions">
              <button class="btn btn-primary" type="submit">Apply</button>
            </div>
          </form>
        </section>

      <?php elseif ($tab === 'security'): ?>
        <section class="card profile-section-card">
          <div class="profile-section-head profile-section-head--solo">
            <div>
              <h3 class="profile-section-title">Security</h3>
            </div>
          </div>

          <div class="profile-form-stack">
            <div class="card-soft profile-form-card">
              <div class="profile-split-head">
                <div class="profile-form-title">Email</div>
                <?php if ($emailVerified): ?>
                  <span class="pill pill-good">Verified</span>
                <?php else: ?>
                  <span class="pill profile-pill-danger">Pending</span>
                <?php endif; ?>
              </div>

              <div class="profile-form-stack profile-form-stack--tight">
                <div class="note">Address: <b><?= h($email !== '' ? $email : '—') ?></b></div>

                <div class="profile-dashed-box">
                  <div class="profile-form-title">Verification</div>

                  <?php if ($emailVerified): ?>
                    <div class="note">Verified on <b><?= h(date("M d, Y • g:i A", strtotime((string)$u['email_verified_at']))) ?></b></div>
                  <?php else: ?>
                    <form method="post" action="<?= h($bp) ?>/api/email/send-otp.php" class="profile-inline-form">
                      <button class="btn btn-primary" type="submit">Send Code</button>
                    </form>

                    <form method="post" action="<?= h($bp) ?>/api/email/verify-otp.php" class="profile-inline-form">
                      <input type="text" name="code" maxlength="6" placeholder="Enter code" class="profile-code-input">
                      <button class="btn btn-primary" type="submit">Verify</button>
                    </form>
                  <?php endif; ?>
                </div>
              </div>
            </div>

            <div class="card-soft profile-form-card">
              <div class="profile-split-head">
                <div class="profile-form-title">2FA</div>
                <?php if ($twofaEnabled): ?>
                  <span class="pill pill-good">Enabled</span>
                <?php else: ?>
                  <span class="pill profile-pill-danger">Off</span>
                <?php endif; ?>
              </div>

              <div class="profile-form-actions">
                <?php if ($twofaEnabled): ?>
                  <a class="btn btn-ghost" href="<?= h($bp) ?>/api/2fa/setup.php">Reconfigure</a>
                  <a class="btn btn-ghost" href="<?= h($bp) ?>/api/2fa/backup-codes.php">Backup Codes</a>

                  <form method="post" action="<?= h($bp) ?>/api/2fa/disable.php" class="profile-inline-action-form" onsubmit="return confirm('Disable two-factor authentication?');">
                    <button class="btn btn-ghost" type="submit">Disable 2FA</button>
                  </form>
                <?php else: ?>
                  <a class="btn btn-ghost" href="<?= h($bp) ?>/api/2fa/setup.php">Set Up</a>
                <?php endif; ?>
              </div>
            </div>

            <div class="card-soft profile-form-card">
              <div class="profile-form-title">Password</div>
              <form method="post" action="" class="profile-form-stack profile-form-stack--tight">
                <input type="password" name="current_password" placeholder="Current password" autocomplete="current-password">
                <input type="password" name="new_password" placeholder="New password" autocomplete="new-password">
                <input type="password" name="confirm_password" placeholder="Confirm password" autocomplete="new-password">

                <div class="profile-copy-muted">
                  Your new password must meet the same rules as registration.
                </div>

                <ul class="pw-req profile-password-list">
                  <li>At least 16 characters</li>
                  <li>Lowercase letter</li>
                  <li>Uppercase letter</li>
                  <li>Number</li>
                  <li>Special character</li>
                </ul>

                <div class="profile-form-actions">
                  <button class="btn btn-primary" type="submit">Update Password</button>
                </div>
              </form>
            </div>
          </div>
        </section>

      <?php elseif ($tab === 'account'): ?>
        <section class="card profile-section-card">
          <div class="profile-section-head profile-section-head--solo">
            <div>
              <h3 class="profile-section-title">Account</h3>
            </div>
          </div>

          <div class="profile-form-stack">
            <form method="post" action="" class="card-soft profile-form-card">
              <div class="profile-form-title">Identity</div>
              <div class="profile-two-col-grid">
                <div>
                  <label class="profile-field-label" for="username">Username</label>
                  <input id="username" type="text" name="username" value="<?= h($username) ?>" maxlength="40">
                </div>
                <div>
                  <label class="profile-field-label" for="email">Email</label>
                  <input id="email" type="email" name="email" value="<?= h($email) ?>">
                </div>
              </div>

              <div class="profile-form-actions">
                <button class="btn btn-primary" type="submit">Save Changes</button>
              </div>
            </form>
          </div>
        </section>

      <?php elseif ($tab === 'stats'): ?>
        <section class="card profile-section-card">
          <div class="profile-section-head profile-section-head--solo">
            <div>
              <h3 class="profile-section-title">Stats</h3>
            </div>
          </div>

          <div class="profile-stats-grid">
            <div class="card-soft profile-stat-card">
              <div class="profile-stat-label">Level</div>
              <div class="profile-stat-number"><?= (int)$level ?></div>
            </div>

            <div class="card-soft profile-stat-card">
              <div class="profile-stat-label">Matches</div>
              <div class="profile-stat-number"><?= (int)$matchesPlayed ?></div>
            </div>

            <div class="card-soft profile-stat-card">
              <div class="profile-stat-label">Wins</div>
              <div class="profile-stat-number"><?= (int)$matchesWon ?></div>
            </div>

            <div class="card-soft profile-stat-card">
              <div class="profile-stat-label">Credits</div>
              <div class="profile-stat-number"><?= number_format($credits) ?></div>
            </div>
          </div>

          <div class="card-soft profile-bio-wrap">
            <div class="profile-bio-pad">
              <div class="profile-form-title">Recent Match History</div>

              <?php if (empty($recentMatches)): ?>
                <div class="profile-bio-empty">No completed matches yet.</div>
              <?php else: ?>
                <div class="profile-match-list">
                  <?php foreach ($recentMatches as $match): ?>
                    <?php
                      $matchHref = trim((string)$match['link_url']);
                      if ($matchHref !== '') {
                        $matchHref = $bp . '/' . ltrim($matchHref, '/');
                      }

                      $createdAt = trim((string)$match['created_at']);
                      $createdLabel = $createdAt !== ''
                        ? date("M d, Y • g:i A", strtotime($createdAt))
                        : '';
                    ?>

                    <?php if ($matchHref !== ''): ?>
                      <a class="profile-match-row" href="<?= h($matchHref) ?>">
                    <?php else: ?>
                      <div class="profile-match-row">
                    <?php endif; ?>

                        <div class="profile-match-place">
                          <?= $match['place'] ? '#' . (int)$match['place'] : '—' ?>
                        </div>

                        <div class="profile-match-copy">
                          <div class="profile-match-title">
                            <?= h($match['mode']) ?>
                          </div>
                          <div class="profile-match-meta">
                            <?= h($createdLabel) ?>
                          </div>
                        </div>

                        <div class="profile-match-xp">
                          <?= $match['xp'] !== null ? '+' . number_format((int)$match['xp']) . ' EXP' : '—' ?>
                        </div>

                    <?php if ($matchHref !== ''): ?>
                      </a>
                    <?php else: ?>
                      </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </section>
      <?php endif; ?>
    </main>

    <aside class="card userpanel userpanel--side profile-side-card">
      <div class="userpanel__head">
        <div>
          <div class="userpanel__title profile-side-title">Status</div>
          <div class="profile-copy-muted"><?= (int)$profileCompletion ?>%</div>
        </div>
      </div>

      <div class="profile-side-stack">
        <div class="card-soft profile-side-row">
          <div class="profile-side-row__split"><span>Approval</span><strong><?= $approved ? '✅' : '⏳' ?></strong></div>
        </div>
        <div class="card-soft profile-side-row">
          <div class="profile-side-row__split"><span>Email</span><strong><?= $emailVerified ? '✅' : '❌' ?></strong></div>
        </div>
        <div class="card-soft profile-side-row">
          <div class="profile-side-row__split"><span>2FA</span><strong><?= $twofaEnabled ? '✅' : '❌' ?></strong></div>
        </div>
        <div class="card-soft profile-side-row">
          <div class="profile-side-row__split"><span>Avatar</span><strong><?= !empty($avatar) ? '✅' : '❌' ?></strong></div>
        </div>
        <div class="card-soft profile-side-row">
          <div class="profile-side-row__split"><span>Bio</span><strong><?= $bio !== '' ? '✅' : '❌' ?></strong></div>
        </div>
      </div>
    </aside>

  </div>
</section>

<?php ui_footer(); ?>
