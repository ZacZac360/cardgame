<?php
// shop.php
session_start();

require_once __DIR__ . "/includes/helpers.php";
require_once __DIR__ . "/includes/auth.php";
require_once __DIR__ . "/includes/ui.php";

require_login();

$bp = base_path();
$u  = current_user();

$is_guest = ((int)($u['is_guest'] ?? 0) === 1);
if ($is_guest) {
  header("Location: {$bp}/guest_dashboard.php");
  exit;
}

$username = $u['username'] ?? $u['display_name'] ?? 'Player';

$currentCredits = (int)($u['credits'] ?? 0);
$topupMsg = flash_get('msg');
$topupErr = flash_get('err');
if (isset($_GET['cancel'])) {
  $topupErr = 'Top-up was cancelled.';
}


$creditPacks = [
  [
    'code'          => 'starter_50',
    'name'          => 'Starter Cache',
    'price_php'     => 50,
    'credits'       => 250,
    'bonus_credits' => 0,
    'tag'           => 'Entry',
  ],
  [
    'code'          => 'duel_100',
    'name'          => 'Duel Stack',
    'price_php'     => 100,
    'credits'       => 500,
    'bonus_credits' => 50,
    'tag'           => 'Popular',
  ],
  [
    'code'          => 'arena_200',
    'name'          => 'Arena Vault',
    'price_php'     => 200,
    'credits'       => 1000,
    'bonus_credits' => 150,
    'tag'           => 'Best Value',
  ],
];

$featuredItems = [
  ['name' => 'Nebula Frame',  'type' => 'Profile Border', 'price' => '900'],
  ['name' => 'Static Burst',  'type' => 'Card Back',      'price' => '700'],
  ['name' => 'Circuit Echo',  'type' => 'Table Skin',     'price' => '1400'],
  ['name' => 'Verdant Glow',  'type' => 'Avatar Accent',  'price' => '450'],
];

$dailyItems = [
  ['name' => 'Ion Crest',   'type' => 'Icon',      'price' => '450'],
  ['name' => 'Pulse Edge',  'type' => 'Frame',     'price' => '900'],
  ['name' => 'Afterglow',   'type' => 'Board',     'price' => '1400'],
  ['name' => 'Arc Static',  'type' => 'Card Back', 'price' => '700'],
];

$bundleItems = [
  ['name' => 'Circuit Collection Bundle', 'type' => 'Premium Set', 'price' => '2200'],
  ['name' => 'Volt Starter Set',          'type' => 'Bundle',      'price' => '1600'],
  ['name' => 'Nebula Vanity Pack',        'type' => 'Bundle',      'price' => '1800'],
];

$cosmeticItems = [
  ['name' => 'Nebula Frame',   'type' => 'Frame',      'price' => '900'],
  ['name' => 'Static Burst',   'type' => 'Card Back',  'price' => '700'],
  ['name' => 'Circuit Echo',   'type' => 'Board',      'price' => '1400'],
  ['name' => 'Ion Crest',      'type' => 'Icon',       'price' => '450'],
  ['name' => 'Verdant Glow',   'type' => 'Accent',     'price' => '450'],
  ['name' => 'Afterglow',      'type' => 'Board',      'price' => '1400'],
  ['name' => 'Pulse Edge',     'type' => 'Frame',      'price' => '900'],
  ['name' => 'Arc Static',     'type' => 'Card Back',  'price' => '700'],
];

$allowedTabs = ['featured', 'credits', 'cosmetics', 'daily', 'bundles'];
$activeTab = strtolower(trim((string)($_GET['tab'] ?? 'featured')));
if (!in_array($activeTab, $allowedTabs, true)) {
  $activeTab = 'featured';
}

function is_shop_tab(string $tab, string $activeTab): string {
  return $tab === $activeTab ? ' is-active' : '';
}

ui_header("Shop");
?>

