<ul class="brandline" aria-label="Sattığımız markalar">
  <?php foreach (brands() as $b): ?><li><a href="/urunler?marka=<?= rawurlencode($b['name']) ?>" title="<?= e($b['name']) ?> ürünleri"><?= brand_logo($b['name']) ?></a></li><?php endforeach; ?>
</ul>
