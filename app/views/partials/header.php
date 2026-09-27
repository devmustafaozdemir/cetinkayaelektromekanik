<?php
$nav = [
    'products' => ['/urunler', 'Ürünler'],
    'brands'   => ['/markalar', 'Markalar'],
    'services' => ['/hizmetler', 'Hizmetler'],
    'about'    => ['/hakkimizda', 'Kurumsal'],
    'blog'     => ['/blog', 'Blog'],
    'contact'  => ['/iletisim', 'İletişim'],
];
$navCats = q_all('SELECT name, slug, art FROM product_categories ORDER BY sort, id');
?>
<div class="topbar">
  <div class="container topbar__inner">
    <div class="topbar__info">
      <span><?= icon('map-pin') ?> <?= e(setting('address_short', 'Kocaeli')) ?></span>
      <span class="hide-sm"><?= icon('clock') ?> <?= e(setting_lines('hours')[0] ?? '') ?></span>
    </div>
    <div class="topbar__links">
      <a href="mailto:<?= e(setting('email')) ?>" class="hide-sm"><?= icon('mail') ?> <?= e(setting('email')) ?></a>
      <a href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
    </div>
  </div>
</div>
<header class="header" data-header>
  <div class="container header__inner">
    <a href="/" class="logo" aria-label="<?= e(setting('site_name')) ?> ana sayfa">
      <span class="logo__mark"><?= icon('zap') ?></span>
      <span class="logo__text">
        <strong>Çetinkaya</strong>
        <small>Elektromekanik</small>
      </span>
    </a>
    <nav class="nav" id="nav" aria-label="Ana menü">
      <ul>
        <?php foreach ($nav as $key => [$href, $label]): ?>
          <?php if ($key === 'products' && $navCats): ?>
          <li class="has-mega">
            <a href="<?= $href ?>" class="<?= $active === $key ? 'is-active' : '' ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?> <?= icon('chevron-down', 'icon icon--sm') ?></a>
            <div class="mega">
              <?php foreach ($navCats as $c): ?>
                <a href="/urunler/kategori/<?= e($c['slug']) ?>" class="mega__item"><span class="mega__art"><?= product_art($c['art']) ?></span><span><?= e($c['name']) ?></span></a>
              <?php endforeach; ?>
              <a href="/urunler" class="mega__all">Tüm ürünler <?= icon('arrow-right') ?></a>
            </div>
          </li>
          <?php else: ?>
          <li><a href="<?= $href ?>" class="<?= $active === $key ? 'is-active' : '' ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
      <div class="nav__mobile-cta">
        <a href="/teklif-al" class="btn btn--primary btn--block"><?= icon('calculator') ?> Teklif Al</a>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--ghost btn--block"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
      </div>
    </nav>
    <div class="header__actions">
      <a href="/teklif-al" class="btn btn--primary btn--sm hide-md"><?= icon('calculator') ?> Teklif Al</a>
      <button class="nav-toggle" type="button" aria-controls="nav" aria-expanded="false" aria-label="Menüyü aç">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>
