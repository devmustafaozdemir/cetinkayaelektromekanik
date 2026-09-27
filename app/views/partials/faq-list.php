<div class="faq">
  <?php foreach ($faqs as $i => $f): ?>
    <details class="faq__item"<?= $i === 0 ? ' open' : '' ?>>
      <summary><?= e($f['question']) ?><span class="faq__icon"><?= icon('plus') ?></span></summary>
      <div class="faq__answer"><p><?= nl2br(e($f['answer'])) ?></p></div>
    </details>
  <?php endforeach; ?>
</div>
