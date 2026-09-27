<ul class="brand-row">
  <?php foreach (brands() as $b): ?>
    <li class="brand-row__item"><a href="/urunler?marka=<?= rawurlencode($b['name']) ?>"><span><?= e($b['name']) ?></span><small><?= e($b['desc'] ?: 'Çözüm ortağı') ?></small></a></li>
  <?php endforeach; ?>
</ul>
