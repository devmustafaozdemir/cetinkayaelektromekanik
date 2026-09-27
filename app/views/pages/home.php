<?php
$stats = array_map(fn($l) => array_pad(explode('|', $l, 2), 2, ''), setting_lines('stats'));
$values = setting_lines('about_values');
?>
<section class="hero">
  <div class="hero__bg" aria-hidden="true"></div>
  <div class="container hero__inner">
    <div class="hero__content">
      <span class="badge badge--glow"><span class="pulse"></span><?= e(setting('hero_badge')) ?></span>
      <h1><?= e(setting('hero_title')) ?></h1>
      <p class="hero__lead"><?= e(setting('hero_text')) ?></p>
      <div class="hero__actions">
        <a href="/urunler" class="btn btn--primary btn--lg"><?= icon('grid') ?> Ürünleri İncele</a>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--outline-light btn--lg"><?= icon('phone') ?> Hemen Arayın</a>
      </div>
      <ul class="hero__trust">
        <li><?= icon('check-circle') ?> Orijinal ve garantili ürün</li>
        <li><?= icon('check-circle') ?> Projeye özel kapasite seçimi</li>
        <li><?= icon('check-circle') ?> Montaj ve servis desteği</li>
      </ul>
    </div>
    <div class="hero__card reveal">
      <div class="track-card">
        <div class="track-card__head">
          <span class="track-card__icon"><?= icon('calculator') ?></span>
          <div>
            <h2>Hızlı teklif alın</h2>
            <p>Bilgilerinizi bırakın, satış ekibimiz sizi arasın.</p>
          </div>
        </div>
        <form action="/teklif-al" method="post" class="track-card__form">
          <?= csrf_field() ?>
          <input type="hidden" name="quick" value="1">
          <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
          <label class="field">
            <span>Ad Soyad</span>
            <input type="text" name="name" required autocomplete="name" placeholder="Adınız Soyadınız">
          </label>
          <label class="field">
            <span>Telefon</span>
            <input type="tel" name="phone" required autocomplete="tel" placeholder="05xx xxx xx xx">
          </label>
          <label class="field">
            <span>İlgilendiğiniz ürün</span>
            <select name="category">
              <?php foreach ($categories as $c): ?><option><?= e($c['name']) ?></option><?php endforeach; ?>
              <option>Emin değilim, danışmak istiyorum</option>
            </select>
          </label>
          <button class="btn btn--primary btn--block"><?= icon('arrow-right') ?> Teklif İste</button>
        </form>
        <p class="track-card__note"><?= icon('shield') ?> Bilgileriniz yalnızca teklif için kullanılır.</p>
      </div>
    </div>
  </div>
  <div class="container">
    <?php partial('brand-logos'); ?>
  </div>
</section>

<?php if ($stats): ?>
<section class="stats">
  <div class="container stats__grid">
    <?php foreach ($stats as [$num, $label]): ?>
      <div class="stat reveal"><strong data-count="<?= e($num) ?>"><?= e($num) ?></strong><span><?= e($label) ?></span></div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Ürün Gruplarımız</span>
      <h2>Su depolama ve basınçlandırmada eksiksiz çözüm</h2>
      <p>Depodan pompaya, hidrofordan dalgıç pompaya kadar ihtiyacınız olan tüm ürünler tek adreste.</p>
    </div>
    <div class="cat-grid">
      <?php foreach ($categories as $c): ?>
        <a href="/urunler/kategori/<?= e($c['slug']) ?>" class="cat-card reveal">
          <span class="cat-card__art"><?= product_art($c['art']) ?></span>
          <span class="cat-card__body">
            <h3><?= e($c['name']) ?></h3>
            <p><?= e($c['summary']) ?></p>
            <span class="link-arrow"><?= (int)$c['cnt'] ?> ürün <?= icon('arrow-right') ?></span>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php if ($products): ?>
