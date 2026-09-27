<?php
$values = setting_lines('about_values');
$stats = array_map(fn($l) => array_pad(explode('|', $l, 2), 2, ''), setting_lines('stats'));
$statIcons = ['users', 'truck', 'layers', 'award', 'check-circle', 'star'];
$tints = ['tank' => 'sky', 'booster' => 'navy', 'pump' => 'mist', 'submersible' => 'sea', 'drop' => 'sky'];
?>
<section class="hero">
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container hero__inner">
    <div class="hero__copy">
      <span class="pill"><?= icon('droplet') ?> <?= e(setting('hero_badge')) ?></span>
      <h1><?= e(setting('hero_title')) ?></h1>
      <p class="hero__text"><?= e(setting('hero_text')) ?></p>
      <div class="hero__actions">
        <a href="/urunler" class="btn btn--signal btn--lg">Ürünleri incele <?= icon('arrow-right') ?></a>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--line btn--lg"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
      </div>
      <ul class="hero__trust">
        <?php foreach (setting_lines('hero_trust') as $t): ?><li><?= icon('check-circle') ?> <?= e($t) ?></li><?php endforeach; ?>
      </ul>
    </div>

    <?php partial('designer', ['mode' => 'compact']); ?>
  </div>
  <div class="container">
    <div class="logo-strip">
      <span class="logo-strip__label">Sattığımız markalar</span>
      <?php partial('brand-logos'); ?>
    </div>
  </div>
</section>

<?php if ($stats): ?>
<section class="proof">
  <div class="container">
    <div class="proof__card">
      <div class="proof__intro">
        <h2><?= e(setting('stats_title', 'Sahada kanıtlanmış çözümler')) ?></h2>
        <p><?= e(setting('stats_text')) ?></p>
      </div>
      <ul class="proof__stats">
        <?php foreach ($stats as $i => [$num, $label]): ?>
          <li><span class="proof__icon"><?= icon($statIcons[$i % count($statIcons)]) ?></span><strong data-count="<?= e($num) ?>"><?= e($num) ?></strong><span><?= e($label) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="block">
  <div class="container">
    <div class="block__head block__head--center">
      <span class="kicker"><?= e(setting('home_cats_kicker')) ?></span>
      <h2><?= e(setting('home_cats_title')) ?></h2>
      <p><?= e(setting('home_cats_text')) ?></p>
    </div>
    <div class="cat-grid">
      <?php foreach ($categories as $c): ?>
        <a href="/urunler/kategori/<?= e($c['slug']) ?>" class="cat-card cat-card--<?= $tints[$c['art']] ?? 'sky' ?>">
          <span class="cat-card__art"><?= category_photo($c, false) ?></span>
          <span class="cat-card__body">
            <h3><?= e($c['name']) ?></h3>
            <p><?= e($c['summary']) ?></p>
            <span class="cat-card__more"><?= (int)$c['cnt'] ?> ürün <?= icon('arrow-right') ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($products): ?>
<section class="block block--soft">
  <div class="container">
    <div class="block__head block__head--row">
      <div><span class="kicker"><?= e(setting('home_featured_kicker')) ?></span><h2><?= e(setting('home_featured_title')) ?></h2></div>
      <a href="/urunler" class="btn btn--line">Tüm ürünler <?= icon('arrow-right') ?></a>
    </div>
    <div class="product-grid">
      <?php foreach ($products as $p) partial('product-card', ['p' => $p]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="block">
  <div class="container">
    <div class="block__head block__head--center">
      <span class="kicker"><?= e(setting('home_steps_kicker')) ?></span>
      <h2><?= e(setting('home_steps_title')) ?></h2>
    </div>
    <ol class="steps">
      <?php $stepIcons = ['clipboard', 'ruler', 'calculator', 'truck', 'check-circle', 'handshake']; foreach (setting_pairs('home_steps') as $i => [$h, $t]): ?>
        <li><span class="steps__icon"><?= icon($stepIcons[$i % count($stepIcons)]) ?></span><h3><?= e($h) ?></h3><p><?= e($t) ?></p></li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="block block--soft">
  <div class="container why">
    <div class="why__art" aria-hidden="true">
      <div class="why__stage"><?= model_svg('tank:paslanmaz', 'art', ['w' => 4, 'l' => 3, 'h' => 2]) ?></div>
      <?php foreach (array_slice(setting_pairs('home_why_badges'), 0, 2) as $i => [$a, $b]): ?>
        <div class="why__badge why__badge--<?= $i ? 'b' : 'a' ?>"><?= icon($i ? 'wrench' : 'shield') ?><span><strong><?= e($a) ?></strong><?= e($b) ?></span></div>
      <?php endforeach; ?>
    </div>
    <div>
      <span class="kicker"><?= e(setting('home_why_kicker')) ?></span>
      <h2><?= e(setting('about_title')) ?></h2>
      <p class="muted"><?= e(explode("\n", setting('about_text'))[0]) ?></p>
      <ul class="ticks"><?php foreach ($values as $v): ?><li><?= e($v) ?></li><?php endforeach; ?></ul>
      <a href="/hakkimizda" class="btn btn--navy">Bizi tanıyın <?= icon('arrow-right') ?></a>
    </div>
  </div>
</section>

<?php if ($services): ?>
<section class="block">
  <div class="container">
    <div class="block__head block__head--center">
      <span class="kicker"><?= e(setting('home_services_kicker')) ?></span>
      <h2><?= e(setting('home_services_title')) ?></h2>
    </div>
    <div class="svc-grid">
      <?php foreach ($services as $s): ?>
        <a href="/hizmetler/<?= e($s['slug']) ?>" class="svc-card"><span class="svc-card__icon"><?= icon($s['icon']) ?></span><h3><?= e($s['title']) ?></h3><p><?= e($s['summary']) ?></p></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($refs): ?>
<section class="block">
  <div class="container">
    <div class="block__head block__head--row">
      <div><span class="kicker"><?= e(setting('home_refs_kicker')) ?></span><h2><?= e(setting('home_refs_title')) ?></h2><p><?= e(setting('home_refs_text')) ?></p></div>
      <a href="/referanslar" class="btn btn--line">Tüm referanslar <?= icon('arrow-right') ?></a>
    </div>
    <ul class="ref-grid"><?php foreach ($refs as $r) partial('ref-card', ['r' => $r]); ?></ul>
  </div>
</section>
<?php endif; ?>

<?php if ($posts): ?>
<section class="block block--soft">
  <div class="container">
    <div class="block__head block__head--row">
      <div><span class="kicker"><?= e(setting('home_blog_kicker')) ?></span><h2><?= e(setting('home_blog_title')) ?></h2></div>
      <a href="/blog" class="btn btn--line">Bütün yazılar <?= icon('arrow-right') ?></a>
    </div>
    <div class="post-grid">
      <?php foreach ($posts as $post) partial('post-card', ['post' => $post]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="block">
  <div class="container duo">
    <div>
      <span class="kicker"><?= e(setting('home_faq_kicker')) ?></span>
      <h2><?= e(setting('home_faq_title')) ?></h2>
      <p class="muted"><?= e(setting('home_faq_text')) ?></p>
      <div class="contact-chips">
        <a href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?><span><small>Telefon</small><?= e(setting('phone')) ?></span></a>
        <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')))) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?><span><small>WhatsApp</small><?= e(setting('whatsapp', setting('phone2'))) ?></span></a>
      </div>
    </div>
    <div>
      <?php partial('faq-list', ['faqs' => $faqs]); ?>
      <p class="mt"><a href="/sss" class="text-link">Bütün sorular</a></p>
    </div>
  </div>
</section>
