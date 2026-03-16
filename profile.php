<?php
//cardgame/index.php (user side)
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";
require_once __DIR__ . "/includes/profile_helpers.php";

require_login();

$u  = current_user();
$bp = base_path();
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

ui_header("Profile");
?>

<section class="section" style="padding-top:0;">
  <div class="hub-grid">

    <!-- LEFT -->
    <aside class="card hub-left" style="padding:14px; position:sticky; top:86px;">
      <div style="font-weight:950; letter-spacing:.02em; opacity:.9; margin-bottom:10px;">
        PROFILE
      </div>

      <div style="display:grid; gap:10px;">
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
      </div>

      <div style="margin-top:14px; padding-top:14px; border-top:1px solid rgba(255,255,255,.08); display:grid; gap:8px;">
        <span class="pill"><?= h($roleLabel) ?></span>
        <span class="pill"><?= h($playerId) ?></span>

        <?php if ($approved): ?>
          <span class="pill" style="border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10);">Approved</span>
        <?php else: ?>
          <span class="pill" style="border-color: rgba(255,205,102,.45); background: rgba(255,205,102,.10);">Pending</span>
        <?php endif; ?>
      </div>
    </aside>

    <!-- CENTER -->
    <main style="min-width:0;">
      <?php if ($flashSuccess): ?>
        <div class="alert" style="margin-bottom:12px; border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10);">
          <?= h($flashSuccess) ?>
        </div>
      <?php endif; ?>

      <?php if ($flashError): ?>
        <div class="alert" style="margin-bottom:12px; border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">
          <?= h($flashError) ?>
        </div>
      <?php endif; ?>

      <div class="card hub-hero">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:16px; position:relative; z-index:1; flex-wrap:wrap;">
          <div style="display:flex; gap:14px; align-items:center; min-width:0;">
            <?php if ($avatar !== ''): ?>
              <img
                src="<?= h($bp . '/' . ltrim($avatar, '/')) ?>"
                alt="Avatar"
                style="width:72px; height:72px; border-radius:22px; object-fit:cover; border:1px solid rgba(255,255,255,.14); background:rgba(255,255,255,.05);"
              >
            <?php else: ?>
              <div style="width:72px; height:72px; border-radius:22px; display:grid; place-items:center; font-weight:1000; font-size:28px; border:1px solid rgba(255,255,255,.14); background:rgba(255,255,255,.06);">
                <?= h($avatarInitial) ?>
              </div>
            <?php endif; ?>

            <div style="min-width:0;">
              <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:8px;">
                <span class="pill"><?= h($roleLabel) ?></span>
                <span class="pill"><?= h($playerId) ?></span>
                <?php if ($emailVerified): ?>
                  <span class="pill" style="border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10);">Email Verified</span>
                <?php else: ?>
                  <span class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">Email Pending</span>
                <?php endif; ?>
              </div>

              <h2 style="margin:0 0 6px;"><?= h($displayName !== '' ? $displayName : $username) ?></h2>

              <div style="display:flex; flex-wrap:wrap; gap:8px;">
                <span class="note">Joined: <b><?= h($joinedAt) ?></b></span>
                <span class="note">Appearance: <b><?= h(ucfirst($appearanceMode)) ?></b></span>
                <span class="note">Completion: <b><?= (int)$profileCompletion ?>%</b></span>
              </div>
            </div>
          </div>

          <div style="display:flex; gap:8px; flex-wrap:wrap;">
            <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=avatar">Avatar</a>
            <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=appearance">Appearance</a>
            <a class="btn btn-primary" href="<?= h($bp) ?>/profile.php?tab=security">Security</a>
          </div>
        </div>
      </div>

      <div style="margin-top:12px; display:grid; gap:12px;">
        <?php if ($tab === 'overview'): ?>
          <div class="card" style="padding:16px;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
              <div style="font-weight:950;">Overview</div>
              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=bio">Edit Bio</a>
                <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=account">Account</a>
              </div>
            </div>

            <div style="margin-top:14px; display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px;">
              <div class="card-soft" style="padding:14px;">
                <div style="font-size:12px; color:var(--muted); margin-bottom:6px;">Username</div>
                <div style="font-weight:900;"><?= h($username) ?></div>
              </div>

              <div class="card-soft" style="padding:14px;">
                <div style="font-size:12px; color:var(--muted); margin-bottom:6px;">Player ID</div>
                <div style="font-weight:900;"><?= h($playerId) ?></div>
              </div>

              <div class="card-soft" style="padding:14px;">
                <div style="font-size:12px; color:var(--muted); margin-bottom:6px;">Email</div>
                <div style="font-weight:900; word-break:break-word;"><?= h($email !== '' ? $email : '—') ?></div>
              </div>

              <div class="card-soft" style="padding:14px;">
                <div style="font-size:12px; color:var(--muted); margin-bottom:6px;">Joined</div>
                <div style="font-weight:900;"><?= h($joinedAt) ?></div>
              </div>
            </div>

            <div style="margin-top:12px;" class="card-soft">
              <div style="padding:14px;">
                <div style="font-size:12px; color:var(--muted); margin-bottom:8px;">Bio</div>
                <div style="line-height:1.6; color:var(--text);">
                  <?= $bio !== '' ? nl2br(h($bio)) : '<span style="color:var(--muted);">—</span>' ?>
                </div>
              </div>
            </div>
          </div>

        <?php elseif ($tab === 'avatar'): ?>
          <div class="card" style="padding:16px;">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
              <div style="font-weight:950;">Avatar</div>
              <span class="pill">Square</span>
            </div>

            <div style="margin-top:14px; display:grid; grid-template-columns: 220px minmax(0,1fr); gap:16px;">
              <div class="card-soft" style="padding:16px; display:grid; place-items:center;">
                <?php if ($avatar !== ''): ?>
                  <img
                    src="<?= h($bp . '/' . ltrim($avatar, '/')) ?>"
                    alt="Avatar Preview"
                    style="width:156px; height:156px; border-radius:28px; object-fit:cover; border:1px solid rgba(255,255,255,.14); background:rgba(255,255,255,.05);"
                  >
                <?php else: ?>
                  <div style="width:156px; height:156px; border-radius:28px; display:grid; place-items:center; font-weight:1000; font-size:54px; border:1px solid rgba(255,255,255,.14); background:rgba(255,255,255,.06);">
                    <?= h($avatarInitial) ?>
                  </div>
                <?php endif; ?>
              </div>

              <div style="display:grid; gap:12px;">
                <form class="card-soft" style="padding:14px;" method="post" enctype="multipart/form-data" action="">
                  <div style="font-weight:900; margin-bottom:10px;">Upload</div>
                  <input type="file" name="avatar_file" accept="image/*" class="input">
                  <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                    <button class="btn btn-primary" type="submit">Save</button>
                    <button class="btn btn-ghost" type="button">Reset</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

        <?php elseif ($tab === 'bio'): ?>
          <div class="card" style="padding:16px;">
            <div style="font-weight:950;">Bio</div>

            <form method="post" action="" style="margin-top:14px; display:grid; gap:12px;">
              <div class="card-soft" style="padding:14px;">
                <label style="display:block; font-size:12px; color:var(--muted); margin-bottom:8px;">Display Name</label>
                <input class="input" type="text" name="display_name" value="<?= h($displayName) ?>" maxlength="40">
              </div>

              <div class="card-soft" style="padding:14px;">
                <label style="display:block; font-size:12px; color:var(--muted); margin-bottom:8px;">
                  Bio
                </label>

                <textarea
                  name="bio"
                  rows="6"
                  maxlength="280"
                  placeholder="Tell other players about yourself..."
                  style="
                    width:100%;
                    min-height:120px;
                    resize:vertical;
                    padding:12px 14px;
                    border-radius:12px;
                    border:1px solid var(--border);
                    background:var(--surface-1);
                    color:var(--text);
                    font-family:inherit;
                    font-size:14px;
                    line-height:1.5;
                  "
                ><?= h($bio) ?></textarea>

                <div id="bioCount" style="margin-top:6px;font-size:11px;color:var(--muted);">
                0 / 280
                </div>
              </div>

              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button class="btn btn-primary" type="submit">Save Changes</button>
                <button class="btn btn-ghost" type="reset">Reset</button>
              </div>
            </form>
          </div>

        <?php elseif ($tab === 'appearance'): ?>
  <div class="card" style="padding:16px;">
    <div style="display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;">
      <div style="font-weight:950;">Appearance</div>
      <span class="pill"><?= h(ucfirst($appearanceMode)) ?></span>
    </div>

    <form method="post" action="<?= h($bp) ?>/profile.php?tab=appearance" style="margin-top:14px; display:grid; gap:12px;">
      <div class="card-soft" style="padding:14px;">
        <label style="display:block; font-size:12px; color:var(--muted); margin-bottom:10px;">Choose Mode</label>

        <div style="display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap:10px;">
          <label class="card-soft" style="padding:14px; cursor:pointer; border:1px solid rgba(255,255,255,.08);">
            <input type="radio" name="appearance_mode" value="default" <?= $appearanceMode === 'default' ? 'checked' : '' ?> style="margin-right:8px;">
            <strong>Default</strong>
            <div style="margin-top:6px; color:var(--muted); font-size:13px;">
              Current site look. Blue/green base style.
            </div>
          </label>

          <label class="card-soft" style="padding:14px; cursor:pointer; border:1px solid rgba(255,255,255,.08);">
            <input type="radio" name="appearance_mode" value="dark" <?= $appearanceMode === 'dark' ? 'checked' : '' ?> style="margin-right:8px;">
            <strong>Dark</strong>
            <div style="margin-top:6px; color:var(--muted); font-size:13px;">
              Darker surfaces, same blue/green identity.
            </div>
          </label>

          <label class="card-soft" style="padding:14px; cursor:pointer; border:1px solid rgba(255,255,255,.08);">
            <input type="radio" name="appearance_mode" value="light" <?= $appearanceMode === 'light' ? 'checked' : '' ?> style="margin-right:8px;">
            <strong>Light</strong>
            <div style="margin-top:6px; color:var(--muted); font-size:13px;">
              Brighter look, still blue/green.
            </div>
          </label>
        </div>
      </div>

      <div class="card-soft" style="padding:14px;">
        <div style="color:var(--muted); font-size:13px;">
          This setting is saved to your account and applies across the platform while logged in.
        </div>
      </div>

      <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <button class="btn btn-primary" type="submit">Apply</button>
        <a class="btn btn-ghost" href="<?= h($bp) ?>/profile.php?tab=appearance">Reset</a>
      </div>
    </form>
  </div>

        <?php elseif ($tab === 'security'): ?>
          <div class="card" style="padding:16px;">
            <div style="font-weight:950;">Security</div>

            <div style="margin-top:14px; display:grid; gap:12px;">
              <div class="card-soft" style="padding:14px;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                  <div style="font-weight:900;">Email</div>
                  <?php if ($emailVerified): ?>
                    <span class="pill" style="border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10);">Verified</span>
                  <?php else: ?>
                    <span class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">Pending</span>
                  <?php endif; ?>
                </div>

                <div style="margin-top:12px; display:grid; gap:10px;">
                  <div class="note">Address: <b><?= h($email !== '' ? $email : '—') ?></b></div>

                  <div style="padding:14px; border-radius:16px; border:1px dashed rgba(255,255,255,.12); background:rgba(255,255,255,.03);">
                    <div style="font-weight:900; margin-bottom:10px;">Verification</div>

                    <?php if ($emailVerified): ?>
                      <div class="note">Verified on <b><?= h(date("M d, Y • g:i A", strtotime((string)$u['email_verified_at']))) ?></b></div>
                    <?php else: ?>
                      <form method="post" action="<?= h($bp) ?>/api/email/send-otp.php" style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px;">
                        <button class="btn btn-primary" type="submit">Send Code</button>
                      </form>

                      <form method="post" action="<?= h($bp) ?>/api/email/verify-otp.php" style="display:flex; gap:8px; flex-wrap:wrap;">
                        <input class="input" type="text" name="code" maxlength="6" placeholder="Enter code" style="max-width:180px;">
                        <button class="btn btn-primary" type="submit">Verify</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="card-soft" style="padding:14px;">
                <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                  <div style="font-weight:900;">2FA</div>
                  <?php if ($twofaEnabled): ?>
                    <span class="pill" style="border-color: rgba(57,255,106,.35); background: rgba(57,255,106,.10);">Enabled</span>
                  <?php else: ?>
                    <span class="pill" style="border-color: rgba(255,77,109,.40); background: rgba(255,77,109,.10);">Off</span>
                  <?php endif; ?>
                </div>

                <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                  <?php if ($twofaEnabled): ?>
                    <a class="btn btn-ghost" href="/cardgame/api/2fa/setup.php">Reconfigure</a>
                    <a class="btn btn-ghost" href="/cardgame/api/2fa/backup-codes.php">Backup Codes</a>
                    <a class="btn btn-ghost" href="/cardgame/api/2fa/disable.php"
                      onclick="return confirm('Disable two-factor authentication?');">
                      Disable 2FA
                    </a>
                  <?php else: ?>
                    <a class="btn btn-ghost" href="/cardgame/api/2fa/setup.php">Set Up</a>
                  <?php endif; ?>
                </div>
              </div>

              <div class="card-soft" style="padding:14px;">
                <div style="font-weight:900; margin-bottom:12px;">Password</div>
                <form method="post" action="" style="display:grid; gap:10px;">
                  <input class="input" type="password" name="current_password" placeholder="Current password" autocomplete="current-password">
                  <input class="input" type="password" name="new_password" placeholder="New password" autocomplete="new-password">
                  <input class="input" type="password" name="confirm_password" placeholder="Confirm password" autocomplete="new-password">

                  <div style="color:var(--muted); font-size:13px;">
                    Your new password must meet the same rules as registration.
                  </div>

                  <ul class="pw-req" style="margin:0 0 0 18px; padding:0; color:var(--muted); font-size:13px; line-height:1.5;">
                    <li>At least 16 characters</li>
                    <li>Lowercase letter</li>
                    <li>Uppercase letter</li>
                    <li>Number</li>
                    <li>Special character</li>
                  </ul>

                  <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <button class="btn btn-primary" type="submit">Update Password</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

        <?php elseif ($tab === 'account'): ?>
          <div class="card" style="padding:16px;">
            <div style="font-weight:950;">Account</div>

            <div style="margin-top:14px; display:grid; gap:12px;">
              <form method="post" action="" class="card-soft" style="padding:14px;">
                <div style="font-weight:900; margin-bottom:12px;">Identity</div>
                <div style="display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px;">
                  <div>
                    <label style="display:block; font-size:12px; color:var(--muted); margin-bottom:8px;">Username</label>
                    <input class="input" type="text" name="username" value="<?= h($username) ?>" maxlength="40">
                  </div>
                  <div>
                    <label style="display:block; font-size:12px; color:var(--muted); margin-bottom:8px;">Email</label>
                    <input class="input" type="email" name="email" value="<?= h($email) ?>">
                  </div>
                </div>

                <div style="margin-top:12px; display:flex; gap:8px; flex-wrap:wrap;">
                  <button class="btn btn-primary" type="submit">Save Changes</button>
                </div>
              </form>

              <div class="card-soft" style="padding:14px;">
                <div style="font-weight:900; margin-bottom:12px;">Sessions</div>
                <div style="display:grid; gap:10px;">
                  <div style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.03); display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                    <div>
                      <div style="font-weight:900;">Current Session</div>
                      <div style="color:var(--muted); font-size:13px; margin-top:4px;">Browser</div>
                    </div>
                    <button class="btn btn-ghost" type="button">Keep</button>
                  </div>

                  <div style="padding:12px; border-radius:14px; border:1px solid rgba(255,255,255,.08); background:rgba(255,255,255,.03); display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                    <div>
                      <div style="font-weight:900;">Other Sessions</div>
                      <div style="color:var(--muted); font-size:13px; margin-top:4px;">Available when connected</div>
                    </div>
                    <button class="btn btn-ghost" type="button">Log Out Others</button>
                  </div>
                </div>
              </div>

              <div class="card-soft" style="padding:14px; border:1px solid rgba(255,77,109,.18);">
                <div style="font-weight:900; margin-bottom:12px;">Danger Zone</div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                  <button class="btn btn-ghost" type="button">Deactivate</button>
                  <button class="btn btn-ghost" type="button">Delete Account</button>
                </div>
              </div>
            </div>
          </div>

        <?php elseif ($tab === 'stats'): ?>
          <div class="card" style="padding:16px;">
            <div style="font-weight:950;">Stats</div>

          <div style="margin-top:14px; display:grid; grid-template-columns: repeat(4, minmax(0,1fr)); gap:12px;">
            <div class="card-soft" style="padding:14px;">
              <div style="font-size:12px; color:var(--muted); margin-bottom:6px;">Level</div>
              <div style="font-size:24px; font-weight:1000;"><?= $level ?></div>
            </div>

            <div class="card-soft" style="padding:14px;">
              <div style="font-size:12px; color:var(--muted); margin-bottom:6px;">Matches</div>
              <div style="font-size:24px; font-weight:1000;"><?= $matchesPlayed ?> </div>
            </div>

            <div class="card-soft" style="padding:14px;">
              <div style="font-size:12px; color:var(--muted); margin-bottom:6px;">Wins</div>
              <div style="font-size:24px; font-weight:1000;"><?= $matchesWon ?></div>
            </div>

            <div class="card-soft" style="padding:14px;">
              <div style="font-size:12px; color:var(--muted); margin-bottom:6px;">Credits</div>
              <div style="font-size:24px; font-weight:1000;"><?= number_format($credits) ?></div>
            </div>
          </div>

            <div style="margin-top:12px;" class="card-soft">
              <div style="padding:14px;">
                <div style="font-weight:900; margin-bottom:10px;">Recent</div>
                <div style="color:var(--muted);">—</div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </main>

    <!-- RIGHT -->
    <aside class="hub-right">
      <div class="card" style="padding:16px; border-radius: calc(var(--radius) + 10px);">
        <div>
          <div style="font-weight:950;">Status</div>
          <div style="color: var(--muted); font-size:13px; margin-top:4px;">
            <?= (int)$profileCompletion ?>%
          </div>
        </div>

        <div style="margin-top:12px; display:grid; gap:10px;">
          <div class="card-soft" style="padding:12px;">
            <div style="display:flex; justify-content:space-between; gap:12px;"><span>Approval</span><span><?= $approved ? '✅' : '⏳' ?></span></div>
          </div>
          <div class="card-soft" style="padding:12px;">
            <div style="display:flex; justify-content:space-between; gap:12px;"><span>Email</span><span><?= $emailVerified ? '✅' : '❌' ?></span></div>
          </div>
          <div class="card-soft" style="padding:12px;">
            <div style="display:flex; justify-content:space-between; gap:12px;"><span>2FA</span><span><?= $twofaEnabled ? '✅' : '❌' ?></span></div>
          </div>
          <div class="card-soft" style="padding:12px;">
            <div style="display:flex; justify-content:space-between; gap:12px;"><span>Avatar</span><span><?= !empty($avatar) ? '✅' : '❌' ?></span></div>
          </div>
          <div class="card-soft" style="padding:12px;">
            <div style="display:flex; justify-content:space-between; gap:12px;"><span>Bio</span><span><?= $bio !== '' ? '✅' : '❌' ?></span></div>
          </div>
        </div>
      </div>
    </aside>

  </div>
</section>

<?php ui_footer(); ?>