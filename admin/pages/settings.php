<?php
$groups = [
    'general' => ['Genel', 'settings', [
        'site_name'        => ['Firma adı', 'text'],
        'site_tagline'     => ['Slogan', 'text'],
        'meta_description' => ['Site açıklaması (SEO)', 'textarea', 'Google arama sonuçlarında görünen açıklama. 150-160 karakter önerilir.'],
        'brands'           => ['Yetkili markalar', 'textarea', 'Her satıra bir marka yazın.'],
    ]],
    'contact' => ['İletişim', 'phone', [
        'phone'        => ['Telefon', 'text'],
        'phone2'       => ['Telefon 2 / GSM', 'text'],
        'whatsapp'     => ['WhatsApp numarası', 'text'],
        'email'        => ['E-posta', 'email'],
        'notify_email' => ['Bildirim e-postası', 'email', 'Yeni servis talebi ve mesajlarda bildirim gönderilecek adres (boşsa yukarıdaki e-posta kullanılır).'],
        'address'      => ['Adres', 'textarea'],
        'hours'        => ['Çalışma saatleri', 'textarea', 'Her satıra bir gün aralığı. İlk satır üst barda görünür.'],
        'map_link'     => ['Google Maps bağlantısı', 'url'],
        'map_embed'    => ['Harita embed adresi', 'url', 'Google Maps › Paylaş › Harita yerleştir kısmındaki iframe “src” adresi.'],
    ]],
    'home' => ['Ana Sayfa', 'home', [
        'hero_badge' => ['Üst rozet metni', 'text'],
        'hero_title' => ['Ana başlık', 'text'],
        'hero_text'  => ['Açıklama', 'textarea'],
        'stats'      => ['İstatistikler', 'textarea', 'Her satır: değer|etiket  (örn. 25+|Yıllık Tecrübe)'],
    ]],
    'about' => ['Hakkımızda', 'users', [
        'about_title'  => ['Başlık', 'text'],
        'about_text'   => ['Metin', 'textarea', 'Paragrafları boş satırla ayırın.'],
        'about_values' => ['Değerler / öne çıkanlar', 'textarea', 'Her satıra bir madde.'],
    ]],
    'social' => ['Sosyal Medya', 'instagram', [
        'instagram' => ['Instagram', 'url'],
        'facebook'  => ['Facebook', 'url'],
        'linkedin'  => ['LinkedIn', 'url'],
        'youtube'   => ['YouTube', 'url'],
    ]],
];
$tab = array_key_exists($_GET['tab'] ?? '', $groups) ? $_GET['tab'] : 'general';

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
