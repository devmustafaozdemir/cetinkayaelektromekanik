<?php
$crumbs = [['/urunler', 'Ürünler']];
if ($product['category']) $crumbs[] = ['/urunler/kategori/' . $product['category_slug'], $product['category']];
$crumbs[] = [null, $product['title']];
$model = product_model($product);
$waText = 'Merhaba, "' . $product['title'] . '" için fiyat bilgisi almak istiyorum.';
?>
<section class="pd">
  <div class="container">
    <nav class="breadcrumb" aria-label="Sayfa yolu">
      <a href="/">Ana sayfa</a>
      <?php foreach ($crumbs as [$href, $label]): ?><span aria-hidden="true">/</span><?php if ($href): ?><a href="<?= e($href) ?>"><?= e($label) ?></a><?php else: ?><span aria-current="page"><?= e($label) ?></span><?php endif; ?><?php endforeach; ?>
    </nav>
    <div class="pd__grid">
      <div class="pd__visual" data-model-view data-model="<?= e($model) ?>" data-viewer="<?= e(asset('js/viewer3d.js')) ?>">
        <div class="pd__media">
          <div class="pd__2d" data-svg><?php if ($product['image']): ?><img src="<?= e(upload_url($product['image'])) ?>" alt="<?= e($product['title']) ?>"><?php else: ?><?= model_svg($model) ?><?php endif; ?></div>
          <div class="viewer" data-viewer-host hidden></div>
        </div>
        <div class="pd__visual-bar">
          <div class="viewswitch viewswitch--light" role="group" aria-label="Görünüm">
            <button type="button" data-view="2d" aria-pressed="true"><?= $product['image'] ? 'Fotoğraf' : 'Çizim' ?></button>
            <button type="button" data-view="3d" aria-pressed="false">3D incele</button>
          </div>
          <span class="muted small" data-hint hidden>Sürükleyerek çevirin, yakınlaştırmak için kaydırın.</span>
        </div>
      </div>
      <div class="pd__info">
        <?php if ($product['brand']): ?><a href="/urunler?marka=<?= rawurlencode($product['brand']) ?>" class="pd__brand" title="<?= e($product['brand']) ?> ürünleri"><?= brand_logo($product['brand']) ?></a><?php endif; ?>
        <h1><?= e($product['title']) ?></h1>
        <p class="lead"><?= e($product['summary']) ?></p>
        <?php if ($specs): ?>
          <dl class="pd__specs">
            <?php foreach (array_slice($specs, 0, 5) as [$k, $v]): ?><div><dt><?= e($k) ?></dt><dd><?= e($v) ?></dd></div><?php endforeach; ?>
          </dl>
        <?php endif; ?>
        <div class="pd__actions">
          <a href="/teklif-al?urun=<?= e($product['slug']) ?>" class="btn btn--signal btn--lg">Bu ürün için teklif iste</a>
          <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')), $waText)) ?>" target="_blank" rel="noopener" class="btn btn--line btn--lg"><?= icon('whatsapp') ?> WhatsApp</a>
        </div>
        <p class="pd__note">Kapasite, model ve montaj seçeneklerini teklif aşamasında projenize göre netleştiriyoruz.</p>
      </div>
    </div>
  </div>
</section>
<section class="block block--tight">
  <div class="container pd__body">
    <div>
      <article class="prose">
        <h2>Ürün hakkında</h2>
        <?= $product['content'] ?: '<p>' . e($product['summary']) . '</p>' ?>
      </article>
      <?php if ($specs): ?>
        <h2 class="spec-title" id="ozellikler">Teknik özellikler</h2>
        <table class="spec-table"><tbody>
          <?php foreach ($specs as [$k, $v]): ?><tr><th scope="row"><?= e($k) ?></th><td><?= e($v) ?></td></tr><?php endforeach; ?>
        </tbody></table>
        <p class="muted small">Değerler model ve kapasiteye göre değişir; projenize uygun değerleri teklifte belirtiriz.</p>
      <?php endif; ?>
    </div>
    <aside class="aside-box">
      <h2>Fiyat almak için</h2>
      <p>Miktarı ve kullanım yerini yazmanız yeterli. Emin olmadığınız bilgileri birlikte netleştiririz.</p>
      <a href="/teklif-al?urun=<?= e($product['slug']) ?>" class="btn btn--signal btn--block">Teklif iste</a>
      <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--line-light btn--block"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
    </aside>
  </div>
</section>
<?php if ($related): ?>
<section class="block block--steel">
  <div class="container">
    <div class="block__head"><h2>Benzer ürünler</h2></div>
    <div class="product-grid">
      <?php foreach ($related as $p) partial('product-card', ['p' => $p]); ?>
    </div>
  </div>
</section>
<?php endif; ?>
