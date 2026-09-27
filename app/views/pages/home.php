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
        <a href="/servis-talebi" class="btn btn--primary btn--lg"><?= icon('clipboard') ?> Online Servis Talebi</a>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--outline-light btn--lg"><?= icon('phone') ?> Hemen Arayın</a>
      </div>
      <ul class="hero__trust">
        <li><?= icon('check-circle') ?> Orijinal yedek parça</li>
        <li><?= icon('check-circle') ?> Onayınız olmadan işlem yok</li>
        <li><?= icon('check-circle') ?> Online durum takibi</li>
      </ul>
    </div>
    <div class="hero__card reveal">
      <div class="track-card">
        <div class="track-card__head">
          <span class="track-card__icon"><?= icon('search') ?></span>
          <div>
            <h2>Cihazınız ne durumda?</h2>
            <p>Takip kodunuzla servis sürecini anlık görün.</p>
          </div>
        </div>
        <form action="/servis-takip" method="get" class="track-card__form">
          <label class="field">
            <span>Takip Kodu</span>
            <input type="text" name="kod" placeholder="CE-26XXXXX" required autocomplete="off" style="text-transform:uppercase">
          </label>
          <label class="field">
            <span>Telefonunuzun son 4 hanesi</span>
            <input type="text" name="telefon" inputmode="numeric" placeholder="1320" required maxlength="20">
          </label>
          <button class="btn btn--primary btn--block"><?= icon('search') ?> Sorgula</button>
        </form>
        <ol class="track-card__steps" aria-hidden="true">
          <li class="done"><span></span>Talep</li>
          <li class="done"><span></span>Tespit</li>
          <li class="current"><span></span>Onarım</li>
          <li><span></span>Teslim</li>
        </ol>
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
      <span class="eyebrow">Hizmetlerimiz</span>
      <h2>Arızadan teslimata, uçtan uca servis</h2>
      <p>Profesyonel el aletlerinden endüstriyel motorlara kadar tüm onarım ihtiyaçlarınız için uzman ekip ve donanımlı atölye.</p>
    </div>
    <div class="services-grid">
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

<section class="section section--muted">
  <div class="container">
    <div class="section-head">
      <span class="eyebrow">Nasıl Çalışıyoruz?</span>
      <h2>4 adımda şeffaf servis süreci</h2>
      <p>Her aşamada bilgilendirilirsiniz; onayınız olmadan hiçbir ücretli işlem yapılmaz.</p>
    </div>
    <ol class="process">
      <li class="reveal"><span class="process__num">01</span><span class="process__icon"><?= icon('clipboard') ?></span><h3>Talep & Teslim</h3><p>Online talep oluşturun ya da cihazınızı servisimize getirin. Takip kodunuz anında oluşur.</p></li>
      <li class="reveal"><span class="process__num">02</span><span class="process__icon"><?= icon('stethoscope') ?></span><h3>Arıza Tespiti</h3><p>Uzman teknisyenlerimiz cihazı söküp detaylı arıza tespiti yapar.</p></li>
      <li class="reveal"><span class="process__num">03</span><span class="process__icon"><?= icon('wrench') ?></span><h3>Onay & Onarım</h3><p>Fiyat bilgisini onaylamanızın ardından orijinal parçalarla onarım yapılır.</p></li>
      <li class="reveal"><span class="process__num">04</span><span class="process__icon"><?= icon('check-circle') ?></span><h3>Test & Teslim</h3><p>Cihaz yük altında test edilir, teslime hazır olduğunda bilgilendirilirsiniz.</p></li>
    </ol>
  </div>
</section>

<section class="section">
  <div class="container split">
    <div class="split__visual reveal" aria-hidden="true">
      <div class="motor-art">
        <svg viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="g1" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f59e0b"/><stop offset="1" stop-color="#ea580c"/></linearGradient>
            <linearGradient id="g2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1e3a5f"/><stop offset="1" stop-color="#0b1a2c"/></linearGradient>
          </defs>
          <circle cx="200" cy="200" r="170" fill="url(#g2)"/>
          <g class="spin" style="transform-origin:200px 200px">
            <?php for ($i = 0; $i < 12; $i++): ?>
              <rect x="192" y="48" width="16" height="46" rx="4" fill="#f59e0b" opacity=".9" transform="rotate(<?= $i * 30 ?> 200 200)"/>
            <?php endfor; ?>
          </g>
          <circle cx="200" cy="200" r="98" fill="none" stroke="#2c5282" stroke-width="10"/>
          <circle cx="200" cy="200" r="70" fill="url(#g1)"/>
          <circle cx="200" cy="200" r="22" fill="#0b1a2c"/>
          <path d="M205 160l-26 46h20l-6 34 28-48h-20z" fill="#fff"/>
        </svg>
        <div class="float-badge float-badge--a"><?= icon('shield') ?><span><strong>Yetkili</strong> Servis</span></div>
        <div class="float-badge float-badge--b"><?= icon('package') ?><span><strong>Orijinal</strong> Parça</span></div>
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

<?php if ($posts): ?>
<section class="section section--muted">
  <div class="container">
    <div class="section-head section-head--row">
      <div>
        <span class="eyebrow">Blog</span>
        <h2>Bakım rehberleri ve teknik bilgiler</h2>
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
        <a href="<?= e(setting('map_link')) ?>" target="_blank" rel="noopener" class="contact-mini__item"><?= icon('map-pin') ?><span><small>Adres</small>İzmit Sanayi Sitesi</span></a>
      </div>
    </div>
    <div>
      <?php partial('faq-list', ['faqs' => $faqs]); ?>
      <a href="/sss" class="link-arrow mt-2">Tüm soruları görün <?= icon('arrow-right') ?></a>
    </div>
  </div>
</section>
