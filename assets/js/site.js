(() => {
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const header = $('[data-header]');

  /* ---------- Mobile nav ---------- */
  const toggle = $('.nav-toggle');
  const nav = $('#nav');
  if (toggle && nav) {
    const setOpen = (open) => {
      if (open && header) nav.style.top = Math.max(0, header.getBoundingClientRect().bottom) + 'px';
      nav.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
      document.body.classList.toggle('nav-open', open);
    };
    toggle.addEventListener('click', () => setOpen(!nav.classList.contains('is-open')));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
    window.addEventListener('resize', () => { if (window.innerWidth > 960) setOpen(false); });
  }

  /* ---------- Photos fall back to the drawing if they fail ---------- */
  $$('img[data-fallback]').forEach((img) => {
    const fail = () => {
      const art = img.nextElementSibling;
      img.remove();
      if (art) art.hidden = false;
      img.closest('.photo')?.classList.add('is-fallback');
    };
    if (img.complete && img.naturalWidth === 0) fail(); else img.addEventListener('error', fail);
  });

  /* ---------- 3D viewer (lazy-loaded module) ---------- */
  let viewerModule = null;
  const loadViewer = (url) => (viewerModule ||= import(url));
  const webgl = (() => {
    try { const c = document.createElement('canvas'); return !!(c.getContext('webgl2') || c.getContext('webgl')); } catch (e) { return false; }
  })();

  /** Wires a 2D/3D switch. getSpec() returns [spec, dims]. Returns { refresh }. */
  function viewSwitch(root, getSpec) {
    const buttons = $$('[data-view]', root);
    const svgBox = $('[data-svg]', root);
    const host = $('[data-viewer-host]', root);
    const hint = $('[data-hint]', root);
    const url = root.dataset.viewer;
    let viewer = null;
    let mode = '2d';
    if (!webgl || !host || !url) {
      buttons.forEach((b) => { if (b.dataset.view === '3d') b.hidden = true; });
      return { refresh() {} };
    }
    const setPressed = () => buttons.forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.view === mode)));
    buttons.forEach((b) => b.addEventListener('click', async () => {
      if (b.dataset.view === mode) return;
      mode = b.dataset.view;
      setPressed();
      if (mode === '3d') {
        host.hidden = false;
        if (svgBox) svgBox.style.visibility = 'hidden';
        if (!viewer) {
          host.innerHTML = '<div class="viewer__loading">3D model yükleniyor…</div>';
          try {
            const mod = await loadViewer(url);
            host.innerHTML = '';
            const [spec, dims] = getSpec();
            viewer = mod.mount(host, spec, dims);
          } catch (e) {
            host.innerHTML = '<div class="viewer__loading">3D görünüm açılamadı. Çizim görünümüne dönün.</div>';
            return;
          }
        }
        if (hint) hint.hidden = false;
      } else {
        host.hidden = true;
        if (svgBox) svgBox.style.visibility = '';
        if (hint) hint.hidden = true;
      }
    }));
    return {
      refresh() { if (viewer) { const [spec, dims] = getSpec(); viewer.update(spec, dims); } },
    };
  }

  /* ---------- Tank sizer (home) ---------- */
  const MATERIALS = {
    galvaniz: ['#c9d0d7', '#a9b2bc', '#dde2e7', '#7f8a96'],
    paslanmaz: ['#e3e7eb', '#c3cad1', '#f1f3f5', '#8d98a3'],
    grp: ['#7fa9c4', '#5f8aa6', '#9cc0d6', '#406a86'],
    sandvic: ['#eeeeea', '#d3d4cf', '#f8f8f5', '#9a9c95'],
  };
  const r3 = (x) => Math.round(x * 1000) / 1000;

  // Mirrors svg_tank() in app/models.php
  function tankSVG(variant, W, L, H) {
    const [cl, cr, ct, rib] = MATERIALS[variant] || MATERIALS.galvaniz;
    const c = 0.8660254, h = 0.5, vw = 480, vh = 330, pad = 34;
    const s = Math.min((vw - 2 * pad) / ((W + L) * c), (vh - 2 * pad - 14) / ((W + L) * h + H + 0.35));
    const ox = vw / 2 + (L - W) * c * s / 2;
    const oy = (vh - ((W + L) * h * s + H * s)) / 2 + H * s - 4;
    const m = (...v) => v.map(r3).join(',');
    const face = (matrix, cols, rows, fill, dimples) => {
      let g = `<g transform="matrix(${matrix})">`;
      for (let i = 0; i < cols; i++) for (let j = 0; j < rows; j++) {
        g += `<rect x="${i + 0.015}" y="${j + 0.015}" width=".97" height=".97" fill="${fill}" stroke="${rib}" stroke-width=".03"/>`;
        if (dimples && variant !== 'sandvic') g += `<circle cx="${i + 0.5}" cy="${j + 0.5}" r=".27" fill="none" stroke="${rib}" stroke-width=".028" opacity=".75"/><circle cx="${i + 0.47}" cy="${j + 0.47}" r=".2" fill="#fff" opacity=".18"/>`;
        else if (dimples) g += `<rect x="${i + 0.12}" y="${j + 0.12}" width=".76" height=".76" fill="none" stroke="${rib}" stroke-width=".02" opacity=".5"/>`;
      }
      return g + '</g>';
    };
    const lm = m(c * s, h * s, 0, s, ox - L * c * s, oy + L * h * s - H * s);
    const rm = m(c * s, -h * s, 0, s, ox + (W - L) * c * s, oy + (W + L) * h * s - H * s);
    const tm = m(c * s, h * s, -c * s, h * s, ox, oy - H * s);
    const gx = L - 0.35;
    return `<svg class="art" viewBox="0 0 ${vw} ${vh}" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">`
      + `<ellipse cx="${r3(ox + (W - L) * c * s / 2)}" cy="${r3(oy + (W + L) * h * s + 10)}" rx="${r3((W + L) * c * s / 1.8)}" ry="12" fill="#13243d" opacity=".12"/>`
      + `<path d="M${m(ox - L * c * s, oy + L * h * s)} L${m(ox + (W - L) * c * s, oy + (W + L) * h * s)} L${m(ox + W * c * s, oy + W * h * s)} l0,7 L${m(ox + (W - L) * c * s, oy + (W + L) * h * s + 7)} L${m(ox - L * c * s, oy + L * h * s + 7)}z" fill="#2d3d52"/>`
      + face(lm, W, H, cl, true) + face(rm, L, H, cr, true) + face(tm, W, L, ct, false)
      + `<g transform="matrix(${tm})"><circle cx=".75" cy=".75" r=".32" fill="${cr}" stroke="${rib}" stroke-width=".04"/><circle cx=".75" cy=".75" r=".22" fill="none" stroke="${rib}" stroke-width=".03"/><circle cx="${W - 0.5}" cy="${L - 0.5}" r=".12" fill="#2d3d52"/></g>`
      + `<g transform="matrix(${rm})"><rect x="${r3(gx - 0.05)}" y=".25" width=".1" height="${r3(H - 0.45)}" rx=".05" fill="#f8fafc" stroke="#2d3d52" stroke-width=".025"/><rect x="${r3(gx - 0.035)}" y="${r3(0.25 + (H - 0.45) * 0.3)}" width=".07" height="${r3((H - 0.45) * 0.7)}" rx=".035" fill="#2b7fb8"/></g>`
      + `<g transform="matrix(${lm})"><circle cx=".5" cy=".45" r=".13" fill="#2d3d52"/><circle cx=".5" cy=".45" r=".07" fill="#6b7a8c"/></g>`
      + '</svg>';
  }

  const sizer = $('[data-sizer]');
  if (sizer) {
    const dims = { w: 4, l: 3, h: 2 };
    let material = 'galvaniz';
    const nf = new Intl.NumberFormat('tr-TR');
    const svgBox = $('[data-svg]', sizer);
    const quote = $('[data-quote]', sizer);
    const quotePath = quote.getAttribute('href').split('?')[0];
    const sw = viewSwitch(sizer, () => ['tank:' + material, { ...dims }]);
    let t = null;
    const render = () => {
      $$('[data-dim]', sizer).forEach((o) => {
        const k = o.dataset.dim;
        o.textContent = dims[k];
        const [dec, inc] = $$(`[data-step="${k}"]`, sizer);
        dec.disabled = dims[k] <= +o.dataset.min;
        inc.disabled = dims[k] >= +o.dataset.max;
      });
      const vol = dims.w * dims.l * dims.h;
      $('[data-volume]', sizer).textContent = nf.format(vol);
      $('[data-litres]', sizer).textContent = nf.format(vol * 1000);
      // 4 people × 150 L per flat per day
      $('[data-flats]', sizer).textContent = nf.format(Math.max(1, Math.floor(vol * 1000 / 600)));
      svgBox.innerHTML = tankSVG(material, dims.w, dims.l, dims.h);
      quote.href = `${quotePath}?olcu=${dims.w}x${dims.l}x${dims.h}&malzeme=${material}`;
      clearTimeout(t);
      t = setTimeout(() => sw.refresh(), 120);
    };
    $$('[data-step]', sizer).forEach((b) => b.addEventListener('click', () => {
      const k = b.dataset.step;
      const o = $(`[data-dim="${k}"]`, sizer);
      dims[k] = Math.min(+o.dataset.max, Math.max(+o.dataset.min, dims[k] + +b.dataset.delta));
      render();
    }));
    $$('input[name="material"]', sizer).forEach((r) => r.addEventListener('change', () => { material = r.value; render(); }));
    render();
  }

  /* ---------- Product page 2D / 3D ---------- */
  $$('[data-model-view]').forEach((root) => {
    viewSwitch(root, () => [root.dataset.model, {}]);
  });

  /* ---------- Reading progress + TOC ---------- */
  const bar = $('[data-progress]');
  const content = $('.post-content');
  if (bar && content) {
    const links = $$('.toc a');
    const heads = links.map((a) => document.getElementById(decodeURIComponent(a.hash.slice(1)))).filter(Boolean);
    const update = () => {
      const r = content.getBoundingClientRect();
      const total = r.height - window.innerHeight * 0.6;
      bar.style.width = Math.max(0, Math.min(100, (-r.top / total) * 100)) + '%';
      let current = null;
      heads.forEach((h) => { if (h.getBoundingClientRect().top < 140) current = h.id; });
      links.forEach((a) => a.classList.toggle('is-active', a.hash.slice(1) === current));
    };
    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  /* ---------- Copy buttons ---------- */
  $$('[data-copy]').forEach((btn) => btn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(btn.dataset.copy);
      btn.setAttribute('aria-label', 'Bağlantı kopyalandı');
      btn.style.borderColor = 'var(--navy)';
    } catch (e) { /* clipboard unavailable */ }
  }));

  /* ---------- Light client-side validation (the server validates too) ---------- */
  $$('form[data-validate]').forEach((form) => form.addEventListener('submit', (e) => {
    let first = null;
    $$('[required]', form).forEach((f) => {
      const ok = f.type === 'checkbox' ? f.checked : f.value.trim().length >= (parseInt(f.getAttribute('minlength'), 10) || 1);
      f.closest('.field, .check')?.classList.toggle('has-error', !ok);
      if (!ok && !first) first = f;
    });
    if (first) { e.preventDefault(); first.focus(); return; }
    const b = $('button:not([type=button])', form);
    if (b) { b.disabled = true; b.textContent = 'Gönderiliyor…'; }
  }));
})();