<section class="section section--flush-top">
  <div class="hub-grid">

    <!-- LEFT -->
    <aside class="card hub-left hub-sidebar">
      <div class="hub-sidebar__title">
        MENU
      </div>

      <div class="hub-sidebar__nav">
        <a class="hub-item" href="<?= h($bp) ?>/play.php">
          <span class="hub-ico">🎮</span>
          <span>Play</span>
        </a>

        <a class="hub-item" href="<?= h($bp) ?>/solo.php">
          <span class="hub-ico">🧪</span>
          <span>Solo</span>
        </a>

        <a class="hub-item is-active" href="<?= h($bp) ?>/shop.php">
          <span class="hub-ico">🛒</span>
          <span>Shop</span>
        </a>

        <a class="hub-item" href="<?= h($bp) ?>/profile.php?tab=overview">
          <span class="hub-ico">⚙️</span>
          <span>Options</span>
        </a>
      </div>

      <div class="hub-sidebar__status">
        <span class="pill">Player</span>

        <div class="hub-sidebar__status-block">
          <span class="pill status-pill--good">
            Store Access
          </span>
          <div class="hub-sidebar__hint">
            Browse cosmetics, bundles, and currency packs.
          </div>
        </div>
      </div>
    </aside>

    <!-- CENTER -->
    <main class="page-main">
      <section class="shop-shell shop-shell--clean">

        <nav class="shop-tabsbar" aria-label="Shop categories">
          <a href="<?= h($bp) ?>/shop.php?tab=featured" class="shop-tabsbar__item<?= h(is_shop_tab('featured', $activeTab)) ?>" data-tab-link="featured">Featured</a>
          <a href="<?= h($bp) ?>/shop.php?tab=credits" class="shop-tabsbar__item<?= h(is_shop_tab('credits', $activeTab)) ?>" data-tab-link="credits">Credits</a>
          <a href="<?= h($bp) ?>/shop.php?tab=cosmetics" class="shop-tabsbar__item<?= h(is_shop_tab('cosmetics', $activeTab)) ?>" data-tab-link="cosmetics">Cosmetics</a>
          <a href="<?= h($bp) ?>/shop.php?tab=daily" class="shop-tabsbar__item<?= h(is_shop_tab('daily', $activeTab)) ?>" data-tab-link="daily">Daily</a>
          <a href="<?= h($bp) ?>/shop.php?tab=bundles" class="shop-tabsbar__item<?= h(is_shop_tab('bundles', $activeTab)) ?>" data-tab-link="bundles">Bundles</a>
        </nav>

        <section class="shop-pane" data-tab-pane="featured" <?= $activeTab !== 'featured' ? 'hidden' : '' ?>>
          <div class="shop-pane__head">
            <div>
              <h2>Featured</h2>
              <p>Current storefront highlights.</p>
            </div>
            <button class="btn btn-ghost" type="button" data-switch-tab="cosmetics">View all</button>
          </div>

          <div class="shop-showcase">
            <article class="shop-banner-card">
              <div class="shop-banner-card__content">
                <span class="pill">Limited</span>
                <h3>Circuit Collection Bundle</h3>
                <p>Frame, card back, board style, and profile accent grouped into one premium set.</p>

                <div class="shop-banner-card__actions">
                  <button class="btn btn-primary" type="button" data-switch-tab="bundles">View Bundle</button>
                  <button class="btn btn-ghost" type="button" data-switch-tab="cosmetics">Browse Set</button>
                </div>
              </div>
            </article>

            <div class="shop-featured-strip">
              <?php foreach ($featuredItems as $item): ?>
                <article class="shop-product-tile">
                  <div class="shop-product-tile__art"></div>
                  <span class="pill"><?= h($item['type']) ?></span>
                  <h3><?= h($item['name']) ?></h3>
                  <div class="shop-product-tile__foot">
                    <strong><?= h($item['price']) ?></strong>
                    <button class="btn btn-ghost" type="button">View</button>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        </section>

        <section class="shop-pane" data-tab-pane="credits" <?= $activeTab !== 'credits' ? 'hidden' : '' ?>>
          <div class="shop-pane__head">
            <div>
              <h2>Zeny</h2>
              <p>Top up your wallet for cosmetics, bundles, and future store rotations.</p>
            </div>
          </div>

          <?php if (!empty($topupMsg)): ?>
            <div class="card-soft hub-alert hub-alert--success hub-mb-14">
              <strong>Success.</strong> <?= h($topupMsg) ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($topupErr)): ?>
            <div class="card-soft hub-alert hub-alert--danger hub-mb-14">
              <strong>Heads up.</strong> <?= h($topupErr) ?>
            </div>
          <?php endif; ?>

          <div class="shop-featured-strip">
            <?php foreach ($creditPacks as $pack): ?>
              <?php
                $baseCredits  = (int)$pack['credits'];
                $bonusCredits = (int)$pack['bonus_credits'];
                $totalCredits = $baseCredits + $bonusCredits;
              ?>
              <article class="shop-product-tile">
                <div class="shop-product-tile__art"></div>
                <span class="pill"><?= h($pack['tag']) ?></span>
                <h3><?= h($pack['name']) ?></h3>

                <p class="shop-product-tile__price">
                  <?= number_format($totalCredits) ?> Zeny
                </p>

                <div class="shop-product-tile__foot shop-product-tile__foot--center">
                  <strong>₱<?= number_format((float)$pack['price_php'], 2) ?></strong>

                  <form method="post" action="<?= h($bp) ?>/api/payments/paymongo-topup-create.php" class="hub-form-reset">
                    <input type="hidden" name="pack_code" value="<?= h($pack['code']) ?>">
                    <button class="btn btn-primary" type="submit">Buy Zeny</button>
                  </form>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="shop-pane" data-tab-pane="cosmetics" <?= $activeTab !== 'cosmetics' ? 'hidden' : '' ?>>
          <div class="shop-pane__head">
            <div>
              <h2>Cosmetics</h2>
              <p>Visual upgrades for profile, cards, boards, and account flair.</p>
            </div>
          </div>

          <div class="category-strip category-strip--spaced">
            <span class="category-pill is-active">All</span>
            <span class="category-pill">Frames</span>
            <span class="category-pill">Card Backs</span>
            <span class="category-pill">Boards</span>
            <span class="category-pill">Icons</span>
            <span class="category-pill">Accents</span>
          </div>

          <div class="shop-featured-strip">
            <?php foreach ($cosmeticItems as $item): ?>
              <article class="shop-product-tile">
                <div class="shop-product-tile__art"></div>
                <span class="pill"><?= h($item['type']) ?></span>
                <h3><?= h($item['name']) ?></h3>
                <div class="shop-product-tile__foot">
                  <strong><?= h($item['price']) ?></strong>
                  <button class="btn btn-ghost" type="button">View</button>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="shop-pane" data-tab-pane="daily" <?= $activeTab !== 'daily' ? 'hidden' : '' ?>>
          <div class="shop-pane__head">
            <div>
              <h2>Daily Rotation</h2>
              <p>Smaller picks on a limited-time rotation.</p>
            </div>
          </div>

          <div class="shop-featured-strip">
            <?php foreach ($dailyItems as $item): ?>
              <article class="shop-product-tile">
                <div class="shop-product-tile__art"></div>
                <span class="pill"><?= h($item['type']) ?></span>
                <h3><?= h($item['name']) ?></h3>
                <p class="shop-copy-muted">Available today only.</p>
                <div class="shop-product-tile__foot">
                  <strong><?= h($item['price']) ?></strong>
                  <button class="btn btn-ghost" type="button">Claim</button>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

        <section class="shop-pane" data-tab-pane="bundles" <?= $activeTab !== 'bundles' ? 'hidden' : '' ?>>
          <div class="shop-pane__head">
            <div>
              <h2>Bundles</h2>
              <p>Grouped offers with a cleaner value pitch than buying pieces one by one.</p>
            </div>
          </div>

          <div class="shop-showcase">

            <div class="shop-featured-strip">
              <?php foreach ($bundleItems as $item): ?>
                <article class="shop-product-tile">
                  <div class="shop-product-tile__art"></div>
                  <span class="pill"><?= h($item['type']) ?></span>
                  <h3><?= h($item['name']) ?></h3>
                  <div class="shop-product-tile__foot">
                    <strong><?= h($item['price']) ?></strong>
                    <button class="btn btn-ghost" type="button">View</button>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          </div>
        </section>

      </section>
    </main>

    <!-- RIGHT -->
    <aside class="hub-right">
      <div class="card panel-card--lg">
        <div class="panel-head-simple">
          <div>
            <div class="panel-title">Store Info</div>
            <div class="panel-sub">
              Rotation, bundles, and account wallet
            </div>
          </div>
        </div>

        <div class="stack-10 hub-mt-12">
          <div class="card-soft link-card link-card--block">
            <div class="text-strong">💳 Wallet</div>
            <div class="panel-sub">
              You currently have <?= number_format($currentCredits) ?> Zeny available.
            </div>
          </div>

          <div class="card-soft link-card link-card--block">
            <div class="text-strong">✨ Cosmetics</div>
            <div class="panel-sub">
              Frames, boards, icons, and card backs rotate through the storefront.
            </div>
          </div>

          <div class="card-soft link-card link-card--block">
            <div class="text-strong">📦 Bundles</div>
            <div class="panel-sub">
              Higher-value grouped offers show up in limited windows.
            </div>
          </div>
        </div>
      </div>
    </aside>

  </div>
</section>

<script>
(function () {
  const tabLinks = document.querySelectorAll('[data-tab-link]');
  const panes = document.querySelectorAll('[data-tab-pane]');
  const quickSwitches = document.querySelectorAll('[data-switch-tab]');

  function setActiveTab(tabName) {
    panes.forEach((pane) => {
      pane.hidden = pane.getAttribute('data-tab-pane') !== tabName;
    });

    tabLinks.forEach((link) => {
      link.classList.toggle('is-active', link.getAttribute('data-tab-link') === tabName);
    });

    const url = new URL(window.location.href);
    url.searchParams.set('tab', tabName);
    window.history.replaceState({}, '', url);
  }

  tabLinks.forEach((link) => {
    link.addEventListener('click', function (event) {
      event.preventDefault();
      setActiveTab(this.getAttribute('data-tab-link'));
    });
  });

  quickSwitches.forEach((button) => {
    button.addEventListener('click', function () {
      setActiveTab(this.getAttribute('data-switch-tab'));
    });
  });
})();
</script>

<?php ui_footer(); ?>