<?php
declare(strict_types=1);

/* ---------- Output ---------- */

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
}

function url(string $path = ''): string
{
    return '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = ROOT . '/assets/' . ltrim($path, '/');
    $v = is_file($file) ? filemtime($file) : 1;
    return '/assets/' . ltrim($path, '/') . '?v=' . $v;
}

function upload_url(?string $file): string
{
    return $file ? '/uploads/' . rawurlencode($file) : '';
}

function redirect(string $to, int $code = 302): never
{
    header('Location: ' . $to, true, $code);
    exit;
}

function view(string $name, array $vars = []): string
{
    extract($vars, EXTR_SKIP);
    ob_start();
    require APP . '/views/' . $name . '.php';
    return (string)ob_get_clean();
}

function partial(string $name, array $vars = []): void
{
    echo view('partials/' . $name, $vars);
}

/* ---------- Settings ---------- */

function settings(): array
{
    static $cache = null;
    if ($cache === null || isset($GLOBALS['__settings_dirty'])) {
        unset($GLOBALS['__settings_dirty']);
        $cache = [];
        foreach (q_all('SELECT key, value FROM settings') as $r) {
            $cache[$r['key']] = $r['value'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $s = settings();
    return isset($s[$key]) && $s[$key] !== '' ? $s[$key] : $default;
}

function setting_set(string $key, string $value): void
{
    q('INSERT INTO settings(key, value) VALUES(?, ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value', [$key, $value]);
    $GLOBALS['__settings_dirty'] = true;
}

function setting_lines(string $key): array
{
    return array_values(array_filter(array_map('trim', preg_split('/\R/', setting($key)))));
}

function tel_href(string $phone): string
{
    $d = preg_replace('/\D+/', '', $phone);
    if (str_starts_with($d, '0')) {
        $d = '90' . substr($d, 1);
    }
    return 'tel:+' . $d;
}

function wa_href(string $phone, string $text = ''): string
{
    $d = preg_replace('/\D+/', '', $phone);
    if (str_starts_with($d, '0')) {
        $d = '90' . substr($d, 1);
    }
    return 'https://wa.me/' . $d . ($text !== '' ? '?text=' . rawurlencode($text) : '');
}

/* ---------- Text ---------- */

function slugify(string $text): string
{
    $map = ['ç' => 'c', 'Ç' => 'c', 'ğ' => 'g', 'Ğ' => 'g', 'ı' => 'i', 'I' => 'i', 'İ' => 'i', 'ö' => 'o', 'Ö' => 'o', 'ş' => 's', 'Ş' => 's', 'ü' => 'u', 'Ü' => 'u'];
    $text = strtr($text, $map);
    $text = mb_strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim((string)$text, '-') ?: 'icerik';
}

function unique_slug(string $table, string $slug, ?int $ignoreId = null): string
{
    $base = $slug;
    $i = 2;
    while (true) {
        $id = q_val("SELECT id FROM {$table} WHERE slug = ?", [$slug]);
        if (!$id || (int)$id === $ignoreId) {
            return $slug;
        }
        $slug = $base . '-' . $i++;
    }
}

function excerpt(string $html, int $len = 160): string
{
    $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
    return mb_strlen($text) > $len ? rtrim(mb_substr($text, 0, $len - 1)) . '…' : $text;
}

function reading_time(string $html): int
{
    $words = count(preg_split('/\s+/', trim(strip_tags($html))) ?: []);
    return max(1, (int)ceil($words / 200));
}

function tr_date(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '';
    }
    $months = ['', 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    $t = strtotime($date);
    $s = date('j', $t) . ' ' . $months[(int)date('n', $t)] . ' ' . date('Y', $t);
    return $withTime ? $s . ', ' . date('H:i', $t) : $s;
}

/* ---------- Security ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $t = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        http_response_code(419);
        exit('Oturum süresi doldu. Lütfen sayfayı yenileyip tekrar deneyin.');
    }
}

function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function flash(string $type, string $msg): void
{
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg];
}

function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function old(string $key, string $default = ''): string
{
    return (string)($_SESSION['old'][$key] ?? $default);
}

/** Simple per-IP throttle for public forms. */
function too_many_submissions(string $table, int $max = 5, int $minutes = 10): bool
{
    $since = date('Y-m-d H:i:s', time() - $minutes * 60);
    return (int)q_val("SELECT COUNT(*) FROM {$table} WHERE ip = ? AND created_at >= ?", [client_ip(), $since]) >= $max;
}

/* ---------- Auth ---------- */

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = !empty($_SESSION['uid']) ? q_one('SELECT id, username, name FROM users WHERE id = ?', [$_SESSION['uid']]) : null;
    }
    return $user;
}

/* ---------- Domain ---------- */

function quote_statuses(): array
{
    return [
        'new'       => ['Yeni Talep', 'amber'],
        'contacted' => ['İletişime Geçildi', 'blue'],
        'quoted'    => ['Teklif Verildi', 'violet'],
        'won'       => ['Satışa Döndü', 'green'],
        'lost'      => ['Olumsuz', 'red'],
    ];
}

function status_label(string $s): string
{
    return quote_statuses()[$s][0] ?? $s;
}

function status_color(string $s): string
{
    return quote_statuses()[$s][1] ?? 'slate';
}

function quote_types(): array
{
    return [
        'product' => 'Ürün teklifi',
        'project' => 'Proje / keşif talebi',
        'install' => 'Montaj & kurulum',
        'service' => 'Bakım & servis',
    ];
}

function new_quote_code(): string
{
    do {
        $code = 'TK-' . date('y') . strtoupper(substr(str_replace(['0', 'O', '1', 'I'], '', bin2hex(random_bytes(6))), 0, 5));
    } while (strlen($code) < 10 || q_val('SELECT 1 FROM quotes WHERE code = ?', [$code]));
    return $code;
}

/** "Anahtar|Değer" lines → [[key, value], ...] */
function parse_specs(string $specs): array
{
    $out = [];
    foreach (preg_split('/\R/', $specs) as $line) {
        $line = trim($line);
        if ($line === '') continue;
        $parts = array_map('trim', explode('|', $line, 2));
        $out[] = [$parts[0], $parts[1] ?? ''];
    }
    return $out;
}

/** Brands from settings: "Ad|Açıklama" per line. */
function brands(): array
{
    $out = [];
    foreach (setting_lines('brands') as $line) {
        [$name, $desc] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
        $slug = slugify($name);
        $out[] = ['name' => $name, 'desc' => $desc, 'slug' => $slug, 'logo' => brand_logo_url($slug)];
    }
    return $out;
}

/** Uploaded logo (admin) wins over the bundled one in assets/img/brands/. */
function brand_logo_url(string $slug): string
{
    $up = setting('brand_logo_' . $slug);
    if ($up !== '') {
        return upload_url($up);
    }
    foreach (['webp', 'png', 'svg'] as $ext) {
        if (is_file(ROOT . "/assets/img/brands/{$slug}.{$ext}")) {
            return asset("img/brands/{$slug}.{$ext}");
        }
    }
    return '';
}

function brand_logo(string $name, string $class = 'brand-logo'): string
{
    $url = brand_logo_url(slugify($name));
    return $url !== ''
        ? '<img class="' . e($class) . '" src="' . e($url) . '" alt="' . e($name) . '" loading="lazy">'
        : '<span class="' . e($class) . ' brand-logo--text">' . e($name) . '</span>';
}

function product_arts(): array
{
    return ['tank' => 'Modüler depo', 'pump' => 'Santrifüj pompa', 'booster' => 'Hidrofor', 'submersible' => 'Dalgıç pompa', 'drop' => 'Su damlası'];
}

/** Category photo with the illustration as fallback (shown if the photo is missing or fails to load). */
function category_photo(array $c, bool $credit = true): string
{
    $art = product_art($c['art'] ?? 'tank');
    $src = !empty($c['photo']) ? $c['photo'] : '';
    if ($src === '') {
        return '<span class="photo photo--art">' . $art . '</span>';
    }
    if (!preg_match('#^https?://#', $src)) {
        $src = upload_url($src);
    }
    $cap = '';
    if ($credit && !empty($c['photo_credit'])) {
        $label = 'Fotoğraf: ' . e($c['photo_credit']);
        $cap = '<figcaption>' . (!empty($c['photo_source']) ? '<a href="' . e($c['photo_source']) . '" target="_blank" rel="noopener">' . $label . '</a>' : $label) . '</figcaption>';
    }
    return '<figure class="photo"><img src="' . e($src) . '" alt="' . e($c['name'] ?? '') . '" loading="lazy" data-fallback><span class="photo__art" hidden>' . $art . '</span>' . $cap . '</figure>';
}

/** Brand logo: uploaded file if set, otherwise the built-in SVG wordmark. */
function site_logo(bool $light = false): string
{
    if (setting('logo') !== '') {
        return '<img class="logo__img" src="' . e(upload_url(setting(($light && setting('logo_light') !== '') ? 'logo_light' : 'logo'))) . '" alt="' . e(setting('site_name')) . '">';
    }
    $navy = $light ? '#ffffff' : '#1f3a60';
    return '<svg class="logo__svg" viewBox="0 0 420 88" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="' . e(setting('site_name')) . '">'
        . '<defs><linearGradient id="lgS" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f1f3f5"/><stop offset=".45" stop-color="#aeb5bd"/><stop offset=".55" stop-color="#d7dbe0"/><stop offset="1" stop-color="#7d8690"/></linearGradient></defs>'
        // Ç
        . '<path d="M44 8H22C12 8 6 14 6 24v28c0 10 6 16 16 16h22V56H24c-4 0-6-2-6-6V26c0-4 2-6 6-6h20z" fill="url(#lgS)" stroke="#6b737c" stroke-width="1"/>'
        . '<path d="M22 68h8l-3 8h-6l2-4h-3z" fill="url(#lgS)" stroke="#6b737c" stroke-width="1"/>'
        // E with red wedge
        . '<path d="M50 8h36l-4 12H62v6h-8z" fill="url(#lgS)" stroke="#6b737c" stroke-width="1"/>'
        . '<path d="M54 30l30-6-24 18h-8z" fill="#e1161c"/>'
        . '<path d="M50 46h26l-4 10H62v2h26l-4 10H50z" fill="url(#lgS)" stroke="#6b737c" stroke-width="1"/>'
        . '<text x="104" y="50" font-family="Manrope, Arial Black, sans-serif" font-weight="800" font-size="43" fill="' . $navy . '" letter-spacing="1">ÇETİNKAYA</text>'
        . '<path d="M106 69h26M384 69h26" stroke="#e1161c" stroke-width="3"/>'
        . '<text x="258" y="75" text-anchor="middle" font-family="Manrope, Arial, sans-serif" font-weight="700" font-size="16.5" fill="#e1161c" letter-spacing="5.2">ELEKTROMEKANİK</text>'
        . '</svg>';
}

/** Category/product drawing by category art key (see app/models.php). */
function product_art(string $kind, string $class = 'art'): string
{
    return model_svg(art_default_model($kind), $class);
}

/* ---------- Icons (Lucide-style, stroke based) ---------- */

function icon(string $name, string $class = 'icon'): string
{
    static $paths = [
        'wrench'    => '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
        'drill'     => '<path d="M10 18a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H5a3 3 0 0 1-3-3 1 1 0 0 1 1-1z"/><path d="M13 10H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a1 1 0 0 1 1 1v6a1 1 0 0 1-1 1z"/><path d="M5 18v-2.5"/><path d="M8 18v-7"/><path d="M14 4h3l4 2-4 2h-3"/>',
        'cog'       => '<path d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16Z"/><path d="M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/><path d="M12 2v2"/><path d="M12 22v-2"/><path d="m17 20.66-1-1.73"/><path d="M11 10.27 7 3.34"/><path d="m20.66 17-1.73-1"/><path d="m3.34 7 1.73 1"/><path d="M14 12h8"/><path d="M2 12h2"/><path d="m20.66 7-1.73 1"/><path d="m3.34 17 1.73-1"/><path d="m17 3.34-1 1.73"/><path d="m11 13.73-4 6.93"/>',
        'zap'       => '<path d="M4 14a1 1 0 0 1-.78-1.63l9.9-10.2a.5.5 0 0 1 .86.46l-1.92 6.02A1 1 0 0 0 13 10h7a1 1 0 0 1 .78 1.63l-9.9 10.2a.5.5 0 0 1-.86-.46l1.92-6.02A1 1 0 0 0 11 14z"/>',
        'shield'    => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
        'package'   => '<path d="M11 21.73a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73z"/><path d="M12 22V12"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="m7.5 4.27 9 5.15"/>',
        'calendar'  => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/>',
        'battery'   => '<rect width="16" height="10" x="2" y="7" rx="2"/><path d="M22 11v2"/><path d="M6 11v2"/><path d="M10 11v2"/><path d="M14 11v2"/>',
        'building'  => '<path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>',
        'phone'     => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'mail'      => '<rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'map-pin'   => '<path d="M20 10c0 4.99-5.54 10.19-7.4 11.8a1 1 0 0 1-1.2 0C9.54 20.19 4 14.99 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
        'clock'     => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'arrow-right' => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
        'arrow-left'  => '<path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>',
        'check'     => '<path d="M20 6 9 17l-5-5"/>',
        'check-circle' => '<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>',
        'search'    => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'menu'      => '<path d="M4 12h16"/><path d="M4 6h16"/><path d="M4 18h16"/>',
        'x'         => '<path d="M18 6 6 18"/><path d="m6 6 12 12"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'whatsapp'  => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="M9.5 9.5c.3 1.9 1.6 3.4 3.5 4.2l1-1 2 .8v1.2c-3.3.6-7-2.9-6.5-6.2h1.2l.8 2z"/>',
        'instagram' => '<rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/>',
        'facebook'  => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
        'linkedin'  => '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"/><rect width="4" height="12" x="2" y="9"/><circle cx="4" cy="4" r="2"/>',
        'youtube'   => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>',
        'clipboard' => '<rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
        'truck'     => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
        'stethoscope' => '<path d="M11 2v2"/><path d="M5 2v2"/><path d="M5 3H4a2 2 0 0 0-2 2v4a6 6 0 0 0 12 0V5a2 2 0 0 0-2-2h-1"/><path d="M8 15a6 6 0 0 0 12 0v-3"/><circle cx="20" cy="10" r="2"/>',
        'award'     => '<path d="m15.477 12.89 1.515 8.526a.5.5 0 0 1-.81.47l-3.58-2.687a1 1 0 0 0-1.197 0l-3.586 2.686a.5.5 0 0 1-.81-.469l1.514-8.526"/><circle cx="12" cy="8" r="6"/>',
        'users'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
        'gauge'     => '<path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/>',
        'book'      => '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
        'eye'       => '<path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.88 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.88 0"/><circle cx="12" cy="12" r="3"/>',
        'share'     => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" x2="15.42" y1="13.51" y2="17.49"/><line x1="15.41" x2="8.59" y1="6.51" y2="10.49"/>',
        'link'      => '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>',
        'plus'      => '<path d="M5 12h14"/><path d="M12 5v14"/>',
        'edit'      => '<path d="M12 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.375 2.625a1 1 0 0 1 3 3l-9.013 9.014a2 2 0 0 1-.853.505l-2.873.84a.5.5 0 0 1-.62-.62l.84-2.873a2 2 0 0 1 .506-.852z"/>',
        'trash'     => '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
        'home'      => '<path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
        'file-text' => '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
        'inbox'     => '<polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/>',
        'settings'  => '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
        'help'      => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',
        'tag'       => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>',
        'layers'    => '<path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>',
        'external'  => '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
        'panels'    => '<rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M9 4v16"/><path d="M15 4v16"/><path d="M3 12h18"/>',
        'droplet'   => '<path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>',
        'ruler'     => '<path d="M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.41 2.41 0 0 1 0-3.4l2.6-2.6a2.41 2.41 0 0 1 3.4 0Z"/><path d="m14.5 12.5 2-2"/><path d="m11.5 9.5 2-2"/><path d="m8.5 6.5 2-2"/><path d="m17.5 15.5 2-2"/>',
        'grid'      => '<rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/><path d="M9 3v18"/><path d="M15 3v18"/>',
        'calculator'=> '<rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/>',
        'handshake' => '<path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4"/><path d="m21 3 1 11h-2"/><path d="M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3"/><path d="M3 4h8"/>',
        'filter'    => '<polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>',
        'star'      => '<path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/>',
    ];
    $p = $paths[$name] ?? $paths['wrench'];
    return '<svg class="' . e($class) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

function service_icons(): array
{
    return ['droplet', 'ruler', 'wrench', 'cog', 'gauge', 'truck', 'shield', 'package', 'calendar', 'building', 'calculator', 'handshake', 'award', 'layers'];
}
