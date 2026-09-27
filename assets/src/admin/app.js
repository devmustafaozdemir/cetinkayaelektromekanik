import { sb, check, esc, icon, $, $$, toast, SITE_URL } from './lib.js';
import * as V from './views.js';

const app = document.getElementById('app');

/* ---------- Menu & routes ---------- */
const MENU = [
  ['dashboard', 'Genel Bakış', 'home', '#/'],
  ['quotes', 'Teklif Talepleri', 'calculator', '#/teklifler'],
  ['messages', 'Mesajlar', 'inbox', '#/mesajlar'],
  [null, 'Katalog'],
  ['products', 'Ürünler', 'package', '#/urunler'],
  ['pcategories', 'Ürün Kategorileri', 'grid', '#/urun-kategorileri'],
  [null, 'İçerik'],
  ['posts', 'Blog Yazıları', 'file-text', '#/blog'],
  ['categories', 'Blog Kategorileri', 'tag', '#/blog-kategorileri'],
  ['services', 'Hizmetler', 'layers', '#/hizmetler'],
  ['faqs', 'SSS', 'help', '#/sss'],
  [null, 'Sistem'],
  ['settings', 'Site Ayarları', 'settings', '#/ayarlar'],
  ['account', 'Hesabım', 'users', '#/hesabim'],
];
const ROUTES = [
  [/^\/$/, V.dashboard, 'dashboard'],
  [/^\/teklifler$/, V.quotes, 'quotes'],
  [/^\/teklifler\/(\d+|yeni)$/, V.quoteEdit, 'quotes'],
  [/^\/mesajlar$/, V.messages, 'messages'],
  [/^\/mesajlar\/(\d+)$/, V.message, 'messages'],
  [/^\/urunler$/, V.products, 'products'],
  [/^\/urunler\/(\d+|yeni)$/, V.productEdit, 'products'],
  [/^\/urun-kategorileri$/, V.productCategories, 'pcategories'],
  [/^\/blog$/, V.posts, 'posts'],
  [/^\/blog\/(\d+|yeni)$/, V.postEdit, 'posts'],
  [/^\/blog-kategorileri$/, V.blogCategories, 'categories'],
  [/^\/hizmetler$/, V.services, 'services'],
  [/^\/hizmetler\/(\d+|yeni)$/, V.serviceEdit, 'services'],
  [/^\/sss$/, V.faqs, 'faqs'],
  [/^\/ayarlar(?:\/([a-z]+))?$/, V.settings, 'settings'],
  [/^\/hesabim$/, account, 'account'],
];

let user = null;
let shellReady = false;
let renderId = 0;

/* ---------- Auth screens ---------- */
const AUTH_SIDE = `
  <div class="auth__side">
    <div class="auth__brand"><span class="logo-mark">${icon('panels')}</span><div><strong>Çetinkaya</strong><small>Yönetim Paneli</small></div></div>
    <div>
      <h1>Ürünlerinizi, tekliflerinizi ve site içeriğinizi tek yerden yönetin.</h1>
      <ul>
        <li>${icon('calculator')} Teklif taleplerini takip edin, satışa dönüştürün</li>
        <li>${icon('package')} Ürün kataloğunu güncel tutun</li>
        <li>${icon('file-text')} Blog yazıları yayınlayın</li>
      </ul>
    </div>
    <small>© ${new Date().getFullYear()} Çetinkaya Elektromekanik</small>
  </div>`;

