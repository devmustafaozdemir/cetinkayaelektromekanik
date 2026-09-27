<article class="post-card reveal">
  <a href="/blog/<?= e($post['slug']) ?>" class="post-card__media" tabindex="-1" aria-hidden="true">
    <?php if ($post['cover']): ?>
      <img src="<?= e(upload_url($post['cover'])) ?>" alt="" loading="lazy">
    <?php else: ?>
      <span class="cover-fallback"><?= icon('book') ?></span>
    <?php endif; ?>
  </a>
  <div class="post-card__body">
    <div class="post-meta">
      <?php if (!empty($post['category'])): ?><a class="chip" href="/blog/kategori/<?= e($post['category_slug']) ?>"><?= e($post['category']) ?></a><?php endif; ?>
      <span><?= e(tr_date($post['published_at'])) ?></span>
      <span>· <?= reading_time($post['content']) ?> dk okuma</span>
    </div>
    <h3><a href="/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h3>
    <p><?= e($post['excerpt'] ?: excerpt($post['content'], 140)) ?></p>
    <a href="/blog/<?= e($post['slug']) ?>" class="link-arrow">Devamını oku <?= icon('arrow-right') ?></a>
  </div>
</article>
