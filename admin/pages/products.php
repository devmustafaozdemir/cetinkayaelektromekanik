<?php
$id = (int)($_GET['id'] ?? 0);

if ($a === 'delete' && $method === 'POST') {
    $row = q_one('SELECT image FROM products WHERE id = ?', [$id]);
    if ($row) {
        delete_upload($row['image']);
        q('DELETE FROM products WHERE id = ?', [$id]);
        flash('success', 'Ürün silindi.');
    }
    redirect(admin_url('products'));
}
if ($a === 'toggle' && $method === 'POST') {
    q('UPDATE products SET active = 1 - active WHERE id = ?', [$id]);
    redirect(admin_url('products', array_filter(['kategori' => $_GET['kategori'] ?? null])));
}

if ($a === 'edit') {
    $row = $id ? q_one('SELECT * FROM products WHERE id = ?', [$id]) : null;
    if ($id && !$row) redirect(admin_url('products'));
    $errors = [];
    if ($method === 'POST') {
        $d = [
            'title'       => post_str('title', 200),
            'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
            'brand'       => post_str('brand', 80),
            'summary'     => post_str('summary', 400),
            'content'     => sanitize_html((string)($_POST['content'] ?? '')),
            'specs'       => post_str('specs', 5000),
            'model'       => array_key_exists($_POST['model'] ?? '', model_types()) ? $_POST['model'] : '',
            'featured'    => !empty($_POST['featured']) ? 1 : 0,
            'active'      => !empty($_POST['active']) ? 1 : 0,
        ];
        $d['slug'] = unique_slug('products', slugify(post_str('slug', 200) ?: $d['title']), $id ?: null);
        if ($d['title'] === '') $errors['title'] = 'Ürün adı zorunludur.';
        $image = $row['image'] ?? '';
        if (!$errors && !empty($_FILES['image']['name'])) {
            try {
                $new = store_image($_FILES['image'], 1400);
                delete_upload($image);
                $image = $new;
            } catch (RuntimeException $ex) {
                $errors['image'] = $ex->getMessage();
            }
        } elseif (!empty($_POST['remove_image'])) {
            delete_upload($image);
            $image = '';
        }
        if (!$errors) {
            $d['image'] = $image;
            if ($row) {
                q('UPDATE products SET title=:title, slug=:slug, category_id=:category_id, brand=:brand, summary=:summary, content=:content, specs=:specs,
                   featured=:featured, active=:active, image=:image, model=:model, updated_at=now_tr() WHERE id=:id', $d + ['id' => $id]);
            } else {
                $d['sort'] = (int)q_val('SELECT COALESCE(MAX(sort),0)+1 FROM products');
                q('INSERT INTO products(title, slug, category_id, brand, summary, content, specs, featured, active, image, model, sort, created_at, updated_at)
                   VALUES(:title, :slug, :category_id, :brand, :summary, :content, :specs, :featured, :active, :image, :model, :sort, now_tr(), now_tr())', $d);
                $id = (int)db()->lastInsertId();
            }
            flash('success', 'Ürün kaydedildi.');
            redirect(admin_url('products', ['a' => 'edit', 'id' => $id]));
        }
        $row = array_merge($row ?? [], $d, ['image' => $image]);
    }
    admin_render('product-edit', [
        'title'      => $id ? 'Ürünü Düzenle' : 'Yeni Ürün',
        'row'        => $row ?? ['title' => '', 'slug' => '', 'category_id' => (int)($_GET['kategori'] ?? 0) ?: null, 'brand' => '', 'summary' => '', 'content' => '', 'specs' => '', 'featured' => 0, 'active' => 1, 'image' => '', 'model' => ''],
        'id'         => $id,
        'errors'     => $errors,
        'categories' => q_all('SELECT * FROM product_categories ORDER BY sort, id'),
        'brands'     => array_column(brands(), 'name'),
        'quoteCount' => $id ? (int)q_val('SELECT COUNT(*) FROM quotes WHERE product_id = ?', [$id]) : 0,
        'editor'     => true,
    ]);
}

$where = ' WHERE 1=1';
$params = [];
$cat = (int)($_GET['kategori'] ?? 0);
if ($cat) { $where .= ' AND p.category_id = ?'; $params[] = $cat; }
$search = trim((string)($_GET['q'] ?? ''));
if ($search !== '') { $where .= ' AND (p.title LIKE ? OR p.brand LIKE ?)'; array_push($params, '%' . $search . '%', '%' . $search . '%'); }
admin_render('products', [
    'title'      => 'Ürünler',
    'rows'       => q_all('SELECT p.*, c.name AS category, c.art, (SELECT COUNT(*) FROM quotes q WHERE q.product_id = p.id) AS quotes FROM products p LEFT JOIN product_categories c ON c.id = p.category_id' . $where . ' ORDER BY c.sort, p.sort, p.id', $params),
    'categories' => q_all('SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) AS cnt FROM product_categories c ORDER BY c.sort, c.id'),
    'cat'        => $cat,
    'search'     => $search,
    'total'      => (int)q_val('SELECT COUNT(*) FROM products'),
]);
