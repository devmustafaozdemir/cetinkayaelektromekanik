<?php
$services = q_all('SELECT title, slug FROM services WHERE active = 1 ORDER BY sort, id LIMIT 6');
$socials = array_filter(['instagram' => setting('instagram'), 'facebook' => setting('facebook'), 'linkedin' => setting('linkedin'), 'youtube' => setting('youtube')]);
?>
<section class="cta-band">
  <div class="container cta-band__inner">
    <div>
      <h2>Cihazınız mı arızalandı?</h2>
      <p>Online servis talebi oluşturun, takip kodunuzla süreci adım adım izleyin.</p>
    </div>
    <div class="cta-band__actions">
      <a href="/servis-talebi" class="btn btn--primary btn--lg"><?= icon('clipboard') ?> Servis Talebi Oluştur</a>
      <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')), 'Merhaba, servis hakkında bilgi almak istiyorum.')) ?>" class="btn btn--light btn--lg" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp</a>
    </div>
  </div>
</section>
<footer class="footer">
  <div class="container footer__grid">
    <div class="footer__brand">
      <a href="/" class="logo logo--light">
        <span class="logo__mark"><?= icon('zap') ?></span>
        <span class="logo__text"><strong>Çetinkaya</strong><small>Elektromekanik</small></span>
      </a>
      <p><?= e(setting('site_tagline')) ?>. Profesyonel el aletleri onarımı, motor bobinajı ve orijinal yedek parça.</p>
      <?php if ($socials): ?>
      <div class="socials">
        <?php foreach ($socials as $name => $link): ?>
          <a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($name)) ?>"><?= icon($name) ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div>
      <h3>Hizmetler</h3>
      <ul>
        <?php foreach ($services as $s): ?>
          <li><a href="/hizmetler/<?= e($s['slug']) ?>"><?= e($s['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div>
      <h3>Kurumsal</h3>
      <ul>
        <li><a href="/hakkimizda">Hakkımızda</a></li>
        <li><a href="/blog">Blog</a></li>
        <li><a href="/sss">Sıkça Sorulan Sorular</a></li>
        <li><a href="/servis-takip">Servis Takip</a></li>
        <li><a href="/iletisim">İletişim</a></li>
      </ul>
    </div>
    <div>
      <h3>İletişim</h3>
      <ul class="footer__contact">
        <li><?= icon('map-pin') ?><span><?= e(setting('address')) ?></span></li>
        <li><?= icon('phone') ?><span><a href="<?= e(tel_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a><?php if (setting('phone2')): ?><br><a href="<?= e(tel_href(setting('phone2'))) ?>"><?= e(setting('phone2')) ?></a><?php endif; ?></span></li>
        <li><?= icon('mail') ?><span><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></span></li>
      </ul>
    </div>
  </div>
  <div class="container footer__bottom">
    <span>© <?= date('Y') ?> <?= e(setting('site_name')) ?>. Tüm hakları saklıdır.</span>
    <span class="footer__brands"><?php foreach (setting_lines('brands') as $b): ?><span><?= e($b) ?></span><?php endforeach; ?> Yetkili Servisi</span>
  </div>
</footer>
<a class="fab-wa" href="<?= e(wa_href(setting('whatsapp', setting('phone2')), 'Merhaba, servis hakkında bilgi almak istiyorum.')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp ile yazın"><?= icon('whatsapp') ?></a>
