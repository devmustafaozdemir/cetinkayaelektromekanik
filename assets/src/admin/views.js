import {
  sb, check, esc, icon, $, $$, slugify, trDate, toLocalInput, excerpt, telHref, waHref, status, toast, brands, uploadImage, makeEditor,
  QUOTE_STATUSES, QUOTE_TYPES, MODEL_TYPES, ARTS, SERVICE_ICONS, SETTINGS_GROUPS, SITE_URL,
} from './lib.js';

const go = (hash) => { location.hash = hash; };
const val = (form, name) => (form.elements[name]?.value ?? '').trim();
const checked = (form, name) => (form.elements[name]?.checked ? 1 : 0);
const opt = (value, label, current) => `<option value="${esc(value)}"${String(value) === String(current ?? '') ? ' selected' : ''}>${esc(label)}</option>`;
const field = (name, label, value = '', type = 'text', extra = '') =>
  `<label class="field"><span>${label}</span><input type="${type}" name="${name}" value="${esc(value)}" ${extra}></label>`;
const area = (name, label, value = '', rows = 3, extra = '') =>
  `<label class="field"><span>${label}</span><textarea name="${name}" rows="${rows}" ${extra}>${esc(value)}</textarea></label>`;
const sw = (name, label, on) =>
  `<label class="switch"><input type="checkbox" name="${name}" value="1"${on ? ' checked' : ''}><span class="switch__ui"></span><span>${label}</span></label>`;
const empty = (ic, title, text = '', action = '') => `<div class="empty">${icon(ic)}<h3>${title}</h3>${text ? `<p class="muted">${text}</p>` : ''}${action}</div>`;
// live: the Next.js site reads Supabase on every request, so saves show up immediately
const publishNote = '<p class="muted small publish-note">' + icon('external') + (window.APP_CONFIG?.live ? ' Kaydettiğiniz değişiklikler sitede hemen görünür.' : ' Kaydettiğiniz değişiklikler sitede birkaç dakika içinde görünür.') + '</p>';

function slugWire(root) {
  const src = $('[data-slug-source]', root);
  const tgt = $('[data-slug-target]', root);
  if (!src || !tgt) return;
  let touched = tgt.value !== '';
  tgt.addEventListener('input', () => { touched = tgt.value !== ''; });
  src.addEventListener('input', () => { if (!touched) tgt.value = slugify(src.value); });
}
function imageWire(root) {
  $$('[data-dropzone]', root).forEach((dz) => {
    const input = $('input[type=file]', dz);
    const img = $('[data-preview]', dz);
    const hint = $('.dropzone__hint', dz);
    input.addEventListener('change', () => {
      const f = input.files[0];
      if (!f) return;
      img.src = URL.createObjectURL(f); img.hidden = false; if (hint) hint.hidden = true;
    });
  });
}
const dropzone = (name, url, hint = 'Görsel seçin veya sürükleyin') => `
  <label class="dropzone" data-dropzone>
    <input type="file" name="${name}" accept="image/jpeg,image/png,image/webp,image/gif">
    <img src="${esc(url || '')}" alt="" data-preview${url ? '' : ' hidden'}>
    <span class="dropzone__hint"${url ? ' hidden' : ''}>${icon('plus')} ${hint}<small>JPG, PNG, WEBP · otomatik küçültülür</small></span>
  </label>`;
async function withBusy(btn, fn) {
  const label = btn?.innerHTML;
  if (btn) { btn.disabled = true; btn.textContent = 'Kaydediliyor…'; }
  try { await fn(); } catch (e) { toast(e.message, 'error'); } finally { if (btn && document.body.contains(btn)) { btn.disabled = false; btn.innerHTML = label; } }
}
function confirmDelete(what) { return confirm(`${what} silinsin mi? Bu işlem geri alınamaz.`); }

/* =================== Dashboard =================== */
export async function dashboard() {
  const [quotes, messages, products, posts] = await Promise.all([
    sb.from('quotes').select('*').order('id', { ascending: false }).limit(500).then(check),
    sb.from('messages').select('*').order('id', { ascending: false }).limit(200).then(check),
    sb.from('products').select('id,active').then(check),
    sb.from('posts').select('id,title,status,views').then(check),
  ]);
  const monthStart = new Date(); monthStart.setDate(1); monthStart.setHours(0, 0, 0, 0);
  const open = quotes.filter((q) => ['new', 'contacted', 'quoted'].includes(q.status));
  const fresh = quotes.filter((q) => q.status === 'new');
  const won = quotes.filter((q) => q.status === 'won' && new Date(q.updated_at) >= monthStart);
  const closed = quotes.filter((q) => ['won', 'lost'].includes(q.status));
  const rate = closed.length ? Math.round(quotes.filter((q) => q.status === 'won').length / closed.length * 100) : null;
  const unread = messages.filter((m) => !m.is_read).length;
  const days = [];
  for (let i = 13; i >= 0; i--) { const d = new Date(); d.setHours(0, 0, 0, 0); d.setDate(d.getDate() - i); days.push({ d, c: 0 }); }
  quotes.forEach((q) => { const t = new Date(q.created_at); t.setHours(0, 0, 0, 0); const day = days.find((x) => +x.d === +t); if (day) day.c++; });
  const max = Math.max(1, ...days.map((x) => x.c));
  const byStatus = {}; quotes.forEach((q) => { byStatus[q.status] = (byStatus[q.status] || 0) + 1; });
  const top = {}; quotes.forEach((q) => { const k = q.product_name || q.category || 'Belirtilmedi'; top[k] = (top[k] || 0) + 1; });
  const topList = Object.entries(top).sort((a, b) => b[1] - a[1]).slice(0, 5);
  return {
    title: 'Genel Bakış',
    html: `
      <div class="welcome">
        <div><h2>Merhaba 👋</h2><p class="muted">${trDate(new Date())} — satış ve teklif durumunuz.</p></div>
        <div class="welcome__actions">
          <a href="#/urunler/yeni" class="btn">${icon('package')} Yeni Ürün</a>
          <a href="#/teklifler/yeni" class="btn btn--primary">${icon('plus')} Teklif Kaydı</a>
        </div>
      </div>
      <div class="kpis">
        <a href="#/teklifler?durum=new" class="kpi"><span class="kpi__icon kpi__icon--amber">${icon('calculator')}</span><div><strong>${fresh.length}</strong><span>Yeni teklif talebi</span></div><small>${open.length} açık talep</small></a>
        <a href="#/teklifler?durum=won" class="kpi"><span class="kpi__icon kpi__icon--green">${icon('handshake')}</span><div><strong>${won.length}</strong><span>Bu ay satışa dönen</span></div><small>Dönüşüm oranı: ${rate === null ? '—' : '%' + rate}</small></a>
        <a href="#/mesajlar" class="kpi"><span class="kpi__icon kpi__icon--blue">${icon('inbox')}</span><div><strong>${unread}</strong><span>Okunmamış mesaj</span></div><small>İletişim formu</small></a>
        <a href="#/urunler" class="kpi"><span class="kpi__icon kpi__icon--violet">${icon('package')}</span><div><strong>${products.filter((p) => p.active).length}</strong><span>Yayındaki ürün</span></div><small>${posts.filter((p) => p.status === 'published').length} blog yazısı</small></a>
      </div>
      <div class="grid-2">
        <section class="card"><div class="card__head"><h3>Son 14 gün — teklif talepleri</h3></div>
          <div class="bars" role="img" aria-label="Son 14 gündeki günlük teklif talepleri">
            ${days.map((x) => `<div class="bars__col" title="${trDate(x.d)}: ${x.c} talep"><span class="bars__val">${x.c || ''}</span><span class="bars__bar" style="height:${Math.max(3, Math.round(x.c / max * 100))}%"></span><span class="bars__label">${x.d.getDate()}</span></div>`).join('')}
          </div></section>
        <section class="card"><div class="card__head"><h3>Satış hunisi</h3></div>
          ${quotes.length ? `<ul class="status-bars">${Object.entries(QUOTE_STATUSES).map(([k, [l, c]]) => `<li><a href="#/teklifler?durum=${k}"><span class="status status--${c}">${l}</span><span class="status-bars__track"><span class="status-bars__fill fill--${c}" style="width:${Math.round((byStatus[k] || 0) / quotes.length * 100)}%"></span></span><strong>${byStatus[k] || 0}</strong></a></li>`).join('')}</ul>` : '<p class="muted">Henüz teklif talebi yok.</p>'}
        </section>
      </div>
      <div class="grid-2">
        <section class="card"><div class="card__head"><h3>Son teklif talepleri</h3><a href="#/teklifler" class="link">Tümü →</a></div>
          ${quotes.length ? `<ul class="list">${quotes.slice(0, 6).map((q) => `<li><a href="#/teklifler/${q.id}"><div><strong>${esc(q.name)}${q.company ? ' · ' + esc(q.company) : ''}</strong><small>${esc(q.product_name || q.category || QUOTE_TYPES[q.type] || '')} · ${trDate(q.created_at)}</small></div>${status(q.status)}</a></li>`).join('')}</ul>` : '<p class="muted">Henüz teklif talebi yok.</p>'}
        </section>
        <section class="card"><div class="card__head"><h3>En çok teklif istenenler</h3></div>
          ${topList.length ? `<ul class="list list--compact">${topList.map(([n, c]) => `<li><a href="#/teklifler?q=${encodeURIComponent(n)}"><span>${esc(n)}</span><small class="muted">${c} talep</small></a></li>`).join('')}</ul>` : '<p class="muted">Veri yok.</p>'}
          <div class="card__head mt"><h3>Son mesajlar</h3><a href="#/mesajlar" class="link">Tümü →</a></div>
          ${messages.length ? `<ul class="list">${messages.slice(0, 4).map((m) => `<li><a href="#/mesajlar/${m.id}"><div><strong>${m.is_read ? '' : '<span class="dot"></span>'}${esc(m.name)}</strong><small>${esc(excerpt(m.subject || m.message, 60))}</small></div><small class="muted">${trDate(m.created_at)}</small></a></li>`).join('')}</ul>` : '<p class="muted">Henüz mesaj yok.</p>'}
        </section>
      </div>`,
  };
}

