<?php
$values = setting_lines('about_values');
$stats = array_map(fn($l) => array_pad(explode('|', $l, 2), 2, ''), setting_lines('stats'));
$statIcons = ['users', 'truck', 'layers', 'award', 'check-circle', 'star'];
$tints = ['tank' => 'sky', 'booster' => 'navy', 'pump' => 'mist', 'submersible' => 'sea', 'drop' => 'sky'];
?>
<section class="hero">
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container hero__inner">
    <div class="hero__copy">
      <span class="pill"><?= icon('droplet') ?> <?= e(setting('hero_badge')) ?></span>
      <h1><?= e(setting('hero_title')) ?></h1>
      <p class="hero__text"><?= e(setting('hero_text')) ?></p>
      <div class="hero__actions">
        <a href="/urunler" class="btn btn--signal btn--lg">Ürünleri incele <?= icon('arrow-right') ?></a>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--line btn--lg"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
      </div>
      <ul class="hero__trust">
        <li><?= icon('check-circle') ?> Orijinal ve garantili ürün</li>
        <li><?= icon('check-circle') ?> Ücretsiz kapasite hesabı</li>
        <li><?= icon('check-circle') ?> Montaj ve servis desteği</li>
      </ul>
    </div>

    <?php partial('designer', ['mode' => 'compact']); ?>
  </div>
  <div class="container">
    <div class="logo-strip">
      <span class="logo-strip__label">Sattığımız markalar</span>
      <?php partial('brand-logos'); ?>
    </div>
  </div>
</section>

<?php if ($stats): ?>
<section class="proof">
  <div class="container">
    <div class="proof__card">
      <div class="proof__intro">
        <h2><?= e(setting('stats_title', 'Sahada kanıtlanmış çözümler')) ?></h2>
        <p><?= e(setting('stats_text')) ?></p>
      </div>
      <ul class="proof__stats">
        <?php foreach ($stats as $i => [$num, $label]): ?>
          <li><span class="proof__icon"><?= icon($statIcons[$i % count($statIcons)]) ?></span><strong data-count="<?= e($num) ?>"><?= e($num) ?></strong><span><?= e($label) ?></span></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="block">
  <div class="container">
    <div class="block__head block__head--center">
      <span class="kicker">Ürün grupları</span>
      <h2>Su tesisatının her halkası için</h2>
      <p>Depolamadan basınçlandırmaya, kuyudan drenaja kadar ihtiyacınız olan ürünler tek adreste.</p>
    </div>
    <div class="cat-grid">
      <?php foreach ($categories as $c): ?>
        <a href="/urunler/kategori/<?= e($c['slug']) ?>" class="cat-card cat-card--<?= $tints[$c['art']] ?? 'sky' ?>">
          <span class="cat-card__art"><?= category_photo($c, false) ?></span>
          <span class="cat-card__body">
            <h3><?= e($c['name']) ?></h3>
            <p><?= e($c['summary']) ?></p>
            <span class="cat-card__more"><?= (int)$c['cnt'] ?> ürün <?= icon('arrow-right') ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($products): ?>
<section class="block block--soft">
  <div class="container">
    <div class="block__head block__head--row">
      <div><span class="kicker">Öne çıkanlar</span><h2>Sık teklif istenen ürünler</h2></div>
      <a href="/urunler" class="btn btn--line">Tüm ürünler <?= icon('arrow-right') ?></a>
    </div>
    <div class="product-grid">
      <?php foreach ($products as $p) partial('product-card', ['p' => $p]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="block">
  <div class="container">
    <div class="block__head block__head--center">
      <span class="kicker">Nasıl çalışıyoruz?</span>
      <h2>Talebinizden teslimata 4 adım</h2>
    </div>
    <ol class="steps">
      <li><span class="steps__icon"><?= icon('clipboard') ?></span><h3>İhtiyacı konuşuruz</h3><p>Kullanım amacı, daire sayısı, kurulum alanı ve mevcut tesisat.</p></li>
      <li><span class="steps__icon"><?= icon('ruler') ?></span><h3>Kapasiteyi hesaplarız</h3><p>Depo hacmi, debi ve basma yüksekliğine göre doğru ürünü seçeriz.</p></li>
      <li><span class="steps__icon"><?= icon('calculator') ?></span><h3>Teklifi göndeririz</h3><p>Ürün, sevkiyat ve montaj kalemleri ayrı ayrı, yazılı olarak.</p></li>
      <li><span class="steps__icon"><?= icon('truck') ?></span><h3>Teslim eder, kurarız</h3><p>İsterseniz montajı ve devreye almayı da ekibimiz yapar.</p></li>
    </ol>
  </div>
</section>

<section class="block block--soft">
  <div class="container why">
    <div class="why__art" aria-hidden="true">
      <div class="why__stage"><?= model_svg('tank:paslanmaz', 'art', ['w' => 4, 'l' => 3, 'h' => 2]) ?></div>
      <div class="why__badge why__badge--a"><?= icon('shield') ?><span><strong>Orijinal</strong>garantili ürün</span></div>
      <div class="why__badge why__badge--b"><?= icon('wrench') ?><span><strong>Montaj</strong>ve servis</span></div>
    </div>
    <div>
      <span class="kicker">Neden Çetinkaya?</span>
      <h2><?= e(setting('about_title')) ?></h2>
      <p class="muted"><?= e(explode("\n", setting('about_text'))[0]) ?></p>
      <ul class="ticks"><?php foreach ($values as $v): ?><li><?= e($v) ?></li><?php endforeach; ?></ul>
      <a href="/hakkimizda" class="btn btn--navy">Bizi tanıyın <?= icon('arrow-right') ?></a>
    </div>
  </div>
</section>

<?php if ($services): ?>
<section class="block">
  <div class="container">
    <div class="block__head block__head--center">
      <span class="kicker">Satış sonrası</span>
      <h2>Satıştan sonra da yanınızdayız</h2>
    </div>
    <div class="svc-grid">
      <?php foreach ($services as $s): ?>
        <a href="/hizmetler/<?= e($s['slug']) ?>" class="svc-card"><span class="svc-card__icon"><?= icon($s['icon']) ?></span><h3><?= e($s['title']) ?></h3><p><?= e($s['summary']) ?></p></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($posts): ?>
<section class="block block--soft">
  <div class="container">
    <div class="block__head block__head--row">
      <div><span class="kicker">Blog</span><h2>Rehberler ve teknik bilgiler</h2></div>
      <a href="/blog" class="btn btn--line">Bütün yazılar <?= icon('arrow-right') ?></a>
    </div>
    <div class="post-grid">
      <?php foreach ($posts as $post) partial('post-card', ['post' => $post]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="block">
  <div class="container duo">
    <div>
      <span class="kicker">Sık sorulan sorular</span>
      <h2>Aklınıza takılanlar</h2>
      <p class="muted">Cevabını bulamadığınız bir soru varsa arayın ya da WhatsApp'tan yazın.</p>
      <div class="contact-chips">
        <a href="<?= e(tel_href(setting('phone'))) ?>"><?= icon('phone') ?><span><small>Telefon</small><?= e(setting('phone')) ?></span></a>
        <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')))) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?><span><small>WhatsApp</small><?= e(setting('whatsapp', setting('phone2'))) ?></span></a>
      </div>
    </div>
    <div>
      <?php partial('faq-list', ['faqs' => $faqs]); ?>
      <p class="mt"><a href="/sss" class="text-link">Bütün sorular</a></p>
    </div>
  </div>
</section>
