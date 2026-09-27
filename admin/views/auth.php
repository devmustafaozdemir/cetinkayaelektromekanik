<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $mode === 'setup' ? 'Kurulum' : 'Giriş' ?> | Yönetim Paneli</title>
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="auth-body">
  <div class="auth">
    <div class="auth__side">
      <div class="auth__brand"><span class="logo-mark"><?= icon('zap') ?></span><div><strong>Çetinkaya</strong><small>Yönetim Paneli</small></div></div>
      <div>
        <h1>Servis, blog ve site içeriğinizi tek yerden yönetin.</h1>
        <ul>
          <li><?= icon('clipboard') ?> Servis taleplerini takip edin, durum güncelleyin</li>
          <li><?= icon('file-text') ?> Blog yazıları yayınlayın</li>
          <li><?= icon('settings') ?> Hizmetleri ve iletişim bilgilerini düzenleyin</li>
        </ul>
      </div>
      <small>© <?= date('Y') ?> <?= e(setting('site_name')) ?></small>
    </div>
    <div class="auth__main">
      <form method="post" class="auth__form" autocomplete="on">
        <?= csrf_field() ?>
        <?php if ($mode === 'setup'): ?>
          <h2>Kurulum</h2>
          <p class="muted">İlk yönetici hesabını oluşturun.</p>
        <?php else: ?>
          <h2>Tekrar hoş geldiniz</h2>
          <p class="muted">Devam etmek için giriş yapın.</p>
        <?php endif; ?>
        <?php foreach ($errors as $err): ?><div class="alert alert--error"><?= e($err) ?></div><?php endforeach; ?>
        <?php if ($mode === 'setup'): ?>
          <label class="field"><span>Ad Soyad</span><input name="name" value="<?= e($_POST['name'] ?? '') ?>" autocomplete="name"></label>
        <?php endif; ?>
        <label class="field"><span>Kullanıcı adı</span><input name="username" value="<?= e($_POST['username'] ?? '') ?>" required autofocus autocomplete="username"></label>
        <label class="field"><span>Şifre</span><input type="password" name="password" required autocomplete="<?= $mode === 'setup' ? 'new-password' : 'current-password' ?>"<?= $mode === 'setup' ? ' minlength="8"' : '' ?>></label>
        <?php if ($mode === 'setup'): ?>
          <label class="field"><span>Şifre (tekrar)</span><input type="password" name="password2" required autocomplete="new-password" minlength="8"></label>
        <?php endif; ?>
        <button class="btn btn--primary btn--block"><?= $mode === 'setup' ? 'Hesabı Oluştur' : 'Giriş Yap' ?></button>
        <a href="/" class="auth__back"><?= icon('arrow-left') ?> Siteye dön</a>
      </form>
    </div>
  </div>
</body>
</html>
