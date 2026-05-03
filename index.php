<?php
session_start();

require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";

$bp = base_path();

// If already logged in → go to dashboard
if (is_logged_in()) {
  header("Location: {$bp}/dashboard.php");
  exit;
}

$err = flash_get('err');
$msg = flash_get('msg');

// After auth, send to choose.php
$next = $bp . "/choose.php";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Logia</title>

  <link rel="icon" type="image/x-icon" href="<?= h($bp) ?>/assets/brand/favicon.ico"/>
  <link rel="shortcut icon" type="image/x-icon" href="<?= h($bp) ?>/assets/brand/favicon.ico"/>
  <link rel="apple-touch-icon" href="<?= h($bp) ?>/assets/brand/logo.png"/>

  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
</head>
<body>

  <!-- Flash banners -->
  <div class="container">
    <?php if ($err): ?>
      <div class="banner banner--bad"><?= h($err) ?></div>
    <?php elseif ($msg): ?>
      <div class="banner banner--good"><?= h($msg) ?></div>
    <?php endif; ?>
  </div>

  <!-- Hero -->
  <main class="container">
    <section class="hero">
      <div class="hero__copy">
        <div class="chip">No. 1 on this specific localhost port!</div>
        <h1>Master the elements. Outplay the table. Become Logia.</h1>
        <p class="lead">
          Logia is a competitive online card game built around elemental matchups, room-based play,
          and player choice. Jump in as a guest, create an account, or step into ranked once your
          account is fully ready.
        </p>

        <div class="cta">
          <button class="btn btn-primary btn-lg" type="button" data-open-auth="register">Create Account</button>
          <button class="btn btn-ghost btn-lg" type="button" data-open-auth="login">Login</button>
          <a class="btn btn-ghost btn-lg" href="<?= h($bp) ?>/auth_action.php?action=guest&next=<?= urlencode($bp . '/guest_dashboard.php') ?>">Play as Guest</a>
        </div>
      </div>

            <div class="hero__logo-panel" aria-hidden="true">
        <img
          src="<?= h($bp) ?>/assets/brand/logo.png"
          alt="Logia"
          class="landing-hero-logo landing-hero-logo--panel"
        >
      </div>
    </section>

    <!-- Features -->
    <section class="section" id="features">
      <div class="section__head">
        <h2>What you get</h2>
      </div>

      <div class="grid4">
        <article class="fcard">
          <div class="ficon">⚔️</div>
          <h3>Element-based play</h3>
          <p>
            Every match revolves around elemental interactions, timing, and reading your opponent’s next move.
          </p>
        </article>

        <article class="fcard">
          <div class="ficon">🎮</div>
          <h3>Guest or registered access</h3>
          <p>
            Jump in immediately as a guest or create an account to unlock a more complete play experience.
          </p>
        </article>

        <article class="fcard">
          <div class="ficon">🚪</div>
          <h3>Room-based multiplayer</h3>
          <p>
            Create a room, share the code, and run matches with friends or join with a room ID and password.
          </p>
        </article>

        <article class="fcard">
          <div class="ficon">🏆</div>
          <h3>Ranked-ready structure</h3>
          <p>
            Casual is open and easy to enter, while ranked is built for verified, more serious competition.
          </p>
        </article>
      </div>
    </section>

    <!-- How it works -->
    <section class="section" id="how">
      <div class="section__head">
        <h2>How it works</h2>
      </div>

      <div class="steps">
        <div class="step">
          <div class="step__num">1</div>
          <div class="step__body">
            <h3>Enter Logia</h3>
            <p>Create an account, log in, or continue as a guest depending on how you want to play.</p>
          </div>
        </div>

        <div class="step">
          <div class="step__num">2</div>
          <div class="step__body">
            <h3>Choose your mode</h3>
            <p>Play casually, join a room, or prepare for ranked once your account meets the requirements.</p>
          </div>
        </div>

        <div class="step">
          <div class="step__num">3</div>
          <div class="step__body">
            <h3>Win the match</h3>
            <p>Use elemental advantage, room strategy, and match awareness to take control and become Logia.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="section" id="faq">
      <div class="section__head">
        <h2>FAQ</h2>
      </div>

      <div class="faq">
        <button class="faq__q" type="button" data-acc>
          <span>Can I play without creating an account?</span><span class="chev">▾</span>
        </button>
        <div class="faq__a">
          Yes. You can enter as a guest and go straight to the guest dashboard to join or start supported matches.
        </div>

        <button class="faq__q" type="button" data-acc>
          <span>What is the difference between casual and ranked?</span><span class="chev">▾</span>
        </button>
        <div class="faq__a">
          Casual is the easier way to jump into the game. Ranked is intended for players with fully prepared accounts and stricter access requirements.
        </div>

        <button class="faq__q" type="button" data-acc>
          <span>Do I need verification for ranked play?</span><span class="chev">▾</span>
        </button>
        <div class="faq__a">
          Yes. Ranked access may require account approval, email verification, and additional security steps before entry.
        </div>

        <button class="faq__q" type="button" data-acc>
          <span>Can I make private rooms for friends?</span><span class="chev">▾</span>
        </button>
        <div class="faq__a">
          Yes. Room-based play lets you create matches that can be shared directly with invited players.
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="sitefooter">
    <div class="container sitefooter__inner">
      <div class="footleft">
        <div class="footbrand">Logia</div>
        <div class="footmuted">© <?= date('Y') ?> • Elemental card battles, room play, and competitive match flow.</div>
      </div>
      <div class="footright">
        <a href="#top">Back to top</a>
        <span class="sep">•</span>
        <a href="<?= h($bp) ?>/admin/login.php">Admin Login</a>
      </div>
    </div>
  </footer>

  <!-- AUTH MODAL -->
  <div class="modal" id="authModal" aria-hidden="true">
    <div class="modal__backdrop" data-close-auth></div>

    <div class="modal__panel" role="dialog" aria-modal="true" aria-label="Authentication">
      <div class="modal__top">
        <div class="modal__title">
          <div class="modal__brand">
            <span class="logo__mark sm">LG</span>
            <span>Welcome to Logia</span>
          </div>
          <div class="modal__sub">Log in, register, or continue as a guest to start playing.</div>
        </div>
        <button class="iconx" type="button" aria-label="Close" data-close-auth>✕</button>
      </div>

      <div class="tabs" style="margin-bottom:10px;">
        <button class="tab is-active" type="button" data-tab="login">Login</button>
        <button class="tab" type="button" data-tab="register">Register</button>
      </div>

      <div class="navactions">
        <a class="btn btn-ghost" href="<?= h($bp) ?>/auth_action.php?action=guest&next=<?= urlencode($bp . '/guest_dashboard.php') ?>">Play as Guest</a>
        <button class="btn btn-ghost" type="button" data-open-auth="login">Login</button>
        <button class="btn btn-primary" type="button" data-open-auth="register">Register</button>
      </div>

      <!-- LOGIN TAB -->
      <section class="tabpane is-active" data-pane="login">
        <form method="post" action="<?= h($bp) ?>/auth_action.php?next=<?= urlencode($next) ?>" autocomplete="off">
          <input type="hidden" name="action" value="login"/>

          <label for="login_ident">Email or Username</label>
          <input id="login_ident" name="identifier" autocomplete="username" required />

          <label for="login_pw">Password</label>
          <input id="login_pw" name="password" type="password" autocomplete="current-password" required />

          <div style="margin-top:8px; margin-bottom:12px;">
            <a
              href="<?= h($bp) ?>/forgot-password.php"
              style="font-size:13px; color:rgba(238,243,255,.78); text-decoration:none;"
            >
              Forgot password?
            </a>
          </div>

          <div class="formrow">
            <button class="btn btn-primary" type="submit">Login</button>
            <a class="btn btn-ghost" href="<?= h($bp) ?>/auth_action.php?action=guest&next=<?= urlencode($bp . '/guest_dashboard.php') ?>">Play as Guest</a>
          </div>

          <div class="tiny" style="margin-top:10px; color: rgba(238,243,255,.72);">
            Ranked access may require account approval, email verification, and extra security setup.
          </div>
        </form>
      </section>

      <!-- REGISTER TAB -->
      <section class="tabpane" data-pane="register">
        <form method="post" action="<?= h($bp) ?>/auth_action.php?next=<?= urlencode($next) ?>" id="regForm" autocomplete="off">
          <input type="hidden" name="action" value="register"/>

          <label for="reg_user">Username</label>
          <input id="reg_user" name="username" minlength="3" maxlength="32" autocomplete="username" required />

          <label for="reg_email">Email</label>
          <input id="reg_email" name="email" type="email" autocomplete="email" required />

          <div class="twocol">
            <div>
              <label for="reg_password">Password</label>
              <input id="reg_password" name="password" type="password" autocomplete="new-password" required />

              <div class="pw-meter" aria-hidden="true"><div id="pwBar"></div></div>

              <ul class="pw-req" id="pwReq">
                <li class="bad" data-req="len">At least 16 characters</li>
                <li class="bad" data-req="low">Lowercase letter</li>
                <li class="bad" data-req="up">Uppercase letter</li>
                <li class="bad" data-req="num">Number</li>
                <li class="bad" data-req="sym">Special character</li>
              </ul>
            </div>

            <div>
              <label for="reg_password2">Confirm Password</label>
              <input id="reg_password2" name="password2" type="password" autocomplete="new-password" required />
              <small class="hint" id="pwMatch"></small>

              <div class="tiny muted" style="margin-top:10px;">
                Some accounts may need approval before full access is granted.
              </div>
            </div>
          </div>

          <div class="formrow">
            <button class="btn btn-primary" id="regBtn" type="submit" disabled>Register</button>
          </div>
        </form>
      </section>
    </div>
  </div>

  <script src="<?= h($bp) ?>/assets/main.js"></script>
</body>
</html>