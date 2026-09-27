import { chrome } from '@/lib/page';
import { getProducts } from '@/lib/data';
import { type Row } from '@/lib/site';
import { Shell, PageHero, Icon, ProductCard } from './ui';

/** Product listing (all, by category, by brand) with search — mirrors the PHP product_listing(). */
export async function Catalog({ category, brand = '', search = '' }: { category?: Row | null; brand?: string; search?: string }) {
  const { s, cats } = await chrome();
  const all = await getProducts();
  const inCat = category ? all.filter((p) => p.category_id === category.id) : all;
  const q = search.toLocaleLowerCase('tr-TR');
  const products = inCat.filter((p) => (!brand || p.brand === brand)
    && (!q || [p.title, p.summary, p.brand].join(' ').toLocaleLowerCase('tr-TR').includes(q)));
  const brandList = [...new Set(inCat.map((p) => p.brand).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'tr'));
  const baseUrl = category ? `/urunler/kategori/${category.slug}` : '/urunler';
  const total = cats.reduce((n, c) => n + c.cnt, 0);
  const crumbs: [string | null, string][] = category ? [['/urunler', 'Ürünler'], [null, category.name]] : [[null, 'Ürünler']];
  return (
    <Shell s={s} cats={cats} active="products">
      <PageHero heading={category ? category.name : 'Ürünler'} lead={category ? category.summary : 'Modüler su depoları, hidrofor setleri, santrifüj ve dalgıç pompalar. Her ürünü çizim ve 3D olarak inceleyebilir, tek tıkla teklif isteyebilirsiniz.'} crumbs={crumbs} />
      <section className="block block--tight">
        <div className="container catalog">
          <aside className="catalog__side">
            <nav aria-label="Ürün grupları">
              <h2>Ürün grupları</h2>
              <ul className="filter-list">
                <li><a href="/urunler" {...(!category ? { 'aria-current': 'page' as const } : {})}><span>Tüm ürünler</span><em>{total}</em></a></li>
                {cats.map((c) => (
                  <li key={c.id}><a href={`/urunler/kategori/${c.slug}`} {...(category?.id === c.id ? { 'aria-current': 'page' as const } : {})}><span>{c.name}</span><em>{c.cnt}</em></a></li>
                ))}
              </ul>
            </nav>
            {brandList.length ? (
              <nav aria-label="Markalar">
                <h2>Marka</h2>
                <div className="pills">
                  <a href={baseUrl} {...(!brand ? { 'aria-current': 'page' as const } : {})}>Hepsi</a>
                  {brandList.map((b) => <a key={b} href={`${baseUrl}?marka=${encodeURIComponent(b)}`} {...(brand === b ? { 'aria-current': 'page' as const } : {})}>{b}</a>)}
                </div>
              </nav>
            ) : null}
            <div className="catalog__help">
              <h2>Listede yok mu?</h2>
              <p>Projeye özel ürünleri ve farklı modelleri de tedarik ediyoruz.</p>
              <a href="/teklif-al" className="text-link">Talebinizi yazın</a>
            </div>
          </aside>
          <div>
            <div className="catalog__bar">
              <p>{products.length} ürün{brand ? `, ${brand}` : ''}{search ? `, “${search}” araması` : ''}</p>
              <form className="search" method="get" action={baseUrl} role="search">
                <Icon name="search" />
                {brand ? <input type="hidden" name="marka" value={brand} /> : null}
                <input type="search" name="q" defaultValue={search} placeholder="Ürün adı veya marka" aria-label="Ürünlerde ara" />
              </form>
            </div>
            {products.length ? (
              <div className="product-grid product-grid--3">{products.map((p) => <ProductCard p={p} s={s} key={p.id} />)}</div>
            ) : (
              <div className="empty"><p>Bu filtreyle eşleşen ürün yok. Aramayı değiştirin ya da <a href="/urunler" className="text-link">tüm ürünlere dönün</a>.</p></div>
            )}
          </div>
        </div>
      </section>
    </Shell>
  );
}
