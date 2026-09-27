import type { Metadata, Viewport } from 'next';
import { getSettings } from '@/lib/data';
import { setting, siteUrl } from '@/lib/site';

// Content comes from Supabase on every request, so admin changes show up immediately.
export const dynamic = 'force-dynamic';

const V = process.env.ASSET_VERSION || '1';

export async function generateMetadata(): Promise<Metadata> {
  const s = await getSettings();
  const name = setting(s, 'site_name', 'Çetinkaya Elektromekanik');
  return {
    metadataBase: new URL(siteUrl()),
    title: { default: `${name} | ${setting(s, 'site_tagline')}`, template: `%s | ${name}` },
    description: setting(s, 'meta_description'),
    icons: { icon: { url: '/assets/img/favicon.svg', type: 'image/svg+xml' } },
    openGraph: { siteName: name, locale: 'tr_TR', type: 'website' },
    // used by assets/js/site.js to send the quote and contact forms to Supabase
    other: {
      'sb-url': process.env.SUPABASE_URL ?? '',
      'sb-key': process.env.SUPABASE_ANON_KEY ?? '',
      'site-base': '/',
    },
  };
}

export const viewport: Viewport = { width: 'device-width', initialScale: 1, themeColor: '#1f3a60' };

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="tr">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        {/* eslint-disable-next-line @next/next/no-page-custom-font */}
        <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href={`/assets/css/site.css?v=${V}`} />
      </head>
      <body>
        {children}
      </body>
    </html>
  );
}
