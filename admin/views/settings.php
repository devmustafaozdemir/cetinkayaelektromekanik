<div class="settings">
  <nav class="settings__nav">
    <?php foreach ($groups as $k => [$label, $ic]): ?>
      <a href="<?= admin_url('settings', ['tab' => $k]) ?>" class="<?= $tab === $k ? 'is-active' : '' ?>"><?= icon($ic) ?> <?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <div>
  <?php if ($tab === 'general'): ?>
  <form method="post" enctype="multipart/form-data" class="card form-stack">
    <?= csrf_field() ?>
    <input type="hidden" name="logo_action" value="1">
    <h3>Logo</h3>
    <p class="muted small">Yüklemezseniz sitede yerleşik logo çizimi kullanılır. Şeffaf arka planlı PNG önerilir; koyu alt bilgi için açık renkli ikinci bir sürüm yükleyebilirsiniz.</p>
    <div class="form-row">
      <?php foreach (['logo' => 'Logo (açık zemin)', 'logo_light' => 'Logo (koyu zemin, opsiyonel)'] as $k => $l): ?>
        <div class="field"><span><?= $l ?></span>
          <label class="dropzone dropzone--sm<?= $k === 'logo_light' ? ' dropzone--dark' : '' ?>" data-dropzone>
            <input type="file" name="<?= $k ?>" accept="image/png,image/webp,image/jpeg">
            <img src="<?= e(upload_url($values[$k] ?? '')) ?>" alt="" data-preview<?= !empty($values[$k]) ? '' : ' hidden' ?> style="object-fit:contain">
            <span class="dropzone__hint"<?= !empty($values[$k]) ? ' hidden' : '' ?>><?= icon('plus') ?> Dosya seçin</span>
          </label>
          <?php if (!empty($values[$k])): ?><label class="check"><input type="checkbox" name="remove_<?= $k ?>" value="1"> Kaldır</label><?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div><button class="btn btn--primary"><?= icon('check') ?> Logoyu Kaydet</button></div>
  </form>
  <?php endif; ?>
  <form method="post" class="card form-stack">
    <?= csrf_field() ?>
    <h3><?= e($groups[$tab][0]) ?></h3>
    <?php foreach ($groups[$tab][2] as $key => $def): [$label, $type] = $def; $help = $def[2] ?? ''; ?>
      <label class="field">
        <span><?= e($label) ?></span>
        <?php if ($type === 'textarea'): ?>
          <textarea name="<?= $key ?>" rows="<?= in_array($key, ['about_text'], true) ? 8 : 3 ?>"><?= e($values[$key] ?? '') ?></textarea>
        <?php else: ?>
          <input type="<?= $type ?>" name="<?= $key ?>" value="<?= e($values[$key] ?? '') ?>">
        <?php endif; ?>
        <?php if ($help): ?><small class="muted"><?= e($help) ?></small><?php endif; ?>
      </label>
    <?php endforeach; ?>
    <div><button class="btn btn--primary"><?= icon('check') ?> Değişiklikleri Kaydet</button></div>
  </form>
  </div>
</div>
