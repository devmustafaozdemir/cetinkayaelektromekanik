<div class="page-head">
  <div class="tabs tabs--scroll">
    <a href="<?= admin_url('quotes') ?>" class="<?= $filter === '' ? 'is-active' : '' ?>">Tümü <em><?= $counts[''] ?></em></a>
    <a href="<?= admin_url('quotes', ['durum' => 'acik']) ?>" class="<?= $filter === 'acik' ? 'is-active' : '' ?>">Açık <em><?= $counts['acik'] ?></em></a>
    <?php foreach ($statuses as $k => [$l]): ?>
      <a href="<?= admin_url('quotes', ['durum' => $k]) ?>" class="<?= $filter === $k ? 'is-active' : '' ?>"><?= e($l) ?> <em><?= $counts[$k] ?? 0 ?></em></a>
    <?php endforeach; ?>
  </div>
  <div class="page-head__actions">
    <form class="search" method="get"><input type="hidden" name="p" value="quotes"><?php if ($filter): ?><input type="hidden" name="durum" value="<?= e($filter) ?>"><?php endif; ?><?= icon('search') ?><input type="search" name="q" value="<?= e($search) ?>" placeholder="Ad, firma, telefon, ürün…"></form>
    <a href="<?= admin_url('quotes', ['a' => 'edit']) ?>" class="btn btn--primary"><?= icon('plus') ?> Yeni Kayıt</a>
  </div>
</div>
<div class="card card--flush">
  <?php if ($rows): ?>
  <table class="table">
    <thead><tr><th>Müşteri</th><th class="hide-sm">Talep</th><th>Durum</th><th class="hide-sm">Tarih</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr class="<?= $r['status'] === 'new' ? 'is-new' : '' ?>">
        <td>
          <a href="<?= admin_url('quotes', ['a' => 'edit', 'id' => $r['id']]) ?>" class="row-title"><span><?= e($r['name']) ?><small><?= e($r['company'] ? $r['company'] . ' · ' : '') ?><?= e($r['phone']) ?></small></span></a>
        </td>
        <td class="hide-sm"><strong class="block"><?= e($r['product_name'] ?: ($r['category'] ?: '—')) ?></strong><small class="muted"><span class="mono small"><?= e($r['code']) ?></span> · <?= e(quote_types()[$r['type']] ?? '') ?><?= $r['quantity'] ? ' · ' . e($r['quantity']) : '' ?></small></td>
        <td><span class="status status--<?= status_color($r['status']) ?>"><?= e(status_label($r['status'])) ?></span></td>
        <td class="hide-sm nowrap muted"><?= e(tr_date($r['created_at'], true)) ?></td>
        <td class="actions">
          <a href="<?= e(tel_href($r['phone'])) ?>" class="icon-btn" title="Ara"><?= icon('phone') ?></a>
          <a href="<?= e(wa_href($r['phone'])) ?>" class="icon-btn" title="WhatsApp" target="_blank" rel="noopener"><?= icon('whatsapp') ?></a>
          <a href="<?= admin_url('quotes', ['a' => 'edit', 'id' => $r['id']]) ?>" class="icon-btn" title="Aç"><?= icon('edit') ?></a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div class="empty"><?= icon('calculator') ?><h3>Teklif talebi bulunamadı</h3><p class="muted">Web sitesinden gelen teklif talepleri ve telefonla alınan talepler burada listelenir.</p><a href="<?= admin_url('quotes', ['a' => 'edit']) ?>" class="btn btn--primary"><?= icon('plus') ?> Yeni Kayıt</a></div>
  <?php endif; ?>
</div>
