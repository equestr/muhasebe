<?php
require('_class/config.php');
require('session.php');

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * AI Destekli Finansal Analiz Sistemi
 * Version: 1.0.0
 * 
 * Bu sistem:
 * 1. Akıllı kategori sınıflandırması
 * 2. Detaylı finansal analiz
 * 3. Trend analizi
 * 4. Tahmine dayalı öneriler
 * 5. Otomatik kategorizasyon
 * sağlar.
 */

// Yardımcı Fonksiyonlar
class AIHelper {
    // Para formatı
    public static function formatMoney($amount) {
        return number_format($amount, 2, ',', '.') . ' ₺';
    }
    
    // Tarih formatı
    public static function formatDate($date) {
        $tr_months = [
            'January' => 'Ocak', 'February' => 'Şubat', 'March' => 'Mart',
            'April' => 'Nisan', 'May' => 'Mayıs', 'June' => 'Haziran',
            'July' => 'Temmuz', 'August' => 'Ağustos', 'September' => 'Eylül',
            'October' => 'Ekim', 'November' => 'Kasım', 'December' => 'Aralık'
        ];
        
        $eng_date = date('d F Y', strtotime($date));
        return str_replace(array_keys($tr_months), array_values($tr_months), $eng_date);
    }
    
    // Yüzde hesaplama
    public static function calculatePercentage($part, $total) {
        if($total == 0) return 0;
        return round(($part / $total) * 100, 2);
    }
    
    // Trend analizi
    public static function analyzeTrend($current, $previous) {
        if($previous == 0) return ['type' => 'neutral', 'percentage' => 0];
        
        $change = (($current - $previous) / $previous) * 100;
        
        return [
            'type' => $change > 0 ? 'increase' : ($change < 0 ? 'decrease' : 'neutral'),
            'percentage' => abs(round($change, 2))
        ];
    }
    
    // Renk kodları
    public static function getColorCode($index) {
        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF9F40'
        ];
        return $colors[$index % count($colors)];
    }
    
    // Metin benzerlik analizi
    public static function calculateSimilarity($str1, $str2) {
        $str1 = mb_strtolower(trim($str1), 'UTF-8');
        $str2 = mb_strtolower(trim($str2), 'UTF-8');
        
        similar_text($str1, $str2, $percent);
        return $percent;
    }
}

// Ana Sınıflandırma Kategorileri
$category_groups = [
    // Kategori tanımları buraya gelecek
];

// Analiz Motoru Sınıfı
class ExpenseAnalyzer {
    private $db;
    private $user_id;
    private $categories;
    private $analysis_data;
    
    public function __construct($db, $user_id, $categories) {
        $this->db = $db;
        $this->user_id = $user_id;
        $this->categories = $categories;
        $this->analysis_data = [
            'categories' => [],
            'trends' => [],
            'suggestions' => [],
            'anomalies' => []
        ];
    }
    
    // Metodlar buraya gelecek
}

