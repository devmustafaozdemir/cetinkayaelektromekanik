import type { Metadata } from 'next';
import { chrome } from '@/lib/page';
import { getFaqs } from '@/lib/data';
import { Shell, PageHero, FaqList, JsonLd } from '@/components/ui';

export const metadata: Metadata = { title: 'Sıkça Sorulan Sorular', description: 'Ürünler, teklif süreci, montaj ve servis hakkında sıkça sorulan sorular.', alternates: { canonical: '/sss' } };

export default async function Page() {
  const [{ s, cats }, faqs] = await Promise.all([chrome(), getFaqs()]);
  return (
    <Shell s={s} cats={cats} active="about">
      <JsonLd data={{ '@context': 'https://schema.org', '@type': 'FAQPage', mainEntity: faqs.map((f) => ({ '@type': 'Question', name: f.question, acceptedAnswer: { '@type': 'Answer', text: f.answer } })) }} />
      <PageHero heading="Sık sorulan sorular" lead="Ürünler, teklif süreci, montaj ve servis hakkında." crumbs={[[null, 'Sık sorulan sorular']]} />
      <section className="block"><div className="container container--narrow">
        <FaqList faqs={faqs} />
        <div className="callout"><p><strong>Sorunuz burada yok mu?</strong>Bize yazın, en kısa sürede yanıtlayalım.</p><a href="/iletisim" className="btn btn--navy">İletişime geçin</a></div>
      </div></section>
    </Shell>
  );
}
