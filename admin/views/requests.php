<div class="page-head">
  <div class="tabs tabs--scroll">
    <a href="<?= admin_url('requests') ?>" class="<?= $filter === '' ? 'is-active' : '' ?>">Tümü <em><?= $counts[''] ?></em></a>
    <a href="<?= admin_url('requests', ['durum' => 'acik']) ?>" class="<?= $filter === 'acik' ? 'is-active' : '' ?>">Açık <em><?= $counts['acik'] ?></em></a>
    <?php foreach ($statuses as $k => [$l]): if (empty($counts[$k])) continue; ?>
      <a href="<?= admin_url('requests', ['durum' => $k]) ?>" class="<?= $filter === $k ? 'is-active' : '' ?>"><?= e($l) ?> <em><?= $counts[$k] ?></em></a>
    <?php endforeach; ?>
  </div>
  <div class="page-head__actions">
    <form class="search" method="get"><input type="hidden" name="p" value="requests"><?php if ($filter): ?><input type="hidden" name="durum" value="<?= e($filter) ?>"><?php endif; ?><?= icon('search') ?><input type="search" name="q" value="<?= e($search) ?>" placeholder="Kod, ad, telefon, cihaz…"></form>
    <a href="<?= admin_url('requests', ['a' => 'edit']) ?>" class="btn btn--primary"><?= icon('plus') ?> Yeni Kayıt</a>
  </div>
</div>
<div class="card card--flush">
  <?php if ($rows): ?>
  <table class="table">
    <thead><tr><th>Kod</th><th>Müşteri</th><th class="hide-sm">Cihaz</th><th>Durum</th><th class="hide-sm">Tarih</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($rows as $r): ?>
      <tr class="<?= $r['status'] === 'received' ? 'is-new' : '' ?>">
        <td class="nowrap"><a href="<?= admin_url('requests', ['a' => 'edit', 'id' => $r['id']]) ?>" class="mono"><?= e($r['code']) ?></a></td>
        <td><strong><?= e($r['name']) ?></strong><small class="block muted"><?= e($r['phone']) ?><?= $r['company'] ? ' · ' . e($r['company']) : '' ?></small></td>
        <td class="hide-sm"><?= e(trim($r['brand'] . ' ' . $r['device'])) ?><small class="block muted"><?= e($r['model']) ?><?= $r['warranty'] ? ' · <span class="tag tag--green">Garanti</span>' : '' ?></small></td>
        <td><span class="status status--<?= status_color($r['status']) ?>"><?= e(status_label($r['status'])) ?></span></td>
        <td class="hide-sm nowrap muted"><?= e(tr_date($r['created_at'], true)) ?></td>
        <td class="actions">
          <a href="<?= e(tel_href($r['phone'])) ?>" class="icon-btn" title="Ara"><?= icon('phone') ?></a>
          <a href="<?= admin_url('requests', ['a' => 'edit', 'id' => $r['id']]) ?>" class="icon-btn" title="Düzenle"><?= icon('edit') ?></a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div class="empty"><?= icon('clipboard') ?><h3>Kayıt bulunamadı</h3><p class="muted">Web sitesinden gelen talepler ve elden teslim alınan cihazlar burada listelenir.</p><a href="<?= admin_url('requests', ['a' => 'edit']) ?>" class="btn btn--primary"><?= icon('plus') ?> Yeni Servis Kaydı</a></div>
  <?php endif; ?>
</div>