/* =================== Quotes =================== */
export async function quotes({ query }) {
  const rows = check(await sb.from('quotes').select('*').order('id', { ascending: false }).limit(1000));
  const filter = query.get('durum') || '';
  const q = (query.get('q') || '').toLocaleLowerCase('tr-TR');
  const counts = { '': rows.length, acik: rows.filter((r) => ['new', 'contacted', 'quoted'].includes(r.status)).length };
  Object.keys(QUOTE_STATUSES).forEach((k) => { counts[k] = rows.filter((r) => r.status === k).length; });
  let list = rows;
  if (filter === 'acik') list = list.filter((r) => ['new', 'contacted', 'quoted'].includes(r.status));
  else if (filter) list = list.filter((r) => r.status === filter);
  if (q) list = list.filter((r) => [r.code, r.name, r.company, r.phone, r.product_name, r.category, r.city].join(' ').toLocaleLowerCase('tr-TR').includes(q));
  const tab = (k, l) => `<a href="#/teklifler${k ? '?durum=' + k : ''}" class="${filter === k ? 'is-active' : ''}">${l} <em>${counts[k] || 0}</em></a>`;
  return {
    title: 'Teklif Talepleri',
    html: `
      <div class="page-head">
        <div class="tabs tabs--scroll">${tab('', 'Tümü')}${tab('acik', 'Açık')}${Object.entries(QUOTE_STATUSES).map(([k, [l]]) => tab(k, l)).join('')}</div>
        <div class="page-head__actions">
          <form class="search" data-search>${icon('search')}<input type="search" name="q" value="${esc(query.get('q') || '')}" placeholder="Ad, firma, telefon, ürün…"></form>
          <a href="#/teklifler/yeni" class="btn btn--primary">${icon('plus')} Yeni Kayıt</a>
        </div>
      </div>
      <div class="card card--flush">
        ${list.length ? `<table class="table"><thead><tr><th>Müşteri</th><th class="hide-sm">Talep</th><th>Durum</th><th class="hide-sm">Tarih</th><th></th></tr></thead><tbody>
          ${list.map((r) => `<tr class="${r.status === 'new' ? 'is-new' : ''}">
            <td><a href="#/teklifler/${r.id}" class="row-title"><span>${esc(r.name)}<small>${r.company ? esc(r.company) + ' · ' : ''}${esc(r.phone)}</small></span></a></td>
            <td class="hide-sm"><strong class="block">${esc(r.product_name || r.category || '—')}</strong><small class="muted"><span class="mono small">${esc(r.code)}</span> · ${esc(QUOTE_TYPES[r.type] || '')}${r.quantity ? ' · ' + esc(r.quantity) : ''}</small></td>
            <td>${status(r.status)}</td>
            <td class="hide-sm nowrap muted">${trDate(r.created_at, true)}</td>
            <td class="actions"><a href="${telHref(r.phone)}" class="icon-btn" title="Ara">${icon('phone')}</a><a href="${waHref(r.phone)}" target="_blank" rel="noopener" class="icon-btn" title="WhatsApp">${icon('whatsapp')}</a><a href="#/teklifler/${r.id}" class="icon-btn" title="Aç">${icon('edit')}</a></td>
          </tr>`).join('')}</tbody></table>`
        : empty('calculator', 'Teklif talebi bulunamadı', 'Web sitesinden gelen ve telefonla alınan talepler burada listelenir.', '<a href="#/teklifler/yeni" class="btn btn--primary">Yeni Kayıt</a>')}
      </div>`,
    mount(root) {
      $('[data-search]', root).addEventListener('submit', (e) => {
        e.preventDefault();
        const v = e.target.elements.q.value.trim();
        go(`#/teklifler?${new URLSearchParams({ ...(filter ? { durum: filter } : {}), ...(v ? { q: v } : {}) })}`);
      });
    },
  };
}

