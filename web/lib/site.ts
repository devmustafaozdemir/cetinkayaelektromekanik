import sanitizeHtml from 'sanitize-html';
import { ICONS, MODEL_SVGS, TEXT_DEFAULTS, MODEL_TYPES, LOGO_SVGS } from './generated';

export type Row = Record<string, any>; // eslint-disable-line @typescript-eslint/no-explicit-any
export type Settings = Record<string, string>;

/* ---------- Settings (with the editable text defaults) ---------- */
export function setting(s: Settings, key: string, def = ''): string {
  const v = s[key];
  return v !== undefined && v !== '' ? v : def !== '' ? def : TEXT_DEFAULTS[key] ?? '';
}
export const settingLines = (s: Settings, key: string) =>
  setting(s, key).split(/\r?\n/).map((l) => l.trim()).filter(Boolean);
export const settingPairs = (s: Settings, key: string): [string, string][] =>
  settingLines(s, key).map((l) => {
    const [a, b = ''] = l.split(/\|(.*)/s);
    return [a.trim(), b.trim()];
  });

/** Designer consumption table: [label, litres/day, count label, hint]. */
export function waterUses(s: Settings): [string, number, string, string][] {
  const out: [string, number, string, string][] = [];
  for (const l of settingLines(s, 'water_use')) {
    const p = l.split('|').map((x) => x.trim());
    const lpd = parseInt(p[1] || '', 10);
    if (p[0] && lpd > 0) out.push([p[0], lpd, p[2] || 'Kişi sayısı', p.slice(3).join('|')]);
  }
  return out.length ? out : [['Konut', 150, 'Kişi sayısı', '']];
}

