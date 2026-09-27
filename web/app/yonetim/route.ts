// The admin panel is a client-side app (assets/src/admin, bundled to /assets/js/admin)
// that talks to Supabase directly; this route serves its HTML shell with the config.
export const dynamic = 'force-dynamic';

const esc = (v: string) => v.replace(/[\\'<>]/g, (c) => ({ '\\': '\\\\', "'": "\\'", '<': '\\u003c', '>': '\\u003e' })[c] as string);

export function GET() {
  const v = process.env.ASSET_VERSION || '1';
  const cfg = `window.APP_CONFIG = { supabaseUrl: '${esc(process.env.SUPABASE_URL ?? '')}', supabaseKey: '${esc(process.env.SUPABASE_ANON_KEY ?? '')}', siteUrl: '/', live: true };`;
  const html = `<!doctype html>
<html lang="tr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Yönetim Paneli</title>
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/admin.css?v=${v}">
<link rel="stylesheet" href="/assets/js/admin/quill.snow.css?v=${v}">
<script>
  ${cfg}
  window.__RECOVERY = /type=recovery/.test(location.hash);
</script>
</head>
<body class="auth-body">
<div id="app"><div class="boot" role="status"><span class="boot__spinner"></span> Yükleniyor…</div></div>
<noscript><p style="padding:2rem;font-family:sans-serif">Yönetim paneli için JavaScript gereklidir.</p></noscript>
<script type="module" src="/assets/js/admin/app.js?v=${v}"></script>
</body>
</html>`;
  return new Response(html, { headers: { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store', 'X-Robots-Tag': 'noindex' } });
}
