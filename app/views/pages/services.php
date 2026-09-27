<?php partial('page-hero', ['heading' => 'Hizmetler', 'lead' => 'Sattığımız ürünler için keşif, montaj, bakım ve sevkiyat desteği.', 'crumbs' => [[null, 'Hizmetler']]]); ?>
<section class="block">
  <div class="container">
    <ul class="svc-list">
      <?php foreach ($services as $s): ?>
        <li><a href="/hizmetler/<?= e($s['slug']) ?>"><?= icon($s['icon']) ?><h3><?= e($s['title']) ?></h3><p><?= e($s['summary']) ?></p></a></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
