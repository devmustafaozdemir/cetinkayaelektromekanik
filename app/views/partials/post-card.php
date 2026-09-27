<article class="post">
  <?php if ($post['cover']): ?>
    <a href="/blog/<?= e($post['slug']) ?>" class="post__media" tabindex="-1" aria-hidden="true"><img src="<?= e(upload_url($post['cover'])) ?>" alt="" loading="lazy"></a>
  <?php endif; ?>
  <p class="post__meta"><?php if (!empty($post['category'])): ?><a href="/blog/kategori/<?= e($post['category_slug']) ?>"><?= e($post['category']) ?></a>, <?php endif; ?><time datetime="<?= e(date('Y-m-d', strtotime((string)$post['published_at']))) ?>"><?= e(tr_date($post['published_at'])) ?></time></p>
  <h3><a href="/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h3>
  <p><?= e($post['excerpt'] ?: excerpt($post['content'], 140)) ?></p>
  <p class="post__time"><?= reading_time($post['content']) ?> dakikalık okuma</p>
</article>
