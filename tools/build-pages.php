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
    ['CE-26DEMO1', 'Ahmet Yılmaz', '0532 000 12 34', 'Makita', 'Kırıcı-Delici', 'HR2470', 1, 'Çalışırken kıvılcım yapıyor, güç kaybı var.', 'repairing', 'Kömür ve rotor değişimi onaylandı. Tahmini teslim: 2 iş günü.',
        [['received', 'Cihaz servise teslim alındı.', '-3 days'], ['inspecting', 'Arıza tespiti yapılıyor.', '-2 days'], ['quote', 'Rotor ve kömür değişimi gerekiyor, fiyat bilgisi iletildi.', '-1 day'], ['repairing', 'Müşteri onayı alındı, onarıma başlandı.', '-3 hours']]],
    ['CE-26DEMO2', 'Kaya Yapı Ltd.', '0262 000 55 66', 'Metabo', 'Avuç Taşlama', 'W 750-125', 0, 'Şalter arızalı, çalışmıyor.', 'ready', '',
        [['received', 'Online talep oluşturuldu.', '-5 days'], ['inspecting', '', '-4 days'], ['repairing', 'Şalter değiştirildi.', '-2 days'], ['ready', 'Cihaz test edildi, teslime hazır.', '-1 day']]],
    ['CE-26DEMO3', 'Mehmet Demir', '0544 000 77 88', 'HiKOKI', 'Daire Testere', 'C7ST', 1, 'Motor dönmüyor, uğultu yapıyor.', 'received', '',
        [['received', 'Online talep oluşturuldu.', '-2 hours']]],
];
foreach ($demo as [$code, $name, $phone, $brand, $device, $model, $warranty, $issue, $status, $note, $log]) {
    $created = date('Y-m-d H:i:s', strtotime($log[0][2]));
    q('INSERT INTO service_requests(code, name, phone, brand, device, model, warranty, issue, status, admin_note, created_at, updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)',
        [$code, $name, $phone, $brand, $device, $model, $warranty, $issue, $status, $note, $created, $created]);
    $rid = db()->lastInsertId();
    foreach ($log as [$st, $n, $when]) {
        q('INSERT INTO request_log(request_id, status, note, created_at) VALUES(?,?,?,?)', [$rid, $st, $n, date('Y-m-d H:i:s', strtotime($when))]);
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
  if (f.method.toLowerCase() === 'get' && f.querySelector('[name=kod]')) {
    e.preventDefault();
    location.href = '{$base}servis-takip/ornek/';
    return;
  }
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
    '?p=dashboard', '?p=requests', '?p=requests&a=edit&id=1', '?p=requests&a=edit&id=2', '?p=requests&a=edit&id=3', '?p=requests&a=edit',
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
[, $html] = fetch($host . '/servis-takip?kod=CE-26DEMO1&telefon=1234');
write('servis-takip/ornek/index.html', preview_chrome(rewrite($html), false));
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
