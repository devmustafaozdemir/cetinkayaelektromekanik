<?php
$user = q_one('SELECT * FROM users WHERE id = ?', [current_user()['id']]);
if ($method === 'POST') {
    if ($a === 'adduser') {
        $username = post_str('username', 60);
        $pass = (string)($_POST['password'] ?? '');
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,60}$/', $username) || strlen($pass) < 8) {
            flash('error', 'Kullanıcı adı en az 3 karakter, şifre en az 8 karakter olmalı.');
        } elseif (q_val('SELECT 1 FROM users WHERE username = ?', [$username])) {
            flash('error', 'Bu kullanıcı adı zaten kullanılıyor.');
        } else {
            q('INSERT INTO users(username, name, password_hash, created_at) VALUES(?, ?, ?, now_tr())', [$username, post_str('name', 120) ?: $username, password_hash($pass, PASSWORD_DEFAULT)]);
            flash('success', 'Yeni yönetici eklendi.');
        }
    } elseif ($a === 'deluser') {
        $uid = (int)($_GET['id'] ?? 0);
        if ($uid !== (int)$user['id']) {
            q('DELETE FROM users WHERE id = ?', [$uid]);
            flash('success', 'Yönetici silindi.');
        }
    } else {
        $name = post_str('name', 120);
        q('UPDATE users SET name = ? WHERE id = ?', [$name, $user['id']]);
        $new = (string)($_POST['new_password'] ?? '');
        if ($new !== '') {
            if (!password_verify((string)($_POST['current_password'] ?? ''), $user['password_hash'])) {
                flash('error', 'Mevcut şifre hatalı.');
                redirect(admin_url('account'));
            }
            if (strlen($new) < 8 || $new !== ($_POST['new_password2'] ?? '')) {
                flash('error', 'Yeni şifre en az 8 karakter olmalı ve tekrarıyla eşleşmeli.');
                redirect(admin_url('account'));
            }
            q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $user['id']]);
            session_regenerate_id(true);
        }
        flash('success', 'Hesap bilgileri güncellendi.');
    }
    redirect(admin_url('account'));
}
admin_render('account', ['title' => 'Hesabım', 'user' => $user, 'users' => q_all('SELECT id, username, name, last_login FROM users ORDER BY id')]);
