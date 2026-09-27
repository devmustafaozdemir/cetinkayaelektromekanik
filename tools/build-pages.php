<?php
declare(strict_types=1);

/**
 * Builds a static, read-only preview of the site into docs/ for GitHub Pages.
 * GitHub Pages cannot run PHP, so forms and the admin panel are shown as
 * non-functional snapshots.
 *
 * Usage: php tools/build-pages.php [base-path] [public-url]
 *   php tools/build-pages.php /cetinkayaelektromekanik/ https://devmustafaozdemir.github.io/cetinkayaelektromekanik
 */

$root = dirname(__DIR__);
$base = rtrim($argv[1] ?? '/cetinkayaelektromekanik/', '/') . '/';
$publicUrl = rtrim($argv[2] ?? 'https://devmustafaozdemir.github.io/cetinkayaelektromekanik', '/');
$out = $root . '/docs';
$port = 8765;
$host = "http://127.0.0.1:{$port}";

$tmp = sys_get_temp_dir() . '/cem-pages-' . bin2hex(random_bytes(4));
mkdir($tmp);
$dbPath = $tmp . '/site.sqlite';
$uploadsBefore = glob($root . '/uploads/*') ?: [];

/* ---------- Demo data (in a throw-away database) ---------- */
putenv('APP_DB_PATH=' . $dbPath);
$_SERVER['HTTP_HOST'] = '127.0.0.1';
require $root . '/app/bootstrap.php';
q("INSERT INTO users(username, name, password_hash, created_at) VALUES('demo', 'Demo Yönetici', ?, now_tr())", [password_hash('demo-preview', PASSWORD_DEFAULT)]);
$demo = [
    ['TK-26DEMO1', 'product', 'Ahmet Yılmaz', 'Yılmaz Yapı', '0532 000 12 34', 'İzmit / Kocaeli', 1, 'Meksis Galvaniz Modüler Su Deposu', 'Modüler Su Depoları', '30 m³', '24 daireli site projemiz için bodruma kurulacak depo fiyatı istiyoruz.', 'quoted', '3x5x2 m öneri yapıldı, montaj dahil fiyat iletildi.',
        [['new', 'Web sitesinden teklif talebi.', '-3 days'], ['contacted', 'Telefonla görüşüldü, alan ölçüleri alındı.', '-2 days'], ['quoted', 'Teklif e-posta ile gönderildi.', '-1 day']]],
    ['TK-26DEMO2', 'product', 'Elif Şahin', 'Şahin Otel', '0262 000 55 66', 'Kartepe / Kocaeli', 5, 'Grundfos Çok Pompalı Hidrofor Seti', 'Hidrofor Sistemleri', '1 set', 'Otelimiz için mevcut hidroforun yenilenmesi gerekiyor.', 'won', 'Sipariş onaylandı, montaj planlanıyor.',
        [['new', 'Web sitesinden teklif talebi.', '-6 days'], ['quoted', 'Teklif iletildi.', '-4 days'], ['won', 'Sipariş onaylandı.', '-1 day']]],
    ['TK-26DEMO3', 'project', 'Mehmet Demir', '', '0544 000 77 88', 'Gebze / Kocaeli', null, '', 'Birden fazla / proje', '', 'Fabrika için yangın ve kullanma suyu deposu ile pompa sistemi projesi.', 'new', '',
        [['new', 'Web sitesinden teklif talebi.', '-2 hours']]],
    ['TK-26DEMO4', 'product', 'Can Arslan', '', '0555 000 33 44', 'Derince / Kocaeli', 11, 'Standart Pompa Derin Kuyu Dalgıç Pompası', 'Dalgıç Pompalar', '1 adet', '80 metre kuyu için pompa lazım.', 'new', '',
        [['new', 'Hızlı teklif formundan geldi.', '-5 hours']]],
];
foreach ($demo as [$code, $type, $name, $company, $phone, $city, $pid, $pname, $cat, $qty, $msg, $status, $note, $log]) {
    $created = date('Y-m-d H:i:s', strtotime($log[0][2]));
    q('INSERT INTO quotes(code, type, name, company, phone, city, product_id, product_name, category, quantity, message, status, admin_note, created_at, updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
        [$code, $type, $name, $company, $phone, $city, $pid, $pname, $cat, $qty, $msg, $status, $note, $created, date('Y-m-d H:i:s', strtotime(end($log)[2]))]);
    $qid = db()->lastInsertId();
    foreach ($log as [$st, $n, $when]) {
        q('INSERT INTO quote_log(quote_id, status, note, created_at) VALUES(?,?,?,?)', [$qid, $st, $n, date('Y-m-d H:i:s', strtotime($when))]);
    }
}
q("INSERT INTO messages(name, email, phone, subject, message, created_at) VALUES('Ayşe Kara', 'ayse@example.com', '0555 000 11 22', 'Kurumsal bakım anlaşması', 'Merhaba, firmamızdaki 25 adet el aleti için periyodik bakım anlaşması yapmak istiyoruz. Bilgi verebilir misiniz?', ?)", [date('Y-m-d H:i:s', strtotime('-1 hour'))]);
q('UPDATE posts SET views = ? WHERE id = 1', [148]);
q('UPDATE posts SET views = ? WHERE id = 2', [96]);
q('UPDATE posts SET views = ? WHERE id = 3', [61]);