// Analizci sınıfını başlat
$analyzer = new ExpenseAnalyzer($pia, $admin->id, $category_groups);
// Kategori tanımları - Çok detaylı anahtar kelimeler ve alt kategoriler
$category_groups = [
    'Araç Giderleri' => [
        'keywords' => [
            // Yakıt Terimleri
            'benzin', 'mazot', 'dizel', 'lpg', 'yakıt', 'akaryakıt', 
            'motorin', 'euro diesel', 'kurşunsuz', '95 oktan', '97 oktan',
            
            // Marka Bazlı Yakıt İstasyonları
            'shell', 'bp', 'opet', 'total', 'petrol ofisi', 'po', 'lukoil', 
            'aytemiz', 'go', 'alpet', 'petrol', 'akpet', 'sunpet',
            
            // Araç Markaları
            'mercedes', 'bmw', 'audi', 'volkswagen', 'vw', 'ford', 'fiat',
            'renault', 'toyota', 'honda', 'hyundai', 'kia', 'skoda', 'seat',
            'peugeot', 'citroen', 'opel', 'nissan', 'mazda', 'volvo',
            
            // Servis ve Bakım
            'servis', 'bakım', 'tamir', 'onarım', 'revizyon', 'kontrol',
            'periyodik bakım', 'yağ değişimi', 'yağ bakım', 'motor bakım',
            'fren bakım', 'balata değişim', 'lastik değişim', 'rot balans',
            
            // Yedek Parça
            'yedek parça', 'filtre', 'yağ filtresi', 'hava filtresi',
            'polen filtresi', 'yakıt filtresi', 'fren balata', 'debriyaj',
            'amortisör', 'rotbaşı', 'rotil', 'salıncak', 'makas',
            
            // Lastik Markaları ve Terimleri
            'michelin', 'bridgestone', 'goodyear', 'pirelli', 'continental',
            'dunlop', 'hankook', 'kumho', 'lassa', 'petlas', 'yazlık lastik',
            'kışlık lastik', '4 mevsim lastik', 'lastik değişim',
            
            // Araç Temizlik ve Bakım
            'oto yıkama', 'araç yıkama', 'detaylı temizlik', 'iç temizlik',
            'dış temizlik', 'motor temizlik', 'boya koruma', 'seramik kaplama',
            'pasta cila', 'cam filmi', 'koltuk temizliği',
            
            // Resmi İşlemler
            'mtv', 'motorlu taşıtlar vergisi', 'trafik sigortası', 'kasko',
            'muayene', 'egzoz muayene', 'fenni muayene', 'ruhsat', 'noter',
            'plaka', 'trafik cezası', 'ogs', 'hgs', 'köprü geçiş'
        ],
        'sub_categories' => [
            'Yakıt' => [
                'icon' => 'fa-gas-pump',
                'keywords' => ['benzin', 'mazot', 'dizel', 'lpg', 'yakıt']
            ],
            'Bakım' => [
                'icon' => 'fa-wrench',
                'keywords' => ['servis', 'bakım', 'tamir', 'onarım']
            ],
            'Lastik' => [
                'icon' => 'fa-ring',
                'keywords' => ['lastik', 'jant', 'balans', 'rot']
            ],
            'Temizlik' => [
                'icon' => 'fa-spray-can',
                'keywords' => ['yıkama', 'temizlik', 'cila', 'koltuk']
            ],
            'Vergiler' => [
                'icon' => 'fa-file-invoice-dollar',
                'keywords' => ['mtv', 'vergi', 'sigorta', 'muayene']
            ]
        ],
        'icon' => 'fa-car',
        'color' => 'danger',
        'analysis_rules' => [
            'high_frequency' => [
                'daily' => 1,
                'weekly' => 3,
                'monthly' => 8
            ],
            'amount_thresholds' => [
                'low' => 100,
                'medium' => 1000,
                'high' => 5000
            ],
            'suspicious_patterns' => [
                'same_day_multiple' => true,
                'unusual_amount' => true,
                'frequency_change' => true
            ]
        ]
    ],
    
    'Ofis Giderleri' => [
        'keywords' => [
            // Kırtasiye
            'kırtasiye', 'kalem', 'kağıt', 'defter', 'dosya', 'klasör',
            'zımba', 'delgeç', 'ataş', 'post-it', 'not kağıdı', 'ajanda',
            'takvim', 'kartvizit', 'zarf', 'etiket', 'cd', 'dvd', 'flash bellek',
            
            // Yazıcı ve Sarf
            'yazıcı', 'printer', 'toner', 'kartuş', 'drum', 'fotokopi',
            'tarayıcı', 'scanner', 'faks', 'mürekkep', 'şerit', 'etiket',
            
            // Mobilya
            'masa', 'sandalye', 'koltuk', 'dolap', 'raf', 'kitaplık',
            'sehpa', 'etajer', 'perde', 'halı', 'mobilya', 'dekorasyon',
            
            // Elektronik
            'bilgisayar', 'laptop', 'monitör', 'klavye', 'mouse', 'ups',
            'projeksiyon', 'telefon', 'tablet', 'şarj aleti', 'kablo',
            'adaptör', 'powerbank', 'harddisk', 'ses sistemi',
            
            // Temizlik
            'temizlik', 'deterjan', 'çöp poşeti', 'peçete', 'tuvalet kağıdı',
            'havlu', 'sabun', 'dezenfektan', 'kolonya', 'sprey', 'mop',
            'süpürge', 'faraş', 'temizlik bezi',
            
            // Mutfak
            'çay', 'kahve', 'şeker', 'su', 'bardak', 'fincan', 'tabak',
            'kaşık', 'çatal', 'bıçak', 'kettle', 'kahve makinesi',
            'buzdolabı', 'mikrodalga', 'su sebili',
            
            // Güvenlik
            'kamera', 'alarm', 'güvenlik sistemi', 'kasa', 'kilit',
            'anahtar', 'kart okuyucu', 'sensör', 'duman dedektörü',
            
            // İklimlendirme
            'klima', 'vantilatör', 'fan', 'ısıtıcı', 'nem alma',
            'hava temizleme', 'filtre'
        ],
        'sub_categories' => [
            'Kırtasiye' => [
                'icon' => 'fa-pencil-alt',
                'keywords' => ['kırtasiye', 'kalem', 'kağıt', 'defter']
            ],
            'Elektronik' => [
                'icon' => 'fa-laptop',
                'keywords' => ['bilgisayar', 'yazıcı', 'telefon']
            ],
            'Mobilya' => [
                'icon' => 'fa-chair',
                'keywords' => ['masa', 'sandalye', 'dolap']
            ],
            'Temizlik' => [
                'icon' => 'fa-broom',
                'keywords' => ['temizlik', 'deterjan', 'peçete']
            ],
            'Mutfak' => [
                'icon' => 'fa-coffee',
                'keywords' => ['çay', 'kahve', 'su', 'bardak']
            ]
        ],
        'icon' => 'fa-building',
        'color' => 'primary',
        'analysis_rules' => [
            'high_frequency' => [
                'daily' => 2,
                'weekly' => 5,
                'monthly' => 15
            ],
            'amount_thresholds' => [
                'low' => 50,
                'medium' => 500,
                'high' => 2000
            ],
            'suspicious_patterns' => [
                'same_day_multiple' => true,
                'unusual_amount' => true,
                'frequency_change' => true
            ]
        ]
    ]
];
// Devam eden kategori tanımları
$category_groups += [
    'Personel Giderleri' => [
        'keywords' => [
            // Maaş ve Ödemeler
            'maaş', 'ücret', 'avans', 'ikramiye', 'prim', 'bonus',
            'mesai', 'fazla mesai', 'ek ödeme', 'huzur hakkı', 'harcırah',
            'yıllık izin', 'rapor', 'hastalık izni', 'doğum izni',
            
            // SGK ve Vergi
            'sgk', 'bağkur', 'emeklilik', 'işsizlik sigortası', 'gss',
            'gelir vergisi', 'damga vergisi', 'stopaj', 'muhtasar',
            'asgari ücret', 'net maaş', 'brüt maaş',
            
            // Yan Haklar
            'yemek', 'yemek kartı', 'sodexo', 'multinet', 'ticket',
            'yol', 'servis', 'ulaşım', 'benzin yardımı', 'yakıt desteği',
            'sağlık sigortası', 'özel sigorta', 'hayat sigortası',
            
            // Eğitim ve Gelişim
            'eğitim', 'seminer', 'kurs', 'sertifika', 'konferans',
            'workshop', 'online eğitim', 'koçluk', 'mentorluk',
            'yabancı dil', 'mesleki gelişim',
            
            // Tazminatlar
            'kıdem tazminatı', 'ihbar tazminatı', 'iş kazası',
            'işten çıkış', 'fesih', 'istifa', 'işe iade',
            
            // Pozisyonlar
            'müdür', 'şef', 'uzman', 'asistan', 'sekreter', 'stajyer',
            'işçi', 'personel', 'çalışan', 'eleman', 'kadrolu',
            'sözleşmeli', 'part time', 'full time'
        ],
        'sub_categories' => [
            'Maaşlar' => [
                'icon' => 'fa-money-bill-wave',
                'keywords' => ['maaş', 'ücret', 'avans', 'ikramiye']
            ],
            'Sigorta' => [
                'icon' => 'fa-shield-alt',
                'keywords' => ['sgk', 'bağkur', 'sigorta']
            ],
            'Yan Haklar' => [
                'icon' => 'fa-plus-circle',
                'keywords' => ['yemek', 'yol', 'servis', 'sağlık']
            ],
            'Tazminatlar' => [
                'icon' => 'fa-hand-holding-usd',
                'keywords' => ['tazminat', 'ihbar', 'kıdem']
            ]
        ],
        'icon' => 'fa-users',
        'color' => 'success',
        'analysis_rules' => [
            'high_frequency' => [
                'daily' => 0,
                'weekly' => 0,
                'monthly' => 1
            ],
            'amount_thresholds' => [
                'low' => 5000,
                'medium' => 20000,
                'high' => 50000
            ]
        ]
    ],

    'Pazarlama Giderleri' => [
        'keywords' => [
            // Online Reklamlar
            'google ads', 'facebook reklam', 'instagram reklam',
            'linkedin reklam', 'youtube reklam', 'twitter reklam',
            'tiktok reklam', 'dijital reklam', 'sosyal medya',
            'seo', 'sem', 'adwords', 'remarketing',
            
            // Geleneksel Reklamlar
            'tv reklamı', 'radyo reklamı', 'gazete ilanı',
            'dergi ilanı', 'billboard', 'raket', 'megalight',
            'outdoor', 'el ilanı', 'broşür', 'katalog',
            
            // Web ve Dijital
            'website', 'web sitesi', 'domain', 'hosting', 'ssl',
            'mobil uygulama', 'app store', 'play store',
            'email marketing', 'newsletter', 'crm', 'analitik',
            
            // Satış Pazarlama
            'stand', 'fuar', 'organizasyon', 'lansman',
            'promosyon', 'numune', 'hediye', 'sponsor',
            'mailing', 'sms', 'toplu mesaj',
            
            // Ajans Hizmetleri
            'reklam ajansı', 'medya ajansı', 'pr ajansı',
            'sosyal medya ajansı', 'grafik tasarım', 'video',
            'fotoğraf çekimi', 'prodüksiyon', 'baskı',
            
            // Platformlar
            'sahibinden', 'hürriyet emlak', 'emlakjet',
            'arabam', 'letgo', 'n11', 'trendyol', 'hepsiburada',
            'amazon', 'facebook', 'instagram', 'linkedin'
        ],
        'sub_categories' => [
            'Online Reklam' => [
                'icon' => 'fa-globe',
                'keywords' => ['google', 'facebook', 'instagram', 'dijital']
            ],
            'Geleneksel Reklam' => [
                'icon' => 'fa-tv',
                'keywords' => ['tv', 'radyo', 'gazete', 'dergi']
            ],
            'Web Hizmetleri' => [
                'icon' => 'fa-laptop-code',
                'keywords' => ['website', 'domain', 'hosting']
            ],
            'Ajans' => [
                'icon' => 'fa-paint-brush',
                'keywords' => ['ajans', 'tasarım', 'prodüksiyon']
            ]
        ],
        'icon' => 'fa-ad',
        'color' => 'info',
        'analysis_rules' => [
            'high_frequency' => [
                'daily' => 2,
                'weekly' => 5,
                'monthly' => 15
            ],
            'amount_thresholds' => [
                'low' => 1000,
                'medium' => 5000,
                'high' => 20000
            ]
        ]
    ]
];

