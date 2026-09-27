<?php
$footCats = q_all('SELECT name, slug FROM product_categories ORDER BY sort, id');
$socials = array_filter(['instagram' => setting('instagram'), 'facebook' => setting('facebook'), 'linkedin' => setting('linkedin'), 'youtube' => setting('youtube')]);
?>
<section class="cta-band">
  <div class="container cta-band__inner">
    <div>
      <h2>Projeniz için fiyat mı almak istiyorsunuz?</h2>
      <p>İhtiyacınızı paylaşın, size en uygun ürün ve fiyatla hızlıca dönüş yapalım.</p>
    </div>
    <div class="cta-band__actions">
      <a href="/teklif-al" class="btn btn--primary btn--lg"><?= icon('calculator') ?> Hemen Teklif Al</a>
      <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')), 'Merhaba, ürünleriniz hakkında fiyat bilgisi almak istiyorum.')) ?>" class="btn btn--light btn--lg" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp</a>
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
      <p><?= e(setting('site_tagline')) ?>. Keşif, teklif, sevkiyat ve montajda yanınızdayız.</p>
      <?php if ($socials): ?>
      <div class="socials">
        <?php foreach ($socials as $name => $link): ?>
          <a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($name)) ?>"><?= icon($name) ?></a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <div>
      <h3>Ürünler</h3>
      <ul>
        <?php foreach ($footCats as $c): ?>
          <li><a href="/urunler/kategori/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="/markalar">Markalar</a></li>
      </ul>
    </div>
    <div>
      <h3>Kurumsal</h3>
      <ul>
        <li><a href="/hakkimizda">Hakkımızda</a></li>
        <li><a href="/blog">Blog</a></li>
        <li><a href="/sss">Sıkça Sorulan Sorular</a></li>
        <li><a href="/hizmetler">Hizmetler</a></li>
        <li><a href="/teklif-al">Teklif Al</a></li>
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
    <span class="footer__brands"><?php foreach (brands() as $b): ?><span><?= e($b['name']) ?></span><?php endforeach; ?></span>
  </div>
</footer>
<a class="fab-wa" href="<?= e(wa_href(setting('whatsapp', setting('phone2')), 'Merhaba, ürünleriniz hakkında fiyat bilgisi almak istiyorum.')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp ile yazın"><?= icon('whatsapp') ?></a>
