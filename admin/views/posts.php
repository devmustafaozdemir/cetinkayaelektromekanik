<div class="page-head">
  <div class="tabs">
    <?php foreach (['' => 'Tümü', 'published' => 'Yayında', 'draft' => 'Taslak'] as $k => $l): ?>
      <a href="<?= admin_url('posts', array_filter(['durum' => $k])) ?>" class="<?= $status === $k ? 'is-active' : '' ?>"><?= $l ?> <em><?= $counts[$k] ?></em></a>
    <?php endforeach; ?>
  </div>
  <div class="page-head__actions">
    <form class="search" method="get"><input type="hidden" name="p" value="posts"><?= icon('search') ?><input type="search" name="q" value="<?= e($search) ?>" placeholder="Yazı ara…"></form>
    <a href="<?= admin_url('posts', ['a' => 'edit']) ?>" class="btn btn--primary"><?= icon('plus') ?> Yeni Yazı</a>
  </div>
</div>
<div class="card card--flush">
  <?php if ($posts): ?>
  <table class="table">
    <thead><tr><th>Başlık</th><th class="hide-sm">Kategori</th><th>Durum</th><th class="hide-sm">Tarih</th><th class="hide-sm num">Okunma</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($posts as $post): ?>
      <tr>
        <td>
          <a href="<?= admin_url('posts', ['a' => 'edit', 'id' => $post['id']]) ?>" class="row-title">
            <span class="thumb"><?php if ($post['cover']): ?><img src="<?= e(upload_url($post['cover'])) ?>" alt=""><?php else: ?><?= icon('file-text') ?><?php endif; ?></span>
            <span><?= e($post['title']) ?><?php if ($post['featured']): ?> <span class="tag tag--amber"><?= icon('star') ?> Öne çıkan</span><?php endif; ?><small>/blog/<?= e($post['slug']) ?></small></span>
          </a>
        </td>
        <td class="hide-sm"><?= e($post['category'] ?? '—') ?></td>
        <td><?php if ($post['status'] === 'published'): ?><?= strtotime((string)$post['published_at']) > time() ? '<span class="status status--blue">Zamanlandı</span>' : '<span class="status status--green">Yayında</span>' ?><?php else: ?><span class="status status--slate">Taslak</span><?php endif; ?></td>
        <td class="hide-sm nowrap"><?= e(tr_date($post['published_at'] ?: $post['created_at'])) ?></td>
        <td class="hide-sm num"><?= (int)$post['views'] ?></td>
        <td class="actions">
          <a href="/blog/<?= e($post['slug']) ?>" target="_blank" class="icon-btn" title="Görüntüle"><?= icon('eye') ?></a>
          <a href="<?= admin_url('posts', ['a' => 'edit', 'id' => $post['id']]) ?>" class="icon-btn" title="Düzenle"><?= icon('edit') ?></a>
          <form method="post" action="<?= admin_url('posts', ['a' => 'delete', 'id' => $post['id']]) ?>" data-confirm="“<?= e($post['title']) ?>” silinsin mi? Bu işlem geri alınamaz."><?= csrf_field() ?><button class="icon-btn icon-btn--danger" title="Sil"><?= icon('trash') ?></button></form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div class="empty"><?= icon('file-text') ?><h3>Yazı bulunamadı</h3><p class="muted">İlk blog yazınızı oluşturarak başlayın.</p><a href="<?= admin_url('posts', ['a' => 'edit']) ?>" class="btn btn--primary"><?= icon('plus') ?> Yeni Yazı</a></div>
  <?php endif; ?>
</div>