// Şimdi gider/gelir eklerken otomatik kategorizasyon için fonksiyon
function autoCategorizeTrx($description, $category_groups) {
    $description = mb_strtolower($description, 'UTF-8');
    $matches = [];
    
    foreach($category_groups as $category_name => $category_info) {
        $score = 0;
        $matched_keywords = [];
        
        foreach($category_info['keywords'] as $keyword) {
            if(mb_stripos($description, mb_strtolower($keyword, 'UTF-8')) !== false) {
                $score++;
                $matched_keywords[] = $keyword;
            }
        }
        
        if($score > 0) {
            $matches[$category_name] = [
                'score' => $score,
                'matched_keywords' => $matched_keywords,
                'category_info' => $category_info
            ];
        }
    }
    
    // En yüksek skora sahip kategoriyi bul
    if(!empty($matches)) {
        arsort($matches);
        return [
            'category' => array_key_first($matches),
            'match_info' => $matches[array_key_first($matches)],
            'all_matches' => $matches
        ];
    }
    
    return null;
}

// Gider/Gelir eklerken kullanım örneği:
function addTransaction($description, $amount, $type) {
    global $category_groups, $pia, $admin;
    
    // Otomatik kategorizasyon
    $categorization = autoCategorizeTrx($description, $category_groups);
    
    if($categorization) {
        // Kategori ID'sini bul veya oluştur
        $category_id = ensureCategory($categorization['category'], $type);
        
        // İşlemi kaydet
        $query = "INSERT INTO finances (
            u_id, 
            category_id, 
            description, 
            price, 
            event_type,
            ai_categorized,
            ai_confidence,
            ai_matched_keywords
        ) VALUES (?, ?, ?, ?, ?, 1, ?, ?)";
        
        $confidence = ($categorization['match_info']['score'] / 
                      count($categorization['match_info']['matched_keywords'])) * 100;
        
        return $pia->query($query, [
            $admin->id,
            $category_id,
            $description,
            $amount,
            $type,
            $confidence,
            json_encode($categorization['match_info']['matched_keywords'])
        ]);
    }
    
    // Kategorizasyon yapılamadıysa
    return false;
}

