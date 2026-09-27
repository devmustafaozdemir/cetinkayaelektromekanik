<?php
$groups = [
    'general' => ['Genel', 'settings', [
        'site_name'        => ['Firma adı', 'text'],
        'site_tagline'     => ['Slogan', 'text'],
        'meta_description' => ['Site açıklaması (SEO)', 'textarea', 'Google arama sonuçlarında görünen açıklama. 150-160 karakter önerilir.'],
    ]],
    'brands' => ['Markalar', 'award', [
        'brands' => ['Marka listesi', 'textarea', 'Her satıra bir marka: Marka Adı | Kısa açıklama (örn. Grundfos | Pompa ve hidrofor sistemleri). Ürünlerdeki marka adı bununla aynı yazılmalı.'],
    ]],
    'contact' => ['İletişim', 'phone', [
        'phone'        => ['Telefon', 'text'],
        'phone2'       => ['Telefon 2 / GSM', 'text'],
        'whatsapp'     => ['WhatsApp numarası', 'text'],
        'email'        => ['E-posta', 'email'],
        'notify_email' => ['Bildirim e-postası', 'email', 'Yeni teklif talebi ve mesajlarda bildirim gönderilecek adres (boşsa yukarıdaki e-posta kullanılır).'],
        'address'      => ['Adres', 'textarea'],
        'address_short'=> ['Kısa adres', 'text', 'Üst barda görünür (örn. İzmit, Kocaeli).'],
        'hours'        => ['Çalışma saatleri', 'textarea', 'Her satıra bir gün aralığı. İlk satır üst barda görünür.'],
        'map_link'     => ['Google Maps bağlantısı', 'url'],
        'map_embed'    => ['Harita embed adresi', 'url', 'Google Maps › Paylaş › Harita yerleştir kısmındaki iframe “src” adresi.'],
    ]],
    'home' => ['Ana Sayfa', 'home', [
        'hero_badge' => ['Üst rozet metni', 'text'],
        'hero_title' => ['Ana başlık', 'text'],
        'hero_text'  => ['Açıklama', 'textarea'],
        'stats_title' => ['“Sahada kanıtlanmış” başlığı', 'text'],
        'stats_text'  => ['“Sahada kanıtlanmış” metni', 'textarea'],
        'stats'       => ['Rakamlar', 'textarea', 'Her satır: değer|etiket (örn. 500+|Mutlu müşteri). Dört satır önerilir; rakamlar sayfada sayarak belirir.'],
    ]],
    'about' => ['Hakkımızda', 'users', [
        'about_title'  => ['Başlık', 'text'],
        'about_text'   => ['Metin', 'textarea', 'Paragrafları boş satırla ayırın.'],
        'about_values' => ['Değerler / öne çıkanlar', 'textarea', 'Her satıra bir madde.'],
        'refs_lead' => ["Referanslar sayfası açıklaması", 'textarea'],
        'partners_lead' => ["Çözüm ortakları sayfası açıklaması", 'textarea'],
    ]],
    'texts' => ['Sayfa Metinleri', 'file-text', [
        'hero_trust' => ["Ana sayfa: başlık altı maddeler", 'textarea', "Her satıra bir madde (yeşil tik ile görünür)."],
        'home_cats_kicker' => ["Ürün grupları: küçük etiket", 'text'],
        'home_cats_title' => ["Ürün grupları: başlık", 'text'],
        'home_cats_text' => ["Ürün grupları: açıklama", 'textarea'],
        'home_featured_kicker' => ["Öne çıkan ürünler: küçük etiket", 'text'],
        'home_featured_title' => ["Öne çıkan ürünler: başlık", 'text'],
        'home_steps_kicker' => ["Çalışma adımları: küçük etiket", 'text'],
        'home_steps_title' => ["Çalışma adımları: başlık", 'text'],
        'home_steps' => ["Çalışma adımları", 'textarea', "Her satır: Başlık|Açıklama. 4 satır önerilir."],
        'home_why_kicker' => ["“Neden biz” bölümü: küçük etiket", 'text', "Başlık ve maddeler Hakkımızda sekmesinden gelir."],
        'home_why_badges' => ["“Neden biz” rozetleri", 'textarea', "İki satır: Kalın yazı|Devamı (örn. Orijinal|garantili ürün)."],
        'home_services_kicker' => ["Hizmetler: küçük etiket", 'text'],
        'home_services_title' => ["Hizmetler: başlık", 'text'],
        'home_refs_kicker' => ["Referanslar: küçük etiket", 'text'],
        'home_refs_title' => ["Referanslar: başlık", 'text'],
        'home_refs_text' => ["Referanslar: açıklama", 'textarea'],
        'home_blog_kicker' => ["Blog: küçük etiket", 'text'],
        'home_blog_title' => ["Blog: başlık", 'text'],
        'home_faq_kicker' => ["SSS: küçük etiket", 'text'],
        'home_faq_title' => ["SSS: başlık", 'text'],
        'home_faq_text' => ["SSS: açıklama", 'textarea'],
        'footer_cta_title' => ["Alt bilgi: çağrı başlığı", 'text', "Her sayfanın altındaki kırmızı “Teklif iste” kartı."],
        'footer_cta_text' => ["Alt bilgi: çağrı metni", 'textarea'],
        'quote_next_title' => ["Teklif sayfası: yan kutu başlığı", 'text'],
        'quote_next' => ["Teklif sayfası: adımlar", 'textarea', "Her satır: Başlık|Açıklama."],
    ]],
    'designer' => ['Depo Tasarla', 'ruler', [
        'water_use' => ["Kullanım yerleri ve günlük tüketim", 'textarea', "Her satır: Kullanım yeri|Litre/gün|Sayı etiketi|İpucu. Örn. Konut|150|Kişi sayısı|Daire sayısı × 4 kişi. “İhtiyaca göre” hesabında ve tüketim tablosunda kullanılır."],
    ]],
    'social' => ['Sosyal Medya', 'instagram', [
        'instagram' => ['Instagram', 'url'],
        'facebook'  => ['Facebook', 'url'],
        'linkedin'  => ['LinkedIn', 'url'],
        'youtube'   => ['YouTube', 'url'],
    ]],
];
$tab = array_key_exists($_GET['tab'] ?? '', $groups) ? $_GET['tab'] : 'general';

