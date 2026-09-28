# Çetinkaya Elektromekanik — Web Sitesi

Modüler su depoları, pompa ve hidrofor sistemleri satışı için kurumsal web sitesi: ürün kataloğu, online teklif sistemi, blog ve yönetim paneli.

**Teknoloji:** Next.js 16 (React, TypeScript) + Supabase (veritabanı, giriş, görseller). Kurulum ve sunucuya yükleme: [`web/README.md`](web/README.md).

| Klasör | İçerik |
|---|---|
| `web/` | Next.js sitesi (sayfalar, veri katmanı, `/yonetim` paneli sunumu) |
| `assets/` | Stil, site etkileşimleri (`js/site.js`), 3D görüntüleyici, yönetim paneli kaynak kodu (`src/admin`) |
| `supabase/` | Veritabanı şeması (`schema.sql`) ve örnek içerik (`seed.sql`) |
| `.github/workflows/` | Paketleme, Release ve isteğe bağlı otomatik sunucu yüklemesi |

## Özellikler

**Web sitesi**
- Yumuşak, modern tasarım: logodaki lacivert ve kırmızı, açık mavi yüzeyler, yuvarlak kartlar; Manrope + Inter yazı tipleri, mobil uyumlu
- **Sahada kanıtlanmış** bandı: mutlu müşteri, sipariş, ürün çeşidi, tecrübe rakamları (admin → Site Ayarları → Ana Sayfa)
- Marka logoları (`assets/img/brands/`; admin → Site Ayarları → Markalar bölümünden değiştirilebilir)
- **Depo Tasarla (`/depo-tasarla`) ve ana sayfa hesaplayıcısı:** 1 modül = 1,08 m; en, boy ve yükseklik tam/yarım modül (1,08 / 0,54 m) adımlarla, yükseklik 0,5–4 kat. Hacim, litre, dış ölçü, taban alanı ve panel listesi (108×108 tam, 108×54 yarım) anında hesaplanır. **İhtiyaca göre** sekmesinde kullanım yeri, kişi sayısı, günlük tüketim, yedek gün, yangın rezervi ve isteğe bağlı yerleşim alanı girilir; en ekonomik depo ve iki alternatif (daha alçak / daha küçük taban) önerilir. “Bu depo için teklif iste” ölçüleri ve panel listesini teklif formuna taşır
- **2D çizim + 3D görünüm:** her ürün için SVG çizim; “3D incele” ile döndürülebilir, yakınlaştırılabilir 3D model (Three.js, yalnızca tıklanınca yüklenir)
- **Ürün kataloğu:** kategoriler, marka filtresi, arama, ürün detay sayfası (teknik özellik tablosu, benzer ürünler)
- **Teklif sistemi:** her üründe “Teklif İste”, ana sayfada hızlı teklif formu, talep türleri (ürün, proje/keşif, montaj, bakım-servis); müşteriye talep numarası verilir
- Markalar sayfası (Meksis, Grundfos, Wilo, Standart Pompa, Sumak)
- Hizmetler (keşif, montaj, bakım, sevkiyat), Kurumsal menüsü (Hakkımızda, **Referanslar**, **Çözüm Ortakları**, SSS), İletişim + harita, WhatsApp butonu
- **Blog:** kategori, arama, öne çıkan yazı, içindekiler, okuma çubuğu, paylaşım, ilgili yazılar
- SEO: meta/OG etiketleri, `sitemap.xml`, `robots.txt`, Store / Product / BlogPosting / FAQPage yapısal verileri

**Yönetim paneli (`/yonetim`)**
- Genel bakış: yeni/açık teklifler, satışa dönüşüm oranı, 14 günlük grafik, satış hunisi, en çok teklif istenen ürünler
- **Teklif talepleri:** satış durumu (Yeni → İletişime Geçildi → Teklif Verildi → Satışa Döndü / Olumsuz), iç notlar, geçmiş, tek tıkla arama/WhatsApp/e-posta, telefonla gelen talepler için elle kayıt
- **Ürünler:** görsel yükleme (otomatik WebP), zengin metin açıklama, teknik özellikler, marka, öne çıkarma, yayında/gizli
- **Ürün kategorileri:** sürükle-bırak sıralama, kategori çizimi seçimi
- Blog yazıları ve kategorileri, hizmetler, SSS, mesajlar, referanslar ve çözüm ortakları (logo yükleme, sıralama)
- **Sayfa Metinleri:** ana sayfa bölüm başlıkları, çalışma adımları, alt bilgi ve teklif sayfası metinleri; **Depo Tasarla:** kullanım yerleri ve günlük tüketim değerleri
- Site ayarları: iletişim bilgileri, markalar, ana sayfa metinleri, istatistikler, kurumsal metin, sosyal medya
- Çoklu yönetici, şifre değiştirme, şifremi unuttum (Supabase)

