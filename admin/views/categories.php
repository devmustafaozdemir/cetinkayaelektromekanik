<div class="grid-2 grid-2--aside">
  <div class="card card--flush">
    <?php if ($rows): ?>
    <table class="table">
      <thead><tr><th>Kategori</th><th>Adres</th><th class="num">Yazı</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
        <tr>
          <td><strong><?= e($r['name']) ?></strong></td>
          <td class="muted">/blog/kategori/<?= e($r['slug']) ?></td>
          <td class="num"><?= (int)$r['cnt'] ?></td>
          <td class="actions">
            <a href="<?= admin_url('categories', ['id' => $r['id']]) ?>" class="icon-btn" title="Düzenle"><?= icon('edit') ?></a>
            <form method="post" action="<?= admin_url('categories', ['a' => 'delete', 'id' => $r['id']]) ?>" data-confirm="“<?= e($r['name']) ?>” kategorisi silinsin mi?"><?= csrf_field() ?><button class="icon-btn icon-btn--danger" title="Sil"><?= icon('trash') ?></button></form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?><div class="empty"><?= icon('tag') ?><h3>Kategori yok</h3></div><?php endif; ?>
  </div>
  <div class="card">
    <h3><?= $edit ? 'Kategoriyi düzenle' : 'Yeni kategori' ?></h3>
    <form method="post" action="<?= admin_url('categories', $edit ? ['id' => $edit['id']] : []) ?>" class="form-stack">
      <?= csrf_field() ?>
      <label class="field"><span>Ad</span><input name="name" value="<?= e($edit['name'] ?? '') ?>" required></label>
      <label class="field"><span>Adres (slug)</span><input name="slug" value="<?= e($edit['slug'] ?? '') ?>" placeholder="otomatik"></label>
      <button class="btn btn--primary btn--block"><?= icon('check') ?> Kaydet</button>
      <?php if ($edit): ?><a href="<?= admin_url('categories') ?>" class="btn btn--block">Vazgeç</a><?php endif; ?>
    </form>
  </div>
</div>
