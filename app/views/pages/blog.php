<?php
$crumbs = [['/blog', 'Blog']];
if ($category) $crumbs[] = [null, $category['name']];
else $crumbs = [[null, 'Blog']];
$qs = function (int $p) use ($search) {
    $q = array_filter(['q' => $search, 'sayfa' => $p > 1 ? $p : null]);
    return $q ? '?' . http_build_query($q) : '';
};
?>
<?php partial('page-hero', ['heading' => $category ? $category['name'] : 'Blog', 'lead' => $category ? 'Bu kategorideki tüm yazılar' : 'Bakım rehberleri, teknik bilgiler ve servisimizden haberler.', 'crumbs' => $crumbs]); ?>
<section class="section section--tight">
  <div class="container">
    <div class="blog-toolbar">
      <nav class="cat-pills" aria-label="Kategoriler">
        <a href="/blog" class="<?= !$category ? 'is-active' : '' ?>">Tümü</a>
        <?php foreach ($categories as $c): if (!$c['cnt']) continue; ?>
          <a href="/blog/kategori/<?= e($c['slug']) ?>" class="<?= $category && $category['id'] == $c['id'] ? 'is-active' : '' ?>"><?= e($c['name']) ?> <small><?= (int)$c['cnt'] ?></small></a>
        <?php endforeach; ?>
      </nav>
      <form class="search-box" method="get" action="<?= $category ? '/blog/kategori/' . e($category['slug']) : '/blog' ?>" role="search">
        <?= icon('search') ?>
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="Yazılarda ara…" aria-label="Blogda ara">
      </form>
    </div>

    <?php if ($search !== ''): ?>
      <p class="muted mb-2">“<?= e($search) ?>” için <?= $total ?> sonuç bulundu. <a href="<?= $category ? '/blog/kategori/' . e($category['slug']) : '/blog' ?>">Aramayı temizle</a></p>
    <?php endif; ?>

    <?php if ($featured): ?>
      <article class="featured-post reveal">
        <a href="/blog/<?= e($featured['slug']) ?>" class="featured-post__media" tabindex="-1" aria-hidden="true">
          <?php if ($featured['cover']): ?><img src="<?= e(upload_url($featured['cover'])) ?>" alt=""><?php else: ?><span class="cover-fallback cover-fallback--lg"><?= icon('book') ?></span><?php endif; ?>
        </a>
        <div class="featured-post__body">
          <div class="post-meta">
            <span class="chip chip--accent">Öne Çıkan</span>
            <?php if ($featured['category']): ?><a class="chip" href="/blog/kategori/<?= e($featured['category_slug']) ?>"><?= e($featured['category']) ?></a><?php endif; ?>
          </div>
          <h2><a href="/blog/<?= e($featured['slug']) ?>"><?= e($featured['title']) ?></a></h2>
          <p><?= e($featured['excerpt'] ?: excerpt($featured['content'], 200)) ?></p>
          <div class="post-meta"><span><?= icon('calendar') ?> <?= e(tr_date($featured['published_at'])) ?></span><span><?= icon('clock') ?> <?= reading_time($featured['content']) ?> dk okuma</span></div>
          <a href="/blog/<?= e($featured['slug']) ?>" class="btn btn--primary">Yazıyı oku <?= icon('arrow-right') ?></a>
        </div>
      </article>
    <?php endif; ?>

    <?php if ($posts): ?>
      <div class="posts-grid">
        <?php foreach ($posts as $post) partial('post-card', ['post' => $post]); ?>
      </div>
    <?php elseif (!$featured): ?>
      <div class="empty-box"><?= icon('file-text') ?><p>Henüz yazı bulunmuyor.</p></div>
    <?php endif; ?>

    <?php if ($pages > 1): ?>
      <nav class="pagination" aria-label="Sayfalama">
        <?php if ($page > 1): ?><a href="<?= $qs($page - 1) ?: '?' ?>" aria-label="Önceki sayfa"><?= icon('arrow-left') ?></a><?php endif; ?>
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a href="<?= $qs($i) ?: '?' ?>" class="<?= $i === $page ? 'is-active' : '' ?>"<?= $i === $page ? ' aria-current="page"' : '' ?>><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $pages): ?><a href="<?= $qs($page + 1) ?>" aria-label="Sonraki sayfa"><?= icon('arrow-right') ?></a><?php endif; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