**Güvenlik:** Supabase satır düzeyi güvenlik (RLS: ziyaretçi yalnızca yayındaki içeriği okur, teklif/mesaj yalnızca doğrulamalı fonksiyonlarla eklenir, yazma yetkisi yalnızca yöneticide), form spam koruması (honeypot + hız sınırı), HTML temizleme.

## Kurulum

### 1. Supabase (bir kez, ~10 dakika)

1. [supabase.com](https://supabase.com) → *New project*, bölge **Frankfurt (eu-central-1)**.
2. *SQL Editor → New query*: [`supabase/schema.sql`](supabase/schema.sql) dosyasının tamamını yapıştırıp **Run**; ardından [`supabase/seed.sql`](supabase/seed.sql) (örnek içerik). `schema.sql` güncellendiğinde tekrar çalıştırmak güvenlidir, verileri silmez.
3. *Authentication → Users → Add user*: e-posta + şifre, **Auto Confirm User** işaretli. **İlk kullanıcı otomatik yönetici olur.**
4. *Authentication → Sign In / Providers*: **Allow new users to sign up** kapalı.
5. *Authentication → URL Configuration*: *Site URL* = sitenizin adresi, *Redirect URLs* = `https://alanadiniz/yonetim`.
6. *Project Settings → API Keys*: **Project URL** ve **anon public** anahtarını not edin (`service_role` anahtarını hiçbir yere girmeyin).

### 2. Sunucu

Site Node.js ile çalışır (cPanel "Setup Node.js App" ya da VPS). Paket, ortam değişkenleri ve GitHub ile otomatik yükleme: **[`web/README.md`](web/README.md)**.

## Günlük kullanım

- Panel: `https://alanadiniz/yonetim` → e-posta ve şifre. Şifre unutulursa giriş ekranındaki **Şifremi unuttum**.
- Panelde kaydettiğiniz her şey sitede **hemen** görünür.
- Teklif talepleri ve mesajlar panelde anında görünür.
- Yeni yönetici: Supabase'te kullanıcıyı oluşturun, sonra SQL Editor'de
  ```sql
  insert into public.admins (user_id) select id from auth.users where email = 'ornek@firma.com';
  ```
- Yedek: Supabase → *Database → Backups*; *Table Editor*'den CSV dışa aktarma.

## 2D çizimler ve 3D modeller

Ürün görselleri fotoğraf gerektirmez: her ürünün **model tipi** (panel → Ürünler → “Çizim ve 3D model”) hem 2D çizimi hem 3D modeli belirler.

| Model tipi | Örnek |
|---|---|
| `tank:galvaniz`, `tank:paslanmaz`, `tank:grp`, `tank:sandvic` | Modüler depo (108×108 cm kabartmalı paneller, yarım modül destekli) |
| `booster:1` … `booster:4` | Tek/çift/üç/dört pompalı hidrofor seti |
| `pump:horizontal`, `pump:vertical`, `pump:circulator` | Yatay santrifüj, dikey çok kademeli, sirkülasyon |
| `sub:deep`, `sub:drain` | Derin kuyu ve drenaj dalgıç pompası |

- 2D çizimler: `web/lib/generated.ts` (depo tasarlayıcıdaki canlı çizim: `assets/js/site.js`)
- 3D: `assets/src/viewer3d.js` (Three.js) → `npm run build:3d` → `assets/js/viewer3d.js`
- Yönetim paneli: `assets/src/admin/` → `npm run build:admin` → `assets/js/admin/`
- Kategori için “Çizim yok” seçilebilir; fotoğraf yüklenirse önce fotoğraf gösterilir.
