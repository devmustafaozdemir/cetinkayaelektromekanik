import type { Metadata } from 'next';
import { notFound } from 'next/navigation';
import { Catalog } from '@/components/catalog';
import { getCategories } from '@/lib/data';

type P = { params: Promise<{ slug: string }>; searchParams: Promise<{ marka?: string; q?: string }> };
const find = async (slug: string) => (await getCategories()).find((c) => c.slug === slug);

export async function generateMetadata({ params, searchParams }: P): Promise<Metadata> {
  const [{ slug }, sp] = await Promise.all([params, searchParams]);
  const c = await find(slug);
  if (!c) return {};
  return { title: c.name, description: c.summary, alternates: { canonical: `/urunler/kategori/${c.slug}` }, ...(sp.marka || sp.q ? { robots: { index: false, follow: true } } : {}) };
}

export default async function Page({ params, searchParams }: P) {
  const [{ slug }, sp] = await Promise.all([params, searchParams]);
  const c = await find(slug);
  if (!c) notFound();
  return <Catalog category={c} brand={String(sp.marka ?? '').trim()} search={String(sp.q ?? '').trim()} />;
}
