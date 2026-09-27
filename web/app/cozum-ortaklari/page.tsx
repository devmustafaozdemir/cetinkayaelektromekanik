import type { Metadata } from 'next';
import { chrome } from '@/lib/page';
import { getPartners, getSettings } from '@/lib/data';
import { setting } from '@/lib/site';
import { Shell, PageHero, RefCard, BrandLine } from '@/components/ui';

export async function generateMetadata(): Promise<Metadata> {
  return { title: 'Çözüm Ortakları', description: setting(await getSettings(), 'partners_lead'), alternates: { canonical: '/cozum-ortaklari' } };
}

export default async function Page() {
  const [{ s, cats }, partners] = await Promise.all([chrome(), getPartners()]);
  return (
    <Shell s={s} cats={cats} active="about">
      <PageHero heading="Çözüm Ortakları" lead={setting(s, 'partners_lead')} crumbs={[['/hakkimizda', 'Kurumsal'], [null, 'Çözüm Ortakları']]} />
      <section className="block block--tight"><div className="container">
        {partners.length ? <ul className="ref-grid">{partners.map((r) => <RefCard r={r} key={r.id} />)}</ul>
          : <p className="empty-note">Çözüm ortaklarımızın listesi hazırlanıyor.</p>}
      </div></section>
      <section className="block block--steel">
        <div className="container"><div className="block__head block__head--row"><h2>Satışını yaptığımız markalar</h2><a href="/markalar" className="text-link">Markalar sayfası</a></div></div>
        <BrandLine s={s} />
      </section>
    </Shell>
  );
}
