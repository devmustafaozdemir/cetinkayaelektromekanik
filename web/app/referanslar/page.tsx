import type { Metadata } from 'next';
import { chrome } from '@/lib/page';
import { getRefs, getSettings } from '@/lib/data';
import { setting } from '@/lib/site';
import { Shell, PageHero, RefCard } from '@/components/ui';

export async function generateMetadata(): Promise<Metadata> {
  return { title: 'Referanslar', description: setting(await getSettings(), 'refs_lead'), alternates: { canonical: '/referanslar' } };
}

export default async function Page() {
  const [{ s, cats }, refs] = await Promise.all([chrome(), getRefs()]);
  return (
    <Shell s={s} cats={cats} active="about">
      <PageHero heading="Referanslar" lead={setting(s, 'refs_lead')} crumbs={[['/hakkimizda', 'Kurumsal'], [null, 'Referanslar']]} />
      <section className="block block--tight"><div className="container">
        {refs.length ? <ul className="ref-grid">{refs.map((r) => <RefCard r={r} key={r.id} />)}</ul>
          : <p className="empty-note">Referans listemiz hazırlanıyor. Tamamladığımız projeler hakkında bilgi almak için bizi arayın.</p>}
      </div></section>
    </Shell>
  );
}
