<?php partial('page-hero', ['heading' => 'Online Servis Talebi', 'lead' => 'Cihazınızın bilgilerini paylaşın; size özel takip kodunuzla servis sürecini adım adım izleyin.', 'crumbs' => [[null, 'Servis Talebi']]]); ?>
<section class="section">
  <div class="container request-grid">
    <div class="form-card">
      <?php if ($created): ?>
        <div class="success-state">
          <span class="success-state__icon"><?= icon('check') ?></span>
          <h2>Servis talebiniz oluşturuldu</h2>
          <p>Takip kodunuzu not alın. Ekibimiz en kısa sürede sizi arayacak.</p>
          <div class="code-box">
            <small>Takip Kodunuz</small>
            <strong data-code><?= e($created['code']) ?></strong>
            <button type="button" class="btn btn--ghost btn--sm" data-copy="<?= e($created['code']) ?>"><?= icon('clipboard') ?> Kopyala</button>
          </div>
          <div class="hero__actions" style="justify-content:center">
            <a href="/servis-takip?kod=<?= e($created['code']) ?>&amp;telefon=<?= e(substr(preg_replace('/\D/', '', $created['phone']), -4)) ?>" class="btn btn--primary"><?= icon('search') ?> Durumu Görüntüle</a>
            <a href="/servis-talebi" class="btn btn--ghost">Yeni Talep</a>
          </div>
        </div>
      <?php else: ?>
        <?php if (!empty($errors['form'])): ?><div class="alert alert--error"><?= e($errors['form']) ?></div><?php endif; ?>
        <form method="post" class="form" novalidate data-validate>
          <?= csrf_field() ?>
          <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
          <fieldset>
            <legend><span class="step-num">1</span> İletişim Bilgileri</legend>
            <div class="form-row">
              <label class="field<?= isset($errors['name']) ? ' has-error' : '' ?>"><span>Ad Soyad *</span><input type="text" name="name" value="<?= e(old('name')) ?>" required autocomplete="name"><?php if (isset($errors['name'])): ?><em><?= e($errors['name']) ?></em><?php endif; ?></label>
              <label class="field<?= isset($errors['phone']) ? ' has-error' : '' ?>"><span>Telefon *</span><input type="tel" name="phone" value="<?= e(old('phone')) ?>" required autocomplete="tel" placeholder="05xx xxx xx xx"><?php if (isset($errors['phone'])): ?><em><?= e($errors['phone']) ?></em><?php endif; ?></label>
            </div>
            <div class="form-row">
              <label class="field<?= isset($errors['email']) ? ' has-error' : '' ?>"><span>E-posta</span><input type="email" name="email" value="<?= e(old('email')) ?>" autocomplete="email"><?php if (isset($errors['email'])): ?><em><?= e($errors['email']) ?></em><?php endif; ?></label>
              <label class="field"><span>Firma (opsiyonel)</span><input type="text" name="company" value="<?= e(old('company')) ?>" autocomplete="organization"></label>
            </div>
          </fieldset>
          <fieldset>
            <legend><span class="step-num">2</span> Cihaz Bilgileri</legend>
            <div class="field"><span>Marka</span>
              <div class="choice-row">
                <?php foreach (array_merge($brands, ['Diğer']) as $b): ?>
                  <label class="choice"><input type="radio" name="brand" value="<?= e($b) ?>"<?= old('brand') === $b ? ' checked' : '' ?>><span><?= e($b) ?></span></label>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="form-row">
              <label class="field<?= isset($errors['device']) ? ' has-error' : '' ?>"><span>Cihaz Türü *</span><input type="text" name="device" value="<?= e(old('device')) ?>" required list="device-types" placeholder="Örn. Kırıcı-delici"><?php if (isset($errors['device'])): ?><em><?= e($errors['device']) ?></em><?php endif; ?></label>
              <label class="field"><span>Model</span><input type="text" name="model" value="<?= e(old('model')) ?>" placeholder="Örn. HR2470"></label>
            </div>
            <datalist id="device-types">
              <option value="Matkap / Vidalama"><option value="Kırıcı-Delici"><option value="Kırıcı"><option value="Avuç Taşlama"><option value="Büyük Taşlama"><option value="Daire Testere"><option value="Gönye Testere"><option value="Dekupaj Testere"><option value="Planya"><option value="Freze"><option value="Akü / Şarj Cihazı"><option value="Elektrik Motoru">
            </datalist>
            <label class="switch"><input type="checkbox" name="warranty" value="1"<?= old('warranty') ? ' checked' : '' ?>><span class="switch__ui"></span><span>Cihazım garanti kapsamında</span></label>
          </fieldset>
          <fieldset>
            <legend><span class="step-num">3</span> Arıza Açıklaması</legend>
            <label class="field<?= isset($errors['issue']) ? ' has-error' : '' ?>"><span>Arızayı kısaca anlatın *</span><textarea name="issue" rows="4" required placeholder="Örn. Çalışırken kıvılcım yapıyor ve güç kaybediyor."><?= e(old('issue')) ?></textarea><?php if (isset($errors['issue'])): ?><em><?= e($errors['issue']) ?></em><?php endif; ?></label>
          </fieldset>
          <label class="check<?= isset($errors['kvkk']) ? ' has-error' : '' ?>"><input type="checkbox" name="kvkk" value="1" required><span>Kişisel verilerimin servis talebimin yürütülmesi amacıyla işlenmesini kabul ediyorum.</span></label>
          <?php if (isset($errors['kvkk'])): ?><em class="field-error"><?= e($errors['kvkk']) ?></em><?php endif; ?>
          <button class="btn btn--primary btn--lg btn--block"><?= icon('clipboard') ?> Talebi Gönder</button>
        </form>
      <?php endif; ?>
    </div>
    <aside class="aside">
      <div class="aside-card">
        <h3>Sonraki adımlar</h3>
        <ol class="mini-steps">
          <li><strong>Takip kodu</strong><span>Talebiniz anında kaydedilir, takip kodunuz oluşur.</span></li>
          <li><strong>Sizi arıyoruz</strong><span>Ekibimiz cihazın teslimi için sizinle iletişime geçer.</span></li>
          <li><strong>Arıza tespiti</strong><span>Tespit sonrası fiyat bilgisi onayınıza sunulur.</span></li>
          <li><strong>Online takip</strong><span>Tüm süreci Servis Takip sayfasından izlersiniz.</span></li>
        </ol>
      </div>
      <div class="aside-card aside-card--dark">
        <h3>Acil mi?</h3>
        <p>Hemen arayın, ekibimiz yardımcı olsun.</p>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--primary btn--block"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
      </div>
    </aside>
  </div>
</section>
