import type { Metadata } from 'next';
import { chrome } from '@/lib/page';
import { fmtM, fmtMod, fmtNum, tankOptions, tankPanels, waterUses } from '@/lib/site';
import { Shell, PageHero, Icon } from '@/components/ui';
import { Designer } from '@/components/designer';

export const metadata: Metadata = {
  title: 'Depo Tasarla – Modüler Su Deposu Hesaplama',
  description: 'Modüler su deposunu modül ölçüsüne (1 modül = 1,08 m) veya kişi sayısı, günlük tüketim, yedek gün ve yangın rezervine göre tasarlayın; hacim, dış ölçü ve panel listesini görün.',
  alternates: { canonical: '/depo-tasarla' },
};

export default async function Page() {
  const { s, cats } = await chrome();
  const uses = waterUses(s);
  const sizes = [5, 10, 20, 30, 50, 75, 100, 150, 200].map((v) => {
    const o = tankOptions(v)[0];
    const p = tankPanels(o.W, o.L, o.H);
    return { v, o, n: p.full + p.half + p.quarter };
  });
  return (
    <Shell s={s} cats={cats} active="designer">
      <PageHero heading="Depo Tasarla" lead="Modüler su deponuzu ölçüye ya da ihtiyacınıza göre tasarlayın. Hacim, dış ölçü ve panel listesi anında hesaplanır; beğendiğiniz depo için tek tıkla teklif isteyin." crumbs={[[null, 'Depo Tasarla']]} />
      <section className="block block--tight"><div className="container"><Designer s={s} full /></div></section>

      <section className="block block--soft">
        <div className="container">
          <div className="block__head">
            <span className="kicker">Modül sistemi</span>
            <h2>Modüler depo nasıl ölçülür?</h2>
            <p>Depolar standart çelik panellerin cıvatayla birleştirilmesiyle kurulur. Bu yüzden ölçüler metre yerine <strong>modül</strong> ile ifade edilir.</p>
          </div>
          <div className="facts">
            <div className="fact"><span className="fact__icon"><Icon name="ruler" /></span><strong>1 modül = 1,08 m</strong><p>Tam panel 108 × 108 cm&apos;dir. 4 × 3 × 2 modül bir depo 4,32 × 3,24 × 2,16 m ölçüsündedir.</p></div>
            <div className="fact"><span className="fact__icon"><Icon name="layers" /></span><strong>Yarım modül adımları</strong><p>108 × 54 cm yarım panellerle en, boy ve yükseklik 0,54 m adımlarla ayarlanır; depo alanınıza tam oturur.</p></div>
            <div className="fact"><span className="fact__icon"><Icon name="gauge" /></span><strong>0,5 – 4 kat yükseklik</strong><p>0,54 m&apos;den 4,32 m&apos;ye kadar. Su basıncı alt sıralarda arttığı için alt panel sacları statik hesapla daha kalın seçilir.</p></div>
            <div className="fact"><span className="fact__icon"><Icon name="droplet" /></span><strong>1 – 1.000 m³</strong><p>Aynı panel sistemiyle küçük bir yapıdan sanayi tesisine kadar her hacim kurulabilir; büyük hacimlerde çoklu depo önerilir.</p></div>
          </div>
        </div>
      </section>

      <section className="block">
        <div className="container calc-guide">
          <div>
            <span className="kicker">Kapasite hesabı</span>
            <h2>Ne kadar büyük depo gerekir?</h2>
            <p className="formula">Gerekli hacim = <strong>kişi sayısı</strong> × <strong>günlük tüketim</strong> × <strong>yedek gün</strong> + <strong>yangın rezervi</strong></p>
            <p>Örnek: 20 daireli bir binada yaklaşık 80 kişi yaşar. 80 × 150 L × 1 gün = <strong>12 m³</strong>. Buna 2,5 × 2 × 2 modül (2,70 × 2,16 × 2,16 m, 12,6 m³) bir depo yeter. Kesinti sık yaşanıyorsa yedek süreyi 1,5-2 güne çıkarın; yangın tesisatı varsa projedeki yangın suyu hacmini ekleyin.</p>
            <p className="muted small">Değerler ön boyutlandırma içindir. Kesin kapasite, tesisat projesine ve yerel yönetmeliklere göre belirlenir; ücretsiz hesap için bize ulaşın.</p>
          </div>
          <div className="table-card">
            <table className="table-plain">
              <caption>Ortalama günlük su tüketimi</caption>
              <thead><tr><th>Kullanım</th><th>Litre / gün</th></tr></thead>
              <tbody>{uses.map(([label, lpd, per], i) => <tr key={i}><td>{label}</td><td>{lpd} L <small className="muted">/ {per.replace(/\s*sayısı$/u, '').toLocaleLowerCase('tr-TR')}</small></td></tr>)}</tbody>
            </table>
          </div>
        </div>
      </section>

      <section className="block block--soft">
        <div className="container">
          <div className="block__head">
            <span className="kicker">Sık istenen hacimler</span>
            <h2>Hazır depo ölçüleri</h2>
            <p>İstenen hacmi en ekonomik panel düzeniyle sağlayan ölçüler. “Çizimde gör” ile tasarlayıcıda açıp değiştirebilirsiniz.</p>
          </div>
          <div className="table-card">
            <table className="table-plain table-plain--sizes">
              <thead><tr><th>Hacim</th><th>Modül (en × boy × yükseklik)</th><th className="hide-sm">Dış ölçü</th><th className="hide-sm">Panel</th><th /></tr></thead>
              <tbody>
                {sizes.map(({ v, o, n }) => (
                  <tr key={v}>
                    <td><strong>{v} m³</strong> <small className="muted">({fmtNum(o.v, 1)})</small></td>
                    <td>{fmtMod(o.W)} × {fmtMod(o.L)} × {fmtMod(o.H)}</td>
                    <td className="hide-sm">{fmtM(o.W)} × {fmtM(o.L)} × {fmtM(o.H)} m</td>
                    <td className="hide-sm">{n} adet</td>
                    <td><a href={`/depo-tasarla?olcu=${o.W}x${o.L}x${o.H}&malzeme=galvaniz`} className="link-arrow">Çizimde gör <Icon name="arrow-right" /></a></td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </Shell>
  );
}
