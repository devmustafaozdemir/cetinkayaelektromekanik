<?php $field = function (string $name, string $label, string $type = 'text', string $extra = '') use ($req, $errors) { ?>
  <label class="field<?= isset($errors[$name]) ? ' has-error' : '' ?>"><span><?= $label ?></span><input type="<?= $type ?>" name="<?= $name ?>" value="<?= e((string)$req[$name]) ?>" <?= $extra ?>><?php if (isset($errors[$name])): ?><em><?= e($errors[$name]) ?></em><?php endif; ?></label>
<?php }; ?>
<a href="<?= admin_url('requests') ?>" class="back-link"><?= icon('arrow-left') ?> Servis talepleri</a>
<form method="post" class="editor-layout">
  <?= csrf_field() ?>
  <div class="editor-main">
    <?php if ($id): ?>
    <div class="card request-summary">
      <div>
        <small class="muted">Takip kodu</small>
        <div class="code-line"><strong class="mono"><?= e($req['code']) ?></strong><button type="button" class="icon-btn" data-copy="<?= e($req['code']) ?>" title="Kopyala"><?= icon('clipboard') ?></button></div>
        <small class="muted">Oluşturulma: <?= e(tr_date($req['created_at'], true)) ?></small>
      </div>
      <div class="request-summary__actions">
        <a href="<?= e(tel_href($req['phone'])) ?>" class="btn"><?= icon('phone') ?> Ara</a>
        <a href="<?= e(wa_href($req['phone'], 'Merhaba ' . $req['name'] . ', ' . setting('site_name') . ' servis kaydınız (' . $req['code'] . ') güncel durumu: ' . status_label($req['status']) . '. Takip: ' . base_url() . '/servis-takip?kod=' . $req['code'])) ?>" target="_blank" rel="noopener" class="btn btn--wa"><?= icon('whatsapp') ?> WhatsApp bildir</a>
      </div>
    </div>
    <?php endif; ?>
    <div class="card">
      <h3>Müşteri</h3>
      <div class="form-row"><?php $field('name', 'Ad Soyad *', 'text', 'required'); $field('phone', 'Telefon *', 'tel', 'required'); ?></div>
      <div class="form-row"><?php $field('email', 'E-posta', 'email'); $field('company', 'Firma'); ?></div>
    </div>
    <div class="card">
      <h3>Cihaz</h3>
      <div class="form-row form-row--3">
        <label class="field"><span>Marka</span><input type="text" name="brand" value="<?= e($req['brand']) ?>" list="brand-list"></label>
        <?php $field('device', 'Cihaz türü *', 'text', 'required'); $field('model', 'Model'); ?>
      </div>
      <datalist id="brand-list"><?php foreach ($brands as $b): ?><option value="<?= e($b) ?>"><?php endforeach; ?></datalist>
      <label class="switch"><input type="checkbox" name="warranty" value="1"<?= $req['warranty'] ? ' checked' : '' ?>><span class="switch__ui"></span><span>Garanti kapsamında</span></label>
      <label class="field"><span>Arıza açıklaması</span><textarea name="issue" rows="4"><?= e($req['issue']) ?></textarea></label>
    </div>
  </div>
  <aside class="editor-side">
    <div class="card sticky">
      <h3>Durum</h3>
      <div class="status-picker">
        <?php foreach ($statuses as $k => [$l, $c]): ?>
          <label><input type="radio" name="status" value="<?= $k ?>"<?= $req['status'] === $k ? ' checked' : '' ?>><span class="status status--<?= $c ?>"><?= e($l) ?></span></label>
        <?php endforeach; ?>
      </div>
      <label class="field"><span>Geçmişe not ekle <small class="muted">(müşteri görür)</small></span><input type="text" name="log_note" maxlength="500" placeholder="Örn. Rotor değişimi yapıldı."></label>
      <label class="field"><span>Müşteriye görünen servis notu</span><textarea name="admin_note" rows="3" placeholder="Örn. Onarım ücreti: 1.250 ₺ — onayınızı bekliyoruz."><?= e($req['admin_note']) ?></textarea></label>
      <button class="btn btn--primary btn--block"><?= icon('check') ?> <?= $id ? 'Kaydet' : 'Kaydı Oluştur' ?></button>
    </div>
    <?php if ($log): ?>
    <div class="card">
      <h3>Süreç geçmişi</h3>
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
<?php if ($id): ?><form id="delete-form" method="post" action="<?= admin_url('requests', ['a' => 'delete', 'id' => $id]) ?>" data-confirm="Bu servis kaydı ve geçmişi silinsin mi?"><?= csrf_field() ?></form><?php endif; ?>
