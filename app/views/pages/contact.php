<?php partial('page-hero', ['heading' => 'İletişim', 'lead' => 'Ürün bilgisi, fiyat teklifi ve proje görüşmeleri için bize ulaşın.', 'crumbs' => [[null, 'İletişim']]]); ?>
<section class="section">
  <div class="container contact-grid">
    <div class="contact-cards">
      <a href="<?= e(tel_href(setting('phone'))) ?>" class="contact-card reveal"><span class="contact-card__icon"><?= icon('phone') ?></span><div><small>Telefon</small><strong><?= e(setting('phone')) ?></strong><?php if (setting('phone2')): ?><span><?= e(setting('phone2')) ?></span><?php endif; ?></div></a>
      <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')))) ?>" target="_blank" rel="noopener" class="contact-card reveal"><span class="contact-card__icon contact-card__icon--wa"><?= icon('whatsapp') ?></span><div><small>WhatsApp</small><strong><?= e(setting('whatsapp', setting('phone2'))) ?></strong><span>Hızlı yanıt için yazın</span></div></a>
      <a href="mailto:<?= e(setting('email')) ?>" class="contact-card reveal"><span class="contact-card__icon"><?= icon('mail') ?></span><div><small>E-posta</small><strong><?= e(setting('email')) ?></strong></div></a>
      <a href="<?= e(setting('map_link')) ?>" target="_blank" rel="noopener" class="contact-card reveal"><span class="contact-card__icon"><?= icon('map-pin') ?></span><div><small>Adres</small><span><?= e(setting('address')) ?></span></div></a>
      <div class="contact-card reveal"><span class="contact-card__icon"><?= icon('clock') ?></span><div><small>Çalışma Saatleri</small><?php foreach (setting_lines('hours') as $h): ?><span><?= e($h) ?></span><?php endforeach; ?></div></div>
    </div>
    <div class="form-card">
      <?php if ($sent): ?>
        <div class="success-state">
          <span class="success-state__icon"><?= icon('check') ?></span>
          <h2>Mesajınız bize ulaştı</h2>
          <p>En kısa sürede sizinle iletişime geçeceğiz. Teşekkür ederiz!</p>
          <a href="/" class="btn btn--ghost">Ana sayfaya dön</a>
        </div>
      <?php else: ?>
        <h2>Bize yazın</h2>
        <p class="muted">Formu doldurun, mesai saatleri içinde dönüş yapalım.</p>
        <?php if (!empty($errors['form'])): ?><div class="alert alert--error"><?= e($errors['form']) ?></div><?php endif; ?>
        <form method="post" class="form" novalidate data-validate>
          <?= csrf_field() ?>
          <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
          <div class="form-row">
            <label class="field<?= isset($errors['name']) ? ' has-error' : '' ?>"><span>Ad Soyad *</span><input type="text" name="name" value="<?= e(old('name')) ?>" required autocomplete="name"><?php if (isset($errors['name'])): ?><em><?= e($errors['name']) ?></em><?php endif; ?></label>
            <label class="field<?= isset($errors['phone']) ? ' has-error' : '' ?>"><span>Telefon</span><input type="tel" name="phone" value="<?= e(old('phone')) ?>" autocomplete="tel" placeholder="05xx xxx xx xx"><?php if (isset($errors['phone'])): ?><em><?= e($errors['phone']) ?></em><?php endif; ?></label>
          </div>
          <div class="form-row">
            <label class="field<?= isset($errors['email']) ? ' has-error' : '' ?>"><span>E-posta</span><input type="email" name="email" value="<?= e(old('email')) ?>" autocomplete="email"><?php if (isset($errors['email'])): ?><em><?= e($errors['email']) ?></em><?php endif; ?></label>
            <label class="field"><span>Konu</span><input type="text" name="subject" value="<?= e(old('subject')) ?>"></label>
          </div>
          <label class="field<?= isset($errors['message']) ? ' has-error' : '' ?>"><span>Mesajınız *</span><textarea name="message" rows="5" required minlength="10"><?= e(old('message')) ?></textarea><?php if (isset($errors['message'])): ?><em><?= e($errors['message']) ?></em><?php endif; ?></label>
          <button class="btn btn--primary btn--lg"><?= icon('mail') ?> Mesajı Gönder</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php if (setting('map_embed')): ?>
<section class="map-wrap">
  <iframe src="<?= e(setting('map_embed')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Konum haritası" allowfullscreen></iframe>
</section>
<?php endif; ?>
