<?php
declare(strict_types=1);

/**
 * Production build: reads published content from Supabase and renders the whole
 * public site as static HTML (for GitHub Pages), plus the admin app at /yonetim/.
 *
 * Environment:
 *   SUPABASE_URL        https://xxxx.supabase.co            (required)
 *   SUPABASE_ANON_KEY   public anon key                      (required)
 *   SITE_URL            public address incl. base path, e.g. https://cetinkayaelektromekanik.com.tr
 *                       or https://devmustafaozdemir.github.io/cetinkayaelektromekanik
 *   OUT_DIR             output folder (default: _site)
 *   CUSTOM_DOMAIN       writes a CNAME file when set (e.g. cetinkayaelektromekanik.com.tr)
 *
 * Usage: php tools/build-site.php
 */

$root = dirname(__DIR__);
$sbUrl = rtrim((string)getenv('SUPABASE_URL'), '/');
$sbKey = (string)getenv('SUPABASE_ANON_KEY');
if ($sbUrl === '' || $sbKey === '') {
    fwrite(STDERR, "SUPABASE_URL ve SUPABASE_ANON_KEY ortam değişkenleri gerekli.\n");
    exit(1);
}
$siteUrl = rtrim((string)(getenv('SITE_URL') ?: 'https://devmustafaozdemir.github.io/cetinkayaelektromekanik'), '/');
$base = rtrim((string)(parse_url($siteUrl, PHP_URL_PATH) ?? ''), '/') . '/';
$out = getenv('OUT_DIR') ?: $root . '/_site';
$port = 8766;
$host = "http://127.0.0.1:{$port}";

$tmp = sys_get_temp_dir() . '/cem-build-' . bin2hex(random_bytes(4));
mkdir($tmp);
$dbPath = $tmp . '/site.sqlite';

/* ---------- 1. Supabase → temporary SQLite ---------- */
function sb_get(string $table): array
{
    global $sbUrl, $sbKey;
    $rows = [];
    for ($offset = 0; ; $offset += 1000) {
        $ch = curl_init("{$sbUrl}/rest/v1/{$table}?select=*&order=id.asc&limit=1000&offset={$offset}");
        if ($table === 'settings') curl_setopt($ch, CURLOPT_URL, "{$sbUrl}/rest/v1/settings?select=*&limit=1000&offset={$offset}");
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 30, CURLOPT_HTTPHEADER => array_merge(["apikey: {$sbKey}", 'Accept: application/json'], str_starts_with($sbKey, 'eyJ') ? ["Authorization: Bearer {$sbKey}"] : [])]);
        $body = (string)curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) {
            fwrite(STDERR, "Supabase okuma hatası ({$table}): HTTP {$code} {$body}\n");
            exit(1);
        }
        $page = json_decode($body, true) ?: [];
        array_push($rows, ...$page);
        if (count($page) < 1000) return $rows;
    }
}

putenv('APP_DB_PATH=' . $dbPath);
putenv('APP_NO_SEED=1');
$_SERVER['HTTP_HOST'] = '127.0.0.1';
require $root . '/app/bootstrap.php';

$toLocal = function ($v) {
    if ($v === null || $v === '') return $v;
    $t = strtotime((string)$v);
    return $t ? date('Y-m-d H:i:s', $t) : $v;
};
$tables = [
    'settings'           => [],
    'categories'         => [],
    'product_categories' => [],
    'products'           => ['created_at', 'updated_at'],
    'posts'              => ['published_at', 'created_at', 'updated_at'],
    'services'           => [],
    'faqs'               => [],
    'refs'               => [],
    'partners'           => [],
];
$html = ['products' => 'content', 'posts' => 'content', 'services' => 'content'];
$counts = [];
db()->beginTransaction();
foreach ($tables as $table => $dates) {
    $rows = sb_get($table);
    $counts[$table] = count($rows);
    $cols = array_column(db()->query("PRAGMA table_info({$table})")->fetchAll(), 'name');
    foreach ($rows as $r) {
        $r = array_intersect_key($r, array_flip($cols));
        foreach ($dates as $d) if (array_key_exists($d, $r)) $r[$d] = $toLocal($r[$d]);
        if (isset($html[$table], $r[$html[$table]])) $r[$html[$table]] = sanitize_html((string)$r[$html[$table]]);
        $r = array_map(fn($v) => is_bool($v) ? (int)$v : $v, $r);
        $keys = array_keys($r);
        q("INSERT OR REPLACE INTO {$table} (" . implode(',', $keys) . ') VALUES (' . implode(',', array_fill(0, count($keys), '?')) . ')', array_values($r));
    }
}
db()->commit();
if (($counts['settings'] ?? 0) === 0) {
    fwrite(STDERR, "Supabase'de ayar bulunamadı. Önce supabase/schema.sql ve supabase/seed.sql çalıştırılmalı.\n");
    exit(1);
}
echo 'Supabase: ' . implode(', ', array_map(fn($k, $v) => "$k $v", array_keys($counts), $counts)) . "\n";

/* ---------- 2. Render with the PHP templates ---------- */
$env = array_merge(getenv(), ['APP_DB_PATH' => $dbPath, 'APP_NO_SEED' => '1']);
$proc = proc_open([PHP_BINARY, '-S', "127.0.0.1:{$port}", 'router.php'], [1 => ['file', $tmp . '/server.log', 'a'], 2 => ['file', $tmp . '/server.log', 'a']], $pipes, $root, $env);
register_shutdown_function(function () use ($proc, $tmp) {
    proc_terminate($proc);
    foreach (glob($tmp . '/*') as $f) @unlink($f);
    @rmdir($tmp);
});
for ($i = 0; $i < 50 && !@file_get_contents($host . '/robots.txt'); $i++) usleep(100000);

