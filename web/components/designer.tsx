import { DESIGNER_SVGS } from '@/lib/generated';
import { withIds, tankVolume, tankPanels, fmtM, fmtNum, TANK_MODULE, waterUses, type Settings } from '@/lib/site';
import { Icon, Raw } from './ui';

const MATERIALS: [string, string][] = [['galvaniz', 'Galvaniz'], ['paslanmaz', 'Paslanmaz'], ['grp', 'GRP'], ['sandvic', 'İzolasyonlu']];

/** Modular tank designer; the interactive logic lives in assets/js/site.js ([data-designer]). */
export function Designer({ s, full = false }: { s: Settings; full?: boolean }) {
  const [w, l, h] = [4, 3, 2];
  const vol = tankVolume(w, l, h);
  const pc = tankPanels(w, l, h);
  const uses = waterUses(s);
  const materials = (
    <div className="sizer__materials" role="radiogroup" aria-label="Panel malzemesi">
      {full ? <strong className="sizer__label">Panel malzemesi</strong> : null}
      {MATERIALS.map(([k, label]) => (
        <label key={k}><input type="radio" name="material" value={k} defaultChecked={k === 'galvaniz'} /><span>{label}</span></label>
      ))}
    </div>
  );
  return (
    <div className={`sizer${full ? ' sizer--full' : ''}`} data-designer={full ? 'full' : 'compact'} data-viewer="/assets/js/viewer3d.js">
      <div className="sizer__view">
        <div className="sizer__top">
          <div>
            <h2>{full ? 'Depo görünümü' : 'Deponuzu tasarlayın'}</h2>
            <p>{full ? 'Çizimi 3D’ye çevirip döndürebilirsiniz.' : 'Modül seçin, hacmi ve kaç daireye yeteceğini görün.'}</p>
          </div>
          <div className="viewswitch" role="group" aria-label="Görünüm">
            <button type="button" data-view="2d" aria-pressed="true">Çizim</button>
            <button type="button" data-view="3d" aria-pressed="false">3D</button>
          </div>
        </div>
        <div className="sizer__stage" data-stage="">
          <Raw as="div" className="sizer__svg" data-svg="" html={withIds(full ? DESIGNER_SVGS.full : DESIGNER_SVGS.compact)} />
          <div className="viewer" data-viewer-host="" hidden />
          <p className="viewer__hint" data-hint="" hidden>Sürükleyerek çevirin</p>
          <span className="sizer__tag"><span data-modules="" suppressHydrationWarning>4 × 3 × 2 modül</span> · 1 modül = 1,08 m</span>
        </div>
      </div>

      <div className="sizer__side">
        {full ? (
          <div className="sizer__tabs" role="tablist" aria-label="Hesaplama yöntemi">
            <button type="button" role="tab" data-tab="olcu" aria-selected="true"><Icon name="ruler" /> Ölçüye göre</button>
            <button type="button" role="tab" data-tab="ihtiyac" aria-selected="false" tabIndex={-1}><Icon name="users" /> İhtiyaca göre</button>
          </div>
        ) : null}

        <div data-pane="olcu">
          <div className="sizer__controls">
            {([['w', 'En', w, 1, 20, 'modül'], ['l', 'Boy', l, 1, 20, 'modül'], ['h', 'Yükseklik', h, 0.5, 4, 'kat']] as const).map(([k, label, val, min, max, unit]) => (
              <div className="stepper" key={k}>
                <span className="stepper__label" id={`lbl-${k}`}>{label} <small>({unit})</small></span>
                <div className="stepper__box">
                  <button type="button" data-step={k} data-delta="-0.5" aria-label={`${label} yarım modül azalt`}>−</button>
                  <output suppressHydrationWarning data-dim={k} data-min={min} data-max={max} aria-labelledby={`lbl-${k}`} aria-live="polite">{val}</output>
                  <button type="button" data-step={k} data-delta="0.5" aria-label={`${label} yarım modül artır`}>+</button>
                </div>
                <small className="stepper__m" data-dim-m={k} suppressHydrationWarning>{fmtM(val)} m</small>
              </div>
            ))}
          </div>
          {full ? (
            <div className="presets">
              <span>Hızlı seçim:</span>
              {[5, 10, 20, 30, 50, 100].map((p) => <button type="button" data-preset={p} key={p}>{p} m³</button>)}
            </div>
          ) : null}
        </div>

        {full ? (
          <form className="need" data-need-form="" data-pane="ihtiyac" hidden noValidate>
            <label className="field"><span>Kullanım yeri</span>
              <select name="use">{uses.map(([label, lpd, count, hint], i) => <option key={i} value={i} data-lpd={lpd} data-count={count} data-hint={hint}>{label}</option>)}</select>
            </label>
            <div className="need__row">
              <label className="field"><span data-count-label="" suppressHydrationWarning>{uses[0][2]}</span><input type="number" name="people" defaultValue={80} min={1} max={100000} inputMode="numeric" /></label>
              <label className="field"><span>Günlük tüketim <small>L/kişi</small></span><input type="number" name="lpd" defaultValue={uses[0][1]} min={1} max={2000} inputMode="numeric" /></label>
            </div>
            <p className="need__hint" data-use-hint="" suppressHydrationWarning>{uses[0][3]}</p>
            <div className="field"><span>Yedek süre</span>
              <div className="chips" role="group" aria-label="Yedek süre">
                {([['0.5', '½ gün'], ['1', '1 gün'], ['1.5', '1,5 gün'], ['2', '2 gün'], ['3', '3 gün']] as const).map(([d, label]) => (
                  <button type="button" data-days={d} aria-pressed={d === '1' ? 'true' : 'false'} key={d}>{label}</button>
                ))}
              </div>
            </div>
            <label className="field"><span>Yangın rezervi <small>m³ · yoksa 0</small></span><input type="number" name="fire" defaultValue={0} min={0} max={5000} inputMode="numeric" /></label>
            <details className="need__limits">
              <summary>Yerleşim alanı sınırı <small>(isteğe bağlı)</small></summary>
              <div className="need__row need__row--3">
                <label className="field"><span>Maks. en <small>m</small></span><input type="number" name="maxa" min={1} step={0.1} placeholder="Sınırsız" inputMode="decimal" /></label>
                <label className="field"><span>Maks. boy <small>m</small></span><input type="number" name="maxb" min={1} step={0.1} placeholder="Sınırsız" inputMode="decimal" /></label>
                <label className="field"><span>Maks. yükseklik</span>
                  <select name="maxh" defaultValue="3">{[1, 1.5, 2, 2.5, 3, 3.5, 4].map((mh) => <option key={mh} value={mh}>{String(mh).replace('.', ',')} kat ({fmtM(mh)} m)</option>)}</select>
                </label>
              </div>
            </details>
            <div className="need__sum"><span>Gerekli hacim</span><strong data-need-total="" suppressHydrationWarning>12 m³</strong><small data-need-formula="" suppressHydrationWarning /></div>
            <div className="need__options" data-options="" />
          </form>
        ) : null}

        {!full ? materials : null}

        <div className="sizer__result">
          <div>
            <span className="sizer__volume"><span data-volume="" suppressHydrationWarning>{fmtNum(vol, 1)}</span> m³</span>
            <span className="sizer__meta"><strong data-litres="" suppressHydrationWarning>{fmtNum(Math.round(vol * 100) * 10, 0)}</strong> litre</span>
          </div>
          <div className="sizer__flats"><strong data-flats="" suppressHydrationWarning>{Math.floor((vol * 1000) / 600)}</strong> dairenin<br />günlük ihtiyacı</div>
        </div>
        <p className="sizer__need" data-need-note="" suppressHydrationWarning hidden />

        {full ? (
          <>
            {materials}
            <dl className="tank-specs">
              <div><dt>Dış ölçü <small>en × boy × yükseklik</small></dt><dd data-outer="" suppressHydrationWarning>{fmtM(w)} × {fmtM(l)} × {fmtM(h)} m</dd></div>
              <div><dt>Taban alanı</dt><dd data-footprint="" suppressHydrationWarning>{fmtNum(w * l * TANK_MODULE ** 2, 1)} m²</dd></div>
              <div><dt>Tam panel <small>108 × 108 cm</small></dt><dd data-p-full="" suppressHydrationWarning>{pc.full}</dd></div>
              <div data-p-row="half" hidden={!pc.half}><dt>Yarım panel <small>108 × 54 cm</small></dt><dd data-p-half="" suppressHydrationWarning>{pc.half}</dd></div>
              <div data-p-row="quarter" hidden={!pc.quarter}><dt>Çeyrek panel <small>54 × 54 cm</small></dt><dd data-p-quarter="" suppressHydrationWarning>{pc.quarter}</dd></div>
              <div><dt>Toplam panel <small>duvar, tavan, taban</small></dt><dd data-p-total="" suppressHydrationWarning>{pc.full + pc.half + pc.quarter}</dd></div>
            </dl>
            <p className="sizer__big" data-big="" hidden><Icon name="help" /> 1.000 m³ üzeri hacimler proje bazlı tasarlanır; birden fazla depo ile çözüm önerebiliriz.</p>
          </>
        ) : null}

        <a href="/teklif-al" className="btn btn--signal btn--block" data-quote="">Bu depo için teklif iste <Icon name="arrow-right" /></a>
        {!full ? <a href="/depo-tasarla" className="sizer__more" data-more="">İhtiyaca göre hesapla, panel listesini gör <Icon name="arrow-right" /></a> : null}
      </div>
    </div>
  );
}
