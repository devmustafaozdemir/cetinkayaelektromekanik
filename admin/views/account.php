<div class="grid-2">
  <form method="post" class="card form-stack" autocomplete="off">
    <?= csrf_field() ?>
    <h3>Profil ve şifre</h3>
    <label class="field"><span>Kullanıcı adı</span><input value="<?= e($user['username']) ?>" disabled></label>
    <label class="field"><span>Ad Soyad</span><input name="name" value="<?= e($user['name']) ?>"></label>
    <hr>
    <p class="muted small">Şifrenizi değiştirmek istemiyorsanız aşağıyı boş bırakın.</p>
    <label class="field"><span>Mevcut şifre</span><input type="password" name="current_password" autocomplete="current-password"></label>
    <div class="form-row">
      <label class="field"><span>Yeni şifre</span><input type="password" name="new_password" minlength="8" autocomplete="new-password"></label>
      <label class="field"><span>Yeni şifre (tekrar)</span><input type="password" name="new_password2" minlength="8" autocomplete="new-password"></label>
    </div>
    <div><button class="btn btn--primary"><?= icon('check') ?> Kaydet</button></div>
  </form>
  <div class="card">
    <h3>Yöneticiler</h3>
    <ul class="list">
      <?php foreach ($users as $u): ?>
        <li><div class="list__row"><span class="avatar avatar--soft"><?= e(mb_strtoupper(mb_substr($u['name'] ?: $u['username'], 0, 1))) ?></span><div><strong><?= e($u['name']) ?></strong><small>@<?= e($u['username']) ?> · Son giriş: <?= e($u['last_login'] ? tr_date($u['last_login'], true) : '—') ?></small></div>
          <?php if ((int)$u['id'] !== (int)$user['id']): ?><form method="post" action="<?= admin_url('account', ['a' => 'deluser', 'id' => $u['id']]) ?>" data-confirm="Bu yönetici silinsin mi?"><?= csrf_field() ?><button class="icon-btn icon-btn--danger" title="Sil"><?= icon('trash') ?></button></form><?php endif; ?>
        </div></li>
      <?php endforeach; ?>
    </ul>
    <details class="add-user">
      <summary class="btn"><?= icon('plus') ?> Yönetici ekle</summary>
      <form method="post" action="<?= admin_url('account', ['a' => 'adduser']) ?>" class="form-stack" autocomplete="off">
        <?= csrf_field() ?>
        <label class="field"><span>Ad Soyad</span><input name="name"></label>
        <label class="field"><span>Kullanıcı adı</span><input name="username" required></label>
        <label class="field"><span>Şifre</span><input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
        <button class="btn btn--primary">Ekle</button>
      </form>
    </details>
  </div>
</div>
