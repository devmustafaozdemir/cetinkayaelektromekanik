<?php partial('page-hero', ['heading' => $service['title'], 'lead' => $service['summary'], 'crumbs' => [['/hizmetler', 'Hizmetler'], [null, $service['title']]]]); ?>
<section class="section">
  <div class="container layout-aside">
    <article class="prose">
      <?= $service['content'] ?>
      <div class="inline-cta">
        <span class="inline-cta__icon"><?= icon($service['icon']) ?></span>
        <div><strong>Bu hizmet için servis talebi oluşturun</strong><p>Formu doldurun, takip kodunuzla süreci online izleyin.</p></div>
        <a href="/servis-talebi" class="btn btn--primary">Talep Oluştur</a>
      </div>
    </article>
    <aside class="aside">
      <div class="aside-card">
        <h3>Diğer Hizmetler</h3>
        <ul class="aside-links">
          <?php foreach ($others as $o): ?>
            <li><a href="/hizmetler/<?= e($o['slug']) ?>"><?= icon($o['icon']) ?><span><?= e($o['title']) ?></span><?= icon('chevron-right', 'icon icon--end') ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="aside-card aside-card--dark">
        <h3>Hemen ulaşın</h3>
        <p>Sorularınız için uzman ekibimizle görüşün.</p>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--primary btn--block"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
        <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')), 'Merhaba, ' . $service['title'] . ' hakkında bilgi almak istiyorum.')) ?>" class="btn btn--outline-light btn--block" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp</a>
      </div>
    </aside>
  </div>
</section>
