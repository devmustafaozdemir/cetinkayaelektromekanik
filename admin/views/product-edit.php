<a href="<?= admin_url('products') ?>" class="back-link"><?= icon('arrow-left') ?> Ürünler</a>
<form method="post" enctype="multipart/form-data" class="editor-layout" id="post-form">
  <?= csrf_field() ?>
  <div class="editor-main">
    <div class="card">
      <label class="field<?= isset($errors['title']) ? ' has-error' : '' ?>"><input type="text" name="title" value="<?= e($row['title']) ?>" placeholder="Ürün adı" class="input-title" required data-slug-source><?php if (isset($errors['title'])): ?><em><?= e($errors['title']) ?></em><?php endif; ?></label>
      <div class="slug-row"><span><?= e(base_url()) ?>/urunler/</span><input type="text" name="slug" value="<?= e($row['slug']) ?>" placeholder="otomatik-olusturulur" data-slug-target></div>
      <label class="field"><span>Kısa açıklama <small class="muted">(ürün kartında ve Google'da görünür)</small></span><textarea name="summary" rows="2" maxlength="400" data-counter="160"><?= e($row['summary']) ?></textarea></label>
      <div class="field"><span>Ürün açıklaması</span><div id="editor" class="editor"><?= $row['content'] ?></div><input type="hidden" name="content" id="content-input"></div>
    </div>
    <div class="card">
      <h3>Teknik özellikler</h3>
      <p class="muted small">Her satıra bir özellik yazın: <code>Özellik | Değer</code>. İlk 4 satır ürün sayfasının üst kısmında öne çıkarılır.</p>
      <label class="field"><textarea name="specs" rows="8" class="mono-input" placeholder="Marka | Grundfos&#10;Debi | 10 m³/saat&#10;Basma yüksekliği | 60 m&#10;Motor gücü | 2,2 kW"><?= e($row['specs']) ?></textarea></label>
    </div>
  </div>
  <aside class="editor-side">
    <div class="card sticky">
      <h3>Yayın</h3>
      <label class="switch"><input type="checkbox" name="active" value="1"<?= $row['active'] ? ' checked' : '' ?>><span class="switch__ui"></span><span>Sitede göster</span></label>
      <label class="switch"><input type="checkbox" name="featured" value="1"<?= $row['featured'] ? ' checked' : '' ?>><span class="switch__ui"></span><span>Ana sayfada öne çıkar</span></label>
      <label class="field"><span>Kategori</span>
        <select name="category_id">
          <option value="">— Kategorisiz —</option>
          <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"<?= (int)$row['category_id'] === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </label>
      <label class="field"><span>Marka</span><input type="text" name="brand" value="<?= e($row['brand']) ?>" list="brand-list" placeholder="Örn. Grundfos"></label>
      <datalist id="brand-list"><?php foreach ($brands as $b): ?><option value="<?= e($b) ?>"><?php endforeach; ?></datalist>
      <div class="btn-stack">
        <button class="btn btn--primary btn--block"><?= icon('check') ?> Kaydet</button>
        <?php if ($id): ?><a href="/urunler/<?= e($row['slug']) ?>" target="_blank" class="btn btn--block"><?= icon('eye') ?> Sitede görüntüle</a><?php endif; ?>
      </div>
      <?php if ($id): ?><p class="muted small"><?= icon('calculator') ?> Bu ürün için <?= $quoteCount ?> teklif talebi alındı.</p><?php endif; ?>
    </div>
    <div class="card">
      <h3>Ürün görseli</h3>
      <label class="dropzone<?= isset($errors['image']) ? ' has-error' : '' ?>" data-dropzone>
        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif">
        <img src="<?= e(upload_url($row['image'])) ?>" alt="" data-preview<?= $row['image'] ? '' : ' hidden' ?>>
        <span class="dropzone__hint"<?= $row['image'] ? ' hidden' : '' ?>><?= icon('plus') ?> Görsel seçin veya sürükleyin<small>Beyaz/açık arka planlı kare görseller en iyi sonucu verir.<br>Görsel yoksa kategori çizimi kullanılır.</small></span>
      </label>
      <?php if (isset($errors['image'])): ?><em class="error"><?= e($errors['image']) ?></em><?php endif; ?>
      <?php if ($row['image']): ?><label class="check"><input type="checkbox" name="remove_image" value="1"> Görseli kaldır</label><?php endif; ?>
    </div>
    <?php if ($id): ?>
    <div class="card card--danger"><button type="submit" form="delete-form" class="btn btn--danger-ghost btn--block"><?= icon('trash') ?> Ürünü sil</button></div>
    <?php endif; ?>
  </aside>
</form>
<?php if ($id): ?><form id="delete-form" method="post" action="<?= admin_url('products', ['a' => 'delete', 'id' => $id]) ?>" data-confirm="Bu ürün kalıcı olarak silinsin mi?"><?= csrf_field() ?></form><?php endif; ?>
