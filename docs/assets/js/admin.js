(() => {
  const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];

  // Sidebar (mobile)
  const sidebar = $('#sidebar');
  const backdrop = $('.sidebar-backdrop');
  const setSidebar = (open) => { sidebar?.classList.toggle('is-open', open); backdrop?.classList.toggle('is-open', open); };
  $$('[data-sidebar-open]').forEach((b) => b.addEventListener('click', () => setSidebar(true)));
  $$('[data-sidebar-close]').forEach((b) => b.addEventListener('click', () => setSidebar(false)));

  // Dropdown
  $$('[data-dropdown]').forEach((btn) => {
    const menu = btn.nextElementSibling;
    btn.addEventListener('click', (e) => { e.stopPropagation(); menu.classList.toggle('is-open'); });
    document.addEventListener('click', () => menu.classList.remove('is-open'));
  });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { setSidebar(false); $$('.dropdown.is-open').forEach((d) => d.classList.remove('is-open')); } });

  // Toasts
  $$('[data-dismiss]').forEach((b) => b.addEventListener('click', () => b.parentElement.remove()));
  $$('.toast--success').forEach((t) => setTimeout(() => t.remove(), 5000));

  // Confirm dialogs
  $$('form[data-confirm]').forEach((f) => f.addEventListener('submit', (e) => { if (!confirm(f.dataset.confirm)) e.preventDefault(); }));

  // Copy
  $$('[data-copy]').forEach((b) => b.addEventListener('click', async () => {
    try { await navigator.clipboard.writeText(b.dataset.copy); b.title = 'Kopyalandı'; b.style.color = '#16a34a'; setTimeout(() => (b.style.color = ''), 1200); } catch (e) { /* ignore */ }
  }));

  // Slug generation
  const slugify = (s) => s.toLocaleLowerCase('tr-TR')
    .replace(/ç/g, 'c').replace(/ğ/g, 'g').replace(/ı/g, 'i').replace(/i̇/g, 'i').replace(/ö/g, 'o').replace(/ş/g, 's').replace(/ü/g, 'u')
    .replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  const slugSrc = $('[data-slug-source]');
  const slugTgt = $('[data-slug-target]');
  if (slugSrc && slugTgt) {
    let touched = slugTgt.value !== '';
    slugTgt.addEventListener('input', () => { touched = slugTgt.value !== ''; updateSerp(); });
    slugSrc.addEventListener('input', () => { if (!touched) slugTgt.value = slugify(slugSrc.value); updateSerp(); });
  }

  // SERP preview
  function updateSerp() {
    const t = $('[data-serp-title]'); if (!t) return;
    const title = $('[name=meta_title]')?.value || slugSrc?.value || 'Yazı başlığı';
    const desc = $('[name=meta_desc]')?.value || $('[name=excerpt]')?.value || 'Yazının özeti burada görünecek.';
    t.textContent = title;
    $('[data-serp-desc]').textContent = desc;
    $('[data-serp-slug]').textContent = slugTgt?.value || '';
  }
  ['meta_title', 'meta_desc', 'excerpt'].forEach((n) => $(`[name=${n}]`)?.addEventListener('input', updateSerp));

  // Character counters
  $$('[data-counter]').forEach((el) => {
    const ideal = parseInt(el.dataset.counter, 10) || parseInt(el.getAttribute('maxlength'), 10);
    const c = document.createElement('small');
    c.className = 'counter';
    el.after(c);
    const upd = () => { c.textContent = `${el.value.length} / ${ideal}`; c.classList.toggle('is-over', el.value.length > ideal); };
    el.addEventListener('input', upd); upd();
  });

  // Cover dropzone preview
  $$('[data-dropzone]').forEach((dz) => {
    const input = $('input[type=file]', dz);
    const img = $('[data-preview]', dz);
    const hint = $('.dropzone__hint', dz);
    ['dragenter', 'dragover'].forEach((ev) => dz.addEventListener(ev, () => dz.classList.add('is-over')));
    ['dragleave', 'drop'].forEach((ev) => dz.addEventListener(ev, () => dz.classList.remove('is-over')));
    input.addEventListener('change', () => {
      const f = input.files[0]; if (!f) return;
      img.src = URL.createObjectURL(f); img.hidden = false; hint.hidden = true;
    });
  });

  // Rich text editor (Quill)
  const editorEl = $('#editor');
  if (editorEl && window.Quill) {
    const quill = new Quill(editorEl, {
      theme: 'snow',
      placeholder: 'İçeriğinizi yazmaya başlayın…',
      modules: {
        toolbar: {
          container: [
            [{ header: [2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ list: 'ordered' }, { list: 'bullet' }],
            ['blockquote', 'link', 'image', 'video'],
            [{ align: [] }],
            ['clean'],
          ],
          handlers: {
            image() {
              const input = document.createElement('input');
              input.type = 'file'; input.accept = 'image/*';
              input.onchange = async () => {
                const file = input.files[0]; if (!file) return;
                const fd = new FormData(); fd.append('image', file);
                const range = quill.getSelection(true);
                try {
                  const res = await fetch('/admin/?p=posts&a=upload', { method: 'POST', body: fd, headers: { 'X-CSRF-Token': csrf } });
                  const data = await res.json();
                  if (!res.ok) throw new Error(data.error || 'Yükleme başarısız');
                  quill.insertEmbed(range.index, 'image', data.url, 'user');
                  quill.setSelection(range.index + 1);
                } catch (err) { alert(err.message); }
              };
              input.click();
            },
          },
        },
      },
    });
    const hidden = $('#content-input');
    const wc = $('[data-wordcount]');
    const sync = () => {
      hidden.value = quill.root.innerHTML === '<p><br></p>' ? '' : quill.root.innerHTML;
      if (wc) { const words = quill.getText().trim().split(/\s+/).filter(Boolean).length; wc.textContent = `${words} kelime · ~${Math.max(1, Math.ceil(words / 200))} dk okuma`; }
    };
    quill.on('text-change', sync); sync();
    let dirty = false;
    quill.on('text-change', () => { dirty = true; });
    $$('input, textarea, select', editorEl.closest('form')).forEach((i) => i.addEventListener('input', () => { dirty = true; }));
    editorEl.closest('form').addEventListener('submit', () => { sync(); dirty = false; });
    window.addEventListener('beforeunload', (e) => { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
  }

  // Fallback when the editor CDN is unreachable: plain contenteditable area.
  if (editorEl && !window.Quill) {
    const hidden = $('#content-input');
    editorEl.contentEditable = 'true';
    editorEl.classList.add('editor--plain');
    const sync = () => { hidden.value = editorEl.innerHTML.trim(); };
    editorEl.addEventListener('input', sync); sync();
    editorEl.closest('form').addEventListener('submit', sync);
  }

  // Drag & drop sorting
  $$('[data-sortable]').forEach((list) => {
    let dragging = null;
    list.addEventListener('dragstart', (e) => { dragging = e.target.closest('.sortable__item'); dragging?.classList.add('is-dragging'); });
    list.addEventListener('dragend', () => {
      dragging?.classList.remove('is-dragging'); dragging = null;
      const fd = new FormData();
      $$('.sortable__item', list).forEach((li) => fd.append('order[]', li.dataset.id));
      fetch(list.dataset.sortable, { method: 'POST', body: fd, headers: { 'X-CSRF-Token': csrf } });
    });
    list.addEventListener('dragover', (e) => {
      e.preventDefault();
      const over = e.target.closest('.sortable__item');
      if (!over || over === dragging || !dragging) return;
      const r = over.getBoundingClientRect();
      over.parentNode.insertBefore(dragging, e.clientY > r.top + r.height / 2 ? over.nextSibling : over);
    });
  });
})();
