<?php partial('page-hero', ['heading' => 'Sık sorulan sorular', 'lead' => 'Ürünler, teklif süreci, montaj ve servis hakkında.', 'crumbs' => [[null, 'Sık sorulan sorular']]]); ?>
<section class="block">
  <div class="container container--narrow">
    <?php partial('faq-list', ['faqs' => $faqs]); ?>
    <div class="callout">
      <p><strong>Sorunuz burada yok mu?</strong>Bize yazın, en kısa sürede yanıtlayalım.</p>
      <a href="/iletisim" class="btn btn--navy">İletişime geçin</a>
    </div>
  </div>
</section>
