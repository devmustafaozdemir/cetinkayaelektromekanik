<article class="product-card reveal">
  <a href="/urunler/<?= e($p['slug']) ?>" class="product-card__media" tabindex="-1" aria-hidden="true">
    <?php if ($p['image']): ?>
      <img src="<?= e(upload_url($p['image'])) ?>" alt="" loading="lazy">
    <?php else: ?>
      <?= product_art($p['art'] ?? 'tank') ?>
    <?php endif; ?>
    <?php if ($p['brand']): ?><span class="product-card__brand"><?= e($p['brand']) ?></span><?php endif; ?>
  </a>
  <div class="product-card__body">
    <?php if (!empty($p['category'])): ?><a href="/urunler/kategori/<?= e($p['category_slug']) ?>" class="product-card__cat"><?= e($p['category']) ?></a><?php endif; ?>
    <h3><a href="/urunler/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></h3>
    <p><?= e($p['summary']) ?></p>
    <div class="product-card__actions">
      <a href="/urunler/<?= e($p['slug']) ?>" class="link-arrow">İncele <?= icon('arrow-right') ?></a>
      <a href="/teklif-al?urun=<?= e($p['slug']) ?>" class="btn btn--primary btn--sm">Teklif İste</a>
    </div>
  </div>
</article>
