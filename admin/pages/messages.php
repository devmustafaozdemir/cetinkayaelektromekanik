<?php
$id = (int)($_GET['id'] ?? 0);
if ($a === 'delete' && $method === 'POST') {
    q('DELETE FROM messages WHERE id = ?', [$id]);
    flash('success', 'Mesaj silindi.');
    redirect(admin_url('messages'));
}
if ($a === 'unread' && $method === 'POST') {
    q('UPDATE messages SET is_read = 0 WHERE id = ?', [$id]);
    redirect(admin_url('messages'));
}
if ($a === 'readall' && $method === 'POST') {
    q('UPDATE messages SET is_read = 1');
    flash('success', 'Tüm mesajlar okundu olarak işaretlendi.');
    redirect(admin_url('messages'));
}
if ($a === 'view') {
    $msg = q_one('SELECT * FROM messages WHERE id = ?', [$id]);
    if (!$msg) redirect(admin_url('messages'));
    q('UPDATE messages SET is_read = 1 WHERE id = ?', [$id]);
    admin_render('message', ['title' => 'Mesaj', 'msg' => $msg]);
}
admin_render('messages', [
    'title' => 'Mesajlar',
    'rows'  => q_all('SELECT * FROM messages ORDER BY id DESC LIMIT 300'),
]);
