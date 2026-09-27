<?php partial('page-hero', ['heading' => 'İletişim', 'lead' => 'Ürün bilgisi, fiyat ve proje görüşmeleri için arayın, yazın ya da mağazamıza uğrayın.', 'crumbs' => [[null, 'İletişim']]]); ?>
<section class="block block--tight">
  <div class="container contact">
    <dl class="contact-list">
      <div><dt>Telefon</dt><dd><a href="<?= e(tel_href(setting('phone'))) ?>"><?= e(setting('phone')) ?></a><?php if (setting('phone2')): ?><br><a href="<?= e(tel_href(setting('phone2'))) ?>"><?= e(setting('phone2')) ?></a><?php endif; ?></dd></div>
      <div><dt>WhatsApp</dt><dd><a href="<?= e(wa_href(setting('whatsapp', setting('phone2')))) ?>" target="_blank" rel="noopener"><?= e(setting('whatsapp', setting('phone2'))) ?></a></dd></div>
      <div><dt>E-posta</dt><dd><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></dd></div>
      <div><dt>Adres</dt><dd><?= e(setting('address')) ?><br><a href="<?= e(setting('map_link')) ?>" target="_blank" rel="noopener" class="small">Yol tarifi alın</a></dd></div>
      <div><dt>Çalışma saatleri</dt><dd><?php foreach (setting_lines('hours') as $h): ?><?= e($h) ?><br><?php endforeach; ?></dd></div>
    </dl>
    <div>
      <?php if ($sent): ?>
        <div class="done">
          <h2>Mesajınız gönderildi</h2>
          <p>Mesai saatleri içinde size dönüş yapacağız.</p>
          <a href="/" class="btn btn--line">Ana sayfaya dön</a>
        </div>
      <?php else: ?>
        <h2>Mesaj gönderin</h2>
        <?php if (!empty($errors['form'])): ?><div class="alert" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>
        <form method="post" class="form" novalidate data-validate>
          <?= csrf_field() ?>
          <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
          <div class="form-row">
            <label class="field<?= isset($errors['name']) ? ' has-error' : '' ?>"><span>Ad soyad</span><input type="text" name="name" value="<?= e(old('name')) ?>" required autocomplete="name"><?php if (isset($errors['name'])): ?><em><?= e($errors['name']) ?></em><?php endif; ?></label>
            <label class="field<?= isset($errors['phone']) ? ' has-error' : '' ?>"><span>Telefon</span><input type="tel" name="phone" value="<?= e(old('phone')) ?>" autocomplete="tel"><?php if (isset($errors['phone'])): ?><em><?= e($errors['phone']) ?></em><?php endif; ?></label>
          </div>
          <div class="form-row">
            <label class="field<?= isset($errors['email']) ? ' has-error' : '' ?>"><span>E-posta</span><input type="email" name="email" value="<?= e(old('email')) ?>" autocomplete="email"><?php if (isset($errors['email'])): ?><em><?= e($errors['email']) ?></em><?php endif; ?></label>
            <label class="field"><span>Konu <small>isteğe bağlı</small></span><input type="text" name="subject" value="<?= e(old('subject')) ?>"></label>
          </div>
          <label class="field<?= isset($errors['message']) ? ' has-error' : '' ?>"><span>Mesajınız</span><textarea name="message" rows="5" required minlength="10"><?= e(old('message')) ?></textarea><?php if (isset($errors['message'])): ?><em><?= e($errors['message']) ?></em><?php endif; ?></label>
          <div><button class="btn btn--navy btn--lg">Mesajı gönder</button></div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php if (setting('map_embed')): ?>
<iframe class="map" src="<?= e(setting('map_embed')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Konum haritası" allowfullscreen></iframe>
<?php endif; ?>
