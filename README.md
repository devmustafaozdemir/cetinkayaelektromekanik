# Çetinkaya Elektromekanik — Web Sitesi

Modüler su depoları, pompa ve hidrofor sistemleri satışı için kurumsal web sitesi: ürün kataloğu, online teklif sistemi, blog ve yönetim paneli.

İki şekilde yayınlanabilir:

| | **A) GitHub Pages + Supabase** (önerilen) | **B) PHP hosting** |
|---|---|---|
| Maliyet | Ücretsiz | Hosting ücreti |
| Site | Statik HTML (GitHub Pages) | PHP 8.1+ ve SQLite |
| İçerik, teklifler, görseller | Supabase (Postgres, Auth, Storage) | `data/site.sqlite`, `uploads/` |
| Yönetim paneli | `…/yonetim/` | `…/admin/` |
| Kurulum | [Supabase ile yayın](#a-supabase-ile-yayın-önerilen) | [Kurulum (hosting)](#b-kurulum-php-hosting) |

## Özellikler

**Web sitesi**
- Yumuşak, modern tasarım: logodaki lacivert ve kırmızı, açık mavi yüzeyler, yuvarlak kartlar; Manrope + Inter yazı tipleri, mobil uyumlu
- **Sahada kanıtlanmış** bandı: mutlu müşteri, sipariş, ürün çeşidi, tecrübe rakamları (admin → Site Ayarları → Ana Sayfa)
- Marka logoları (`assets/img/brands/`; admin → Site Ayarları → Markalar bölümünden değiştirilebilir)
- **Depo hesaplayıcı (ana sayfa):** en/boy/yükseklik ve panel malzemesi seçilir; çizim, hacim, litre ve yaklaşık daire sayısı anında güncellenir, “Bu ölçüde teklif iste” ölçüleri teklif formuna taşır
- **2D çizim + 3D görünüm:** her ürün için sunucuda üretilen SVG çizim; “3D incele” ile döndürülebilir, yakınlaştırılabilir 3D model (Three.js, yalnızca tıklanınca yüklenir)
- **Ürün kataloğu:** kategoriler, marka filtresi, arama, ürün detay sayfası (teknik özellik tablosu, benzer ürünler)
- **Teklif sistemi:** her üründe “Teklif İste”, ana sayfada hızlı teklif formu, talep türleri (ürün, proje/keşif, montaj, bakım-servis); müşteriye talep numarası verilir
- Markalar sayfası (Meksis, Grundfos, Wilo, Standart Pompa, Sumak)
- Hizmetler (keşif, montaj, bakım, sevkiyat), Kurumsal, SSS, İletişim + harita, WhatsApp butonu
- **Blog:** kategori, arama, öne çıkan yazı, içindekiler, okuma çubuğu, paylaşım, ilgili yazılar
- SEO: meta/OG etiketleri, `sitemap.xml`, `robots.txt`, Store / Product / BlogPosting / FAQPage yapısal verileri

**Yönetim paneli (`/yonetim` veya `/admin`)**
- Genel bakış: yeni/açık teklifler, satışa dönüşüm oranı, 14 günlük grafik, satış hunisi, en çok teklif istenen ürünler
- **Teklif talepleri:** satış durumu (Yeni → İletişime Geçildi → Teklif Verildi → Satışa Döndü / Olumsuz), iç notlar, geçmiş, tek tıkla arama/WhatsApp/e-posta, telefonla gelen talepler için elle kayıt
- **Ürünler:** görsel yükleme (otomatik WebP), zengin metin açıklama, teknik özellikler, marka, öne çıkarma, yayında/gizli
- **Ürün kategorileri:** sürükle-bırak sıralama, kategori çizimi seçimi
- Blog yazıları ve kategorileri, hizmetler, SSS, mesajlar
- Site ayarları: iletişim bilgileri, markalar, ana sayfa metinleri, istatistikler, kurumsal metin, sosyal medya
- Çoklu yönetici, şifre değiştirme, şifremi unuttum (Supabase)

**Güvenlik:** Supabase satır düzeyi güvenlik (RLS: ziyaretçi yalnızca yayındaki içeriği okur, teklif/mesaj yalnızca doğrulamalı fonksiyonlarla eklenir, yazma yetkisi yalnızca yöneticide), form spam koruması (honeypot + hız sınırı), HTML temizleme. PHP sürümünde ek olarak CSRF koruması, parola hash, giriş deneme sınırı, yüklenen görsellerin yeniden kodlanması.

## A) Supabase ile yayın (önerilen)

**Nasıl çalışır:** Site GitHub Pages'te statik HTML olarak yayınlanır (hızlı ve ücretsiz). Ürünler, blog, ayarlar, teklifler ve görseller Supabase'te durur. Ziyaretçinin gönderdiği teklif/mesaj doğrudan Supabase'e kaydedilir ve panelde anında görünür. Panelde bir içerik kaydedildiğinde GitHub Actions siteyi yeniden derleyip yayınlar (anında yayın kuruluysa 1-2 dakikada, değilse en geç 1 saat içinde).

### Kurulum (bir kez, ~15 dakika)

1. **Supabase projesi:** [supabase.com](https://supabase.com) → *New project*. Bölge olarak **Frankfurt (eu-central-1)** seçin, veritabanı şifresini bir yere kaydedin.
2. **Veritabanı:** Supabase → *SQL Editor* → *New query*. [`supabase/schema.sql`](supabase/schema.sql) dosyasının tamamını yapıştırıp **Run**. Ardından aynı şekilde [`supabase/seed.sql`](supabase/seed.sql) (örnek ürün, blog, ayar içerikleri).
3. **Yönetici hesabı:** *Authentication → Users → Add user → Create new user*. E-posta ve şifre yazın, **Auto Confirm User** işaretli olsun. **İlk oluşturulan kullanıcı otomatik olarak yönetici olur.**
4. **Kayıtları kapatın:** *Authentication → Sign In / Providers* → **Allow new users to sign up** kapalı olsun (başkası hesap açamasın).
5. **Adres ayarı:** *Authentication → URL Configuration* → *Site URL*: sitenizin adresi (örn. `https://devmustafaozdemir.github.io/cetinkayaelektromekanik`). *Redirect URLs*'e panel adresini ekleyin: `…/yonetim/` (şifre sıfırlama bağlantısı için).
6. **Anahtarlar:** *Project Settings → API Keys*. **Project URL** ve **anon public** anahtarını (veya *publishable* anahtarı) kopyalayın. Bu anahtar herkese açık olacak şekilde tasarlanmıştır; **`service_role` / secret anahtarını asla kullanmayın.**
7. **GitHub değişkenleri:** GitHub → depo → *Settings → Secrets and variables → Actions → Variables* → *New repository variable*:
   - `SUPABASE_URL` = Project URL (örn. `https://abcd1234.supabase.co`)
   - `SUPABASE_ANON_KEY` = anon / publishable anahtar
   - (isteğe bağlı) `SITE_URL` = kendi alan adınız, örn. `https://cetinkayaelektromekanik.com.tr`
8. **GitHub Pages:** *Settings → Pages → Build and deployment → Source:* **GitHub Actions**.
9. **İlk yayın:** *Actions → Siteyi yayınla → Run workflow*. 1-2 dakika sonra:
   - Site: `https://devmustafaozdemir.github.io/cetinkayaelektromekanik/`
   - Panel: `https://devmustafaozdemir.github.io/cetinkayaelektromekanik/yonetim/`

> “Branch … is not allowed to deploy to github-pages” hatası alırsanız: *Settings → Environments → github-pages → Deployment branches* bölümüne bu dalı ekleyin.

### Anında yayın (önerilir)

Panelde kaydettiğiniz değişikliğin 1-2 dakikada siteye yansıması için:

1. GitHub → sağ üst profil → *Settings → Developer settings → Personal access tokens → Fine-grained tokens → Generate new token*. *Repository access:* yalnızca bu depo; *Permissions → Actions:* **Read and write**. Token'ı kopyalayın.
2. Supabase → *SQL Editor*'de token'ı gizli kasaya (Vault) kaydedin:
   ```sql
   select vault.create_secret('github_pat_BURAYA_TOKEN', 'github_token');
   select vault.create_secret('devmustafaozdemir/cetinkayaelektromekanik', 'github_repo');
   select vault.create_secret('claude/adoring-hopper-jfy8yo', 'github_ref'); -- yayın yapılan dal
   ```
3. [`supabase/publish.sql`](supabase/publish.sql) dosyasının tamamını çalıştırın.

Bundan sonra her kayıtta site kendiliğinden güncellenir; paneldeki **Siteyi şimdi yayınla** düğmesi de çalışır.

### Kendi alan adınız (cetinkayaelektromekanik.com.tr)

1. GitHub → *Settings → Pages → Custom domain*: `cetinkayaelektromekanik.com.tr` yazıp kaydedin, *Enforce HTTPS* işaretleyin.
2. Alan adı DNS panelinde: `@` için A kayıtları `185.199.108.153`, `185.199.109.153`, `185.199.110.153`, `185.199.111.153`; `www` için CNAME `devmustafaozdemir.github.io`.
3. GitHub değişkeni `SITE_URL` = `https://cetinkayaelektromekanik.com.tr`; Supabase *URL Configuration* adreslerini de yeni adresle güncelleyin. Ardından *Run workflow*.

### Günlük kullanım

- Panel: `…/yonetim/` → e-posta ve şifre ile giriş. Şifrenizi unutursanız giriş ekranındaki **Şifremi unuttum** bağlantısını kullanın.
- Teklif talepleri ve mesajlar panelde anında görünür; sol menüde yeni talep sayısı gösterilir.
- Yeni yönetici eklemek: Supabase'te kullanıcıyı oluşturun (3. adım), sonra SQL Editor'de:
  ```sql
  insert into public.admins (user_id) select id from auth.users where email = 'ornek@firma.com';
  ```
- Saatlik otomatik derleme Supabase projesini de aktif tutar (ücretsiz planda 7 gün hiç kullanılmayan projeler duraklatılır). GitHub, depoda 60 gün işlem olmazsa saatlik görevi durdurup e-posta gönderir; e-postadaki bağlantıdan tekrar etkinleştirmeniz yeterlidir.
- Yedek: Supabase → *Database → Backups*; ayrıca *Table Editor*'den tabloları CSV olarak dışa aktarabilirsiniz.

### Geliştirici notları

- Derleme: [`tools/build-site.php`](tools/build-site.php) içeriği Supabase REST API'den okur, PHP şablonlarıyla tüm sayfaları statik HTML'e dönüştürür ve `_site/` klasörüne yazar (`SUPABASE_URL=… SUPABASE_ANON_KEY=… SITE_URL=… php tools/build-site.php`).
- Panel kaynak kodu: `assets/src/admin/` (supabase-js + Quill) → `npm run build:admin` → `assets/js/admin/` (derlenmiş hali repoda).
- Yerel test için Supabase benzetimi: `tools/dev/supabase-mock.php` (dosya başındaki açıklamaya bakın).
- `supabase/seed.sql`, `app/seed.php`'den üretilir: `php tools/export-seed-sql.php > supabase/seed.sql`.

## B) Kurulum (PHP hosting)

1. Tüm dosyaları `public_html` (veya alan adının kök klasörü) içine yükleyin. `.htaccess` dosyalarının da yüklendiğinden emin olun.
2. `data/` ve `uploads/` klasörlerine yazma izni verin (genellikle `755`, gerekirse `775`).
3. Siteyi açın — veritabanı ilk ziyarette örnek içerikle otomatik oluşur.
4. `https://alanadiniz.com.tr/admin/?kurulum=ANAHTAR` adresine gidin ve **ilk yönetici hesabını oluşturun**. Anahtar `config.local.php` dosyasındaki `setup_key` değeridir; bu dosya yoksa oluşturun:
   ```php
   <?php
   return ['setup_key' => 'uzun-rastgele-bir-deger'];
   ```
   Hesap oluşturulduktan sonra anahtar bir daha gerekmez; giriş `https://alanadiniz.com.tr/admin` adresinden yapılır.
5. Panelde **Site Ayarları** bölümünden telefon, e-posta, adres, harita ve istatistikleri; **Ürünler** bölümünden ürünleri, görselleri ve teknik özellikleri kontrol edip güncelleyin.
6. SSL aktifse `.htaccess` içindeki HTTPS yönlendirme satırlarının yorumunu kaldırın.

> Hosting PHP sürümü en az 8.1 olmalı; `pdo_sqlite` ve `gd` eklentileri açık olmalıdır (çoğu hostingde varsayılan olarak açıktır).

## Yerelde çalıştırma

```bash
php -S localhost:8000 router.php
```

Ardından `http://localhost:8000` ve `http://localhost:8000/admin` adreslerini açın.

## 2D çizimler ve 3D modeller

Ürün görselleri fotoğraf gerektirmez: her ürünün **model tipi** (admin → Ürünler → “Çizim ve 3D model”) hem 2D çizimi hem 3D modeli belirler.

| Model tipi | Örnek |
|---|---|
| `tank:galvaniz`, `tank:paslanmaz`, `tank:grp`, `tank:sandvic` | Modüler depo (1×1 m paneller, ölçüye göre kurulur) |
| `booster:1` … `booster:4` | Tek/çift/üç/dört pompalı hidrofor seti |
| `pump:horizontal`, `pump:vertical`, `pump:circulator` | Yatay santrifüj, dikey çok kademeli, sirkülasyon |
| `sub:deep`, `sub:drain` | Derin kuyu ve drenaj dalgıç pompası |

- 2D çizimler: `app/models.php` (sunucuda SVG olarak üretilir, JavaScript gerekmez)
- 3D modeller: `assets/src/viewer3d.js` → derlenmiş hali `assets/js/viewer3d.js` (repoda hazır, hostingde Node.js gerekmez)
- Teknoloji: **Three.js** (parametrik modeller, OrbitControls, RoomEnvironment yansımaları). Paket yaklaşık 150 KB (gzip) ve yalnızca 3D butonuna basıldığında yüklenir; WebGL desteklemeyen cihazlarda 3D butonu gizlenir.
- 3D kodunu değiştirdikten sonra: `npm install && npm run build:3d`

Gerçek ürün fotoğrafı yüklerseniz ürün sayfasında önce fotoğraf gösterilir, 3D model yine açılabilir. Logo, admin → Site Ayarları → Genel → Logo bölümünden yüklenebilir; yüklenmezse yerleşik SVG logo kullanılır.

## Eski statik önizleme (`docs/`)

Supabase kurulumu tamamlanana kadar `docs/` klasöründeki örnek verili önizleme (*Pages → Source: Deploy from a branch → /docs*) yayında kalabilir. Pages kaynağı **GitHub Actions** yapıldıktan sonra bu klasöre gerek kalmaz.

## Yedekleme (PHP hosting)

Tüm içerik `data/site.sqlite` dosyasında, görseller `uploads/` klasöründedir. Bu ikisini yedeklemek yeterlidir.

## Klasör yapısı

```
index.php          Site yönlendirici
router.php         PHP yerleşik sunucu için yönlendirici
app/               Uygulama kodu (controller, görünümler, veritabanı)
admin/             Yönetim paneli (PHP hosting)
admin-app/         Yönetim paneli sayfası (Supabase, /yonetim/)
assets/            CSS, JS, görseller (assets/src: kaynak kod)
supabase/          Veritabanı şeması, örnek içerik, anında yayın
tools/             Derleme araçları
.github/workflows/ GitHub Pages yayın akışı
uploads/           Yüklenen görseller (PHP hosting)
data/              SQLite veritabanı (PHP hosting)
```