/* ---------- Text & links ---------- */
export function slugify(t: string): string {
  const map: Record<string, string> = { ç: 'c', Ç: 'c', ğ: 'g', Ğ: 'g', ı: 'i', I: 'i', İ: 'i', ö: 'o', Ö: 'o', ş: 's', Ş: 's', ü: 'u', Ü: 'u' };
  const s = String(t || '').replace(/[çÇğĞıIİöÖşŞüÜ]/g, (c) => map[c]).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  return s || 'icerik';
}
const decode = (s: string) => s.replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"').replace(/&#0?39;/g, "'");
export const stripTags = (html: string) => decode(String(html || '').replace(/<[^>]+>/g, ' ')).replace(/\s+/g, ' ').trim();
export function excerpt(html: string, len = 160): string {
  const t = stripTags(html);
  return t.length > len ? t.slice(0, len - 1).trimEnd() + '…' : t;
}
export const readingTime = (html: string) => Math.max(1, Math.ceil(stripTags(html).split(' ').filter(Boolean).length / 200));
const MONTHS = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
export function trDate(v?: string | null): string {
  if (!v) return '';
  const d = new Date(v);
  if (isNaN(+d)) return '';
  const p = new Intl.DateTimeFormat('tr-TR', { timeZone: 'Europe/Istanbul', day: 'numeric', month: 'numeric', year: 'numeric' }).formatToParts(d);
  const g = (t: string) => Number(p.find((x) => x.type === t)?.value);
  return `${g('day')} ${MONTHS[g('month') - 1]} ${g('year')}`;
}
export function telHref(phone: string): string {
  let d = String(phone || '').replace(/\D+/g, '');
  if (d.startsWith('0')) d = '90' + d.slice(1);
  return 'tel:+' + d;
}
export function waHref(phone: string, text = ''): string {
  let d = String(phone || '').replace(/\D+/g, '');
  if (d.startsWith('0')) d = '90' + d.slice(1);
  return 'https://wa.me/' + d + (text ? '?text=' + encodeURIComponent(text) : '');
}
export const uploadUrl = (f?: string | null) => (!f ? '' : /^https?:\/\//.test(f) ? f : '/uploads/' + encodeURIComponent(f));
export const siteUrl = () => (process.env.SITE_URL || 'http://localhost:3000').replace(/\/$/, '');

export function parseSpecs(specs: string): [string, string][] {
  return String(specs || '').split(/\r?\n/).map((l) => l.trim()).filter(Boolean).map((l) => {
    const [a, b = ''] = l.split(/\|(.*)/s);
    return [a.trim(), b.trim()];
  });
}

export const QUOTE_TYPES: Record<string, string> = {
  product: 'Ürün teklifi', project: 'Proje / keşif talebi', install: 'Montaj & kurulum', service: 'Bakım & servis',
};

/* ---------- Brands ---------- */
const BUNDLED_BRAND_LOGOS = ['meksis', 'grundfos', 'wilo', 'standart-pompa', 'sumak'];
export type Brand = { name: string; desc: string; slug: string; logo: string };
export function brands(s: Settings): Brand[] {
  return settingLines(s, 'brands').map((line) => {
    const [name, desc = ''] = line.split(/\|(.*)/s).map((x) => (x || '').trim());
    const slug = slugify(name);
    return { name, desc, slug, logo: brandLogoUrl(s, slug) };
  });
}
export function brandLogoUrl(s: Settings, slug: string): string {
  const up = s['brand_logo_' + slug];
  if (up) return uploadUrl(up);
  return BUNDLED_BRAND_LOGOS.includes(slug) ? `/assets/img/brands/${slug}.webp` : '';
}

/* ---------- Icons, drawings, logo (raw SVG strings) ---------- */
export function iconSvg(name: string, cls = 'icon'): string {
  return `<svg class="${cls}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[name] ?? ICONS.wrench}</svg>`;
}
let uid = 0;
const nextId = () => 'g' + (++uid).toString(36);
export const withIds = (svg: string) => svg.replaceAll('__ID__', nextId());

export function artDefaultModel(art: string): string {
  if (art === 'none') return '';
  return ({ tank: 'tank:galvaniz', booster: 'booster:3', pump: 'pump:horizontal', submersible: 'sub:deep', drop: 'tank:grp' } as Record<string, string>)[art] ?? 'tank:galvaniz';
}
export function productModel(p: Row): string {
  const m = String(p.model || '');
  return MODEL_TYPES[m] ? m : artDefaultModel(String(p.art || 'tank'));
}
export function modelSvg(spec: string, cls = 'art'): string {
  const svg = MODEL_SVGS[spec] ?? MODEL_SVGS['tank:galvaniz'];
  return withIds(svg).replace(/^<svg class="art/, `<svg class="${cls}`);
}
export const productArt = (kind: string, cls = 'art') => modelSvg(artDefaultModel(kind), cls);

export function siteLogoSvg(s: Settings, light = false): string {
  const custom = light && s.logo_light ? s.logo_light : s.logo;
  if (custom) return `<img class="logo__img" src="${escapeAttr(uploadUrl(custom))}" alt="${escapeAttr(setting(s, 'site_name'))}">`;
  return withIds(light ? LOGO_SVGS.light : LOGO_SVGS.dark);
}
export const escapeAttr = (v: string) => String(v ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c] as string);

/* ---------- Rich text ---------- */
export function cleanHtml(html: string): string {
  return sanitizeHtml(String(html || ''), {
    allowedTags: ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'h2', 'h3', 'h4', 'blockquote', 'pre', 'code', 'ul', 'ol', 'li', 'a', 'img', 'figure', 'figcaption', 'hr', 'span', 'table', 'thead', 'tbody', 'tr', 'th', 'td', 'iframe'],
    allowedAttributes: {
      p: ['class'], h2: ['id'], h3: ['id'], h4: ['id'], pre: ['class'], li: ['data-list'], a: ['href', 'target', 'rel'], img: ['src', 'alt', 'width', 'height'], span: ['class'],
      iframe: ['src', 'width', 'height', 'allowfullscreen', 'frameborder'],
    },
    allowedSchemes: ['http', 'https', 'mailto', 'tel'],
    allowedIframeHostnames: ['www.youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com', 'www.google.com'],
    transformTags: { a: (tagName, attribs) => ({ tagName, attribs: attribs.target === '_blank' ? { ...attribs, rel: 'noopener noreferrer' } : attribs }) },
  });
}

/** Adds ids to h2/h3 headings; returns the html and a table of contents. */
export function buildToc(html: string): { html: string; toc: { level: number; text: string; id: string }[] } {
  const toc: { level: number; text: string; id: string }[] = [];
  const used = new Set<string>();
  const out = html.replace(/<(h[23])([^>]*)>([\s\S]*?)<\/\1>/gi, (_m, tag: string, attrs: string, inner: string) => {
    const text = stripTags(inner);
    let id = slugify(text);
    const base = id;
    let n = 2;
    while (used.has(id)) id = `${base}-${n++}`;
    used.add(id);
    toc.push({ level: Number(tag[1]), text, id });
    return `<${tag}${attrs.replace(/\sid="[^"]*"/i, '')} id="${id}">${inner}</${tag}>`;
  });
  return { html: out, toc };
}

/* ---------- Modular tank math (mirrors assets/js/site.js) ---------- */
export const TANK_MODULE = 1.08;
const cells = (a: number): [number, number][] => {
  const n = Math.floor(a + 1e-9);
  const out: [number, number][] = [];
  for (let i = 0; i < n; i++) out.push([i, 1]);
  if (a - n > 1e-9) out.push([n, a - n]);
  return out;
};
export function tankPanels(W: number, L: number, H: number) {
  const n = { full: 0, half: 0, quarter: 0 };
  const add = (a: number, b: number, t: number) => {
    const fa = Math.floor(a + 1e-9), ha = a - fa > 1e-9 ? 1 : 0, fb = Math.floor(b + 1e-9), hb = b - fb > 1e-9 ? 1 : 0;
    n.full += fa * fb * t; n.half += (fa * hb + ha * fb) * t; n.quarter += ha * hb * t;
  };
  add(W, H, 2); add(L, H, 2); add(W, L, 2);
  return n;
}
export const tankVolume = (W: number, L: number, H: number) => W * L * H * TANK_MODULE ** 3;
const wallFactor = (H: number) => cells(H).reduce((sum, [y, z]) => sum + z * (1 + 0.15 * (H - y - z + z - 1)), 0);
export function tankOptions(need: number, maxH = 3) {
  const out: { W: number; L: number; H: number; v: number; cost: number }[] = [];
  for (let h2 = 1; h2 <= maxH * 2; h2++) {
    const H = h2 / 2;
    for (let w2 = 2; w2 <= 40; w2++) {
      const W = w2 / 2;
      for (let l2 = 2; l2 <= w2; l2++) {
        const L = l2 / 2;
        const v = tankVolume(W, L, H);
        if (v < need) continue;
        const p = tankPanels(W, L, H);
        out.push({ W, L, H, v, cost: 2 * W * L + 2 * (W + L) * wallFactor(H) + 0.3 * (p.half + p.quarter) });
        break;
      }
    }
  }
  return out.sort((a, b) => a.cost - b.cost || a.v - b.v);
}
export const fmtMod = (x: number) => String(x).replace('.', ',');
export const fmtNum = (x: number, d = 1) => new Intl.NumberFormat('tr-TR', { minimumFractionDigits: d, maximumFractionDigits: d }).format(x);
export const fmtM = (mod: number) => fmtNum(mod * TANK_MODULE, 2);
