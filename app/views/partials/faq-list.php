<div class="faq">
  <?php foreach ($faqs as $f): ?>
    <details class="faq__item">
      <summary><?= e($f['question']) ?><span class="faq__sign" aria-hidden="true"></span></summary>
      <p><?= nl2br(e($f['answer'])) ?></p>
    </details>
  <?php endforeach; ?>
</div>