function authScreen(mode, message = '') {
  shellReady = false;
  document.body.className = 'auth-body';
  document.title = 'Giriş | Yönetim Paneli';
  const forms = {
    login: `
      <h2>Tekrar hoş geldiniz</h2>
      <p class="muted">Devam etmek için giriş yapın.</p>
      <div data-alert></div>
      <label class="field"><span>E-posta</span><input type="email" name="email" required autofocus autocomplete="username"></label>
      <label class="field"><span>Şifre</span><input type="password" name="password" required autocomplete="current-password"></label>
      <button class="btn btn--primary btn--block">Giriş Yap</button>
      <button type="button" class="auth__link" data-mode="forgot">Şifremi unuttum</button>`,
    forgot: `
      <h2>Şifre sıfırlama</h2>
      <p class="muted">E-posta adresinizi yazın; şifre yenileme bağlantısı gönderelim.</p>
      <div data-alert></div>
      <label class="field"><span>E-posta</span><input type="email" name="email" required autofocus autocomplete="username"></label>
      <button class="btn btn--primary btn--block">Bağlantı Gönder</button>
      <button type="button" class="auth__link" data-mode="login">Girişe dön</button>`,
    recovery: `
      <h2>Yeni şifre belirleyin</h2>
      <p class="muted">En az 8 karakterlik yeni şifrenizi yazın.</p>
      <div data-alert></div>
      <label class="field"><span>Yeni şifre</span><input type="password" name="password" required minlength="8" autofocus autocomplete="new-password"></label>
      <label class="field"><span>Yeni şifre (tekrar)</span><input type="password" name="password2" required minlength="8" autocomplete="new-password"></label>
      <button class="btn btn--primary btn--block">Şifreyi Kaydet</button>`,
    denied: `
      <h2>Yetkiniz yok</h2>
      <p class="muted">Bu hesap yönetici olarak tanımlı değil. Yönetici hesabıyla giriş yapın.</p>
      <button type="button" class="btn btn--primary btn--block" data-signout>Farklı hesapla giriş yap</button>`,
    config: `
      <h2>Kurulum tamamlanmamış</h2>
      <p class="muted">Supabase bağlantı bilgileri bu sayfaya eklenmemiş. Siteyi GitHub Actions ile yeniden derleyin (README › Supabase kurulumu).</p>`,
  };
  app.innerHTML = `<div class="auth">${AUTH_SIDE}<div class="auth__main">
    <form class="auth__form" autocomplete="on" novalidate>${forms[mode]}<a href="${esc(SITE_URL)}" class="auth__back">${icon('arrow-left')} Siteye dön</a></form>
  </div></div>`;
  const form = $('form', app);
  const alertBox = $('[data-alert]', form);
  const say = (msg, type = 'error') => { if (alertBox) alertBox.innerHTML = msg ? `<div class="alert alert--${type}">${esc(msg)}</div>` : ''; };
  if (message) say(message, mode === 'forgot' ? 'success' : 'error');
  $$('[data-mode]', form).forEach((b) => (b.onclick = () => authScreen(b.dataset.mode)));
  $('[data-signout]', form)?.addEventListener('click', async () => { await sb.auth.signOut(); authScreen('login'); });
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!form.reportValidity()) return;
    const btn = $('.btn--primary', form);
    const label = btn.textContent;
    btn.disabled = true; btn.textContent = 'Lütfen bekleyin…';
    const f = form.elements;
    try {
      if (mode === 'login') {
        const { error } = await sb.auth.signInWithPassword({ email: f.email.value.trim(), password: f.password.value });
        if (error) throw new Error(/invalid/i.test(error.message) ? 'E-posta veya şifre hatalı.' : error.message);
        await boot();
      } else if (mode === 'forgot') {
        const { error } = await sb.auth.resetPasswordForEmail(f.email.value.trim(), { redirectTo: location.href.split('#')[0] });
        if (error) throw error;
        say('Bağlantı gönderildi. E-postanızı kontrol edin (spam klasörüne de bakın).', 'success');
      } else if (mode === 'recovery') {
        if (f.password.value !== f.password2.value) throw new Error('Şifreler eşleşmiyor.');
        const { error } = await sb.auth.updateUser({ password: f.password.value });
        if (error) throw error;
        history.replaceState(null, '', location.pathname + '#/');
        await boot();
        toast('Şifreniz güncellendi.');
      }
    } catch (err) {
      say(err.message || String(err));
    } finally {
      if (document.body.contains(btn)) { btn.disabled = false; btn.textContent = label; }
    }
  });
}

