<?php
$user = current_user();
$newQuotes = (int)q_val("SELECT COUNT(*) FROM quotes WHERE status = 'new'");
$unread = (int)q_val('SELECT COUNT(*) FROM messages WHERE is_read = 0');
$menu = [
    ['dashboard', 'Genel Bakış', 'home', 0],
    ['quotes', 'Teklif Talepleri', 'calculator', $newQuotes],
    ['messages', 'Mesajlar', 'inbox', $unread],
    [null, 'Katalog'],
    ['products', 'Ürünler', 'package', 0],
    ['pcategories', 'Ürün Kategorileri', 'grid', 0],
    [null, 'İçerik'],
    ['posts', 'Blog Yazıları', 'file-text', 0],
    ['categories', 'Blog Kategorileri', 'tag', 0],
    ['services', 'Hizmetler', 'layers', 0],
    ['faqs', 'SSS', 'help', 0],
    [null, 'Sistem'],
    ['settings', 'Site Ayarları', 'settings', 0],
    ['account', 'Hesabım', 'users', 0],
];
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<title><?= e($title ?? 'Yönetim') ?> | Yönetim Paneli</title>
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<?php if (!empty($editor)): ?><link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet"><?php endif; ?>
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body>
<div class="app">
  <aside class="sidebar" id="sidebar">
    <a href="/admin/" class="sidebar__brand"><span class="logo-mark"><?= icon('panels') ?></span><div><strong>Çetinkaya</strong><small>Yönetim Paneli</small></div></a>
    <nav class="sidebar__nav">
      <?php foreach ($menu as $m): ?>
        <?php if ($m[0] === null): ?>
          <div class="sidebar__label"><?= e($m[1]) ?></div>
        <?php else: ?>
          <a href="<?= admin_url($m[0]) ?>" class="<?= $p === $m[0] ? 'is-active' : '' ?>"><?= icon($m[2]) ?><span><?= e($m[1]) ?></span><?php if ($m[3]): ?><em class="count"><?= $m[3] ?></em><?php endif; ?></a>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar__foot">
      <a href="/" target="_blank" class="sidebar__site"><?= icon('external') ?> Siteyi görüntüle</a>
    </div>
  </aside>
  <div class="sidebar-backdrop" data-sidebar-close></div>
  <div class="main">
    <header class="topnav">
      <button class="icon-btn menu-btn" type="button" data-sidebar-open aria-label="Menü"><?= icon('menu') ?></button>
      <h1 class="topnav__title"><?= e($title ?? '') ?></h1>
      <div class="topnav__right">
        <a href="<?= admin_url('quotes', ['a' => 'edit']) ?>" class="btn btn--primary btn--sm hide-sm"><?= icon('plus') ?> Teklif Kaydı</a>
        <div class="user-menu">
          <button type="button" class="user-menu__btn" data-dropdown><span class="avatar"><?= e(mb_strtoupper(mb_substr($user['name'] ?: $user['username'], 0, 1))) ?></span><span class="hide-sm"><?= e($user['name'] ?: $user['username']) ?></span><?= icon('chevron-down') ?></button>
          <div class="dropdown">
            <a href="<?= admin_url('account') ?>"><?= icon('users') ?> Hesabım</a>
            <form method="post" action="<?= admin_url('logout') ?>"><?= csrf_field() ?><button><?= icon('logout') ?> Çıkış Yap</button></form>
          </div>
        </div>
      </div>
    </header>
    <div class="content">
      <?php foreach (flashes() as $f): ?>
        <div class="toast toast--<?= e($f['type']) ?>" role="status"><?= icon($f['type'] === 'success' ? 'check-circle' : 'help') ?><span><?= e($f['msg']) ?></span><button type="button" aria-label="Kapat" data-dismiss><?= icon('x') ?></button></div>
      <?php endforeach; ?>
      <?= $content ?>
    </div>
  </div>
</div>
<?php if (!empty($editor)): ?><script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script><?php endif; ?>
<script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
