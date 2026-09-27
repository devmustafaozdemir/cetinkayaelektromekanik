<?php
$baseUrl = $category ? '/urunler/kategori/' . $category['slug'] : '/urunler';
$crumbs = $category ? [['/urunler', 'Ürünler'], [null, $category['name']]] : [[null, 'Ürünler']];
$total = array_sum(array_column($categories, 'cnt'));
?>
<?php partial('page-hero', ['heading' => $category ? $category['name'] : 'Ürünlerimiz', 'lead' => $category ? $category['summary'] : 'Modüler su depoları, hidrofor sistemleri ve pompalarda güçlü markalar, projeye uygun çözümler.', 'crumbs' => $crumbs]); ?>
<section class="section section--tight">
  <div class="container catalog">
    <aside class="catalog__side">
      <div class="filter-card">
        <h3>Ürün Grupları</h3>
        <ul class="filter-list">
          <li><a href="/urunler" class="<?= !$category ? 'is-active' : '' ?>"><span>Tüm ürünler</span><em><?= $total ?></em></a></li>
          <?php foreach ($categories as $c): ?>
            <li><a href="/urunler/kategori/<?= e($c['slug']) ?>" class="<?= $category && (int)$category['id'] === (int)$c['id'] ? 'is-active' : '' ?>"><span><?= e($c['name']) ?></span><em><?= (int)$c['cnt'] ?></em></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php if ($brandList): ?>
      <div class="filter-card">
        <h3>Markalar</h3>
        <div class="brand-pills">
          <a href="<?= e($baseUrl) ?>" class="<?= $brand === '' ? 'is-active' : '' ?>">Tümü</a>
          <?php foreach ($brandList as $b): ?>
            <a href="<?= e($baseUrl) ?>?marka=<?= rawurlencode($b) ?>" class="<?= $brand === $b ? 'is-active' : '' ?>"><?= e($b) ?></a>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
      <div class="aside-card aside-card--dark">
        <h3>Aradığınızı bulamadınız mı?</h3>
        <p>Listede olmayan ürünler ve projeye özel ihtiyaçlar için bize yazın.</p>
        <a href="/teklif-al" class="btn btn--primary btn--block"><?= icon('calculator') ?> Teklif Al</a>
      </div>
    </aside>
    <div class="catalog__main">
      <div class="catalog__bar">
        <p class="muted"><strong><?= count($products) ?></strong> ürün listeleniyor<?= $brand !== '' ? ' · <strong>' . e($brand) . '</strong>' : '' ?></p>
        <form class="search-box" method="get" action="<?= e($baseUrl) ?>" role="search">
          <?= icon('search') ?>
          <?php if ($brand !== ''): ?><input type="hidden" name="marka" value="<?= e($brand) ?>"><?php endif; ?>
          <input type="search" name="q" value="<?= e($search) ?>" placeholder="Ürün ara…" aria-label="Ürünlerde ara">
        </form>
      </div>
      <?php if ($products): ?>
        <div class="products-grid products-grid--3">
          <?php foreach ($products as $p) partial('product-card', ['p' => $p]); ?>
        </div>
      <?php else: ?>
        <div class="empty-box"><?= icon('package') ?><p>Bu kriterlere uygun ürün bulunamadı.<br><a href="/urunler">Tüm ürünleri görün →</a></p></div>
      <?php endif; ?>
    </div>
  </div>
</section>
