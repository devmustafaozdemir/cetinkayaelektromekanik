<?php
$siteName = setting('site_name', 'Çetinkaya Elektromekanik');
$pageTitle = isset($title) ? $title . ' | ' . $siteName : $siteName . ' | ' . setting('site_tagline');
$metaDesc = $description ?? setting('meta_description');
$canonical = base_url() . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$active = $active ?? '';
?><!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($metaDesc) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<?php if (!empty($noindex)): ?><meta name="robots" content="noindex, follow"><?php endif; ?>
<meta property="og:site_name" content="<?= e($siteName) ?>">
<meta property="og:title" content="<?= e($title ?? $siteName) ?>">
<meta property="og:description" content="<?= e($metaDesc) ?>">
<meta property="og:type" content="<?= e($og_type ?? 'website') ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:locale" content="tr_TR">
<?php if (!empty($og_image)): ?><meta property="og:image" content="<?= e($og_image) ?>"><meta name="twitter:card" content="summary_large_image"><?php endif; ?>
<meta name="theme-color" content="#10303d">
<link rel="icon" href="<?= asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wdth,wght@100..125,500..800&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset('css/site.css') ?>">
<?php if (!empty($schema)): ?><script type="application/ld+json"><?= json_encode(array_filter($schema, fn($v) => $v !== null), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script><?php endif; ?>
</head>
<body>
<a class="skip-link" href="#main">İçeriğe geç</a>
<?php partial('header', ['active' => $active]); ?>
<main id="main">
<?= $content ?>
</main>
<?php partial('footer'); ?>
<script src="<?= asset('js/site.js') ?>" defer></script>
</body>
</html>
