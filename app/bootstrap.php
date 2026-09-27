<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('APP', ROOT . '/app');
define('DATA_DIR', ROOT . '/data');
define('UPLOAD_DIR', ROOT . '/uploads');

date_default_timezone_set('Europe/Istanbul');
mb_internal_encoding('UTF-8');

$config = [
    'db_path' => getenv('APP_DB_PATH') ?: DATA_DIR . '/site.sqlite',
    'debug'   => getenv('APP_DEBUG') === '1',
];
if (is_file(ROOT . '/config.local.php')) {
    $config = array_merge($config, require ROOT . '/config.local.php');
}

ini_set('display_errors', $config['debug'] ? '1' : '0');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_name('cem_sess');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require APP . '/db.php';
require APP . '/helpers.php';
require APP . '/sanitize.php';
require APP . '/models.php';

db_init($config['db_path']);

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
