<?php partial('page-hero', ['heading' => 'Çözüm Ortakları', 'lead' => setting('partners_lead'), 'crumbs' => [['/hakkimizda', 'Kurumsal'], [null, 'Çözüm Ortakları']]]); ?>
<section class="block block--tight">
  <div class="container">
    <?php if ($partners): ?>
      <ul class="ref-grid"><?php foreach ($partners as $r) partial('ref-card', ['r' => $r]); ?></ul>
    <?php else: ?>
      <p class="empty-note">Çözüm ortaklarımızın listesi hazırlanıyor.</p>
    <?php endif; ?>
  </div>
</section>
<section class="block block--steel">
  <div class="container">
    <div class="block__head block__head--row"><h2>Satışını yaptığımız markalar</h2><a href="/markalar" class="text-link">Markalar sayfası</a></div>
  </div>
  <?php partial('brand-logos'); ?>
</section>
