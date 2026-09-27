<?php $shareUrl = base_url() . '/blog/' . $post['slug']; ?>
<div class="read-progress" data-progress></div>
<?php if ($preview): ?><div class="preview-bar">Önizleme modu — bu yazı henüz yayında değil. <a href="/admin/?p=posts&amp;a=edit&amp;id=<?= (int)$post['id'] ?>">Düzenle</a></div><?php endif; ?>
<article>
  <header class="post-hero">
    <div class="container container--mid">
      <nav class="breadcrumb" aria-label="Sayfa yolu">
        <a href="/">Ana Sayfa</a><?= icon('chevron-right') ?><a href="/blog">Blog</a>
        <?php if ($post['category']): ?><?= icon('chevron-right') ?><a href="/blog/kategori/<?= e($post['category_slug']) ?>"><?= e($post['category']) ?></a><?php endif; ?>
      </nav>
      <h1><?= e($post['title']) ?></h1>
      <?php if ($post['excerpt']): ?><p class="lead"><?= e($post['excerpt']) ?></p><?php endif; ?>
      <div class="post-meta post-meta--light">
        <span><?= icon('calendar') ?> <?= e(tr_date($post['published_at'] ?: $post['created_at'])) ?></span>
        <span><?= icon('clock') ?> <?= reading_time($post['content']) ?> dk okuma</span>
        <span><?= icon('eye') ?> <?= number_format((int)$post['views'], 0, ',', '.') ?> görüntülenme</span>
      </div>
    </div>
  </header>
  <?php if ($post['cover']): ?>
    <div class="container container--mid post-cover"><img src="<?= e(upload_url($post['cover'])) ?>" alt="<?= e($post['title']) ?>"></div>
  <?php endif; ?>
  <div class="section section--tight">
    <div class="container post-layout">
      <aside class="post-aside">
        <?php if (count($toc) > 1): ?>
        <div class="toc">
          <h2>İçindekiler</h2>
          <ol>
            <?php foreach ($toc as $t): ?><li class="toc--h<?= $t['level'] ?>"><a href="#<?= e($t['id']) ?>"><?= e($t['text']) ?></a></li><?php endforeach; ?>
          </ol>
        </div>
        <?php endif; ?>
        <div class="share">
          <span>Paylaş</span>
          <a href="https://wa.me/?text=<?= rawurlencode($post['title'] . ' ' . $shareUrl) ?>" target="_blank" rel="noopener" aria-label="WhatsApp'ta paylaş"><?= icon('whatsapp') ?></a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="Facebook'ta paylaş"><?= icon('facebook') ?></a>
          <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="LinkedIn'de paylaş"><?= icon('linkedin') ?></a>
          <button type="button" data-copy="<?= e($shareUrl) ?>" aria-label="Bağlantıyı kopyala"><?= icon('link') ?></button>
        </div>
      </aside>
      <div class="prose post-content">
        <?= $post['content'] ?>
        <div class="inline-cta">
          <span class="inline-cta__icon"><?= icon('calculator') ?></span>
          <div><strong>Projeniz için doğru ürünü birlikte seçelim</strong><p>İhtiyacınızı paylaşın, size özel teklifle dönüş yapalım.</p></div>
          <a href="/teklif-al" class="btn btn--primary">Teklif Al</a>
        </div>
        <?php if ($prev || $next): ?>
        <nav class="post-nav" aria-label="Diğer yazılar">
          <?php if ($prev): ?><a href="/blog/<?= e($prev['slug']) ?>" class="post-nav__prev"><small><?= icon('arrow-left') ?> Önceki yazı</small><span><?= e($prev['title']) ?></span></a><?php else: ?><span></span><?php endif; ?>
          <?php if ($next): ?><a href="/blog/<?= e($next['slug']) ?>" class="post-nav__next"><small>Sonraki yazı <?= icon('arrow-right') ?></small><span><?= e($next['title']) ?></span></a><?php endif; ?>
        </nav>
        <?php endif; ?>
      </div>
    </div>
  </div>
</article>
<?php if ($related): ?>
<section class="section section--muted">
  <div class="container">
    <div class="section-head section-head--row"><div><span class="eyebrow">Okumaya devam edin</span><h2>İlgili yazılar</h2></div></div>
    <div class="posts-grid">
      <?php foreach ($related as $r) partial('post-card', ['post' => $r]); ?>
    </div>
  </div>
</section>
<?php endif; ?>
