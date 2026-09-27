import type { Metadata } from 'next';
import { chrome } from '@/lib/page';
import { getProducts, getSettings } from '@/lib/data';
import { brands } from '@/lib/site';
import { Shell, PageHero, BrandLogo } from '@/components/ui';

export async function generateMetadata(): Promise<Metadata> {
  return { title: 'Markalar', description: `Satışını yaptığımız markalar: ${brands(await getSettings()).map((b) => b.name).join(', ')}.`, alternates: { canonical: '/markalar' } };
}

export default async function Page() {
  const [{ s, cats }, products] = await Promise.all([chrome(), getProducts()]);
  return (
    <Shell s={s} cats={cats} active="brands">
      <PageHero heading="Markalar" lead="Sattığımız ve kurulumunu yaptığımız markalar. Bir markaya tıklayarak o markanın ürünlerini görebilirsiniz." crumbs={[[null, 'Markalar']]} />
      <section className="block">
        <div className="container">
          <ul className="brand-list">
            {brands(s).map((b) => (
              <li key={b.slug}><a href={`/urunler?marka=${encodeURIComponent(b.name)}`}>
                <span className="brand-list__logo"><BrandLogo s={s} name={b.name} /></span>
                <span className="brand-list__text"><strong>{b.name}</strong><span>{b.desc}</span></span>
                <em>{products.filter((p) => p.brand === b.name).length} ürün</em>
              </a></li>
            ))}
          </ul>
        </div>
      </section>
    </Shell>
  );
}
