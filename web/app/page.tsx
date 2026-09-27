import { chrome, localBusinessSchema } from '@/lib/page';
import { getProducts, getServices, getPosts, getFaqs, getRefs } from '@/lib/data';
import { setting, settingLines, settingPairs, telHref, waHref } from '@/lib/site';
import { Shell, Icon, Art, BrandLine, CategoryPhoto, ProductCard, PostCard, FaqList, RefCard, JsonLd } from '@/components/ui';
import { Designer } from '@/components/designer';

const STAT_ICONS = ['users', 'truck', 'layers', 'award', 'check-circle', 'star'];
const STEP_ICONS = ['clipboard', 'ruler', 'calculator', 'truck', 'check-circle', 'handshake'];
const TINTS: Record<string, string> = { tank: 'sky', booster: 'navy', pump: 'mist', submersible: 'sea', drop: 'sky' };

export default async function Home() {
  const { s, cats } = await chrome();
  const [products, services, posts, faqs, refs] = await Promise.all([getProducts(), getServices(), getPosts(), getFaqs(), getRefs()]);
  const featured = products.filter((p) => p.featured).slice(0, 8);
  const topPosts = [...posts].sort((a, b) => (b.featured || 0) - (a.featured || 0)).slice(0, 3);
  const stats = settingLines(s, 'stats').map((l) => { const [n, ...rest] = l.split('|'); return [n, rest.join('|')]; });
  const wa = setting(s, 'whatsapp', setting(s, 'phone2'));
  return (
    <Shell s={s} cats={cats} active="home">
      <JsonLd data={localBusinessSchema(s)} />
      <section className="hero">
        <div className="hero__glow" aria-hidden="true" />
        <div className="container hero__inner">
          <div className="hero__copy">
            <span className="pill"><Icon name="droplet" /> {setting(s, 'hero_badge')}</span>
            <h1>{setting(s, 'hero_title')}</h1>
            <p className="hero__text">{setting(s, 'hero_text')}</p>
            <div className="hero__actions">
              <a href="/urunler" className="btn btn--signal btn--lg">Ürünleri incele <Icon name="arrow-right" /></a>
              <a href={telHref(setting(s, 'phone'))} className="btn btn--line btn--lg"><Icon name="phone" /> {setting(s, 'phone')}</a>
            </div>
            <ul className="hero__trust">{settingLines(s, 'hero_trust').map((t, i) => <li key={i}><Icon name="check-circle" /> {t}</li>)}</ul>
          </div>
          <Designer s={s} />
        </div>
        <div className="container">
          <div className="logo-strip"><span className="logo-strip__label">Sattığımız markalar</span><BrandLine s={s} /></div>
        </div>
      </section>

      {stats.length ? (
        <section className="proof">
          <div className="container">
            <div className="proof__card">
              <div className="proof__intro"><h2>{setting(s, 'stats_title', 'Sahada kanıtlanmış çözümler')}</h2><p>{setting(s, 'stats_text')}</p></div>
              <ul className="proof__stats">
                {stats.map(([num, label], i) => <li key={i}><span className="proof__icon"><Icon name={STAT_ICONS[i % STAT_ICONS.length]} /></span><strong data-count={num} suppressHydrationWarning>{num}</strong><span>{label}</span></li>)}
              </ul>
            </div>
          </div>
        </section>
      ) : null}

      <section className="block">
        <div className="container">
          <div className="block__head block__head--center">
            <span className="kicker">{setting(s, 'home_cats_kicker')}</span><h2>{setting(s, 'home_cats_title')}</h2><p>{setting(s, 'home_cats_text')}</p>
          </div>
          <div className="cat-grid">
            {cats.map((c) => (
              <a href={`/urunler/kategori/${c.slug}`} className={`cat-card cat-card--${TINTS[c.art] ?? 'sky'}`} key={c.id}>
                <span className="cat-card__art"><CategoryPhoto c={c} credit={false} /></span>
                <span className="cat-card__body"><h3>{c.name}</h3><p>{c.summary}</p><span className="cat-card__more">{c.cnt} ürün <Icon name="arrow-right" /></span></span>
              </a>
            ))}
          </div>
        </div>
      </section>

      {featured.length ? (
        <section className="block block--soft">
          <div className="container">
            <div className="block__head block__head--row">
              <div><span className="kicker">{setting(s, 'home_featured_kicker')}</span><h2>{setting(s, 'home_featured_title')}</h2></div>
              <a href="/urunler" className="btn btn--line">Tüm ürünler <Icon name="arrow-right" /></a>
            </div>
            <div className="product-grid">{featured.map((p) => <ProductCard p={p} s={s} key={p.id} />)}</div>
          </div>
        </section>
      ) : null}

      <section className="block">
        <div className="container">
          <div className="block__head block__head--center"><span className="kicker">{setting(s, 'home_steps_kicker')}</span><h2>{setting(s, 'home_steps_title')}</h2></div>
          <ol className="steps">
            {settingPairs(s, 'home_steps').map(([h, t], i) => <li key={i}><span className="steps__icon"><Icon name={STEP_ICONS[i % STEP_ICONS.length]} /></span><h3>{h}</h3><p>{t}</p></li>)}
          </ol>
        </div>
      </section>

      <section className="block block--soft">
        <div className="container why">
          <div className="why__art" aria-hidden="true">
            <div className="why__stage"><Art spec="tank:paslanmaz" /></div>
            {settingPairs(s, 'home_why_badges').slice(0, 2).map(([a, b], i) => (
              <div className={`why__badge why__badge--${i ? 'b' : 'a'}`} key={i}><Icon name={i ? 'wrench' : 'shield'} /><span><strong>{a}</strong>{b}</span></div>
            ))}
          </div>
          <div>
            <span className="kicker">{setting(s, 'home_why_kicker')}</span>
            <h2>{setting(s, 'about_title')}</h2>
            <p className="muted">{setting(s, 'about_text').split('\n')[0]}</p>
            <ul className="ticks">{settingLines(s, 'about_values').map((v, i) => <li key={i}>{v}</li>)}</ul>
            <a href="/hakkimizda" className="btn btn--navy">Bizi tanıyın <Icon name="arrow-right" /></a>
          </div>
        </div>
      </section>

      {services.length ? (
        <section className="block">
          <div className="container">
            <div className="block__head block__head--center"><span className="kicker">{setting(s, 'home_services_kicker')}</span><h2>{setting(s, 'home_services_title')}</h2></div>
            <div className="svc-grid">
              {services.slice(0, 4).map((x) => <a href={`/hizmetler/${x.slug}`} className="svc-card" key={x.id}><span className="svc-card__icon"><Icon name={x.icon} /></span><h3>{x.title}</h3><p>{x.summary}</p></a>)}
            </div>
          </div>
        </section>
      ) : null}

      {refs.filter((r) => r.featured).length ? (
        <section className="block">
          <div className="container">
            <div className="block__head block__head--row">
              <div><span className="kicker">{setting(s, 'home_refs_kicker')}</span><h2>{setting(s, 'home_refs_title')}</h2><p>{setting(s, 'home_refs_text')}</p></div>
              <a href="/referanslar" className="btn btn--line">Tüm referanslar <Icon name="arrow-right" /></a>
            </div>
            <ul className="ref-grid">{refs.filter((r) => r.featured).slice(0, 8).map((r) => <RefCard r={r} key={r.id} />)}</ul>
          </div>
        </section>
      ) : null}

      {topPosts.length ? (
        <section className="block block--soft">
          <div className="container">
            <div className="block__head block__head--row">
              <div><span className="kicker">{setting(s, 'home_blog_kicker')}</span><h2>{setting(s, 'home_blog_title')}</h2></div>
              <a href="/blog" className="btn btn--line">Bütün yazılar <Icon name="arrow-right" /></a>
            </div>
            <div className="post-grid">{topPosts.map((p) => <PostCard post={p} key={p.id} />)}</div>
          </div>
        </section>
      ) : null}

      <section className="block">
        <div className="container duo">
          <div>
            <span className="kicker">{setting(s, 'home_faq_kicker')}</span>
            <h2>{setting(s, 'home_faq_title')}</h2>
            <p className="muted">{setting(s, 'home_faq_text')}</p>
            <div className="contact-chips">
              <a href={telHref(setting(s, 'phone'))}><Icon name="phone" /><span><small>Telefon</small>{setting(s, 'phone')}</span></a>
              <a href={waHref(wa)} target="_blank" rel="noopener"><Icon name="whatsapp" /><span><small>WhatsApp</small>{wa}</span></a>
            </div>
          </div>
          <div>
            <FaqList faqs={faqs.slice(0, 5)} />
            <p className="mt"><a href="/sss" className="text-link">Bütün sorular</a></p>
          </div>
        </div>
      </section>
    </Shell>
  );
}
