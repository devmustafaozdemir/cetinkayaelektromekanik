<li class="ref-card">
  <div class="ref-card__logo"><?php if ($r['logo'] !== ''): ?><img src="<?= e(upload_url($r['logo'])) ?>" alt="<?= e($r['name']) ?>" loading="lazy"><?php else: ?><span class="ref-card__mono" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($r['name'], 0, 1))) ?></span><?php endif; ?></div>
  <h3><?= e($r['name']) ?></h3>
  <?php if (($r['project'] ?? $r['description'] ?? '') !== ''): ?><p><?= e($r['project'] ?? $r['description']) ?></p><?php endif; ?>
  <div class="ref-card__meta">
    <?php foreach (array_filter([$r['sector'] ?? $r['kind'] ?? '', $r['city'] ?? '', $r['year'] ?? '']) as $m): ?><span><?= e($m) ?></span><?php endforeach; ?>
  </div>
  <?php if (($r['url'] ?? '') !== ''): ?><a href="<?= e($r['url']) ?>" class="text-link" target="_blank" rel="noopener">Web sitesi</a><?php endif; ?>
</li>
