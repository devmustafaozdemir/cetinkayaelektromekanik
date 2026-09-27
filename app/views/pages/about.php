<?php
$paras = array_filter(array_map('trim', preg_split('/\R{2,}/', setting('about_text'))));
$stats = array_map(fn($l) => array_pad(explode('|', $l, 2), 2, ''), setting_lines('stats'));
?>
<?php partial('page-hero', ['heading' => 'Hakkımızda', 'lead' => setting('site_tagline'), 'crumbs' => [[null, 'Hakkımızda']]]); ?>
<section class="section">
  <div class="container split split--top">
    <div class="split__content">
      <span class="eyebrow">Biz Kimiz?</span>
      <h2><?= e(setting('about_title')) ?></h2>
      <?php foreach ($paras as $p): ?><p><?= nl2br(e($p)) ?></p><?php endforeach; ?>
    </div>
    <div class="values-card reveal">
      <h3>Değerlerimiz</h3>
      <ul class="check-list">
        <?php foreach (setting_lines('about_values') as $v): ?><li><?= icon('check') ?><?= e($v) ?></li><?php endforeach; ?>
      </ul>
      <div class="values-card__stats">
        <?php foreach (array_slice($stats, 0, 2) as [$n, $l]): ?><div><strong><?= e($n) ?></strong><span><?= e($l) ?></span></div><?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
<section class="section section--muted">
  <div class="container">
    <div class="section-head"><span class="eyebrow">Markalarımız</span><h2>Güvendiğiniz markaların ürünleri</h2></div>
    <?php partial('brand-logos'); ?>
  </div>
</section>
<section class="section">
  <div class="container">
    <div class="features">
      <div class="feature reveal"><span class="feature__icon"><?= icon('ruler') ?></span><h3>Doğru Kapasite</h3><p>Depo hacmi, debi ve basınç hesabıyla ihtiyaca uygun seçim.</p></div>
      <div class="feature reveal"><span class="feature__icon"><?= icon('award') ?></span><h3>Güçlü Markalar</h3><p>Meksis, Grundfos, Wilo, Standart Pompa ve Sumak ürünleri.</p></div>
      <div class="feature reveal"><span class="feature__icon"><?= icon('truck') ?></span><h3>Hızlı Teslimat</h3><p>Stok ürünlerde hızlı, proje ürünlerinde planlı sevkiyat.</p></div>
      <div class="feature reveal"><span class="feature__icon"><?= icon('users') ?></span><h3>Satış Sonrası Destek</h3><p>Keşif, montaj, devreye alma ve periyodik bakım desteği.</p></div>
    </div>
  </div>
</section>
