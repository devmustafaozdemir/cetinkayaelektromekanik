<?php
partial('page-hero', ['heading' => 'Depo Tasarla', 'lead' => 'Modüler su deponuzu ölçüye ya da ihtiyacınıza göre tasarlayın. Hacim, dış ölçü ve panel listesi anında hesaplanır; beğendiğiniz depo için tek tıkla teklif isteyin.', 'crumbs' => [[null, 'Depo Tasarla']]]);
$m = fn(float $mod) => number_format($mod * TANK_MODULE, 2, ',', '.');
$uses = water_uses();
$sizes = [];
foreach ([5, 10, 20, 30, 50, 75, 100, 150, 200] as $v) {
    $o = tank_options((float)$v)[0] ?? null;
    if ($o) $sizes[] = [$v, $o, tank_panels($o['W'], $o['L'], $o['H'])];
}
?>
<section class="block block--tight">
  <div class="container">
    <?php partial('designer', ['mode' => 'full']); ?>
  </div>
</section>

<section class="block block--soft">
  <div class="container">
    <div class="block__head">
      <span class="kicker">Modül sistemi</span>
      <h2>Modüler depo nasıl ölçülür?</h2>
      <p>Depolar standart çelik panellerin cıvatayla birleştirilmesiyle kurulur. Bu yüzden ölçüler metre yerine <strong>modül</strong> ile ifade edilir.</p>
    </div>
    <div class="facts">
      <div class="fact"><span class="fact__icon"><?= icon('ruler') ?></span><strong>1 modül = 1,08 m</strong><p>Tam panel 108 × 108 cm'dir. 4 × 3 × 2 modül bir depo 4,32 × 3,24 × 2,16 m ölçüsündedir.</p></div>
      <div class="fact"><span class="fact__icon"><?= icon('layers') ?></span><strong>Yarım modül adımları</strong><p>108 × 54 cm yarım panellerle en, boy ve yükseklik 0,54 m adımlarla ayarlanır; depo alanınıza tam oturur.</p></div>
      <div class="fact"><span class="fact__icon"><?= icon('gauge') ?></span><strong>0,5 – 4 kat yükseklik</strong><p>0,54 m'den 4,32 m'ye kadar. Su basıncı alt sıralarda arttığı için alt panel sacları statik hesapla daha kalın seçilir.</p></div>
      <div class="fact"><span class="fact__icon"><?= icon('droplet') ?></span><strong>1 – 1.000 m³</strong><p>Aynı panel sistemiyle küçük bir yapıdan sanayi tesisine kadar her hacim kurulabilir; büyük hacimlerde çoklu depo önerilir.</p></div>
    </div>
  </div>
</section>

<section class="block">
  <div class="container calc-guide">
    <div>
      <span class="kicker">Kapasite hesabı</span>
      <h2>Ne kadar büyük depo gerekir?</h2>
      <p class="formula">Gerekli hacim = <strong>kişi sayısı</strong> × <strong>günlük tüketim</strong> × <strong>yedek gün</strong> + <strong>yangın rezervi</strong></p>
      <p>Örnek: 20 daireli bir binada yaklaşık 80 kişi yaşar. 80 × 150 L × 1 gün = <strong>12 m³</strong>. Buna 2,5 × 2 × 2 modül (2,70 × 2,16 × 2,16 m, 12,6 m³) bir depo yeter. Kesinti sık yaşanıyorsa yedek süreyi 1,5-2 güne çıkarın; yangın tesisatı varsa projedeki yangın suyu hacmini ekleyin.</p>
      <p class="muted small">Değerler ön boyutlandırma içindir. Kesin kapasite, tesisat projesine ve yerel yönetmeliklere göre belirlenir; ücretsiz hesap için bize ulaşın.</p>
    </div>
    <div class="table-card">
      <table class="table-plain">
        <caption>Ortalama günlük su tüketimi</caption>
        <thead><tr><th>Kullanım</th><th>Litre / gün</th></tr></thead>
        <tbody>
          <?php foreach ($uses as [$label, $lpd, $per]): ?><tr><td><?= e($label) ?></td><td><?= $lpd ?> L <small class="muted">/ <?= e(mb_strtolower(preg_replace('/\s*sayısı$/u', '', $per))) ?></small></td></tr><?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section class="block block--soft">
  <div class="container">
    <div class="block__head">
      <span class="kicker">Sık istenen hacimler</span>
      <h2>Hazır depo ölçüleri</h2>
      <p>İstenen hacmi en ekonomik panel düzeniyle sağlayan ölçüler. “Çizimde gör” ile tasarlayıcıda açıp değiştirebilirsiniz.</p>
    </div>
    <div class="table-card">
      <table class="table-plain table-plain--sizes">
        <thead><tr><th>Hacim</th><th>Modül (en × boy × yükseklik)</th><th class="hide-sm">Dış ölçü</th><th class="hide-sm">Panel</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($sizes as [$v, $o, $p]): $q = tank_mod($o['W']) . 'x' . tank_mod($o['L']) . 'x' . tank_mod($o['H']); $q = str_replace(',', '.', $q); ?>
            <tr>
              <td><strong><?= $v ?> m³</strong> <small class="muted">(<?= number_format($o['v'], 1, ',', '.') ?>)</small></td>
              <td><?= tank_mod($o['W']) ?> × <?= tank_mod($o['L']) ?> × <?= tank_mod($o['H']) ?></td>
              <td class="hide-sm"><?= $m($o['W']) ?> × <?= $m($o['L']) ?> × <?= $m($o['H']) ?> m</td>
              <td class="hide-sm"><?= array_sum($p) ?> adet</td>
              <td><a href="/depo-tasarla?olcu=<?= $q ?>&amp;malzeme=galvaniz" class="link-arrow">Çizimde gör <?= icon('arrow-right') ?></a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>
