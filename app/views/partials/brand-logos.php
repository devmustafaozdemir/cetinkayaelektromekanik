<ul class="brand-row">
  <?php foreach (setting_lines('brands') as $b): ?>
    <li class="brand-row__item brand--<?= e(slugify($b)) ?>"><span><?= e($b) ?></span><small>Yetkili Servis</small></li>
  <?php endforeach; ?>
</ul>
