<?php $max = max(1, max($days)); $user = current_user(); ?>
<div class="welcome">
  <div>
    <h2>Merhaba, <?= e(explode(' ', $user['name'] ?: $user['username'])[0]) ?> 👋</h2>
    <p class="muted"><?= e(tr_date(date('Y-m-d'))) ?> — satış ve teklif durumunuz.</p>
  </div>
  <div class="welcome__actions">
    <a href="<?= admin_url('products', ['a' => 'edit']) ?>" class="btn"><?= icon('package') ?> Yeni Ürün</a>
    <a href="<?= admin_url('quotes', ['a' => 'edit']) ?>" class="btn btn--primary"><?= icon('plus') ?> Teklif Kaydı</a>
  </div>
</div>

<div class="kpis">
  <a href="<?= admin_url('quotes', ['durum' => 'new']) ?>" class="kpi"><span class="kpi__icon kpi__icon--amber"><?= icon('calculator') ?></span><div><strong><?= $stats['new'] ?></strong><span>Yeni teklif talebi</span></div><small><?= $stats['open'] ?> açık talep · bu ay <?= $stats['month'] ?> talep</small></a>
  <a href="<?= admin_url('quotes', ['durum' => 'won']) ?>" class="kpi"><span class="kpi__icon kpi__icon--green"><?= icon('handshake') ?></span><div><strong><?= $stats['won'] ?></strong><span>Bu ay satışa dönen</span></div><small>Dönüşüm oranı: <?= $stats['rate'] === null ? '—' : '%' . $stats['rate'] ?></small></a>
  <a href="<?= admin_url('messages') ?>" class="kpi"><span class="kpi__icon kpi__icon--blue"><?= icon('inbox') ?></span><div><strong><?= $stats['unread'] ?></strong><span>Okunmamış mesaj</span></div><small>İletişim formu</small></a>
  <a href="<?= admin_url('products') ?>" class="kpi"><span class="kpi__icon kpi__icon--violet"><?= icon('package') ?></span><div><strong><?= $stats['products'] ?></strong><span>Yayındaki ürün</span></div><small>Blog: <?= number_format($stats['views'], 0, ',', '.') ?> görüntülenme</small></a>
</div>

<div class="grid-2">
  <section class="card">
    <div class="card__head"><h3>Son 14 gün — teklif talepleri</h3></div>
    <div class="bars" role="img" aria-label="Son 14 gündeki günlük teklif talebi sayıları">
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
    <div class="card__head"><h3>Satış hunisi</h3></div>
    <?php if ($byStatus): $total = array_sum($byStatus); ?>
      <ul class="status-bars">
        <?php foreach (quote_statuses() as $key => [$label, $color]): ?>
          <li><a href="<?= admin_url('quotes', ['durum' => $key]) ?>"><span class="status status--<?= $color ?>"><?= e($label) ?></span><span class="status-bars__track"><span class="status-bars__fill fill--<?= $color ?>" style="width: <?= round(($byStatus[$key] ?? 0) / $total * 100) ?>%"></span></span><strong><?= $byStatus[$key] ?? 0 ?></strong></a></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="muted">Henüz teklif talebi yok.</p>
    <?php endif; ?>
  </section>
</div>

<div class="grid-2">
  <section class="card">
    <div class="card__head"><h3>Son teklif talepleri</h3><a href="<?= admin_url('quotes') ?>" class="link">Tümü →</a></div>
    <?php if ($quotes): ?>
    <ul class="list">
      <?php foreach ($quotes as $r): ?>
        <li><a href="<?= admin_url('quotes', ['a' => 'edit', 'id' => $r['id']]) ?>">
          <div><strong><?= e($r['name']) ?><?= $r['company'] ? ' · ' . e($r['company']) : '' ?></strong><small><?= e($r['product_name'] ?: ($r['category'] ?: quote_types()[$r['type']] ?? '')) ?> · <?= e(tr_date($r['created_at'])) ?></small></div>
          <span class="status status--<?= status_color($r['status']) ?>"><?= e(status_label($r['status'])) ?></span>
        </a></li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?><p class="muted">Henüz teklif talebi yok.</p><?php endif; ?>
  </section>
  <section class="card">
    <div class="card__head"><h3>En çok teklif istenenler</h3></div>
    <?php if ($top): ?>
      <ul class="list list--compact">
        <?php foreach ($top as $t): ?><li><a href="<?= admin_url('quotes', ['q' => $t['name']]) ?>"><span><?= e($t['name']) ?></span><small class="muted"><?= (int)$t['c'] ?> talep</small></a></li><?php endforeach; ?>
      </ul>
    <?php else: ?><p class="muted">Veri yok.</p><?php endif; ?>
    <div class="card__head mt"><h3>Son mesajlar</h3><a href="<?= admin_url('messages') ?>" class="link">Tümü →</a></div>
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
  </section>
</div>
