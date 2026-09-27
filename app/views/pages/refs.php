<?php partial('page-hero', ['heading' => 'Referanslar', 'lead' => setting('refs_lead'), 'crumbs' => [['/hakkimizda', 'Kurumsal'], [null, 'Referanslar']]]); ?>
<section class="block block--tight">
  <div class="container">
    <?php if ($refs): ?>
      <ul class="ref-grid"><?php foreach ($refs as $r) partial('ref-card', ['r' => $r]); ?></ul>
    <?php else: ?>
      <p class="empty-note">Referans listemiz hazırlanıyor. Tamamladığımız projeler hakkında bilgi almak için bizi arayın.</p>
    <?php endif; ?>
  </div>
</section>
