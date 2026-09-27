(() => {
  document.documentElement.classList.add('js');

  // Sticky header shadow
  const header = document.querySelector('[data-header]');
  const onScroll = () => header && header.classList.toggle('is-scrolled', window.scrollY > 8);
  onScroll();
  window.addEventListener('scroll', onScroll, { passive: true });

  // Mobile nav
  const toggle = document.querySelector('.nav-toggle');
  const nav = document.getElementById('nav');
  if (toggle && nav) {
    const setOpen = (open) => {
      nav.classList.toggle('is-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Menüyü kapat' : 'Menüyü aç');
      document.body.classList.toggle('nav-open', open);
    };
    toggle.addEventListener('click', () => setOpen(!nav.classList.contains('is-open')));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setOpen(false); });
    window.addEventListener('resize', () => { if (window.innerWidth > 960) setOpen(false); });
  }

  // Reveal on scroll
  const revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => {
        if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    revealEls.forEach((el, i) => { el.style.transitionDelay = (i % 4) * 70 + 'ms'; io.observe(el); });
  } else {
    revealEls.forEach((el) => el.classList.add('is-visible'));
  }

  // Count-up numbers (only for values that start with a number)
  const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.querySelectorAll('[data-count]').forEach((el) => {
    const raw = el.dataset.count;
    const m = raw.match(/^(\D*)(\d+)(.*)$/);
    if (!m || reduce || m[3].startsWith('/')) return;
    const target = parseInt(m[2], 10);
    const io = new IntersectionObserver(([en]) => {
      if (!en.isIntersecting) return;
      io.disconnect();
      const start = performance.now();
      const step = (t) => {
        const p = Math.min(1, (t - start) / 1200);
        el.textContent = m[1] + Math.round(target * (1 - Math.pow(1 - p, 3))) + m[3];
        if (p < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    });
    io.observe(el);
  });

  // Reading progress + TOC highlight
  const bar = document.querySelector('[data-progress]');
  const content = document.querySelector('.post-content');
  if (bar && content) {
    const tocLinks = [...document.querySelectorAll('.toc a')];
    const heads = tocLinks.map((a) => document.getElementById(decodeURIComponent(a.hash.slice(1)))).filter(Boolean);
    const update = () => {
      const r = content.getBoundingClientRect();
      const total = r.height - window.innerHeight * 0.6;
      bar.style.width = Math.max(0, Math.min(100, (-r.top / total) * 100)) + '%';
      let current = null;
      heads.forEach((h) => { if (h.getBoundingClientRect().top < 140) current = h.id; });
      tocLinks.forEach((a) => a.classList.toggle('is-active', a.hash.slice(1) === current));
    };
    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  // Copy buttons
  document.querySelectorAll('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(btn.dataset.copy);
        const old = btn.innerHTML;
        btn.classList.add('is-copied');
        btn.setAttribute('aria-label', 'Kopyalandı');
        if (btn.textContent.trim()) btn.textContent = 'Kopyalandı ✓';
        setTimeout(() => { btn.innerHTML = old; btn.classList.remove('is-copied'); }, 1600);
      } catch (e) { /* clipboard unavailable */ }
    });
  });

  // Light client-side validation (server validates too)
  document.querySelectorAll('form[data-validate]').forEach((form) => {
    form.addEventListener('submit', (e) => {
      let first = null;
      form.querySelectorAll('[required]').forEach((f) => {
        const ok = f.type === 'checkbox' ? f.checked : f.value.trim().length >= (parseInt(f.getAttribute('minlength'), 10) || 1);
        const wrap = f.closest('.field, .check');
        if (wrap) wrap.classList.toggle('has-error', !ok);
        if (!ok && !first) first = f;
      });
      if (first) { e.preventDefault(); first.focus(); }
      else { const b = form.querySelector('button:not([type=button])'); if (b) { b.disabled = true; b.style.opacity = '.7'; } }
    });
  });
})();
