<div class="settings">
  <nav class="settings__nav">
    <?php foreach ($groups as $k => [$label, $ic]): ?>
      <a href="<?= admin_url('settings', ['tab' => $k]) ?>" class="<?= $tab === $k ? 'is-active' : '' ?>"><?= icon($ic) ?> <?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
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
