<article class="product">
  <a href="/urunler/<?= e($p['slug']) ?>" class="product__media" tabindex="-1" aria-hidden="true">
    <?php if ($p['brand']): ?><span class="product__brand"><?= brand_logo($p['brand']) ?></span><?php endif; ?>
    <?php if ($p['image']): ?><img src="<?= e(upload_url($p['image'])) ?>" alt="" loading="lazy"><?php else: ?><?= model_svg(product_model($p)) ?><?php endif; ?>
  </a>
  <div class="product__body">
    <p class="product__meta"><?= e($p['category'] ?? '') ?></p>
    <h3><a href="/urunler/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></h3>
    <p class="product__summary"><?= e($p['summary']) ?></p>
    <a href="/teklif-al?urun=<?= e($p['slug']) ?>" class="product__quote">Teklif iste</a>
  </div>
</article>