export async function quoteEdit({ id }) {
  const isNew = id === 'yeni';
  const [row, products, cats, log] = await Promise.all([
    isNew ? Promise.resolve(null) : sb.from('quotes').select('*').eq('id', id).then(check).then((r) => r[0]),
    sb.from('products').select('id,title').order('title').then(check),
    sb.from('product_categories').select('name').order('sort').then(check),
    isNew ? Promise.resolve([]) : sb.from('quote_log').select('*').eq('quote_id', id).order('id', { ascending: false }).then(check),
  ]);
  if (!isNew && !row) return { title: 'Bulunamadı', html: empty('help', 'Teklif bulunamadı', '', '<a href="#/teklifler" class="btn">Geri dön</a>') };
  const r = row || { type: 'product', status: 'new', name: '', company: '', phone: '', email: '', city: '', category: '', product_id: null, product_name: '', quantity: '', message: '', admin_note: '' };
  return {
    title: isNew ? 'Yeni Teklif Kaydı' : `Teklif Talebi · ${r.code}`,
    html: `
      <a href="#/teklifler" class="back-link">${icon('arrow-left')} Teklif talepleri</a>
      <form class="editor-layout" data-form>
        <div class="editor-main">
          ${isNew ? '' : `<div class="card request-summary">
            <div><small class="muted">Talep no · ${r.source === 'manual' ? 'Panelden eklendi' : 'Web sitesi'}</small>
              <div class="code-line"><strong class="mono">${esc(r.code)}</strong></div><small class="muted">${trDate(r.created_at, true)}</small></div>
            <div class="request-summary__actions">
              <a href="${telHref(r.phone)}" class="btn">${icon('phone')} Ara</a>
              <a href="${waHref(r.phone, `Merhaba ${r.name}, ${r.product_name || r.category || 'talebiniz'} için teklif talebiniz (${r.code}) hakkında yazıyoruz.`)}" target="_blank" rel="noopener" class="btn btn--wa">${icon('whatsapp')} WhatsApp</a>
              ${r.email ? `<a href="mailto:${esc(r.email)}?subject=${encodeURIComponent('Fiyat Teklifi - ' + r.code)}" class="btn">${icon('mail')} E-posta</a>` : ''}
            </div></div>`}
          <div class="card"><h3>Talep</h3>
            <div class="form-row">
              <label class="field"><span>Talep türü</span><select name="type">${Object.entries(QUOTE_TYPES).map(([k, l]) => opt(k, l, r.type)).join('')}</select></label>
              <label class="field"><span>Ürün grubu</span><input name="category" value="${esc(r.category)}" list="cat-list"></label>
            </div>
            <datalist id="cat-list">${cats.map((c) => `<option value="${esc(c.name)}">`).join('')}</datalist>
            <div class="form-row">
              <label class="field"><span>Ürün</span><select name="product_id"><option value="">— Belirtilmedi —</option>${products.map((p) => opt(p.id, p.title, r.product_id)).join('')}</select>
                ${!r.product_id && r.product_name ? `<small class="muted">Kayıtlı ürün: ${esc(r.product_name)}</small>` : ''}</label>
              ${field('quantity', 'Miktar / kapasite', r.quantity)}
            </div>
            ${area('message', 'Müşteri mesajı', r.message, 4)}
          </div>
          <div class="card"><h3>Müşteri</h3>
            <div class="form-row">${field('name', 'Ad Soyad *', r.name, 'text', 'required')}${field('company', 'Firma', r.company)}</div>
            <div class="form-row">${field('phone', 'Telefon *', r.phone, 'tel', 'required')}${field('email', 'E-posta', r.email, 'email')}</div>
            ${field('city', 'Şehir / İlçe', r.city)}
          </div>
        </div>
        <aside class="editor-side">
          <div class="card sticky"><h3>Satış durumu</h3>
            <div class="status-picker">${Object.entries(QUOTE_STATUSES).map(([k, [l, c]]) => `<label><input type="radio" name="status" value="${k}"${r.status === k ? ' checked' : ''}><span class="status status--${c}">${l}</span></label>`).join('')}</div>
            ${field('log_note', 'Geçmişe not ekle', '', 'text', 'maxlength="500" placeholder="Örn. Teklif e-posta ile gönderildi."')}
            ${area('admin_note', 'İç not <small class="muted">(sadece panelde)</small>', r.admin_note, 3)}
            <button class="btn btn--primary btn--block" data-save>${icon('check')} ${isNew ? 'Kaydı Oluştur' : 'Kaydet'}</button>
          </div>
          ${log.length ? `<div class="card"><h3>Geçmiş</h3><ul class="timeline">${log.map((l) => `<li><span class="timeline__dot dot--${(QUOTE_STATUSES[l.status] || [])[1] || 'slate'}"></span><div><strong>${esc((QUOTE_STATUSES[l.status] || [l.status])[0])}</strong>${l.note ? `<p>${esc(l.note)}</p>` : ''}<time>${trDate(l.created_at, true)}</time></div></li>`).join('')}</ul></div>` : ''}
          ${isNew ? '' : `<div class="card card--danger"><button type="button" class="btn btn--danger-ghost btn--block" data-delete>${icon('trash')} Kaydı sil</button></div>`}
        </aside>
      </form>`,
    mount(root) {
      const form = $('[data-form]', root);
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        withBusy($('[data-save]', form), async () => {
          const pid = val(form, 'product_id');
          const d = {
            type: val(form, 'type'), category: val(form, 'category'), quantity: val(form, 'quantity'), message: val(form, 'message'),
            name: val(form, 'name'), company: val(form, 'company'), phone: val(form, 'phone'), email: val(form, 'email'), city: val(form, 'city'),
            admin_note: val(form, 'admin_note'), status: form.querySelector('[name=status]:checked')?.value || 'new',
            product_id: pid ? Number(pid) : null,
          };
          if (d.product_id) d.product_name = products.find((p) => p.id === d.product_id)?.title || '';
          if (d.name.length < 2) throw new Error('Müşteri adı zorunludur.');
          if (d.phone.replace(/\D/g, '').length < 10) throw new Error('Geçerli bir telefon girin.');
          let qid = row?.id;
          if (isNew) {
            const ins = check(await sb.from('quotes').insert({ ...d, source: 'manual' }).select());
            qid = ins[0].id;
          } else {
            check(await sb.from('quotes').update(d).eq('id', qid).select());
          }
          const note = val(form, 'log_note');
          if (note) {
            // A status change is logged by a database trigger; attach the note to that entry instead of adding a second one
            const statusChanged = !isNew && row.status !== d.status;
            const last = statusChanged ? check(await sb.from('quote_log').select('*').eq('quote_id', qid).order('id', { ascending: false }).limit(1))[0] : null;
            if (last && last.status === d.status && !last.note) check(await sb.from('quote_log').update({ note }).eq('id', last.id).select());
            else check(await sb.from('quote_log').insert({ quote_id: qid, status: d.status, note }).select());
          }
          toast(isNew ? 'Teklif kaydı oluşturuldu.' : 'Teklif talebi güncellendi.');
          if (isNew) go(`#/teklifler/${qid}`); else window.dispatchEvent(new HashChangeEvent('hashchange'));
        });
      });
      $('[data-delete]', root)?.addEventListener('click', async () => {
        if (!confirmDelete('Bu teklif talebi')) return;
        try { check(await sb.from('quotes').delete().eq('id', row.id)); toast('Teklif talebi silindi.'); go('#/teklifler'); } catch (e) { toast(e.message, 'error'); }
      });
    },
  };
}

/* =================== Messages =================== */
export async function messages() {
  const rows = check(await sb.from('messages').select('*').order('id', { ascending: false }).limit(500));
  return {
    title: 'Mesajlar',
    html: `
      <div class="page-head"><p class="muted">İletişim formundan gelen mesajlar.</p>${rows.some((m) => !m.is_read) ? `<button class="btn" data-readall>${icon('check')} Tümünü okundu yap</button>` : ''}</div>
      <div class="card card--flush">${rows.length ? `<ul class="inbox">${rows.map((m) => `<li class="${m.is_read ? '' : 'is-unread'}"><a href="#/mesajlar/${m.id}"><span class="avatar avatar--soft">${esc((m.name || '?').charAt(0).toLocaleUpperCase('tr-TR'))}</span><div class="inbox__body"><div class="inbox__top"><strong>${esc(m.name)}</strong><time>${trDate(m.created_at, true)}</time></div><span class="inbox__subject">${esc(m.subject || 'Konu yok')}</span><small class="muted">${esc(excerpt(m.message, 120))}</small></div></a></li>`).join('')}</ul>` : empty('inbox', 'Gelen kutusu boş', 'İletişim formundan gelen mesajlar burada görünür.')}</div>`,
    mount(root) {
      $('[data-readall]', root)?.addEventListener('click', async () => {
        try { check(await sb.from('messages').update({ is_read: 1 }).eq('is_read', 0)); toast('Tüm mesajlar okundu olarak işaretlendi.'); window.dispatchEvent(new HashChangeEvent('hashchange')); } catch (e) { toast(e.message, 'error'); }
      });
    },
  };
}

export async function message({ id }) {
  const m = check(await sb.from('messages').select('*').eq('id', id))[0];
  if (!m) return { title: 'Bulunamadı', html: empty('help', 'Mesaj bulunamadı', '', '<a href="#/mesajlar" class="btn">Geri dön</a>') };
  if (!m.is_read) await sb.from('messages').update({ is_read: 1 }).eq('id', id);
  return {
    title: 'Mesaj',
    html: `
      <a href="#/mesajlar" class="back-link">${icon('arrow-left')} Mesajlar</a>
      <div class="card message">
        <div class="message__head"><span class="avatar avatar--lg">${esc((m.name || '?').charAt(0).toLocaleUpperCase('tr-TR'))}</span><div><h2>${esc(m.subject || 'Konu yok')}</h2><p class="muted"><strong>${esc(m.name)}</strong> · ${trDate(m.created_at, true)}</p></div></div>
        <div class="message__meta">
          ${m.phone ? `<a href="${telHref(m.phone)}" class="btn">${icon('phone')} ${esc(m.phone)}</a><a href="${waHref(m.phone)}" target="_blank" rel="noopener" class="btn btn--wa">${icon('whatsapp')} WhatsApp</a>` : ''}
          ${m.email ? `<a href="mailto:${esc(m.email)}?subject=${encodeURIComponent('Re: ' + (m.subject || 'Mesajınız'))}" class="btn btn--primary">${icon('mail')} Yanıtla</a>` : ''}
        </div>
        <div class="message__body">${esc(m.message).replace(/\n/g, '<br>')}</div>
        <div class="message__foot"><button class="btn" data-unread>Okunmadı olarak işaretle</button><button class="btn btn--danger-ghost" data-delete>${icon('trash')} Sil</button></div>
      </div>`,
    mount(root) {
      $('[data-unread]', root).onclick = async () => { await sb.from('messages').update({ is_read: 0 }).eq('id', id); go('#/mesajlar'); };
      $('[data-delete]', root).onclick = async () => {
        if (!confirmDelete('Mesaj')) return;
        try { check(await sb.from('messages').delete().eq('id', id)); toast('Mesaj silindi.'); go('#/mesajlar'); } catch (e) { toast(e.message, 'error'); }
      };
    },
  };
}