/* ---------- Local server ---------- */
$env = array_merge(getenv(), ['APP_DB_PATH' => $dbPath]);
$proc = proc_open([PHP_BINARY, '-S', "127.0.0.1:{$port}", 'router.php'], [1 => ['file', $tmp . '/server.log', 'a'], 2 => ['file', $tmp . '/server.log', 'a']], $pipes, $root, $env);
register_shutdown_function(function () use ($proc, $tmp) {
    proc_terminate($proc);
    array_map('unlink', glob($tmp . '/*'));
    @rmdir($tmp);
});
for ($i = 0; $i < 50 && !@file_get_contents($host . '/robots.txt'); $i++) {
    usleep(100000);
}

$cookie = $tmp . '/cookies.txt';
function fetch(string $url, ?array $post = null): array
{
    global $cookie;
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEJAR => $cookie, CURLOPT_COOKIEFILE => $cookie]);
    if ($post !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = (string)curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, $body];
}

/* ---------- Output ---------- */
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
function copy_dir(string $src, string $dst): void
{
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS)) as $f) {
        if ($f->getFilename() === '.htaccess' || $f->getFilename() === '.gitkeep') continue;
        $target = $dst . substr($f->getPathname(), strlen($src));
        if (!is_dir(dirname($target))) mkdir(dirname($target), 0775, true);
        copy($f->getPathname(), $target);
    }
}

/** Admin URL (?p=..&a=..&id=..&tab=..) → static folder name. */
function admin_slug(string $query): string
{
    parse_str(html_entity_decode($query), $q);
    $p = $q['p'] ?? 'dashboard';
    $slug = $p;
    if (!empty($q['a']) && $q['a'] !== 'index') $slug .= '-' . $q['a'] . (!empty($q['id']) ? '-' . $q['id'] : '');
    if (!empty($q['tab'])) $slug .= '-' . $q['tab'];
    return preg_replace('/[^a-z0-9-]/', '', $slug);
}

$adminPages = [];
function rewrite(string $html, bool $isAdmin = false): string
{
    global $base, $host, $publicUrl, $adminPages;
    $html = str_replace($host, $publicUrl, $html);
    // Admin links → static snapshots (fall back to the section's list page)
    $html = preg_replace_callback('#(href|action)="/admin/\?([^"]*)"#', function ($m) use ($base, &$adminPages) {
        $slug = admin_slug($m[2]);
        if (!isset($adminPages[$slug])) {
            parse_str(html_entity_decode($m[2]), $q);
            $slug = $q['p'] ?? 'dashboard';
            if (!isset($adminPages[$slug])) $slug = 'dashboard';
        }
        return $m[1] . '="' . $base . 'yonetim/' . ($slug === 'dashboard' ? '' : $slug . '/') . '"';
    }, $html);
    $html = preg_replace('#(href|action)="/admin/"#', '$1="' . $base . 'yonetim/"', $html);
    // Query-string pages → pre-rendered static folders
    $html = preg_replace_callback('#href="/urunler\?marka=([^"&]+)"#', fn($m) => 'href="' . $base . 'urunler/marka/' . slugify(rawurldecode($m[1])) . '/"', $html);
    $html = preg_replace('#href="/urunler/kategori/([a-z0-9-]+)\?marka=[^"]*"#', 'href="' . $base . 'urunler/kategori/$1/"', $html);
    $html = preg_replace('#href="/teklif-al\?urun=([a-z0-9-]+)"#', 'href="' . $base . 'teklif-al/urun/$1/"', $html);
    // Site-absolute URLs → base path
    $html = preg_replace_callback('#(href|src|action)="/(?!/)([^"]*)"#', function ($m) use ($base) {
        $path = $m[2];
        if (str_starts_with('/' . $path, $base)) {
            return $m[0]; // already rewritten
        }
        if ($path !== '' && !str_contains($path, '.') && !str_contains($path, '?') && !str_contains($path, '#')) $path .= '/';
        return $m[1] . '="' . $base . $path . '"';
    }, $html);
    return $html;
}

