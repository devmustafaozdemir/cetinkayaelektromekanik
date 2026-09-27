<?php $paras = array_filter(array_map('trim', preg_split('/\R{2,}/', setting('about_text')))); ?>
<?php partial('page-hero', ['heading' => 'Hakkımızda', 'lead' => setting('site_tagline'), 'crumbs' => [[null, 'Hakkımızda']]]); ?>
<section class="block">
  <div class="container duo">
    <div>
      <h2><?= e(setting('about_title')) ?></h2>
      <?php foreach ($paras as $p): ?><p><?= nl2br(e($p)) ?></p><?php endforeach; ?>
    </div>
    <div>
      <h3>Çalışma ilkelerimiz</h3>
      <ul class="ticks"><?php foreach (setting_lines('about_values') as $v): ?><li><?= e($v) ?></li><?php endforeach; ?></ul>
    </div>
  </div>
</section>
<section class="block block--steel">
  <div class="container">
    <div class="block__head block__head--row"><h2>Sattığımız markalar</h2><a href="/markalar" class="text-link">Markalar sayfası</a></div>
  </div>
  <?php partial('brand-logos'); ?>
</section>
