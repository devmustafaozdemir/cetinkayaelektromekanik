import type { Metadata } from 'next';
import { chrome } from '@/lib/page';
import { getSettings } from '@/lib/data';
import { excerpt, setting, settingLines } from '@/lib/site';
import { Shell, PageHero, BrandLine, Lines } from '@/components/ui';

export async function generateMetadata(): Promise<Metadata> {
  return { title: 'Hakkımızda', description: excerpt(setting(await getSettings(), 'about_text'), 160), alternates: { canonical: '/hakkimizda' } };
}

export default async function Page() {
  const { s, cats } = await chrome();
  const paras = setting(s, 'about_text').split(/(?:\r?\n){2,}/).map((p) => p.trim()).filter(Boolean);
  return (
    <Shell s={s} cats={cats} active="about">
      <PageHero heading="Hakkımızda" lead={setting(s, 'site_tagline')} crumbs={[[null, 'Hakkımızda']]} />
      <section className="block">
        <div className="container duo">
          <div><h2>{setting(s, 'about_title')}</h2>{paras.map((p, i) => <p key={i}><Lines text={p} /></p>)}</div>
          <div><h3>Çalışma ilkelerimiz</h3><ul className="ticks">{settingLines(s, 'about_values').map((v, i) => <li key={i}>{v}</li>)}</ul></div>
        </div>
      </section>
      <section className="block block--steel">
        <div className="container"><div className="block__head block__head--row"><h2>Sattığımız markalar</h2><a href="/markalar" className="text-link">Markalar sayfası</a></div></div>
        <BrandLine s={s} />
      </section>
    </Shell>
  );
}
