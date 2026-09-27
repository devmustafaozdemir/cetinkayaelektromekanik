<?php
/**
 * Modular tank designer. $mode: 'compact' (home hero) or 'full' (/depo-tasarla).
 * Logic lives in assets/js/site.js ([data-designer]); the first render is done here.
 */
$full = ($mode ?? 'compact') === 'full';
$materials = ['galvaniz' => 'Galvaniz', 'paslanmaz' => 'Paslanmaz', 'grp' => 'GRP', 'sandvic' => 'İzolasyonlu'];
[$w, $l, $h] = [4.0, 3.0, 2.0];
$vol = tank_volume($w, $l, $h);
$pc = tank_panels($w, $l, $h);
$m = fn(float $mod) => number_format($mod * TANK_MODULE, 2, ',', '.');
$uses = water_uses();
?>
<div class="sizer<?= $full ? ' sizer--full' : '' ?>" data-designer="<?= $full ? 'full' : 'compact' ?>" data-viewer="<?= e(asset('js/viewer3d.js')) ?>">
  <div class="sizer__view">
    <div class="sizer__top">
      <div>
        <h2><?= $full ? 'Depo görünümü' : 'Deponuzu tasarlayın' ?></h2>
        <p><?= $full ? 'Çizimi 3D’ye çevirip döndürebilirsiniz.' : 'Modül seçin, hacmi ve kaç daireye yeteceğini görün.' ?></p>
      </div>
      <div class="viewswitch" role="group" aria-label="Görünüm">
        <button type="button" data-view="2d" aria-pressed="true">Çizim</button>
        <button type="button" data-view="3d" aria-pressed="false">3D</button>
      </div>
    </div>
    <div class="sizer__stage" data-stage>
      <div class="sizer__svg" data-svg><?= model_svg('tank:galvaniz', 'art', ['w' => $w, 'l' => $l, 'h' => $h, 'dims' => $full]) ?></div>
      <div class="viewer" data-viewer-host hidden></div>
      <p class="viewer__hint" data-hint hidden>Sürükleyerek çevirin</p>
      <span class="sizer__tag"><span data-modules>4 × 3 × 2 modül</span> · 1 modül = 1,08 m</span>
    </div>
  </div>

  <div class="sizer__side">
    <?php if ($full): ?>
      <div class="sizer__tabs" role="tablist" aria-label="Hesaplama yöntemi">
        <button type="button" role="tab" data-tab="olcu" aria-selected="true"><?= icon('ruler') ?> Ölçüye göre</button>
        <button type="button" role="tab" data-tab="ihtiyac" aria-selected="false" tabindex="-1"><?= icon('users') ?> İhtiyaca göre</button>
      </div>
    <?php endif; ?>

    <div data-pane="olcu">
      <div class="sizer__controls">
        <?php foreach (['w' => ['En', $w, 1, 20, 'modül'], 'l' => ['Boy', $l, 1, 20, 'modül'], 'h' => ['Yükseklik', $h, 0.5, 4, 'kat']] as $k => [$label, $val, $min, $max, $unit]): ?>
          <div class="stepper">
            <span class="stepper__label" id="lbl-<?= $k ?>"><?= $label ?> <small>(<?= $unit ?>)</small></span>
            <div class="stepper__box">
              <button type="button" data-step="<?= $k ?>" data-delta="-0.5" aria-label="<?= $label ?> yarım modül azalt">−</button>
              <output data-dim="<?= $k ?>" data-min="<?= $min ?>" data-max="<?= $max ?>" aria-labelledby="lbl-<?= $k ?>" aria-live="polite"><?= $val ?></output>
              <button type="button" data-step="<?= $k ?>" data-delta="0.5" aria-label="<?= $label ?> yarım modül artır">+</button>
            </div>
            <small class="stepper__m" data-dim-m="<?= $k ?>"><?= $m($val) ?> m</small>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if ($full): ?>
        <div class="presets">
          <span>Hızlı seçim:</span>
          <?php foreach ([5, 10, 20, 30, 50, 100] as $p): ?><button type="button" data-preset="<?= $p ?>"><?= $p ?> m³</button><?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <?php if ($full): ?>
      <form class="need" data-need-form data-pane="ihtiyac" hidden novalidate>
        <label class="field"><span>Kullanım yeri</span>
          <select name="use"><?php foreach ($uses as $i => [$label, $lpd, $count, $hint]): ?><option value="<?= $i ?>" data-lpd="<?= $lpd ?>" data-count="<?= e($count) ?>" data-hint="<?= e($hint) ?>"><?= e($label) ?></option><?php endforeach; ?></select>
        </label>
        <div class="need__row">
          <label class="field"><span data-count-label><?= e($uses[0][2] ?? 'Kişi sayısı') ?></span><input type="number" name="people" value="80" min="1" max="100000" inputmode="numeric"></label>
          <label class="field"><span>Günlük tüketim <small>L/kişi</small></span><input type="number" name="lpd" value="<?= (int)($uses[0][1] ?? 150) ?>" min="1" max="2000" inputmode="numeric"></label>
        </div>
        <p class="need__hint" data-use-hint><?= e($uses[0][3] ?? '') ?></p>
        <div class="field"><span>Yedek süre</span>
          <div class="chips" role="group" aria-label="Yedek süre">
            <?php foreach (['0.5' => '½ gün', '1' => '1 gün', '1.5' => '1,5 gün', '2' => '2 gün', '3' => '3 gün'] as $d => $label): ?>
              <button type="button" data-days="<?= $d ?>" aria-pressed="<?= $d === '1' ? 'true' : 'false' ?>"><?= $label ?></button>
            <?php endforeach; ?>
          </div>
        </div>
        <label class="field"><span>Yangın rezervi <small>m³ · yoksa 0</small></span><input type="number" name="fire" value="0" min="0" max="5000" inputmode="numeric"></label>
        <details class="need__limits">
          <summary>Yerleşim alanı sınırı <small>(isteğe bağlı)</small></summary>
          <div class="need__row need__row--3">
            <label class="field"><span>Maks. en <small>m</small></span><input type="number" name="maxa" min="1" step="0.1" placeholder="Sınırsız" inputmode="decimal"></label>
            <label class="field"><span>Maks. boy <small>m</small></span><input type="number" name="maxb" min="1" step="0.1" placeholder="Sınırsız" inputmode="decimal"></label>
            <label class="field"><span>Maks. yükseklik</span><select name="maxh"><?php foreach ([1, 1.5, 2, 2.5, 3, 3.5, 4] as $mh): ?><option value="<?= $mh ?>"<?= $mh == 3 ? ' selected' : '' ?>><?= str_replace('.', ',', (string)$mh) ?> kat (<?= $m($mh) ?> m)</option><?php endforeach; ?></select></label>
          </div>
        </details>
        <div class="need__sum"><span>Gerekli hacim</span><strong data-need-total>12 m³</strong><small data-need-formula></small></div>
        <div class="need__options" data-options></div>
      </form>
    <?php endif; ?>

    <?php if (!$full): ?>
      <div class="sizer__materials" role="radiogroup" aria-label="Panel malzemesi">
        <?php foreach ($materials as $k => $label): ?>
          <label><input type="radio" name="material" value="<?= $k ?>"<?= $k === 'galvaniz' ? ' checked' : '' ?>><span><?= e($label) ?></span></label>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <div class="sizer__result">
      <div><span class="sizer__volume"><span data-volume><?= number_format($vol, 1, ',', '.') ?></span> m³</span><span class="sizer__meta"><strong data-litres><?= number_format(round($vol * 100) * 10, 0, ',', '.') ?></strong> litre</span></div>
      <div class="sizer__flats"><strong data-flats><?= (int)floor($vol * 1000 / 600) ?></strong> dairenin<br>günlük ihtiyacı</div>
    </div>
    <p class="sizer__need" data-need-note hidden></p>

    <?php if ($full): ?>
      <div class="sizer__materials" role="radiogroup" aria-label="Panel malzemesi">
        <strong class="sizer__label">Panel malzemesi</strong>
        <?php foreach ($materials as $k => $label): ?>
          <label><input type="radio" name="material" value="<?= $k ?>"<?= $k === 'galvaniz' ? ' checked' : '' ?>><span><?= e($label) ?></span></label>
        <?php endforeach; ?>
      </div>
      <dl class="tank-specs">
        <div><dt>Dış ölçü <small>en × boy × yükseklik</small></dt><dd data-outer><?= $m($w) ?> × <?= $m($l) ?> × <?= $m($h) ?> m</dd></div>
        <div><dt>Taban alanı</dt><dd data-footprint><?= number_format($w * $l * TANK_MODULE ** 2, 1, ',', '.') ?> m²</dd></div>
        <div><dt>Tam panel <small>108 × 108 cm</small></dt><dd data-p-full><?= $pc['full'] ?></dd></div>
        <div data-p-row="half"<?= $pc['half'] ? '' : ' hidden' ?>><dt>Yarım panel <small>108 × 54 cm</small></dt><dd data-p-half><?= $pc['half'] ?></dd></div>
        <div data-p-row="quarter"<?= $pc['quarter'] ? '' : ' hidden' ?>><dt>Çeyrek panel <small>54 × 54 cm</small></dt><dd data-p-quarter><?= $pc['quarter'] ?></dd></div>
        <div><dt>Toplam panel <small>duvar, tavan, taban</small></dt><dd data-p-total><?= array_sum($pc) ?></dd></div>
      </dl>
      <p class="sizer__big" data-big hidden><?= icon('help') ?> 1.000 m³ üzeri hacimler proje bazlı tasarlanır; birden fazla depo ile çözüm önerebiliriz.</p>
    <?php endif; ?>

    <a href="/teklif-al" class="btn btn--signal btn--block" data-quote>Bu depo için teklif iste <?= icon('arrow-right') ?></a>
    <?php if (!$full): ?>
      <a href="/depo-tasarla" class="sizer__more" data-more>İhtiyaca göre hesapla, panel listesini gör <?= icon('arrow-right') ?></a>
    <?php endif; ?>
  </div>
</div>
