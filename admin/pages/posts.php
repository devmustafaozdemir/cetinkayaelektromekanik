<?php
$id = (int)($_GET['id'] ?? 0);

if ($a === 'upload' && $method === 'POST') {
    header('Content-Type: application/json');
    try {
        $name = store_image($_FILES['image'] ?? [], 1400);
        echo json_encode(['url' => upload_url($name)]);
    } catch (Throwable $ex) {
        http_response_code(422);
        echo json_encode(['error' => $ex->getMessage()]);
    }
    exit;
}

if ($a === 'delete' && $method === 'POST') {
    $post = q_one('SELECT cover FROM posts WHERE id = ?', [$id]);
    if ($post) {
        delete_upload($post['cover']);
        q('DELETE FROM posts WHERE id = ?', [$id]);
        flash('success', 'Yazı silindi.');
    }
    redirect(admin_url('posts'));
}

if ($a === 'edit') {
    $post = $id ? q_one('SELECT * FROM posts WHERE id = ?', [$id]) : null;
    if ($id && !$post) redirect(admin_url('posts'));
    $errors = [];
    if ($method === 'POST') {
        $d = [
            'title'       => post_str('title', 200),
            'slug'        => slugify(post_str('slug', 200) ?: post_str('title', 200)),
            'excerpt'     => post_str('excerpt', 400),
            'content'     => sanitize_html((string)($_POST['content'] ?? '')),
            'category_id' => (int)($_POST['category_id'] ?? 0) ?: null,
            'status'      => in_array($_POST['status'] ?? '', ['draft', 'published'], true) ? $_POST['status'] : 'draft',
            'featured'    => !empty($_POST['featured']) ? 1 : 0,
            'meta_title'  => post_str('meta_title', 120),
            'meta_desc'   => post_str('meta_desc', 300),
            'published_at'=> post_str('published_at', 30),
        ];
        if ($d['title'] === '') $errors['title'] = 'Başlık zorunludur.';
        if (trim(strip_tags($d['content'], '<img><iframe>')) === '') $errors['content'] = 'İçerik boş olamaz.';
        $d['slug'] = unique_slug('posts', $d['slug'], $id ?: null);
        $d['published_at'] = $d['published_at'] !== '' ? date('Y-m-d H:i:s', strtotime($d['published_at'])) : ($d['status'] === 'published' ? date('Y-m-d H:i:s') : null);
        if ($d['excerpt'] === '') $d['excerpt'] = excerpt($d['content'], 180);

        $cover = $post['cover'] ?? '';
        if (!$errors && !empty($_FILES['cover']['name'])) {
            try {
                $new = store_image($_FILES['cover']);
                delete_upload($cover);
                $cover = $new;
            } catch (RuntimeException $ex) {
                $errors['cover'] = $ex->getMessage();
            }
        } elseif (!empty($_POST['remove_cover'])) {
            delete_upload($cover);
            $cover = '';
        }

        if (!$errors) {
            $d['cover'] = $cover;
            if ($post) {
                q("UPDATE posts SET title=:title, slug=:slug, excerpt=:excerpt, content=:content, category_id=:category_id, status=:status, featured=:featured,
                   meta_title=:meta_title, meta_desc=:meta_desc, published_at=:published_at, cover=:cover, updated_at=now_tr() WHERE id=:id", $d + ['id' => $id]);
            } else {
                q('INSERT INTO posts(title, slug, excerpt, content, category_id, status, featured, meta_title, meta_desc, published_at, cover, created_at, updated_at)
                   VALUES(:title, :slug, :excerpt, :content, :category_id, :status, :featured, :meta_title, :meta_desc, :published_at, :cover, now_tr(), now_tr())', $d);
                $id = (int)db()->lastInsertId();
            }
            flash('success', $d['status'] === 'published' ? 'Yazı kaydedildi ve yayında.' : 'Taslak kaydedildi.');
            redirect(admin_url('posts', ['a' => 'edit', 'id' => $id]));
        }
        $post = array_merge($post ?? [], $d, ['cover' => $cover]);
    }
    admin_render('post-edit', [
        'title'      => $id ? 'Yazıyı Düzenle' : 'Yeni Yazı',
        'post'       => $post ?? ['title' => '', 'slug' => '', 'excerpt' => '', 'content' => '', 'category_id' => null, 'status' => 'draft', 'featured' => 0, 'meta_title' => '', 'meta_desc' => '', 'published_at' => null, 'cover' => '', 'views' => 0],
        'id'         => $id,
        'errors'     => $errors,
        'categories' => q_all('SELECT * FROM categories ORDER BY name'),
        'editor'     => true,
    ]);
}

$where = ' WHERE 1=1';
$params = [];
$status = (string)($_GET['durum'] ?? '');
if (in_array($status, ['draft', 'published'], true)) { $where .= ' AND p.status = ?'; $params[] = $status; }
$search = trim((string)($_GET['q'] ?? ''));
if ($search !== '') { $where .= ' AND p.title LIKE ?'; $params[] = '%' . $search . '%'; }
admin_render('posts', [
    'title'  => 'Blog Yazıları',
    'posts'  => q_all('SELECT p.*, c.name AS category FROM posts p LEFT JOIN categories c ON c.id = p.category_id' . $where . ' ORDER BY COALESCE(p.published_at, p.created_at) DESC', $params),
    'status' => $status,
    'search' => $search,
    'counts' => [
        ''          => (int)q_val('SELECT COUNT(*) FROM posts'),
        'published' => (int)q_val("SELECT COUNT(*) FROM posts WHERE status = 'published'"),
        'draft'     => (int)q_val("SELECT COUNT(*) FROM posts WHERE status = 'draft'"),
    ],
]);
