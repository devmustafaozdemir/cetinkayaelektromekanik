import type { Metadata } from 'next';
import { BlogList } from '@/components/blog';

type P = { searchParams: Promise<{ q?: string; sayfa?: string }> };

export async function generateMetadata({ searchParams }: P): Promise<Metadata> {
  const sp = await searchParams;
  return { title: 'Blog', description: 'Su depolama, pompa ve hidrofor seçimi üzerine rehberler ve duyurular.', alternates: { canonical: '/blog' }, ...(sp.q ? { robots: { index: false, follow: true } } : {}) };
}

export default async function Page({ searchParams }: P) {
  const sp = await searchParams;
  return <BlogList search={String(sp.q ?? '').trim()} page={Number(sp.sayfa) || 1} />;
}