if ($method === 'POST' && $tab === 'general' && isset($_POST['logo_action'])) {
    foreach (['logo', 'logo_light'] as $key) {
        if (!empty($_POST['remove_' . $key])) {
            delete_upload(setting($key));
            setting_set($key, '');
        } elseif (!empty($_FILES[$key]['name'])) {
            try {
                $name = store_image($_FILES[$key], 800);
                delete_upload(setting($key));
                setting_set($key, $name);
            } catch (RuntimeException $ex) {
                flash('error', $ex->getMessage());
            }
        }
    }
    flash('success', 'Logo güncellendi.');
    redirect(admin_url('settings', ['tab' => 'general']));
}

if ($method === 'POST' && $tab === 'brands' && isset($_POST['brand_logos'])) {
    foreach (brands() as $b) {
        $key = 'brand_logo_' . $b['slug'];
        if (!empty($_POST['remove_' . $key])) {
            delete_upload(setting($key));
            setting_set($key, '');
        } elseif (!empty($_FILES[$key]['name'])) {
            try {
                $name = store_image($_FILES[$key], 900);
                delete_upload(setting($key));
                setting_set($key, $name);
            } catch (RuntimeException $ex) {
                flash('error', $b['name'] . ': ' . $ex->getMessage());
            }
        }
    }
    flash('success', 'Marka logoları güncellendi.');
    redirect(admin_url('settings', ['tab' => 'brands']));
}

if ($method === 'POST') {
    $errors = 0;
    foreach ($groups[$tab][2] as $key => [$label, $type]) {
        $val = post_str($key, 5000);
        if ($type === 'url' && $val !== '' && !preg_match('#^https://#i', $val)) {
            flash('error', "{$label}: bağlantı https:// ile başlamalı.");
            $errors++;
            continue;
        }
        if ($type === 'email' && $val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
            flash('error', "{$label}: geçerli bir e-posta girin.");
            $errors++;
            continue;
        }
        setting_set($key, $val);
    }
    if (!$errors) flash('success', 'Ayarlar kaydedildi.');
    redirect(admin_url('settings', ['tab' => $tab]));
}

admin_render('settings', ['title' => 'Site Ayarları', 'groups' => $groups, 'tab' => $tab, 'values' => settings()]);
