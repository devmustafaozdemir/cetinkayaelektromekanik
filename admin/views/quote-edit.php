<?php $field = function (string $name, string $label, string $type = 'text', string $extra = '') use ($row, $errors) { ?>
  <label class="field<?= isset($errors[$name]) ? ' has-error' : '' ?>"><span><?= $label ?></span><input type="<?= $type ?>" name="<?= $name ?>" value="<?= e((string)$row[$name]) ?>" <?= $extra ?>><?php if (isset($errors[$name])): ?><em><?= e($errors[$name]) ?></em><?php endif; ?></label>
<?php }; ?>
<a href="<?= admin_url('quotes') ?>" class="back-link"><?= icon('arrow-left') ?> Teklif talepleri</a>
<form method="post" class="editor-layout">
  <?= csrf_field() ?>
  <div class="editor-main">
    <?php if ($id): ?>
    <div class="card request-summary">
      <div>
        <small class="muted">Talep no · <?= $row['source'] === 'manual' ? 'Panelden eklendi' : 'Web sitesi' ?></small>
        <div class="code-line"><strong class="mono"><?= e($row['code']) ?></strong><button type="button" class="icon-btn" data-copy="<?= e($row['code']) ?>" title="Kopyala"><?= icon('clipboard') ?></button></div>
        <small class="muted"><?= e(tr_date($row['created_at'], true)) ?></small>
      </div>
      <div class="request-summary__actions">
        <a href="<?= e(tel_href($row['phone'])) ?>" class="btn"><?= icon('phone') ?> Ara</a>
        <a href="<?= e(wa_href($row['phone'], 'Merhaba ' . $row['name'] . ', ' . setting('site_name') . ' olarak ' . ($row['product_name'] ?: $row['category'] ?: 'talebiniz') . ' için teklif talebiniz (' . $row['code'] . ') hakkında yazıyoruz.')) ?>" target="_blank" rel="noopener" class="btn btn--wa"><?= icon('whatsapp') ?> WhatsApp</a>
        <?php if ($row['email']): ?><a href="mailto:<?= e($row['email']) ?>?subject=<?= rawurlencode('Fiyat Teklifi - ' . $row['code']) ?>" class="btn"><?= icon('mail') ?> E-posta</a><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    <div class="card">
      <h3>Talep</h3>
      <div class="form-row">
        <label class="field"><span>Talep türü</span>
          <select name="type"><?php foreach (quote_types() as $k => $l): ?><option value="<?= $k ?>"<?= $row['type'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select>
        </label>
        <label class="field"><span>Ürün grubu</span>
          <input type="text" name="category" value="<?= e($row['category']) ?>" list="cat-list">
        </label>
      </div>
      <datalist id="cat-list"><?php foreach ($categories as $c): ?><option value="<?= e($c['name']) ?>"><?php endforeach; ?></datalist>
      <div class="form-row">
        <label class="field"><span>Ürün</span>
          <select name="product_id">
            <option value="">— Belirtilmedi —</option>
            <?php foreach ($products as $pr): ?><option value="<?= $pr['id'] ?>"<?= (int)$row['product_id'] === (int)$pr['id'] ? ' selected' : '' ?>><?= e($pr['title']) ?></option><?php endforeach; ?>
          </select>
          <?php if (!$row['product_id'] && $row['product_name']): ?><small class="muted">Kayıtlı ürün: <?= e($row['product_name']) ?></small><?php endif; ?>
          <input type="hidden" name="product_name" value="<?= e($row['product_name']) ?>">
        </label>
        <?php $field('quantity', 'Miktar / kapasite'); ?>
      </div>
      <label class="field"><span>Müşteri mesajı</span><textarea name="message" rows="4"><?= e($row['message']) ?></textarea></label>
    </div>
    <div class="card">
      <h3>Müşteri</h3>
      <div class="form-row"><?php $field('name', 'Ad Soyad *', 'text', 'required'); $field('company', 'Firma'); ?></div>
      <div class="form-row"><?php $field('phone', 'Telefon *', 'tel', 'required'); $field('email', 'E-posta', 'email'); ?></div>
      <?php $field('city', 'Şehir / İlçe'); ?>
    </div>
  </div>
  <aside class="editor-side">
    <div class="card sticky">
      <h3>Satış durumu</h3>
      <div class="status-picker">
        <?php foreach ($statuses as $k => [$l, $c]): ?>
          <label><input type="radio" name="status" value="<?= $k ?>"<?= $row['status'] === $k ? ' checked' : '' ?>><span class="status status--<?= $c ?>"><?= e($l) ?></span></label>
        <?php endforeach; ?>
      </div>
      <label class="field"><span>Geçmişe not ekle</span><input type="text" name="log_note" maxlength="500" placeholder="Örn. Teklif e-posta ile gönderildi."></label>
      <label class="field"><span>İç not <small class="muted">(sadece panelde görünür)</small></span><textarea name="admin_note" rows="3" placeholder="Fiyat, iskonto, görüşme notları…"><?= e($row['admin_note']) ?></textarea></label>
      <button class="btn btn--primary btn--block"><?= icon('check') ?> <?= $id ? 'Kaydet' : 'Kaydı Oluştur' ?></button>
    </div>
    <?php if ($log): ?>
    <div class="card">
      <h3>Geçmiş</h3>
      <ul class="timeline">
        <?php foreach ($log as $l): ?>
          <li><span class="timeline__dot dot--<?= status_color($l['status']) ?>"></span><div><strong><?= e(status_label($l['status'])) ?></strong><?php if ($l['note']): ?><p><?= e($l['note']) ?></p><?php endif; ?><time><?= e(tr_date($l['created_at'], true)) ?></time></div></li>
        <?php endforeach; ?>
      </ul>
    </div>
    <div class="card card--danger"><button type="submit" form="delete-form" class="btn btn--danger-ghost btn--block"><?= icon('trash') ?> Kaydı sil</button></div>
    <?php endif; ?>
  </aside>
</form>
<?php if ($id): ?><form id="delete-form" method="post" action="<?= admin_url('quotes', ['a' => 'delete', 'id' => $id]) ?>" data-confirm="Bu teklif talebi silinsin mi?"><?= csrf_field() ?></form><?php endif; ?>
