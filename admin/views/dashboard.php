<?php $max = max(1, max($days)); $user = current_user(); ?>
<div class="welcome">
  <div>
    <h2>Merhaba, <?= e(explode(' ', $user['name'] ?: $user['username'])[0]) ?> 👋</h2>
    <p class="muted"><?= e(tr_date(date('Y-m-d'))) ?> — işte servisinizin güncel durumu.</p>
  </div>
  <div class="welcome__actions">
    <a href="<?= admin_url('posts', ['a' => 'edit']) ?>" class="btn"><?= icon('edit') ?> Yeni Yazı</a>
    <a href="<?= admin_url('requests', ['a' => 'edit']) ?>" class="btn btn--primary"><?= icon('plus') ?> Servis Kaydı</a>
  </div>
</div>

<div class="kpis">
  <a href="<?= admin_url('requests', ['durum' => 'acik']) ?>" class="kpi"><span class="kpi__icon kpi__icon--amber"><?= icon('wrench') ?></span><div><strong><?= $stats['open'] ?></strong><span>Açık servis kaydı</span></div><small><?= $stats['new'] ?> yeni talep</small></a>
  <a href="<?= admin_url('requests', ['durum' => 'ready']) ?>" class="kpi"><span class="kpi__icon kpi__icon--green"><?= icon('check-circle') ?></span><div><strong><?= $stats['ready'] ?></strong><span>Teslime hazır</span></div><small>Bu ay <?= $stats['month'] ?> kayıt</small></a>
  <a href="<?= admin_url('messages') ?>" class="kpi"><span class="kpi__icon kpi__icon--blue"><?= icon('inbox') ?></span><div><strong><?= $stats['unread'] ?></strong><span>Okunmamış mesaj</span></div><small>İletişim formu</small></a>
  <a href="<?= admin_url('posts') ?>" class="kpi"><span class="kpi__icon kpi__icon--violet"><?= icon('eye') ?></span><div><strong><?= number_format($stats['views'], 0, ',', '.') ?></strong><span>Blog görüntülenme</span></div><small><?= $stats['posts'] ?> yayında · <?= $stats['drafts'] ?> taslak</small></a>
</div>

<div class="grid-2">
  <section class="card">
    <div class="card__head"><h3>Son 14 gün — servis talepleri</h3></div>
    <div class="bars" role="img" aria-label="Son 14 gündeki günlük servis talebi sayıları">
      <?php foreach ($days as $d => $c): ?>
        <div class="bars__col" title="<?= e(tr_date($d)) ?>: <?= $c ?> talep">
          <span class="bars__val"><?= $c ?: '' ?></span>
          <span class="bars__bar" style="height: <?= max(3, round($c / $max * 100)) ?>%"></span>
          <span class="bars__label"><?= date('d', strtotime($d)) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <section class="card">
    <div class="card__head"><h3>Açık kayıtların durumu</h3></div>
    <?php if ($byStatus): $total = array_sum($byStatus); ?>
      <ul class="status-bars">
        <?php foreach (request_statuses() as $key => [$label, $color]): if (empty($byStatus[$key])) continue; ?>
          <li><a href="<?= admin_url('requests', ['durum' => $key]) ?>"><span class="status status--<?= $color ?>"><?= e($label) ?></span><span class="status-bars__track"><span class="status-bars__fill fill--<?= $color ?>" style="width: <?= round($byStatus[$key] / $total * 100) ?>%"></span></span><strong><?= $byStatus[$key] ?></strong></a></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">Açık servis kaydı yok.</p>
    <?php endif; ?>
  </section>
</div>

<div class="grid-2">
  <section class="card">
    <div class="card__head"><h3>Son servis kayıtları</h3><a href="<?= admin_url('requests') ?>" class="link">Tümü →</a></div>
    <?php if ($requests): ?>
    <ul class="list">
      <?php foreach ($requests as $r): ?>
        <li><a href="<?= admin_url('requests', ['a' => 'edit', 'id' => $r['id']]) ?>">
          <div><strong><?= e($r['name']) ?></strong><small><?= e($r['code']) ?> · <?= e(trim($r['brand'] . ' ' . $r['device'])) ?></small></div>
          <span class="status status--<?= status_color($r['status']) ?>"><?= e(status_label($r['status'])) ?></span>
        </a></li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?><p class="muted">Henüz servis kaydı yok.</p><?php endif; ?>
  </section>
  <section class="card">
    <div class="card__head"><h3>Son mesajlar</h3><a href="<?= admin_url('messages') ?>" class="link">Tümü →</a></div>
    <?php if ($messages): ?>
    <ul class="list">
      <?php foreach ($messages as $m): ?>
        <li><a href="<?= admin_url('messages', ['a' => 'view', 'id' => $m['id']]) ?>">
          <div><strong><?php if (!$m['is_read']): ?><span class="dot"></span><?php endif; ?><?= e($m['name']) ?></strong><small><?= e(excerpt($m['subject'] ?: $m['message'], 60)) ?></small></div>
          <small class="muted"><?= e(tr_date($m['created_at'])) ?></small>
        </a></li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?><p class="muted">Henüz mesaj yok.</p><?php endif; ?>
    <?php if ($top): ?>
      <div class="card__head mt"><h3>En çok okunan yazılar</h3></div>
      <ul class="list list--compact">
        <?php foreach ($top as $t): ?><li><a href="<?= admin_url('posts', ['a' => 'edit', 'id' => $t['id']]) ?>"><span><?= e($t['title']) ?></span><small class="muted"><?= icon('eye') ?> <?= (int)$t['views'] ?></small></a></li><?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </section>
</div>
