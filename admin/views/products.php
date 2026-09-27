<div class="page-head">
  <div class="tabs tabs--scroll">
    <a href="<?= admin_url('products') ?>" class="<?= !$cat ? 'is-active' : '' ?>">Tümü <em><?= $total ?></em></a>
    <?php foreach ($categories as $c): ?>
      <a href="<?= admin_url('products', ['kategori' => $c['id']]) ?>" class="<?= $cat === (int)$c['id'] ? 'is-active' : '' ?>"><?= e($c['name']) ?> <em><?= (int)$c['cnt'] ?></em></a>
    <?php endforeach; ?>
  </div>
  <div class="page-head__actions">
    <form class="search" method="get"><input type="hidden" name="p" value="products"><?php if ($cat): ?><input type="hidden" name="kategori" value="<?= $cat ?>"><?php endif; ?><?= icon('search') ?><input type="search" name="q" value="<?= e($search) ?>" placeholder="Ürün veya marka ara…"></form>
    <a href="<?= admin_url('products', array_filter(['a' => 'edit', 'kategori' => $cat ?: null])) ?>" class="btn btn--primary"><?= icon('plus') ?> Yeni Ürün</a>
  </div>
</div>
<div class="card card--flush">
  <?php if ($rows): ?>
  <table class="table">
    <thead><tr><th>Ürün</th><th class="hide-sm">Kategori</th><th class="hide-sm">Marka</th><th>Durum</th><th class="hide-sm num">Teklif</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td>
          <a href="<?= admin_url('products', ['a' => 'edit', 'id' => $r['id']]) ?>" class="row-title">
            <span class="thumb thumb--art"><?php if ($r['image']): ?><img src="<?= e(upload_url($r['image'])) ?>" alt=""><?php else: ?><?= product_art($r['art'] ?? 'tank') ?><?php endif; ?></span>
            <span><?= e($r['title']) ?><?php if ($r['featured']): ?> <span class="tag tag--amber"><?= icon('star') ?> Öne çıkan</span><?php endif; ?><small>/urunler/<?= e($r['slug']) ?></small></span>
          </a>
        </td>
        <td class="hide-sm"><?= e($r['category'] ?? '—') ?></td>
        <td class="hide-sm"><?= e($r['brand'] ?: '—') ?></td>
        <td>
          <form method="post" action="<?= admin_url('products', array_filter(['a' => 'toggle', 'id' => $r['id'], 'kategori' => $cat ?: null])) ?>"><?= csrf_field() ?>
            <button class="status status--<?= $r['active'] ? 'green' : 'slate' ?> status--btn" title="Değiştirmek için tıklayın"><?= $r['active'] ? 'Yayında' : 'Gizli' ?></button>
          </form>
        </td>
        <td class="hide-sm num"><?= (int)$r['quotes'] ?></td>
        <td class="actions">
          <a href="/urunler/<?= e($r['slug']) ?>" target="_blank" class="icon-btn" title="Görüntüle"><?= icon('eye') ?></a>
          <a href="<?= admin_url('products', ['a' => 'edit', 'id' => $r['id']]) ?>" class="icon-btn" title="Düzenle"><?= icon('edit') ?></a>
          <form method="post" action="<?= admin_url('products', ['a' => 'delete', 'id' => $r['id']]) ?>" data-confirm="“<?= e($r['title']) ?>” silinsin mi?"><?= csrf_field() ?><button class="icon-btn icon-btn--danger" title="Sil"><?= icon('trash') ?></button></form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div class="empty"><?= icon('package') ?><h3>Ürün bulunamadı</h3><a href="<?= admin_url('products', ['a' => 'edit']) ?>" class="btn btn--primary"><?= icon('plus') ?> Yeni Ürün</a></div>
  <?php endif; ?>
</div>
