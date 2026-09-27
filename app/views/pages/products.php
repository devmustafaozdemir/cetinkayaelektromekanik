<?php
$baseUrl = $category ? '/urunler/kategori/' . $category['slug'] : '/urunler';
$crumbs = $category ? [['/urunler', 'Ürünler'], [null, $category['name']]] : [[null, 'Ürünler']];
$total = array_sum(array_column($categories, 'cnt'));
?>
<?php partial('page-hero', ['heading' => $category ? $category['name'] : 'Ürünler', 'lead' => $category ? $category['summary'] : 'Modüler su depoları, hidrofor setleri, santrifüj ve dalgıç pompalar. Her ürünü çizim ve 3D olarak inceleyebilir, tek tıkla teklif isteyebilirsiniz.', 'crumbs' => $crumbs]); ?>
<section class="block block--tight">
  <div class="container catalog">
    <aside class="catalog__side">
      <nav aria-label="Ürün grupları">
        <h2>Ürün grupları</h2>
        <ul class="filter-list">
          <li><a href="/urunler"<?= !$category ? ' aria-current="page"' : '' ?>><span>Tüm ürünler</span><em><?= $total ?></em></a></li>
          <?php foreach ($categories as $c): ?>
            <li><a href="/urunler/kategori/<?= e($c['slug']) ?>"<?= $category && (int)$category['id'] === (int)$c['id'] ? ' aria-current="page"' : '' ?>><span><?= e($c['name']) ?></span><em><?= (int)$c['cnt'] ?></em></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>
      <?php if ($brandList): ?>
      <nav aria-label="Markalar">
        <h2>Marka</h2>
        <div class="pills">
          <a href="<?= e($baseUrl) ?>"<?= $brand === '' ? ' aria-current="page"' : '' ?>>Hepsi</a>
          <?php foreach ($brandList as $b): ?><a href="<?= e($baseUrl) ?>?marka=<?= rawurlencode($b) ?>"<?= $brand === $b ? ' aria-current="page"' : '' ?>><?= e($b) ?></a><?php endforeach; ?>
        </div>
      </nav>
      <?php endif; ?>
      <div class="catalog__help">
        <h2>Listede yok mu?</h2>
        <p>Projeye özel ürünleri ve farklı modelleri de tedarik ediyoruz.</p>
        <a href="/teklif-al" class="text-link">Talebinizi yazın</a>
      </div>
    </aside>
    <div>
      <div class="catalog__bar">
        <p><?= count($products) ?> ürün<?= $brand !== '' ? ', ' . e($brand) : '' ?><?= $search !== '' ? ', “' . e($search) . '” araması' : '' ?></p>
        <form class="search" method="get" action="<?= e($baseUrl) ?>" role="search">
          <?= icon('search') ?>
          <?php if ($brand !== ''): ?><input type="hidden" name="marka" value="<?= e($brand) ?>"><?php endif; ?>
          <input type="search" name="q" value="<?= e($search) ?>" placeholder="Ürün adı veya marka" aria-label="Ürünlerde ara">
        </form>
      </div>
      <?php if ($products): ?>
        <div class="product-grid product-grid--3">
          <?php foreach ($products as $p) partial('product-card', ['p' => $p]); ?>
        </div>
      <?php else: ?>
        <div class="empty"><p>Bu filtreyle eşleşen ürün yok. Aramayı değiştirin ya da <a href="/urunler" class="text-link">tüm ürünlere dönün</a>.</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>
