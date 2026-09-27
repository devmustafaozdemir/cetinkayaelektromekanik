import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { BlogList } from '@/components/blog';
import { getBlogCategories } from '@/lib/data';

type P = { params: Promise<{ slug: string }>; searchParams: Promise<{ q?: string; sayfa?: string }> };
const find = async (slug: string) => (await getBlogCategories()).find((c) => c.slug === slug);

export async function generateMetadata({ params }: P): Promise<Metadata> {
  const c = await find((await params).slug);
  return c ? { title: `${c.name} – Blog`, description: `${c.name} kategorisindeki yazılar.`, alternates: { canonical: `/blog/kategori/${c.slug}` } } : {};
}

export default async function Page({ params, searchParams }: P) {
  const [{ slug }, sp] = await Promise.all([params, searchParams]);
  const c = await find(slug);
  if (!c) notFound();
  return <BlogList category={c} search={String(sp.q ?? '').trim()} page={Number(sp.sayfa) || 1} />;
}
