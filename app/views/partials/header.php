<?php
$nav = [
    'home'     => ['/', 'Ana Sayfa'],
    'services' => ['/hizmetler', 'Hizmetler'],
    'about'    => ['/hakkimizda', 'Hakkımızda'],
    'blog'     => ['/blog', 'Blog'],
    'track'    => ['/servis-takip', 'Servis Takip'],
    'contact'  => ['/iletisim', 'İletişim'],
];
?>
<div class="topbar">
  <div class="container topbar__inner">
    <div class="topbar__info">
      <span><?= icon('map-pin') ?> İzmit Sanayi Sitesi, Kocaeli</span>
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
          <li><a href="<?= $href ?>" class="<?= $active === $key ? 'is-active' : '' ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="nav__mobile-cta">
        <a href="/servis-talebi" class="btn btn--primary btn--block"><?= icon('clipboard') ?> Servis Talebi Oluştur</a>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--ghost btn--block"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
      </div>
    </nav>
    <div class="header__actions">
      <a href="/servis-talebi" class="btn btn--primary btn--sm hide-md"><?= icon('clipboard') ?> Servis Talebi</a>
      <button class="nav-toggle" type="button" aria-controls="nav" aria-expanded="false" aria-label="Menüyü aç">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>