/* ---------- Shell ---------- */
function shell() {
  document.body.className = '';
  const initial = (user?.email || '?').charAt(0).toLocaleUpperCase('tr-TR');
  app.innerHTML = `
    <div class="app">
      <aside class="sidebar" id="sidebar">
        <a href="#/" class="sidebar__brand"><span class="logo-mark">${icon('panels')}</span><div><strong>Çetinkaya</strong><small>Yönetim Paneli</small></div></a>
        <nav class="sidebar__nav">
          ${MENU.map(([key, label, ic, href]) => (key === null
            ? `<div class="sidebar__label">${label}</div>`
            : `<a href="${href}" data-nav="${key}">${icon(ic)}<span>${label}</span><em class="count" data-count="${key}" hidden></em></a>`)).join('')}
        </nav>
        <div class="sidebar__foot">
          <button type="button" class="sidebar__site" data-publish>${icon('zap')} Siteyi şimdi yayınla</button>
          <a href="${esc(SITE_URL)}" target="_blank" rel="noopener" class="sidebar__site">${icon('external')} Siteyi görüntüle</a>
        </div>
      </aside>
      <div class="sidebar-backdrop" data-sidebar-close></div>
      <div class="main">
        <header class="topnav">
          <button class="icon-btn menu-btn" type="button" data-sidebar-open aria-label="Menü">${icon('menu')}</button>
          <h1 class="topnav__title" data-title></h1>
          <div class="topnav__right">
            <a href="#/teklifler/yeni" class="btn btn--primary btn--sm hide-sm">${icon('plus')} Teklif Kaydı</a>
            <div class="user-menu">
              <button type="button" class="user-menu__btn" data-dropdown><span class="avatar">${esc(initial)}</span><span class="hide-sm">${esc(user?.email || '')}</span>${icon('chevron-down')}</button>
              <div class="dropdown">
                <a href="#/hesabim">${icon('users')} Hesabım</a>
                <button type="button" data-signout>${icon('logout')} Çıkış Yap</button>
              </div>
            </div>
          </div>
        </header>
        <div class="content">
          <div id="toasts" class="toasts" aria-live="polite"></div>
          <div id="view"></div>
        </div>
      </div>
    </div>`;
  const sidebar = $('#sidebar');
  const backdrop = $('.sidebar-backdrop');
  const setSidebar = (open) => { sidebar.classList.toggle('is-open', open); backdrop.classList.toggle('is-open', open); };
  $('[data-sidebar-open]').onclick = () => setSidebar(true);
  backdrop.onclick = () => setSidebar(false);
  $$('.sidebar__nav a').forEach((a) => a.addEventListener('click', () => setSidebar(false)));
  const dd = $('[data-dropdown]');
  dd.onclick = (e) => { e.stopPropagation(); dd.nextElementSibling.classList.toggle('is-open'); };
  document.addEventListener('click', () => $('.dropdown.is-open')?.classList.remove('is-open'));
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { setSidebar(false); $('.dropdown.is-open')?.classList.remove('is-open'); } });
  $('[data-signout]').onclick = async () => { await sb.auth.signOut(); location.hash = '#/'; authScreen('login'); };
  $('[data-publish]').onclick = publishNow;
  shellReady = true;
}

async function publishNow(e) {
  const btn = e.currentTarget;
  btn.disabled = true;
  try {
    const { data, error } = await sb.rpc('request_publish');
    const missing = error && /could not find|does not exist|PGRST202|404/i.test(`${error.code} ${error.message}`);
    if (error && !missing) throw error;
    if (missing || data === false) toast('Anında yayın henüz ayarlanmamış (README › Anında yayın). Değişiklikler yine de en geç 1 saat içinde siteye yansır.', 'error');
    else toast('Yayın başlatıldı. Site 1-2 dakika içinde güncellenir.');
  } catch (err) { toast(err.message || String(err), 'error'); } finally { btn.disabled = false; }
}

async function refreshCounts() {
  const count = async (q) => { const { count: c } = await q; return c || 0; };
  try {
    const [quotes, messages] = await Promise.all([
      count(sb.from('quotes').select('id', { count: 'exact', head: true }).eq('status', 'new')),
      count(sb.from('messages').select('id', { count: 'exact', head: true }).eq('is_read', 0)),
    ]);
    for (const [key, n] of [['quotes', quotes], ['messages', messages]]) {
      const el = $(`[data-count="${key}"]`);
      if (el) { el.textContent = n; el.hidden = !n; }
    }
  } catch { /* counts are cosmetic */ }
}

