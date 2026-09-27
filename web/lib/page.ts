import 'server-only';
import { getSettings, getCategories } from './data';
import { setting, siteUrl, type Settings } from './site';

/** Settings + categories used by every page's header and footer. */
export async function chrome() {
  const [s, cats] = await Promise.all([getSettings(), getCategories()]);
  return { s, cats };
}

export function localBusinessSchema(s: Settings) {
  const same = ['instagram', 'facebook', 'linkedin', 'youtube'].map((k) => setting(s, k)).filter(Boolean);
  return {
    '@context': 'https://schema.org', '@type': 'Store', name: setting(s, 'site_name'), description: setting(s, 'meta_description'),
    url: siteUrl(), telephone: setting(s, 'phone'), email: setting(s, 'email'),
    address: { '@type': 'PostalAddress', streetAddress: setting(s, 'address'), addressLocality: 'İzmit', addressRegion: 'Kocaeli', addressCountry: 'TR' },
    ...(same.length ? { sameAs: same } : {}),
  };
}
