<?php partial('page-hero', ['heading' => 'Hizmetlerimiz', 'lead' => 'Yetkili servis güvencesiyle profesyonel el aletleri ve elektrik motorları için eksiksiz onarım, bakım ve yedek parça hizmetleri.', 'crumbs' => [[null, 'Hizmetler']]]); ?>
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
    <div class="section-head"><span class="eyebrow">Yetkili Markalar</span><h2>Üretici onaylı servis</h2><p>Garanti kapsamındaki ürünleriniz üretici prosedürlerine uygun olarak onarılır.</p></div>
    <?php partial('brand-logos'); ?>
  </div>
</section>
