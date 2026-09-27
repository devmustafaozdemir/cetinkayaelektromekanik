import type { Metadata } from 'next';
import { Catalog } from '@/components/catalog';

type P = { searchParams: Promise<{ marka?: string; q?: string }> };

export async function generateMetadata({ searchParams }: P): Promise<Metadata> {
  const sp = await searchParams;
  return {
    title: 'Ürünler',
    description: 'Meksis modüler su depoları, Grundfos, Wilo, Standart Pompa ve Sumak pompa ve hidrofor sistemleri.',
    alternates: { canonical: '/urunler' },
    ...(sp.marka || sp.q ? { robots: { index: false, follow: true } } : {}),
  };
}

export default async function Page({ searchParams }: P) {
  const sp = await searchParams;
  return <Catalog brand={String(sp.marka ?? '').trim()} search={String(sp.q ?? '').trim()} />;
}
