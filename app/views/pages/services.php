<?php partial('page-hero', ['heading' => 'Hizmetler', 'lead' => 'Sattığımız ürünler için keşif, montaj, bakım ve sevkiyat desteği.', 'crumbs' => [[null, 'Hizmetler']]]); ?>
<section class="block">
  <div class="container">
    <div class="svc-grid">
      <?php foreach ($services as $s): ?>
        <a href="/hizmetler/<?= e($s['slug']) ?>" class="svc-card"><span class="svc-card__icon"><?= icon($s['icon']) ?></span><h3><?= e($s['title']) ?></h3><p><?= e($s['summary']) ?></p></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
