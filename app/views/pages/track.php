<?php
$flow = ['received', 'inspecting', 'quote', 'repairing', 'ready', 'delivered'];
?>
<?php partial('page-hero', ['heading' => 'Servis Takip', 'lead' => 'Takip kodunuz ve telefon numaranızla cihazınızın servis durumunu anlık öğrenin.', 'crumbs' => [[null, 'Servis Takip']]]); ?>
<section class="section">
  <div class="container container--mid">
    <form class="track-form" method="get">
      <label class="field"><span>Takip Kodu</span><input type="text" name="kod" value="<?= e($code) ?>" placeholder="CE-26XXXXX" required autocomplete="off" style="text-transform:uppercase"></label>
      <label class="field"><span>Telefon (son 4 hane yeterli)</span><input type="text" name="telefon" value="<?= e($phone) ?>" inputmode="numeric" placeholder="1320" required></label>
      <button class="btn btn--primary btn--lg"><?= icon('search') ?> Sorgula</button>
    </form>

    <?php if ($error): ?>
      <div class="alert alert--error"><?= icon('help') ?> <?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($result):
      $status = $result['status'];
      $idx = array_search($status === 'parts' ? 'repairing' : $status, $flow, true);
    ?>
      <div class="track-result reveal">
        <div class="track-result__head">
          <div>
            <small>Takip Kodu</small>
            <h2><?= e($result['code']) ?></h2>
            <p><?= e(trim($result['brand'] . ' ' . $result['device'] . ' ' . $result['model'])) ?></p>
          </div>
          <span class="status status--<?= e(status_color($status)) ?>"><?= e(status_label($status)) ?></span>
        </div>
        <?php if ($status !== 'cancelled'): ?>
        <ol class="stepper">
          <?php foreach ($flow as $i => $s): ?>
            <li class="<?= $idx !== false && $i < $idx ? 'done' : ($i === $idx ? 'current' : '') ?>"><span class="stepper__dot"><?= $idx !== false && $i < $idx ? icon('check') : $i + 1 ?></span><span class="stepper__label"><?= e(status_label($s)) ?></span></li>
          <?php endforeach; ?>
        </ol>
        <?php endif; ?>
        <?php if ($result['admin_note']): ?>
          <div class="note"><strong>Servis notu:</strong> <?= nl2br(e($result['admin_note'])) ?></div>
        <?php endif; ?>
        <h3 class="timeline-title">Süreç geçmişi</h3>
        <ul class="timeline">
          <?php foreach (array_reverse($result['log']) as $l): ?>
            <li><span class="timeline__dot status--<?= e(status_color($l['status'])) ?>"></span><div><strong><?= e(status_label($l['status'])) ?></strong><?php if ($l['note']): ?><p><?= e($l['note']) ?></p><?php endif; ?><time><?= e(tr_date($l['created_at'], true)) ?></time></div></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php elseif (!$error): ?>
      <div class="empty-box"><?= icon('clipboard') ?><p>Takip kodunuz servis talebi oluşturduğunuzda veya cihazınızı teslim ettiğinizde size iletilir.<br><a href="/servis-talebi">Yeni servis talebi oluşturun →</a></p></div>
    <?php endif; ?>
  </div>
</section>
