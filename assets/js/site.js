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
  // Mirrors tank_materials() / svg_tank() in app/models.php
  const MATERIALS = {
    galvaniz: ['#c3cbd4', '#8e9aa8', '#eef1f4', '#a4afbb'],
    paslanmaz: ['#dfe4e9', '#a5afba', '#ffffff', '#bcc5ce'],
    grp: ['#7fb0d1', '#4d7fa3', '#c1dcee', '#6b9cc0'],
    sandvic: ['#ecedea', '#b6b8b2', '#ffffff', '#d2d4ce'],
  };
  const r3 = (x) => Math.round(x * 1000) / 1000;
  function tankSVG(variant, W, L, H) {
    const [base, dark, light, rib] = MATERIALS[variant] || MATERIALS.galvaniz;
    const id = 'sz';
    const c = 0.8660254, h = 0.5, vw = 480, vh = 330, pad = 34;
    const s = Math.min((vw - 2 * pad) / ((W + L) * c), (vh - 2 * pad - 14) / ((W + L) * h + H + 0.35));
    const ox = vw / 2 + (L - W) * c * s / 2;
    const oy = (vh - ((W + L) * h * s + H * s)) / 2 + H * s - 4;
    const f = (...v) => v.map(r3).join(',');
    const flat = variant === 'sandvic';
    const face = (m, cols, rows, tint, op, dimples) => {
      let g = `<g transform="matrix(${m})"><rect x="0" y="0" width="${cols}" height="${rows}" fill="${rib}"/>`;
      for (let i = 0; i < cols; i++) for (let j = 0; j < rows; j++) {
        g += `<rect x="${i + 0.025}" y="${j + 0.025}" width=".95" height=".95" rx=".07" fill="url(#${id}pn)"/>`;
        if (dimples && !flat) g += `<circle cx="${i + 0.5}" cy="${j + 0.5}" r=".31" fill="url(#${id}dm)"/>`;
        else if (dimples) g += `<rect x="${i + 0.14}" y="${j + 0.14}" width=".72" height=".72" rx=".05" fill="#fff" opacity=".22"/>`;
      }
      if (op > 0) g += `<rect x="0" y="0" width="${cols}" height="${rows}" fill="${tint}" opacity="${op}"/>`;
      return g + '</g>';
    };
    const lm = f(c * s, h * s, 0, s, ox - L * c * s, oy + L * h * s - H * s);
    const rm = f(c * s, -h * s, 0, s, ox + (W - L) * c * s, oy + (W + L) * h * s - H * s);
    const tm = f(c * s, h * s, -c * s, h * s, ox, oy - H * s);
    const gx = L - 0.35, gh = H - 0.45;
    return `<svg class="art" viewBox="0 0 ${vw} ${vh}" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><defs>`
      + `<filter id="${id}blur" x="-20%" y="-50%" width="140%" height="200%"><feGaussianBlur stdDeviation="7"/></filter>`
      + `<linearGradient id="${id}pn" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="${light}"/><stop offset="1" stop-color="${base}"/></linearGradient>`
      + `<radialGradient id="${id}dm" cx=".42" cy=".38" r=".6"><stop offset="0" stop-color="#fff" stop-opacity=".85"/><stop offset=".45" stop-color="#fff" stop-opacity=".2"/><stop offset=".8" stop-color="${dark}" stop-opacity=".25"/><stop offset="1" stop-color="${dark}" stop-opacity=".55"/></radialGradient></defs>`
      + `<ellipse cx="${r3(ox + (W - L) * c * s / 2)}" cy="${r3(oy + (W + L) * h * s + 12)}" rx="${r3((W + L) * c * s / 1.9)}" ry="14" fill="#1f3a60" opacity=".22" filter="url(#${id}blur)"/>`
      + `<path d="M${f(ox - L * c * s, oy + L * h * s)} L${f(ox + (W - L) * c * s, oy + (W + L) * h * s)} L${f(ox + W * c * s, oy + W * h * s)} l0,7 L${f(ox + (W - L) * c * s, oy + (W + L) * h * s + 7)} L${f(ox - L * c * s, oy + L * h * s + 7)}z" fill="#3b4d66"/>`
      + face(lm, W, H, '#ffffff', 0, true) + face(rm, L, H, '#1f3a60', 0.16, true) + face(tm, W, L, '#ffffff', 0.28, false)
      + `<g transform="matrix(${tm})"><circle cx=".75" cy=".75" r=".33" fill="${dark}"/><circle cx=".73" cy=".73" r=".27" fill="url(#${id}pn)"/><circle cx=".73" cy=".73" r=".27" fill="url(#${id}dm)" opacity=".7"/><circle cx="${W - 0.5}" cy="${L - 0.5}" r=".13" fill="#3b4d66"/><circle cx="${W - 0.52}" cy="${L - 0.52}" r=".07" fill="#8b9bb0"/></g>`
      + `<g transform="matrix(${rm})"><rect x="${r3(gx - 0.06)}" y=".25" width=".12" height="${r3(gh)}" rx=".06" fill="#ffffff" opacity=".9"/><rect x="${r3(gx - 0.035)}" y="${r3(0.25 + gh * 0.3)}" width=".07" height="${r3(gh * 0.7)}" rx=".035" fill="#3b8fd1"/></g>`
      + `<g transform="matrix(${lm})"><circle cx=".5" cy=".45" r=".15" fill="#3b4d66"/><circle cx=".5" cy=".45" r=".09" fill="#9aa9bb"/><circle cx=".48" cy=".43" r=".04" fill="#e9eef3"/></g>`
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

  /* ---------- Count-up for the proof numbers ("1.200+", "%100", "20+") ---------- */
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!reduce && 'IntersectionObserver' in window) {
    const nf = new Intl.NumberFormat('tr-TR');
    $$('[data-count]').forEach((el) => {
      const m = el.dataset.count.match(/^(\D*)([\d.]+)(.*)$/);
      if (!m) return;
      const target = parseInt(m[2].replace(/\./g, ''), 10);
      if (!target || m[3].startsWith('/')) return;
      const io = new IntersectionObserver(([en]) => {
        if (!en.isIntersecting) return;
        io.disconnect();
        const t0 = performance.now();
        const tick = (t) => {
          const p = Math.min(1, (t - t0) / 1400);
          el.textContent = m[1] + nf.format(Math.round(target * (1 - Math.pow(1 - p, 3)))) + m[3];
          if (p < 1) requestAnimationFrame(tick); else el.textContent = el.dataset.count;
        };
        requestAnimationFrame(tick);
      }, { threshold: 0.4 });
      io.observe(el);
    });
  }

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
