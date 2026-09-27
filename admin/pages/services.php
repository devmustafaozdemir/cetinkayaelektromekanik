<?php
$id = (int)($_GET['id'] ?? 0);
if ($a === 'delete' && $method === 'POST') {
    q('DELETE FROM services WHERE id = ?', [$id]);
    flash('success', 'Hizmet silindi.');
    redirect(admin_url('services'));
}
if ($a === 'sort' && $method === 'POST') {
    foreach ((array)($_POST['order'] ?? []) as $i => $sid) {
        q('UPDATE services SET sort = ? WHERE id = ?', [$i + 1, (int)$sid]);
    }
    header('Content-Type: application/json');
    exit('{"ok":true}');
}
if ($a === 'edit') {
    $row = $id ? q_one('SELECT * FROM services WHERE id = ?', [$id]) : null;
    if ($id && !$row) redirect(admin_url('services'));
    $errors = [];
    if ($method === 'POST') {
        $d = [
            'title'   => post_str('title', 160),
            'summary' => post_str('summary', 400),
            'content' => sanitize_html((string)($_POST['content'] ?? '')),
            'icon'    => in_array($_POST['icon'] ?? '', service_icons(), true) ? $_POST['icon'] : 'wrench',
            'active'  => !empty($_POST['active']) ? 1 : 0,
        ];
        $d['slug'] = unique_slug('services', slugify(post_str('slug', 160) ?: $d['title']), $id ?: null);
        if ($d['title'] === '') $errors['title'] = 'Başlık zorunludur.';
        if (!$errors) {
            if ($row) {
                q('UPDATE services SET title=:title, slug=:slug, summary=:summary, content=:content, icon=:icon, active=:active WHERE id=:id', $d + ['id' => $id]);
            } else {
                $d['sort'] = (int)q_val('SELECT COALESCE(MAX(sort),0)+1 FROM services');
                q('INSERT INTO services(title, slug, summary, content, icon, active, sort) VALUES(:title, :slug, :summary, :content, :icon, :active, :sort)', $d);
                $id = (int)db()->lastInsertId();
            }
            flash('success', 'Hizmet kaydedildi.');
            redirect(admin_url('services', ['a' => 'edit', 'id' => $id]));
        }
        $row = array_merge($row ?? [], $d);
    }
    admin_render('service-edit', [
        'title'  => $id ? 'Hizmeti Düzenle' : 'Yeni Hizmet',
        'row'    => $row ?? ['title' => '', 'slug' => '', 'summary' => '', 'content' => '', 'icon' => 'wrench', 'active' => 1],
        'id'     => $id,
        'errors' => $errors,
        'editor' => true,
    ]);
}
admin_render('services', ['title' => 'Hizmetler', 'rows' => q_all('SELECT * FROM services ORDER BY sort, id')]);