/* =================== Products =================== */
export async function products({ query }) {
  const [rows, cats] = await Promise.all([
    sb.from('products').select('*').order('sort').order('id').then(check),
    sb.from('product_categories').select('*').order('sort').then(check),
  ]);
  const cat = Number(query.get('kategori') || 0);
  const q = (query.get('q') || '').toLocaleLowerCase('tr-TR');
  let list = cat ? rows.filter((r) => r.category_id === cat) : rows;
  if (q) list = list.filter((r) => `${r.title} ${r.brand}`.toLocaleLowerCase('tr-TR').includes(q));
  const catName = Object.fromEntries(cats.map((c) => [c.id, c.name]));
  return {
    title: 'Ürünler',
    html: `
      <div class="page-head">
        <div class="tabs tabs--scroll"><a href="#/urunler" class="${!cat ? 'is-active' : ''}">Tümü <em>${rows.length}</em></a>${cats.map((c) => `<a href="#/urunler?kategori=${c.id}" class="${cat === c.id ? 'is-active' : ''}">${esc(c.name)} <em>${rows.filter((r) => r.category_id === c.id).length}</em></a>`).join('')}</div>
        <div class="page-head__actions">
          <form class="search" data-search>${icon('search')}<input type="search" name="q" value="${esc(query.get('q') || '')}" placeholder="Ürün veya marka ara…"></form>
          <a href="#/urunler/yeni${cat ? '?kategori=' + cat : ''}" class="btn btn--primary">${icon('plus')} Yeni Ürün</a>
        </div>
      </div>
      <div class="card card--flush">${list.length ? `<table class="table"><thead><tr><th>Ürün</th><th class="hide-sm">Kategori</th><th class="hide-sm">Marka</th><th>Durum</th><th></th></tr></thead><tbody>
        ${list.map((r) => `<tr>
          <td><a href="#/urunler/${r.id}" class="row-title"><span class="thumb">${r.image ? `<img src="${esc(r.image)}" alt="">` : icon('package')}</span><span>${esc(r.title)}${r.featured ? ` <span class="tag tag--amber">${icon('star')} Öne çıkan</span>` : ''}<small>/urunler/${esc(r.slug)}</small></span></a></td>
          <td class="hide-sm">${esc(catName[r.category_id] || '—')}</td>
          <td class="hide-sm">${esc(r.brand || '—')}</td>
          <td><button class="status status--${r.active ? 'green' : 'slate'} status--btn" data-toggle="${r.id}" data-active="${r.active}" title="Değiştirmek için tıklayın">${r.active ? 'Yayında' : 'Gizli'}</button></td>
          <td class="actions"><a href="${SITE_URL}urunler/${esc(r.slug)}/" target="_blank" class="icon-btn" title="Sitede gör">${icon('eye')}</a><a href="#/urunler/${r.id}" class="icon-btn" title="Düzenle">${icon('edit')}</a><button class="icon-btn icon-btn--danger" data-del="${r.id}" title="Sil">${icon('trash')}</button></td>
        </tr>`).join('')}</tbody></table>` : empty('package', 'Ürün bulunamadı', '', '<a href="#/urunler/yeni" class="btn btn--primary">Yeni Ürün</a>')}</div>`,
    mount(root) {
      $('[data-search]', root).addEventListener('submit', (e) => { e.preventDefault(); const v = e.target.elements.q.value.trim(); go(`#/urunler?${new URLSearchParams({ ...(cat ? { kategori: cat } : {}), ...(v ? { q: v } : {}) })}`); });
      $$('[data-toggle]', root).forEach((b) => b.addEventListener('click', async () => {
        try { check(await sb.from('products').update({ active: b.dataset.active === '1' ? 0 : 1 }).eq('id', b.dataset.toggle)); window.dispatchEvent(new HashChangeEvent('hashchange')); } catch (e) { toast(e.message, 'error'); }
      }));
      $$('[data-del]', root).forEach((b) => b.addEventListener('click', async () => {
        if (!confirmDelete('Ürün')) return;
        try { check(await sb.from('products').delete().eq('id', b.dataset.del)); toast('Ürün silindi.'); window.dispatchEvent(new HashChangeEvent('hashchange')); } catch (e) { toast(e.message, 'error'); }
      }));
    },
  };
}

export async function productEdit({ id, query }) {
  const isNew = id === 'yeni';
  const [row, cats, brandList] = await Promise.all([
    isNew ? Promise.resolve(null) : sb.from('products').select('*').eq('id', id).then(check).then((r) => r[0]),
    sb.from('product_categories').select('*').order('sort').then(check),
    brands(),
  ]);
  if (!isNew && !row) return { title: 'Bulunamadı', html: empty('help', 'Ürün bulunamadı', '', '<a href="#/urunler" class="btn">Geri dön</a>') };
  const r = row || { title: '', slug: '', category_id: Number(query.get('kategori')) || null, brand: '', summary: '', content: '', specs: '', model: '', featured: 0, active: 1, image: '' };
  let editor;
  return {
    title: isNew ? 'Yeni Ürün' : 'Ürünü Düzenle',
    html: `
      <a href="#/urunler" class="back-link">${icon('arrow-left')} Ürünler</a>
      <form class="editor-layout" data-form>
        <div class="editor-main">
          <div class="card">
            <label class="field"><input type="text" name="title" value="${esc(r.title)}" placeholder="Ürün adı" class="input-title" required data-slug-source></label>
            <div class="slug-row"><span>${esc(SITE_URL)}urunler/</span><input type="text" name="slug" value="${esc(r.slug)}" placeholder="otomatik-olusturulur" data-slug-target></div>
            ${area('summary', 'Kısa açıklama <small class="muted">(ürün kartında ve Google’da görünür)</small>', r.summary, 2, 'maxlength="400"')}
            <div class="field"><span>Ürün açıklaması</span><div class="editor" data-editor></div></div>
          </div>
          <div class="card"><h3>Teknik özellikler</h3>
            <p class="muted small">Her satıra bir özellik: <code>Özellik | Değer</code>. İlk satırlar ürün sayfasının üstünde öne çıkar.</p>
            <label class="field"><textarea name="specs" rows="8" class="mono-input" placeholder="Marka | Grundfos&#10;Debi | 10 m³/saat">${esc(r.specs)}</textarea></label>
          </div>
        </div>
        <aside class="editor-side">
          <div class="card sticky"><h3>Yayın</h3>
            ${sw('active', 'Sitede göster', r.active)}${sw('featured', 'Ana sayfada öne çıkar', r.featured)}
            <label class="field"><span>Kategori</span><select name="category_id"><option value="">— Kategorisiz —</option>${cats.map((c) => opt(c.id, c.name, r.category_id)).join('')}</select></label>
            <label class="field"><span>Marka</span><input name="brand" value="${esc(r.brand)}" list="brand-list" placeholder="Örn. Grundfos"></label>
            <datalist id="brand-list">${brandList.map((b) => `<option value="${esc(b.name)}">`).join('')}</datalist>
            <div class="btn-stack"><button class="btn btn--primary btn--block" data-save>${icon('check')} Kaydet</button>
              ${isNew ? '' : `<a href="${SITE_URL}urunler/${esc(r.slug)}/" target="_blank" class="btn btn--block">${icon('eye')} Sitede görüntüle</a>`}</div>
            ${publishNote}
          </div>
          <div class="card"><h3>Çizim ve 3D model</h3>
            <p class="muted small">Ürün sayfasındaki çizim ve “3D incele” modeli bu seçime göre oluşturulur.</p>
            <label class="field"><span>Model tipi</span><select name="model"><option value="">Kategoriye göre otomatik</option>${Object.entries(MODEL_TYPES).map(([k, l]) => opt(k, l, r.model)).join('')}</select></label>
          </div>
          <div class="card"><h3>Ürün fotoğrafı <small class="muted">(isteğe bağlı)</small></h3>
            ${dropzone('image_file', r.image)}
            ${r.image ? '<label class="check"><input type="checkbox" name="remove_image" value="1"> Fotoğrafı kaldır</label>' : ''}
          </div>
          ${isNew ? '' : `<div class="card card--danger"><button type="button" class="btn btn--danger-ghost btn--block" data-delete>${icon('trash')} Ürünü sil</button></div>`}
        </aside>
      </form>`,
    async mount(root) {
      slugWire(root); imageWire(root);
      editor = await makeEditor($('[data-editor]', root), r.content);
      const form = $('[data-form]', root);
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        withBusy($('[data-save]', form), async () => {
          const d = {
            title: val(form, 'title'), slug: slugify(val(form, 'slug') || val(form, 'title')), summary: val(form, 'summary'),
            content: editor.html(), specs: val(form, 'specs'), model: val(form, 'model'), brand: val(form, 'brand'),
            category_id: val(form, 'category_id') ? Number(val(form, 'category_id')) : null, active: checked(form, 'active'), featured: checked(form, 'featured'),
          };
          if (!d.title) throw new Error('Ürün adı zorunludur.');
          const f = form.elements.image_file.files[0];
          if (f) d.image = await uploadImage(f, 'products');
          else if (checked(form, 'remove_image')) d.image = '';
          if (isNew) {
            const ins = check(await sb.from('products').insert({ ...d, sort: Date.now() % 100000 }).select());
            toast('Ürün kaydedildi.'); go(`#/urunler/${ins[0].id}`);
          } else {
            check(await sb.from('products').update(d).eq('id', r.id).select());
            toast('Ürün kaydedildi.');
          }
        });
      });
      $('[data-delete]', root)?.addEventListener('click', async () => {
        if (!confirmDelete('Ürün')) return;
        try { check(await sb.from('products').delete().eq('id', r.id)); toast('Ürün silindi.'); go('#/urunler'); } catch (e) { toast(e.message, 'error'); }
      });
    },
  };
}

