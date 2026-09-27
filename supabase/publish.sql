-- =====================================================================
-- Anında yayın (isteğe bağlı)
-- Panelde ürün, blog, ayar vb. kaydedildiğinde GitHub Actions'taki
-- "Siteyi yayınla" iş akışını hemen başlatır. Kurulmazsa site yine de
-- saatte bir kendiliğinden güncellenir.
--
-- 1) GitHub › Settings › Developer settings › Fine-grained tokens › Generate
--    Repository access: yalnızca bu depo · Permissions › Actions: Read and write
-- 2) Supabase › SQL Editor'de (değerleri kendinize göre düzenleyip) çalıştırın:
--      select vault.create_secret('github_pat_XXXX', 'github_token');
--      select vault.create_secret('devmustafaozdemir/cetinkayaelektromekanik', 'github_repo');
--      select vault.create_secret('claude/adoring-hopper-jfy8yo', 'github_ref');
-- 3) Bu dosyanın tamamını SQL Editor'de çalıştırın.
-- Token değiştirmek için: select vault.update_secret(id, 'yeni_token') from vault.secrets where name = 'github_token';
-- =====================================================================

create extension if not exists pg_net;

-- Internal: sends the workflow_dispatch request (queued by pg_net, sent after commit)
create or replace function public._dispatch_publish()
returns boolean
language plpgsql
security definer
set search_path = public
as $$
declare
  v_token text;
  v_repo  text;
  v_ref   text;
begin
  select decrypted_secret into v_token from vault.decrypted_secrets where name = 'github_token';
  select decrypted_secret into v_repo  from vault.decrypted_secrets where name = 'github_repo';
  select decrypted_secret into v_ref   from vault.decrypted_secrets where name = 'github_ref';
  if coalesce(v_token, '') = '' or coalesce(v_repo, '') = '' then
    return false;
  end if;
  perform net.http_post(
    url     := 'https://api.github.com/repos/' || v_repo || '/actions/workflows/pages.yml/dispatches',
    body    := jsonb_build_object('ref', coalesce(nullif(v_ref, ''), 'main')),
    params  := '{}'::jsonb,
    headers := jsonb_build_object(
      'Authorization', 'Bearer ' || v_token,
      'Accept', 'application/vnd.github+json',
      'X-GitHub-Api-Version', '2022-11-28',
      'User-Agent', 'cetinkaya-supabase',
      'Content-Type', 'application/json'
    ),
    timeout_milliseconds := 10000
  );
  return true;
end;
$$;
revoke all on function public._dispatch_publish() from public, anon, authenticated;

-- "Siteyi şimdi yayınla" button in the admin panel
create or replace function public.request_publish()
returns boolean
language plpgsql
security definer
set search_path = public
as $$
begin
  if not public.is_admin() then
    raise exception 'Bu işlem için yetkiniz yok.';
  end if;
  return public._dispatch_publish();
end;
$$;
revoke all on function public.request_publish() from public, anon;
grant execute on function public.request_publish() to authenticated;

-- Automatic: one request per saved change (GitHub coalesces bursts via the workflow's concurrency group)
create or replace function public.publish_on_change()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  perform public._dispatch_publish();
  return null;
end;
$$;
revoke all on function public.publish_on_change() from public, anon, authenticated;

do $$
declare t text;
begin
  foreach t in array array['settings', 'categories', 'product_categories', 'products', 'posts', 'services', 'faqs', 'refs', 'partners'] loop
    execute format('drop trigger if exists publish_on_change on public.%I', t);
    execute format('create trigger publish_on_change after insert or update or delete on public.%I for each statement execute function public.publish_on_change()', t);
  end loop;
end $$;