function fetch(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true]);
    $body = (string)curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $body];
}
function rrmdir(string $dir): void
{
    if (!is_dir($dir)) return;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $f) {
        $f->isDir() ? rmdir($f->getPathname()) : unlink($f->getPathname());
    }
    rmdir($dir);
}
function write(string $path, string $content): void
{
    global $out;
    $file = $out . '/' . ltrim($path, '/');
    if (!is_dir(dirname($file))) mkdir(dirname($file), 0775, true);
    file_put_contents($file, $content);
}
function copy_dir(string $src, string $dst, array $skipDirs = []): void
{
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS)) as $f) {
        $rel = substr($f->getPathname(), strlen($src) + 1);
        foreach ($skipDirs as $s) if (str_starts_with($rel, $s . '/')) continue 2;
        if (in_array($f->getFilename(), ['.htaccess', '.gitkeep'], true)) continue;
        $target = $dst . '/' . $rel;
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0775, true);
        copy($f->getPathname(), $target);
    }
}
function rewrite(string $html): string
{
    global $base, $host, $siteUrl, $sbUrl, $sbKey;
    $html = str_replace($host, $siteUrl, $html);
    $html = preg_replace_callback('#href="/urunler\?marka=([^"&]+)"#', fn($m) => 'href="' . $base . 'urunler/marka/' . slugify(rawurldecode($m[1])) . '/"', $html);
    $html = preg_replace('#href="/urunler/kategori/([a-z0-9-]+)\?marka=[^"]*"#', 'href="' . $base . 'urunler/kategori/$1/"', $html);
    $html = preg_replace('#href="/teklif-al\?urun=([a-z0-9-]+)"#', 'href="' . $base . 'teklif-al/urun/$1/"', $html);
    $html = preg_replace_callback('#(href|src|action|data-viewer)="/(?!/)([^"]*)"#', function ($m) use ($base) {
        $path = $m[2];
        if ($base !== '/' && str_starts_with('/' . $path, $base)) return $m[0];
        // folder URLs need a trailing slash on static hosting, also before a query string (/depo-tasarla/?olcu=…)
        if ($path !== '' && !str_contains($path, '.') && !str_contains($path, '#')) {
            [$p, $q] = array_pad(explode('?', $path, 2), 2, null);
            $path = rtrim($p, '/') . '/' . ($q !== null ? '?' . $q : '');
        }
        return $m[1] . '="' . $base . $path . '"';
    }, $html);
    // Admin links in page chrome point to the Supabase admin app
    $html = preg_replace('#href="' . preg_quote($base, '#') . 'admin/[^"]*"#', 'href="' . $base . 'yonetim/"', $html);
    $meta = '<meta name="sb-url" content="' . htmlspecialchars($sbUrl) . '"><meta name="sb-key" content="' . htmlspecialchars($sbKey) . '"><meta name="site-base" content="' . htmlspecialchars($base) . '">';
    return str_replace('</head>', $meta . '</head>', $html);
}

rrmdir($out);
mkdir($out, 0775, true);

[, $sitemap] = fetch($host . '/sitemap.xml');
preg_match_all('#<loc>([^<]+)</loc>#', $sitemap, $m);
$paths = array_map(fn($u) => parse_url(html_entity_decode($u), PHP_URL_PATH), $m[1]);
$pages = 0;
foreach ($paths as $path) {
    [$code, $page] = fetch($host . $path);
    if ($code !== 200) {
        fwrite(STDERR, "HATA {$code} {$path}\n");
        exit(1);
    }
    write(($path === '/' ? '' : $path . '/') . 'index.html', rewrite($page));
    $pages++;
}
foreach (brands() as $b) {
    [, $page] = fetch($host . '/urunler?marka=' . rawurlencode($b['name']));
    write('urunler/marka/' . $b['slug'] . '/index.html', rewrite($page));
    $pages++;
}
foreach (q_all('SELECT slug FROM products WHERE active = 1') as $pr) {
    [, $page] = fetch($host . '/teklif-al?urun=' . $pr['slug']);
    write('teklif-al/urun/' . $pr['slug'] . '/index.html', rewrite($page));
    $pages++;
}
[, $page] = fetch($host . '/bulunamadi');
write('404.html', rewrite($page));

// sitemap/robots with public addresses (trailing slashes match the static folders)
$sitemap = preg_replace_callback('#<loc>' . preg_quote($host, '#') . '([^<]*)</loc>#', fn($m) => '<loc>' . $siteUrl . rtrim($m[1], '/') . '/</loc>', $sitemap);
write('sitemap.xml', $sitemap);
write('robots.txt', "User-agent: *\nDisallow: {$base}yonetim/\n\nSitemap: {$siteUrl}/sitemap.xml\n");

/* ---------- 3. Assets + admin app ---------- */
copy_dir($root . '/assets', $out . '/assets', ['src']);
$adminHtml = (string)file_get_contents($root . '/admin-app/index.html');
$adminHtml = str_replace(['{{BASE}}', '{{SB_URL}}', '{{SB_KEY}}', '{{SITE_URL}}', '{{V}}'], [$base, htmlspecialchars($sbUrl), htmlspecialchars($sbKey), htmlspecialchars($siteUrl), (string)time()], $adminHtml);
write('yonetim/index.html', $adminHtml);
touch($out . '/.nojekyll');
if ($domain = getenv('CUSTOM_DOMAIN')) write('CNAME', trim($domain) . "\n");

echo "Tamamlandı: {$pages} sayfa → " . $out . "\nAdres: {$siteUrl}/\n";
