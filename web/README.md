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

GitHub › **Actions › Next.js paketi** › en son yeşil çalıştırma › **Artifacts › cetinkaya-web**. İndirilen zip'in içindekiler sunucuya yüklenecek dosyalardır (`server.js`, `.next/`, `public/`, `node_modules/`). `npm install` gerekmez.

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
- İkonlar, 2D çizimler ve varsayılan sayfa metinleri `lib/generated.ts` dosyasındadır; PHP sürümünden `php tools/export-next-data.php` ile üretilir.