/* =================== Simple sortable lists (product categories, FAQ, services) =================== */
async function move(table, rows, index, dir) {
  const j = index + dir;
  if (j < 0 || j >= rows.length) return;
  const a = rows[index], b = rows[j];
  await Promise.all([
    sb.from(table).update({ sort: j + 1 }).eq('id', a.id),
    sb.from(table).update({ sort: index + 1 }).eq('id', b.id),
  ]);
  window.dispatchEvent(new HashChangeEvent('hashchange'));
}
const moveButtons = (i, n) => `<span class="move"><button type="button" class="icon-btn" data-move="${i}" data-dir="-1" title="Yukarı"${i === 0 ? ' disabled' : ''}>↑</button><button type="button" class="icon-btn" data-move="${i}" data-dir="1" title="Aşağı"${i === n - 1 ? ' disabled' : ''}>↓</button></span>`;

export async function productCategories({ query }) {
  const [rows, prods] = await Promise.all([sb.from('product_categories').select('*').order('sort').order('id').then(check), sb.from('products').select('id,category_id').then(check)]);
  const edit = rows.find((r) => String(r.id) === query.get('id')) || null;
  return {
    title: 'Ürün Kategorileri',
    html: `
      <div class="grid-2 grid-2--aside">
        <div class="card card--flush"><table class="table"><thead><tr><th>Kategori</th><th class="num">Ürün</th><th></th></tr></thead><tbody>
          ${rows.map((r, i) => `<tr><td><strong>${esc(r.name)}</strong><small class="block muted">/urunler/kategori/${esc(r.slug)}</small></td><td class="num">${prods.filter((p) => p.category_id === r.id).length}</td>
            <td class="actions">${moveButtons(i, rows.length)}<a href="#/urun-kategorileri?id=${r.id}" class="icon-btn" title="Düzenle">${icon('edit')}</a><button class="icon-btn icon-btn--danger" data-del="${r.id}" title="Sil">${icon('trash')}</button></td></tr>`).join('')}
        </tbody></table></div>
        <form class="card form-stack sticky" data-form>
          <h3>${edit ? 'Kategoriyi düzenle' : 'Yeni kategori'}</h3>
          ${field('name', 'Ad', edit?.name || '', 'text', 'required data-slug-source')}
          ${field('slug', 'Adres (slug)', edit?.slug || '', 'text', 'placeholder="otomatik" data-slug-target')}
          ${area('summary', 'Kısa açıklama', edit?.summary || '', 3)}
          <label class="field"><span>Çizim</span><select name="art">${Object.entries(ARTS).map(([k, l]) => opt(k, l, edit?.art || 'tank')).join('')}</select></label>
          <div class="field"><span>Kategori fotoğrafı <small class="muted">(yoksa çizim kullanılır)</small></span>${dropzone('photo_file', edit?.photo)}${edit?.photo ? '<label class="check"><input type="checkbox" name="remove_photo" value="1"> Fotoğrafı kaldır</label>' : ''}</div>
          <button class="btn btn--primary btn--block" data-save>${icon('check')} Kaydet</button>
          ${edit ? '<a href="#/urun-kategorileri" class="btn btn--block">Vazgeç</a>' : ''}
        </form>
      </div>`,
    mount(root) {
      slugWire(root); imageWire(root);
      $$('[data-move]', root).forEach((b) => b.onclick = () => move('product_categories', rows, Number(b.dataset.move), Number(b.dataset.dir)));
      $$('[data-del]', root).forEach((b) => b.onclick = async () => {
        if (!confirmDelete('Kategori (ürünleri kategorisiz kalır)')) return;
        try { check(await sb.from('product_categories').delete().eq('id', b.dataset.del)); toast('Kategori silindi.'); go('#/urun-kategorileri'); window.dispatchEvent(new HashChangeEvent('hashchange')); } catch (e) { toast(e.message, 'error'); }
      });
      const form = $('[data-form]', root);
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        withBusy($('[data-save]', form), async () => {
          const d = { name: val(form, 'name'), slug: slugify(val(form, 'slug') || val(form, 'name')), summary: val(form, 'summary'), art: val(form, 'art') };
          if (!d.name) throw new Error('Kategori adı zorunludur.');
          const f = form.elements.photo_file.files[0];
          if (f) { d.photo = await uploadImage(f, 'categories'); d.photo_credit = ''; d.photo_source = ''; } else if (checked(form, 'remove_photo')) d.photo = '';
          if (edit) check(await sb.from('product_categories').update(d).eq('id', edit.id).select());
          else check(await sb.from('product_categories').insert({ ...d, sort: rows.length + 1 }).select());
          toast('Kategori kaydedildi.'); go('#/urun-kategorileri'); window.dispatchEvent(new HashChangeEvent('hashchange'));
        });
      });
    },
  };
}

/* =================== Blog =================== */
export async function posts({ query }) {
  const [rows, cats] = await Promise.all([sb.from('posts').select('*').order('published_at', { ascending: false, nullsFirst: true }).then(check), sb.from('categories').select('*').then(check)]);
  const st = query.get('durum') || '';
  const list = st ? rows.filter((r) => r.status === st) : rows;
  const catName = Object.fromEntries(cats.map((c) => [c.id, c.name]));
  const tab = (k, l) => `<a href="#/blog${k ? '?durum=' + k : ''}" class="${st === k ? 'is-active' : ''}">${l} <em>${k ? rows.filter((r) => r.status === k).length : rows.length}</em></a>`;
  return {
    title: 'Blog Yazıları',
    html: `
      <div class="page-head"><div class="tabs">${tab('', 'Tümü')}${tab('published', 'Yayında')}${tab('draft', 'Taslak')}</div><a href="#/blog/yeni" class="btn btn--primary">${icon('plus')} Yeni Yazı</a></div>
      <div class="card card--flush">${list.length ? `<table class="table"><thead><tr><th>Başlık</th><th class="hide-sm">Kategori</th><th>Durum</th><th class="hide-sm">Tarih</th><th></th></tr></thead><tbody>
        ${list.map((p) => `<tr><td><a href="#/blog/${p.id}" class="row-title"><span class="thumb">${p.cover ? `<img src="${esc(p.cover)}" alt="">` : icon('file-text')}</span><span>${esc(p.title)}${p.featured ? ` <span class="tag tag--amber">${icon('star')} Öne çıkan</span>` : ''}<small>/blog/${esc(p.slug)}</small></span></a></td>
          <td class="hide-sm">${esc(catName[p.category_id] || '—')}</td>
          <td>${p.status === 'published' ? (new Date(p.published_at) > new Date() ? '<span class="status status--blue">Zamanlandı</span>' : '<span class="status status--green">Yayında</span>') : '<span class="status status--slate">Taslak</span>'}</td>
          <td class="hide-sm nowrap">${trDate(p.published_at || p.created_at)}</td>
          <td class="actions"><a href="#/blog/${p.id}" class="icon-btn" title="Düzenle">${icon('edit')}</a><button class="icon-btn icon-btn--danger" data-del="${p.id}" title="Sil">${icon('trash')}</button></td></tr>`).join('')}
      </tbody></table>` : empty('file-text', 'Yazı bulunamadı', 'İlk blog yazınızı oluşturarak başlayın.', '<a href="#/blog/yeni" class="btn btn--primary">Yeni Yazı</a>')}</div>`,
    mount(root) {
      $$('[data-del]', root).forEach((b) => b.onclick = async () => {
        if (!confirmDelete('Yazı')) return;
        try { check(await sb.from('posts').delete().eq('id', b.dataset.del)); toast('Yazı silindi.'); window.dispatchEvent(new HashChangeEvent('hashchange')); } catch (e) { toast(e.message, 'error'); }
      });
    },
  };
}

