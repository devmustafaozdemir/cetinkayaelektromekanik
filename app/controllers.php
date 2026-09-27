<?php
declare(strict_types=1);

function render(string $page, array $data = [], int $status = 200): void
{
    http_response_code($status);
    $data['content'] = view('pages/' . $page, $data);
    echo view('layout', $data);
    unset($_SESSION['old']);
}

function not_found(): void
{
    render('404', ['title' => 'Sayfa bulunamadı', 'noindex' => true], 404);
}

function published_posts_sql(): string
{
    return "FROM posts p LEFT JOIN categories c ON c.id = p.category_id
            WHERE p.status = 'published' AND p.published_at <= now_tr()";
}

function page_home(): void
{
    render('home', [
        'active'     => 'home',
        'categories' => product_categories_with_counts(),
        'products'   => q_all(products_sql() . ' AND p.featured = 1 ORDER BY p.sort, p.id LIMIT 8'),
        'services'   => q_all('SELECT * FROM services WHERE active = 1 ORDER BY sort, id LIMIT 4'),
        'posts'      => q_all('SELECT p.*, c.name AS category, c.slug AS category_slug ' . published_posts_sql() . ' ORDER BY p.featured DESC, p.published_at DESC LIMIT 3'),
        'faqs'       => q_all('SELECT * FROM faqs WHERE active = 1 ORDER BY sort, id LIMIT 5'),
        'schema'     => local_business_schema(),
    ]);
}

/* ---------- Products ---------- */

function products_sql(): string
{
    return 'SELECT p.*, c.name AS category, c.slug AS category_slug, c.art AS art
            FROM products p LEFT JOIN product_categories c ON c.id = p.category_id WHERE p.active = 1';
}

function product_categories_with_counts(): array
{
    return q_all('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.active = 1) AS cnt FROM product_categories c ORDER BY c.sort, c.id');
}

function product_listing(?array $category): void
{
    $where = '';
    $params = [];
    if ($category) {
        $where .= ' AND p.category_id = ?';
        $params[] = $category['id'];
    }
    $brand = trim((string)($_GET['marka'] ?? ''));
    if ($brand !== '') {
        $where .= ' AND p.brand = ?';
        $params[] = $brand;
    }
    $search = trim((string)($_GET['q'] ?? ''));
    if ($search !== '') {
        $where .= ' AND (p.title LIKE ? OR p.summary LIKE ? OR p.brand LIKE ?)';
        array_push($params, '%' . $search . '%', '%' . $search . '%', '%' . $search . '%');
    }
    $brandParams = $category ? [$category['id']] : [];
    render('products', [
        'title'       => $category ? $category['name'] : 'Ürünler',
        'description' => $category ? $category['summary'] : 'Meksis modüler su depoları, Grundfos, Wilo, Standart Pompa ve Sumak pompa ve hidrofor sistemleri.',
        'active'      => 'products',
        'category'    => $category,
        'categories'  => product_categories_with_counts(),
        'products'    => q_all(products_sql() . $where . ' ORDER BY c.sort, p.sort, p.id', $params),
        'brandList'   => array_column(q_all("SELECT DISTINCT brand FROM products WHERE active = 1 AND brand != ''" . ($category ? ' AND category_id = ?' : '') . ' ORDER BY brand', $brandParams), 'brand'),
        'brand'       => $brand,
        'search'      => $search,
        'noindex'     => $search !== '' || $brand !== '',
    ]);
}

function page_products(): void
{
    product_listing(null);
}

function page_product_category(string $method, string $slug): void
{
    $cat = q_one('SELECT * FROM product_categories WHERE slug = ?', [$slug]);
    if (!$cat) {
        not_found();
        return;
    }
    product_listing($cat);
}

function page_product(string $method, string $slug): void
{
    $product = q_one(products_sql() . ' AND p.slug = ?', [$slug]);
    if (!$product) {
        not_found();
        return;
    }
    $image = $product['image'] ? base_url() . upload_url($product['image']) : '';
    render('product', [
        'title'       => $product['title'],
        'description' => $product['summary'],
        'og_image'    => $image,
        'active'      => 'products',
        'product'     => $product,
        'specs'       => parse_specs($product['specs']),
        'related'     => q_all(products_sql() . ' AND p.id != ? ORDER BY (p.category_id = ?) DESC, (p.brand = ?) DESC, p.sort LIMIT 4', [$product['id'], (int)$product['category_id'], $product['brand']]),
        'schema'      => [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product['title'],
            'description' => $product['summary'],
            'brand'       => $product['brand'] ? ['@type' => 'Brand', 'name' => $product['brand']] : null,
            'image'       => $image ?: null,
            'category'    => $product['category'],
        ],
    ]);
}

