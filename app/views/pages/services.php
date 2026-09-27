<?php partial('page-hero', ['heading' => 'Hizmetlerimiz', 'lead' => 'Satışını yaptığımız ürünler için keşiften montaja, bakımdan sevkiyata kadar uçtan uca destek.', 'crumbs' => [[null, 'Hizmetler']]]); ?>
<section class="section">
  <div class="container">
    <div class="services-grid">
      <?php foreach ($services as $s): ?>
        <a href="/hizmetler/<?= e($s['slug']) ?>" class="service-card reveal">
          <span class="service-card__icon"><?= icon($s['icon']) ?></span>
          <h3><?= e($s['title']) ?></h3>
          <p><?= e($s['summary']) ?></p>
          <span class="link-arrow">Detaylı bilgi <?= icon('arrow-right') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<section class="section section--muted">
  <div class="container">
    <div class="section-head"><span class="eyebrow">Markalarımız</span><h2>Güçlü markalar, güvenilir ürünler</h2><p>Satış ve hizmetlerimizi dünya çapında tanınan markaların ürünleriyle sunuyoruz.</p></div>
    <?php partial('brand-logos'); ?>
  </div>
</section>