export async function postEdit({ id }) {
  const isNew = id === 'yeni';
  const [row, cats] = await Promise.all([isNew ? Promise.resolve(null) : sb.from('posts').select('*').eq('id', id).then(check).then((r) => r[0]), sb.from('categories').select('*').order('name').then(check)]);
  if (!isNew && !row) return { title: 'Bulunamadı', html: empty('help', 'Yazı bulunamadı', '', '<a href="#/blog" class="btn">Geri dön</a>') };
  const p = row || { title: '', slug: '', excerpt: '', content: '', cover: '', category_id: null, status: 'draft', featured: 0, meta_title: '', meta_desc: '', published_at: null };
  let editor;
  return {
    title: isNew ? 'Yeni Yazı' : 'Yazıyı Düzenle',
    html: `
      <a href="#/blog" class="back-link">${icon('arrow-left')} Blog yazıları</a>
      <form class="editor-layout" data-form>
        <div class="editor-main">
          <div class="card">
            <label class="field"><input type="text" name="title" value="${esc(p.title)}" placeholder="Yazı başlığı" class="input-title" required data-slug-source></label>
            <div class="slug-row"><span>${esc(SITE_URL)}blog/</span><input type="text" name="slug" value="${esc(p.slug)}" placeholder="otomatik-olusturulur" data-slug-target></div>
            <div class="field"><div class="editor" data-editor></div></div>
          </div>
          <div class="card"><h3>Özet & SEO</h3>
            ${area('excerpt', 'Kısa özet <small class="muted">(liste ve paylaşımlarda görünür)</small>', p.excerpt, 3, 'maxlength="400"')}
            <div class="form-row">${field('meta_title', 'SEO başlığı', p.meta_title, 'text', 'maxlength="120" placeholder="Boşsa başlık kullanılır"')}${field('meta_desc', 'SEO açıklaması', p.meta_desc, 'text', 'maxlength="300" placeholder="Boşsa özet kullanılır"')}</div>
          </div>
        </div>
        <aside class="editor-side">
          <div class="card sticky"><h3>Yayın</h3>
            <div class="seg"><label><input type="radio" name="status" value="draft"${p.status !== 'published' ? ' checked' : ''}><span>Taslak</span></label><label><input type="radio" name="status" value="published"${p.status === 'published' ? ' checked' : ''}><span>Yayında</span></label></div>
            <label class="field"><span>Yayın tarihi</span><input type="datetime-local" name="published_at" value="${toLocalInput(p.published_at)}"><small class="muted">İleri bir tarih seçerek zamanlayabilirsiniz.</small></label>
            <label class="field"><span>Kategori</span><select name="category_id"><option value="">— Kategorisiz —</option>${cats.map((c) => opt(c.id, c.name, p.category_id)).join('')}</select></label>
            ${sw('featured', 'Öne çıkan yazı', p.featured)}
            <button class="btn btn--primary btn--block" data-save>${icon('check')} Kaydet</button>
            ${publishNote}
          </div>
          <div class="card"><h3>Kapak görseli</h3>${dropzone('cover_file', p.cover)}${p.cover ? '<label class="check"><input type="checkbox" name="remove_cover" value="1"> Kapak görselini kaldır</label>' : ''}</div>
          ${isNew ? '' : `<div class="card card--danger"><button type="button" class="btn btn--danger-ghost btn--block" data-delete>${icon('trash')} Yazıyı sil</button></div>`}
        </aside>
      </form>`,
    async mount(root) {
      slugWire(root); imageWire(root);
      editor = await makeEditor($('[data-editor]', root), p.content);
      const form = $('[data-form]', root);
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        withBusy($('[data-save]', form), async () => {
          const statusVal = form.querySelector('[name=status]:checked').value;
          const pub = val(form, 'published_at');
          const content = editor.html();
          const d = {
            title: val(form, 'title'), slug: slugify(val(form, 'slug') || val(form, 'title')), content,
            excerpt: val(form, 'excerpt') || excerpt(content, 180), meta_title: val(form, 'meta_title'), meta_desc: val(form, 'meta_desc'),
            category_id: val(form, 'category_id') ? Number(val(form, 'category_id')) : null, status: statusVal, featured: checked(form, 'featured'),
            published_at: pub ? new Date(pub).toISOString() : (statusVal === 'published' ? new Date().toISOString() : null),
          };
          if (!d.title) throw new Error('Başlık zorunludur.');
          if (!content.replace(/<[^>]+>/g, '').trim() && !/<img/.test(content)) throw new Error('İçerik boş olamaz.');
          const f = form.elements.cover_file.files[0];
          if (f) d.cover = await uploadImage(f, 'blog'); else if (checked(form, 'remove_cover')) d.cover = '';
          if (isNew) { const ins = check(await sb.from('posts').insert(d).select()); toast('Yazı kaydedildi.'); go(`#/blog/${ins[0].id}`); }
          else { check(await sb.from('posts').update(d).eq('id', p.id).select()); toast(statusVal === 'published' ? 'Yazı kaydedildi ve yayında.' : 'Taslak kaydedildi.'); }
        });
      });
      $('[data-delete]', root)?.addEventListener('click', async () => {
        if (!confirmDelete('Yazı')) return;
        try { check(await sb.from('posts').delete().eq('id', p.id)); toast('Yazı silindi.'); go('#/blog'); } catch (e) { toast(e.message, 'error'); }
      });
    },
  };
}

export async function blogCategories({ query }) {
  const [rows, ps] = await Promise.all([sb.from('categories').select('*').order('name').then(check), sb.from('posts').select('id,category_id').then(check)]);
  const edit = rows.find((r) => String(r.id) === query.get('id')) || null;
  return {
    title: 'Blog Kategorileri',
    html: `<div class="grid-2 grid-2--aside">
      <div class="card card--flush"><table class="table"><thead><tr><th>Kategori</th><th class="num">Yazı</th><th></th></tr></thead><tbody>
        ${rows.map((r) => `<tr><td><strong>${esc(r.name)}</strong><small class="block muted">/blog/kategori/${esc(r.slug)}</small></td><td class="num">${ps.filter((p) => p.category_id === r.id).length}</td><td class="actions"><a href="#/blog-kategorileri?id=${r.id}" class="icon-btn">${icon('edit')}</a><button class="icon-btn icon-btn--danger" data-del="${r.id}">${icon('trash')}</button></td></tr>`).join('')}
      </tbody></table></div>
      <form class="card form-stack" data-form><h3>${edit ? 'Kategoriyi düzenle' : 'Yeni kategori'}</h3>
        ${field('name', 'Ad', edit?.name || '', 'text', 'required data-slug-source')}${field('slug', 'Adres (slug)', edit?.slug || '', 'text', 'placeholder="otomatik" data-slug-target')}
        <button class="btn btn--primary btn--block" data-save>${icon('check')} Kaydet</button>${edit ? '<a href="#/blog-kategorileri" class="btn btn--block">Vazgeç</a>' : ''}
      </form></div>`,
    mount(root) {
      slugWire(root);
      $$('[data-del]', root).forEach((b) => b.onclick = async () => { if (!confirmDelete('Kategori')) return; try { check(await sb.from('categories').delete().eq('id', b.dataset.del)); window.dispatchEvent(new HashChangeEvent('hashchange')); } catch (e) { toast(e.message, 'error'); } });
      const form = $('[data-form]', root);
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        withBusy($('[data-save]', form), async () => {
          const d = { name: val(form, 'name'), slug: slugify(val(form, 'slug') || val(form, 'name')) };
          if (!d.name) throw new Error('Kategori adı zorunludur.');
          if (edit) check(await sb.from('categories').update(d).eq('id', edit.id).select()); else check(await sb.from('categories').insert(d).select());
          toast('Kategori kaydedildi.'); go('#/blog-kategorileri'); window.dispatchEvent(new HashChangeEvent('hashchange'));
        });
      });
    },
  };
}

/* =================== Services =================== */
export async function services() {
  const rows = check(await sb.from('services').select('*').order('sort').order('id'));
  return {
    title: 'Hizmetler',
    html: `<div class="page-head"><p class="muted">Sıra ana sayfada ve menüde kullanılır.</p><a href="#/hizmetler/yeni" class="btn btn--primary">${icon('plus')} Yeni Hizmet</a></div>
      <div class="card card--flush"><table class="table"><tbody>${rows.map((r, i) => `<tr><td><a href="#/hizmetler/${r.id}" class="row-title"><span class="thumb">${icon(r.icon)}</span><span>${esc(r.title)}<small>${esc(excerpt(r.summary, 90))}</small></span></a></td><td>${r.active ? '<span class="status status--green">Aktif</span>' : '<span class="status status--slate">Pasif</span>'}</td><td class="actions">${moveButtons(i, rows.length)}<a href="#/hizmetler/${r.id}" class="icon-btn">${icon('edit')}</a><button class="icon-btn icon-btn--danger" data-del="${r.id}">${icon('trash')}</button></td></tr>`).join('')}</tbody></table></div>`,
    mount(root) {
      $$('[data-move]', root).forEach((b) => b.onclick = () => move('services', rows, Number(b.dataset.move), Number(b.dataset.dir)));
      $$('[data-del]', root).forEach((b) => b.onclick = async () => { if (!confirmDelete('Hizmet')) return; try { check(await sb.from('services').delete().eq('id', b.dataset.del)); window.dispatchEvent(new HashChangeEvent('hashchange')); } catch (e) { toast(e.message, 'error'); } });
    },
  };
}

