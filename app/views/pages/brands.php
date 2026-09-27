<?php partial('page-hero', ['heading' => 'Markalarımız', 'lead' => 'Su depolama ve pompa sistemlerinde güvenilir markaların ürünlerini sunuyoruz.', 'crumbs' => [[null, 'Markalar']]]); ?>
<section class="section">
  <div class="container">
    <div class="brand-grid">
      <?php foreach (brands() as $b): ?>
        <a href="/urunler?marka=<?= rawurlencode($b['name']) ?>" class="brand-card reveal">
          <span class="brand-card__logo"><?= e($b['name']) ?></span>
          <span class="brand-card__desc"><?= e($b['desc']) ?></span>
          <span class="link-arrow"><?= (int)($counts[$b['name']] ?? 0) ?> ürün <?= icon('arrow-right') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