function page_brands(): void
{
    $counts = [];
    foreach (q_all("SELECT brand, COUNT(*) c FROM products WHERE active = 1 GROUP BY brand") as $r) {
        $counts[$r['brand']] = (int)$r['c'];
    }
    render('brands', [
        'title'       => 'Markalar',
        'description' => 'Satışını yaptığımız markalar: ' . implode(', ', array_column(brands(), 'name')) . '.',
        'active'      => 'brands',
        'counts'      => $counts,
    ]);
}

function page_services(): void
{
    render('services', [
        'title'       => 'Hizmetlerimiz',
        'description' => 'Elektrikli el aletleri onarımı, motor bobinajı, yetkili garanti servisi, orijinal yedek parça ve periyodik bakım hizmetleri.',
        'active'      => 'services',
        'services'    => q_all('SELECT * FROM services WHERE active = 1 ORDER BY sort, id'),
    ]);
}

function page_service(string $method, string $slug): void
{
    $service = q_one('SELECT * FROM services WHERE slug = ? AND active = 1', [$slug]);
    if (!$service) {
        not_found();
        return;
    }
    render('service', [
        'title'       => $service['title'],
        'description' => $service['summary'],
        'active'      => 'services',
        'service'     => $service,
        'others'      => q_all('SELECT * FROM services WHERE active = 1 AND id != ? ORDER BY sort, id', [$service['id']]),
    ]);
}

function page_about(): void
{
    render('about', [
        'title'       => 'Hakkımızda',
        'description' => excerpt(setting('about_text'), 160),
        'active'      => 'about',
    ]);
}

function page_faq(): void
{
    $faqs = q_all('SELECT * FROM faqs WHERE active = 1 ORDER BY sort, id');
    render('faq', [
        'title'       => 'Sıkça Sorulan Sorular',
        'description' => 'Servis süreci, garanti, onarım süresi ve servis takibi hakkında sıkça sorulan sorular.',
        'active'      => 'faq',
        'faqs'        => $faqs,
        'schema'      => [
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(fn($f) => [
                '@type' => 'Question', 'name' => $f['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['answer']],
            ], $faqs),
        ],
    ]);
}

function blog_listing(array $opts): void
{
    $perPage = 9;
    $page = max(1, (int)($_GET['sayfa'] ?? 1));
    $where = '';
    $params = [];
    $search = trim((string)($_GET['q'] ?? ''));
    if (!empty($opts['category'])) {
        $where .= ' AND p.category_id = ?';
        $params[] = $opts['category']['id'];
    }
    if ($search !== '') {
        $where .= ' AND (p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)';
        $like = '%' . $search . '%';
        array_push($params, $like, $like, $like);
    }
    $total = (int)q_val('SELECT COUNT(*) ' . published_posts_sql() . $where, $params);
    $pages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $pages);
    $posts = q_all('SELECT p.*, c.name AS category, c.slug AS category_slug ' . published_posts_sql() . $where
        . ' ORDER BY p.published_at DESC LIMIT ' . $perPage . ' OFFSET ' . (($page - 1) * $perPage), $params);

    $featured = null;
    if ($page === 1 && $search === '' && empty($opts['category']) && $posts) {
        $featured = array_shift($posts);
    }

    render('blog', [
        'title'       => $opts['title'],
        'description' => $opts['description'],
        'active'      => 'blog',
        'posts'       => $posts,
        'featured'    => $featured,
        'search'      => $search,
        'page'        => $page,
        'pages'       => $pages,
        'total'       => $total,
        'category'    => $opts['category'] ?? null,
        'categories'  => q_all("SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id AND p.status = 'published' AND p.published_at <= now_tr()) AS cnt FROM categories c ORDER BY c.name"),
        'noindex'     => $search !== '',
    ]);
}

function page_blog(): void
{
    blog_listing([
        'title'       => 'Blog',
        'description' => 'Elektrikli el aletleri bakımı, motor bobinajı ve servis süreçleri hakkında faydalı rehberler ve duyurular.',
    ]);
}

function page_blog_category(string $method, string $slug): void
{
    $cat = q_one('SELECT * FROM categories WHERE slug = ?', [$slug]);
    if (!$cat) {
        not_found();
        return;
    }
    blog_listing([
        'title'       => $cat['name'] . ' – Blog',
        'description' => $cat['name'] . ' kategorisindeki yazılar.',
        'category'    => $cat,
    ]);
}

