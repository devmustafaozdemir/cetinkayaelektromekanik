import { chrome } from '@/lib/page';
import { getPosts, getBlogCategories } from '@/lib/data';
import { excerpt, readingTime, trDate, uploadUrl, withIds, type Row } from '@/lib/site';
import { DESIGNER_SVGS } from '@/lib/generated';
import { Shell, PageHero, Icon, PostCard, Raw } from './ui';

const PER_PAGE = 9;

export async function BlogList({ category, search = '', page = 1 }: { category?: Row | null; search?: string; page?: number }) {
  const [{ s, cats }, posts, bcats] = await Promise.all([chrome(), getPosts(), getBlogCategories()]);
  const q = search.toLocaleLowerCase('tr-TR');
  const filtered = posts.filter((p) => (!category || p.category_id === category.id)
    && (!q || [p.title, p.excerpt, p.content].join(' ').toLocaleLowerCase('tr-TR').includes(q)));
  const total = filtered.length;
  const pages = Math.max(1, Math.ceil(total / PER_PAGE));
  const cur = Math.min(Math.max(1, page), pages);
  const list = filtered.slice((cur - 1) * PER_PAGE, cur * PER_PAGE);
  const featured = cur === 1 && !search && !category && list.length ? list.shift()! : null;
  const base = category ? `/blog/kategori/${category.slug}` : '/blog';
  const qs = (n: number) => {
    const u = new URLSearchParams();
    if (search) u.set('q', search);
    if (n > 1) u.set('sayfa', String(n));
    return u.size ? `?${u}` : '?';
  };
  const counts = new Map<number, number>();
  posts.forEach((p) => counts.set(p.category_id, (counts.get(p.category_id) ?? 0) + 1));
  return (
    <Shell s={s} cats={cats} active="blog">
      <PageHero heading={category ? category.name : 'Blog'} lead={category ? undefined : 'Su depolama, pompa ve hidrofor seçimi üzerine rehberler ve firmamızdan duyurular.'} crumbs={category ? [['/blog', 'Blog'], [null, category.name]] : [[null, 'Blog']]} />
      <section className="block block--tight">
        <div className="container">
          <div className="blog-bar">
            <nav className="pills" aria-label="Kategoriler">
              <a href="/blog" {...(!category ? { 'aria-current': 'page' as const } : {})}>Hepsi</a>
              {bcats.filter((c) => counts.get(c.id)).map((c) => <a key={c.id} href={`/blog/kategori/${c.slug}`} {...(category?.id === c.id ? { 'aria-current': 'page' as const } : {})}>{c.name}</a>)}
            </nav>
            <form className="search" method="get" action={base} role="search">
              <Icon name="search" /><input type="search" name="q" defaultValue={search} placeholder="Yazılarda ara" aria-label="Blogda ara" />
            </form>
          </div>
          {search ? <p className="muted">“{search}” için {total} yazı bulundu. <a href={base} className="text-link">Aramayı temizle</a></p> : null}
          {featured ? (
            <article className="post post--lead">
              {featured.cover
                ? <a href={`/blog/${featured.slug}`} className="post__media" tabIndex={-1} aria-hidden="true"><img src={uploadUrl(featured.cover)} alt="" /></a>
                : <a href={`/blog/${featured.slug}`} className="post--lead__art" tabIndex={-1} aria-hidden="true"><Raw html={withIds(DESIGNER_SVGS.blogLead)} /></a>}
              <div>
                <p className="post__meta">{featured.category ? <><a href={`/blog/kategori/${featured.category_slug}`}>{featured.category}</a>, </> : null}{trDate(featured.published_at)}</p>
                <h2><a href={`/blog/${featured.slug}`}>{featured.title}</a></h2>
                <p>{featured.excerpt || excerpt(featured.content, 200)}</p>
                <p className="post__time">{readingTime(featured.content)} dakikalık okuma</p>
              </div>
            </article>
          ) : null}
          {list.length ? <div className="post-grid">{list.map((p) => <PostCard post={p} key={p.id} />)}</div>
            : !featured ? <div className="empty"><p>Bu aramayla eşleşen yazı yok. <a href="/blog" className="text-link">Bütün yazılara dönün</a>.</p></div> : null}
          {pages > 1 ? (
            <nav className="pagination" aria-label="Sayfalar">
              {Array.from({ length: pages }, (_, i) => i + 1).map((n) => <a key={n} href={qs(n)} {...(n === cur ? { 'aria-current': 'page' as const } : {})}>{n}</a>)}
            </nav>
          ) : null}
        </div>
      </section>
    </Shell>
  );
}
