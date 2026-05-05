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
          <button class="btn btn-primary" type="button" data-guest-auth-open="register">
            Create Account
          </button>
          <button class="btn btn-ghost" type="button" data-guest-auth-open="login">
            Sign In
          </button>
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
          <button class="btn btn-primary btn-lg" type="button" data-guest-auth-open="register">
            Create Account
          </button>
          <button class="btn btn-ghost btn-lg" type="button" data-guest-auth-open="login">
            Sign In
          </button>
        </div>
      </div>

    </div>

  </div>
</section>

<div class="guest-auth-modal" id="guestAuthModal" aria-hidden="true">
  <div class="guest-auth-modal__backdrop" data-guest-auth-close></div>

  <div class="guest-auth-modal__panel" role="dialog" aria-modal="true" aria-label="Guest account access">
    <div class="guest-auth-modal__top">
      <div>
        <div class="guest-auth-modal__eyebrow">Guest Upgrade</div>
        <h2 id="guestAuthTitle">Create Account</h2>
        <p id="guestAuthSub">
          Create an account to unlock profile progress, shop, friends, messages, notifications, and ranked access.
        </p>
      </div>

      <button class="guest-auth-modal__close" type="button" data-guest-auth-close aria-label="Close">✕</button>
    </div>

    <div class="guest-auth-tabs">
      <button class="guest-auth-tab is-active" type="button" data-guest-auth-tab="register">Register</button>
      <button class="guest-auth-tab" type="button" data-guest-auth-tab="login">Login</button>
    </div>

    <section class="guest-auth-pane is-active" data-guest-auth-pane="register">
      <form method="post" action="<?= h($bp) ?>/guest_exit.php?to=register" autocomplete="off" id="guestRegisterForm">
        <div class="guest-auth-grid">
          <div>
            <label for="guest_reg_user">Username</label>
            <input class="input" id="guest_reg_user" name="username" minlength="3" maxlength="32" autocomplete="username" required />
          </div>

          <div>
            <label for="guest_reg_email">Email</label>
            <input class="input" id="guest_reg_email" name="email" type="email" autocomplete="email" required />
          </div>
        </div>

        <div class="guest-auth-grid">
          <div>
            <label for="guest_reg_password">Password</label>
            <div class="password-wrap">
              <input class="input" id="guest_reg_password" name="password" type="password" autocomplete="new-password" required />
              <button class="password-toggle" type="button" data-guest-toggle-password="#guest_reg_password">Show</button>
            </div>

            <ul class="guest-pw-req" id="guestPwReq">
              <li class="bad" data-req="len">At least 16 characters</li>
              <li class="bad" data-req="low">Lowercase letter</li>
              <li class="bad" data-req="up">Uppercase letter</li>
              <li class="bad" data-req="num">Number</li>
              <li class="bad" data-req="sym">Special character</li>
            </ul>
          </div>

          <div>
            <label for="guest_reg_password2">Confirm Password</label>
            <div class="password-wrap">
              <input class="input" id="guest_reg_password2" name="password2" type="password" autocomplete="new-password" required />
              <button class="password-toggle" type="button" data-guest-toggle-password="#guest_reg_password2">Show</button>
            </div>
            <small class="guest-auth-hint" id="guestPwMatch"></small>
          </div>
        </div>

        <div class="guest-auth-actions">
          <button class="btn btn-primary" type="submit" id="guestRegBtn" disabled>Create Account</button>
          <button class="btn btn-ghost" type="button" data-guest-auth-tab="login">I already have an account</button>
        </div>

        <p class="guest-auth-note">
          Guest mode will close before registration so the new account can log in cleanly.
        </p>
      </form>
    </section>

    <section class="guest-auth-pane" data-guest-auth-pane="login">
      <form method="post" action="<?= h($bp) ?>/guest_exit.php?to=login" autocomplete="off">
        <label for="guest_login_ident">Email or Username</label>
        <input class="input" id="guest_login_ident" name="identifier" autocomplete="username" required />

        <label for="guest_login_pw">Password</label>
        <div class="password-wrap">
          <input class="input" id="guest_login_pw" name="password" type="password" autocomplete="current-password" required />
          <button class="password-toggle" type="button" data-guest-toggle-password="#guest_login_pw">Show</button>
        </div>

        <div class="guest-auth-linkrow">
          <a href="<?= h($bp) ?>/forgot-password.php">Forgot password?</a>
        </div>

        <div class="guest-auth-actions">
          <button class="btn btn-primary" type="submit">Sign In</button>
          <button class="btn btn-ghost" type="button" data-guest-auth-tab="register">Create an account</button>
        </div>

        <p class="guest-auth-note">
          Guest mode will close before login so the player session does not conflict.
        </p>
      </form>
    </section>
  </div>