<section class="section section--muted">
  <div class="container">
    <div class="section-head section-head--row">
      <div>
        <span class="eyebrow">Öne Çıkan Ürünler</span>
        <h2>En çok tercih edilenler</h2>
      </div>
      <a href="/urunler" class="btn btn--ghost">Tüm ürünler <?= icon('arrow-right') ?></a>
    </div>
    <div class="products-grid">
      <?php foreach ($products as $p) partial('product-card', ['p' => $p]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Nasıl Çalışıyoruz?</span>
      <h2>İhtiyaçtan teslimata 4 adım</h2>
      <p>Doğru ürünü doğru kapasitede seçmeniz için sürecin her adımında yanınızdayız.</p>
    </div>
    <ol class="process">
      <li class="reveal"><span class="process__num">01</span><span class="process__icon"><?= icon('clipboard') ?></span><h3>İhtiyaç Analizi</h3><p>Kullanım amacını, tüketimi ve kurulum alanını birlikte değerlendiriyoruz.</p></li>
      <li class="reveal"><span class="process__num">02</span><span class="process__icon"><?= icon('ruler') ?></span><h3>Kapasite & Ürün Seçimi</h3><p>Depo hacmi, debi ve basınç hesabıyla en uygun ürünü belirliyoruz.</p></li>
      <li class="reveal"><span class="process__num">03</span><span class="process__icon"><?= icon('calculator') ?></span><h3>Şeffaf Teklif</h3><p>Ürün, sevkiyat ve montaj kalemleriyle net ve anlaşılır teklif sunuyoruz.</p></li>
      <li class="reveal"><span class="process__num">04</span><span class="process__icon"><?= icon('truck') ?></span><h3>Sevkiyat & Montaj</h3><p>Ürünlerinizi teslim ediyor, istenirse montaj ve devreye almayı yapıyoruz.</p></li>
    </ol>
  </div>
</section>

<section class="section section--muted">
  <div class="container split">
    <div class="split__visual reveal" aria-hidden="true">
      <div class="motor-art">
        <div class="art-stage"><?= product_art('tank') ?></div>
        <div class="float-badge float-badge--a"><?= icon('shield') ?><span><strong>Orijinal</strong> Ürün</span></div>
        <div class="float-badge float-badge--b"><?= icon('wrench') ?><span><strong>Montaj</strong> Desteği</span></div>
      </div>
    </div>
    <div class="split__content">
      <span class="eyebrow">Neden Çetinkaya?</span>
      <h2><?= e(setting('about_title')) ?></h2>
      <p><?= e(explode("\n", setting('about_text'))[0]) ?></p>
      <ul class="check-list">
        <?php foreach ($values as $v): ?><li><?= icon('check') ?><?= e($v) ?></li><?php endforeach; ?>
      </ul>
      <a href="/hakkimizda" class="btn btn--dark"><?= icon('arrow-right') ?> Bizi Tanıyın</a>
    </div>
  </div>
</section>

<?php if ($services): ?>
<section class="section">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Satış Sonrası</span>
      <h2>Satıştan sonra da yanınızdayız</h2>
      <p>Keşif, montaj ve bakım hizmetlerimizle sistemleriniz uzun yıllar sorunsuz çalışsın.</p>
    </div>
    <div class="services-grid services-grid--4">
      <?php foreach ($services as $s): ?>
        <a href="/hizmetler/<?= e($s['slug']) ?>" class="service-card reveal">
          <span class="service-card__icon"><?= icon($s['icon']) ?></span>
          <h3><?= e($s['title']) ?></h3>
          <p><?= e($s['summary']) ?></p>
          <span class="link-arrow">Detaylı bilgi <?= icon('arrow-right') ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if ($posts): ?>
<section class="section section--muted">
  <div class="container">
    <div class="section-head section-head--row">
      <div>
        <span class="eyebrow">Blog</span>
        <h2>Rehberler ve teknik bilgiler</h2>
      </div>
      <a href="/blog" class="btn btn--ghost">Tüm yazılar <?= icon('arrow-right') ?></a>
    </div>
    <div class="posts-grid">
      <?php foreach ($posts as $post) partial('post-card', ['post' => $post]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container faq-split">
    <div>
      <span class="eyebrow">Sıkça Sorulan Sorular</span>
      <h2>Aklınıza takılanlar</h2>
      <p class="muted">Aradığınız cevabı bulamadıysanız bizi arayın ya da WhatsApp'tan yazın.</p>
      <div class="contact-mini">
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="contact-mini__item"><?= icon('phone') ?><span><small>Telefon</small><?= e(setting('phone')) ?></span></a>
        <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')))) ?>" target="_blank" rel="noopener" class="contact-mini__item"><?= icon('whatsapp') ?><span><small>WhatsApp</small><?= e(setting('whatsapp', setting('phone2'))) ?></span></a>
        <a href="<?= e(setting('map_link')) ?>" target="_blank" rel="noopener" class="contact-mini__item"><?= icon('map-pin') ?><span><small>Adres</small><?= e(setting('address_short', 'Kocaeli')) ?></span></a>
      </div>
    </div>
    <div>
      <?php partial('faq-list', ['faqs' => $faqs]); ?>
      <a href="/sss" class="link-arrow mt-2">Tüm soruları görün <?= icon('arrow-right') ?></a>
    </div>
  </div>
</section>
