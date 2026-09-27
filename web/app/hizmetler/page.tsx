import type { Metadata } from 'next';
import { chrome } from '@/lib/page';
import { getServices } from '@/lib/data';
import { Shell, PageHero, Icon } from '@/components/ui';

export const metadata: Metadata = { title: 'Hizmetlerimiz', description: 'Sattığımız ürünler için keşif, montaj, bakım ve sevkiyat desteği.', alternates: { canonical: '/hizmetler' } };

export default async function Page() {
  const [{ s, cats }, services] = await Promise.all([chrome(), getServices()]);
  return (
    <Shell s={s} cats={cats} active="services">
      <PageHero heading="Hizmetler" lead="Sattığımız ürünler için keşif, montaj, bakım ve sevkiyat desteği." crumbs={[[null, 'Hizmetler']]} />
      <section className="block"><div className="container"><div className="svc-grid">
        {services.map((x) => <a href={`/hizmetler/${x.slug}`} className="svc-card" key={x.id}><span className="svc-card__icon"><Icon name={x.icon} /></span><h3>{x.title}</h3><p>{x.summary}</p></a>)}
      </div></div></section>
    </Shell>
  );
}
