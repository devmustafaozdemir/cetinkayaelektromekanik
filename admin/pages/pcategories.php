<?php
$id = (int)($_GET['id'] ?? 0);
if ($a === 'sort' && $method === 'POST') {
    foreach ((array)($_POST['order'] ?? []) as $i => $cid) q('UPDATE product_categories SET sort = ? WHERE id = ?', [$i + 1, (int)$cid]);
    header('Content-Type: application/json');
    exit('{"ok":true}');
}
if ($method === 'POST') {
    if ($a === 'delete') {
        q('DELETE FROM product_categories WHERE id = ?', [$id]);
        flash('success', 'Kategori silindi. Ürünleri kategorisiz olarak kaldı.');
    } else {
        $name = post_str('name', 100);
        if ($name === '') {
            flash('error', 'Kategori adı boş olamaz.');
            redirect(admin_url('pcategories', $id ? ['id' => $id] : []));
        }
        $slug = unique_slug('product_categories', slugify(post_str('slug', 100) ?: $name), $id ?: null);
        $art = array_key_exists($_POST['art'] ?? '', product_arts()) ? $_POST['art'] : 'tank';
        $summary = post_str('summary', 400);
        if ($id) q('UPDATE product_categories SET name = ?, slug = ?, art = ?, summary = ? WHERE id = ?', [$name, $slug, $art, $summary, $id]);
        else q('INSERT INTO product_categories(name, slug, art, summary, sort) VALUES(?, ?, ?, ?, (SELECT COALESCE(MAX(sort),0)+1 FROM product_categories))', [$name, $slug, $art, $summary]);
        flash('success', 'Kategori kaydedildi.');
    }
    redirect(admin_url('pcategories'));
}
admin_render('pcategories', [
    'title' => 'Ürün Kategorileri',
    'rows'  => q_all('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS cnt FROM product_categories c ORDER BY c.sort, c.id'),
    'edit'  => $id ? q_one('SELECT * FROM product_categories WHERE id = ?', [$id]) : null,
]);
