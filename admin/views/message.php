<a href="<?= admin_url('messages') ?>" class="back-link"><?= icon('arrow-left') ?> Mesajlar</a>
<div class="card message">
  <div class="message__head">
    <span class="avatar avatar--lg"><?= e(mb_strtoupper(mb_substr($msg['name'], 0, 1))) ?></span>
    <div>
      <h2><?= e($msg['subject'] ?: 'Konu yok') ?></h2>
      <p class="muted"><strong><?= e($msg['name']) ?></strong> · <?= e(tr_date($msg['created_at'], true)) ?></p>
    </div>
  </div>
  <div class="message__meta">
    <?php if ($msg['phone']): ?><a href="<?= e(tel_href($msg['phone'])) ?>" class="btn"><?= icon('phone') ?> <?= e($msg['phone']) ?></a><a href="<?= e(wa_href($msg['phone'])) ?>" target="_blank" rel="noopener" class="btn btn--wa"><?= icon('whatsapp') ?> WhatsApp</a><?php endif; ?>
    <?php if ($msg['email']): ?><a href="mailto:<?= e($msg['email']) ?>?subject=<?= rawurlencode('Re: ' . ($msg['subject'] ?: 'Mesajınız')) ?>" class="btn btn--primary"><?= icon('mail') ?> Yanıtla</a><?php endif; ?>
  </div>
  <div class="message__body"><?= nl2br(e($msg['message'])) ?></div>
  <div class="message__foot">
    <form method="post" action="<?= admin_url('messages', ['a' => 'unread', 'id' => $msg['id']]) ?>"><?= csrf_field() ?><button class="btn">Okunmadı olarak işaretle</button></form>
    <form method="post" action="<?= admin_url('messages', ['a' => 'delete', 'id' => $msg['id']]) ?>" data-confirm="Mesaj silinsin mi?"><?= csrf_field() ?><button class="btn btn--danger-ghost"><?= icon('trash') ?> Sil</button></form>
  </div>
</div>
