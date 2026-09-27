<?php partial('page-hero', ['heading' => $service['title'], 'lead' => $service['summary'], 'crumbs' => [['/hizmetler', 'Hizmetler'], [null, $service['title']]]]); ?>
<section class="block block--tight">
  <div class="container pd__body">
    <article class="prose">
      <?= $service['content'] ?>
      <div class="callout">
        <p><strong>Bu hizmet için görüşelim</strong>Kısa bir form doldurun, ekibimiz sizi arasın.</p>
        <a href="/teklif-al" class="btn btn--signal">Talep oluştur</a>
      </div>
    </article>
    <aside class="aside-box">
      <h2>Diğer hizmetler</h2>
      <ul class="aside-links">
        <?php foreach ($others as $o): ?><li><a href="/hizmetler/<?= e($o['slug']) ?>"><?= e($o['title']) ?></a></li><?php endforeach; ?>
      </ul>
      <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--line-light btn--block"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
    </aside>
  </div>
</section>
