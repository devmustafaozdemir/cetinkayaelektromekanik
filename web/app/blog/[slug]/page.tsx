import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { chrome } from '@/lib/page';
import { getPosts } from '@/lib/data';
import { buildToc, cleanHtml, excerpt, readingTime, setting, siteUrl, trDate, uploadUrl } from '@/lib/site';
import { Shell, Icon, PostCard, JsonLd } from '@/components/ui';

type P = { params: Promise<{ slug: string }> };
const find = async (slug: string) => (await getPosts()).find((p) => p.slug === slug);

export async function generateMetadata({ params }: P): Promise<Metadata> {
  const p = await find((await params).slug);
  if (!p) return {};
  const img = p.cover ? uploadUrl(p.cover) : '';
  return {
    title: p.meta_title || p.title,
    description: p.meta_desc || p.excerpt || excerpt(p.content),
    alternates: { canonical: `/blog/${p.slug}` },
    openGraph: { type: 'article', ...(img ? { images: [img] } : {}) },
  };
}

export default async function Page({ params }: P) {
  const { slug } = await params;
  const [{ s, cats }, posts] = await Promise.all([chrome(), getPosts()]);
  const idx = posts.findIndex((p) => p.slug === slug);
  if (idx < 0) notFound();
  const post = posts[idx];
  const { html, toc } = buildToc(cleanHtml(post.content));
  const related = posts.filter((p) => p.id !== post.id).sort((a, b) => Number(b.category_id === post.category_id) - Number(a.category_id === post.category_id)).slice(0, 3);
  const next = idx > 0 ? posts[idx - 1] : null; // posts are newest first
  const prev = idx < posts.length - 1 ? posts[idx + 1] : null;
  const shareUrl = `${siteUrl()}/blog/${post.slug}`;
  const img = post.cover ? uploadUrl(post.cover) : '';
  return (
    <Shell s={s} cats={cats} active="blog">
      <JsonLd data={{
        '@context': 'https://schema.org', '@type': 'BlogPosting', headline: post.title, description: post.excerpt,
        ...(img ? { image: img } : {}), datePublished: post.published_at, dateModified: post.updated_at,
        author: { '@type': 'Organization', name: setting(s, 'site_name') }, publisher: { '@type': 'Organization', name: setting(s, 'site_name') }, mainEntityOfPage: shareUrl,
      }} />
      <div className="read-progress" data-progress="" />
      <article>
        <header className="article-head">
          <div className="container container--mid">
            <nav className="breadcrumb" aria-label="Sayfa yolu">
              <a href="/">Ana sayfa</a><span aria-hidden="true">/</span><a href="/blog">Blog</a>
              {post.category ? <><span aria-hidden="true">/</span><a href={`/blog/kategori/${post.category_slug}`}>{post.category}</a></> : null}
            </nav>
            <h1>{post.title}</h1>
            {post.excerpt ? <p className="lead">{post.excerpt}</p> : null}
            <p className="article-meta">{trDate(post.published_at || post.created_at)}, {readingTime(post.content)} dakikalık okuma</p>
            {img ? <div className="article-cover"><img src={img} alt="" /></div> : null}
          </div>
        </header>
        <div className="block block--tight">
          <div className="container article">
            <aside className="article__side">
              {toc.length > 1 ? (
                <nav className="toc" aria-label="İçindekiler">
                  <h2>Bu yazıda</h2>
                  <ol>{toc.map((t) => <li className={`toc--h${t.level}`} key={t.id}><a href={`#${t.id}`}>{t.text}</a></li>)}</ol>
                </nav>
              ) : null}
              <div>
                <h2>Paylaşın</h2>
                <div className="share">
                  <a href={`https://wa.me/?text=${encodeURIComponent(`${post.title} ${shareUrl}`)}`} target="_blank" rel="noopener" aria-label="WhatsApp'ta paylaş"><Icon name="whatsapp" /></a>
                  <a href={`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(shareUrl)}`} target="_blank" rel="noopener" aria-label="LinkedIn'de paylaş"><Icon name="linkedin" /></a>
                  <a href={`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`} target="_blank" rel="noopener" aria-label="Facebook'ta paylaş"><Icon name="facebook" /></a>
                  <button type="button" data-copy={shareUrl} aria-label="Bağlantıyı kopyala"><Icon name="link" /></button>
                </div>
              </div>
            </aside>
            <div>
              <div className="prose post-content" dangerouslySetInnerHTML={{ __html: html }} />
              <div className="callout">
                <p><strong>Projeniz için doğru ürünü birlikte seçelim</strong>İhtiyacınızı yazın, kapasite hesabıyla birlikte teklif gönderelim.</p>
                <a href="/teklif-al" className="btn btn--signal">Teklif iste</a>
              </div>
              {prev || next ? (
                <nav className="post-nav" aria-label="Diğer yazılar">
                  {prev ? <a href={`/blog/${prev.slug}`}><small>Önceki yazı</small><span>{prev.title}</span></a> : <span />}
                  {next ? <a href={`/blog/${next.slug}`} className="post-nav__next"><small>Sonraki yazı</small><span>{next.title}</span></a> : null}
                </nav>
              ) : null}
            </div>
          </div>
        </div>
      </article>
      {related.length ? (
        <section className="block block--steel">
          <div className="container"><div className="block__head"><h2>Diğer rehberler</h2></div><div className="post-grid">{related.map((r) => <PostCard post={r} key={r.id} />)}</div></div>
        </section>
      ) : null}
    </Shell>
  );
}
