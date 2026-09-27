# Çetinkaya Elektromekanik — Web Sitesi

Modüler su depoları, pompa ve hidrofor sistemleri satışı için kurumsal web sitesi: ürün kataloğu, online teklif sistemi, blog ve yönetim paneli.

**Teknoloji:** PHP 8.1+ ve SQLite (ek kurulum/veritabanı sunucusu gerekmez). Standart cPanel / paylaşımlı hostinglerde çalışır.

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

**Yönetim paneli (`/admin`)**
- Genel bakış: yeni/açık teklifler, satışa dönüşüm oranı, 14 günlük grafik, satış hunisi, en çok teklif istenen ürünler
- **Teklif talepleri:** satış durumu (Yeni → İletişime Geçildi → Teklif Verildi → Satışa Döndü / Olumsuz), iç notlar, geçmiş, tek tıkla arama/WhatsApp/e-posta, telefonla gelen talepler için elle kayıt
- **Ürünler:** görsel yükleme (otomatik WebP), zengin metin açıklama, teknik özellikler, marka, öne çıkarma, yayında/gizli
- **Ürün kategorileri:** sürükle-bırak sıralama, kategori çizimi seçimi
- Blog yazıları ve kategorileri, hizmetler, SSS, mesajlar
- Site ayarları: iletişim bilgileri, markalar, ana sayfa metinleri, istatistikler, kurumsal metin, sosyal medya
- Çoklu yönetici, şifre değiştirme

**Güvenlik:** CSRF koruması, parola hash, giriş deneme sınırı, form spam koruması (honeypot + hız sınırı), HTML temizleme, yüklenen görsellerin yeniden kodlanması, `data/` ve `app/` klasörlerine erişim engeli.

## Kurulum (hosting)

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

## GitHub Pages önizlemesi

GitHub Pages PHP çalıştıramadığı için `docs/` klasöründe sitenin **statik önizleme kopyası** bulunur (tüm sayfalar + yönetim paneli ekranları, örnek teklif verileriyle). Formlar, arama ve giriş bu kopyada çalışmaz.

- Yayınlamak: GitHub → **Settings → Pages → Build and deployment → Source: Deploy from a branch**, dal olarak bu dalı ve klasör olarak **`/docs`** seçin.
- Adres: `https://devmustafaozdemir.github.io/cetinkayaelektromekanik/`
- Yönetim paneli önizlemesi: `…/cetinkayaelektromekanik/yonetim/`
- Değişiklikten sonra önizlemeyi yenilemek için: `php tools/build-pages.php`

## Yedekleme

Tüm içerik `data/site.sqlite` dosyasında, görseller `uploads/` klasöründedir. Bu ikisini yedeklemek yeterlidir.

## Klasör yapısı

```
index.php          Site yönlendirici
router.php         PHP yerleşik sunucu için yönlendirici
app/               Uygulama kodu (controller, görünümler, veritabanı)
admin/             Yönetim paneli
assets/            CSS, JS, görseller
uploads/           Yüklenen görseller
data/              SQLite veritabanı
```
