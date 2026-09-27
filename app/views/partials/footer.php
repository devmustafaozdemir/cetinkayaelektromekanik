<?php
$footCats = q_all('SELECT name, slug FROM product_categories ORDER BY sort, id');
$socials = array_filter(['instagram' => setting('instagram'), 'facebook' => setting('facebook'), 'linkedin' => setting('linkedin'), 'youtube' => setting('youtube')]);
$hours = setting_lines('hours');
?>
<footer class="footer">
  <div class="container footer__cta">
    <div>
      <h2><?= e(setting('footer_cta_title')) ?></h2>
      <p><?= e(setting('footer_cta_text')) ?></p>
    </div>
    <div class="footer__cta-actions">
      <a href="/teklif-al" class="btn btn--signal btn--lg">Teklif iste</a>
      <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')), 'Merhaba, fiyat bilgisi almak istiyorum.')) ?>" class="btn btn--line-light btn--lg" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp'tan yazın</a>
    </div>
  </div>
  <div class="container footer__grid">
    <div class="footer__brand">
      <a href="/" class="logo logo--light" aria-label="<?= e(setting('site_name')) ?> ana sayfa"><?= site_logo(true) ?></a>
      <p><?= e(setting('site_tagline')) ?></p>
      <?php if ($socials): ?>
      <div class="socials">
        <?php foreach ($socials as $name => $link): ?><a href="<?= e($link) ?>" target="_blank" rel="noopener" aria-label="<?= e(ucfirst($name)) ?>"><?= icon($name) ?></a><?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
    <nav aria-label="Ürün grupları">
      <h3>Ürünler</h3>
      <ul>
        <?php foreach ($footCats as $c): ?><li><a href="/urunler/kategori/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a></li><?php endforeach; ?>
        <li><a href="/markalar">Markalar</a></li>
      </ul>
    </nav>
    <nav aria-label="Firma">
      <h3>Firma</h3>
      <ul>
        <li><a href="/hakkimizda">Hakkımızda</a></li>
        <li><a href="/referanslar">Referanslar</a></li>
        <li><a href="/cozum-ortaklari">Çözüm ortakları</a></li>
        <li><a href="/depo-tasarla">Depo tasarla</a></li>
        <li><a href="/hizmetler">Hizmetler</a></li>
        <li><a href="/blog">Blog</a></li>
        <li><a href="/sss">Sık sorulan sorular</a></li>
        <li><a href="/iletisim">İletişim</a></li>
      </ul>
    </nav>
    <div>
      <h3>Bize ulaşın</h3>
      <address class="footer__contact">
        <a href="<?= e(tel_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a>
        <?php if (setting('phone2')): ?><a href="<?= e(tel_href(setting('phone2'))) ?>"><?= e(setting('phone2')) ?></a><?php endif; ?>
        <a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a>
        <span><?= e(setting('address')) ?></span>
        <?php foreach ($hours as $h): ?><span class="footer__hours"><?= e($h) ?></span><?php endforeach; ?>
      </address>
    </div>
  </div>
  <div class="container footer__bottom">
    <span>© <?= date('Y') ?> <?= e(setting('site_name')) ?></span>
    <span><?= e(implode(', ', array_column(brands(), 'name'))) ?></span>
  </div>
</footer>
<a class="fab-call" href="<?= e(tel_href(setting('phone'))) ?>" aria-label="Bizi arayın: <?= e(setting('phone')) ?>"><?= icon('phone') ?></a>
<a class="fab-wa" href="<?= e(wa_href(setting('whatsapp', setting('phone2')), 'Merhaba, fiyat bilgisi almak istiyorum.')) ?>" target="_blank" rel="noopener" aria-label="WhatsApp ile yazın"><?= icon('whatsapp') ?></a>