function page_post(string $method, string $slug): void
{
    $post = q_one('SELECT p.*, c.name AS category, c.slug AS category_slug ' . published_posts_sql() . ' AND p.slug = ?', [$slug]);
    $preview = false;
    if (!$post && current_user()) {
        $post = q_one('SELECT p.*, c.name AS category, c.slug AS category_slug FROM posts p LEFT JOIN categories c ON c.id = p.category_id WHERE p.slug = ?', [$slug]);
        $preview = (bool)$post;
    }
    if (!$post) {
        not_found();
        return;
    }
    if (!$preview) {
        $seen = $_SESSION['seen_posts'] ?? [];
        if (!in_array($post['id'], $seen, true)) {
            q('UPDATE posts SET views = views + 1 WHERE id = ?', [$post['id']]);
            $_SESSION['seen_posts'] = array_slice(array_merge($seen, [$post['id']]), -100);
        }
    }

    [$html, $toc] = build_toc($post['content']);
    $post['content'] = $html;

    $related = q_all('SELECT p.*, c.name AS category, c.slug AS category_slug ' . published_posts_sql()
        . ' AND p.id != ? ORDER BY (p.category_id = ?) DESC, p.published_at DESC LIMIT 3', [$post['id'], (int)$post['category_id']]);
    $prev = q_one('SELECT p.title, p.slug ' . published_posts_sql() . ' AND p.published_at < ? ORDER BY p.published_at DESC LIMIT 1', [$post['published_at']]);
    $next = q_one('SELECT p.title, p.slug ' . published_posts_sql() . ' AND p.published_at > ? ORDER BY p.published_at ASC LIMIT 1', [$post['published_at']]);

    $image = $post['cover'] ? base_url() . upload_url($post['cover']) : '';
    render('post', [
        'title'       => $post['meta_title'] ?: $post['title'],
        'description' => $post['meta_desc'] ?: ($post['excerpt'] ?: excerpt($post['content'])),
        'og_image'    => $image,
        'og_type'     => 'article',
        'active'      => 'blog',
        'post'        => $post,
        'toc'         => $toc,
        'related'     => $related,
        'prev'        => $prev,
        'next'        => $next,
        'preview'     => $preview,
        'noindex'     => $preview,
        'schema'      => [
            '@context'      => 'https://schema.org',
            '@type'         => 'BlogPosting',
            'headline'      => $post['title'],
            'description'   => $post['excerpt'],
            'image'         => $image ?: null,
            'datePublished' => date(DATE_ATOM, strtotime((string)$post['published_at'])),
            'dateModified'  => date(DATE_ATOM, strtotime($post['updated_at'])),
            'author'        => ['@type' => 'Organization', 'name' => setting('site_name')],
            'publisher'     => ['@type' => 'Organization', 'name' => setting('site_name')],
            'mainEntityOfPage' => base_url() . url('blog/' . $post['slug']),
        ],
    ]);
}

/** Adds ids to h2/h3 headings and returns [html, toc]. */
function build_toc(string $html): array
{
    $toc = [];
    $used = [];
    $html = preg_replace_callback('#<(h[23])([^>]*)>(.*?)</\1>#si', function ($m) use (&$toc, &$used) {
        $text = trim(html_entity_decode(strip_tags($m[3]), ENT_QUOTES, 'UTF-8'));
        $id = slugify($text);
        $base = $id;
        $n = 2;
        while (isset($used[$id])) {
            $id = $base . '-' . $n++;
        }
        $used[$id] = true;
        $toc[] = ['level' => (int)$m[1][1], 'text' => $text, 'id' => $id];
        $attrs = preg_replace('/\sid="[^"]*"/i', '', $m[2]);
        return '<' . $m[1] . $attrs . ' id="' . $id . '">' . $m[3] . '</' . $m[1] . '>';
    }, $html) ?? $html;
    return [$html, $toc];
}

