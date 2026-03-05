<?php
// index.php
session_start();
require_once __DIR__ . "/includes/helpers.php";

$bp  = base_path();
$err = flash_get('err');
$msg = flash_get('msg');

// After auth, send to choose.php (no DB changes).
$next = $bp . "/choose.php";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>CardGame — Home</title>
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
        <div class="chip">MVP Platform • Auth + Dashboard • Admin Approval</div>
        <h1>Build rooms. Choose your mode. Play your way.</h1>
        <p class="lead">
          A polished card-game platform shell: account security, role-based access, approvals,
          and a clean pre-game flow — with the actual game module coming soon.
        </p>

        <div class="cta">
          <button class="btn btn-primary btn-lg" type="button" data-open-auth="register">Get Started</button>
          <button class="btn btn-ghost btn-lg" type="button" data-open-auth="login">I already have an account</button>
        </div>

        <div class="hero__notes">
          <span class="note">✔ Separate Admin login</span>
          <span class="note">✔ Approval workflow</span>
          <span class="note">✔ Modal auth UI</span>
        </div>
      </div>

      <div class="hero__panel" aria-hidden="true">
        <div class="mock">
          <div class="mock__top">
            <span class="dot d1"></span><span class="dot d2"></span><span class="dot d3"></span>
            <span class="mock__title">CardGame • Lobby Preview</span>
          </div>
          <div class="mock__body">
            <div class="mock__card">
              <div class="k">Mode</div><div class="v">Casual / Ranked</div>
            </div>
            <div class="mock__card">
              <div class="k">Rooms</div><div class="v">Create • Join • Invite</div>
            </div>
            <div class="mock__card">
              <div class="k">Security</div><div class="v">RBAC • Approval • 2FA-ready</div>
            </div>
            <div class="mock__bar"></div>
            <div class="mock__line"></div>
            <div class="mock__line short"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="section" id="features">
      <div class="section__head">
        <h2>What you get</h2>
        <p>Everything before the actual gameplay — done cleanly and realistically.</p>
      </div>

      <div class="grid4">
        <article class="fcard">
          <div class="ficon">🧩</div>
          <h3>Platform-first</h3>
          <p>Landing, auth, dashboards, and flows that look like a real product.</p>
        </article>
        <article class="fcard">
          <div class="ficon">🛡️</div>
          <h3>Security-ready</h3>
          <p>Approval gating, role checks, and room for verification + 2FA later.</p>
        </article>
        <article class="fcard">
          <div class="ficon">🧭</div>
          <h3>Mode selection</h3>
          <p>Users pick what they want to see after login: Casual vs Ranked.</p>
        </article>
        <article class="fcard">
          <div class="ficon">📦</div>
          <h3>Expandable</h3>
          <p>Game page can be plugged in later without rewriting the whole app.</p>
        </article>
      </div>
    </section>

    <!-- How it works -->
    <section class="section" id="how">
      <div class="section__head">
        <h2>How it works</h2>
        <p>Simple flow. Clean UX. No clutter.</p>
      </div>

      <div class="steps">
        <div class="step">
          <div class="step__num">1</div>
          <div class="step__body">
            <h3>Login or Register</h3>
            <p>Use the modal to sign in or create an account. Admin approval applies if required.</p>
          </div>
        </div>
        <div class="step">
          <div class="step__num">2</div>
          <div class="step__body">
            <h3>Choose your experience</h3>
            <p>After auth, you pick your path: Casual or Ranked — what you want to see.</p>
          </div>
        </div>
        <div class="step">
          <div class="step__num">3</div>
          <div class="step__body">
            <h3>Use the platform</h3>
            <p>Rooms, lobby, dashboard — the full shell. Gameplay screen comes later.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="section" id="faq">
      <div class="section__head">
        <h2>FAQ</h2>
        <p>Quick answers — looks complete, stays honest.</p>
      </div>

      <div class="faq">
        <button class="faq__q" type="button" data-acc>
          <span>Is the game included?</span><span class="chev">▾</span>
        </button>
        <div class="faq__a">
          The project delivers the full platform shell (auth, dashboard, rooms/lobby UX). The game screen is a stub for now.
        </div>

        <button class="faq__q" type="button" data-acc>
          <span>Why do some accounts need approval?</span><span class="chev">▾</span>
        </button>
        <div class="faq__a">
          Approval simulates a moderated platform. It also demonstrates admin controls and RBAC.
        </div>

        <button class="faq__q" type="button" data-acc>
          <span>Where is Admin login?</span><span class="chev">▾</span>
        </button>
        <div class="faq__a">
          Admin sign-in is intentionally separate: it lives under the admin area and never mixes into user auth.
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="sitefooter">
    <div class="container sitefooter__inner">
      <div class="footleft">
        <div class="footbrand">CardGame</div>
        <div class="footmuted">© <?= date('Y') ?> • Platform shell (no gameplay module yet)</div>
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
            <span class="logo__mark sm">CG</span>
            <span>Welcome</span>
          </div>
          <div class="modal__sub">Login or create an account to continue.</div>
        </div>
        <button class="iconx" type="button" aria-label="Close" data-close-auth>✕</button>
      </div>

      <div class="tabs" role="tablist" aria-label="Auth tabs">
        <button class="tab is-active" type="button" role="tab" data-tab="login">Login</button>
        <button class="tab" type="button" role="tab" data-tab="register">Register</button>
      </div>

      <!-- LOGIN TAB -->
      <section class="tabpane is-active" data-pane="login">
        <form method="post" action="<?= h($bp) ?>/auth_action.php?next=<?= urlencode($next) ?>" autocomplete="off">
          <input type="hidden" name="action" value="login"/>

          <label for="login_ident">Email or Username</label>
          <input id="login_ident" name="identifier" autocomplete="username" required />

          <label for="login_pw">Password</label>
          <input id="login_pw" name="password" type="password" autocomplete="current-password" required />

          <div class="formrow">
            <button class="btn btn-primary" type="submit">Login</button>
            <a class="btn btn-ghost" href="<?= h($bp) ?>/auth_action.php?action=guest&next=<?= urlencode($next) ?>">Play as Guest</a>
          </div>

          <div class="tiny muted">
            Ranked later can require: <span class="pill">Email Verified</span> <span class="pill">2FA</span>
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
              <label for="reg_password2">Confirm</label>
              <input id="reg_password2" name="password2" type="password" autocomplete="new-password" required />
              <small class="hint" id="pwMatch"></small>

              <div class="tiny muted" style="margin-top:10px;">
                Admin approval may be required before you can sign in.
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