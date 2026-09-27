import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { chrome } from '@/lib/page';
import { getServices } from '@/lib/data';
import { cleanHtml, setting, telHref } from '@/lib/site';
import { Shell, PageHero, Icon } from '@/components/ui';

type P = { params: Promise<{ slug: string }> };

export async function generateMetadata({ params }: P): Promise<Metadata> {
  const { slug } = await params;
  const x = (await getServices()).find((v) => v.slug === slug);
  return x ? { title: x.title, description: x.summary, alternates: { canonical: `/hizmetler/${x.slug}` } } : {};
}

export default async function Page({ params }: P) {
  const { slug } = await params;
  const [{ s, cats }, services] = await Promise.all([chrome(), getServices()]);
  const x = services.find((v) => v.slug === slug);
  if (!x) notFound();
  return (
    <Shell s={s} cats={cats} active="services">
      <PageHero heading={x.title} lead={x.summary} crumbs={[['/hizmetler', 'Hizmetler'], [null, x.title]]} />
      <section className="block block--tight">
        <div className="container pd__body">
          <article className="prose">
            <div style={{ display: 'contents' }} dangerouslySetInnerHTML={{ __html: cleanHtml(x.content) }} />
            <div className="callout">
              <p><strong>Bu hizmet için görüşelim</strong>Kısa bir form doldurun, ekibimiz sizi arasın.</p>
              <a href="/teklif-al" className="btn btn--signal">Talep oluştur</a>
            </div>
          </article>
          <aside className="aside-box">
            <h2>Diğer hizmetler</h2>
            <ul className="aside-links">{services.filter((o) => o.id !== x.id).map((o) => <li key={o.id}><a href={`/hizmetler/${o.slug}`}>{o.title}</a></li>)}</ul>
            <a href={telHref(setting(s, 'phone'))} className="btn btn--line-light btn--block"><Icon name="phone" /> {setting(s, 'phone')}</a>
          </aside>
        </div>
      </section>
    </Shell>
  );
}