function page_contact(string $method): void
{
    $errors = [];
    if ($method === 'POST') {
        csrf_check();
        $d = [
            'name'    => trim((string)($_POST['name'] ?? '')),
            'email'   => trim((string)($_POST['email'] ?? '')),
            'phone'   => trim((string)($_POST['phone'] ?? '')),
            'subject' => trim((string)($_POST['subject'] ?? '')),
            'message' => trim((string)($_POST['message'] ?? '')),
        ];
        if (!empty($_POST['website'])) {
            redirect(url('iletisim?gonderildi=1'));
        }
        if (mb_strlen($d['name']) < 2) $errors['name'] = 'Adınızı yazın.';
        if ($d['email'] === '' && $d['phone'] === '') $errors['phone'] = 'Size ulaşabilmemiz için telefon veya e-posta girin.';
        if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Geçerli bir e-posta adresi girin.';
        if (mb_strlen($d['message']) < 10) $errors['message'] = 'Mesajınız en az 10 karakter olmalı.';
        if (!$errors && too_many_submissions('messages')) $errors['form'] = 'Çok fazla mesaj gönderdiniz. Lütfen biraz sonra tekrar deneyin.';
        if (!$errors) {
            q('INSERT INTO messages(name, email, phone, subject, message, ip, created_at) VALUES(?,?,?,?,?,?,now_tr())', [
                mb_substr($d['name'], 0, 120), mb_substr($d['email'], 0, 160), mb_substr($d['phone'], 0, 40),
                mb_substr($d['subject'], 0, 160), mb_substr($d['message'], 0, 5000), client_ip(),
            ]);
            notify_admin('Yeni iletişim mesajı: ' . ($d['subject'] ?: $d['name']), "Ad: {$d['name']}\nTelefon: {$d['phone']}\nE-posta: {$d['email']}\n\n{$d['message']}");
            redirect(url('iletisim?gonderildi=1'));
        }
        $_SESSION['old'] = $d;
    }
    render('contact', [
        'title'       => 'İletişim',
        'description' => 'Çetinkaya Elektromekanik iletişim bilgileri, adres, çalışma saatleri ve iletişim formu.',
        'active'      => 'contact',
        'errors'      => $errors,
        'sent'        => isset($_GET['gonderildi']),
    ]);
}

function page_quote(string $method): void
{
    $errors = [];
    $created = null;
    $product = null;
    if (!empty($_REQUEST['urun'])) {
        $product = q_one(products_sql() . ' AND p.slug = ?', [(string)$_REQUEST['urun']]);
    }
    if ($method === 'POST') {
        csrf_check();
        $d = [];
        foreach (['name' => 120, 'company' => 160, 'phone' => 40, 'email' => 160, 'city' => 60, 'category' => 120, 'quantity' => 120, 'message' => 5000] as $k => $max) {
            $d[$k] = mb_substr(trim((string)($_POST[$k] ?? '')), 0, $max);
        }
        $d['type'] = array_key_exists($_POST['type'] ?? '', quote_types()) ? $_POST['type'] : 'product';
        $quick = !empty($_POST['quick']);
        if (!empty($_POST['website'])) {
            redirect(url('teklif-al'));
        }
        if (mb_strlen($d['name']) < 2) $errors['name'] = 'Adınızı yazın.';
        if (strlen(preg_replace('/\D/', '', $d['phone'])) < 10) $errors['phone'] = 'Geçerli bir telefon numarası girin.';
        if ($d['email'] !== '' && !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Geçerli bir e-posta adresi girin.';
        if (!$quick && !$product && $d['category'] === '' && mb_strlen($d['message']) < 5) $errors['message'] = 'İhtiyacınızı kısaca yazın veya bir ürün grubu seçin.';
        if (!$quick && empty($_POST['kvkk'])) $errors['kvkk'] = 'Devam etmek için aydınlatma metnini onaylayın.';
        if (!$errors && too_many_submissions('quotes', 5, 30)) $errors['form'] = 'Kısa sürede çok fazla talep oluşturdunuz. Lütfen bizi arayın.';
        if (!$errors) {
            $code = new_quote_code();
            q('INSERT INTO quotes(code, type, name, company, phone, email, city, product_id, product_name, category, quantity, message, ip, created_at, updated_at)
               VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,now_tr(),now_tr())', [
                $code, $d['type'], $d['name'], $d['company'], $d['phone'], $d['email'], $d['city'],
                $product['id'] ?? null, $product['title'] ?? '', $product['category'] ?? $d['category'], $d['quantity'], $d['message'], client_ip(),
            ]);
            q('INSERT INTO quote_log(quote_id, status, note, created_at) VALUES(?, ?, ?, now_tr())', [db()->lastInsertId(), 'new', $quick ? 'Hızlı teklif formundan geldi.' : 'Web sitesinden teklif talebi.']);
            notify_admin("Yeni teklif talebi {$code}", "Talep türü: " . quote_types()[$d['type']] . "\nAd: {$d['name']}\nFirma: {$d['company']}\nTelefon: {$d['phone']}\nE-posta: {$d['email']}\nŞehir: {$d['city']}\nÜrün: " . ($product['title'] ?? $d['category']) . "\nMiktar: {$d['quantity']}\n\n{$d['message']}");
            $_SESSION['last_quote'] = $code;
            redirect(url('teklif-al?tamam=1'));
        }
        $_SESSION['old'] = $d;
    }
    if (isset($_GET['tamam']) && !empty($_SESSION['last_quote'])) {
        $created = q_one('SELECT * FROM quotes WHERE code = ?', [$_SESSION['last_quote']]);
    }
    // Prefill from the home page tank sizer: ?olcu=4x3x2&malzeme=galvaniz
    if ($method === 'GET' && preg_match('/^(\d{1,2})x(\d{1,2})x(\d)$/', (string)($_GET['olcu'] ?? ''), $m)) {
        [$w, $l, $h] = [(int)$m[1], (int)$m[2], (int)$m[3]];
        $mat = ['galvaniz' => 'galvaniz', 'paslanmaz' => 'paslanmaz çelik', 'grp' => 'GRP', 'sandvic' => 'izolasyonlu'][$_GET['malzeme'] ?? ''] ?? 'galvaniz';
        $cat = (string)q_val("SELECT name FROM product_categories WHERE art = 'tank' ORDER BY sort LIMIT 1");
        $_SESSION['old'] = [
            'category' => $cat,
            'quantity' => "{$w} × {$l} × {$h} m, " . ($w * $l * $h) . " m³ {$mat} modüler depo",
        ];
    }
    render('quote', [
        'title'       => 'Teklif Al',
        'description' => 'Modüler su deposu, pompa ve hidrofor sistemleri için hızlı teklif alın.',
        'active'      => 'quote',
        'errors'      => $errors,
        'created'     => $created,
        'product'     => $product,
        'categories'  => q_all('SELECT name FROM product_categories ORDER BY sort, id'),
        'noindex'     => $created !== null || $product !== null,
    ]);
}

