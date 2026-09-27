import { notFound, redirect } from 'next/navigation';
import { getSettings } from '@/lib/data';
import { brands } from '@/lib/site';

// Old static-site brand URLs (/urunler/marka/grundfos) → /urunler?marka=Grundfos
export default async function Page({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const b = brands(await getSettings()).find((x) => x.slug === slug);
  if (!b) notFound();
  redirect(`/urunler?marka=${encodeURIComponent(b.name)}`);
}
