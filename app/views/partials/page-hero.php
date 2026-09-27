<section class="page-head">
  <div class="container">
    <nav class="breadcrumb" aria-label="Sayfa yolu">
      <a href="/">Ana sayfa</a>
      <?php foreach (($crumbs ?? []) as [$href, $label]): ?>
        <span aria-hidden="true">/</span><?php if ($href): ?><a href="<?= e($href) ?>"><?= e($label) ?></a><?php else: ?><span aria-current="page"><?= e($label) ?></span><?php endif; ?>
      <?php endforeach; ?>
    </nav>
    <h1><?= e($heading) ?></h1>
    <?php if (!empty($lead)): ?><p class="lead"><?= e($lead) ?></p><?php endif; ?>
  </div>
</section>
