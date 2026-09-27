# Çetinkaya Elektromekanik — Web Sitesi

Makita · Metabo · HiKOKI yetkili servisi için kurumsal web sitesi, blog, online servis talebi/takibi ve yönetim paneli.

**Teknoloji:** PHP 8.1+ ve SQLite (ek kurulum/veritabanı sunucusu gerekmez). Standart cPanel / paylaşımlı hostinglerde çalışır.

## Özellikler

**Web sitesi**
- Modern, mobil uyumlu tasarım (hero, hizmetler, süreç, SSS, blog önizleme, CTA)
- Hizmetler ve hizmet detay sayfaları
- **Blog:** kategori, arama, sayfalama, öne çıkan yazı, içindekiler, okuma ilerleme çubuğu, paylaşım butonları, ilgili yazılar, okunma sayısı, zamanlanmış yayın
- **Online servis talebi:** müşteriye anında takip kodu verilir
- **Servis takip:** takip kodu + telefonun son 4 hanesiyle adım adım durum ve süreç geçmişi
- İletişim formu, Google Harita, WhatsApp butonu
- SEO: meta/OG etiketleri, `sitemap.xml`, `robots.txt`, LocalBusiness / BlogPosting / FAQPage yapısal verileri

**Yönetim paneli (`/admin`)**
- Genel bakış: açık kayıtlar, son 14 gün grafiği, durum dağılımı, son mesajlar, en çok okunan yazılar
- Servis talepleri: durum güncelleme (müşteri takip sayfasına yansır), not ekleme, elden teslim alınan cihaz için kayıt açma, WhatsApp ile müşteriye bildirim
- Blog: zengin metin editörü, görsel yükleme (otomatik WebP’ye dönüştürülür), kapak görseli, taslak/yayın/zamanlama, SEO alanları ve Google önizlemesi
- Kategoriler, hizmetler (sürükle-bırak sıralama, ikon seçimi), SSS
- Site ayarları: iletişim bilgileri, çalışma saatleri, ana sayfa metinleri, istatistikler, hakkımızda, sosyal medya
- Çoklu yönetici, şifre değiştirme

**Güvenlik:** CSRF koruması, parola hash, giriş deneme sınırı, form spam koruması (honeypot + hız sınırı), HTML temizleme, yüklenen görsellerin yeniden kodlanması, `data/` ve `app/` klasörlerine erişim engeli.

## Kurulum (hosting)

1. Tüm dosyaları `public_html` (veya alan adının kök klasörü) içine yükleyin. `.htaccess` dosyalarının da yüklendiğinden emin olun.
2. `data/` ve `uploads/` klasörlerine yazma izni verin (genellikle `755`, gerekirse `775`).
3. Siteyi açın — veritabanı ilk ziyarette örnek içerikle otomatik oluşur.
4. `https://alanadiniz.com.tr/admin` adresine gidin ve **ilk yönetici hesabını oluşturun**.
5. Panelde **Site Ayarları** bölümünden telefon, e-posta, adres, harita ve istatistikleri kontrol edip güncelleyin.
6. SSL aktifse `.htaccess` içindeki HTTPS yönlendirme satırlarının yorumunu kaldırın.

> Hosting PHP sürümü en az 8.1 olmalı; `pdo_sqlite` ve `gd` eklentileri açık olmalıdır (çoğu hostingde varsayılan olarak açıktır).

## Yerelde çalıştırma

```bash
php -S localhost:8000 router.php
```

Ardından `http://localhost:8000` ve `http://localhost:8000/admin` adreslerini açın.

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
