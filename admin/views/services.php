<div class="page-head">
  <p class="muted">Sürükleyerek sıralayın. Sıralama ana sayfada ve menüde kullanılır.</p>
  <a href="<?= admin_url('services', ['a' => 'edit']) ?>" class="btn btn--primary"><?= icon('plus') ?> Yeni Hizmet</a>
</div>
<ul class="sortable" data-sortable="<?= admin_url('services', ['a' => 'sort']) ?>">
  <?php foreach ($rows as $r): ?>
    <li class="sortable__item card" draggable="true" data-id="<?= $r['id'] ?>">
      <span class="drag-handle" title="Sürükle">⋮⋮</span>
      <span class="svc-icon"><?= icon($r['icon']) ?></span>
      <div class="sortable__body"><strong><?= e($r['title']) ?></strong><small class="muted"><?= e(excerpt($r['summary'], 110)) ?></small></div>
      <?= $r['active'] ? '<span class="status status--green">Aktif</span>' : '<span class="status status--slate">Pasif</span>' ?>
      <div class="actions">
        <a href="/hizmetler/<?= e($r['slug']) ?>" target="_blank" class="icon-btn" title="Görüntüle"><?= icon('eye') ?></a>
        <a href="<?= admin_url('services', ['a' => 'edit', 'id' => $r['id']]) ?>" class="icon-btn" title="Düzenle"><?= icon('edit') ?></a>
        <form method="post" action="<?= admin_url('services', ['a' => 'delete', 'id' => $r['id']]) ?>" data-confirm="“<?= e($r['title']) ?>” silinsin mi?"><?= csrf_field() ?><button class="icon-btn icon-btn--danger" title="Sil"><?= icon('trash') ?></button></form>
      </div>
    </li>
  <?php endforeach; ?>
</ul>