// Kategori varsa getir, yoksa oluştur
function ensureCategory($category_name, $type) {
    global $pia, $admin;
    
    $existing = $pia->get_row("
        SELECT id FROM categories 
        WHERE title = ? AND type = ? AND u_id = ?
    ", [$category_name, $type, $admin->id]);
    
    if($existing) {
        return $existing->id;
    }
    
    // Yeni kategori oluştur
    $pia->query("
        INSERT INTO categories (title, type, u_id) 
        VALUES (?, ?, ?)
    ", [$category_name, $type, $admin->id]);
    
    return $pia->lastInsertId();
}
$category_groups += [
    'Sabit Giderler' => [
        'keywords' => [
            // Kira ve Emlak
            'kira', 'kiralama', 'stopaj', 'depozito', 'emlak vergisi',
            'aidat', 'site aidatı', 'apartman aidatı', 'bina gideri',
            'gayrimenkul', 'tapu', 'noter', 'komisyon', 'emlakçı',
            
            // Faturalar
            'elektrik faturası', 'elektrik', 'edaş', 'bedaş', 'ayedaş',
            'su faturası', 'su', 'iski', 'aski', 'izsu',
            'doğalgaz', 'doğal gaz', 'igdaş', 'izgaz', 'esgaz',
            'telefon', 'internet', 'fiber', 'adsl', 'vdsl',
            'superonline', 'turknet', 'ttnet', 'vodafone', 'turkcell',
            'türk telekom', 'digiturk', 'dsmart', 'tivibu', 'netflix',
            
            // Belediye ve Vergiler
            'çtv', 'çevre temizlik vergisi', 'ilan reklam vergisi',
            'belediye vergisi', 'çöp vergisi', 'aydınlatma',
            'tabela vergisi', 'ruhsat harcı', 'işgaliye',
            
            // Sigorta
            'dask', 'deprem sigortası', 'yangın sigortası',
            'işyeri sigortası', 'konut sigortası', 'risk sigortası',
            'sorumluluk sigortası', 'cam sigortası',
            
            // Güvenlik
            'güvenlik', 'kamera sistemi', 'alarm sistemi',
            'güvenlik görevlisi', 'bekçi', 'koruma', 'devriye',
            'x-ray cihazı', 'metal dedektör',
            
            // Asansör ve Teknik
            'asansör bakım', 'jeneratör bakım', 'ups bakım',
            'klima bakım', 'kazan bakım', 'hidrofor', 'su deposu',
            'yangın sistemi', 'paratoner', 'havalandırma'
        ],
        'sub_categories' => [
            'Kira' => [
                'icon' => 'fa-building',
                'keywords' => ['kira', 'stopaj', 'depozito']
            ],
            'Faturalar' => [
                'icon' => 'fa-file-invoice',
                'keywords' => ['elektrik', 'su', 'doğalgaz', 'telefon']
            ],
            'Vergiler' => [
                'icon' => 'fa-receipt',
                'keywords' => ['vergi', 'çtv', 'belediye']
            ],
            'Bakım' => [
                'icon' => 'fa-tools',
                'keywords' => ['asansör', 'jeneratör', 'klima']
            ]
        ],
        'icon' => 'fa-home',
        'color' => 'warning',
        'analysis_rules' => [
            'high_frequency' => [
                'daily' => 0,
                'weekly' => 0,
                'monthly' => 1
            ],
            'amount_thresholds' => [
                'low' => 1000,
                'medium' => 5000,
                'high' => 20000
            ],
            'payment_patterns' => [
                'regular_monthly' => true,
                'due_date_tracking' => true,
                'late_payment_alert' => true
            ]
        ]
    ],

    'Finansal Giderler' => [
        'keywords' => [
            // Banka İşlemleri
            'kredi', 'kredi taksiti', 'kredi faizi', 'kredi kartı',
            'banka', 'havale', 'eft', 'swift', 'pos', 'komisyon',
            'hesap işletim', 'banka masrafı', 'dosya masrafı',
            'ekspertiz', 'ipotek', 'teminat mektubu',
            
            // Vergi ve Harçlar
            'kdv', 'stopaj', 'damga vergisi', 'bsmv', 'kkdf',
            'gelir vergisi', 'kurumlar vergisi', 'geçici vergi',
            'muhtasar', 'tevkifat', 'katma değer', 'vergi cezası',
            
            // Muhasebe ve Mali Müşavirlik
            'muhasebe', 'mali müşavir', 'smmm', 'ymm', 'denetim',
            'beyanname', 'defter tasdik', 'bağımsız denetim',
            'vergi danışmanlığı', 'bordro', 'e-fatura', 'e-defter',
            
            // Sigorta ve Teminatlar
            'sigorta primi', 'kasko', 'trafik sigortası',
            'işyeri sigortası', 'nakliyat sigortası', 'teminat',
            'kefalet', 'garanti', 'risk primi',
            
            // Finansal Yazılımlar
            'logo', 'mikro', 'eta', 'netsis', 'sap', 'oracle',
            'microsoft dynamics', 'parasol', 'luca', 'nebim',
            'e-fatura sistemi', 'e-defter sistemi'
        ],
        'sub_categories' => [
            'Banka' => [
                'icon' => 'fa-university',
                'keywords' => ['kredi', 'banka', 'havale', 'eft']
            ],
            'Vergi' => [
                'icon' => 'fa-percent',
                'keywords' => ['kdv', 'stopaj', 'vergi']
            ],
            'Muhasebe' => [
                'icon' => 'fa-calculator',
                'keywords' => ['muhasebe', 'mali müşavir', 'denetim']
            ],
            'Yazılım' => [
                'icon' => 'fa-laptop-code',
                'keywords' => ['logo', 'mikro', 'netsis', 'parasol']
            ]
        ],
        'icon' => 'fa-money-bill-wave',
        'color' => 'danger',
        'analysis_rules' => [
            'high_frequency' => [
                'daily' => 1,
                'weekly' => 3,
                'monthly' => 10
            ],
            'amount_thresholds' => [
                'low' => 5000,
                'medium' => 20000,
                'high' => 100000
            ],
            'alert_conditions' => [
                'unusual_amount' => true,
                'duplicate_payment' => true,
                'frequency_change' => true
            ]
        ]
    ]
];
$category_groups += [
    'Teknoloji Giderleri' => [
        'keywords' => [
            // Donanım
            'bilgisayar', 'laptop', 'masaüstü', 'server', 'sunucu',
            'nas', 'storage', 'harddisk', 'ssd', 'ram', 'işlemci',
            'anakart', 'ekran kartı', 'monitör', 'ups', 'güç kaynağı',
            'switch', 'router', 'modem', 'access point', 'firewall',
            'printer', 'yazıcı', 'tarayıcı', 'pos cihazı', 'yedek parça',
            
            // Markalar
            'dell', 'hp', 'lenovo', 'asus', 'acer', 'msi', 'apple',
            'samsung', 'lg', 'intel', 'amd', 'nvidia', 'cisco', 'huawei',
            'xerox', 'canon', 'epson', 'brother', 'synology', 'qnap',
            
            // Yazılım
            'microsoft', 'windows', 'office', 'adobe', 'photoshop',
            'autocad', 'solidworks', 'sap', 'oracle', 'sql server',
            'antivirus', 'kaspersky', 'eset', 'norton', 'mcafee',
            'zoom', 'teams', 'slack', 'jira', 'trello', 'asana',
            'crm', 'erp', 'muhasebe yazılımı', 'bulut depolama',
            
            // Lisanslar
            'lisans', 'yazılım lisansı', 'kullanıcı lisansı',
            'yıllık lisans', 'aylık abonelik', 'perpetual lisans',
            'volume licensing', 'open license', 'enterprise',
            
            // Altyapı
            'network', 'ağ', 'kablolama', 'fiber', 'cat6', 'cat7',
            'patch panel', 'rack kabin', 'sistem odası', 'soğutma',
            'iklimlendirme', 'kesintisiz güç', 'jeneratör',
            
            // Güvenlik
            'firewall lisans', 'ssl sertifika', 'vpn', 'antivirüs',
            'güvenlik duvarı', 'veri güvenliği', 'yedekleme',
            'felaket kurtarma', 'penetrasyon testi', 'audit',
            
            // Hizmetler
            'hosting', 'domain', 'ssl', 'cloud', 'aws', 'azure',
            'google cloud', 'cdn', 'email hosting', 'backup',
            'teknik destek', 'danışmanlık', 'kurulum', 'konfigurasyon',
            
            // Telekomünikasyon
            'gsm', 'mobil hat', 'sabit hat', 'fiber internet',
            'metro ethernet', 'noktadan noktaya', 'vpn bağlantı',
            'data hattı', 'telefon santrali', 'ip telefon'
        ],
        'sub_categories' => [
            'Donanım' => [
                'icon' => 'fa-desktop',
                'keywords' => ['bilgisayar', 'server', 'yazıcı', 'donanım'],
                'analysis_rules' => [
                    'depreciation_period' => 36, // ay
                    'maintenance_cycle' => 6,    // ay
                    'replacement_warning' => 24  // ay
                ]
            ],
            'Yazılım' => [
                'icon' => 'fa-code',
                'keywords' => ['lisans', 'windows', 'office', 'yazılım'],
                'analysis_rules' => [
                    'renewal_tracking' => true,
                    'version_tracking' => true,
                    'license_audit' => true
                ]
            ],
            'Altyapı' => [
                'icon' => 'fa-network-wired',
                'keywords' => ['network', 'kablolama', 'fiber', 'altyapı'],
                'analysis_rules' => [
                    'capacity_monitoring' => true,
                    'performance_tracking' => true,
                    'upgrade_planning' => true
                ]
            ],
            'Güvenlik' => [
                'icon' => 'fa-shield-alt',
                'keywords' => ['firewall', 'antivirüs', 'ssl', 'güvenlik'],
                'analysis_rules' => [
                    'security_audit' => true,
                    'vulnerability_scan' => true,
                    'compliance_check' => true
                ]
            ],
            'Telekomünikasyon' => [
                'icon' => 'fa-phone-alt',
                'keywords' => ['gsm', 'internet', 'telefon', 'hat'],
                'analysis_rules' => [
                    'usage_monitoring' => true,
                    'cost_optimization' => true,
                    'service_quality' => true
                ]
            ]
        ],
        'icon' => 'fa-microchip',
        'color' => 'info',
        'analysis_rules' => [
            'high_frequency' => [
                'daily' => 1,
                'weekly' => 3,
                'monthly' => 8
            ],
            'amount_thresholds' => [
                'low' => 1000,
                'medium' => 10000,
                'high' => 50000
            ],
            'budget_rules' => [
                'annual_budget_percentage' => 15,
                'emergency_fund_percentage' => 10,
                'upgrade_cycle_years' => 3
            ],
            'alert_conditions' => [
                'unusual_spending' => true,
                'maintenance_due' => true,
                'license_expiry' => true,
                'security_updates' => true,
                'performance_issues' => true
            ],
            'reporting_rules' => [
                'asset_tracking' => true,
                'depreciation_tracking' => true,
                'roi_calculation' => true,
                'tco_analysis' => true
            ],
            'optimization_rules' => [
                'bulk_purchase_threshold' => 5000,
                'vendor_consolidation' => true,
                'license_optimization' => true,
                'cloud_vs_onprem' => true
            ]
        ],
        'vendor_categories' => [
            'Hardware' => ['dell', 'hp', 'lenovo', 'cisco'],
            'Software' => ['microsoft', 'oracle', 'adobe'],
            'Security' => ['kaspersky', 'cisco', 'fortinet'],
            'Cloud' => ['aws', 'azure', 'google'],
            'Telecom' => ['turkcell', 'vodafone', 'türk telekom']
        ],
        'expense_patterns' => [
            'regular' => [
                'monthly_subscriptions' => ['hosting', 'saas', 'lisans'],
                'quarterly_maintenance' => ['bakım', 'güncelleme'],
                'annual_licenses' => ['yıllık lisans', 'yazılım']
            ],
            'irregular' => [
                'hardware_upgrades' => ['sunucu', 'bilgisayar'],
                'emergency_repairs' => ['arıza', 'tamir'],
                'security_incidents' => ['güvenlik', 'saldırı']
            ]
        ]
    ]
];

// Akıllı Analiz Sınıfı
class SmartExpenseAnalyzer {
    private $db;
    private $categories;
    private $analysis_data;
    
    public function __construct($db, $categories) {
        $this->db = $db;
        $this->categories = $categories;
        $this->analysis_data = [
            'patterns' => [],
            'anomalies' => [],
            'predictions' => [],
            'optimizations' => []
        ];
    }
    
    // Harcama Analizi
    public function analyzeExpense($expense_data) {
        $analysis = [
            'category_match' => $this->findCategoryMatch($expense_data['description']),
            'amount_analysis' => $this->analyzeAmount($expense_data['amount']),
            'frequency_analysis' => $this->analyzeFrequency($expense_data),
            'pattern_detection' => $this->detectPatterns($expense_data),
            'optimization_suggestions' => $this->generateOptimizations($expense_data)
        ];
        
        return $this->enrichAnalysisWithAI($analysis);
    }
    
    // Kategori Eşleştirme
    private function findCategoryMatch($description) {
        $matches = [];
        $description = mb_strtolower($description, 'UTF-8');
        
        foreach($this->categories as $category_name => $category_info) {
            $score = 0;
            $matched_keywords = [];
            
            foreach($category_info['keywords'] as $keyword) {
                if(mb_stripos($description, mb_strtolower($keyword, 'UTF-8')) !== false) {
                    $score++;
                    $matched_keywords[] = $keyword;
                }
            }
            
            if($score > 0) {
                $matches[$category_name] = [
                    'score' => $score,
                    'confidence' => ($score / count($category_info['keywords'])) * 100,
                    'matched_keywords' => $matched_keywords,
                    'category_info' => $category_info
                ];
            }
        }
        
        return $matches;
    }
    
$category_groups += [
    'Eğitim ve Gelişim Giderleri' => [
        'keywords' => [
            // Kurumsal Eğitimler
            'eğitim', 'seminer', 'konferans', 'workshop', 'sertifika programı',
            'online eğitim', 'e-learning', 'uzaktan eğitim', 'webinar',
            'in-house training', 'kurum içi eğitim', 'oryantasyon',
            
            // Sertifikasyonlar
            'iso', 'iso 9001', 'iso 27001', 'iso 22000', 'haccp',
            'pmp', 'itil', 'cisa', 'cissp', 'ceh', 'ccna', 'mcse',
            'aws certification', 'google certification', 'microsoft sertifika',
            
            // Dil Eğitimi
            'ingilizce', 'almanca', 'fransızca', 'ispanyolca', 'rusça',
            'dil kursu', 'yabancı dil', 'toefl', 'ielts', 'yds', 'yökdil',
            'berlitz', 'wall street', 'american life', 'british council',
            
            // Mesleki Gelişim
            'mesleki yeterlilik', 'ustalık belgesi', 'kalfalık',
            'iş güvenliği', 'ilk yardım', 'hijyen eğitimi',
            'forklift ehliyeti', 'operatör belgesi', 'src belgesi',
            
            // Yazılım Eğitimleri
            'excel', 'word', 'powerpoint', 'sql', 'python', 'java',
            'web tasarım', 'grafik tasarım', 'autocad', '3d max',
            'solidworks', 'photoshop', 'illustrator', 'indesign',
            
            // Kişisel Gelişim
            'liderlik', 'yöneticilik', 'koçluk', 'mentorluk',
            'iletişim', 'satış', 'pazarlama', 'müzakere', 'sunum',
            'zaman yönetimi', 'stres yönetimi', 'çatışma yönetimi',
            
            // Eğitim Materyalleri
            'kitap', 'dergi', 'e-kitap', 'video', 'online kurs',
            'udemy', 'coursera', 'linkedin learning', 'pluralsight',
            'eğitim platformu', 'dijital içerik', 'eğitim yazılımı',
            
            // Eğitim Kurumları
            'üniversite', 'meb', 'halk eğitim', 'özel eğitim',
            'akademi', 'enstitü', 'araştırma merkezi', 'laboratuvar',
            'bilim merkezi', 'teknoloji merkezi', 'inovasyon merkezi'
        ],
        'sub_categories' => [
            'Kurumsal Eğitim' => [
                'icon' => 'fa-chalkboard-teacher',
                'keywords' => ['eğitim', 'seminer', 'konferans'],
                'analysis_rules' => [
                    'roi_tracking' => true,
                    'effectiveness_measure' => true,
                    'attendance_tracking' => true
                ]
            ],
            'Sertifikasyon' => [
                'icon' => 'fa-certificate',
                'keywords' => ['sertifika', 'iso', 'belge'],
                'analysis_rules' => [
                    'renewal_tracking' => true,
                    'compliance_check' => true,
                    'validity_period' => true
                ]
            ],
            'Dil Eğitimi' => [
                'icon' => 'fa-language',
                'keywords' => ['dil', 'ingilizce', 'yabancı'],
                'analysis_rules' => [
                    'level_tracking' => true,
                    'progress_monitoring' => true,
                    'exam_preparation' => true
                ]
            ],
            'Mesleki Gelişim' => [
                'icon' => 'fa-user-graduate',
                'keywords' => ['mesleki', 'yeterlilik', 'ustalık'],
                'analysis_rules' => [
                    'skill_gap_analysis' => true,
                    'career_planning' => true,
                    'requirement_tracking' => true
                ]
            ]
        ],
        'icon' => 'fa-graduation-cap',
        'color' => 'success',
        'analysis_rules' => [
            'high_frequency' => [
                'daily' => 0,
                'weekly' => 1,
                'monthly' => 4
            ],
            'amount_thresholds' => [
                'low' => 500,
                'medium' => 5000,
                'high' => 20000
            ],
            'budget_rules' => [
                'annual_budget_percentage' => 8,
                'per_employee_budget' => 2000,
                'minimum_training_hours' => 40
            ],
            'tracking_rules' => [
                'completion_rate' => true,
                'certification_success' => true,
                'knowledge_retention' => true,
                'skill_improvement' => true
            ]
        ],
        'expense_patterns' => [
            'regular' => [
                'annual_certifications' => ['iso', 'sertifika'],
                'monthly_subscriptions' => ['online', 'platform'],
                'quarterly_assessments' => ['değerlendirme', 'sınav']
            ],
            'project_based' => [
                'new_hire_training' => ['oryantasyon', 'işe alım'],
                'compliance_training' => ['zorunlu', 'yasal'],
                'skill_upgrade' => ['yükseltme', 'geliştirme']
            ]
        ],
        'roi_metrics' => [
            'productivity_increase' => [
                'measurement_period' => 6, // ay
                'minimum_improvement' => 10, // yüzde
                'tracking_methods' => ['performance_review', 'skill_assessment']
            ],
            'certification_value' => [
                'market_value_increase' => true,
                'competitive_advantage' => true,
                'compliance_requirement' => true
            ],
            'knowledge_retention' => [
                'assessment_frequency' => 3, // ay
                'minimum_score' => 75, // yüzde
                'refresher_threshold' => 60 // yüzde
            ]
        ]
    ]
];
$category_groups += [
    'Seyahat ve Konaklama Giderleri' => [
        'keywords' => [
            // Ulaşım
            'uçak bileti', 'thy', 'pegasus', 'anadolujet', 'sunexpress',
            'tren bileti', 'tcdd', 'yht', 'marmaray', 'başkentray',
            'otobüs bileti', 'metro turizm', 'pamukkale', 'kamil koç',
            'ulusoy', 'nilüfer', 'varan', 'rent a car', 'araç kiralama',
            'taksi', 'uber', 'bitaksi', 'transfer', 'shuttle', 'servis',
            
            // Havayolu Şirketleri
            'turkish airlines', 'lufthansa', 'emirates', 'qatar airways',
            'klm', 'air france', 'british airways', 'delta', 'united',
            'american airlines', 'etihad', 'singapore airlines',
            
            // Konaklama
            'otel', 'hotel', 'hilton', 'sheraton', 'marriott', 'radisson',
            'holiday inn', 'novotel', 'mercure', 'ibis', 'ramada', 'crown',
            'airbnb', 'booking.com', 'hotels.com', 'trivago', 'expedia',
            'tatil', 'resort', 'pansiyon', 'apart', 'suite', 'rezarvasyon',
            
            // Seyahat Acenteleri
            'ets tur', 'jolly tur', 'setur', 'pronto tour', 'anı tur',
            'tatil budur', 'tatil sepeti', 'mng turizm', 'atlas global',
            'karavan', 'gezi', 'tur', 'organizasyon', 'rehber',
            
            // Yemek ve İkram
            'restoran', 'lokanta', 'cafe', 'kahvaltı', 'öğle yemeği',
            'akşam yemeği', 'business lunch', 'catering', 'minibar',
            'room service', 'yemek hizmeti', 'ikram', 'ziyafet',
            
            // Vize ve Pasaport
            'vize', 'pasaport', 'harç', 'konsolosluk', 'büyükelçilik',
            'schengen', 'tourist visa', 'business visa', 'work permit',
            'seyahat sigortası', 'vfs global', 'ikamet', 'oturma izni',
            
            // Harcırah ve Ödenekler
            'harcırah', 'gündelik', 'konaklama bedeli', 'yol masrafı',
            'seyahat ödeneği', 'yurtdışı harcırah', 'yevmiye',
            'temsil ağırlama', 'misafir ağırlama', 'business expense',
            
            // Toplantı ve Organizasyon
            'toplantı salonu', 'konferans', 'seminer', 'workshop',
            'fuar katılım', 'stand', 'b2b', 'networking', 'etkinlik',
            'kongre merkezi', 'zirve', 'summit', 'meeting room'
        ],
        'sub_categories' => [
            'Ulaşım' => [
                'icon' => 'fa-plane',
                'keywords' => ['uçak', 'tren', 'otobüs', 'taksi'],
                'analysis_rules' => [
                    'preferred_vendors' => true,
                    'advance_booking' => true,
                    'class_policy' => [
                        'domestic' => 'economy',
                        'international' => ['economy', 'business'],
                        'exceptions' => ['CEO', 'CFO', 'CTO']
                    ]
                ]
            ],
            'Konaklama' => [
                'icon' => 'fa-hotel',
                'keywords' => ['otel', 'hotel', 'pansiyon', 'airbnb'],
                'analysis_rules' => [
                    'star_rating' => [3, 4, 5],
                    'max_nightly_rate' => [
                        'domestic' => 1000,
                        'international' => 2000,
                        'premium' => 3000
                    ],
                    'duration_limits' => [
                        'minimum' => 1,
                        'maximum' => 30,
                        'approval_required' => 7
                    ]
                ]
            ],
            'Yemek' => [
                'icon' => 'fa-utensils',
                'keywords' => ['restoran', 'yemek', 'cafe', 'catering'],
                'analysis_rules' => [
                    'meal_allowance' => [
                        'breakfast' => 100,
                        'lunch' => 150,
                        'dinner' => 200
                    ],
                    'group_limits' => [
                        'per_person' => 250,
                        'max_group' => 10
                    ]
                ]
            ],
            'Vize/Pasaport' => [
                'icon' => 'fa-passport',
                'keywords' => ['vize', 'pasaport', 'konsolosluk'],
                'analysis_rules' => [
                    'expiry_tracking' => true,
                    'processing_time' => true,
                    'cost_tracking' => true
                ]
            ]
        ],
        'icon' => 'fa-suitcase',
        'color' => 'primary',
        'analysis_rules' => [
            'high_frequency' => [
                'daily' => 2,
                'weekly' => 5,
                'monthly' => 15
            ],
            'amount_thresholds' => [
                'low' => 1000,
                'medium' => 5000,
                'high' => 20000
            ],
            'approval_rules' => [
                'automatic' => [
                    'threshold' => 1000,
                    'conditions' => ['policy_compliant', 'budget_available']
                ],
                'manager' => [
                    'threshold' => 5000,
                    'response_time' => 24 // saat
                ],
                'director' => [
                    'threshold' => 20000,
                    'response_time' => 48 // saat
                ]
            ],
            'policy_rules' => [
                'advance_booking' => [
                    'domestic_flight' => 7, // gün
                    'international_flight' => 14, // gün
                    'hotel' => 3 // gün
                ],
                'class_restrictions' => [
                    'flight' => [
                        'domestic' => 'economy',
                        'international_under_6h' => 'economy',
                        'international_over_6h' => 'business'
                    ],
                    'train' => ['economy', 'business'],
                    'hotel' => ['3_star', '4_star', '5_star']
                ]
            ]
        ],
        'expense_patterns' => [
            'regular' => [
                'monthly_trips' => ['satış', 'müşteri ziyareti'],
                'quarterly_meetings' => ['toplantı', 'konferans'],
                'annual_events' => ['fuar', 'kongre']
            ],
            'seasonal' => [
                'peak_seasons' => ['yaz', 'kış'],
                'off_peak' => ['bahar', 'sonbahar']
            ]
        ],
        'optimization_rules' => [
            'bulk_booking' => [
                'minimum_group' => 5,
                'discount_expected' => 15 // yüzde
            ],
            'preferred_vendors' => [
                'airlines' => ['thy', 'pegasus'],
                'hotels' => ['hilton', 'marriott'],
                'agencies' => ['ets', 'setur']
            ],
            'loyalty_programs' => [
                'miles_tracking' => true,
                'hotel_points' => true,
                'corporate_benefits' => true
            ]
        ]
    ]
];
$category_groups += [
    'Emlak ve Konut Giderleri' => [
        'keywords' => [
            // Emlak İşlemleri
            'kira', 'depozito', 'emlak komisyonu', 'tapu harcı',
            'emlakçı', 'gayrimenkul', 'noter', 'ekspertiz',
            'değerleme', 'ipotek', 'konut kredisi', 'emlak vergisi',
            'çevre temizlik vergisi', 'kentsel dönüşüm', 'iskan',
            'kat irtifakı', 'kat mülkiyeti', 'arsa payı',
            
            // Apartman Giderleri
            'aidat', 'apartman aidatı', 'site aidatı', 'kapıcı',
            'asansör', 'bina sigortası', 'ortak alan', 'havuz',
            'otopark', 'güvenlik', 'bahçe bakım', 'jeneratör',
            
            // Ev Tadilatı
            'boya', 'badana', 'tadilat', 'tamirat', 'mutfak dolabı',
            'parke', 'laminat', 'fayans', 'seramik', 'pencere',
            'kapı', 'mobilya', 'beyaz eşya', 'perde', 'halı'
        ],
        'sub_categories' => [
            'Emlak İşlemleri' => ['icon' => 'fa-home'],
            'Apartman Giderleri' => ['icon' => 'fa-building'],
            'Tadilat' => ['icon' => 'fa-tools']
        ]
    ],

    'Ev Giderleri' => [
        'keywords' => [
            // Faturalar
            'elektrik faturası', 'su faturası', 'doğalgaz',
            'telefon', 'internet', 'tv', 'digiturk', 'dsmart',
            'netflix', 'spotify', 'apple tv', 'amazon prime',
            
            // Mutfak
            'market', 'süpermarket', 'manav', 'kasap', 'şarküteri',
            'ekmek', 'süt', 'yoğurt', 'et', 'tavuk', 'balık',
            'meyve', 'sebze', 'bakliyat', 'içecek', 'atıştırmalık',
            
            // Temizlik
            'deterjan', 'şampuan', 'sabun', 'çamaşır suyu',
            'yumuşatıcı', 'bulaşık deterjanı', 'temizlik malzemesi',
            'çöp poşeti', 'havlu kağıt', 'tuvalet kağıdı'
        ],
        'sub_categories' => [
            'Faturalar' => ['icon' => 'fa-file-invoice'],
            'Mutfak' => ['icon' => 'fa-utensils'],
            'Temizlik' => ['icon' => 'fa-broom']
        ]
    ],

    'Resmi Ödemeler' => [
        'keywords' => [
            // Trafik
            'trafik cezası', 'park cezası', 'hız cezası',
            'ogs', 'hgs', 'mtv', 'araç muayene', 'ehliyet harç',
            'plaka', 'ruhsat', 'sigorta', 'kasko',
            
            // Vergiler
            'gelir vergisi', 'kdv', 'damga vergisi', 'emlak vergisi',
            'çtv', 'motorlu taşıtlar vergisi', 'belediye vergisi',
            
            // Harçlar
            'pasaport harcı', 'kimlik harcı', 'noter harcı',
            'tapu harcı', 'mahkeme harcı', 'apostil'
        ],
        'sub_categories' => [
            'Trafik' => ['icon' => 'fa-car'],
            'Vergiler' => ['icon' => 'fa-percent'],
            'Harçlar' => ['icon' => 'fa-file-alt']
        ]
    ],

    'Kişisel Harcamalar' => [
        'keywords' => [
            // Giyim
            'kıyafet', 'ayakkabı', 'çanta', 'ceket', 'pantolon',
            'gömlek', 'tişört', 'elbise', 'takım elbise', 'mont',
            'kazak', 'iç giyim', 'çorap', 'spor giyim', 'aksesuar',
            
            // Kişisel Bakım
            'kuaför', 'berber', 'spa', 'masaj', 'cilt bakımı',
            'manikür', 'pedikür', 'kozmetik', 'parfüm', 'makyaj',
            'diş bakımı', 'lens', 'gözlük', 'güneş gözlüğü',
            
            // Sağlık
            'hastane', 'muayene', 'ilaç', 'eczane', 'vitamin',
            'tahlil', 'röntgen', 'fizik tedavi', 'diş tedavi',
            'gözlük camı', 'lens solüsyonu', 'sağlık sigortası',
            
            // Hobi ve Eğlence
            'sinema', 'tiyatro', 'konser', 'spor salonu',
            'yüzme', 'tenis', 'golf', 'kitap', 'dergi',
            'müzik', 'oyun', 'hobi malzemesi', 'kurs ücreti'
        ],
        'sub_categories' => [
            'Giyim' => ['icon' => 'fa-tshirt'],
            'Kişisel Bakım' => ['icon' => 'fa-cut'],
            'Sağlık' => ['icon' => 'fa-heartbeat'],
            'Hobi' => ['icon' => 'fa-gamepad']
        ],
        'analysis_rules' => [
            'budget_limits' => [
                'monthly' => [
                    'giyim' => 2000,
                    'kişisel_bakım' => 1000,
                    'hobi' => 1500
                ],
                'annual' => [
                    'sağlık' => 10000,
                    'gözlük_lens' => 2000
                ]
            ],
            'seasonal_patterns' => [
                'winter' => ['mont', 'kazak', 'bot'],
                'summer' => ['tişört', 'şort', 'sandalet']
            ]
        ]
    ]
];

// Otomatik Kategorizasyon İyileştirmesi
class SmartCategorizer {
    private $categories;
    private $learning_data;
    
    public function __construct($categories) {
        $this->categories = $categories;
        $this->learning_data = [
            'patterns' => [],
            'corrections' => [],
            'user_preferences' => []
        ];
    }
    
    // Akıllı Eşleştirme
    public function categorize($description, $amount) {
        $description = mb_strtolower($description, 'UTF-8');
        $matches = [];
        
        foreach($this->categories as $category_name => $category_info) {
            $score = 0;
            $matched_keywords = [];
            
            // Anahtar kelime kontrolü
            foreach($category_info['keywords'] as $keyword) {
                if(mb_stripos($description, mb_strtolower($keyword, 'UTF-8')) !== false) {
                    $score += 2;
                    $matched_keywords[] = $keyword;
                }
            }
            
            // Alt kategori kontrolü
            foreach($category_info['sub_categories'] as $sub_cat => $sub_info) {
                foreach($sub_info['keywords'] ?? [] as $keyword) {
                    if(mb_stripos($description, mb_strtolower($keyword, 'UTF-8')) !== false) {
                        $score += 1;
                        $matched_keywords[] = $keyword;
                    }
                }
            }
            
            if($score > 0) {
                $matches[$category_name] = [
                    'score' => $score,
                    'confidence' => ($score * 100) / (count($category_info['keywords']) + 1),
                    'matched_keywords' => array_unique($matched_keywords),
                    'category_info' => $category_info
                ];
            }
        }
        
        // En iyi eşleşmeyi bul
        if(!empty($matches)) {
            arsort($matches);
            return [
                'category' => array_key_first($matches),
                'match_info' => $matches[array_key_first($matches)],
                'all_matches' => $matches
            ];
        }
        
        return null;
    }
}