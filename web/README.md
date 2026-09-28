# Çetinkaya Elektromekanik — Next.js sitesi

Site **Next.js 16** (App Router, React 19, TypeScript) ile çalışır; içerik, teklifler, mesajlar ve görseller **Supabase**'tedir.
Her sayfa istekte Supabase'ten okunur: panelde kaydettiğiniz değişiklik **anında** sitede görünür (ayrıca yayın gerekmez).

| Adres | Açıklama |
|---|---|
| `/` … | Site (ürünler, depo tasarla, blog, kurumsal, teklif, iletişim) |
| `/yonetim` | Yönetim paneli (Supabase girişi) |
| `/sitemap.xml`, `/robots.txt` | Otomatik |

## Gerekenler

- Node.js çalıştırabilen bir hosting: **cPanel'de "Setup Node.js App"** özelliği olan bir paket ya da bir VPS. Node.js **20 veya üzeri** (22 önerilir).
- Kurulmuş Supabase projesi (ana README › "Supabase ile yayın" adım 1-6). `publish.sql` (anında yayın) bu sürümde **gerekmez**.

## Ortam değişkenleri

| Değişken | Değer |
|---|---|
| `SUPABASE_URL` | Supabase › Project Settings › API Keys › Project URL |
| `SUPABASE_ANON_KEY` | Aynı sayfadaki **anon public** (veya publishable) anahtar — `service_role` anahtarını asla girmeyin |
| `SITE_URL` | Sitenin adresi, örn. `https://cetinkayaelektromekanik.com.tr` |

## Yükleme paketi

Her güncellemede GitHub paketi kendisi hazırlar:

- **Releases › "Site paketi (son sürüm)" › `deploy.zip`** — deponun ana sayfasında sağdaki *Releases* bölümünden; süresiz durur.
- ya da **Actions › Next.js paketi** › son çalıştırma › **Artifacts › cetinkaya-web** (30 gün saklanır).

Zip'in içindekiler sunucuya yüklenecek dosyalardır (`server.js`, `.next/`, `public/`, `node_modules/`). `npm install` gerekmez.

## GitHub ile otomatik yükleme (isteğe bağlı)

Sunucu bilgilerini bir kez GitHub'a girerseniz, her güncellemeden sonra site sunucuya **kendiliğinden** yüklenir ve yeniden başlatılır.
GitHub › depo › **Settings › Secrets and variables › Actions › Secrets › New repository secret**:

**cPanel (FTP):**

| Secret | Değer |
|---|---|
| `FTP_SERVER` | FTP sunucusu, örn. `ftp.cetinkayaelektromekanik.com.tr` |
| `FTP_USERNAME` | cPanel › FTP Accounts'taki kullanıcı |
| `FTP_PASSWORD` | O kullanıcının şifresi |
| `FTP_DIR` | (isteğe bağlı) Uygulama klasörü, varsayılan `cetinkaya-web/` — sonunda `/` olsun |

Yükleme bitince `tmp/restart.txt` güncellenir; cPanel uygulamayı kendiliğinden yeniden başlatır. İlk kurulumdaki *Setup Node.js App* ayarları (A bölümü) bir kez elle yapılmalıdır.

**VPS (SSH):**

| Secret | Değer |
|---|---|
| `SSH_HOST` | Sunucu IP'si veya alan adı |
| `SSH_USER` | Kullanıcı adı |
| `SSH_KEY` | Bu kullanıcıya tanımlı **özel** SSH anahtarı (tamamı, `-----BEGIN…` dahil) |
| `SSH_DIR` | (isteğe bağlı) Varsayılan `/var/www/cetinkaya` |
| `SSH_PORT` | (isteğe bağlı) Varsayılan `22` |

Dosyalar rsync ile yüklenir (sunucudaki `.env` korunur), ardından `pm2 restart cetinkaya` çalışır.

Secrets girilmemişse bu adımlar atlanır; paket yine Releases'e konur.

Kendi bilgisayarınızda üretmek için: `cd web && npm ci && npm run build && npm run package` → `web/deploy/` ve `web/deploy.zip`.

## A) cPanel (Setup Node.js App)

1. **Dosya Yöneticisi**'nde `public_html` **dışında** bir klasör açın (örn. `cetinkaya-web`), zip'i yükleyip oraya çıkarın.
2. **Setup Node.js App › Create Application**
   - Node.js version: 22 (yoksa 20)
   - Application mode: **Production**
   - Application root: `cetinkaya-web`
   - Application URL: alan adınız
   - Application startup file: `server.js`
3. Aynı ekranda **Environment variables** bölümüne yukarıdaki üç değişkeni ekleyin, **Save** ve **Restart**.
4. Tarayıcıda alan adınızı açın; panel için `/yonetim`.

Güncelleme: yeni zip'i aynı klasöre çıkarıp (üzerine yazarak) **Restart** deyin.

## B) VPS (Ubuntu + pm2 + Nginx)

```bash
# Node.js 22 ve pm2
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash - && sudo apt install -y nodejs
sudo npm i -g pm2
# paketi /var/www/cetinkaya altına çıkarın, sonra:
cd /var/www/cetinkaya
cp .env.example .env && nano .env        # SUPABASE_URL, SUPABASE_ANON_KEY, SITE_URL, PORT=3000
pm2 start server.js --name cetinkaya --node-args="--env-file=.env"
pm2 save && pm2 startup
```

Nginx'te alan adını `http://127.0.0.1:3000`'e yönlendirin (`proxy_pass`) ve `certbot --nginx` ile SSL alın.
Güncelleme: dosyaları değiştirip `pm2 restart cetinkaya`.

## Supabase ayarı (bir kez)

Supabase › **Authentication › URL Configuration**: *Site URL* = sitenizin adresi, *Redirect URLs* = `https://alanadiniz/yonetim` (şifre sıfırlama bağlantısı için).

## Geliştirme

```bash
cd web
cp .env.example .env.local   # değerleri girin
npm install
npm run dev                  # http://localhost:3000
```

- Sayfalar: `app/` · ortak parçalar: `components/` · veri: `lib/data.ts` · yardımcılar: `lib/site.ts`
- Stil, site etkileşimleri (depo tasarlayıcı, 2D/3D, formlar, menü), 3D görüntüleyici ve panel paketi kök dizindeki `assets/` klasöründen `public/assets`'e kopyalanır (`npm run sync-assets`, `dev` ve `build` bunu kendiliğinden yapar).
- İkonlar, 2D çizimler (SVG) ve varsayılan sayfa metinleri `lib/generated.ts` dosyasındadır.
