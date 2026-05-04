<?php
session_start();

require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();

$u  = current_user();
$bp = base_path();

$is_guest = ((int)($u['is_guest'] ?? 0) === 1);

if (!$is_guest) {
  header("Location: {$bp}/dashboard.php");
  exit;
}

$flashError = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

ui_header("Guest Dashboard", true);
?>

<section class="section section--flush-top guest-home">
  <div class="guest-shell">

    <?php if ($flashError): ?>
      <div class="card-soft profile-alert profile-alert--bad guest-alert">
        <?= h($flashError) ?>
      </div>
    <?php endif; ?>

    <div class="card hub-hero guest-hero">
      <div class="hero-top hero-top--start">
        <div>
          <span class="pill status-pill--warn">Guest Access</span>
        </div>

        <div class="guest-hero__actions">
          <a class="btn btn-primary" href="<?= h($bp) ?>/guest_exit.php?to=register">
            Create Account
          </a>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/guest_exit.php?to=login">
            Sign In
          </a>
          <a class="btn btn-ghost" href="<?= h($bp) ?>/logout.php">
            Leave Guest
          </a>
        </div>
      </div>

      <div class="hero-body guest-hero__body">
        <h2>Play as Guest</h2>
        <p class="lead hero-lead">
          Guest mode lets you try Logia through casual play and private room entry.
          Ranked, shop, profile, notifications, friends, and messages require a registered account.
        </p>

        <div class="hero-meta">
          <span class="pill">Casual Play</span>
          <span class="pill">Private Room Entry</span>
          <span class="pill status-pill--bad">Social Locked</span>
          <span class="pill status-pill--bad">Ranked Locked</span>
        </div>
      </div>
    </div>

    <div class="guest-grid">

      <div class="card guest-action-card guest-action-card--primary">
        <div class="guest-action-card__icon">⚡</div>
        <div class="guest-action-card__body">
          <div class="guest-action-card__eyebrow">Quick Start</div>
          <h3>Random Match</h3>
          <p>
            Enter a casual match quickly. Best for testing the game without creating an account.
          </p>
        </div>

        <a class="btn btn-primary btn-lg" href="<?= h($bp) ?>/guest_quick_match.php">
          Find Match
        </a>
      </div>

      <div class="card guest-action-card">
        <div class="guest-action-card__icon">🚪</div>
        <div class="guest-action-card__body">
          <div class="guest-action-card__eyebrow">Private Entry</div>
          <h3>Join Room</h3>
          <p>
            Join a private room using a room code from another player.
          </p>
        </div>

        <form method="get" action="<?= h($bp) ?>/room.php" class="guest-room-form">
          <input class="input" type="text" name="room_code" placeholder="Room Code" required>
          <input class="input" type="text" name="room_password" placeholder="Password, if needed">
          <button class="btn btn-primary btn-lg" type="submit">
            Join Room
          </button>
        </form>
      </div>

      <div class="card guest-action-card guest-action-card--register">
        <div class="guest-action-card__icon">🛡️</div>
        <div class="guest-action-card__body">
          <div class="guest-action-card__eyebrow">Full Access</div>
          <h3>Create an Account</h3>
          <p>
            Register to unlock profile progress, Zeny, shop, notifications, friends, messages, and ranked access.
          </p>
        </div>

        <div class="guest-register-actions">
          <a class="btn btn-primary btn-lg" href="<?= h($bp) ?>/guest_exit.php?to=register">
            Create Account
          </a>
          <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/guest_exit.php?to=login">
            Sign In
          </a>
        </div>
      </div>

    </div>

  </div>
</section>

<?php ui_footer(); ?>