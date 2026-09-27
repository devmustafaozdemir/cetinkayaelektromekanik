<?php
declare(strict_types=1);

/** Initial content, written once when the database is first created. Everything is editable in the admin panel. */
function db_seed(): void
{
    $pdo = db();
    $pdo->beginTransaction();

    $settings = [
        'site_name'        => 'Çetinkaya Elektromekanik',
        'site_tagline'     => 'Modüler Su Depoları · Pompa ve Hidrofor Sistemleri',
        'meta_description' => 'Kocaeli / İzmit\'te Meksis modüler su depoları, Grundfos, Wilo, Standart Pompa ve Sumak pompa ve hidrofor sistemleri satışı. Projeye özel keşif, teklif, montaj ve servis.',
        'phone'            => '0262 335 13 20',
        'phone2'           => '0533 033 79 04',
        'whatsapp'         => '0533 033 79 04',
        'email'            => 'info@cetinkayaelektromekanik.com.tr',
        'address'          => 'Sanayi Mah. 12. Cad. İzmit Sanayi Sitesi 11. Blok No:1, 41040 İzmit / Kocaeli',
        'address_short'    => 'İzmit Sanayi Sitesi, Kocaeli',
        'hours'            => "Pazartesi – Cuma: 08:30 – 18:30\nCumartesi: 08:30 – 14:00\nPazar: Kapalı",
        'map_embed'        => 'https://www.google.com/maps?q=%C4%B0zmit+Sanayi+Sitesi+11.+Blok+Kocaeli&output=embed',
        'map_link'         => 'https://www.google.com/maps/search/?api=1&query=%C3%87etinkaya+Elektromekanik+%C4%B0zmit',
        'instagram'        => '',
        'facebook'         => '',
        'linkedin'         => '',
        'youtube'          => '',
        'brands'           => "Meksis|Modüler su depoları\nGrundfos|Pompa ve hidrofor sistemleri\nWilo|Pompa ve hidrofor sistemleri\nStandart Pompa|Pompa ve hidrofor sistemleri\nSumak|Pompa sistemleri",
        'hero_badge'       => 'Su depolama ve pompa sistemlerinde çözüm ortağınız',
        'hero_title'       => 'Suyunuzu güvenle depolayın, doğru basınçla ulaştırın.',
        'hero_text'        => 'Meksis modüler su depoları ile Grundfos, Wilo, Standart Pompa ve Sumak pompa ve hidrofor sistemlerini projenize uygun seçiyor, hızlı teklif ve montaj desteğiyle teslim ediyoruz.',
        'stats'            => "5|Güçlü Marka\n4|Ürün Grubu\n%100|Orijinal Ürün\n7/24|Online Teklif",
        'about_title'      => 'Doğru ürün, doğru kapasite, zamanında teslimat.',
        'about_text'       => "Çetinkaya Elektromekanik, İzmit merkezli olarak modüler su depoları, pompa ve hidrofor sistemlerinin satışını yapan bir firmadır.\n\nMeksis modüler su depoları ile Grundfos, Wilo, Standart Pompa ve Sumak markalarının ürünlerini; konut, site, otel, hastane, fabrika ve tarım projelerinin ihtiyacına göre seçiyor, kapasite hesabından teklife, sevkiyattan montaja kadar sürecin her adımında yanınızda oluyoruz.\n\nSatış sonrası keşif, montaj ve bakım desteğimizle kurduğumuz sistemlerin uzun yıllar sorunsuz çalışmasını hedefliyoruz.",
        'about_values'     => "Orijinal ve garantili ürün\nİhtiyaca göre doğru kapasite seçimi\nHızlı teklif ve şeffaf fiyatlandırma\nSatış sonrası montaj ve bakım desteği",
    ];
    $st = $pdo->prepare('INSERT OR REPLACE INTO settings(key, value) VALUES(?, ?)');
    foreach ($settings as $k => $v) {
        $st->execute([$k, $v]);
    }

    $cats = [
        ['Modüler Su Depoları', 'tank', 'Galvaniz, paslanmaz, GRP ve izolasyonlu panel seçenekleriyle her kapasiteye uygun modüler depolar.'],
        ['Hidrofor Sistemleri', 'booster', 'Binalarda sabit ve yeterli su basıncı için tek, çift ve çok pompalı hidrofor setleri.'],
        ['Santrifüj Pompalar', 'pump', 'Temiz su transferi, ısıtma-soğutma ve sulama için yatay ve dikey santrifüj pompalar.'],
        ['Dalgıç Pompalar', 'submersible', 'Kuyu, drenaj ve atık su uygulamaları için dalgıç pompa çözümleri.'],
    ];
    $st = $pdo->prepare('INSERT INTO product_categories(name, slug, art, summary, sort) VALUES(?, ?, ?, ?, ?)');
    foreach ($cats as $i => [$name, $art, $summary]) {
        $st->execute([$name, slugify($name), $art, $summary, $i + 1]);
    }

    $tankInfo = '<p>Modüler su depoları, standart ölçülerdeki panellerin sahada cıvatalı bağlantıyla birleştirilmesiyle kurulur. Bu sayede dar kapılardan ve merdivenlerden kolayca taşınır; bodrum, çatı veya teknik hacimlere istenen ölçüde depo kurulabilir.</p>';
    $products = [
        // [category, brand, featured, title, summary, content, specs]
        [1, 'Meksis', 1, 'Meksis Galvaniz Modüler Su Deposu',
            'Sıcak daldırma galvaniz panellerden oluşan, ekonomik ve dayanıklı modüler su deposu.',
            $tankInfo . '<h3>Öne çıkan özellikler</h3><ul><li>Sıcak daldırma galvaniz kaplı çelik paneller</li><li>Sahada hızlı ve kolay montaj</li><li>İhtiyaca göre genişletilebilir kapasite</li><li>Kullanma ve yangın suyu depolamaya uygun</li></ul>',
            "Marka|Meksis\nPanel malzemesi|Sıcak daldırma galvaniz çelik\nKapasite|Projeye göre\nKullanım alanı|Kullanma suyu, yangın suyu\nMontaj|Cıvatalı panel sistemi"],
        [1, 'Meksis', 1, 'Meksis Paslanmaz Çelik Modüler Su Deposu',
            'Hijyenik içme suyu depolaması için paslanmaz çelik panelli modüler su deposu.',
            $tankInfo . '<h3>Öne çıkan özellikler</h3><ul><li>Paslanmaz çelik paneller ile hijyenik depolama</li><li>Korozyona karşı yüksek dayanım</li><li>Kolay temizlenebilir yüzey</li><li>Uzun kullanım ömrü</li></ul>',
            "Marka|Meksis\nPanel malzemesi|Paslanmaz çelik\nKapasite|Projeye göre\nKullanım alanı|İçme ve kullanma suyu\nMontaj|Cıvatalı panel sistemi"],
        [1, 'Meksis', 0, 'Meksis GRP (CTP) Modüler Su Deposu',
            'Cam elyaf takviyeli polyester panellerden hafif ve korozyona dayanıklı modüler depo.',
            $tankInfo . '<h3>Öne çıkan özellikler</h3><ul><li>Hafif, korozyona dayanıklı GRP paneller</li><li>Işık geçirmeyen yapı ile yosunlaşmaya karşı koruma</li><li>Düşük bakım ihtiyacı</li></ul>',
            "Marka|Meksis\nPanel malzemesi|GRP (cam elyaf takviyeli polyester)\nKapasite|Projeye göre\nKullanım alanı|İçme ve kullanma suyu\nMontaj|Cıvatalı panel sistemi"],
        [1, 'Meksis', 0, 'Meksis İzolasyonlu (Sandviç Panel) Modüler Su Deposu',
            'Dış mekân ve soğuk iklimler için ısı yalıtımlı panellere sahip modüler su deposu.',
            $tankInfo . '<h3>Öne çıkan özellikler</h3><ul><li>Isı yalıtımlı sandviç panel yapısı</li><li>Donma ve aşırı ısınmaya karşı koruma</li><li>Çatı ve açık alan uygulamalarına uygun</li></ul>',
            "Marka|Meksis\nPanel yapısı|İzolasyonlu sandviç panel\nKapasite|Projeye göre\nKullanım alanı|Dış mekân, çatı uygulamaları\nMontaj|Cıvatalı panel sistemi"],
        [2, 'Grundfos', 1, 'Grundfos Çok Pompalı Hidrofor Seti',
            'Frekans kontrollü, yüksek verimli çok pompalı hidrofor sistemi.',
            '<p>Çok pompalı hidrofor setleri; apartman, site, otel ve hastane gibi değişken su tüketimi olan binalarda sabit basınç sağlar. Frekans kontrolü sayesinde pompalar ihtiyaç kadar çalışır, enerji tüketimi düşer.</p><h3>Öne çıkan özellikler</h3><ul><li>Frekans konvertörlü sabit basınç kontrolü</li><li>Pompalar arası otomatik sıralı çalışma</li><li>Kuru çalışma ve aşırı akım koruması</li><li>Kompakt, montaja hazır şase</li></ul>',
            "Marka|Grundfos\nPompa sayısı|2 – 6 (projeye göre)\nKontrol|Frekans kontrollü, sabit basınç\nKullanım alanı|Konut, site, otel, hastane, endüstri"],
        [2, 'Wilo', 1, 'Wilo Çift Pompalı Hidrofor Sistemi',
            'Asil-yedek veya paralel çalışma imkânı sunan çift pompalı hidrofor seti.',
            '<p>Çift pompalı hidrofor sistemleri, bir pompanın arıza veya bakım durumunda diğerinin devreye girmesiyle kesintisiz su sağlar.</p><h3>Öne çıkan özellikler</h3><ul><li>Asil-yedek ve paralel çalışma</li><li>Basınç tanklı veya frekans kontrollü seçenekler</li><li>Sessiz ve verimli çalışma</li></ul>',
            "Marka|Wilo\nPompa sayısı|2\nKontrol|Basınç şalterli / frekans kontrollü\nKullanım alanı|Apartman, işyeri, küçük tesis"],
        [2, 'Standart Pompa', 0, 'Standart Pompa Kompakt Hidrofor',
            'Müstakil ev ve küçük işletmeler için tek pompalı, kompakt hidrofor.',
            '<p>Tek pompalı kompakt hidroforlar; müstakil konut, bahçe ve küçük işletmelerde şebeke basıncının yetersiz olduğu durumlarda pratik bir çözümdür.</p><ul><li>Kompakt, kolay montaj</li><li>Basınç tanklı yapı</li><li>Düşük bakım ihtiyacı</li></ul>',
            "Marka|Standart Pompa\nPompa sayısı|1\nKullanım alanı|Müstakil konut, bahçe, küçük işletme"],
        [3, 'Grundfos', 1, 'Grundfos Dikey Çok Kademeli Pompa',
            'Yüksek basınç gerektiren uygulamalar için dikey çok kademeli santrifüj pompa.',
            '<p>Dikey çok kademeli pompalar; hidrofor setleri, kazan besleme, su arıtma ve endüstriyel proseslerde yüksek basınç ihtiyacını az yer kaplayarak karşılar.</p><ul><li>Az yer kaplayan dikey yapı</li><li>Yüksek verim</li><li>Paslanmaz çelik gövde seçenekleri</li></ul>',
            "Marka|Grundfos\nTip|Dikey çok kademeli santrifüj\nKullanım alanı|Basınçlandırma, kazan besleme, endüstri"],
        [3, 'Wilo', 0, 'Wilo Sirkülasyon Pompası',
            'Isıtma, soğutma ve kullanım sıcak suyu sistemleri için sirkülasyon pompası.',
            '<p>Sirkülasyon pompaları, kapalı devre ısıtma-soğutma sistemlerinde ve kullanım sıcak suyu hatlarında suyun dolaşımını sağlar.</p><ul><li>Enerji verimli motor seçenekleri</li><li>Sessiz çalışma</li><li>Kolay montaj</li></ul>',
            "Marka|Wilo\nTip|Sirkülasyon pompası\nKullanım alanı|Isıtma, soğutma, sıcak su"],
        [3, 'Sumak', 0, 'Sumak Yatay Santrifüj Pompa',
            'Temiz su transferi ve sulama için yatay milli santrifüj pompa.',
            '<p>Yatay santrifüj pompalar; su transferi, sulama ve genel amaçlı basınçlandırma uygulamalarında ekonomik ve güvenilir bir çözüm sunar.</p><ul><li>Sağlam döküm gövde</li><li>Kolay bakım</li><li>Geniş debi aralığı</li></ul>',
            "Marka|Sumak\nTip|Yatay santrifüj\nKullanım alanı|Su transferi, sulama, basınçlandırma"],
        [4, 'Standart Pompa', 1, 'Standart Pompa Derin Kuyu Dalgıç Pompası',
            'Derin kuyulardan su temini için paslanmaz çelik gövdeli dalgıç pompa.',
            '<p>Derin kuyu dalgıç pompaları; tarımsal sulama, içme suyu ve sanayi tesislerinde kuyudan su çekmek için kullanılır.</p><ul><li>Paslanmaz çelik gövde</li><li>Farklı kuyu çaplarına uygun seçenekler</li><li>Yüksek basma yüksekliği</li></ul>',
            "Marka|Standart Pompa\nTip|Derin kuyu dalgıç pompa\nKullanım alanı|Sulama, içme suyu, sanayi"],
        [4, 'Sumak', 0, 'Sumak Drenaj Dalgıç Pompası',
            'Bodrum, asansör kuyusu ve şantiyelerde su tahliyesi için drenaj pompası.',
            '<p>Drenaj dalgıç pompaları; su basan bodrumlar, asansör kuyuları, şantiyeler ve rögarlarda biriken suyun tahliyesi için kullanılır.</p><ul><li>Şamandıralı otomatik çalışma seçeneği</li><li>Taşınabilir, hafif yapı</li><li>Kolay kurulum</li></ul>',
            "Marka|Sumak\nTip|Drenaj dalgıç pompa\nKullanım alanı|Bodrum, asansör kuyusu, şantiye"],
    ];
    $models = ['tank:galvaniz', 'tank:paslanmaz', 'tank:grp', 'tank:sandvic', 'booster:3', 'booster:2', 'booster:1', 'pump:vertical', 'pump:circulator', 'pump:horizontal', 'sub:deep', 'sub:drain'];
    $st = $pdo->prepare('INSERT INTO products(category_id, brand, featured, title, slug, summary, content, specs, sort, model) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($products as $i => [$cat, $brand, $featured, $title, $summary, $content, $specs]) {
        $st->execute([$cat, $brand, $featured, $title, slugify($title), $summary, $content, $specs, $i + 1, $models[$i]]);
    }

    $services = [
        ['Keşif & Projelendirme', 'ruler', 'Yerinde keşif, su ihtiyacı ve depo/pompa kapasite hesabı ile projenize en uygun sistemin belirlenmesi.',
            '<p>Doğru ürün seçimi, doğru hesapla başlar. Uzman ekibimiz yerinde keşif yaparak kullanım amacını, kişi/ünite sayısını, bina yüksekliğini ve mevcut tesisatı değerlendirir.</p><h3>Neleri hesaplıyoruz?</h3><ul><li>Günlük su ihtiyacı ve depo hacmi</li><li>Depo yerleşimi ve panel ölçüleri</li><li>Gerekli debi ve basma yüksekliği</li><li>Pompa sayısı ve kontrol tipi</li></ul><p>Keşif sonrası size ürün ve kapasite önerisiyle birlikte detaylı teklif sunuyoruz.</p>'],
        ['Montaj & Kurulum', 'wrench', 'Modüler su deposu montajı, hidrofor ve pompa kurulumu ile devreye alma.',
            '<p>Satın aldığınız modüler su depoları ile pompa ve hidrofor sistemlerinin montajını deneyimli ekiplerimizle yapıyoruz.</p><ul><li>Modüler depo panel montajı ve sızdırmazlık testi</li><li>Hidrofor ve pompa yerleşimi</li><li>Elektrik bağlantıları ve devreye alma</li><li>Çalışma testleri ve kullanıcı bilgilendirmesi</li></ul>'],
        ['Bakım & Servis', 'gauge', 'Pompa ve hidrofor sistemleri için periyodik bakım, arıza tespiti ve onarım desteği.',
            '<p>Satış sonrası desteğimizle sistemlerinizin verimli ve kesintisiz çalışmasını sağlıyoruz.</p><ul><li>Periyodik bakım ve kontrol</li><li>Basınç tankı ve şalter ayarları</li><li>Arıza tespiti ve onarım</li><li>Depo temizliği ve dezenfeksiyon yönlendirmesi</li></ul>'],
        ['Hızlı Sevkiyat', 'truck', 'Stoktaki ürünlerde hızlı teslimat, proje ürünlerinde planlı sevkiyat.',
            '<p>Stokta bulunan pompa ve hidrofor ürünlerini hızlıca teslim ediyor, modüler depo ve proje ürünlerinde üretim ve sevkiyat planını sizinle birlikte yapıyoruz.</p>'],
    ];
    $st = $pdo->prepare('INSERT INTO services(title, slug, icon, summary, content, sort) VALUES(?, ?, ?, ?, ?, ?)');
    foreach ($services as $i => [$title, $icon, $summary, $content]) {
        $st->execute([$title, slugify($title), $icon, $summary, $content, $i + 1]);
    }

    $faqs = [
        ['Teklif almak için hangi bilgiler gerekli?', 'Modüler depo için ihtiyaç duyduğunuz yaklaşık hacim (m³) ve depo kurulacak alanın ölçüleri; pompa ve hidrofor için binadaki kat/daire sayısı veya ihtiyaç duyulan debi ve basınç bilgisi yeterlidir. Emin değilseniz sadece kullanım amacını yazmanız yeterli, ekibimiz sizi arayarak birlikte belirler.'],
        ['Teklife ne kadar sürede dönüş yapıyorsunuz?', 'Standart ürünlerde genellikle aynı gün, projeye özel taleplerde keşif veya bilgi tamamlandıktan sonra en kısa sürede teklif iletiyoruz.'],
        ['Modüler su deposu hangi ölçülerde kurulabilir?', 'Modüler depolar standart panellerin birleştirilmesiyle kurulduğu için, alanınıza uygun farklı en-boy-yükseklik kombinasyonlarında ve ihtiyacınız olan hacimde tasarlanabilir.'],
        ['Hangi markaların ürünlerini satıyorsunuz?', 'Meksis modüler su depoları ile Grundfos, Wilo, Standart Pompa ve Sumak markalarının pompa ve hidrofor ürünlerini satıyoruz.'],
        ['Montaj ve servis hizmeti veriyor musunuz?', 'Evet. Satışını yaptığımız ürünler için keşif, montaj, devreye alma ve periyodik bakım desteği sunuyoruz.'],
    ];
    $st = $pdo->prepare('INSERT INTO faqs(question, answer, sort) VALUES(?, ?, ?)');
    foreach ($faqs as $i => [$q, $a]) {
        $st->execute([$q, $a, $i + 1]);
    }

    $blogCats = ['Rehber', 'Teknik Bilgi', 'Duyurular'];
    $st = $pdo->prepare('INSERT INTO categories(name, slug) VALUES(?, ?)');
    foreach ($blogCats as $c) {
        $st->execute([$c, slugify($c)]);
    }

    $posts = [
        [1, 1, 'Su Deposu Kapasitesi Nasıl Hesaplanır?',
            'Binanız için doğru su deposu hacmini belirlemek; kişi sayısı, günlük tüketim ve kesinti süresine bağlıdır. Adım adım hesaplama rehberi.',
            '<p>Su deposu seçerken en sık yapılan hata, kapasitenin ihtiyaçtan küçük ya da gereğinden büyük seçilmesidir. Küçük depo kesintilerde yetersiz kalır; çok büyük depo ise suyun uzun süre beklemesine ve gereksiz maliyete yol açar.</p><h2>1. Günlük su ihtiyacını belirleyin</h2><p>Konutlarda kişi başı günlük su tüketimi için genellikle <strong>150–200 litre</strong> arası bir değer esas alınır. Örneğin 20 daireli, daire başına ortalama 4 kişinin yaşadığı bir binada:</p><blockquote>20 daire × 4 kişi × 150 litre = 12.000 litre (12 m³) / gün</blockquote><h2>2. Kaç günlük yedek istediğinize karar verin</h2><p>Bölgenizdeki kesinti sıklığına göre depo genellikle <strong>1–2 günlük</strong> ihtiyaca göre boyutlandırılır. Yukarıdaki örnekte 1,5 günlük yedek için yaklaşık 18 m³ hacim gerekir.</p><h2>3. Yangın suyu ihtiyacını ekleyin</h2><p>Yangın tesisatı olan binalarda yangın suyu için ayrılması gereken hacim, ilgili yönetmelik ve proje hesaplarına göre ayrıca eklenmelidir.</p><h2>4. Yerleşim alanını ölçün</h2><p>Modüler depolar standart panellerle kurulduğu için alanınıza göre farklı en-boy-yükseklik kombinasyonları oluşturulabilir. Kapı ve merdiven ölçüleri kurulumu engellemez; paneller içeride birleştirilir.</p><p>Hesaplamada emin olamadığınız noktalar için bize ulaşın; ücretsiz ön değerlendirme ile size uygun depo hacmini birlikte belirleyelim.</p>'],
        [1, 1, 'Hidrofor Seçerken Dikkat Edilmesi Gereken 6 Nokta',
            'Kat sayısı, daire sayısı, pompa sayısı ve kontrol tipi… Binanız için doğru hidroforu seçmenin püf noktaları.',
            '<p>Hidrofor, şebeke basıncının yetersiz kaldığı binalarda suyu istenen basınçla musluklara ulaştıran sistemdir. Doğru seçim hem konforu hem de enerji maliyetini doğrudan etkiler.</p><h2>1. Gerekli debi</h2><p>Aynı anda kullanılan musluk ve cihaz sayısına göre saatlik su ihtiyacı (m³/saat) belirlenir.</p><h2>2. Gerekli basınç</h2><p>Bina yüksekliği, en üst kattaki musluk için gereken basınç ve boru kayıpları hesaba katılır.</p><h2>3. Pompa sayısı</h2><p>Tek pompalı sistemler küçük yapılar için yeterlidir. Apartman ve sitelerde <strong>çift veya çok pompalı</strong> sistemler, bir pompa arızalandığında suyun kesilmemesini sağlar.</p><h2>4. Kontrol tipi</h2><p><strong>Frekans kontrollü</strong> sistemler pompayı ihtiyaç kadar çalıştırır; basınç sabit kalır ve enerji tüketimi belirgin şekilde azalır.</p><h2>5. Basınç tankı</h2><p>Doğru boyutta genleşme tankı, pompanın sık dur-kalk yapmasını önler ve ömrünü uzatır.</p><h2>6. Marka ve servis desteği</h2><p>Yedek parça bulunabilirliği ve satış sonrası destek, uzun vadede en az ürün kadar önemlidir.</p>'],
        [2, 0, 'Modüler Su Deposu mu, Betonarme Depo mu?',
            'Modüler su depolarının betonarme depolara göre avantajlarını; kurulum, hijyen, bakım ve maliyet açısından karşılaştırdık.',
            '<p>Binalarda su depolamak için geleneksel olarak betonarme depolar kullanılır. Ancak son yıllarda <strong>modüler su depoları</strong> birçok avantajıyla öne çıkıyor.</p><h2>Kurulum hızı</h2><p>Betonarme depolar kalıp, beton dökümü ve kür süresi gerektirir. Modüler depolar ise hazır panellerin sahada birleştirilmesiyle kısa sürede kurulur.</p><h2>Hijyen</h2><p>Betonarme depolarda zamanla çatlak, sızıntı ve yosunlaşma görülebilir. Paslanmaz çelik veya GRP panelli modüler depolar hijyenik yüzeyleri sayesinde kolay temizlenir.</p><h2>Taşınabilirlik ve esneklik</h2><p>Paneller dar alanlardan taşınabilir; ihtiyaç arttığında depo genişletilebilir, taşınmak gerektiğinde sökülüp yeniden kurulabilir.</p><h2>Bakım</h2><p>Modüler depolarda bakım ve temizlik için içeri erişim kolaydır; hasarlı bir panel tek başına değiştirilebilir.</p><table><thead><tr><th></th><th>Modüler depo</th><th>Betonarme depo</th></tr></thead><tbody><tr><td>Kurulum</td><td>Hızlı</td><td>Uzun</td></tr><tr><td>Hijyen</td><td>Yüksek</td><td>Zamanla azalır</td></tr><tr><td>Genişletme</td><td>Mümkün</td><td>Zor</td></tr><tr><td>Taşınabilirlik</td><td>Var</td><td>Yok</td></tr></tbody></table>'],
        [3, 0, 'Online Teklif Sistemimiz Yayında',
            'Artık ihtiyacınız olan ürünler için web sitemiz üzerinden dakikalar içinde teklif talebi oluşturabilirsiniz.',
            '<p>Müşterilerimize daha hızlı hizmet verebilmek için web sitemizde <strong>online teklif sistemini</strong> devreye aldık.</p><h2>Nasıl çalışır?</h2><ol><li><a href="/urunler">Ürünler</a> sayfasından ihtiyacınız olan ürünü seçin veya doğrudan <a href="/teklif-al">Teklif Al</a> formunu doldurun.</li><li>Talebiniz ekibimize anında ulaşır ve size bir talep numarası verilir.</li><li>Satış ekibimiz sizinle iletişime geçerek ihtiyacınıza uygun teklifi hazırlar.</li></ol>'],
    ];
    $st = $pdo->prepare("INSERT INTO posts(category_id, featured, title, slug, excerpt, content, status, published_at) VALUES(?, ?, ?, ?, ?, ?, 'published', ?)");
    foreach ($posts as $i => [$cat, $featured, $title, $excerpt, $content]) {
        $st->execute([$cat, $featured, $title, slugify($title), $excerpt, $content, date('Y-m-d H:i:s', strtotime('-' . ($i * 9 + 2) . ' days'))]);
    }

    $pdo->commit();
}
