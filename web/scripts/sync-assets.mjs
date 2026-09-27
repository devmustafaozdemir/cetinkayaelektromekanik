// Copies the shared front-end assets (CSS, site.js, 3D viewer, admin bundle, images)
// from ../assets into public/assets. Sources (assets/src) are skipped.
import { cpSync, rmSync, existsSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const src = join(root, '..', 'assets');
const dst = join(root, 'public', 'assets');
if (!existsSync(src)) {
  console.log('assets klasörü bulunamadı, public/assets olduğu gibi kullanılıyor.');
  process.exit(0);
}
rmSync(dst, { recursive: true, force: true });
cpSync(src, dst, { recursive: true, filter: (p) => !p.includes(`${join(src, 'src')}`) && !p.endsWith('.htaccess') });
console.log('assets → public/assets kopyalandı');
