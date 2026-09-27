import { Fragment, type ReactNode } from 'react';
import { SiteScript } from './site-script';
import {
  iconSvg, modelSvg, productModel, uploadUrl, brands, brandLogoUrl, slugify, trDate, excerpt, readingTime, setting, settingLines,
  telHref, waHref, siteLogoSvg, productArt, type Row, type Settings,
} from '@/lib/site';

/** Inserts a trusted SVG/HTML string without adding a layout box. */
export function Raw({ html, as: Tag = 'span', className, ...rest }: { html: string; as?: 'span' | 'div'; className?: string } & Record<`data-${string}`, string>) {
  return <Tag className={className} style={className ? undefined : { display: 'contents' }} {...rest} dangerouslySetInnerHTML={{ __html: html }} />;
}
export const Icon = ({ name, cls = 'icon' }: { name: string; cls?: string }) => <Raw html={iconSvg(name, cls)} />;
export const Art = ({ spec, cls = 'art' }: { spec: string; cls?: string }) => <Raw html={modelSvg(spec, cls)} />;
export const JsonLd = ({ data }: { data: object }) => (
  <script type="application/ld+json" dangerouslySetInnerHTML={{ __html: JSON.stringify(data).replace(/</g, '\\u003c') }} />
);
/** Newlines → <br> (like PHP nl2br). */
export const Lines = ({ text }: { text: string }) => (
  <>{String(text || '').split(/\r?\n/).map((l, i) => <Fragment key={i}>{i > 0 && <br />}{l}</Fragment>)}</>
);

export function BrandLogo({ s, name, cls = 'brand-logo' }: { s: Settings; name: string; cls?: string }) {
  const url = brandLogoUrl(s, slugify(name));
  return url ? <img className={cls} src={url} alt={name} loading="lazy" /> : <span className={`${cls} brand-logo--text`}>{name}</span>;
}

export function PageHero({ heading, lead, crumbs = [] }: { heading: string; lead?: string; crumbs?: [string | null, string][] }) {
  return (
    <section className="page-head">
      <div className="container">
        <nav className="breadcrumb" aria-label="Sayfa yolu">
          <a href="/">Ana sayfa</a>
          {crumbs.map(([href, label], i) => (
            <Fragment key={i}>
              <span aria-hidden="true">/</span>
              {href ? <a href={href}>{label}</a> : <span aria-current="page">{label}</span>}
            </Fragment>
          ))}
        </nav>
        <h1>{heading}</h1>
        {lead ? <p className="lead">{lead}</p> : null}
      </div>
    </section>
  );
}

export function ProductCard({ p, s }: { p: Row; s: Settings }) {
  return (
    <article className="product">
      <a href={`/urunler/${p.slug}`} className="product__media" tabIndex={-1} aria-hidden="true">
        {p.brand ? <span className="product__brand"><BrandLogo s={s} name={p.brand} /></span> : null}
        {p.image ? <img src={uploadUrl(p.image)} alt="" loading="lazy" /> : <Art spec={productModel(p)} />}
      </a>
      <div className="product__body">
        <p className="product__meta">{p.category ?? ''}</p>
        <h3><a href={`/urunler/${p.slug}`}>{p.title}</a></h3>
        <p className="product__summary">{p.summary}</p>
        <a href={`/teklif-al?urun=${p.slug}`} className="product__quote">Teklif iste</a>
      </div>
    </article>
  );
}

export function PostCard({ post }: { post: Row }) {
  return (
    <article className="post">
      {post.cover ? <a href={`/blog/${post.slug}`} className="post__media" tabIndex={-1} aria-hidden="true"><img src={uploadUrl(post.cover)} alt="" loading="lazy" /></a> : null}
      <p className="post__meta">
        {post.category ? <><a href={`/blog/kategori/${post.category_slug}`}>{post.category}</a>, </> : null}
        <time dateTime={String(post.published_at || '').slice(0, 10)}>{trDate(post.published_at)}</time>
      </p>
      <h3><a href={`/blog/${post.slug}`}>{post.title}</a></h3>
      <p>{post.excerpt || excerpt(post.content, 140)}</p>
      <p className="post__time">{readingTime(post.content)} dakikalık okuma</p>
    </article>
  );
}

export function FaqList({ faqs }: { faqs: Row[] }) {
  return (
    <div className="faq">
      {faqs.map((f) => (
        <details className="faq__item" key={f.id}>
          <summary>{f.question}<span className="faq__sign" aria-hidden="true" /></summary>
          <p><Lines text={f.answer} /></p>
        </details>
      ))}
    </div>
  );
}

export function BrandLine({ s }: { s: Settings }) {
  return (
    <ul className="brandline" aria-label="Sattığımız markalar">
      {brands(s).map((b) => (
        <li key={b.slug}><a href={`/urunler?marka=${encodeURIComponent(b.name)}`} title={`${b.name} ürünleri`}><BrandLogo s={s} name={b.name} /></a></li>
      ))}
    </ul>
  );
}

