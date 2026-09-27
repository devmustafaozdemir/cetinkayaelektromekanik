import { createClient } from '@supabase/supabase-js';
import { ICONS } from './icons.js';

const cfg = window.APP_CONFIG || {};
export const sb = createClient(cfg.supabaseUrl, cfg.supabaseKey, {
  auth: { persistSession: true, autoRefreshToken: true, storageKey: 'cem-admin-auth' },
});
export const SITE_URL = cfg.siteUrl || '/';

/* ---------- Domain constants (mirrors app/helpers.php, app/models.php) ---------- */
export const QUOTE_STATUSES = {
  new: ['Yeni Talep', 'amber'],
  contacted: ['İletişime Geçildi', 'blue'],
  quoted: ['Teklif Verildi', 'violet'],
  won: ['Satışa Döndü', 'green'],
  lost: ['Olumsuz', 'red'],
};
export const QUOTE_TYPES = { product: 'Ürün teklifi', project: 'Proje / keşif talebi', install: 'Montaj & kurulum', service: 'Bakım & servis' };
export const MODEL_TYPES = {
  'tank:galvaniz': 'Modüler depo – galvaniz', 'tank:paslanmaz': 'Modüler depo – paslanmaz', 'tank:grp': 'Modüler depo – GRP', 'tank:sandvic': 'Modüler depo – izolasyonlu',
  'booster:1': 'Hidrofor – tek pompalı', 'booster:2': 'Hidrofor – çift pompalı', 'booster:3': 'Hidrofor – üç pompalı', 'booster:4': 'Hidrofor – dört pompalı',
  'pump:horizontal': 'Pompa – yatay santrifüj', 'pump:vertical': 'Pompa – dikey çok kademeli', 'pump:circulator': 'Pompa – sirkülasyon',
  'sub:deep': 'Dalgıç – derin kuyu', 'sub:drain': 'Dalgıç – drenaj',
};
export const ARTS = { tank: 'Modüler depo', booster: 'Hidrofor', pump: 'Santrifüj pompa', submersible: 'Dalgıç pompa', drop: 'Su damlası' };
export const SERVICE_ICONS = ['droplet', 'ruler', 'wrench', 'cog', 'gauge', 'truck', 'shield', 'package', 'calendar', 'building', 'calculator', 'handshake', 'award', 'layers'];
export const SETTINGS_GROUPS = {
  general: ['Genel', 'settings', {
    site_name: ['Firma adı', 'text'],
    site_tagline: ['Slogan', 'text'],
    meta_description: ['Site açıklaması (Google)', 'textarea', 'Arama sonuçlarında görünen açıklama. 150-160 karakter önerilir.'],
  }],
  brands: ['Markalar', 'award', {
    brands: ['Marka listesi', 'textarea', 'Her satıra bir marka: Marka Adı | Kısa açıklama. Ürünlerdeki marka adı bununla aynı yazılmalı.'],
  }],
  contact: ['İletişim', 'phone', {
    phone: ['Telefon', 'text'], phone2: ['Telefon 2 / GSM', 'text'], whatsapp: ['WhatsApp numarası', 'text'], email: ['E-posta', 'email'],
    address: ['Adres', 'textarea'], address_short: ['Kısa adres', 'text', 'Üst barda görünür (örn. İzmit, Kocaeli).'],
    hours: ['Çalışma saatleri', 'textarea', 'Her satıra bir gün aralığı. İlk satır üst barda görünür.'],
    map_link: ['Google Maps bağlantısı', 'url'], map_embed: ['Harita embed adresi', 'url', 'Google Maps › Paylaş › Harita yerleştir kısmındaki iframe “src” adresi.'],
  }],
  home: ['Ana Sayfa', 'home', {
    hero_badge: ['Üst rozet metni', 'text'], hero_title: ['Ana başlık', 'text'], hero_text: ['Açıklama', 'textarea'],
    stats_title: ['“Sahada kanıtlanmış” başlığı', 'text'], stats_text: ['“Sahada kanıtlanmış” metni', 'textarea'],
    stats: ['Rakamlar', 'textarea', 'Her satır: değer|etiket (örn. 500+|Mutlu müşteri).'],
  }],
  about: ['Hakkımızda', 'users', {
    about_title: ['Başlık', 'text'], about_text: ['Metin', 'textarea', 'Paragrafları boş satırla ayırın.'], about_values: ['Öne çıkanlar', 'textarea', 'Her satıra bir madde.'],
  }],
  social: ['Sosyal Medya', 'instagram', {
    instagram: ['Instagram', 'url'], facebook: ['Facebook', 'url'], linkedin: ['LinkedIn', 'url'], youtube: ['YouTube', 'url'],
  }],
};

/* ---------- Utilities ---------- */
export const icon = (name, cls = 'icon') => `<svg class="${cls}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[name] || ICONS.wrench}</svg>`;
export const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
export const $ = (s, r = document) => r.querySelector(s);
export const $$ = (s, r = document) => [...r.querySelectorAll(s)];

export function slugify(t) {
  const map = { ç: 'c', Ç: 'c', ğ: 'g', Ğ: 'g', ı: 'i', I: 'i', İ: 'i', ö: 'o', Ö: 'o', ş: 's', Ş: 's', ü: 'u', Ü: 'u' };
  const s = String(t || '').replace(/[çÇğĞıIİöÖşŞüÜ]/g, (c) => map[c]).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  return s || 'icerik';
}

