<?php partial('page-hero', ['heading' => 'Sıkça Sorulan Sorular', 'lead' => 'Ürünler, teklif süreci, montaj ve servis hakkında merak edilenler.', 'crumbs' => [[null, 'SSS']]]); ?>
<section class="section">
  <div class="container container--narrow">
    <?php partial('faq-list', ['faqs' => $faqs]); ?>
    <div class="inline-cta mt-3">
      <span class="inline-cta__icon"><?= icon('help') ?></span>
      <div><strong>Sorunuzun cevabını bulamadınız mı?</strong><p>Bize yazın, en kısa sürede dönüş yapalım.</p></div>
      <a href="/iletisim" class="btn btn--primary">İletişime Geç</a>
    </div>
  </div>
</section>
