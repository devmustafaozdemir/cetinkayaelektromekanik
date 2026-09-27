import 'server-only';
import { cache } from 'react';
import { createClient, type SupabaseClient } from '@supabase/supabase-js';
import type { Row, Settings } from './site';

/**
 * Public content is read with the anon key; Supabase row level security only
 * returns published posts, active products etc. Every request reads fresh data,
 * so changes made in the admin panel are visible immediately.
 */
let client: SupabaseClient | null = null;
function sb(): SupabaseClient {
  const url = process.env.SUPABASE_URL;
  const key = process.env.SUPABASE_ANON_KEY;
  if (!url || !key) throw new Error('SUPABASE_URL ve SUPABASE_ANON_KEY ortam değişkenleri tanımlı değil (.env.local).');
  client ??= createClient(url, key, { auth: { persistSession: false, autoRefreshToken: false } });
  return client;
}

async function all(table: string, order: [string, boolean][] = [['sort', true], ['id', true]]): Promise<Row[]> {
  let q = sb().from(table).select('*');
  for (const [col, asc] of order) q = q.order(col, { ascending: asc });
  const { data, error } = await q;
  if (error) {
    // tables added in later schema versions: render without them until schema.sql is re-run
    if (['refs', 'partners'].includes(table) && /PGRST205|schema cache|does not exist/i.test(`${error.code} ${error.message}`)) return [];
    throw new Error(`Supabase (${table}): ${error.message}`);
  }
  return data ?? [];
}

export const getSettings = cache(async (): Promise<Settings> => {
  const { data, error } = await sb().from('settings').select('key,value');
  if (error) throw new Error(`Supabase (settings): ${error.message}`);
  return Object.fromEntries((data ?? []).map((r) => [r.key, r.value ?? '']));
});

export const getCategories = cache(async (): Promise<Row[]> => {
  const [cats, products] = await Promise.all([all('product_categories'), getProducts()]);
  return cats.map((c) => ({ ...c, cnt: products.filter((p) => p.category_id === c.id).length }));
});

/** Active products with their category name/slug/art (like the PHP products_sql join). */
export const getProducts = cache(async (): Promise<Row[]> => {
  const [products, cats] = await Promise.all([all('products'), all('product_categories')]);
  const byId = new Map(cats.map((c) => [c.id, c]));
  return products
    .map((p): Row => {
      const c = byId.get(p.category_id);
      return { ...p, category: c?.name ?? null, category_slug: c?.slug ?? null, art: c?.art ?? null, _csort: c?.sort ?? 999 };
    })
    .sort((a, b) => a._csort - b._csort || a.sort - b.sort || a.id - b.id);
});

/** Published posts (RLS), newest first, with category name/slug. */
export const getPosts = cache(async (): Promise<Row[]> => {
  const [posts, cats] = await Promise.all([all('posts', [['published_at', false], ['id', false]]), getBlogCategories()]);
  const byId = new Map(cats.map((c) => [c.id, c]));
  return posts.map((p) => ({ ...p, category: byId.get(p.category_id)?.name ?? null, category_slug: byId.get(p.category_id)?.slug ?? null }));
});

export const getBlogCategories = cache(() => all('categories', [['name', true]]));
export const getServices = cache(async () => (await all('services')).filter((s) => s.active));
export const getFaqs = cache(async () => (await all('faqs')).filter((s) => s.active));
export const getRefs = cache(async () => (await all('refs')).filter((s) => s.active));
export const getPartners = cache(async () => (await all('partners')).filter((s) => s.active));
