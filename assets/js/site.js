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
    window.addEventListener('resize', () => { if (window.innerWidth > 1140) setOpen(false); });
  }

  /* ---------- Mobile nav: sub-menus open with their arrow ---------- */
  $$('.sub-toggle').forEach((b) => b.addEventListener('click', () => {
    const li = b.closest('li');
    const open = !li.classList.contains('is-open');
    li.classList.toggle('is-open', open);
    b.setAttribute('aria-expanded', String(open));
  }));

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

  /* ---------- Modular tank: drawing + designer ---------- */
  // 1 modül = 1,08 m; en, boy ve yükseklik tam/yarım modül (1,08 / 0,54 m) adımlarla.
  // Drawing mirrors svg_tank() in app/models.php; 3D in assets/src/viewer3d.js.
  const MOD = 1.08;
  const MATERIALS = {
    galvaniz: ['#c3cbd4', '#8e9aa8', '#eef1f4', '#a4afbb'],
    paslanmaz: ['#dfe4e9', '#a5afba', '#ffffff', '#bcc5ce'],
    grp: ['#7fb0d1', '#4d7fa3', '#c1dcee', '#6b9cc0'],
    sandvic: ['#ecedea', '#b6b8b2', '#ffffff', '#d2d4ce'],
  };
  const MAT_LABELS = { galvaniz: 'galvaniz', paslanmaz: 'paslanmaz çelik', grp: 'GRP', sandvic: 'izolasyonlu' };
  const r3 = (x) => Math.round(x * 1000) / 1000;
  /** Panel cells along a side of `a` modules: [[start, size], …] – full modules, then a half one. */
  const cells = (a) => {
    const n = Math.floor(a + 1e-9);
    const out = [];
    for (let i = 0; i < n; i++) out.push([i, 1]);
    if (a - n > 1e-9) out.push([n, a - n]);
    return out;
  };
  // wall rows from the top: the half row (if any) sits at the top of the tank
  const rowsTop = (b) => cells(b).map(([y, z]) => [b - y - z, z]);
  const nf1 = new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 1 });
  const nf2 = new Intl.NumberFormat('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const nf0 = new Intl.NumberFormat('tr-TR', { maximumFractionDigits: 0 });
  const fmtMod = (x) => nf1.format(x);
  const fmtM = (x) => nf2.format(x * MOD);

  function tankSVG(variant, W, L, H, opt = {}) {
    const [base, dark, light, rib] = MATERIALS[variant] || MATERIALS.galvaniz;
    const id = opt.id || 'sz';
    const dims = !!opt.dims;
    const flat = variant === 'sandvic';
    const c = 0.8660254, h = 0.5, vw = 480, vh = 330, bh = 7;
    const [mL, mR, mT, mB] = dims ? [34, 52, 26, 40] : [30, 30, 30, 36];
    const s = Math.min((vw - mL - mR) / ((W + L) * c), (vh - mT - mB - bh) / ((W + L) * h + H));
    const boxW = (W + L) * c * s, boxH = ((W + L) * h + H) * s + bh;
    const ox = mL + (vw - mL - mR - boxW) / 2 + L * c * s;
    const oy = mT + (vh - mT - mB - boxH) / 2 + H * s;
    const f = (...v) => v.map(r3).join(',');
    const face = (m, a, b, tint, op, wall) => {
      let g = `<g transform="matrix(${m})"><rect width="${r3(a)}" height="${r3(b)}" fill="${rib}"/>`;
      for (const [x0, cw] of cells(a)) for (const [y0, ch] of (wall ? rowsTop(b) : cells(b))) {
        g += `<rect x="${r3(x0 + 0.025)}" y="${r3(y0 + 0.025)}" width="${r3(cw - 0.05)}" height="${r3(ch - 0.05)}" rx=".05" fill="url(#${id}pn)"/>`;
        const mg = Math.min(cw, ch) * 0.2 + 0.02;
        const bw = r3(cw - 2 * mg), bb = r3(ch - 2 * mg);
        if (flat) g += `<rect x="${r3(x0 + mg)}" y="${r3(y0 + mg)}" width="${bw}" height="${bb}" rx=".04" fill="#fff" opacity=".22"/>`;
        else g += `<rect x="${r3(x0 + mg + 0.035)}" y="${r3(y0 + mg + 0.035)}" width="${bw}" height="${bb}" rx=".09" fill="${dark}" opacity=".45"/>`
          + `<rect x="${r3(x0 + mg)}" y="${r3(y0 + mg)}" width="${bw}" height="${bb}" rx=".09" fill="url(#${id}bs)"/>`;
      }
      if (op > 0) g += `<rect width="${r3(a)}" height="${r3(b)}" fill="${tint}" opacity="${op}"/>`;
      return g + '</g>';
    };
    const lmA = [c * s, h * s, 0, s, ox - L * c * s, oy + L * h * s - H * s];
    const lm = f(...lmA);
    const rm = f(c * s, -h * s, 0, s, ox + (W - L) * c * s, oy + (W + L) * h * s - H * s);
    const tm = f(c * s, h * s, -c * s, h * s, ox, oy - H * s);
    const A = [ox - L * c * s, oy + L * h * s];
    const B = [ox + (W - L) * c * s, oy + (W + L) * h * s];
    const C = [ox + W * c * s, oy + W * h * s];
    let o = `<svg class="art" viewBox="0 0 ${vw} ${vh}" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><defs>`
      + `<filter id="${id}blur" x="-20%" y="-50%" width="140%" height="200%"><feGaussianBlur stdDeviation="7"/></filter>`
      + `<linearGradient id="${id}pn" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="${light}"/><stop offset="1" stop-color="${base}"/></linearGradient>`
      + `<linearGradient id="${id}bs" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fff"/><stop offset=".45" stop-color="${light}"/><stop offset="1" stop-color="${base}"/></linearGradient></defs>`;
    // ground shadow + steel base
    o += `<ellipse cx="${r3((A[0] + C[0]) / 2)}" cy="${r3(B[1] + 12)}" rx="${r3(boxW / 1.9)}" ry="14" fill="#1f3a60" opacity=".22" filter="url(#${id}blur)"/>`;
    o += `<path d="M${f(...A)} L${f(...B)} L${f(...C)} l0,${bh} L${f(B[0], B[1] + bh)} L${f(A[0], A[1] + bh)}z" fill="#3b4d66"/>`;
    o += `<path d="M${f(...A)} L${f(...B)} L${f(...C)}" fill="none" stroke="#6d7f96" stroke-width="1.2"/>`;
    o += face(lm, W, H, '#ffffff', 0, true) + face(rm, L, H, '#1f3a60', 0.16, true) + face(tm, W, L, '#ffffff', 0.28, false);
    // roof: manhole + vent
    const mx = Math.min(0.62, W / 2), my = Math.min(0.62, L / 2);
    o += `<g transform="matrix(${tm})"><circle cx="${mx}" cy="${my}" r=".32" fill="${dark}"/><circle cx="${r3(mx - 0.02)}" cy="${r3(my - 0.02)}" r=".26" fill="url(#${id}bs)"/><circle cx="${r3(mx - 0.02)}" cy="${r3(my - 0.02)}" r=".26" fill="none" stroke="${dark}" stroke-width=".03"/>`;
    if (W + L >= 3) o += `<circle cx="${r3(W - 0.45)}" cy="${r3(L - 0.45)}" r=".13" fill="#3b4d66"/><circle cx="${r3(W - 0.47)}" cy="${r3(L - 0.47)}" r=".07" fill="#8b9bb0"/>`;
    o += '</g>';
    // right face: level gauge + ladder (rails continue above the roof)
    const gh = Math.max(0.12, H - 0.35);
    o += `<g transform="matrix(${rm})"><rect x=".25" y=".2" width=".1" height="${r3(gh)}" rx=".05" fill="#fff" opacity=".9"/><rect x=".27" y="${r3(0.2 + gh * 0.3)}" width=".06" height="${r3(gh * 0.7)}" rx=".03" fill="#3b8fd1"/>`;
    const lx = Math.max(0.55, L - 0.75), top = -0.42, bot = H + bh / s;
    let rungs = '';
    for (let y = H - 0.22; y > top + 0.1; y -= 0.26) rungs += `M${r3(lx)},${r3(y)}h.34`;
    o += `<path d="M${r3(lx)},${r3(bot)}V${top}M${r3(lx + 0.34)},${r3(bot)}V${top}${rungs}" fill="none" stroke="#56677b" stroke-width="1.6" vector-effect="non-scaling-stroke"/>`
      + `<path d="M${r3(lx)},${top}q.17,-.2 .34,0" fill="none" stroke="#56677b" stroke-width="1.6" vector-effect="non-scaling-stroke"/></g>`;
    // left face: inlet (top) and outlet (bottom) nozzles
    const pt = (x, y) => [lmA[0] * x + lmA[2] * y + lmA[4], lmA[1] * x + lmA[3] * y + lmA[5]];
    const nozzle = (x, y, r) => {
      const [px, py] = pt(x, y);
      const dx = -c * 0.3 * s, dy = h * 0.3 * s;
      return `<g transform="matrix(${lm})"><circle cx="${r3(x)}" cy="${r3(y)}" r="${r3(r * 1.5)}" fill="#56677b"/></g>`
        + `<line x1="${r3(px)}" y1="${r3(py)}" x2="${r3(px + dx)}" y2="${r3(py + dy)}" stroke="#8494a6" stroke-width="${r3(2 * r * s)}"/>`
        + `<g transform="translate(${r3(dx)},${r3(dy)})"><g transform="matrix(${lm})"><circle cx="${r3(x)}" cy="${r3(y)}" r="${r3(r * 1.45)}" fill="#6d7d90"/><circle cx="${r3(x)}" cy="${r3(y)}" r="${r3(r * 0.8)}" fill="#2c3a4c"/></g></g>`;
    };
    o += nozzle(0.5, Math.min(0.4, H * 0.35), 0.075);
    if (W >= 1.5 || H >= 1) o += nozzle(Math.min(1.5, W - 0.5), H - Math.min(0.35, H * 0.35), 0.09);
    // dimension lines
    if (dims) {
      const ln = 'stroke="#1f3a60" stroke-opacity=".55" stroke-width="1"';
      const txt = (x, y, rot, t) => `<text x="${r3(x)}" y="${r3(y)}" transform="rotate(${rot} ${r3(x)} ${r3(y)})" text-anchor="middle" dominant-baseline="middle" font-size="12.5" font-weight="700" fill="#1f3a60" stroke="#fff" stroke-width="3.5" paint-order="stroke" stroke-linejoin="round">${t}</text>`;
      const dim = (P, Q, n, rot, label) => {
        const off = 14, P2 = [P[0] + n[0] * off, P[1] + bh + n[1] * off], Q2 = [Q[0] + n[0] * off, Q[1] + bh + n[1] * off];
        const tick = (X) => `M${f(X[0] - n[0] * 4, X[1] - n[1] * 4)}L${f(X[0] + n[0] * 4, X[1] + n[1] * 4)}`;
        return `<path d="M${f(P[0] + n[0] * 3, P[1] + bh + n[1] * 3)}L${f(P2[0] + n[0] * 3, P2[1] + n[1] * 3)}M${f(Q[0] + n[0] * 3, Q[1] + bh + n[1] * 3)}L${f(Q2[0] + n[0] * 3, Q2[1] + n[1] * 3)}M${f(...P2)}L${f(...Q2)}${tick(P2)}${tick(Q2)}" fill="none" ${ln}/>`
          + txt((P2[0] + Q2[0]) / 2 + n[0] * 12, (P2[1] + Q2[1]) / 2 + n[1] * 12, rot, label);
      };
      o += dim(A, B, [-c, h], 30, `${fmtM(W)} m`) + dim(B, C, [c, h], -30, `${fmtM(L)} m`);
      const x = C[0] + 16, y1 = C[1] + bh, y2 = C[1] - H * s;
      o += `<path d="M${r3(C[0] + 4)},${r3(y1)}H${r3(x + 3)}M${r3(C[0] + 4)},${r3(y2)}H${r3(x + 3)}M${r3(x)},${r3(y1)}V${r3(y2)}M${r3(x - 4)},${r3(y1)}h8M${r3(x - 4)},${r3(y2)}h8" fill="none" ${ln}/>`
        + txt(x + 13, (y1 + y2) / 2, -90, `${fmtM(H)} m`);
    }
    return o + '</svg>';
  }

  /** Panel counts: walls, roof and floor. */
  function tankPanels(W, L, H) {
    const n = { full: 0, half: 0, quarter: 0 };
    const add = (a, b, times) => {
      const fa = Math.floor(a + 1e-9), ha = a - fa > 1e-9 ? 1 : 0, fb = Math.floor(b + 1e-9), hb = b - fb > 1e-9 ? 1 : 0;
      n.full += fa * fb * times; n.half += (fa * hb + ha * fb) * times; n.quarter += ha * hb * times;
    };
    add(W, H, 2); add(L, H, 2); add(W, L, 2);
    return n;
  }
  const tankVolume = (W, L, H) => W * L * H * MOD ** 3;

  // Relative wall cost per metre of perimeter: lower rows carry more pressure → thicker sheet (+15 % per row)
  const wallFactor = (H) => rowsTop(H).reduce((sum, [y, z]) => sum + z * (1 + 0.15 * (y + z - 1)), 0);
  /** Tank options for a required volume (m³), cheapest (panel area weighted by sheet thickness, fewest half panels) first. */
  function tankOptions(need, lim = {}) {
    const maxH = lim.maxH || 3, maxA = lim.maxA || 20, maxB = lim.maxB || 20;
    const out = [];
    for (let h2 = 1; h2 <= maxH * 2; h2++) {
      const H = h2 / 2;
      for (let w2 = 2; w2 <= 40; w2++) {
        const W = w2 / 2;
        for (let l2 = 2; l2 <= w2; l2++) {
          const L = l2 / 2;
          if (!((W <= maxA && L <= maxB) || (W <= maxB && L <= maxA))) continue;
          const v = tankVolume(W, L, H);
          if (v < need) continue;
          const p = tankPanels(W, L, H);
          out.push({ W, L, H, v, cost: 2 * W * L + 2 * (W + L) * wallFactor(H) + 0.3 * (p.half + p.quarter) });
          break; // larger L only adds volume for this W/H
        }
      }
    }
    return out.sort((a, b) => a.cost - b.cost || a.v - b.v);
  }


  $$('[data-designer]').forEach((root) => {
    const st = { w: 4, l: 3, h: 2, material: 'galvaniz' };
    const init = new URLSearchParams(location.search);
    const m0 = (init.get('olcu') || '').match(/^(\d+(?:[.,]5)?)x(\d+(?:[.,]5)?)x(\d(?:[.,]5)?)$/);
    if (m0 && root.dataset.designer === 'full') [st.w, st.l, st.h] = m0.slice(1).map((x) => parseFloat(x.replace(',', '.')));
    if (MATERIALS[init.get('malzeme')] && root.dataset.designer === 'full') st.material = init.get('malzeme');
    let need = null; // set when the tank comes from the need calculator
    const svgBox = $('[data-svg]', root);
    const quote = $('[data-quote]', root);
    const quotePath = quote ? quote.getAttribute('href').split('?')[0] : '';
    const more = $('[data-more]', root);
    const morePath = more ? more.getAttribute('href').split('?')[0] : '';
    const sw = viewSwitch(root, () => ['tank:' + st.material, { w: st.w, l: st.l, h: st.h }]);
    const set = (sel, v) => $$(sel, root).forEach((el) => { el.textContent = v; });
    let t = null;

    function render() {
      $$('[data-dim]', root).forEach((o) => {
        const k = o.dataset.dim;
        o.textContent = fmtMod(st[k]);
        const [dec, inc] = $$(`[data-step="${k}"]`, root);
        dec.disabled = st[k] <= +o.dataset.min;
        inc.disabled = st[k] >= +o.dataset.max;
      });
      $$('[data-dim-m]', root).forEach((o) => { o.textContent = `${fmtM(st[o.dataset.dimM])} m`; });
      $$('input[name="material"]', root).forEach((r) => { r.checked = r.value === st.material; });
      const v = tankVolume(st.w, st.l, st.h);
      const p = tankPanels(st.w, st.l, st.h);
      set('[data-volume]', nf1.format(v));
      set('[data-litres]', nf0.format(Math.round(v * 1000 / 10) * 10));
      set('[data-flats]', nf0.format(Math.max(1, Math.floor(v * 1000 / 600))));
      set('[data-people]', nf0.format(Math.max(1, Math.floor(v * 1000 / 150))));
      set('[data-outer]', `${fmtM(st.w)} × ${fmtM(st.l)} × ${fmtM(st.h)} m`);
      set('[data-modules]', `${fmtMod(st.w)} × ${fmtMod(st.l)} × ${fmtMod(st.h)} modül`);
      set('[data-footprint]', `${nf1.format(st.w * st.l * MOD * MOD)} m²`);
      set('[data-p-full]', p.full); set('[data-p-half]', p.half); set('[data-p-quarter]', p.quarter);
      set('[data-p-total]', p.full + p.half + p.quarter);
      $$('[data-p-row="half"]', root).forEach((el) => { el.hidden = !p.half; });
      $$('[data-p-row="quarter"]', root).forEach((el) => { el.hidden = !p.quarter; });
      $$('[data-big]', root).forEach((el) => { el.hidden = v <= 1000; });
      $$('[data-need-note]', root).forEach((el) => {
        el.hidden = !need;
        if (need) el.textContent = v >= need.total ? `İhtiyacınız ${nf1.format(need.total)} m³ — bu depo karşılıyor.` : `Dikkat: ihtiyacınız ${nf1.format(need.total)} m³, bu depo ${nf1.format(v)} m³.`;
        el.classList.toggle('is-warn', !!need && v < need.total);
      });
      svgBox.innerHTML = tankSVG(st.material, st.w, st.l, st.h, { dims: root.dataset.designer === 'full', id: 'dz' });
      const olcu = `${st.w}x${st.l}x${st.h}`;
      if (quote) {
        const lines = [
          `Depo: ${fmtMod(st.w)} × ${fmtMod(st.l)} × ${fmtMod(st.h)} modül (${fmtM(st.w)} × ${fmtM(st.l)} × ${fmtM(st.h)} m)`,
          `Hacim: ${nf1.format(v)} m³ · Malzeme: ${MAT_LABELS[st.material]}`,
          `Panel: ${p.full} tam (108×108)${p.half ? `, ${p.half} yarım (108×54)` : ''}${p.quarter ? `, ${p.quarter} çeyrek (54×54)` : ''}`,
        ];
        if (need) lines.push(`İhtiyaç: ${need.formula} = ${nf1.format(need.total)} m³`);
        quote.href = `${quotePath}?${new URLSearchParams({ olcu, malzeme: st.material, detay: lines.join('\n') })}`;
      }
      if (more) more.href = `${morePath}?olcu=${olcu}&malzeme=${st.material}`;
      clearTimeout(t);
      t = setTimeout(() => sw.refresh(), 120);
    }
    const apply = (o) => { st.w = o.W; st.l = o.L; st.h = o.H; render(); };

    $$('[data-step]', root).forEach((b) => b.addEventListener('click', () => {
      const k = b.dataset.step;
      const o = $(`[data-dim="${k}"]`, root);
      st[k] = Math.min(+o.dataset.max, Math.max(+o.dataset.min, st[k] + +b.dataset.delta));
      need = null;
      render();
    }));
    $$('input[name="material"]', root).forEach((r) => r.addEventListener('change', () => { st.material = r.value; render(); }));
    $$('[data-preset]', root).forEach((b) => b.addEventListener('click', () => {
      const best = tankOptions(+b.dataset.preset, { maxH: 3 })[0];
      need = null;
      if (best) apply(best);
    }));

    // Tabs
    const tabs = $$('[data-tab]', root);
    tabs.forEach((b) => b.addEventListener('click', () => {
      tabs.forEach((x) => { const on = x === b; x.setAttribute('aria-selected', String(on)); x.tabIndex = on ? 0 : -1; });
      $$('[data-pane]', root).forEach((p) => { p.hidden = p.dataset.pane !== b.dataset.tab; });
      if (b.dataset.tab === 'ihtiyac') calcNeed(true);
    }));

    // Need calculator
    const nform = $('[data-need-form]', root);
    const optBox = $('[data-options]', root);
    let days = 1;
    function calcNeed(applyBest) {
      if (!nform) return;
      const uo = nform.elements.use.selectedOptions[0];
      const use = [uo.text, +uo.dataset.lpd, uo.dataset.count, uo.dataset.hint];
      const people = Math.max(0, +nform.elements.people.value || 0);
      const lpd = Math.max(0, +nform.elements.lpd.value || 0);
      const fire = Math.max(0, +nform.elements.fire.value || 0);
      const daily = people * lpd / 1000;
      const total = daily * days + fire;
      const toMods = (m) => (m > 0 ? Math.floor(m / MOD * 2) / 2 : 20);
      const lim = { maxH: +nform.elements.maxh.value || 3, maxA: toMods(+nform.elements.maxa.value), maxB: toMods(+nform.elements.maxb.value) };
      const formula = `${nf0.format(people)} × ${nf0.format(lpd)} L × ${nf1.format(days)} gün${fire ? ` + ${nf1.format(fire)} m³ yangın` : ''}`;
      $('[data-need-total]', root).textContent = `${nf1.format(total)} m³`;
      $('[data-need-formula]', root).textContent = `Günlük ${nf1.format(daily)} m³ · ${formula}`;
      $('[data-count-label]', root).textContent = use[2];
      $('[data-use-hint]', root).textContent = use[3];
      if (total <= 0) { optBox.innerHTML = '<p class="muted small">Kişi sayısı ve günlük tüketimi girin.</p>'; return; }
      const all = tankOptions(total, lim);
      if (!all.length) { optBox.innerHTML = '<p class="designer__warn">Bu hacim seçtiğiniz alana sığmıyor. Birden fazla depo veya proje bazlı çözüm için bizimle görüşün.</p>'; return; }
      const best = all[0];
      const pick = [['Önerilen', best]];
      const lower = all.find((o) => o.H === best.H - 0.5) || all.find((o) => o.H < best.H);
      const taller = all.find((o) => o.H === best.H + 0.5) || all.find((o) => o.H > best.H);
      if (lower) pick.push(['Daha alçak', lower]);
      if (taller) pick.push(['Daha küçük taban', taller]);
      need = { total, formula };
      optBox.innerHTML = pick.map(([label, o], i) => `<button type="button" class="opt${i === 0 ? ' is-best' : ''}" data-opt="${i}" aria-pressed="false">
        <span class="opt__label">${label}</span>
        <strong>${fmtMod(o.W)} × ${fmtMod(o.L)} × ${fmtMod(o.H)} <small>modül</small></strong>
        <span class="opt__meta">${nf1.format(o.v)} m³ · ${fmtM(o.W)} × ${fmtM(o.L)} m taban · ${fmtM(o.H)} m yükseklik</span></button>`).join('');
      const choose = (i) => {
        $$('[data-opt]', optBox).forEach((b) => b.setAttribute('aria-pressed', String(+b.dataset.opt === i)));
        apply(pick[i][1]);
      };
      $$('[data-opt]', optBox).forEach((b) => b.addEventListener('click', () => { need = { total, formula }; choose(+b.dataset.opt); }));
      if (applyBest) choose(0);
    }
    if (nform) {
      nform.addEventListener('submit', (e) => e.preventDefault());
      nform.elements.use.addEventListener('change', () => { nform.elements.lpd.value = nform.elements.use.selectedOptions[0].dataset.lpd; calcNeed(true); });
      $$('[data-days]', nform).forEach((b) => b.addEventListener('click', () => {
        days = +b.dataset.days;
        $$('[data-days]', nform).forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
        calcNeed(true);
      }));
      let nt = null;
      nform.addEventListener('input', (e) => { if (e.target.name === 'use') return; clearTimeout(nt); nt = setTimeout(() => calcNeed(true), 250); });
    }
    render();
  });

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

  /* ---------- Static (Supabase) mode: forms + search without a PHP server ---------- */
  const sbUrl = document.querySelector('meta[name="sb-url"]')?.content;
  const sbKey = document.querySelector('meta[name="sb-key"]')?.content;
  const siteBase = document.querySelector('meta[name="site-base"]')?.content || '/';
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  async function rpc(fn, payload) {
    const res = await fetch(`${sbUrl}/rest/v1/rpc/${fn}`, {
      method: 'POST',
      headers: { apikey: sbKey, 'Content-Type': 'application/json', ...(sbKey.startsWith('eyJ') ? { Authorization: `Bearer ${sbKey}` } : {}) }, // legacy anon JWT or new publishable key
      body: JSON.stringify({ payload }),
    });
    const data = await res.json().catch(() => null);
    if (!res.ok) throw new Error((data && data.message) || 'Gönderilemedi. Lütfen bizi arayın.');
    return data;
  }

  // Pre-fill the quote form from the tank designer (?olcu=4x3x2.5&malzeme=grp&detay=…)
  const qf = $('form[data-remote="quote"]');
  const params = new URLSearchParams(location.search);
  const olcu = (params.get('olcu') || '').match(/^(\d+(?:[.,]5)?)x(\d+(?:[.,]5)?)x(\d(?:[.,]5)?)$/);
  if (qf && olcu) {
    const [w, l, h] = olcu.slice(1).map((x) => parseFloat(x.replace(',', '.')));
    const q = $('[name="quantity"]', qf);
    if (q && !q.value) q.value = `${fmtMod(w)} × ${fmtMod(l)} × ${fmtMod(h)} modül · ${nf1.format(tankVolume(w, l, h))} m³ ${MAT_LABELS[params.get('malzeme')] || 'galvaniz'} modüler depo`;
    const msg = $('[name="message"]', qf);
    if (msg && !msg.value && params.get('detay')) msg.value = params.get('detay').slice(0, 1000);
    const cat = $('[name="category"]', qf);
    if (cat && !cat.value) cat.value = 'Modüler Su Depoları';
  }

  if (sbUrl && sbKey) {
    $$('form[data-remote]').forEach((form) => form.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (form.dataset.invalid === '1') return;
      const kind = form.dataset.remote;
      const fd = new FormData(form);
      const payload = Object.fromEntries(fd.entries());
      delete payload._csrf;
      if (kind === 'quote') { payload.product_slug = payload.urun || ''; delete payload.urun; }
      const btn = $('button:not([type=button])', form);
      const label = btn ? btn.textContent : '';
      $('.alert', form.parentElement)?.remove();
      try {
        const result = await rpc(kind === 'quote' ? 'submit_quote' : 'submit_message', payload);
        const box = document.createElement('div');
        box.className = 'done';
        box.innerHTML = kind === 'quote'
          ? `<h2>Talebiniz bize ulaştı</h2><p>Satış ekibimiz <strong>${esc(payload.phone || '')}</strong> numarasından size dönecek. Görüşmede bu numarayı söylemeniz yeterli:</p><p class="done__code">${esc(result || '')}</p><div class="hero__actions"><a href="${siteBase}urunler/" class="btn btn--navy">Ürünlere dön</a></div>`
          : `<h2>Mesajınız gönderildi</h2><p>Mesai saatleri içinde size dönüş yapacağız.</p><a href="${siteBase}" class="btn btn--line">Ana sayfaya dön</a>`;
        form.replaceWith(box);
        box.scrollIntoView({ behavior: 'smooth', block: 'center' });
      } catch (err) {
        const a = document.createElement('div');
        a.className = 'alert';
        a.setAttribute('role', 'alert');
        a.textContent = err.message;
        form.prepend(a);
        if (btn) { btn.disabled = false; btn.textContent = label; }
      }
    }));

    // Search on static listing pages: filter the cards already on the page
    $$('form.search').forEach((form) => {
      const grid = $('.product-grid, .post-grid');
      if (!grid) return;
      const input = $('input[type="search"]', form);
      const items = $$('.product, .post', grid.parentElement);
      const norm = (t) => t.toLocaleLowerCase('tr-TR');
      const run = () => {
        const q = norm(input.value.trim());
        let shown = 0;
        items.forEach((it) => { const hit = !q || norm(it.textContent).includes(q); it.hidden = !hit; if (hit) shown++; });
        const bar = $('.catalog__bar p');
        if (bar) bar.textContent = q ? `${shown} ürün, “${input.value.trim()}” araması` : `${items.length} ürün`;
      };
      form.addEventListener('submit', (e) => { e.preventDefault(); run(); });
      input.addEventListener('input', run);
    });
  }

  /* ---------- Light client-side validation (the server validates too) ---------- */
  $$('form[data-validate]').forEach((form) => form.addEventListener('submit', (e) => {
    let first = null;
    $$('[required]', form).forEach((f) => {
      const ok = f.type === 'checkbox' ? f.checked : f.value.trim().length >= (parseInt(f.getAttribute('minlength'), 10) || 1);
      f.closest('.field, .check')?.classList.toggle('has-error', !ok);
      if (!ok && !first) first = f;
    });
    form.dataset.invalid = first ? '1' : '0';
    if (first) { e.preventDefault(); first.focus(); return; }
    const b = $('button:not([type=button])', form);
    if (b && !form.dataset.remote) { b.disabled = true; b.textContent = 'Gönderiliyor…'; }
    else if (b && sbUrl) { b.disabled = true; b.textContent = 'Gönderiliyor…'; }
  }, true));
})();
