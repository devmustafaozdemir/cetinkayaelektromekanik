import type { Metadata } from 'next';
import { chrome } from '@/lib/page';
import { getProducts } from '@/lib/data';
import { QUOTE_TYPES, productModel, setting, settingPairs, telHref, uploadUrl, tankVolume, fmtMod, fmtNum } from '@/lib/site';
import { Shell, PageHero, Icon, Art } from '@/components/ui';

type P = { searchParams: Promise<{ urun?: string; olcu?: string; malzeme?: string; detay?: string }> };
const MATS: Record<string, string> = { galvaniz: 'galvaniz', paslanmaz: 'paslanmaz çelik', grp: 'GRP', sandvic: 'izolasyonlu' };

export async function generateMetadata({ searchParams }: P): Promise<Metadata> {
  const sp = await searchParams;
  return { title: 'Teklif Al', description: 'Modüler su deposu, pompa ve hidrofor sistemleri için hızlı teklif alın.', alternates: { canonical: '/teklif-al' }, ...(sp.urun ? { robots: { index: false, follow: true } } : {}) };
}

export default async function Page({ searchParams }: P) {
  const sp = await searchParams;
  const [{ s, cats }, products] = await Promise.all([chrome(), getProducts()]);
  const product = sp.urun ? products.find((p) => p.slug === sp.urun) ?? null : null;
  // Prefill from the tank designer: ?olcu=4x3x2.5&malzeme=galvaniz&detay=…
  let quantity = '', message = '', category = '';
  const m = String(sp.olcu ?? '').match(/^(\d{1,2}(?:\.5)?)x(\d{1,2}(?:\.5)?)x(\d(?:\.5)?)$/);
  if (m) {
    const [w, l, h] = m.slice(1).map(Number);
    quantity = `${fmtMod(w)} × ${fmtMod(l)} × ${fmtMod(h)} modül · ${fmtNum(tankVolume(w, l, h), 1)} m³ ${MATS[sp.malzeme ?? ''] ?? 'galvaniz'} modüler depo`;
    message = String(sp.detay ?? '').trim().slice(0, 1000);
    category = cats.find((c) => c.art === 'tank')?.name ?? '';
  }
  return (
    <Shell s={s} cats={cats} active="quote">
      <PageHero heading="Teklif isteyin" lead="Ne kadar bilgi verirseniz teklif o kadar net olur; ama emin olmadığınız alanları boş bırakabilirsiniz." crumbs={[[null, 'Teklif iste']]} />
      <section className="block block--tight">
        <div className="container form-layout">
          <div>
            <form method="post" className="form" noValidate data-validate="" data-remote="quote">
              <input type="text" name="website" className="hp" tabIndex={-1} autoComplete="off" aria-hidden="true" />
              {product ? (
                <>
                  <input type="hidden" name="urun" value={product.slug} />
                  <div className="picked">
                    <span className="picked__art">{product.image ? <img src={uploadUrl(product.image)} alt="" /> : <Art spec={productModel(product)} />}</span>
                    <div><small>Teklif istediğiniz ürün</small><strong>{product.title}</strong></div>
                    <a href="/teklif-al">Değiştir</a>
                  </div>
                </>
              ) : null}
              <fieldset>
                <legend>Ne istiyorsunuz?</legend>
                <div className="choice-row" role="radiogroup" aria-label="Talep türü">
                  {Object.entries(QUOTE_TYPES).map(([k, l]) => <label className="choice" key={k}><input type="radio" name="type" value={k} defaultChecked={k === 'product'} /><span>{l}</span></label>)}
                </div>
                <input type="hidden" name="category" defaultValue={category} />
                <label className="field"><span>Miktar veya kapasite <small>isteğe bağlı</small></span><input type="text" name="quantity" defaultValue={quantity} placeholder="Örneğin 20 m³ depo, 2 pompa ya da 12 katlı bina" /></label>
                <label className="field"><span>Açıklama</span><textarea name="message" rows={4} defaultValue={message} placeholder="Kullanım amacı, kurulum yeri, daire sayısı gibi bilgiler" /></label>
              </fieldset>
              <fieldset>
                <legend>Size nasıl ulaşalım?</legend>
                <div className="form-row">
                  <label className="field"><span>Ad soyad</span><input type="text" name="name" required autoComplete="name" /></label>
                  <label className="field"><span>Firma <small>isteğe bağlı</small></span><input type="text" name="company" autoComplete="organization" /></label>
                </div>
                <div className="form-row">
                  <label className="field"><span>Telefon</span><input type="tel" name="phone" required autoComplete="tel" placeholder="05xx xxx xx xx" /></label>
                  <label className="field"><span>E-posta <small>isteğe bağlı</small></span><input type="email" name="email" autoComplete="email" /></label>
                </div>
                <label className="field"><span>Şehir veya ilçe <small>isteğe bağlı</small></span><input type="text" name="city" placeholder="Örneğin İzmit" /></label>
              </fieldset>
              <label className="check"><input type="checkbox" name="kvkk" value="1" required /><span>Bilgilerimin bu talebi yanıtlamak için kullanılmasını kabul ediyorum.</span></label>
              <div><button className="btn btn--signal btn--lg">Teklif talebini gönder</button></div>
            </form>
          </div>
          <aside className="aside-box">
            <h2>{setting(s, 'quote_next_title')}</h2>
            <ol className="next-steps">{settingPairs(s, 'quote_next').map(([h, t], i) => <li key={i}><strong>{h}</strong><span>{t}</span></li>)}</ol>
            <hr className="aside-box__rule" />
            <p>Acil ihtiyaçlarda doğrudan arayın.</p>
            <a href={telHref(setting(s, 'phone'))} className="btn btn--line-light btn--block"><Icon name="phone" /> {setting(s, 'phone')}</a>
          </aside>
        </div>
      </section>
    </Shell>
  );
}
