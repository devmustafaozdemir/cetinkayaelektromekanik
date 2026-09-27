// Builds a self-contained folder for Node.js hosting: deploy/ (+ deploy.zip if `zip` exists).
// Contents: server.js + minimal node_modules (Next standalone), .next/static, public/.
import { cpSync, rmSync, existsSync, writeFileSync } from 'node:fs';
import { execSync } from 'node:child_process';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const out = join(root, 'deploy');
const standalone = join(root, '.next', 'standalone');
if (!existsSync(standalone)) throw new Error('Önce "npm run build" çalıştırın.');
rmSync(out, { recursive: true, force: true });
cpSync(standalone, out, { recursive: true });
cpSync(join(root, '.next', 'static'), join(out, '.next', 'static'), { recursive: true });
cpSync(join(root, 'public'), join(out, 'public'), { recursive: true });
rmSync(join(out, '.env.local'), { force: true }); // never ship local secrets
writeFileSync(join(out, '.env.example'), [
  'SUPABASE_URL=https://xxxxxxxx.supabase.co',
  'SUPABASE_ANON_KEY=anon-veya-publishable-anahtar',
  'SITE_URL=https://cetinkayaelektromekanik.com.tr',
  'PORT=3000',
  '',
].join('\n'));
try {
  rmSync(join(root, 'deploy.zip'), { force: true });
  execSync('zip -qr ../deploy.zip .', { cwd: out });
  console.log('deploy/ ve deploy.zip hazır');
} catch {
  console.log('deploy/ hazır (zip bulunamadı, klasörü sıkıştırarak yükleyin)');
}