export async function serviceEdit({ id }) {
  const isNew = id === 'yeni';
  const row = isNew ? null : check(await sb.from('services').select('*').eq('id', id))[0];
  const s = row || { title: '', slug: '', icon: 'wrench', summary: '', content: '', active: 1 };
  let editor;
  return {
    title: isNew ? 'Yeni Hizmet' : 'Hizmeti Düzenle',
    html: `<a href="#/hizmetler" class="back-link">${icon('arrow-left')} Hizmetler</a>
      <form class="editor-layout" data-form>
        <div class="editor-main"><div class="card">
          <label class="field"><input type="text" name="title" value="${esc(s.title)}" placeholder="Hizmet adı" class="input-title" required data-slug-source></label>
          <div class="slug-row"><span>${esc(SITE_URL)}hizmetler/</span><input type="text" name="slug" value="${esc(s.slug)}" placeholder="otomatik-olusturulur" data-slug-target></div>
          ${area('summary', 'Kısa açıklama', s.summary, 2, 'maxlength="400"')}
          <div class="field"><span>Detay içeriği</span><div class="editor" data-editor></div></div>
        </div></div>
        <aside class="editor-side"><div class="card sticky"><h3>Ayarlar</h3>
          ${sw('active', 'Sitede göster', s.active)}
          <div class="field"><span>İkon</span><div class="icon-picker">${SERVICE_ICONS.map((ic) => `<label title="${ic}"><input type="radio" name="icon" value="${ic}"${s.icon === ic ? ' checked' : ''}><span>${icon(ic)}</span></label>`).join('')}</div></div>
          <button class="btn btn--primary btn--block" data-save>${icon('check')} Kaydet</button>${publishNote}
        </div></aside>
      </form>`,
    async mount(root) {
      slugWire(root);
      editor = await makeEditor($('[data-editor]', root), s.content);
      const form = $('[data-form]', root);
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        withBusy($('[data-save]', form), async () => {
          const d = { title: val(form, 'title'), slug: slugify(val(form, 'slug') || val(form, 'title')), summary: val(form, 'summary'), content: editor.html(), active: checked(form, 'active'), icon: form.querySelector('[name=icon]:checked')?.value || 'wrench' };
          if (!d.title) throw new Error('Başlık zorunludur.');
          if (isNew) { const ins = check(await sb.from('services').insert({ ...d, sort: 99 }).select()); toast('Hizmet kaydedildi.'); go(`#/hizmetler/${ins[0].id}`); }
          else { check(await sb.from('services').update(d).eq('id', s.id).select()); toast('Hizmet kaydedildi.'); }
        });
      });
    },
  };
}

/* =================== FAQ =================== */
export async function faqs({ query }) {
  const rows = check(await sb.from('faqs').select('*').order('sort').order('id'));
  const edit = rows.find((r) => String(r.id) === query.get('id')) || null;
  return {
    title: 'Sıkça Sorulan Sorular',
    html: `<div class="grid-2 grid-2--aside">
      <div class="card card--flush"><table class="table"><tbody>${rows.map((r, i) => `<tr><td><strong>${esc(r.question)}</strong><small class="block muted">${esc(excerpt(r.answer, 110))}</small></td><td>${r.active ? '' : '<span class="status status--slate">Pasif</span>'}</td><td class="actions">${moveButtons(i, rows.length)}<a href="#/sss?id=${r.id}" class="icon-btn">${icon('edit')}</a><button class="icon-btn icon-btn--danger" data-del="${r.id}">${icon('trash')}</button></td></tr>`).join('')}</tbody></table></div>
      <form class="card form-stack sticky" data-form><h3>${edit ? 'Soruyu düzenle' : 'Yeni soru'}</h3>
        ${field('question', 'Soru', edit?.question || '', 'text', 'required')}${area('answer', 'Cevap', edit?.answer || '', 6, 'required')}${sw('active', 'Sitede göster', edit ? edit.active : 1)}
        <button class="btn btn--primary btn--block" data-save>${icon('check')} Kaydet</button>${edit ? '<a href="#/sss" class="btn btn--block">Vazgeç</a>' : ''}
      </form></div>`,
    mount(root) {
      $$('[data-move]', root).forEach((b) => b.onclick = () => move('faqs', rows, Number(b.dataset.move), Number(b.dataset.dir)));
      $$('[data-del]', root).forEach((b) => b.onclick = async () => { if (!confirmDelete('Soru')) return; try { check(await sb.from('faqs').delete().eq('id', b.dataset.del)); window.dispatchEvent(new HashChangeEvent('hashchange')); } catch (e) { toast(e.message, 'error'); } });
      const form = $('[data-form]', root);
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        withBusy($('[data-save]', form), async () => {
          const d = { question: val(form, 'question'), answer: val(form, 'answer'), active: checked(form, 'active') };
          if (!d.question || !d.answer) throw new Error('Soru ve cevap zorunludur.');
          if (edit) check(await sb.from('faqs').update(d).eq('id', edit.id).select()); else check(await sb.from('faqs').insert({ ...d, sort: rows.length + 1 }).select());
          toast('Soru kaydedildi.'); go('#/sss'); window.dispatchEvent(new HashChangeEvent('hashchange'));
        });
      });
    },
  };
}

