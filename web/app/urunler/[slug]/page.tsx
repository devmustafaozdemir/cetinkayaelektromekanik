import { Fragment } from 'react';
import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { chrome } from '@/lib/page';
import { getProducts } from '@/lib/data';
import { cleanHtml, parseSpecs, productModel, setting, telHref, uploadUrl, waHref, siteUrl } from '@/lib/site';
import { Shell, Icon, Art, BrandLogo, ProductCard, JsonLd } from '@/components/ui';

type P = { params: Promise<{ slug: string }> };
const find = async (slug: string) => (await getProducts()).find((p) => p.slug === slug);

export async function generateMetadata({ params }: P): Promise<Metadata> {
  const p = await find((await params).slug);
  if (!p) return {};
  const img = p.image ? uploadUrl(p.image) : '';
  return { title: p.title, description: p.summary, alternates: { canonical: `/urunler/${p.slug}` }, openGraph: img ? { images: [img] } : undefined };
}

export default async function Page({ params }: P) {
  const { slug } = await params;
  const [{ s, cats }, all] = await Promise.all([chrome(), getProducts()]);
  const product = all.find((p) => p.slug === slug);
  if (!product) notFound();
  const specs = parseSpecs(product.specs);
  const model = productModel(product);
  const related = all.filter((p) => p.id !== product.id)
    .map((p, i) => ({ p, k: [p.category_id === product.category_id ? 0 : 1, p.brand === product.brand ? 0 : 1, p.sort, i] }))
    .sort((a, b) => a.k[0] - b.k[0] || a.k[1] - b.k[1] || a.k[2] - b.k[2] || a.k[3] - b.k[3]).slice(0, 4).map((x) => x.p);
  const crumbs: [string | null, string][] = [['/urunler', 'Ürünler']];
  if (product.category) crumbs.push([`/urunler/kategori/${product.category_slug}`, product.category]);
  const wa = waHref(setting(s, 'whatsapp', setting(s, 'phone2')), `Merhaba, "${product.title}" için fiyat bilgisi almak istiyorum.`);
  const image = product.image ? uploadUrl(product.image) : '';
  return (
    <Shell s={s} cats={cats} active="products">
      <JsonLd data={{
        '@context': 'https://schema.org', '@type': 'Product', name: product.title, description: product.summary, category: product.category,
        ...(product.brand ? { brand: { '@type': 'Brand', name: product.brand } } : {}), ...(image ? { image: image.startsWith('http') ? image : siteUrl() + image } : {}),
      }} />
      <section className="pd">
        <div className="container">
          <nav className="breadcrumb" aria-label="Sayfa yolu">
            <a href="/">Ana sayfa</a>
            {crumbs.map(([h, l]) => <Fragment key={l}><span aria-hidden="true">/</span><a href={h!}>{l}</a></Fragment>)}
            <span aria-hidden="true">/</span><span aria-current="page">{product.title}</span>
          </nav>
          <div className="pd__grid">
            <div className="pd__visual" data-model-view="" data-model={model} data-viewer="/assets/js/viewer3d.js">
              <div className="pd__media">
                <div className="pd__2d" data-svg="">{image ? <img src={image} alt={product.title} /> : <Art spec={model} />}</div>
                <div className="viewer" data-viewer-host="" hidden />
              </div>
              <div className="pd__visual-bar">
                <div className="viewswitch viewswitch--light" role="group" aria-label="Görünüm" hidden={model === ''}>
                  <button type="button" data-view="2d" aria-pressed="true">{image ? 'Fotoğraf' : 'Çizim'}</button>
                  <button type="button" data-view="3d" aria-pressed="false">3D incele</button>
                </div>
                <span className="muted small" data-hint="" hidden>Sürükleyerek çevirin, yakınlaştırmak için kaydırın.</span>
              </div>
            </div>
            <div className="pd__info">
              {product.brand ? <a href={`/urunler?marka=${encodeURIComponent(product.brand)}`} className="pd__brand" title={`${product.brand} ürünleri`}><BrandLogo s={s} name={product.brand} /></a> : null}
              <h1>{product.title}</h1>
              <p className="lead">{product.summary}</p>
              {specs.length ? <dl className="pd__specs">{specs.slice(0, 5).map(([k, v], i) => <div key={i}><dt>{k}</dt><dd>{v}</dd></div>)}</dl> : null}
              <div className="pd__actions">
                <a href={`/teklif-al?urun=${product.slug}`} className="btn btn--signal btn--lg">Bu ürün için teklif iste</a>
                <a href={wa} target="_blank" rel="noopener" className="btn btn--line btn--lg"><Icon name="whatsapp" /> WhatsApp</a>
              </div>
              <p className="pd__note">Kapasite, model ve montaj seçeneklerini teklif aşamasında projenize göre netleştiriyoruz.</p>
            </div>
          </div>
        </div>
      </section>
      <section className="block block--tight">
        <div className="container pd__body">
          <div>
            <article className="prose">
              <h2>Ürün hakkında</h2>
              {product.content ? <div style={{ display: 'contents' }} dangerouslySetInnerHTML={{ __html: cleanHtml(product.content) }} /> : <p>{product.summary}</p>}
            </article>
            {specs.length ? (
              <>
                <h2 className="spec-title" id="ozellikler">Teknik özellikler</h2>
                <table className="spec-table"><tbody>{specs.map(([k, v], i) => <tr key={i}><th scope="row">{k}</th><td>{v}</td></tr>)}</tbody></table>
                <p className="muted small">Değerler model ve kapasiteye göre değişir; projenize uygun değerleri teklifte belirtiriz.</p>
              </>
            ) : null}
          </div>
          <aside className="aside-box">
            <h2>Fiyat almak için</h2>
            <p>Miktarı ve kullanım yerini yazmanız yeterli. Emin olmadığınız bilgileri birlikte netleştiririz.</p>
            <a href={`/teklif-al?urun=${product.slug}`} className="btn btn--signal btn--block">Teklif iste</a>
            <a href={telHref(setting(s, 'phone'))} className="btn btn--line-light btn--block"><Icon name="phone" /> {setting(s, 'phone')}</a>
          </aside>
        </div>
      </section>
      {related.length ? (
        <section className="block block--steel">
          <div className="container">
            <div className="block__head"><h2>Benzer ürünler</h2></div>
            <div className="product-grid">{related.map((p) => <ProductCard p={p} s={s} key={p.id} />)}</div>
          </div>
        </section>
      ) : null}
    </Shell>
  );
}
