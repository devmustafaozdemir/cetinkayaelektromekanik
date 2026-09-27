<ul class="brandline" aria-label="Sattığımız markalar">
  <?php foreach (brands() as $b): ?><li><a href="/urunler?marka=<?= rawurlencode($b['name']) ?>"><?= e($b['name']) ?></a></li><?php endforeach; ?>
</ul>
