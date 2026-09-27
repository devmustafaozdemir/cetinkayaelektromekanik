'use client';
import { useEffect } from 'react';

/**
 * Loads assets/js/site.js once React has finished hydrating the page (first idle
 * moment after mount), so its DOM changes (designer, counters, menus) never race
 * with hydration.
 */
export function SiteScript({ src }: { src: string }) {
  useEffect(() => {
    const load = () => {
      if (document.querySelector(`script[data-site-js]`)) return;
      const s = document.createElement('script');
      s.src = src;
      s.dataset.siteJs = '';
      document.body.appendChild(s);
    };
    const w = window as Window & { requestIdleCallback?: (cb: () => void, o?: { timeout: number }) => number };
    if (w.requestIdleCallback) w.requestIdleCallback(load, { timeout: 400 });
    else setTimeout(load, 50);
  }, [src]);
  return null;
}
