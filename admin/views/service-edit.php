<a href="<?= admin_url('services') ?>" class="back-link"><?= icon('arrow-left') ?> Hizmetler</a>
<form method="post" class="editor-layout" id="post-form">
  <?= csrf_field() ?>
  <div class="editor-main">
    <div class="card">
      <label class="field<?= isset($errors['title']) ? ' has-error' : '' ?>"><input type="text" name="title" value="<?= e($row['title']) ?>" placeholder="Hizmet adı" class="input-title" required data-slug-source><?php if (isset($errors['title'])): ?><em><?= e($errors['title']) ?></em><?php endif; ?></label>
      <div class="slug-row"><span><?= e(base_url()) ?>/hizmetler/</span><input type="text" name="slug" value="<?= e($row['slug']) ?>" placeholder="otomatik-olusturulur" data-slug-target></div>
      <label class="field"><span>Kısa açıklama <small class="muted">(kartlarda görünür)</small></span><textarea name="summary" rows="2" maxlength="400" data-counter><?= e($row['summary']) ?></textarea></label>
      <div class="field"><span>Detay içeriği</span><div id="editor" class="editor"><?= $row['content'] ?></div><input type="hidden" name="content" id="content-input"></div>
    </div>
  </div>
  <aside class="editor-side">
    <div class="card sticky">
      <h3>Ayarlar</h3>
      <label class="switch"><input type="checkbox" name="active" value="1"<?= $row['active'] ? ' checked' : '' ?>><span class="switch__ui"></span><span>Sitede göster</span></label>
      <div class="field"><span>İkon</span>
        <div class="icon-picker">
          <?php foreach (service_icons() as $ic): ?>
            <label title="<?= e($ic) ?>"><input type="radio" name="icon" value="<?= $ic ?>"<?= $row['icon'] === $ic ? ' checked' : '' ?>><span><?= icon($ic) ?></span></label>
          <?php endforeach; ?>
        </div>
      </div>
      <button class="btn btn--primary btn--block"><?= icon('check') ?> Kaydet</button>
    </div>
  </aside>
</form>
