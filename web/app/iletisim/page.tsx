import type { Metadata } from 'next';
import { chrome } from '@/lib/page';
import { setting, settingLines, telHref, waHref } from '@/lib/site';
import { Shell, PageHero } from '@/components/ui';

export const metadata: Metadata = { title: 'İletişim', description: 'Ürün bilgisi, fiyat ve proje görüşmeleri için bize ulaşın.', alternates: { canonical: '/iletisim' } };

export default async function Page() {
  const { s, cats } = await chrome();
  const wa = setting(s, 'whatsapp', setting(s, 'phone2'));
  return (
    <Shell s={s} cats={cats} active="contact">
      <PageHero heading="İletişim" lead="Ürün bilgisi, fiyat ve proje görüşmeleri için arayın, yazın ya da mağazamıza uğrayın." crumbs={[[null, 'İletişim']]} />
      <section className="block block--tight">
        <div className="container contact">
          <dl className="contact-list">
            <div><dt>Telefon</dt><dd><a href={telHref(setting(s, 'phone'))}>{setting(s, 'phone')}</a>{setting(s, 'phone2') ? <><br /><a href={telHref(setting(s, 'phone2'))}>{setting(s, 'phone2')}</a></> : null}</dd></div>
            <div><dt>WhatsApp</dt><dd><a href={waHref(wa)} target="_blank" rel="noopener">{wa}</a></dd></div>
            <div><dt>E-posta</dt><dd><a href={`mailto:${setting(s, 'email')}`}>{setting(s, 'email')}</a></dd></div>
            <div><dt>Adres</dt><dd>{setting(s, 'address')}<br /><a href={setting(s, 'map_link')} target="_blank" rel="noopener" className="small">Yol tarifi alın</a></dd></div>
            <div><dt>Çalışma saatleri</dt><dd>{settingLines(s, 'hours').map((h, i) => <span key={i}>{h}<br /></span>)}</dd></div>
          </dl>
          <div>
            <h2>Mesaj gönderin</h2>
            <form method="post" className="form" noValidate data-validate="" data-remote="message">
              <input type="text" name="website" className="hp" tabIndex={-1} autoComplete="off" aria-hidden="true" />
              <div className="form-row">
                <label className="field"><span>Ad soyad</span><input type="text" name="name" required autoComplete="name" /></label>
                <label className="field"><span>Telefon</span><input type="tel" name="phone" autoComplete="tel" /></label>
              </div>
              <div className="form-row">
                <label className="field"><span>E-posta</span><input type="email" name="email" autoComplete="email" /></label>
                <label className="field"><span>Konu <small>isteğe bağlı</small></span><input type="text" name="subject" /></label>
              </div>
              <label className="field"><span>Mesajınız</span><textarea name="message" rows={5} required minLength={10} /></label>
              <div><button className="btn btn--navy btn--lg">Mesajı gönder</button></div>
            </form>
          </div>
        </div>
      </section>
      {setting(s, 'map_embed') ? <iframe className="map" src={setting(s, 'map_embed')} loading="lazy" referrerPolicy="no-referrer-when-downgrade" title="Konum haritası" allowFullScreen /> : null}
    </Shell>
  );
}
