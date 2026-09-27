<?php $shareUrl = base_url() . '/blog/' . $post['slug']; ?>
<div class="read-progress" data-progress></div>
<?php if ($preview): ?><div class="preview-bar">Önizleme: bu yazı henüz yayında değil. <a href="/admin/?p=posts&amp;a=edit&amp;id=<?= (int)$post['id'] ?>">Düzenle</a></div><?php endif; ?>
<article>
  <header class="article-head">
    <div class="container container--mid">
      <nav class="breadcrumb" aria-label="Sayfa yolu">
        <a href="/">Ana sayfa</a><span aria-hidden="true">/</span><a href="/blog">Blog</a>
        <?php if ($post['category']): ?><span aria-hidden="true">/</span><a href="/blog/kategori/<?= e($post['category_slug']) ?>"><?= e($post['category']) ?></a><?php endif; ?>
      </nav>
      <h1><?= e($post['title']) ?></h1>
      <?php if ($post['excerpt']): ?><p class="lead"><?= e($post['excerpt']) ?></p><?php endif; ?>
      <p class="article-meta"><?= e(tr_date($post['published_at'] ?: $post['created_at'])) ?>, <?= reading_time($post['content']) ?> dakikalık okuma</p>
      <?php if ($post['cover']): ?><div class="article-cover"><img src="<?= e(upload_url($post['cover'])) ?>" alt=""></div><?php endif; ?>
    </div>
  </header>
  <div class="block block--tight">
    <div class="container article">
      <aside class="article__side">
        <?php if (count($toc) > 1): ?>
        <nav class="toc" aria-label="İçindekiler">
          <h2>Bu yazıda</h2>
          <ol><?php foreach ($toc as $t): ?><li class="toc--h<?= $t['level'] ?>"><a href="#<?= e($t['id']) ?>"><?= e($t['text']) ?></a></li><?php endforeach; ?></ol>
        </nav>
        <?php endif; ?>
        <div>
          <h2>Paylaşın</h2>
          <div class="share">
            <a href="https://wa.me/?text=<?= rawurlencode($post['title'] . ' ' . $shareUrl) ?>" target="_blank" rel="noopener" aria-label="WhatsApp'ta paylaş"><?= icon('whatsapp') ?></a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="LinkedIn'de paylaş"><?= icon('linkedin') ?></a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= rawurlencode($shareUrl) ?>" target="_blank" rel="noopener" aria-label="Facebook'ta paylaş"><?= icon('facebook') ?></a>
            <button type="button" data-copy="<?= e($shareUrl) ?>" aria-label="Bağlantıyı kopyala"><?= icon('link') ?></button>
          </div>
        </div>
      </aside>
      <div>
        <div class="prose post-content"><?= $post['content'] ?></div>
        <div class="callout">
          <p><strong>Projeniz için doğru ürünü birlikte seçelim</strong>İhtiyacınızı yazın, kapasite hesabıyla birlikte teklif gönderelim.</p>
          <a href="/teklif-al" class="btn btn--signal">Teklif iste</a>
        </div>
        <?php if ($prev || $next): ?>
        <nav class="post-nav" aria-label="Diğer yazılar">
          <?php if ($prev): ?><a href="/blog/<?= e($prev['slug']) ?>"><small>Önceki yazı</small><span><?= e($prev['title']) ?></span></a><?php else: ?><span></span><?php endif; ?>
          <?php if ($next): ?><a href="/blog/<?= e($next['slug']) ?>" class="post-nav__next"><small>Sonraki yazı</small><span><?= e($next['title']) ?></span></a><?php endif; ?>
        </nav>
        <?php endif; ?>
      </div>
    </div>
  </div>
</article>
<?php if ($related): ?>
<section class="block block--steel">
  <div class="container">
    <div class="block__head"><h2>Diğer rehberler</h2></div>
    <div class="post-grid"><?php foreach ($related as $r) partial('post-card', ['post' => $r]); ?></div>
  </div>
</section>
<?php endif; ?>
