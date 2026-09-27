<div class="grid-2 grid-2--aside">
  <div>
    <p class="muted mb">Sürükleyerek sıralayın. İlk 5 soru ana sayfada gösterilir.</p>
    <ul class="sortable" data-sortable="<?= admin_url('faqs', ['a' => 'sort']) ?>">
      <?php foreach ($rows as $r): ?>
        <li class="sortable__item card" draggable="true" data-id="<?= $r['id'] ?>">
          <span class="drag-handle">⋮⋮</span>
          <div class="sortable__body"><strong><?= e($r['question']) ?></strong><small class="muted"><?= e(excerpt($r['answer'], 120)) ?></small></div>
          <?= $r['active'] ? '' : '<span class="status status--slate">Pasif</span>' ?>
          <div class="actions">
            <a href="<?= admin_url('faqs', ['id' => $r['id']]) ?>" class="icon-btn" title="Düzenle"><?= icon('edit') ?></a>
            <form method="post" action="<?= admin_url('faqs', ['a' => 'delete', 'id' => $r['id']]) ?>" data-confirm="Bu soru silinsin mi?"><?= csrf_field() ?><button class="icon-btn icon-btn--danger" title="Sil"><?= icon('trash') ?></button></form>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    <?php if (!$rows): ?><div class="card empty"><?= icon('help') ?><h3>Henüz soru yok</h3></div><?php endif; ?>
  </div>
  <div class="card sticky">
    <h3><?= $edit ? 'Soruyu düzenle' : 'Yeni soru' ?></h3>
    <form method="post" action="<?= admin_url('faqs', $edit ? ['id' => $edit['id']] : []) ?>" class="form-stack">
      <?= csrf_field() ?>
      <label class="field"><span>Soru</span><input name="question" value="<?= e($edit['question'] ?? '') ?>" required></label>
      <label class="field"><span>Cevap</span><textarea name="answer" rows="6" required><?= e($edit['answer'] ?? '') ?></textarea></label>
      <label class="switch"><input type="checkbox" name="active" value="1"<?= ($edit['active'] ?? 1) ? ' checked' : '' ?>><span class="switch__ui"></span><span>Sitede göster</span></label>
      <button class="btn btn--primary btn--block"><?= icon('check') ?> Kaydet</button>
      <?php if ($edit): ?><a href="<?= admin_url('faqs') ?>" class="btn btn--block">Vazgeç</a><?php endif; ?>
    </form>
  </div>
</div>
