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
    <div class="section-head"><span class="eyebrow">Yetkili Servis</span><h2>Güvendiğiniz markaların yetkili servisi</h2></div>
    <?php partial('brand-logos'); ?>
  </div>
</section>
<section class="section">
  <div class="container">
    <div class="features">
      <div class="feature reveal"><span class="feature__icon"><?= icon('award') ?></span><h3>Uzman Kadro</h3><p>Üretici eğitimlerinden geçmiş, deneyimli teknik ekip.</p></div>
      <div class="feature reveal"><span class="feature__icon"><?= icon('gauge') ?></span><h3>Test Donanımı</h3><p>Onarım sonrası yük, izolasyon ve güvenlik testleri.</p></div>
      <div class="feature reveal"><span class="feature__icon"><?= icon('package') ?></span><h3>Parça Stoğu</h3><p>Sık kullanılan orijinal parçalarda hazır stok.</p></div>
      <div class="feature reveal"><span class="feature__icon"><?= icon('users') ?></span><h3>Kurumsal Destek</h3><p>Sanayi kuruluşlarına özel planlı bakım ve öncelikli servis.</p></div>
    </div>
  </div>
</section>