function preview_chrome(string $html, bool $isAdmin): string
{
    global $base;
    $banner = '<div id="gh-preview" style="position:relative;z-index:100;background:#fef3c7;color:#78350f;font:500 13px/1.4 system-ui,sans-serif;padding:8px 16px;text-align:center">'
        . '<strong>Önizleme sürümü</strong> — bu kopya GitHub Pages üzerinde çalışır; formlar ve yönetim paneli burada işlem yapmaz. '
        . ($isAdmin ? '<a href="' . $base . '" style="text-decoration:underline;font-weight:600">Siteye dön</a>' : '<a href="' . $base . 'yonetim/" style="text-decoration:underline;font-weight:600">Yönetim paneli önizlemesi →</a>')
        . '</div>';
    $script = <<<JS
<script>
document.addEventListener('submit', function (e) {
  var f = e.target;
  if (f.method.toLowerCase() === 'get' && f.querySelector('[name=q]')) {
    e.preventDefault();
    alert('Önizleme sürümünde arama çalışmaz. Canlı sitede yazılarda arama yapılır.');
    return;
  }
  e.preventDefault();
  e.stopImmediatePropagation();
  alert('Önizleme sürümünde formlar gönderilmez. Canlı sitede bu işlem kaydedilir.');
}, true);
</script>
JS;
    $html = preg_replace('#<body([^>]*)>#', '<body$1>' . $banner, $html, 1);
    return str_replace('</body>', $script . '</body>', $html);
}

rrmdir($out);
mkdir($out, 0775, true);

/* ---------- Public pages ---------- */
[, $sitemap] = fetch($host . '/sitemap.xml');
preg_match_all('#<loc>([^<]+)</loc>#', $sitemap, $m);
$paths = array_map(fn($u) => parse_url(html_entity_decode($u), PHP_URL_PATH), $m[1]);

// Admin page list must be known before rewriting links
$adminList = [
    '?p=dashboard', '?p=quotes', '?p=quotes&a=edit&id=1', '?p=quotes&a=edit&id=2', '?p=quotes&a=edit&id=3', '?p=quotes&a=edit&id=4', '?p=quotes&a=edit',
    '?p=products', '?p=products&a=edit', '?p=products&a=edit&id=1', '?p=products&a=edit&id=5', '?p=products&a=edit&id=8', '?p=products&a=edit&id=11', '?p=pcategories',
    '?p=messages', '?p=posts', '?p=posts&a=edit', '?p=posts&a=edit&id=1', '?p=posts&a=edit&id=2', '?p=posts&a=edit&id=3', '?p=posts&a=edit&id=4',
    '?p=categories', '?p=services', '?p=services&a=edit&id=1', '?p=services&a=edit', '?p=faqs',
    '?p=settings', '?p=settings&tab=contact', '?p=settings&tab=home', '?p=settings&tab=about', '?p=settings&tab=social', '?p=account',
    '?p=messages&a=view&id=1', // last: viewing marks the demo message as read
];
foreach ($adminList as $a) $adminPages[admin_slug(substr($a, 1))] = $a;

foreach ($paths as $path) {
    [$code, $html] = fetch($host . $path);
    if ($code !== 200) {
        fwrite(STDERR, "HATA {$code} {$path}\n");
        continue;
    }
    write(($path === '/' ? '' : $path . '/') . 'index.html', preview_chrome(rewrite($html), false));
}
foreach (brands() as $b) {
    [, $html] = fetch($host . '/urunler?marka=' . rawurlencode($b['name']));
    write('urunler/marka/' . $b['slug'] . '/index.html', preview_chrome(rewrite($html), false));
}
foreach (q_all('SELECT slug FROM products WHERE active = 1') as $pr) {
    [, $html] = fetch($host . '/teklif-al?urun=' . $pr['slug']);
    write('teklif-al/urun/' . $pr['slug'] . '/index.html', preview_chrome(rewrite($html), false));
}
[, $html] = fetch($host . '/bulunamadi');
write('404.html', preview_chrome(rewrite($html), false));
write('robots.txt', "User-agent: *\nDisallow: /\n");

/* ---------- Admin snapshots ---------- */
[, $login] = fetch($host . '/admin/?p=login');
preg_match('/name="_csrf" value="([^"]+)"/', $login, $t);
fetch($host . '/admin/?p=login', ['_csrf' => $t[1] ?? '', 'username' => 'demo', 'password' => 'demo-preview']);
foreach ($adminPages as $slug => $query) {
    [$code, $html] = fetch($host . '/admin/' . $query);
    if ($code !== 200) {
        fwrite(STDERR, "HATA {$code} admin {$query}\n");
        continue;
    }
    // Admin pages are self-contained; strip the CSRF token from the static copy
    $html = preg_replace('/(name="_csrf" value=")[^"]*"/', '$1"', $html);
    $html = preg_replace('/(<meta name="csrf" content=")[^"]*"/', '$1"', $html);
    write('yonetim/' . ($slug === 'dashboard' ? '' : $slug . '/') . 'index.html', preview_chrome(rewrite($html, true), true));
}

/* ---------- Assets ---------- */
copy_dir($root . '/assets', $out . '/assets');
if (is_dir($root . '/uploads')) copy_dir($root . '/uploads', $out . '/uploads');
touch($out . '/.nojekyll');

// Remove any uploads created while building
foreach (array_diff(glob($root . '/uploads/*') ?: [], $uploadsBefore) as $f) @unlink($f);

$count = iterator_count(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($out, FilesystemIterator::SKIP_DOTS)));
echo "Tamamlandı: {$count} dosya → docs/\nAdres: {$publicUrl}/\n";
