<?php
// Router for PHP's built-in server:  php -S localhost:8000 router.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (preg_match('#^/(data|app)(/|$)#', $path) || str_ends_with($path, '.sqlite')) {
    http_response_code(403);
    exit('Forbidden');
}
if (str_starts_with($path, '/admin')) {
    if ($path === '/admin') { header('Location: /admin/'); exit; }
    if (is_file(__DIR__ . $path) && !str_ends_with($path, '.php')) return false;
    require __DIR__ . '/admin/index.php';
    return true;
}
if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false;
}
require __DIR__ . '/index.php';