function page_sitemap(): void
{
    header('Content-Type: application/xml; charset=utf-8');
    $b = base_url();
    $urls = [['/', null], ['/urunler', null], ['/markalar', null], ['/hizmetler', null], ['/hakkimizda', null], ['/blog', null], ['/sss', null], ['/iletisim', null], ['/teklif-al', null]];
    foreach (q_all('SELECT slug FROM product_categories') as $c) $urls[] = ['/urunler/kategori/' . $c['slug'], null];
    foreach (q_all('SELECT slug, updated_at FROM products WHERE active = 1') as $p) $urls[] = ['/urunler/' . $p['slug'], $p['updated_at']];
    foreach (q_all('SELECT slug FROM services WHERE active = 1') as $s) $urls[] = ['/hizmetler/' . $s['slug'], null];
    foreach (q_all('SELECT slug FROM categories') as $c) $urls[] = ['/blog/kategori/' . $c['slug'], null];
    foreach (q_all('SELECT p.slug, p.updated_at ' . published_posts_sql()) as $p) $urls[] = ['/blog/' . $p['slug'], $p['updated_at']];
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as [$u, $mod]) {
        echo '  <url><loc>' . e($b . $u) . '</loc>' . ($mod ? '<lastmod>' . date('Y-m-d', strtotime($mod)) . '</lastmod>' : '') . "</url>\n";
    }
    echo '</urlset>';
}

function page_robots(): void
{
    header('Content-Type: text/plain; charset=utf-8');
    echo "User-agent: *\nDisallow: /admin/\n\nSitemap: " . base_url() . "/sitemap.xml\n";
}

function local_business_schema(): array
{
    $same = array_values(array_filter([setting('instagram'), setting('facebook'), setting('linkedin'), setting('youtube')]));
    return array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => 'Store',
        'name'        => setting('site_name'),
        'description' => setting('meta_description'),
        'url'         => base_url(),
        'telephone'   => setting('phone'),
        'email'       => setting('email'),
        'address'     => ['@type' => 'PostalAddress', 'streetAddress' => setting('address'), 'addressLocality' => 'İzmit', 'addressRegion' => 'Kocaeli', 'addressCountry' => 'TR'],
        'sameAs'      => $same ?: null,
    ]);
}

/** Best-effort e-mail notification; silently skipped when mail() isn't configured. */
function notify_admin(string $subject, string $body): void
{
    $to = setting('notify_email', setting('email'));
    if ($to === '' || !function_exists('mail')) {
        return;
    }
    $headers = 'Content-Type: text/plain; charset=UTF-8' . "\r\n" . 'From: ' . setting('site_name') . ' <no-reply@' . preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? 'localhost') . '>';
    @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);
}