const MONTHS = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
export function trDate(v, withTime = false) {
  if (!v) return '';
  const d = new Date(v);
  if (isNaN(d)) return '';
  const s = `${d.getDate()} ${MONTHS[d.getMonth()]} ${d.getFullYear()}`;
  return withTime ? `${s}, ${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}` : s;
}
export const toLocalInput = (v) => {
  if (!v) return '';
  const d = new Date(v);
  const p = (n) => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${p(d.getMonth() + 1)}-${p(d.getDate())}T${p(d.getHours())}:${p(d.getMinutes())}`;
};
export const excerpt = (html, n = 120) => {
  const t = String(html || '').replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
  return t.length > n ? t.slice(0, n - 1).trim() + '…' : t;
};
export const telHref = (p) => { let d = String(p || '').replace(/\D/g, ''); if (d.startsWith('0')) d = '90' + d.slice(1); return 'tel:+' + d; };
export const waHref = (p, text = '') => { let d = String(p || '').replace(/\D/g, ''); if (d.startsWith('0')) d = '90' + d.slice(1); return 'https://wa.me/' + d + (text ? '?text=' + encodeURIComponent(text) : ''); };
export const status = (s) => { const [l, c] = QUOTE_STATUSES[s] || [s, 'slate']; return `<span class="status status--${c}">${esc(l)}</span>`; };

/** Throws a readable Turkish error for failed Supabase calls. */
export function check({ data, error }) {
  if (error) {
    const msg = error.message || String(error);
    if (/row-level security|permission denied/i.test(msg)) throw new Error('Bu işlem için yetkiniz yok. Yönetici hesabıyla giriş yaptığınızdan emin olun.');
    if (/duplicate key|unique/i.test(msg)) throw new Error('Bu adres (slug) zaten kullanılıyor; farklı bir adres yazın.');
    throw new Error(msg);
  }
  return data;
}

export async function brands() {
  const rows = check(await sb.from('settings').select('*').eq('key', 'brands'));
  return String(rows[0]?.value || '').split(/\r?\n/).map((l) => l.trim()).filter(Boolean).map((l) => {
    const [name, desc = ''] = l.split('|').map((x) => x.trim());
    return { name, desc, slug: slugify(name) };
  });
}

/* ---------- Images → WebP → Supabase Storage ---------- */
export async function uploadImage(file, folder, maxWidth = 1600) {
  if (!file) return '';
  if (!/^image\/(jpeg|png|webp|gif)$/.test(file.type)) throw new Error('Desteklenen formatlar: JPG, PNG, WEBP, GIF.');
  if (file.size > 12 * 1024 * 1024) throw new Error('Görsel en fazla 12 MB olabilir.');
  const bmp = await createImageBitmap(file);
  const scale = Math.min(1, maxWidth / bmp.width);
  const canvas = document.createElement('canvas');
  canvas.width = Math.round(bmp.width * scale);
  canvas.height = Math.round(bmp.height * scale);
  canvas.getContext('2d').drawImage(bmp, 0, 0, canvas.width, canvas.height);
  const blob = await new Promise((r) => canvas.toBlob(r, 'image/webp', 0.85));
  const path = `${folder}/${new Date().toISOString().slice(0, 10)}-${Math.random().toString(36).slice(2, 10)}.webp`;
  // ArrayBuffer (not Blob) → sent as a plain body with the right content type
  check(await sb.storage.from('media').upload(path, await blob.arrayBuffer(), { contentType: 'image/webp', upsert: false }));
  return sb.storage.from('media').getPublicUrl(path).data.publicUrl;
}

/* ---------- Rich text editor (lazy) ---------- */
let quillPromise = null;
export async function makeEditor(el, html) {
  quillPromise ||= import('quill').then((m) => m.default);
  const Quill = await quillPromise;
  el.innerHTML = html || '';
  const q = new Quill(el, {
    theme: 'snow',
    placeholder: 'İçeriği yazmaya başlayın…',
    modules: {
      toolbar: {
        container: [[{ header: [2, 3, false] }], ['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['blockquote', 'link', 'image'], ['clean']],
        handlers: {
          image() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = async () => {
              try {
                const url = await uploadImage(input.files[0], 'content', 1400);
                const range = q.getSelection(true);
                q.insertEmbed(range.index, 'image', url, 'user');
              } catch (e) { toast(e.message, 'error'); }
            };
            input.click();
          },
        },
      },
    },
  });
  return { html: () => (q.root.innerHTML === '<p><br></p>' ? '' : q.root.innerHTML) };
}

/* ---------- Toasts ---------- */
export function toast(msg, type = 'success') {
  const host = $('#toasts');
  if (!host) return alert(msg);
  const t = document.createElement('div');
  t.className = `toast toast--${type}`;
  t.setAttribute('role', type === 'error' ? 'alert' : 'status');
  t.innerHTML = `${icon(type === 'success' ? 'check-circle' : 'help')}<span>${esc(msg)}</span><button type="button" aria-label="Kapat">${icon('x')}</button>`;
  t.querySelector('button').onclick = () => t.remove();
  host.prepend(t);
  if (type === 'success') setTimeout(() => t.remove(), 4500);
}