</div>

<style>
  .guest-auth-modal{
    position:fixed;
    inset:0;
    z-index:10000;
    display:none;
    align-items:center;
    justify-content:center;
    padding:18px;
  }

  .guest-auth-modal.is-open{
    display:flex;
  }

  .guest-auth-modal__backdrop{
    position:absolute;
    inset:0;
    background:rgba(2,6,18,.68);
    backdrop-filter:blur(8px);
  }

  .guest-auth-modal__panel{
    position:relative;
    z-index:1;
    width:min(860px, 100%);
    max-height:calc(100vh - 36px);
    overflow:auto;
    border-radius:28px;
    border:1px solid rgba(255,255,255,.14);
    background:
      radial-gradient(circle at 12% 0%, rgba(57,255,106,.11), transparent 34%),
      radial-gradient(circle at 90% 0%, rgba(139,92,255,.16), transparent 32%),
      rgba(12,16,34,.98);
    box-shadow:0 30px 90px rgba(0,0,0,.52);
    color:var(--text);
    padding:20px;
  }

  .guest-auth-modal__top{
    display:flex;
    justify-content:space-between;
    gap:16px;
    align-items:flex-start;
    padding-bottom:14px;
    border-bottom:1px solid var(--border);
  }

  .guest-auth-modal__eyebrow{
    color:var(--muted);
    font-size:11px;
    font-weight:950;
    letter-spacing:.10em;
    text-transform:uppercase;
    margin-bottom:7px;
  }

  .guest-auth-modal__top h2{
    margin:0;
    font-size:32px;
    line-height:1;
    font-weight:950;
  }

  .guest-auth-modal__top p{
    margin:8px 0 0;
    color:var(--muted);
    line-height:1.45;
    max-width:62ch;
  }

  .guest-auth-modal__close{
    width:40px;
    height:40px;
    border-radius:14px;
    border:1px solid var(--border);
    background:rgba(255,255,255,.06);
    color:var(--text);
    cursor:pointer;
    font-size:22px;
    font-weight:950;
  }

  .guest-auth-tabs{
    margin:14px 0;
    display:flex;
    gap:8px;
    flex-wrap:wrap;
  }

  .guest-auth-tab{
    min-height:42px;
    padding:0 16px;
    border-radius:14px;
    border:1px solid var(--border);
    background:rgba(255,255,255,.05);
    color:var(--text);
    cursor:pointer;
    font-weight:950;
  }

  .guest-auth-tab.is-active{
    border-color:rgba(57,255,106,.36);
    background:rgba(57,255,106,.14);
  }

  .guest-auth-pane{
    display:none;
  }

  .guest-auth-pane.is-active{
    display:block;
  }

  .guest-auth-pane form{
    display:grid;
    gap:14px;
  }

  .guest-auth-grid{
    display:grid;
    grid-template-columns:repeat(2, minmax(0, 1fr));
    gap:14px;
  }

  .guest-auth-pane label{
    display:block;
    color:var(--muted);
    font-size:12px;
    margin-bottom:8px;
    font-weight:800;
  }

  .guest-auth-pane input{
    width:100%;
    min-height:46px;
  }

  .guest-auth-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
  }

  .guest-auth-actions .btn{
    min-width:150px;
  }

  .guest-auth-note,
  .guest-auth-hint{
    color:var(--muted);
    font-size:12px;
    line-height:1.4;
  }

  .guest-auth-linkrow{
    margin-top:-4px;
  }

  .guest-auth-linkrow a{
    color:rgba(238,243,255,.78);
    font-size:13px;
    text-decoration:none;
  }

  .guest-pw-req{
    margin:10px 0 0;
    padding-left:18px;
    color:var(--muted);
    font-size:12px;
    line-height:1.55;
  }

  .guest-pw-req li.good{
    color:rgba(57,255,106,.92);
  }

  .guest-pw-req li.bad{
    color:rgba(255,116,140,.92);
  }

  @media (max-width:720px){
    .guest-auth-modal{
      padding:8px;
    }

    .guest-auth-modal__panel{
      padding:14px;
      border-radius:20px;
    }

    .guest-auth-modal__top{
      padding-right:0;
    }

    .guest-auth-modal__top h2{
      font-size:26px;
    }

    .guest-auth-grid{
      grid-template-columns:1fr;
    }

    .guest-auth-actions{
      width:100%;
    }

    .guest-auth-actions .btn{
      flex:1 1 150px;
    }
  }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("guestAuthModal");
  const title = document.getElementById("guestAuthTitle");
  const sub = document.getElementById("guestAuthSub");

  function setGuestAuthMode(mode) {
    const nextMode = mode === "login" ? "login" : "register";

    document.querySelectorAll("[data-guest-auth-tab]").forEach((btn) => {
      btn.classList.toggle("is-active", btn.dataset.guestAuthTab === nextMode);
    });

    document.querySelectorAll("[data-guest-auth-pane]").forEach((pane) => {
      pane.classList.toggle("is-active", pane.dataset.guestAuthPane === nextMode);
    });

    if (title) {
      title.textContent = nextMode === "login" ? "Sign In" : "Create Account";
    }

    if (sub) {
      sub.textContent = nextMode === "login"
        ? "Sign in to continue with your registered account and unlock your saved progress."
        : "Create an account to unlock profile progress, shop, friends, messages, notifications, and ranked access.";
    }
  }

  function openGuestAuth(mode) {
    setGuestAuthMode(mode);
    modal?.classList.add("is-open");
    modal?.setAttribute("aria-hidden", "false");

    const firstInput = mode === "login"
      ? document.getElementById("guest_login_ident")
      : document.getElementById("guest_reg_user");

    setTimeout(() => firstInput?.focus(), 60);
  }

  function closeGuestAuth() {
    modal?.classList.remove("is-open");
    modal?.setAttribute("aria-hidden", "true");
  }

  document.querySelectorAll("[data-guest-auth-open]").forEach((btn) => {
    btn.addEventListener("click", () => openGuestAuth(btn.dataset.guestAuthOpen || "register"));
  });

  document.querySelectorAll("[data-guest-auth-tab]").forEach((btn) => {
    btn.addEventListener("click", () => setGuestAuthMode(btn.dataset.guestAuthTab || "register"));
  });

  document.querySelectorAll("[data-guest-auth-close]").forEach((btn) => {
    btn.addEventListener("click", closeGuestAuth);
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modal?.classList.contains("is-open")) {
      closeGuestAuth();
    }
  });

  document.querySelectorAll("[data-guest-toggle-password]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = document.querySelector(btn.dataset.guestTogglePassword || "");
      if (!input) return;

      input.type = input.type === "password" ? "text" : "password";
      btn.textContent = input.type === "password" ? "Show" : "Hide";
    });
  });

  const pw = document.getElementById("guest_reg_password");
  const pw2 = document.getElementById("guest_reg_password2");
  const regBtn = document.getElementById("guestRegBtn");
  const match = document.getElementById("guestPwMatch");

  function checkPassword() {
    const value = pw?.value || "";
    const confirm = pw2?.value || "";

    const tests = {
      len: value.length >= 16,
      low: /[a-z]/.test(value),
      up: /[A-Z]/.test(value),
      num: /\d/.test(value),
      sym: /[^A-Za-z0-9]/.test(value),
    };

    Object.entries(tests).forEach(([key, ok]) => {
      const li = document.querySelector(`#guestPwReq [data-req="${key}"]`);
      if (!li) return;
      li.classList.toggle("good", ok);
      li.classList.toggle("bad", !ok);
    });

    const allGood = Object.values(tests).every(Boolean);
    const same = value !== "" && value === confirm;

    if (match) {
      match.textContent = confirm === ""
        ? ""
        : (same ? "Passwords match." : "Passwords do not match.");
      match.style.color = same ? "rgba(57,255,106,.92)" : "rgba(255,116,140,.92)";
    }

    if (regBtn) {
      regBtn.disabled = !(allGood && same);
    }
  }

  pw?.addEventListener("input", checkPassword);
  pw2?.addEventListener("input", checkPassword);
});
</script>

<?php ui_footer(); ?>