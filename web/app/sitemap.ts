import type { MetadataRoute } from 'next';
import { getBlogCategories, getCategories, getPosts, getProducts, getServices } from '@/lib/data';
import { siteUrl } from '@/lib/site';

export const dynamic = 'force-dynamic';

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const b = siteUrl();
  const [cats, products, services, bcats, posts] = await Promise.all([getCategories(), getProducts(), getServices(), getBlogCategories(), getPosts()]);
  const fixed = ['/', '/depo-tasarla', '/urunler', '/markalar', '/hizmetler', '/hakkimizda', '/referanslar', '/cozum-ortaklari', '/blog', '/sss', '/iletisim', '/teklif-al'];
  return [
    ...fixed.map((u) => ({ url: b + u })),
    ...cats.map((c) => ({ url: `${b}/urunler/kategori/${c.slug}` })),
    ...products.map((p) => ({ url: `${b}/urunler/${p.slug}`, lastModified: p.updated_at })),
    ...services.map((x) => ({ url: `${b}/hizmetler/${x.slug}` })),
    ...bcats.map((c) => ({ url: `${b}/blog/kategori/${c.slug}` })),
    ...posts.map((p) => ({ url: `${b}/blog/${p.slug}`, lastModified: p.updated_at })),
  ];
}
