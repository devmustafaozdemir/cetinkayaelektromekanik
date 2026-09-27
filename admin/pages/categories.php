<?php
$id = (int)($_GET['id'] ?? 0);
if ($method === 'POST') {
    if ($a === 'delete') {
        q('DELETE FROM categories WHERE id = ?', [$id]);
        flash('success', 'Kategori silindi. Yazıları kategorisiz olarak kaldı.');
    } else {
        $name = post_str('name', 80);
        if ($name === '') {
            flash('error', 'Kategori adı boş olamaz.');
        } else {
            $slug = unique_slug('categories', slugify(post_str('slug', 80) ?: $name), $id ?: null);
            if ($id) q('UPDATE categories SET name = ?, slug = ? WHERE id = ?', [$name, $slug, $id]);
            else q('INSERT INTO categories(name, slug) VALUES(?, ?)', [$name, $slug]);
            flash('success', 'Kategori kaydedildi.');
        }
    }
    redirect(admin_url('categories'));
}
admin_render('categories', [
    'title' => 'Blog Kategorileri',
    'rows'  => q_all('SELECT c.*, (SELECT COUNT(*) FROM posts p WHERE p.category_id = c.id) AS cnt FROM categories c ORDER BY c.name'),
    'edit'  => $id ? q_one('SELECT * FROM categories WHERE id = ?', [$id]) : null,
]);
