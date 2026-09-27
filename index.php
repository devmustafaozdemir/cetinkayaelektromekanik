<?php
declare(strict_types=1);

require __DIR__ . '/app/bootstrap.php';

$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$path = '/' . trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($path !== '/' && str_ends_with((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/')) {
    redirect($path, 301);
}

require APP . '/controllers.php';

$routes = [
    '#^/$#'                             => 'page_home',
    '#^/urunler$#'                      => 'page_products',
    '#^/urunler/kategori/([a-z0-9-]+)$#' => 'page_product_category',
    '#^/urunler/([a-z0-9-]+)$#'         => 'page_product',
    '#^/markalar$#'                     => 'page_brands',
    '#^/hizmetler$#'                    => 'page_services',
    '#^/hizmetler/([a-z0-9-]+)$#'       => 'page_service',
    '#^/hakkimizda$#'                   => 'page_about',
    '#^/blog$#'                         => 'page_blog',
    '#^/blog/kategori/([a-z0-9-]+)$#'   => 'page_blog_category',
    '#^/blog/([a-z0-9-]+)$#'            => 'page_post',
    '#^/iletisim$#'                     => 'page_contact',
    '#^/teklif-al$#'                    => 'page_quote',
    '#^/sss$#'                          => 'page_faq',
    '#^/(servis-talebi|servis-takip)$#' => fn() => redirect(url('teklif-al'), 301),
    '#^/sitemap\.xml$#'                 => 'page_sitemap',
    '#^/robots\.txt$#'                  => 'page_robots',
];

foreach ($routes as $pattern => $handler) {
    if (preg_match($pattern, $path, $m)) {
        array_shift($m);
        $handler($method, ...$m);
        exit;
    }
}

not_found();
