<div class="page-head">
  <p class="muted">İletişim formundan gelen mesajlar.</p>
  <?php if ($rows): ?><form method="post" action="<?= admin_url('messages', ['a' => 'readall']) ?>"><?= csrf_field() ?><button class="btn"><?= icon('check') ?> Tümünü okundu yap</button></form><?php endif; ?>
</div>
<div class="card card--flush">
  <?php if ($rows): ?>
  <ul class="inbox">
    <?php foreach ($rows as $m): ?>
      <li class="<?= $m['is_read'] ? '' : 'is-unread' ?>">
        <a href="<?= admin_url('messages', ['a' => 'view', 'id' => $m['id']]) ?>">
          <span class="avatar avatar--soft"><?= e(mb_strtoupper(mb_substr($m['name'], 0, 1))) ?></span>
          <div class="inbox__body">
            <div class="inbox__top"><strong><?= e($m['name']) ?></strong><time><?= e(tr_date($m['created_at'], true)) ?></time></div>
            <span class="inbox__subject"><?= e($m['subject'] ?: 'Konu yok') ?></span>
            <small class="muted"><?= e(excerpt($m['message'], 120)) ?></small>
          </div>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
  <?php else: ?>
    <div class="empty"><?= icon('inbox') ?><h3>Gelen kutusu boş</h3><p class="muted">İletişim formundan gelen mesajlar burada görünür.</p></div>
  <?php endif; ?>
</div>
