# Çetinkaya Elektromekanik — Web Sitesi

Modüler su depoları, pompa ve hidrofor sistemleri satışı için kurumsal web sitesi: ürün kataloğu, online teklif sistemi, blog ve yönetim paneli.

**Teknoloji:** PHP 8.1+ ve SQLite (ek kurulum/veritabanı sunucusu gerekmez). Standart cPanel / paylaşımlı hostinglerde çalışır.

## Özellikler

**Web sitesi**
- Modern, mobil uyumlu tasarım; ürün grupları açılır menüsü
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
4. `https://alanadiniz.com.tr/admin` adresine gidin ve **ilk yönetici hesabını oluşturun**.
5. Panelde **Site Ayarları** bölümünden telefon, e-posta, adres, harita ve istatistikleri; **Ürünler** bölümünden ürünleri, görselleri ve teknik özellikleri kontrol edip güncelleyin.
6. SSL aktifse `.htaccess` içindeki HTTPS yönlendirme satırlarının yorumunu kaldırın.

> Hosting PHP sürümü en az 8.1 olmalı; `pdo_sqlite` ve `gd` eklentileri açık olmalıdır (çoğu hostingde varsayılan olarak açıktır).

## Yerelde çalıştırma

```bash
php -S localhost:8000 router.php
```

Ardından `http://localhost:8000` ve `http://localhost:8000/admin` adreslerini açın.

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
