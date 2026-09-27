<?php
$values = setting_lines('about_values');
$materials = ['galvaniz' => 'Galvaniz', 'paslanmaz' => 'Paslanmaz', 'grp' => 'GRP', 'sandvic' => 'İzolasyonlu'];
?>
<section class="hero">
  <div class="container hero__inner">
    <div class="hero__copy">
      <h1><?= e(setting('hero_title')) ?></h1>
      <p class="hero__text"><?= e(setting('hero_text')) ?></p>
      <div class="hero__actions">
        <a href="/urunler" class="btn btn--navy btn--lg">Ürünleri incele</a>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--line btn--lg"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
      </div>
      <p class="hero__note">Modüler depo, hidrofor ya da pompa; ihtiyacınızı yazın, kapasite hesabını birlikte yapalım.</p>
    </div>

    <div class="sizer" data-sizer data-viewer="<?= e(asset('js/viewer3d.js')) ?>">
      <div class="sizer__top">
        <h2>Deponuzu ölçün</h2>
        <div class="viewswitch" role="group" aria-label="Görünüm">
          <button type="button" data-view="2d" aria-pressed="true">Çizim</button>
          <button type="button" data-view="3d" aria-pressed="false">3D</button>
        </div>
      </div>
      <p>Modüler depolar 1 × 1 metrelik panellerle kurulur. Ölçüleri değiştirin; hacmi ve kaç daireye yeteceğini görün.</p>
      <div class="sizer__stage" data-stage>
        <div class="sizer__svg" data-svg><?= model_svg('tank:galvaniz', 'art', ['w' => 4, 'l' => 3, 'h' => 2]) ?></div>
        <div class="viewer" data-viewer-host hidden></div>
        <p class="viewer__hint" data-hint hidden>Sürükleyerek çevirin, iki parmakla yakınlaştırın.</p>
      </div>
      <div class="sizer__controls">
        <?php foreach (['w' => ['En', 4, 1, 12], 'l' => ['Boy', 3, 1, 12], 'h' => ['Yükseklik', 2, 1, 4]] as $k => [$label, $val, $min, $max]): ?>
          <div class="stepper">
            <span class="stepper__label" id="lbl-<?= $k ?>"><?= $label ?> (m)</span>
            <div class="stepper__box">
              <button type="button" data-step="<?= $k ?>" data-delta="-1" aria-label="<?= $label ?> azalt">−</button>
              <output data-dim="<?= $k ?>" data-min="<?= $min ?>" data-max="<?= $max ?>" aria-labelledby="lbl-<?= $k ?>" aria-live="polite"><?= $val ?></output>
              <button type="button" data-step="<?= $k ?>" data-delta="1" aria-label="<?= $label ?> artır">+</button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="sizer__materials" role="radiogroup" aria-label="Panel malzemesi">
        <?php foreach ($materials as $k => $l): ?>
          <label><input type="radio" name="material" value="<?= $k ?>"<?= $k === 'galvaniz' ? ' checked' : '' ?>><span><?= e($l) ?></span></label>
        <?php endforeach; ?>
      </div>
      <div class="sizer__result">
        <span class="sizer__volume"><span data-volume>24</span> <small>m³</small></span>
        <span class="sizer__meta"><strong data-litres>24.000</strong> litre, yaklaşık <strong data-flats>40</strong> dairenin bir günlük ihtiyacı</span>
      </div>
      <a href="/teklif-al?olcu=4x3x2&amp;malzeme=galvaniz" class="btn btn--signal btn--block" data-quote>Bu ölçüde teklif iste</a>
    </div>
  </div>
  <?php partial('brand-logos'); ?>
</section>

<section class="block">
  <div class="container">
    <div class="block__head">
      <h2>Ürün grupları</h2>
      <p>Depolamadan basınçlandırmaya, kuyudan drenaja kadar su tesisatının bütün halkaları.</p>
    </div>
    <ul class="cat-rows">
      <?php foreach ($categories as $c): ?>
        <li class="cat-row">
          <a href="/urunler/kategori/<?= e($c['slug']) ?>" tabindex="-1" aria-hidden="true"><?= category_photo($c) ?></a>
          <div>
            <h3><a href="/urunler/kategori/<?= e($c['slug']) ?>"><?= e($c['name']) ?></a></h3>
            <p><?= e($c['summary']) ?></p>
          </div>
          <span class="cat-row__count"><?= (int)$c['cnt'] ?> ürün</span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php if ($products): ?>
<section class="block block--steel">
  <div class="container">
    <div class="block__head block__head--row">
      <h2>Sık teklif istenen ürünler</h2>
      <a href="/urunler" class="text-link">Tüm ürünler</a>
    </div>
    <div class="product-grid">
      <?php foreach ($products as $p) partial('product-card', ['p' => $p]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="block">
  <div class="container">
    <div class="block__head">
      <h2>Talebinizden teslimata</h2>
      <p>Her adımda kimin ne yapacağı baştan belli.</p>
    </div>
    <ol class="steps">
      <li><h3>İhtiyacı konuşuruz</h3><p>Kullanım amacı, kişi ya da daire sayısı, kurulum alanı ve mevcut tesisat.</p></li>
      <li><h3>Kapasiteyi hesaplarız</h3><p>Depo hacmi, gerekli debi ve basma yüksekliğine göre ürünü seçeriz.</p></li>
      <li><h3>Teklifi göndeririz</h3><p>Ürün, sevkiyat ve montaj kalemleri ayrı ayrı yazılı olarak.</p></li>
      <li><h3>Teslim eder, kurarız</h3><p>İsterseniz montajı ve devreye almayı da ekibimiz yapar.</p></li>
    </ol>
  </div>
</section>

<section class="block block--steel">
  <div class="container duo">
    <div>
      <h2><?= e(setting('about_title')) ?></h2>
      <?php foreach (array_slice(array_filter(array_map('trim', preg_split('/\R{2,}/', setting('about_text')))), 0, 2) as $para): ?><p><?= e($para) ?></p><?php endforeach; ?>
      <a href="/hakkimizda" class="text-link">Firmamızı tanıyın</a>
    </div>
    <div>
      <ul class="ticks">
        <?php foreach ($values as $v): ?><li><?= e($v) ?></li><?php endforeach; ?>
      </ul>
      <?php if ($services): ?>
      <ul class="svc-list svc-list--compact">
        <?php foreach ($services as $s): ?>
          <li><a href="/hizmetler/<?= e($s['slug']) ?>"><?= icon($s['icon']) ?><h3><?= e($s['title']) ?></h3><p><?= e($s['summary']) ?></p></a></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($posts): ?>
<section class="block">
  <div class="container">
    <div class="block__head block__head--row">
      <h2>Rehberler</h2>
      <a href="/blog" class="text-link">Bütün yazılar</a>
    </div>
    <div class="post-grid">
      <?php foreach ($posts as $post) partial('post-card', ['post' => $post]); ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="block block--steel">
  <div class="container duo">
    <div>
      <h2>Sık sorulan sorular</h2>
      <p class="muted">Cevabını bulamadığınız bir soru varsa arayın ya da WhatsApp'tan yazın.</p>
      <p><a href="<?= e(tel_href(setting('phone'))) ?>" class="text-link"><?= e(setting('phone')) ?></a></p>
    </div>
    <div>
      <?php partial('faq-list', ['faqs' => $faqs]); ?>
      <p class="mt"><a href="/sss" class="text-link">Bütün sorular</a></p>
    </div>
  </div>
</section>
