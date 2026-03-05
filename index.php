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
  <title>insertName — Home</title>
  <link rel="stylesheet" href="<?= h($bp) ?>/assets/style.css"/>
</head>
<body>

  <!-- Top Nav -->
  <header class="topnav" id="top">
    <div class="topnav__inner">
      <a class="logo" href="<?= h($bp) ?>/index.php#top" aria-label="insertName Home">
        <span class="logo__mark">CG</span>
        <span class="logo__text">insertName</span>
      </a>


      <div class="navactions">
        <button class="btn btn-ghost" type="button" data-open-auth="login">Login</button>
        <button class="btn btn-primary" type="button" data-open-auth="register">Register</button>
      </div>
    </div>
  </header>

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
        <div class="chip">No. 1 Card Game on this specific localhost port!</div>
        <h1>Build rooms. Choose your mode. Play your way.</h1>
        <p class="lead">
          Believe in the heart of the cards. Bet low win big!
        </p>

        <div class="cta">
          <button class="btn btn-primary btn-lg" type="button" data-open-auth="register">Get Started</button>
          <button class="btn btn-ghost btn-lg" type="button" data-open-auth="login">I already have an account</button>
        </div>
      </div>

      <div class="hero__panel" aria-hidden="true">
        <div class="statpanel">
          <div class="statpanel__top">
            <span class="dot d1"></span><span class="dot d2"></span><span class="dot d3"></span>
            <span class="statpanel__title">CardGame • Platform Analytics</span>
            <span class="statpanel__pill">Live</span>
          </div>

          <div class="statgrid">
            <div class="stat">
              <div class="stat__label">Rooms Created</div>
              <div class="stat__value">1,284</div>
              <div class="stat__delta up">+12% this week</div>
            </div>

            <div class="stat">
              <div class="stat__label">Matches Logged</div>
              <div class="stat__value">8,902</div>
              <div class="stat__delta up">+7% this week</div>
            </div>

            <div class="stat">
              <div class="stat__label">Avg. Session</div>
              <div class="stat__value">18m</div>
              <div class="stat__delta">stable</div>
            </div>

            <div class="stat">
              <div class="stat__label">Queue Health</div>
              <div class="stat__value">Good</div>
              <div class="stat__delta good">low wait</div>
            </div>
          </div>

          <div class="spark">
            <div class="spark__head">
              <span class="spark__title">Activity (7 days)</span>
            </div>
            <div class="spark__bars" aria-hidden="true">
              <span style="--h:38%"></span>
              <span style="--h:62%"></span>
              <span style="--h:48%"></span>
              <span style="--h:74%"></span>
              <span style="--h:58%"></span>
              <span style="--h:82%"></span>
              <span style="--h:66%"></span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="section" id="features">
      <div class="section__head">
        <h2>What you get</h2>
      </div>

      <div class="grid4">
        <article class="fcard">
          <div class="ficon">🧩</div>
          <h3>Insert</h3>
          <p>Lorem ipsum</p>
        </article>
        <article class="fcard">
          <div class="ficon">🛡️</div>
          <h3>Insert</h3>
          <p>Lorem ipsum</p>
        </article>
        <article class="fcard">
          <div class="ficon">🧭</div>
          <h3>Insert</h3>
          <p>Lorem ipsum</p>
        </article>
        <article class="fcard">
          <div class="ficon">📦</div>
          <h3>Insert</h3>
          <p>Lorem ipsum</p>
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
            <h3>Enter the game!</h3>
            <p>Create an account, login, or play as guest!</p>
          </div>
        </div>
        <div class="step">
          <div class="step__num">2</div>
          <div class="step__body">
            <h3>Choose your experience</h3>
            <p>Casual or Ranked — pick your poison.</p>
          </div>
        </div>
        <div class="step">
          <div class="step__num">3</div>
          <div class="step__body">
            <h3>Play Responsibly!</h3>
            <p>Ranked matches require age verification due to monetary exchanges.</p>
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
          <span>Insert</span><span class="chev">▾</span>
        </button>
        <div class="faq__a">
          lorem ipsum
        </div>

        <button class="faq__q" type="button" data-acc>
          <span>Insert</span><span class="chev">▾</span>
        </button>
        <div class="faq__a">
          Lorem ipsum
        </div>

        <button class="faq__q" type="button" data-acc>
          <span>Insert</span><span class="chev">▾</span>
        </button>
        <div class="faq__a">
          Lorem ipsum
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <footer class="sitefooter">
    <div class="container sitefooter__inner">
      <div class="footleft">
        <div class="footbrand">insertName</div>
        <div class="footmuted">© <?= date('Y') ?> • Platform shell</div>
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