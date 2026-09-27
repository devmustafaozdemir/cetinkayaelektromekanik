import { chrome } from '@/lib/page';
import { Shell } from '@/components/ui';

export default async function NotFound() {
  const { s, cats } = await chrome();
  return (
    <Shell s={s} cats={cats}>
      <section className="error-page">
        <div className="container">
          <h1>Bu adreste bir sayfa yok.</h1>
          <p className="lead">Sayfa taşınmış ya da adres yanlış yazılmış olabilir. Aradığınız bir ürünse ürünler sayfasından arayabilirsiniz.</p>
          <div className="hero__actions"><a href="/urunler" className="btn btn--navy">Ürünlere git</a><a href="/" className="btn btn--line">Ana sayfa</a></div>
        </div>
      </section>
    </Shell>
  );
}