export function RefCard({ r }: { r: Row }) {
  const text = r.project ?? r.description ?? '';
  const meta = [r.sector ?? r.kind ?? '', r.city ?? '', r.year ?? ''].filter(Boolean);
  return (
    <li className="ref-card">
      <div className="ref-card__logo">
        {r.logo ? <img src={uploadUrl(r.logo)} alt={r.name} loading="lazy" /> : <span className="ref-card__mono" aria-hidden="true">{String(r.name).charAt(0).toLocaleUpperCase('tr-TR')}</span>}
      </div>
      <h3>{r.name}</h3>
      {text ? <p>{text}</p> : null}
      <div className="ref-card__meta">{meta.map((m, i) => <span key={i}>{m}</span>)}</div>
      {r.url ? <a href={r.url} className="text-link" target="_blank" rel="noopener">Web sitesi</a> : null}
    </li>
  );
}

/** Category photo with the illustration as fallback. */
export function CategoryPhoto({ c, credit = true }: { c: Row; credit?: boolean }) {
  const src = c.photo ? uploadUrl(c.photo) : '';
  if (!src && c.art === 'none') return <span className="photo photo--art photo--none"><Icon name="droplet" /></span>;
  const art = productArt(c.art ?? 'tank');
  if (!src) return <span className="photo photo--art"><Raw html={art} /></span>;
  const label = c.photo_credit ? `Fotoğraf: ${c.photo_credit}` : '';
  return (
    <figure className="photo">
      <img src={src} alt={c.name ?? ''} loading="lazy" data-fallback="" />
      <span className="photo__art" hidden><Raw html={art} /></span>
      {credit && label ? <figcaption>{c.photo_source ? <a href={c.photo_source} target="_blank" rel="noopener">{label}</a> : label}</figcaption> : null}
    </figure>
  );
}

/* ---------- Header & footer ---------- */
const NAV: [string, string, string][] = [
  ['products', '/urunler', 'Ürünler'], ['designer', '/depo-tasarla', 'Depo Tasarla'], ['services', '/hizmetler', 'Hizmetler'],
  ['about', '/hakkimizda', 'Kurumsal'], ['blog', '/blog', 'Blog'], ['contact', '/iletisim', 'İletişim'],
];
const CORP: [string, string, string][] = [['/hakkimizda', 'Hakkımızda', 'building'], ['/referanslar', 'Referanslar', 'award'], ['/cozum-ortaklari', 'Çözüm Ortakları', 'handshake'], ['/sss', 'Sık Sorulan Sorular', 'help']];

export function Header({ s, cats, active }: { s: Settings; cats: Row[]; active: string }) {
  const phone = setting(s, 'phone');
  const cur = (k: string) => (active === k ? { 'aria-current': 'page' as const } : {});
  return (
    <>
      <div className="topbar">
        <div className="container topbar__inner">
          <span><Icon name="map-pin" /> {setting(s, 'address_short', 'Kocaeli')}</span>
          <span className="hide-sm"><Icon name="clock" /> {settingLines(s, 'hours')[0] ?? ''}</span>
          <a href={telHref(phone)} className="topbar__end"><Icon name="phone" /> {phone}</a>
          <a href={`mailto:${setting(s, 'email')}`} className="hide-sm"><Icon name="mail" /> {setting(s, 'email')}</a>
        </div>
      </div>
      <header className="header" data-header="">
        <div className="container header__inner">
          <a href="/" className="logo" aria-label={`${setting(s, 'site_name')} ana sayfa`}><Raw html={siteLogoSvg(s)} /></a>
          <nav className="nav" id="nav" aria-label="Ana menü">
            <ul>
              {NAV.map(([key, href, label]) => {
                if (key === 'products' && cats.length) {
                  return (
                    <li className="has-mega" key={key}>
                      <a href={href} {...cur(key)}>{label} <Icon name="chevron-down" cls="icon icon--sm" /></a>
                      <button type="button" className="sub-toggle" aria-expanded="false" aria-label={`${label} alt menüsünü aç`}><Icon name="chevron-down" /></button>
                      <div className="mega">
                        {cats.map((c) => (
                          <a href={`/urunler/kategori/${c.slug}`} className="mega__item" key={c.id}><span className="mega__art"><Raw html={productArt(c.art)} /></span>{c.name}</a>
                        ))}
                        <a href="/urunler" className="mega__all">Tüm ürünleri gör</a>
                        <a href="/markalar" className="mega__all mega__all--soft">Markalar</a>
                      </div>
                    </li>
                  );
                }
                if (key === 'about') {
                  return (
                    <li className="has-mega" key={key}>
                      <a href={href} {...cur(key)}>{label} <Icon name="chevron-down" cls="icon icon--sm" /></a>
                      <button type="button" className="sub-toggle" aria-expanded="false" aria-label={`${label} alt menüsünü aç`}><Icon name="chevron-down" /></button>
                      <div className="mega mega--list">
                        {CORP.map(([h, l, ic]) => <a href={h} className="mega__item" key={h}><span className="mega__ic"><Icon name={ic} /></span>{l}</a>)}
                      </div>
                    </li>
                  );
                }
                return <li key={key}><a href={href} {...cur(key)}>{label}</a></li>;
              })}
            </ul>
            <div className="nav__mobile-cta">
              <a href="/teklif-al" className="btn btn--signal btn--block">Teklif iste</a>
              <a href={telHref(phone)} className="btn btn--line btn--block"><Icon name="phone" /> {phone}</a>
            </div>
          </nav>
          <div className="header__actions">
            <a href={telHref(phone)} className="header__phone hide-md" aria-label={`Telefon: ${phone}`} title={phone}><Icon name="phone" /></a>
            <a href="/teklif-al" className="btn btn--signal btn--sm hide-sm">Teklif iste</a>
            <button className="nav-toggle" type="button" aria-controls="nav" aria-expanded="false" aria-label="Menüyü aç"><span /><span /><span /></button>
          </div>
        </div>
      </header>
    </>
  );
}

