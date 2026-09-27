<?php partial('page-hero', ['heading' => 'Markalar', 'lead' => 'Sattığımız ve kurulumunu yaptığımız markalar. Bir markaya tıklayarak o markanın ürünlerini görebilirsiniz.', 'crumbs' => [[null, 'Markalar']]]); ?>
<section class="block">
  <div class="container">
    <ul class="brand-list">
      <?php foreach (brands() as $b): ?>
        <li><a href="/urunler?marka=<?= rawurlencode($b['name']) ?>"><strong><?= e($b['name']) ?></strong><span><?= e($b['desc']) ?></span><em><?= (int)($counts[$b['name']] ?? 0) ?> ürün</em></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