/* =================== Settings =================== */
export async function settings({ id }) {
  const tab = SETTINGS_GROUPS[id] ? id : 'general';
  const rows = check(await sb.from('settings').select('*'));
  const v = Object.fromEntries(rows.map((r) => [r.key, r.value]));
  const [label, , fields] = SETTINGS_GROUPS[tab];
  const brandList = tab === 'brands' ? await brands() : [];
  const logoCard = tab === 'general' ? `
    <form class="card form-stack" data-logo><h3>Logo</h3>
      <p class="muted small">Yüklemezseniz sitede yerleşik logo çizimi kullanılır. Şeffaf arka planlı PNG önerilir.</p>
      <div class="form-row">
        <div class="field"><span>Logo (açık zemin)</span>${dropzone('logo', v.logo, 'Dosya seçin')}${v.logo ? '<label class="check"><input type="checkbox" name="remove_logo" value="1"> Kaldır</label>' : ''}</div>
        <div class="field"><span>Logo (koyu zemin, isteğe bağlı)</span>${dropzone('logo_light', v.logo_light, 'Dosya seçin')}${v.logo_light ? '<label class="check"><input type="checkbox" name="remove_logo_light" value="1"> Kaldır</label>' : ''}</div>
      </div>
      <div><button class="btn btn--primary" data-save>${icon('check')} Logoyu Kaydet</button></div>
    </form>` : '';
  const brandCard = tab === 'brands' ? `
    <form class="card form-stack" data-brandlogos><h3>Marka logoları</h3>
      <p class="muted small">Yüklediğiniz logo, sitedeki hazır logonun yerine geçer. Şeffaf arka planlı PNG/WEBP önerilir.</p>
      <div class="brand-logos-admin">${brandList.map((b) => `<div class="brand-logos-admin__row"><span class="brand-logos-admin__preview">${v['brand_logo_' + b.slug] ? `<img src="${esc(v['brand_logo_' + b.slug])}" alt="">` : '<em class="muted">Hazır logo</em>'}</span><div><strong>${esc(b.name)}</strong><input type="file" name="brand_logo_${b.slug}" accept="image/png,image/webp,image/jpeg">${v['brand_logo_' + b.slug] ? `<label class="check"><input type="checkbox" name="remove_brand_logo_${b.slug}" value="1"> Yüklenen logoyu kaldır</label>` : ''}</div></div>`).join('')}</div>
      <div><button class="btn btn--primary" data-save>${icon('check')} Logoları Kaydet</button></div>
    </form>` : '';
  return {
    title: 'Site Ayarları',
    html: `<div class="settings">
      <nav class="settings__nav">${Object.entries(SETTINGS_GROUPS).map(([k, [l, ic]]) => `<a href="#/ayarlar/${k}" class="${tab === k ? 'is-active' : ''}">${icon(ic)} ${l}</a>`).join('')}</nav>
      <div>${logoCard}
        <form class="card form-stack" data-form><h3>${label}</h3>
          ${Object.entries(fields).map(([k, [l, type, help]]) => `<label class="field"><span>${l}</span>${type === 'textarea' ? `<textarea name="${k}" rows="${k === 'about_text' ? 8 : 3}">${esc(v[k] || '')}</textarea>` : `<input type="${type}" name="${k}" value="${esc(v[k] || '')}">`}${help ? `<small class="muted">${esc(help)}</small>` : ''}</label>`).join('')}
          <div><button class="btn btn--primary" data-save>${icon('check')} Değişiklikleri Kaydet</button></div>${publishNote}
        </form>${brandCard}
      </div></div>`,
    mount(root) {
      imageWire(root);
      const upsert = (pairs) => sb.from('settings').upsert(pairs.map(([key, value]) => ({ key, value })), { onConflict: 'key' }).select().then(check);
      const form = $('[data-form]', root);
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        withBusy($('[data-save]', form), async () => {
          const pairs = [];
          for (const [k, [l, type]] of Object.entries(fields)) {
            const value = val(form, k);
            if (type === 'url' && value && !/^https:\/\//i.test(value)) throw new Error(`${l}: bağlantı https:// ile başlamalı.`);
            if (type === 'email' && value && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(value)) throw new Error(`${l}: geçerli bir e-posta girin.`);
            pairs.push([k, value]);
          }
          await upsert(pairs);
          toast('Ayarlar kaydedildi.');
        });
      });
      const logoForm = $('[data-logo]', root);
      logoForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        withBusy($('[data-save]', logoForm), async () => {
          const pairs = [];
          for (const k of ['logo', 'logo_light']) {
            const f = logoForm.elements[k].files[0];
            if (f) pairs.push([k, await uploadImage(f, 'logos', 800)]); else if (checked(logoForm, 'remove_' + k)) pairs.push([k, '']);
          }
          if (pairs.length) await upsert(pairs);
          toast('Logo güncellendi.'); window.dispatchEvent(new HashChangeEvent('hashchange'));
        });
      });
      const bForm = $('[data-brandlogos]', root);
      bForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        withBusy($('[data-save]', bForm), async () => {
          const pairs = [];
          for (const b of brandList) {
            const k = 'brand_logo_' + b.slug;
            const f = bForm.elements[k].files[0];
            if (f) pairs.push([k, await uploadImage(f, 'brands', 900)]); else if (checked(bForm, 'remove_' + k)) pairs.push([k, '']);
          }
          if (pairs.length) await upsert(pairs);
          toast('Marka logoları güncellendi.'); window.dispatchEvent(new HashChangeEvent('hashchange'));
        });
      });
    },
  };
}

/* =================== References & partners (logo lists) =================== */
function logoList({ table, route, title, noun, fields, meta }) {
  return async ({ query }) => {
    const rows = check(await sb.from(table).select('*').order('sort').order('id'));
    const edit = rows.find((r) => String(r.id) === query.get('id')) || null;
    const isNew = query.get('id') === 'yeni';
    const showForm = edit || isNew || !rows.length;
    const r = edit || {};
    return {
      title,
      html: `<div class="grid-2 grid-2--aside">
        <div>
          <div class="page-head"><p class="muted small">${rows.length} kayıt · sıralama sitede görünen sıradır.</p><a href="#/${route}?id=yeni" class="btn btn--primary">${icon('plus')} Yeni ${noun}</a></div>
          <div class="card card--flush">${rows.length ? `<table class="table"><tbody>${rows.map((x, i) => `<tr>
            <td><div class="row-title">${x.logo ? `<span class="thumb thumb--logo"><img src="${esc(x.logo)}" alt=""></span>` : `<span class="thumb">${esc(x.name.charAt(0))}</span>`}<span>${esc(x.name)}<small>${esc(meta(x))}</small></span></div></td>
            <td>${x.active ? '' : '<span class="status status--slate">Gizli</span>'}${x.featured === 0 ? '<span class="status status--slate">Ana sayfada yok</span>' : ''}</td>
            <td class="actions">${moveButtons(i, rows.length)}<a href="#/${route}?id=${x.id}" class="icon-btn" title="Düzenle">${icon('edit')}</a><button class="icon-btn icon-btn--danger" data-del="${x.id}" title="Sil">${icon('trash')}</button></td></tr>`).join('')}</tbody></table>`
            : empty('award', `Henüz ${noun.toLocaleLowerCase('tr-TR')} eklenmedi`, 'Sağdaki formdan ilk kaydı ekleyin; sitede ilgili sayfada görünür.')}</div>
        </div>
        ${showForm ? `<form class="card form-stack sticky" data-form><h3>${edit ? `${noun} düzenle` : `Yeni ${noun.toLocaleLowerCase('tr-TR')}`}</h3>
          ${fields.map(([name, label, type]) => (type === 'textarea' ? area(name, label, r[name] || '', 3) : field(name, label, r[name] || '', type || 'text', name === 'name' ? 'required' : ''))).join('')}
          <div class="field"><span>Logo <small class="muted">(isteğe bağlı, şeffaf PNG önerilir)</small></span>${dropzone('logo_file', r.logo, 'Logo seçin')}${r.logo ? '<label class="check"><input type="checkbox" name="remove_logo" value="1"> Logoyu kaldır</label>' : ''}</div>
          ${sw('active', 'Sitede göster', edit ? edit.active : 1)}
          ${table === 'refs' ? sw('featured', 'Ana sayfada göster', edit ? edit.featured : 1) : ''}
          <button class="btn btn--primary btn--block" data-save>${icon('check')} Kaydet</button>${edit || isNew ? `<a href="#/${route}" class="btn btn--block">Vazgeç</a>` : ''}
          ${publishNote}
        </form>` : `<div class="card"><h3>${title}</h3><p class="muted small">Düzenlemek için listeden bir kayda tıklayın ya da “Yeni ${noun}” ile ekleyin.</p></div>`}
      </div>`,
      mount(root) {
        imageWire(root);
        $$('[data-move]', root).forEach((b) => b.onclick = () => move(table, rows, Number(b.dataset.move), Number(b.dataset.dir)));
        $$('[data-del]', root).forEach((b) => b.onclick = async () => {
          if (!confirmDelete(noun)) return;
          try { check(await sb.from(table).delete().eq('id', b.dataset.del)); toast(`${noun} silindi.`); go(`#/${route}`); window.dispatchEvent(new HashChangeEvent('hashchange')); } catch (e) { toast(e.message, 'error'); }
        });
        const form = $('[data-form]', root);
        form?.addEventListener('submit', (e) => {
          e.preventDefault();
          withBusy($('[data-save]', form), async () => {
            const d = Object.fromEntries(fields.map(([name]) => [name, val(form, name)]));
            d.active = checked(form, 'active');
            if (table === 'refs') d.featured = checked(form, 'featured');
            if (!d.name) throw new Error('Ad zorunludur.');
            if (d.url && !/^https?:\/\//.test(d.url)) d.url = 'https://' + d.url;
            const f = form.elements.logo_file.files[0];
            if (f) d.logo = await uploadImage(f, table, 600); else if (checked(form, 'remove_logo')) d.logo = '';
            if (edit) check(await sb.from(table).update(d).eq('id', edit.id).select());
            else check(await sb.from(table).insert({ ...d, sort: rows.length + 1 }).select());
            toast(`${noun} kaydedildi.`); go(`#/${route}`); window.dispatchEvent(new HashChangeEvent('hashchange'));
          });
        });
      },
    };
  };
}

export const references = logoList({
  table: 'refs', route: 'referanslar', title: 'Referanslar', noun: 'Referans',
  fields: [['name', 'Müşteri / proje adı *'], ['project', 'Yapılan iş', 'textarea'], ['sector', 'Sektör (örn. Konut, Sanayi)'], ['city', 'Şehir / ilçe'], ['year', 'Yıl']],
  meta: (x) => [x.sector, x.city, x.year].filter(Boolean).join(' · '),
});
export const partners = logoList({
  table: 'partners', route: 'cozum-ortaklari', title: 'Çözüm Ortakları', noun: 'Çözüm ortağı',
  fields: [['name', 'Firma adı *'], ['kind', 'İş birliği türü (örn. Montaj, Tedarik)'], ['description', 'Kısa açıklama', 'textarea'], ['url', 'Web sitesi (örn. firma.com.tr)']],
  meta: (x) => [x.kind, x.url].filter(Boolean).join(' · '),
});