export function Footer({ s, cats }: { s: Settings; cats: Row[] }) {
  const wa = waHref(setting(s, 'whatsapp', setting(s, 'phone2')), 'Merhaba, fiyat bilgisi almak istiyorum.');
  const socials = (['instagram', 'facebook', 'linkedin', 'youtube'] as const).filter((k) => setting(s, k));
  const phone = setting(s, 'phone');
  return (
    <>
      <footer className="footer">
        <div className="container footer__cta">
          <div>
            <h2>{setting(s, 'footer_cta_title')}</h2>
            <p>{setting(s, 'footer_cta_text')}</p>
          </div>
          <div className="footer__cta-actions">
            <a href="/teklif-al" className="btn btn--signal btn--lg">Teklif iste</a>
            <a href={wa} className="btn btn--line-light btn--lg" target="_blank" rel="noopener"><Icon name="whatsapp" /> WhatsApp&apos;tan yazın</a>
          </div>
        </div>
        <div className="container footer__grid">
          <div className="footer__brand">
            <a href="/" className="logo logo--light" aria-label={`${setting(s, 'site_name')} ana sayfa`}><Raw html={siteLogoSvg(s, true)} /></a>
            <p>{setting(s, 'site_tagline')}</p>
            {socials.length ? (
              <div className="socials">
                {socials.map((k) => <a key={k} href={setting(s, k)} target="_blank" rel="noopener" aria-label={k[0].toUpperCase() + k.slice(1)}><Icon name={k} /></a>)}
              </div>
            ) : null}
          </div>
          <nav aria-label="Ürün grupları">
            <h3>Ürünler</h3>
            <ul>
              {cats.map((c) => <li key={c.id}><a href={`/urunler/kategori/${c.slug}`}>{c.name}</a></li>)}
              <li><a href="/markalar">Markalar</a></li>
            </ul>
          </nav>
          <nav aria-label="Firma">
            <h3>Firma</h3>
            <ul>
              {[['/hakkimizda', 'Hakkımızda'], ['/referanslar', 'Referanslar'], ['/cozum-ortaklari', 'Çözüm ortakları'], ['/depo-tasarla', 'Depo tasarla'], ['/hizmetler', 'Hizmetler'], ['/blog', 'Blog'], ['/sss', 'Sık sorulan sorular'], ['/iletisim', 'İletişim']].map(([h, l]) => <li key={h}><a href={h}>{l}</a></li>)}
            </ul>
          </nav>
          <div>
            <h3>Bize ulaşın</h3>
            <address className="footer__contact">
              <a href={telHref(phone)}>{phone}</a>
              {setting(s, 'phone2') ? <a href={telHref(setting(s, 'phone2'))}>{setting(s, 'phone2')}</a> : null}
              <a href={`mailto:${setting(s, 'email')}`}>{setting(s, 'email')}</a>
              <span>{setting(s, 'address')}</span>
              {settingLines(s, 'hours').map((h, i) => <span className="footer__hours" key={i}>{h}</span>)}
            </address>
          </div>
        </div>
        <div className="container footer__bottom">
          <span>© {new Date().getFullYear()} {setting(s, 'site_name')}</span>
          <span>{brands(s).map((b) => b.name).join(', ')}</span>
        </div>
      </footer>
      <a className="fab-call" href={telHref(phone)} aria-label={`Bizi arayın: ${phone}`}><Icon name="phone" /></a>
      <a className="fab-wa" href={wa} target="_blank" rel="noopener" aria-label="WhatsApp ile yazın"><Icon name="whatsapp" /></a>
    </>
  );
}

export function Shell({ s, cats, active = '', children }: { s: Settings; cats: Row[]; active?: string; children: ReactNode }) {
  return (
    <>
      <a className="skip-link" href="#main">İçeriğe geç</a>
      <Header s={s} cats={cats} active={active} />
      <main id="main">{children}</main>
      <Footer s={s} cats={cats} />
      {/* inside the page segment: runs only after this page's content has hydrated */}
      <SiteScript src={`/assets/js/site.js?v=${process.env.ASSET_VERSION || '1'}`} />
    </>
  );
}
