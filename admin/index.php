<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');

$p = preg_replace('/[^a-z]/', '', (string)($_GET['p'] ?? 'dashboard')) ?: 'dashboard';
$a = preg_replace('/[^a-z]/', '', (string)($_GET['a'] ?? 'index')) ?: 'index';
$method = $_SERVER['REQUEST_METHOD'];

function admin_render(string $viewName, array $data = []): void
{
    global $p;
    $data['content'] = view('../../admin/views/' . $viewName, $data);
    $data['p'] = $p;
    echo view('../../admin/views/layout', $data);
    unset($_SESSION['old']);
    exit;
}

function admin_url(string $page, array $params = []): string
{
    return '/admin/?' . http_build_query(['p' => $page] + $params);
}

function post_str(string $key, int $max = 10000): string
{
    return mb_substr(trim((string)($_POST[$key] ?? '')), 0, $max);
}

/* ---------- First run: create the admin account ---------- */
$hasUsers = (int)q_val('SELECT COUNT(*) FROM users') > 0;
if (!$hasUsers) {
    $errors = [];
    if ($method === 'POST') {
        csrf_check();
        $username = post_str('username', 60);
        $name = post_str('name', 120);
        $pass = (string)($_POST['password'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,60}$/', $username)) $errors[] = 'Kullanıcı adı en az 3 karakter olmalı (harf, rakam, _ . -).';
        if (strlen($pass) < 8) $errors[] = 'Şifre en az 8 karakter olmalı.';
        if ($pass !== ($_POST['password2'] ?? '')) $errors[] = 'Şifreler eşleşmiyor.';
        if (!$errors) {
            q('INSERT INTO users(username, name, password_hash, created_at) VALUES(?, ?, ?, now_tr())', [$username, $name ?: $username, password_hash($pass, PASSWORD_DEFAULT)]);
            session_regenerate_id(true);
            $_SESSION['uid'] = (int)db()->lastInsertId();
            flash('success', 'Yönetici hesabınız oluşturuldu. Hoş geldiniz!');
            redirect(admin_url('dashboard'));
        }
    }
    echo view('../../admin/views/auth', ['mode' => 'setup', 'errors' => $errors]);
    exit;
}

/* ---------- Login / logout ---------- */
if ($p === 'login') {
    if (current_user()) redirect(admin_url('dashboard'));
    $errors = [];
    if ($method === 'POST') {
        csrf_check();
        q('DELETE FROM login_attempts WHERE at < ?', [time() - 900]);
        $attempts = (int)q_val('SELECT COUNT(*) FROM login_attempts WHERE ip = ?', [client_ip()]);
        if ($attempts >= 5) {
            $errors[] = 'Çok fazla hatalı deneme. Lütfen 15 dakika sonra tekrar deneyin.';
        } else {
            $user = q_one('SELECT * FROM users WHERE username = ?', [post_str('username', 60)]);
            if ($user && password_verify((string)($_POST['password'] ?? ''), $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['uid'] = (int)$user['id'];
                q('DELETE FROM login_attempts WHERE ip = ?', [client_ip()]);
                q("UPDATE users SET last_login = now_tr() WHERE id = ?", [$user['id']]);
                if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                    q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash((string)$_POST['password'], PASSWORD_DEFAULT), $user['id']]);
                }
                $next = (string)($_SESSION['after_login'] ?? '');
                unset($_SESSION['after_login']);
                redirect(str_starts_with($next, '/admin/') ? $next : admin_url('dashboard'));
            }
            q('INSERT INTO login_attempts(ip, at) VALUES(?, ?)', [client_ip(), time()]);
            $errors[] = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
    echo view('../../admin/views/auth', ['mode' => 'login', 'errors' => $errors]);
    exit;
}

if (!current_user()) {
    if ($method === 'GET') $_SESSION['after_login'] = $_SERVER['REQUEST_URI'] ?? '';
    redirect(admin_url('login'));
}

if ($p === 'logout') {
    csrf_check();
    $_SESSION = [];
    session_regenerate_id(true);
    redirect(admin_url('login'));
}

if ($method === 'POST') {
    csrf_check();
}

$file = __DIR__ . '/pages/' . $p . '.php';
if (!is_file($file)) {
    http_response_code(404);
    admin_render('notfound', ['title' => 'Sayfa bulunamadı']);
}
require $file;