/* ---------- Router ---------- */
async function render() {
  if (!user) return;
  if (!shellReady) shell();
  const raw = location.hash.replace(/^#/, '') || '/';
  const [path, qs = ''] = raw.split('?');
  const query = new URLSearchParams(qs);
  let match = null;
  let handler = notFound;
  let key = '';
  for (const [re, fn, k] of ROUTES) {
    match = path.match(re);
    if (match) { handler = fn; key = k; break; }
  }
  $$('[data-nav]').forEach((a) => a.classList.toggle('is-active', a.dataset.nav === key));
  const view = $('#view');
  const id = ++renderId;
  view.setAttribute('aria-busy', 'true');
  view.classList.add('is-loading');
  try {
    const page = await handler({ id: match?.[1], query });
    if (id !== renderId) return; // a newer navigation won
    document.title = `${page.title} | Yönetim Paneli`;
    $('[data-title]').textContent = page.title;
    view.innerHTML = page.html;
    page.mount?.(view);
  } catch (err) {
    if (id !== renderId) return;
    if (/JWT|expired|not logged/i.test(err.message)) { user = null; authScreen('login', 'Oturumunuzun süresi doldu, tekrar giriş yapın.'); return; }
    $('[data-title]').textContent = 'Hata';
    view.innerHTML = `<div class="card empty">${icon('help')}<h3>Sayfa yüklenemedi</h3><p class="muted">${esc(err.message || String(err))}</p><button class="btn" type="button" onclick="location.reload()">Tekrar dene</button></div>`;
  } finally {
    if (id === renderId) { view.removeAttribute('aria-busy'); view.classList.remove('is-loading'); }
  }
  refreshCounts();
}

async function notFound() {
  return { title: 'Sayfa bulunamadı', html: `<div class="card empty">${icon('help')}<h3>Aradığınız sayfa bulunamadı</h3><a href="#/" class="btn btn--primary">Genel Bakış</a></div>` };
}

async function account() {
  return {
    title: 'Hesabım',
    html: `<div class="grid-2">
      <form class="card form-stack" data-password novalidate>
        <h3>Şifre değiştir</h3>
        <p class="muted small">Giriş e-postanız: <strong>${esc(user?.email || '')}</strong></p>
        <label class="field"><span>Yeni şifre</span><input type="password" name="password" required minlength="8" autocomplete="new-password"></label>
        <label class="field"><span>Yeni şifre (tekrar)</span><input type="password" name="password2" required minlength="8" autocomplete="new-password"></label>
        <div><button class="btn btn--primary">${icon('check')} Şifreyi Güncelle</button></div>
      </form>
      <section class="card form-stack">
        <h3>Yeni yönetici eklemek</h3>
        <p class="muted small">Supabase paneli › Authentication › Users › “Add user” ile kullanıcıyı oluşturun, ardından SQL Editor'de şunu çalıştırın:</p>
        <pre class="code">insert into public.admins (user_id)
select id from auth.users where email = 'ornek@firma.com';</pre>
      </section>
    </div>`,
    mount(root) {
      const form = $('[data-password]', root);
      form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!form.reportValidity()) return;
        const f = form.elements;
        if (f.password.value !== f.password2.value) return toast('Şifreler eşleşmiyor.', 'error');
        const { error } = await sb.auth.updateUser({ password: f.password.value });
        if (error) return toast(error.message, 'error');
        form.reset();
        toast('Şifreniz güncellendi.');
      });
    },
  };
}

/* ---------- Boot ---------- */
async function boot() {
  const { data } = await sb.auth.getSession();
  user = data.session?.user || null;
  if (!user) return authScreen('login');
  if (window.__RECOVERY) { window.__RECOVERY = false; user = null; history.replaceState(null, '', location.pathname); return authScreen('recovery'); }
  let admin = false;
  try { admin = check(await sb.rpc('is_admin')) === true; } catch { admin = false; }
  if (!admin) { user = null; return authScreen('denied'); }
  shellReady = false;
  await render();
}

if (!window.APP_CONFIG?.supabaseUrl || /\{\{/.test(window.APP_CONFIG.supabaseUrl)) {
  authScreen('config');
} else {
  sb.auth.onAuthStateChange((event) => {
    if (event === 'SIGNED_OUT' && user) { user = null; setTimeout(() => authScreen('login'), 0); }
  });
  window.addEventListener('hashchange', render);
  boot().catch((err) => authScreen('login', err.message));
}
