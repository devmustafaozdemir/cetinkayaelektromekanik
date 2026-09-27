<?php
declare(strict_types=1);

/** Initial content, written once when the database is first created. Everything is editable in the admin panel. */
function db_seed(): void
{
    $pdo = db();
    $pdo->beginTransaction();

    $settings = [
        'site_name'        => 'Çetinkaya Elektromekanik',
        'site_tagline'     => 'Makita · Metabo · HiKOKI Yetkili Servisi',
        'meta_description' => 'İzmit / Kocaeli\'de Makita, Metabo ve HiKOKI (Hitachi) yetkili servisi. Elektrikli el aletleri onarımı, motor bobinajı, orijinal yedek parça ve online servis takibi.',
        'phone'            => '0262 335 13 20',
        'phone2'           => '0533 033 79 04',
        'whatsapp'         => '0533 033 79 04',
        'email'            => 'info@cetinkayaelektromekanik.com.tr',
        'address'          => 'Sanayi Mah. 12. Cad. İzmit Sanayi Sitesi 11. Blok No:1, 41040 İzmit / Kocaeli',
        'hours'            => "Pazartesi – Cuma: 08:30 – 18:30\nCumartesi: 08:30 – 14:00\nPazar: Kapalı",
        'map_embed'        => 'https://www.google.com/maps?q=%C4%B0zmit+Sanayi+Sitesi+11.+Blok+Kocaeli&output=embed',
        'map_link'         => 'https://www.google.com/maps/search/?api=1&query=%C3%87etinkaya+Elektromekanik+%C4%B0zmit',
        'instagram'        => '',
        'facebook'         => '',
        'linkedin'         => '',
        'youtube'          => '',
        'brands'           => "Makita\nMetabo\nHiKOKI",
        'hero_badge'       => 'Kocaeli\'nin güvenilir yetkili servisi',
        'hero_title'       => 'Elektrikli aletleriniz ilk günkü performansına kavuşsun.',
        'hero_text'        => 'Makita, Metabo ve HiKOKI yetkili servisi olarak; profesyonel el aletleri onarımı, motor bobinajı ve orijinal yedek parça tedarikini tek çatı altında sunuyoruz. Cihazınızın durumunu online takip edin.',
        'stats'            => "3|Yetkili Marka\n100%|Orijinal Yedek Parça\n7/24|Online Servis Takibi\n1|Merkezi Servis Noktası",
        'about_title'      => 'Atölyemizde her cihaz, ustalık ve özenle yeniden hayat bulur.',
        'about_text'       => "Çetinkaya Elektromekanik, İzmit Sanayi Sitesi'nde profesyonel ve endüstriyel elektrikli el aletlerinin satış sonrası hizmetlerini sunan bir yetkili servis işletmesidir.\n\nMakita, Metabo ve HiKOKI (eski adıyla Hitachi) markalarının yetkili servisi olarak garanti kapsamındaki ve garanti dışındaki tüm onarımları üretici standartlarında, orijinal yedek parçalarla gerçekleştiriyoruz. Bireysel ustalardan sanayi kuruluşlarına kadar geniş bir müşteri kitlesine hızlı, şeffaf ve güvenilir hizmet vermeyi ilke ediniyoruz.\n\nArıza tespitinden teslimata kadar her aşamada sizi bilgilendiriyor, onayınız olmadan işlem yapmıyoruz.",
        'about_values'     => "Orijinal yedek parça garantisi\nÜretici standartlarında test ve kalibrasyon\nOnayınız alınmadan işlem yapılmaz\nŞeffaf fiyatlandırma ve hızlı teslim",
    ];
    $st = $pdo->prepare('INSERT OR REPLACE INTO settings(key, value) VALUES(?, ?)');
    foreach ($settings as $k => $v) {
        $st->execute([$k, $v]);
    }

    $services = [
        ['Elektrikli El Aletleri Onarımı', 'drill', 'Matkap, kırıcı-delici, taşlama, daire testere, planya ve tüm profesyonel el aletlerinin arıza tespiti ve onarımı.',
            '<p>Profesyonel kullanımdaki elektrikli el aletleri yoğun çalışma koşullarında aşınır. Atölyemizde cihazınızı söküp detaylı arıza tespiti yapıyor, yalnızca gerekli parçaları değiştiriyoruz.</p><h3>Onarımını yaptığımız cihazlar</h3><ul><li>Darbeli ve darbesiz matkaplar, vidalamalar</li><li>Kırıcı-deliciler ve kırıcılar</li><li>Avuç ve büyük taşlama makineleri</li><li>Daire, dekupaj, tilki kuyruğu ve gönye testereler</li><li>Planya, freze, zımpara ve polisaj makineleri</li><li>Akülü aletler ve şarj cihazları</li></ul><h3>Süreç</h3><p>Cihaz teslim alındıktan sonra arıza tespiti yapılır, fiyat bilgisi size iletilir ve onayınızla onarıma başlanır. Onarım sonrası cihazınız yük altında test edilerek teslim edilir.</p>'],
        ['Motor Bobinajı & Sarım', 'cog', 'Yanan rotor ve statorların hassas sarımı; endüstriyel elektrik motorlarının bakım ve onarımı.',
            '<p>Aşırı yüklenme, nem ya da kömür aşınması nedeniyle yanan motor sargıları, doğru tel kesiti ve izolasyon sınıfı kullanılarak yeniden sarılır.</p><h3>Hizmet kapsamı</h3><ul><li>Rotor (endüvi) ve stator sarımı</li><li>Kollektör tornalama ve temizliği</li><li>Rulman, kömür ve kömür yuvası değişimi</li><li>İzolasyon (megger) ve balans testleri</li></ul><p>Bobinaj sonrası her motor, yüksüz ve yük altında test edilir.</p>'],
        ['Yetkili Garanti Servisi', 'shield', 'Makita, Metabo ve HiKOKI ürünleri için üretici onaylı garanti kapsamında ücretsiz onarım.',
            '<p>Garanti süresi devam eden Makita, Metabo ve HiKOKI ürünleriniz, üretici prosedürlerine uygun şekilde yetkili servisimizde onarılır.</p><h3>Garanti başvurusu için</h3><ul><li>Fatura veya garanti belgesi</li><li>Cihazın tüm aksesuarlarıyla birlikte teslimi</li><li>Arızanın kısa açıklaması</li></ul><p>Kullanım hatası, düşme veya yetkisiz müdahale kaynaklı arızalar garanti kapsamı dışındadır; bu durumda onarım öncesi fiyat onayınız alınır.</p>'],
        ['Orijinal Yedek Parça', 'package', 'Kömür, rotor, stator, şalter, dişli ve tüm yedek parçalarda orijinal ürün tedariki.',
            '<p>Cihazınızın ömrünü ve güvenliğini doğrudan etkileyen yedek parçalarda yalnızca orijinal ürün kullanıyoruz.</p><ul><li>Karbon fırçalar (kömür)</li><li>Rotor, stator ve alan bobinleri</li><li>Şalterler, kablolar, dişli grupları</li><li>Akü ve şarj cihazları</li></ul><p>Stokta bulunmayan parçalar distribütör üzerinden kısa sürede temin edilir.</p>'],
        ['Periyodik Bakım', 'calendar', 'Cihazlarınızın ömrünü uzatan temizlik, yağlama, kömür kontrolü ve güvenlik testleri.',
            '<p>Düzenli bakım, arıza oranını ve beklenmedik iş kayıplarını azaltır. Periyodik bakımda cihazınız sökülerek temizlenir, yağlanır ve aşınan parçalar raporlanır.</p><ul><li>İç temizlik ve toz tahliyesi</li><li>Dişli kutusu gres yenileme</li><li>Kömür ve kollektör kontrolü</li><li>Kablo, şalter ve elektrik güvenlik testleri</li></ul>'],
        ['Kurumsal Servis Çözümleri', 'building', 'Sanayi kuruluşları ve şantiyeler için toplu bakım, öncelikli servis ve cihaz parkı yönetimi.',
            '<p>Fabrika, atölye ve şantiyelerdeki cihaz parkınız için planlı bakım programları oluşturuyor, öncelikli servis sunuyoruz.</p><ul><li>Toplu cihaz teslim alma ve teslim</li><li>Cihaz bazlı servis geçmişi</li><li>Öncelikli arıza tespiti</li><li>Kurumsal fiyatlandırma</li></ul>'],
    ];
    $st = $pdo->prepare('INSERT INTO services(title, slug, icon, summary, content, sort) VALUES(?, ?, ?, ?, ?, ?)');
    foreach ($services as $i => [$title, $icon, $summary, $content]) {
        $st->execute([$title, slugify($title), $icon, $summary, $content, $i + 1]);
    }

    $faqs = [
        ['Arıza tespiti ücretli mi?', 'Garanti kapsamındaki cihazlarda arıza tespiti ve onarım ücretsizdir. Garanti dışı cihazlarda arıza tespiti sonrası size fiyat bilgisi verilir; onay vermezseniz cihazınızı iade alabilirsiniz.'],
        ['Onarım ne kadar sürer?', 'Standart arızalar çoğunlukla birkaç iş günü içinde tamamlanır. Parça tedariki gereken durumlarda süre parçanın temin süresine bağlıdır. Cihazınızın durumunu Servis Takip sayfasından anlık izleyebilirsiniz.'],
        ['Hangi markalara hizmet veriyorsunuz?', 'Makita, Metabo ve HiKOKI (Hitachi) markalarının yetkili servisiyiz. Diğer marka profesyonel el aletleri ve elektrik motorları için de onarım ve bobinaj hizmeti veriyoruz.'],
        ['Garanti başvurusu için neler gerekli?', 'Fatura ya da garanti belgesi ile cihazın kendisini getirmeniz yeterlidir. Mümkünse cihazı aksesuarlarıyla birlikte teslim etmeniz arıza tespitini hızlandırır.'],
        ['Servis talebimin durumunu nasıl öğrenirim?', 'Talep oluşturduğunuzda size bir takip kodu verilir. Servis Takip sayfasında takip kodunuz ve telefon numaranızla cihazınızın hangi aşamada olduğunu görebilirsiniz.'],
    ];
    $st = $pdo->prepare('INSERT INTO faqs(question, answer, sort) VALUES(?, ?, ?)');
    foreach ($faqs as $i => [$q, $a]) {
        $st->execute([$q, $a, $i + 1]);
    }

    $cats = ['Bakım Rehberi', 'Teknik Bilgi', 'Duyurular'];
    $st = $pdo->prepare('INSERT INTO categories(name, slug) VALUES(?, ?)');
    foreach ($cats as $c) {
        $st->execute([$c, slugify($c)]);
    }

    $posts = [
        [1, 1, 'Elektrikli El Aletinizin Kömürünü Ne Zaman Değiştirmelisiniz?',
            'Kıvılcım artışı, güç kaybı ve kesik çalışma kömür aşınmasının ilk işaretleridir. Kömür değişim zamanını doğru belirleyerek motorunuzu koruyun.',
            '<p>Fırçalı motorlu elektrikli el aletlerinde <strong>karbon fırçalar (kömürler)</strong>, akımı dönen rotora ileten sarf parçalardır. Zamanla aşınmaları normaldir; ancak geç değiştirildiklerinde kollektöre ve rotora kalıcı hasar verebilirler.</p><h2>Kömür aşınmasının belirtileri</h2><ul><li>Havalandırma deliklerinden gelen kıvılcımda belirgin artış</li><li>Cihazın kesik kesik çalışması veya hiç çalışmaması</li><li>Güç ve devir kaybı</li><li>Yanık kokusu</li></ul><h2>Ne sıklıkla kontrol edilmeli?</h2><p>Yoğun profesyonel kullanımda her <strong>100–150 çalışma saatinde</strong> bir kontrol önerilir. Kömür boyu yaklaşık 5–6 mm\'nin altına düştüğünde değişim yapılmalıdır. Birçok Makita modelinde bulunan otomatik kesme (auto-stop) kömürleri, kritik seviyede motoru durdurarak rotoru korur.</p><h2>Neden orijinal kömür?</h2><p>Kömürün sertliği ve iletkenliği motor tasarımına göre belirlenir. Uyumsuz kömürler kollektörü çizer, kıvılcımı artırır ve rotorun yanmasına yol açabilir. Kömürleri her zaman <strong>çift olarak</strong> ve orijinal parça ile değiştirin.</p><blockquote>Kömür değişimi küçük bir masraftır; ihmal edildiğinde ise rotor değişimi gerekebilir.</blockquote><p>Cihazınızın kömür kontrolü için servisimize uğrayabilir veya online servis talebi oluşturabilirsiniz.</p>'],
        [1, 1, 'Akülü Aletlerde Batarya Ömrünü Uzatmanın 7 Yolu',
            'Li-ion bataryalar doğru kullanıldığında yıllarca ilk günkü performansını korur. Şarj, depolama ve kullanım alışkanlıklarınızı gözden geçirin.',
            '<p>Akülü el aletlerinin en değerli bileşeni bataryadır. Aşağıdaki basit alışkanlıklarla bataryanızın ömrünü belirgin şekilde uzatabilirsiniz.</p><h2>1. Aşırı sıcak ve soğuktan koruyun</h2><p>Bataryayı güneş altında araç içinde veya dondurucu soğukta bırakmayın. İdeal çalışma ve depolama sıcaklığı 10–25 °C arasıdır.</p><h2>2. Sıcak bataryayı hemen şarj etmeyin</h2><p>Yoğun kullanım sonrası ısınan bataryanın soğumasını bekleyin. Orijinal hızlı şarj cihazlarının fan soğutma özelliği bu süreyi kısaltır.</p><h2>3. Orijinal şarj cihazı kullanın</h2><p>Orijinal şarj cihazları, hücreleri dengeleyerek ve sıcaklığı izleyerek şarj eder.</p><h2>4. Tamamen boşaltmayın</h2><p>Li-ion hücrelerin tamamen boşalması ömürlerini kısaltır. Güç düştüğünde şarja takın.</p><h2>5. Uzun süreli depolamada %40–60 doluluk</h2><p>Aylarca kullanılmayacak bataryaları yarı dolu, serin ve kuru bir yerde saklayın.</p><h2>6. Kontakları temiz tutun</h2><p>Toz ve metal talaşı kontaklarda temassızlığa ve ısınmaya yol açar.</p><h2>7. Darbelere dikkat</h2><p>Düşürülen bataryalarda iç hücre hasarı oluşabilir. Şişme, aşırı ısınma veya koku fark ederseniz bataryayı kullanmayı bırakıp servise getirin.</p>'],
        [2, 0, 'Motor Bobinajı Nedir, Ne Zaman Gerekir?',
            'Yanık kokusu, dumanlanma veya motorun hiç dönmemesi sargı arızasına işaret edebilir. Bobinaj sürecini ve önleyici tedbirleri anlattık.',
            '<p><strong>Bobinaj</strong>, elektrik motorlarının manyetik alanı oluşturan bakır sargılarının yeniden sarılması işlemidir. Aşırı yük, nem, toz ya da izolasyon yaşlanması sonucu sargılar yanabilir.</p><h2>Sargı arızasının belirtileri</h2><ul><li>Motordan yanık kokusu ve duman gelmesi</li><li>Sigortanın ya da kaçak akım rölesinin attırması</li><li>Motorun uğultu yapıp dönmemesi</li><li>Kollektörde halka şeklinde kıvılcım</li></ul><h2>Bobinaj süreci</h2><ol><li>Motor sökülür ve eski sargı sökülerek tel kesiti, sarım sayısı kayıt altına alınır.</li><li>Oluklar temizlenir, yeni izolasyon kağıtları yerleştirilir.</li><li>Uygun kesitte emaye bakır tel ile orijinal şemaya göre sarım yapılır.</li><li>Sargı vernikle emprenye edilir ve fırınlanır.</li><li>Megger, yüksüz ve yük altında testler yapılır.</li></ol><h2>Yeniden sarım mı, parça değişimi mi?</h2><p>Küçük el aletlerinde orijinal rotor/stator değişimi çoğunlukla daha ekonomik ve hızlıdır. Endüstriyel motorlarda ise bobinaj, yeni motor maliyetine göre ciddi tasarruf sağlar. Hangisinin uygun olduğunu arıza tespiti sonrası birlikte değerlendiriyoruz.</p>'],
        [3, 0, 'Online Servis Takip Sistemimiz Yayında',
            'Artık cihazınızın servis sürecini takip kodunuzla internet üzerinden anlık olarak izleyebilirsiniz.',
            '<p>Müşterilerimize daha şeffaf bir hizmet sunmak amacıyla <strong>online servis takip sistemimizi</strong> devreye aldık.</p><h2>Nasıl çalışır?</h2><ol><li>Web sitemizdeki <a href="/servis-talebi">Servis Talebi</a> formunu doldurun ya da cihazınızı doğrudan servisimize getirin.</li><li>Size özel bir takip kodu oluşturulur.</li><li><a href="/servis-takip">Servis Takip</a> sayfasında takip kodu ve telefon numaranızla cihazınızın durumunu görüntüleyin.</li></ol><p>Arıza tespiti, fiyat onayı, onarım ve teslime hazır aşamalarının tamamını buradan izleyebilirsiniz.</p>'],
    ];
    $st = $pdo->prepare("INSERT INTO posts(category_id, featured, title, slug, excerpt, content, status, published_at) VALUES(?, ?, ?, ?, ?, ?, 'published', ?)");
    foreach ($posts as $i => [$cat, $featured, $title, $excerpt, $content]) {
        $st->execute([$cat, $featured, $title, slugify($title), $excerpt, $content, date('Y-m-d H:i:s', strtotime('-' . ($i * 9 + 2) . ' days'))]);
    }

    $pdo->commit();
}
