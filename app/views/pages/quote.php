<?php partial('page-hero', ['heading' => 'Teklif isteyin', 'lead' => 'Ne kadar bilgi verirseniz teklif o kadar net olur; ama emin olmadığınız alanları boş bırakabilirsiniz.', 'crumbs' => [[null, 'Teklif iste']]]); ?>
<section class="block block--tight">
  <div class="container form-layout">
    <div>
      <?php if ($created): ?>
        <div class="done">
          <h2>Talebiniz bize ulaştı</h2>
          <p>Satış ekibimiz <strong><?= e($created['phone']) ?></strong> numarasından size dönecek. Görüşmede bu numarayı söylemeniz yeterli:</p>
          <p class="done__code" data-code><?= e($created['code']) ?></p>
          <div class="hero__actions">
            <a href="/urunler" class="btn btn--navy">Ürünlere dön</a>
            <a href="<?= e(wa_href(setting('whatsapp', setting('phone2')), 'Merhaba, ' . $created['code'] . ' numaralı teklif talebim hakkında yazıyorum.')) ?>" class="btn btn--line" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp'tan yazın</a>
          </div>
        </div>
      <?php else: ?>
        <?php if (!empty($errors['form'])): ?><div class="alert" role="alert"><?= e($errors['form']) ?></div><?php endif; ?>
        <?php if ($errors && empty($errors['form'])): ?><div class="alert" role="alert">Formda düzeltilmesi gereken alanlar var; işaretli alanlara bakın.</div><?php endif; ?>
        <form method="post" class="form" novalidate data-validate data-remote="quote">
          <?= csrf_field() ?>
          <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
          <?php if ($product): ?>
            <input type="hidden" name="urun" value="<?= e($product['slug']) ?>">
            <div class="picked">
              <span class="picked__art"><?php if ($product['image']): ?><img src="<?= e(upload_url($product['image'])) ?>" alt=""><?php else: ?><?= model_svg(product_model($product)) ?><?php endif; ?></span>
              <div><small>Teklif istediğiniz ürün</small><strong><?= e($product['title']) ?></strong></div>
              <a href="/teklif-al">Değiştir</a>
            </div>
          <?php endif; ?>
          <fieldset>
            <legend>Ne istiyorsunuz?</legend>
            <div class="choice-row" role="radiogroup" aria-label="Talep türü">
              <?php foreach (quote_types() as $k => $l): ?>
                <label class="choice"><input type="radio" name="type" value="<?= $k ?>"<?= old('type', 'product') === $k ? ' checked' : '' ?>><span><?= e($l) ?></span></label>
              <?php endforeach; ?>
            </div>
            <?php if (!$product): ?>
            <label class="field"><span>Ürün grubu</span>
              <select name="category">
                <option value="">Seçin</option>
                <?php foreach ($categories as $c): ?><option<?= old('category') === $c['name'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?>
                <option<?= old('category') === 'Birden fazla / proje' ? ' selected' : '' ?>>Birden fazla / proje</option>
              </select>
            </label>
            <?php endif; ?>
            <label class="field"><span>Miktar veya kapasite <small>isteğe bağlı</small></span><input type="text" name="quantity" value="<?= e(old('quantity')) ?>" placeholder="Örneğin 20 m³ depo, 2 pompa ya da 12 katlı bina"></label>
            <label class="field<?= isset($errors['message']) ? ' has-error' : '' ?>"><span>Açıklama</span><textarea name="message" rows="4" placeholder="Kullanım amacı, kurulum yeri, daire sayısı gibi bilgiler"><?= e(old('message')) ?></textarea><?php if (isset($errors['message'])): ?><em><?= e($errors['message']) ?></em><?php endif; ?></label>
          </fieldset>
          <fieldset>
            <legend>Size nasıl ulaşalım?</legend>
            <div class="form-row">
              <label class="field<?= isset($errors['name']) ? ' has-error' : '' ?>"><span>Ad soyad</span><input type="text" name="name" value="<?= e(old('name')) ?>" required autocomplete="name"><?php if (isset($errors['name'])): ?><em><?= e($errors['name']) ?></em><?php endif; ?></label>
              <label class="field"><span>Firma <small>isteğe bağlı</small></span><input type="text" name="company" value="<?= e(old('company')) ?>" autocomplete="organization"></label>
            </div>
            <div class="form-row">
              <label class="field<?= isset($errors['phone']) ? ' has-error' : '' ?>"><span>Telefon</span><input type="tel" name="phone" value="<?= e(old('phone')) ?>" required autocomplete="tel" placeholder="05xx xxx xx xx"><?php if (isset($errors['phone'])): ?><em><?= e($errors['phone']) ?></em><?php endif; ?></label>
              <label class="field<?= isset($errors['email']) ? ' has-error' : '' ?>"><span>E-posta <small>isteğe bağlı</small></span><input type="email" name="email" value="<?= e(old('email')) ?>" autocomplete="email"><?php if (isset($errors['email'])): ?><em><?= e($errors['email']) ?></em><?php endif; ?></label>
            </div>
            <label class="field"><span>Şehir veya ilçe <small>isteğe bağlı</small></span><input type="text" name="city" value="<?= e(old('city')) ?>" placeholder="Örneğin İzmit"></label>
          </fieldset>
          <label class="check<?= isset($errors['kvkk']) ? ' has-error' : '' ?>"><input type="checkbox" name="kvkk" value="1" required><span>Bilgilerimin bu talebi yanıtlamak için kullanılmasını kabul ediyorum.</span></label>
          <?php if (isset($errors['kvkk'])): ?><em class="field-error"><?= e($errors['kvkk']) ?></em><?php endif; ?>
          <div><button class="btn btn--signal btn--lg">Teklif talebini gönder</button></div>
        </form>
      <?php endif; ?>
    </div>
    <aside class="aside-box">
      <h2>Bundan sonra</h2>
      <ol class="next-steps">
        <li><strong>Talep numaranız oluşur</strong><span>Ekibimize anında bildirim gider.</span></li>
        <li><strong>Sizi ararız</strong><span>Eksik bilgi varsa birlikte tamamlarız.</span></li>
        <li><strong>Teklifi göndeririz</strong><span>Ürün, sevkiyat ve montaj ayrı kalemlerle.</span></li>
      </ol>
      <hr class="aside-box__rule">
      <p>Acil ihtiyaçlarda doğrudan arayın.</p>
      <a href="<?= e(tel_href(setting('phone'))) ?>" class="btn btn--line-light btn--block"><?= icon('phone') ?> <?= e(setting('phone')) ?></a>
    </aside>
  </div>
</section>
