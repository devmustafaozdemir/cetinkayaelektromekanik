<div class="grid-2 grid-2--aside">
  <div>
    <p class="muted mb">Sürükleyerek sıralayın. Sıralama menüde, ana sayfada ve ürünler sayfasında kullanılır.</p>
    <ul class="sortable" data-sortable="<?= admin_url('pcategories', ['a' => 'sort']) ?>">
      <?php foreach ($rows as $r): ?>
        <li class="sortable__item card" draggable="true" data-id="<?= $r['id'] ?>">
          <span class="drag-handle">⋮⋮</span>
          <span class="thumb thumb--art"><?= product_art($r['art']) ?></span>
          <div class="sortable__body"><strong><?= e($r['name']) ?></strong><small class="muted"><?= (int)$r['cnt'] ?> ürün · /urunler/kategori/<?= e($r['slug']) ?></small></div>
          <div class="actions">
            <a href="<?= admin_url('products', ['kategori' => $r['id']]) ?>" class="icon-btn" title="Ürünleri"><?= icon('package') ?></a>
            <a href="<?= admin_url('pcategories', ['id' => $r['id']]) ?>" class="icon-btn" title="Düzenle"><?= icon('edit') ?></a>
            <form method="post" action="<?= admin_url('pcategories', ['a' => 'delete', 'id' => $r['id']]) ?>" data-confirm="“<?= e($r['name']) ?>” kategorisi silinsin mi?"><?= csrf_field() ?><button class="icon-btn icon-btn--danger" title="Sil"><?= icon('trash') ?></button></form>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="card sticky">
    <h3><?= $edit ? 'Kategoriyi düzenle' : 'Yeni kategori' ?></h3>
    <form method="post" enctype="multipart/form-data" action="<?= admin_url('pcategories', $edit ? ['id' => $edit['id']] : []) ?>" class="form-stack">
      <?= csrf_field() ?>
      <label class="field"><span>Ad</span><input name="name" value="<?= e($edit['name'] ?? '') ?>" required></label>
      <label class="field"><span>Adres (slug)</span><input name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="otomatik"></label>
      <label class="field"><span>Kısa açıklama</span><textarea name="summary" rows="3"><?= e($edit['summary'] ?? '') ?></textarea></label>
      <div class="field"><span>Görsel (çizim)</span>
        <div class="art-picker">
          <?php foreach (product_arts() as $k => $l): ?>
            <label title="<?= e($l) ?>"><input type="radio" name="art" value="<?= $k ?>"<?= ($edit['art'] ?? 'tank') === $k ? ' checked' : '' ?>><span><?= product_art($k) ?><small><?= e($l) ?></small></span></label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="field"><span>Kategori fotoğrafı</span>
        <?php if (!empty($edit['photo'])): ?><span class="photo-preview"><?= category_photo($edit, false) ?></span><?php endif; ?>
        <input type="file" name="photo_file" accept="image/jpeg,image/png,image/webp">
        <input type="text" name="photo" value="<?= e($edit['photo'] ?? '') ?>" placeholder="veya https:// ile başlayan görsel adresi">
        <small class="muted">Fotoğraf yoksa yukarıdaki çizim kullanılır. Alanı boşaltırsanız fotoğraf kaldırılır.</small>
      </div>
      <div class="form-row">
        <label class="field"><span>Fotoğraf kaynağı (yazar, lisans)</span><input name="photo_credit" value="<?= e($edit['photo_credit'] ?? '') ?>"></label>
        <label class="field"><span>Kaynak bağlantısı</span><input name="photo_source" value="<?= e($edit['photo_source'] ?? '') ?>" placeholder="https://"></label>
      </div>
      <button class="btn btn--primary btn--block"><?= icon('check') ?> Kaydet</button>
      <?php if ($edit): ?><a href="<?= admin_url('pcategories') ?>" class="btn btn--block">Vazgeç</a><?php endif; ?>
    </form>
  </div>
</div>
