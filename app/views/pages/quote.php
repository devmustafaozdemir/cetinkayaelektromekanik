<?php partial('page-hero', ['heading' => 'Teklif Al', 'lead' => 'İhtiyacınızı paylaşın; satış ekibimiz size en uygun ürün ve fiyatla hızlıca dönüş yapsın.', 'crumbs' => [[null, 'Teklif Al']]]); ?>
<section class="section">
  <div class="container request-grid">
    <div class="form-card">
      <?php if ($created): ?>
        <div class="success-state">
          <span class="success-state__icon"><?= icon('check') ?></span>
          <h2>Teklif talebiniz alındı</h2>
          <p>Satış ekibimiz en kısa sürede <strong><?= e($created['phone']) ?></strong> numarasından sizinle iletişime geçecek.</p>
          <div class="code-box">
            <small>Talep Numaranız</small>
            <strong data-code><?= e($created['code']) ?></strong>
          </div>
          <div class="hero__actions" style="justify-content:center">
            <a href="/urunler" class="btn btn--primary"><?= icon('grid') ?> Ürünlere Dön</a>
            <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')), 'Merhaba, ' . $created['code'] . ' numaralı teklif talebim hakkında yazıyorum.')) ?>" class="btn btn--ghost" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp'tan yazın</a>
          </div>
        </div>
      <?php else: ?>
        <?php if (!empty($errors['form'])): ?><div class="alert alert--error"><?= e($errors['form']) ?></div><?php endif; ?>
        <form method="post" class="form" novalidate data-validate>
          <?= csrf_field() ?>
          <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
          <?php if ($product): ?>
            <input type="hidden" name="urun" value="<?= e($product['slug']) ?>">
            <div class="picked-product">
              <span class="picked-product__art"><?php if ($product['image']): ?><img src="<?= e(upload_url($product['image'])) ?>" alt=""><?php else: ?><?= product_art($product['art'] ?? 'tank') ?><?php endif; ?></span>
              <div><small>Teklif istenen ürün</small><strong><?= e($product['title']) ?></strong><span><?= e($product['brand']) ?></span></div>
              <a href="/teklif-al" class="icon-link" aria-label="Ürünü kaldır"><?= icon('x') ?></a>
            </div>
          <?php endif; ?>
          <fieldset>
            <legend><span class="step-num">1</span> Talebiniz</legend>
            <div class="field"><span>Talep türü</span>
              <div class="choice-row">
                <?php foreach (quote_types() as $k => $l): ?>
                  <label class="choice"><input type="radio" name="type" value="<?= $k ?>"<?= old('type', 'product') === $k ? ' checked' : '' ?>><span><?= e($l) ?></span></label>
                <?php endforeach; ?>
              </div>
            </div>
            <?php if (!$product): ?>
            <label class="field"><span>Ürün grubu</span>
              <select name="category">
                <option value="">Seçiniz</option>
                <?php foreach ($categories as $c): ?><option<?= old('category') === $c['name'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                <option<?= old('category') === 'Birden fazla / proje' ? ' selected' : '' ?>>Birden fazla / proje</option>
              </select>
            </label>
            <?php endif; ?>
            <label class="field"><span>Miktar / kapasite <small class="muted">(opsiyonel)</small></span><input type="text" name="quantity" value="<?= e(old('quantity')) ?>" placeholder="Örn. 20 m³ depo, 2 adet pompa, 12 katlı bina"></label>
            <label class="field<?= isset($errors['message']) ? ' has-error' : '' ?>"><span>Açıklama</span><textarea name="message" rows="4" placeholder="Kullanım amacı, kurulum yeri, bina/daire sayısı gibi bilgileri yazabilirsiniz."><?= e(old('message')) ?></textarea><?php if (isset($errors['message'])): ?><em><?= e($errors['message']) ?></em><?php endif; ?></label>
          </fieldset>
          <fieldset>
            <legend><span class="step-num">2</span> İletişim Bilgileri</legend>
            <div class="form-row">
              <label class="field<?= isset($errors['name']) ? ' has-error' : '' ?>"><span>Ad Soyad *</span><input type="text" name="name" value="<?= e(old('name')) ?>" required autocomplete="name"><?php if (isset($errors['name'])): ?><em><?= e($errors['name']) ?></em><?php endif; ?></label>
              <label class="field"><span>Firma</span><input type="text" name="company" value="<?= e(old('company')) ?>" autocomplete="organization"></label>
            </div>
            <div class="form-row">
              <label class="field<?= isset($errors['phone']) ? ' has-error' : '' ?>"><span>Telefon *</span><input type="tel" name="phone" value="<?= e(old('phone')) ?>" required autocomplete="tel" placeholder="05xx xxx xx xx"><?php if (isset($errors['phone'])): ?><em><?= e($errors['phone']) ?></em><?php endif; ?></label>
              <label class="field<?= isset($errors['email']) ? ' has-error' : '' ?>"><span>E-posta</span><input type="email" name="email" value="<?= e(old('email')) ?>" autocomplete="email"><?php if (isset($errors['email'])): ?><em><?= e($errors['email']) ?></em><?php endif; ?></label>
            </div>
            <label class="field"><span>Şehir / İlçe</span><input type="text" name="city" value="<?= e(old('city')) ?>" placeholder="Örn. İzmit / Kocaeli"></label>
          </fieldset>
          <label class="check<?= isset($errors['kvkk']) ? ' has-error' : '' ?>"><input type="checkbox" name="kvkk" value="1" required><span>Kişisel verilerimin teklif talebimin yanıtlanması amacıyla işlenmesini kabul ediyorum.</span></label>
          <?php if (isset($errors['kvkk'])): ?><em class="field-error"><?= e($errors['kvkk']) ?></em><?php endif; ?>
          <button class="btn btn--primary btn--lg btn--block"><?= icon('calculator') ?> Teklif Talebini Gönder</button>
        </form>
      <?php endif; ?>
    </div>
    <aside class="aside">
      <div class="aside-card">
        <h3>Sonraki adımlar</h3>
        <ol class="mini-steps">
          <li><strong>Talebiniz ulaşır</strong><span>Size bir talep numarası verilir, ekibimize anında bildirim gider.</span></li>
          <li><strong>Sizi arıyoruz</strong><span>Gerekirse ihtiyacınızı netleştirmek için sizinle görüşürüz.</span></li>
          <li><strong>Teklifiniz hazır</strong><span>Ürün, sevkiyat ve montaj kalemleriyle teklifinizi iletiriz.</span></li>
        </ol>
      </div>
      <div class="aside-card aside-card--dark">
        <h3>Hemen görüşelim</h3>
        <p>Acil ihtiyaçlarınız için doğrudan arayın.</p>
        <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--primary btn--block"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
        <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')), 'Merhaba, fiyat teklifi almak istiyorum.')) ?>" class="btn btn--outline-light btn--block" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp</a>
      </div>
    </aside>
  </div>
</section>
