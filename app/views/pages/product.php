<?php
$crumbs = [['/urunler', 'Ürünler']];
if ($product['category']) $crumbs[] = ['/urunler/kategori/' . $product['category_slug'], $product['category']];
$crumbs[] = [null, $product['title']];
$waText = 'Merhaba, "' . $product['title'] . '" için fiyat bilgisi almak istiyorum.';
?>
<section class="product-hero">
  <div class="container">
    <nav class="breadcrumb breadcrumb--dark" aria-label="Sayfa yolu">
      <a href="/">Ana Sayfa</a>
      <?php foreach ($crumbs as [$href, $label]): ?>
        <?= icon('chevron-right') ?><?php if ($href): ?><a href="<?= e($href) ?>"><?= e($label) ?></a><?php else: ?><span aria-current="page"><?= e($label) ?></span><?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="product-top">
      <div class="product-gallery">
        <?php if ($product['image']): ?>
          <img src="<?= e(upload_url($product['image'])) ?>" alt="<?= e($product['title']) ?>">
        <?php else: ?>
          <?= product_art($product['art'] ?? 'tank') ?>
        <?php endif; ?>
      </div>
      <div class="product-info">
        <?php if ($product['brand']): ?><a href="/urunler?marka=<?= rawurlencode($product['brand']) ?>" class="product-info__brand"><?= e($product['brand']) ?></a><?php endif; ?>
        <h1><?= e($product['title']) ?></h1>
        <p class="lead"><?= e($product['summary']) ?></p>
        <?php if ($specs): ?>
          <ul class="spec-mini">
            <?php foreach (array_slice($specs, 0, 4) as [$k, $v]): ?><li><span><?= e($k) ?></span><strong><?= e($v) ?></strong></li><?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <div class="product-info__actions">
          <a href="/teklif-al?urun=<?= e($product['slug']) ?>" class="btn btn--primary btn--lg"><?= icon('calculator') ?> Teklif İste</a>
          <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')), $waText)) ?>" target="_blank" rel="noopener" class="btn btn--ghost btn--lg"><?= icon('whatsapp') ?> WhatsApp</a>
        </div>
        <ul class="product-perks">
          <li><?= icon('shield') ?> Orijinal ve garantili ürün</li>
          <li><?= icon('ruler') ?> Ücretsiz kapasite danışmanlığı</li>
          <li><?= icon('truck') ?> Sevkiyat ve montaj desteği</li>
        </ul>
      </div>
    </div>
  </div>
</section>
<section class="section section--tight">
  <div class="container layout-aside">
    <div>
      <div class="tabs-lite" role="tablist">
        <a href="#aciklama" class="is-active">Ürün Açıklaması</a>
        <?php if ($specs): ?><a href="#ozellikler">Teknik Özellikler</a><?php endif; ?>
      </div>
      <article class="prose" id="aciklama">
        <?= $product['content'] ?: '<p>' . e($product['summary']) . '</p>' ?>
      </article>
      <?php if ($specs): ?>
        <h2 class="spec-title" id="ozellikler">Teknik Özellikler</h2>
        <table class="spec-table">
          <tbody>
            <?php foreach ($specs as [$k, $v]): ?><tr><th><?= e($k) ?></th><td><?= e($v) ?></td></tr><?php endforeach; ?>
          </tbody>
        </table>
        <p class="muted small">Teknik değerler model ve kapasiteye göre değişiklik gösterebilir. Projenize uygun değerler için bizimle iletişime geçin.</p>
      <?php endif; ?>
    </div>
    <aside class="aside">
      <div class="aside-card aside-card--dark">
        <h3>Bu ürün için fiyat alın</h3>
        <p>İhtiyacınızı yazın, en kısa sürede size özel teklifle dönelim.</p>
        <a href="/teklif-al?urun=<?= e($product['slug']) ?>" class="btn btn--primary btn--block"><?= icon('calculator') ?> Teklif İste</a>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--outline-light btn--block"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
      </div>
      <?php if ($product['category']): ?>
      <div class="aside-card">
        <h3>Kategori</h3>
        <a href="/urunler/kategori/<?= e($product['category_slug']) ?>" class="link-arrow"><?= e($product['category']) ?> <?= icon('arrow-right') ?></a>
      </div>
      <?php endif; ?>
    </aside>
  </div>
</section>
<?php if ($related): ?>
<section class="section section--muted">
  <div class="container">
    <div class="section-head section-head--row"><div><span class="eyebrow">Benzer Ürünler</span><h2>Bunlar da ilginizi çekebilir</h2></div></div>
    <div class="products-grid">
      <?php foreach ($related as $p) partial('product-card', ['p' => $p]); ?>
    </div>
  </div>
</section>
<?php endif; ?>
