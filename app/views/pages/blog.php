<?php
$crumbs = $category ? [['/blog', 'Blog'], [null, $category['name']]] : [[null, 'Blog']];
$base = $category ? '/blog/kategori/' . $category['slug'] : '/blog';
$qs = function (int $p) use ($search) {
    $q = array_filter(['q' => $search, 'sayfa' => $p > 1 ? $p : null]);
    return $q ? '?' . http_build_query($q) : '';
};
?>
<?php partial('page-hero', ['heading' => $category ? $category['name'] : 'Blog', 'lead' => $category ? null : 'Su depolama, pompa ve hidrofor seçimi üzerine rehberler ve firmamızdan duyurular.', 'crumbs' => $crumbs]); ?>
<section class="block block--tight">
  <div class="container">
    <div class="blog-bar">
      <nav class="pills" aria-label="Kategoriler">
        <a href="/blog"<?= !$category ? ' aria-current="page"' : '' ?>>Hepsi</a>
        <?php foreach ($categories as $c): if (!$c['cnt']) continue; ?>
          <a href="/blog/kategori/<?= e($c['slug']) ?>"<?= $category && $category['id'] == $c['id'] ? ' aria-current="page"' : '' ?>><?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </nav>
      <form class="search" method="get" action="<?= e($base) ?>" role="search">
        <?= icon('search') ?><input type="search" name="q" value="<?= e($search) ?>" placeholder="Yazılarda ara" aria-label="Blogda ara">
      </form>
    </div>
    <?php if ($search !== ''): ?><p class="muted">“<?= e($search) ?>” için <?= $total ?> yazı bulundu. <a href="<?= e($base) ?>" class="text-link">Aramayı temizle</a></p><?php endif; ?>

    <?php if ($featured): ?>
      <article class="post post--lead">
        <?php if ($featured['cover']): ?>
          <a href="/blog/<?= e($featured['slug']) ?>" class="post__media" tabindex="-1" aria-hidden="true"><img src="<?= e(upload_url($featured['cover'])) ?>" alt=""></a>
        <?php else: ?>
          <a href="/blog/<?= e($featured['slug']) ?>" class="post--lead__art" tabindex="-1" aria-hidden="true"><?= model_svg('tank:grp', 'art', ['w' => 3, 'l' => 2, 'h' => 2]) ?></a>
        <?php endif; ?>
        <div>
          <p class="post__meta"><?php if ($featured['category']): ?><a href="/blog/kategori/<?= e($featured['category_slug']) ?>"><?= e($featured['category']) ?></a>, <?php endif; ?><?= e(tr_date($featured['published_at'])) ?></p>
          <h2><a href="/blog/<?= e($featured['slug']) ?>"><?= e($featured['title']) ?></a></h2>
          <p><?= e($featured['excerpt'] ?: excerpt($featured['content'], 200)) ?></p>
          <p class="post__time"><?= reading_time($featured['content']) ?> dakikalık okuma</p>
        </div>
      </article>
    <?php endif; ?>

    <?php if ($posts): ?>
      <div class="post-grid"><?php foreach ($posts as $post) partial('post-card', ['post' => $post]); ?></div>
    <?php elseif (!$featured): ?>
      <div class="empty"><p>Bu aramayla eşleşen yazı yok. <a href="/blog" class="text-link">Bütün yazılara dönün</a>.</p></div>
    <?php endif; ?>

    <?php if ($pages > 1): ?>
      <nav class="pagination" aria-label="Sayfalar">
        <?php for ($i = 1; $i <= $pages; $i++): ?><a href="<?= $qs($i) ?: '?' ?>"<?= $i === $page ? ' aria-current="page"' : '' ?>><?= $i ?></a><?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
