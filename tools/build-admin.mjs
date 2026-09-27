// Bundles the Supabase admin app (assets/src/admin → assets/js/admin).
// Usage: npm run build:admin
import { build } from 'esbuild';
import { rmSync, mkdirSync, copyFileSync } from 'node:fs';

const out = 'assets/js/admin';
rmSync(out, { recursive: true, force: true });
mkdirSync(out, { recursive: true });
await build({
  entryPoints: ['assets/src/admin/app.js'],
  bundle: true,
  splitting: true,
  format: 'esm',
  minify: true,
  target: 'es2020',
  outdir: out,
  chunkNames: 'chunk-[hash]',
  legalComments: 'eof',
  logLevel: 'info',
});
copyFileSync('node_modules/quill/dist/quill.snow.css', `${out}/quill.snow.css`);
