<?php
// finances.php

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

// Veritabanı tabloları kontrolü ve oluşturma
$db_tables = [
    'finances' => "
        CREATE TABLE IF NOT EXISTS finances (
            id INT AUTO_INCREMENT PRIMARY KEY,
            u_id INT NOT NULL,
            category_id INT,
            description TEXT,
            price DECIMAL(10,2),
            event_type VARCHAR(10),
            event_date DATE,
            payment_type VARCHAR(20),
            ai_categorized TINYINT(1),
            ai_confidence DECIMAL(5,2),
            ai_matched_keywords TEXT,
            ai_suggested_categories TEXT,
            notes TEXT,
            created_at DATETIME,
            updated_at DATETIME,
            INDEX(u_id),
            INDEX(category_id),
            INDEX(event_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'categories' => "
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            u_id INT NOT NULL,
            title VARCHAR(100),
            icon VARCHAR(50),
            color VARCHAR(20),
            created_at DATETIME,
            INDEX(u_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'user_budgets' => "
        CREATE TABLE IF NOT EXISTS user_budgets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            u_id INT NOT NULL,
            type VARCHAR(10),
            total_amount DECIMAL(10,2),
            last_updated DATETIME,
            INDEX(u_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ",
    'user_preferences' => "
        CREATE TABLE IF NOT EXISTS user_preferences (
            id INT AUTO_INCREMENT PRIMARY KEY,
            u_id INT NOT NULL,
            learning_data JSON,
            settings JSON,
            last_updated DATETIME,
            INDEX(u_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    "
];

// Tabloları kontrol et ve oluştur
foreach($db_tables as $table => $sql) {
    try {
        $pia->query($sql);
    } catch (Exception $e) {
        error_log("Tablo oluşturma hatası ($table): " . $e->getMessage());
    }
}

// Yardımcı Fonksiyonlar Sınıfı
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
// Kategori tanımları ve analiz kuralları
$category_groups = [
    'Araç Giderleri' => [
        'keywords' => [
            // Yakıt Terimleri
            'benzin', 'mazot', 'dizel', 'lpg', 'yakıt', 'akaryakıt', 
            'motorin', 'euro diesel', 'kurşunsuz', '95 oktan', '97 oktan',
            'opet maxima', 'shell v-power', 'bp ultimate', 'total excellium',
            'otogaz', 'araç yakıt', 'yakıt alımı', 'akaryakıt alımı',
            
            // Marka Bazlı Yakıt İstasyonları
            'shell', 'bp', 'opet', 'total', 'petrol ofisi', 'po', 'lukoil', 
            'aytemiz', 'go', 'alpet', 'petrol', 'akpet', 'sunpet', 'moil',
            'kadoil', 'termopet', 'memoil', 'petline', 'socar', 'tp petrol',
            
            // Servis ve Bakım
            'servis', 'bakım', 'tamir', 'onarım', 'revizyon', 'kontrol',
            'periyodik bakım', 'yağ değişimi', 'yağ bakım', 'motor bakım',
            'fren bakım', 'balata değişim', 'lastik değişim', 'rot balans',
            'akü değişimi', 'far ayarı', 'debriyaj', 'şanzıman', 'egzoz',
            'radyatör', 'klima bakım', 'polen filtresi', 'hava filtresi',
            'yağ filtresi', 'yakıt filtresi', 'triger seti', 'kayış',
            'amortisör', 'rotbaşı', 'rotil', 'salıncak', 'direksiyon',
            'marş motoru', 'alternatör', 'distribütör', 'enjektör',
            'turbo', 'katalitik', 'abs', 'esp', 'airbag', 'immobilizer',
            
            // Lastik ve Jant
            'lastik', 'jant', 'michelin', 'bridgestone', 'goodyear',
            'pirelli', 'continental', 'dunlop', 'hankook', 'kumho',
            'lassa', 'petlas', 'yazlık lastik', 'kışlık lastik',
            'dört mevsim lastik', 'stepne', 'bijon', 'supap', 'balans',
            'rot', 'lastik tamiri', 'patlak lastik', 'jant düzeltme',
            
            // Araç Temizlik ve Bakım
            'oto yıkama', 'araç yıkama', 'detaylı temizlik', 'iç temizlik',
            'dış temizlik', 'motor temizlik', 'boya koruma', 'seramik kaplama',
            'pasta cila', 'cam filmi', 'koltuk temizliği', 'döşeme temizliği',
            'tavan temizliği', 'torpido temizliği', 'halı yıkama', 'oto kuaför',
            'boya koruma', 'cam suyu', 'antifriz', 'motor yağı', 'fren hidroliği',
            'direksiyon hidroliği', 'şanzıman yağı', 'diferansiyel yağı',
            
            // Resmi İşlemler ve Belgeler
            'mtv', 'motorlu taşıtlar vergisi', 'trafik sigortası', 'kasko',
            'muayene', 'egzoz muayene', 'fenni muayene', 'ruhsat', 'noter',
            'plaka', 'trafik cezası', 'ogs', 'hgs', 'köprü geçiş', 'otoyol',
            'sürücü belgesi', 'ehliyet yenileme', 'araç tescil', 'devir işlemi',
            'zorunlu sigorta', 'yeşil kart', 'araç pasaport', 'geçiş belgesi',
            
            // Araç Markaları ve Servisler
            'mercedes', 'bmw', 'audi', 'volkswagen', 'vw', 'ford', 'fiat',
            'renault', 'toyota', 'honda', 'hyundai', 'kia', 'skoda', 'seat',
            'peugeot', 'citroen', 'opel', 'nissan', 'mazda', 'volvo', 'yetkili servis',
            'özel servis', 'oto sanayi', 'oto tamirci', 'oto elektrikçi',
            'oto döşemeci', 'oto boyacı', 'oto kaporta', 'oto ekspertiz',
            
            // Araç Aksesuarları
            'oto aksesuar', 'teyp', 'hoparlör', 'navigasyon', 'gps',
            'park sensörü', 'geri görüş kamerası', 'alarm', 'immobilizer',
            'merkezi kilit', 'sis farı', 'led far', 'xenon far', 'far ampulü',
            'paspas', 'koltuk kılıfı', 'bagaj havuzu', 'portbagaj', 'çeki demiri',
            'akü takviye', 'yangın tüpü', 'ilk yardım çantası', 'reflektör',
            'zincir', 'kar çorabı', 'cam suyu', 'silecek', 'anten', 'usb şarj'
        ],
        'sub_categories' => [
            'Yakıt' => [
                'icon' => 'fa-gas-pump',
                'keywords' => ['benzin', 'mazot', 'dizel', 'lpg', 'yakıt'],
                'analysis_rules' => [
                    'frequency' => [
                        'daily_limit' => 1,
                        'weekly_limit' => 3,
                        'monthly_limit' => 10
                    ],
                    'amount_thresholds' => [
                        'warning' => 500,
                        'alert' => 1000
                    ],
                    'pattern_detection' => [
                        'same_day_fill' => true,
                        'unusual_amount' => true,
                        'frequency_change' => true
                    ]
                ]
            ],
            'Bakım' => [
                'icon' => 'fa-wrench',
                'keywords' => ['servis', 'bakım', 'tamir', 'onarım'],
                'analysis_rules' => [
                    'service_intervals' => [
                        'regular' => 6, // ay
                        'major' => 12    // ay
                    ],
                    'amount_thresholds' => [
                        'regular' => 1000,
                        'major' => 5000
                    ],
                    'notifications' => [
                        'upcoming_service' => true,
                        'warranty_expiry' => true
                    ]
                ]
            ],
            'Lastik' => [
                'icon' => 'fa-ring',
                'keywords' => ['lastik', 'jant', 'balans', 'rot'],
                'analysis_rules' => [
                    'replacement_cycle' => [
                        'summer' => 24, // ay
                        'winter' => 24, // ay
                        'all_season' => 36 // ay
                    ],
                    'rotation_interval' => 6, // ay
                    'pressure_check' => 1  // ay
                ]
            ],
            'Resmi İşlemler' => [
                'icon' => 'fa-file-alt',
                'keywords' => ['mtv', 'sigorta', 'muayene', 'ceza'],
                'analysis_rules' => [
                    'due_date_tracking' => true,
                    'renewal_reminders' => true,
                    'penalty_tracking' => true
                ]
            ],
            'Temizlik' => [
                'icon' => 'fa-spray-can',
                'keywords' => ['yıkama', 'temizlik', 'cila', 'koltuk'],
                'analysis_rules' => [
                    'frequency' => [
                        'regular_wash' => 7, // gün
                        'detailed_clean' => 90 // gün
                    ]
                ]
            ]
        ],
        'icon' => 'fa-car',
        'color' => 'danger',
        'analysis_rules' => [
            'budget_tracking' => [
                'monthly_limit' => 5000,
                'yearly_limit' => 50000,
                'emergency_fund' => 10000
            ],
            'expense_patterns' => [
                'regular' => [
                    'fuel' => 'weekly',
                    'service' => 'semi_annual',
                    'insurance' => 'annual'
                ],
                'seasonal' => [
                    'winter_prep' => ['10', '11'], // aylar
                    'summer_prep' => ['3', '4']    // aylar
                ]
            ],
            'notification_rules' => [
                'upcoming_expenses' => true,
                'unusual_patterns' => true,
                'maintenance_reminders' => true,
                'document_renewals' => true
            ],
            'reporting' => [
                'monthly_summary' => true,
                'quarterly_analysis' => true,
                'yearly_comparison' => true,
                'category_breakdown' => true
            ]
        ]
    ]
    // Diğer kategoriler bir sonraki parçada devam edecek...
];
// Kategori tanımları devam
$category_groups += [
    'Ev Giderleri' => [
        'keywords' => [
            // Faturalar ve Abonelikler
            'elektrik faturası', 'su faturası', 'doğalgaz faturası',
            'telefon faturası', 'internet faturası', 'tv faturası',
            'digiturk', 'dsmart', 'tivibu', 'netflix', 'spotify',
            'apple tv', 'amazon prime', 'youtube premium', 'blutv',
            'mubi', 'gain', 'exxen', 'turkcell tv+', 'vodafone tv',
            'türk telekom tv', 'fiber internet', 'adsl', 'vdsl',
            'superonline', 'turknet', 'millenicom', 'kablonet',
            'türksat', 'vodafone net', 'ttnet', 'gsm fatura',
            
            // Market ve Gıda Alışverişi
            'market', 'süpermarket', 'hipermarket', 'bakkal', 'manav',
            'kasap', 'şarküteri', 'fırın', 'pastane', 'organik market',
            'migros', 'carrefour', 'a101', 'bim', 'şok', 'metro',
            'makro', 'file', 'happy center', 'kim market', 'yunus market',
            'ekmek', 'süt', 'yoğurt', 'peynir', 'zeytin', 'yumurta',
            'et', 'tavuk', 'balık', 'sucuk', 'salam', 'sosis',
            'meyve', 'sebze', 'bakliyat', 'pirinç', 'bulgur', 'makarna',
            'un', 'şeker', 'tuz', 'baharat', 'salça', 'sıvı yağ',
            'zeytinyağı', 'tereyağı', 'margarin', 'reçel', 'bal',
            'çay', 'kahve', 'maden suyu', 'su', 'meşrubat', 'meyve suyu',
            'gazlı içecek', 'bisküvi', 'çikolata', 'cips', 'kuruyemiş',
            
            // Temizlik ve Hijyen
            'deterjan', 'şampuan', 'sabun', 'diş macunu', 'deodorant',
            'çamaşır suyu', 'yumuşatıcı', 'bulaşık deterjanı',
            'temizlik malzemesi', 'temizlik bezi', 'sünger', 'fırça',
            'çöp poşeti', 'çöp kovası', 'havlu kağıt', 'tuvalet kağıdı',
            'peçete', 'ıslak mendil', 'temizlik eldiveni', 'mop',
            'süpürge', 'faraş', 'cam sil', 'kir çözücü', 'kireç çözücü',
            'lavabo açıcı', 'ahşap temizleyici', 'mobilya cilası',
            'halı şampuanı', 'çamaşır sepeti', 'ütü', 'ütü masası',
            
            // Ev Bakım ve Onarım
            'tamir', 'tadilat', 'boya', 'badana', 'fayans', 'parke',
            'laminat', 'duvar kağıdı', 'tesisatçı', 'elektrikçi',
            'boyacı', 'marangoz', 'camcı', 'çilingir', 'klima servisi',
            'kombi bakım', 'petek temizliği', 'su tesisatı', 'sıhhi tesisat',
            'elektrik tesisatı', 'internet altyapı', 'anten montaj',
            'mobilya montaj', 'beyaz eşya montaj', 'avize montaj',
            'pencere tamiri', 'kapı tamiri', 'kilit değişimi',
            
            // Ev Eşyaları ve Mobilya
            'mobilya', 'koltuk', 'kanepe', 'yatak', 'dolap', 'gardırop',
            'masa', 'sandalye', 'sehpa', 'kitaplık', 'tv ünitesi',
            'beyaz eşya', 'buzdolabı', 'çamaşır makinesi', 'bulaşık makinesi',
            'fırın', 'ocak', 'aspiratör', 'mikrodalga', 'elektrikli süpürge',
            'küçük ev aleti', 'tost makinesi', 'kettle', 'blender',
            'mutfak robotu', 'kahve makinesi', 'ütü', 'saç kurutma makinesi',
            'vantilatör', 'klima', 'perde', 'halı', 'kilim', 'yastık',
            'yorgan', 'nevresim', 'çarşaf', 'havlu', 'battaniye'
        ],
        'sub_categories' => [
            'Faturalar' => [
                'icon' => 'fa-file-invoice',
                'keywords' => ['fatura', 'elektrik', 'su', 'doğalgaz', 'internet'],
                'analysis_rules' => [
                    'payment_tracking' => [
                        'due_dates' => true,
                        'auto_payment' => true,
                        'late_payment_warning' => true
                    ],
                    'consumption_analysis' => [
                        'monthly_comparison' => true,
                        'seasonal_patterns' => true,
                        'usage_optimization' => true
                    ],
                    'budget_alerts' => [
                        'threshold_percentage' => 20,
                        'year_over_year' => true,
                        'unusual_increase' => true
                    ]
                ]
            ],
            'Market' => [
                'icon' => 'fa-shopping-cart',
                'keywords' => ['market', 'süpermarket', 'manav', 'kasap'],
                'analysis_rules' => [
                    'shopping_patterns' => [
                        'frequency_analysis' => true,
                        'basket_size_tracking' => true,
                        'price_comparison' => true
                    ],
                    'budget_management' => [
                        'weekly_limit' => 1000,
                        'monthly_limit' => 4000,
                        'category_breakdown' => true
                    ],
                    'stock_management' => [
                        'inventory_tracking' => true,
                        'expiry_date_alerts' => true,
                        'shopping_list_automation' => true
                    ]
                ]
            ],
            'Temizlik' => [
                'icon' => 'fa-broom',
                'keywords' => ['temizlik', 'deterjan', 'hijyen'],
                'analysis_rules' => [
                    'consumption_tracking' => [
                        'monthly_usage' => true,
                        'reorder_points' => true,
                        'bulk_purchase_optimization' => true
                    ],
                    'cost_optimization' => [
                        'brand_comparison' => true,
                        'bulk_discounts' => true,
                        'seasonal_deals' => true
                    ]
                ]
            ],
            'Bakım Onarım' => [
                'icon' => 'fa-tools',
                'keywords' => ['tamir', 'tadilat', 'servis', 'montaj'],
                'analysis_rules' => [
                    'maintenance_schedule' => [
                        'regular_checks' => true,
                        'seasonal_maintenance' => true,
                        'warranty_tracking' => true
                    ],
                    'cost_tracking' => [
                        'repair_history' => true,
                        'replacement_vs_repair' => true,
                        'service_provider_comparison' => true
                    ]
                ]
            ]
        ],
        'icon' => 'fa-home',
        'color' => 'primary',
        'analysis_rules' => [
            'budget_management' => [
                'monthly_allocation' => [
                    'faturalar' => 0.30,
                    'market' => 0.40,
                    'temizlik' => 0.10,
                    'bakım' => 0.20
                ],
                'emergency_fund' => [
                    'percentage' => 0.10,
                    'minimum_amount' => 5000
                ],
                'seasonal_adjustments' => [
                    'winter_increase' => 0.20,
                    'summer_increase' => 0.15
                ]
            ],
            'optimization_strategies' => [
                'utility_usage' => [
                    'peak_hours_analysis' => true,
                    'energy_efficiency' => true,
                    'water_conservation' => true
                ],
                'bulk_purchasing' => [
                    'minimum_savings' => 0.15,
                    'storage_consideration' => true,
                    'expiry_tracking' => true
                ],
                'service_contracts' => [
                    'annual_maintenance' => true,
                    'warranty_extension' => true,
                    'insurance_coverage' => true
                ]
            ]
        ]
    ]
    // Diğer kategoriler bir sonraki parçada devam edecek...
];
$category_groups += [
    'Kişisel Harcamalar' => [
        'keywords' => [
            // Giyim ve Aksesuar
            'giyim', 'kıyafet', 'elbise', 'pantolon', 'gömlek', 'tişört',
            'kazak', 'hırka', 'mont', 'kaban', 'ceket', 'takım elbise',
            'etek', 'şort', 'mayo', 'iç giyim', 'çorap', 'pijama',
            'ayakkabı', 'bot', 'çizme', 'spor ayakkabı', 'terlik',
            'çanta', 'cüzdan', 'kemer', 'şapka', 'bere', 'atkı',
            'eldiven', 'takı', 'saat', 'gözlük', 'güneş gözlüğü',
            'mağaza', 'butik', 'zara', 'h&m', 'mango', 'pull&bear',
            'bershka', 'stradivarius', 'koton', 'lcw', 'defacto',
            'nike', 'adidas', 'puma', 'new balance', 'skechers',
            
            // Kişisel Bakım
            'kuaför', 'berber', 'saç kesimi', 'saç boyama', 'fön',
            'manikür', 'pedikür', 'epilasyon', 'ağda', 'cilt bakımı',
            'masaj', 'spa', 'hamam', 'sauna', 'güzellik merkezi',
            'kozmetik', 'parfüm', 'deodorant', 'şampuan', 'saç kremi',
            'duş jeli', 'vücut kremi', 'el kremi', 'güneş kremi',
            'makyaj malzemesi', 'ruj', 'rimel', 'fondöten', 'allık',
            'far', 'eyeliner', 'kaş kalemi', 'oje', 'aseton',
            'tıraş malzemesi', 'tıraş köpüğü', 'tıraş bıçağı',
            'after shave', 'kolonya', 'diş fırçası', 'diş macunu',
            
            // Sağlık ve Medikal
            'hastane', 'doktor', 'muayene', 'kontrol', 'check-up',
            'tahlil', 'röntgen', 'tomografi', 'ultrason', 'mri',
            'diş', 'ortodonti', 'implant', 'dolgu', 'kanal tedavisi',
            'göz', 'lens', 'gözlük camı', 'çerçeve', 'işitme cihazı',
            'fizik tedavi', 'rehabilitasyon', 'psikoterapi', 'psikolog',
            'diyetisyen', 'beslenme', 'vitamin', 'supplement', 'protein',
            'ilaç', 'antibiyotik', 'ağrı kesici', 'tansiyon ilacı',
            'şeker ilacı', 'kolesterol ilacı', 'alerji ilacı',
            
            // Spor ve Fitness
            'spor salonu', 'fitness center', 'pilates', 'yoga',
            'yüzme', 'tenis', 'basketbol', 'futbol', 'voleybol',
            'spor malzemesi', 'spor kıyafeti', 'spor ayakkabı',
            'protein tozu', 'vitamin', 'supplement', 'sporcu içeceği',
            'personal trainer', 'grup dersi', 'crossfit', 'zumba',
            
            // Eğlence ve Sosyal Aktiviteler
            'sinema', 'tiyatro', 'konser', 'festival', 'müze',
            'sergi', 'park', 'lunapark', 'aquapark', 'bowling',
            'bilardo', 'langırt', 'paintball', 'escape room',
            'restoran', 'cafe', 'bar', 'pub', 'club', 'diskotek',
            'kahvaltı', 'öğle yemeği', 'akşam yemeği', 'brunch',
            'fast food', 'pizza', 'burger', 'döner', 'kebap',
            'çay', 'kahve', 'tatlı', 'pasta', 'dondurma',
            
            // Hobi ve Kişisel Gelişim
            'kitap', 'dergi', 'gazete', 'kırtasiye', 'kalem',
            'defter', 'boya', 'hobi malzemesi', 'el işi',
            'müzik aleti', 'kurs', 'workshop', 'seminer',
            'konferans', 'eğitim', 'sertifika', 'online kurs',
            'dil kursu', 'müzik dersi', 'resim dersi', 'dans dersi'
        ],
        'sub_categories' => [
            'Giyim' => [
                'icon' => 'fa-tshirt',
                'keywords' => ['giyim', 'kıyafet', 'ayakkabı', 'aksesuar'],
                'analysis_rules' => [
                    'seasonal_tracking' => [
                        'winter_clothing' => ['10', '11', '12', '01', '02'],
                        'summer_clothing' => ['04', '05', '06', '07', '08'],
                        'transition_periods' => ['03', '09']
                    ],
                    'budget_allocation' => [
                        'monthly_limit' => 2000,
                        'special_events' => 5000,
                        'seasonal_shopping' => 7500
                    ],
                    'shopping_patterns' => [
                        'frequency_analysis' => true,
                        'brand_preferences' => true,
                        'price_range_tracking' => true,
                        'sale_season_optimization' => true
                    ]
                ]
            ],
            'Kişisel Bakım' => [
                'icon' => 'fa-spa',
                'keywords' => ['kuaför', 'kozmetik', 'bakım', 'güzellik'],
                'analysis_rules' => [
                    'service_intervals' => [
                        'hair_care' => 45, // gün
                        'skin_care' => 30, // gün
                        'nail_care' => 15  // gün
                    ],
                    'product_tracking' => [
                        'replenishment_alerts' => true,
                        'expiry_tracking' => true,
                        'preferred_brands' => true
                    ],
                    'cost_optimization' => [
                        'package_deals' => true,
                        'loyalty_programs' => true,
                        'bulk_purchases' => true
                    ]
                ]
            ],
            'Sağlık' => [
                'icon' => 'fa-heartbeat',
                'keywords' => ['hastane', 'doktor', 'ilaç', 'tedavi'],
                'analysis_rules' => [
                    'medical_tracking' => [
                        'regular_checkups' => true,
                        'prescription_tracking' => true,
                        'appointment_scheduling' => true
                    ],
                    'insurance_integration' => [
                        'coverage_analysis' => true,
                        'reimbursement_tracking' => true,
                        'network_optimization' => true
                    ],
                    'health_monitoring' => [
                        'chronic_conditions' => true,
                        'preventive_care' => true,
                        'wellness_programs' => true
                    ]
                ]
            ],
            'Eğlence' => [
                'icon' => 'fa-smile',
                'keywords' => ['sinema', 'tiyatro', 'restoran', 'cafe'],
                'analysis_rules' => [
                    'activity_tracking' => [
                        'frequency_analysis' => true,
                        'preferred_venues' => true,
                        'social_patterns' => true
                    ],
                    'budget_management' => [
                        'weekly_limit' => 500,
                        'monthly_limit' => 2000,
                        'special_events' => 1000
                    ],
                    'optimization' => [
                        'happy_hours' => true,
                        'group_discounts' => true,
                        'membership_benefits' => true
                    ]
                ]
            ]
        ],
        'icon' => 'fa-user',
        'color' => 'success',
        'analysis_rules' => [
            'budget_distribution' => [
                'essential' => 0.50,  // Temel ihtiyaçlar
                'lifestyle' => 0.30,  // Yaşam tarzı
                'discretionary' => 0.20  // İsteğe bağlı
            ],
            'spending_patterns' => [
                'daily_tracking' => true,
                'weekly_analysis' => true,
                'monthly_reporting' => true,
                'yearly_comparison' => true
            ],
            'optimization_strategies' => [
                'loyalty_programs' => true,
                'seasonal_purchases' => true,
                'bulk_buying' => true,
                'discount_timing' => true
            ],
            'alert_conditions' => [
                'overspending' => [
                    'daily_threshold' => 1000,
                    'weekly_threshold' => 5000,
                    'monthly_threshold' => 15000
                ],
                'unusual_activity' => [
                    'frequency_change' => true,
                    'amount_variance' => true,
                    'category_shift' => true
                ]
            ]
        ]
    ]
    // Diğer kategoriler bir sonraki parçada devam edecek...
];
$category_groups += [
    'Eğitim Giderleri' => [
        'keywords' => [
            // Okul ve Üniversite
            'okul ücreti', 'harç', 'kayıt ücreti', 'eğitim ödemesi',
            'özel okul', 'kolej', 'anaokulu', 'ilkokul', 'ortaokul',
            'lise', 'üniversite', 'yüksek lisans', 'doktora',
            'yurt ücreti', 'pansiyon', 'öğrenci evi', 'apart',
            'okul servisi', 'öğrenci servisi', 'okul yemek',
            'okul forması', 'okul çantası', 'okul ayakkabısı',
            'mezuniyet', 'diploma', 'transkript', 'öğrenci belgesi',
            
            // Kurs ve Sertifikalar
            'dil kursu', 'yabancı dil', 'ingilizce', 'almanca',
            'fransızca', 'ispanyolca', 'italyanca', 'rusça', 'arapça',
            'toefl', 'ielts', 'yds', 'yökdil', 'cambridge', 'pearson',
            'sertifika programı', 'mesleki eğitim', 'teknik eğitim',
            'bilgisayar kursu', 'yazılım kursu', 'web tasarım',
            'grafik tasarım', 'dijital pazarlama', 'sosyal medya',
            'muhasebe kursu', 'finansal analiz', 'borsa eğitimi',
            'yatırım danışmanlığı', 'emlak danışmanlığı',
            
            // Özel Ders ve Mentorluk
            'özel ders', 'birebir ders', 'etüt', 'matematik dersi',
            'fizik dersi', 'kimya dersi', 'biyoloji dersi',
            'geometri dersi', 'türkçe dersi', 'edebiyat dersi',
            'tarih dersi', 'coğrafya dersi', 'felsefe dersi',
            'müzik dersi', 'piyano dersi', 'gitar dersi',
            'keman dersi', 'flüt dersi', 'şan dersi',
            'resim dersi', 'kariyer koçluğu', 'yaşam koçluğu',
            
            // Sınav ve Test Hazırlık
            'sınav hazırlık', 'test hazırlık', 'deneme sınavı',
            'yks hazırlık', 'tyt hazırlık', 'ayt hazırlık',
            'kpss hazırlık', 'ales hazırlık', 'dgs hazırlık',
            'yds hazırlık', 'yökdil hazırlık', 'toefl hazırlık',
            'gmat hazırlık', 'gre hazırlık', 'sat hazırlık',
            'soru bankası', 'test kitabı', 'konu anlatım',
            'online sınav', 'online test', 'yaprak test',
            
            // Eğitim Materyalleri
            'ders kitabı', 'yardımcı kitap', 'kaynak kitap',
            'referans kitap', 'sözlük', 'atlas', 'ansiklopedi',
            'dergi aboneliği', 'bilimsel yayın', 'akademik dergi',
            'tablet', 'laptop', 'bilgisayar', 'yazıcı', 'tarayıcı',
            'hesap makinesi', 'cetvel takımı', 'pergel', 'iletki',
            'kalem', 'silgi', 'kalemtraş', 'defter', 'klasör',
            'dosya', 'sunum malzemesi', 'poster', 'maket',
            'laboratuvar malzemesi', 'deney seti', 'mikroskop',
            
            // Online Eğitim ve Dijital Platformlar
            'udemy', 'coursera', 'edx', 'linkedin learning',
            'pluralsight', 'skillshare', 'masterclass',
            'online eğitim', 'e-learning', 'uzaktan eğitim',
            'video eğitim', 'webinar', 'online seminer',
            'zoom premium', 'microsoft teams', 'google classroom',
            'eğitim yazılımı', 'dil öğrenme uygulaması', 'duolingo',
            'babbel', 'rosetta stone', 'memrise', 'busuu'
        ],
        'sub_categories' => [
            'Okul Ödemeleri' => [
                'icon' => 'fa-school',
                'keywords' => ['okul', 'harç', 'kayıt', 'yurt'],
                'analysis_rules' => [
                    'payment_schedule' => [
                        'term_payments' => true,
                        'installment_tracking' => true,
                        'early_payment_discount' => true
                    ],
                    'budget_planning' => [
                        'annual_projection' => true,
                        'emergency_fund' => true,
                        'scholarship_tracking' => true
                    ],
                    'expense_categorization' => [
                        'tuition' => true,
                        'accommodation' => true,
                        'transportation' => true,
                        'meals' => true
                    ]
                ]
            ],
            'Kurslar' => [
                'icon' => 'fa-chalkboard-teacher',
                'keywords' => ['kurs', 'sertifika', 'eğitim programı'],
                'analysis_rules' => [
                    'course_tracking' => [
                        'duration_monitoring' => true,
                        'progress_tracking' => true,
                        'completion_rate' => true
                    ],
                    'cost_analysis' => [
                        'roi_calculation' => true,
                        'comparison_shopping' => true,
                        'group_discount_opportunities' => true
                    ],
                    'scheduling' => [
                        'course_calendar' => true,
                        'conflict_detection' => true,
                        'prerequisite_tracking' => true
                    ]
                ]
            ],
            'Özel Dersler' => [
                'icon' => 'fa-user-graduate',
                'keywords' => ['özel ders', 'etüt', 'mentorluk'],
                'analysis_rules' => [
                    'lesson_management' => [
                        'scheduling_system' => true,
                        'attendance_tracking' => true,
                        'performance_monitoring' => true
                    ],
                    'tutor_analysis' => [
                        'qualification_verification' => true,
                        'student_feedback' => true,
                        'success_rate_tracking' => true
                    ],
                    'cost_optimization' => [
                        'group_lesson_options' => true,
                        'package_pricing' => true,
                        'referral_discounts' => true
                    ]
                ]
            ],
            'Eğitim Materyalleri' => [
                'icon' => 'fa-book',
                'keywords' => ['kitap', 'materyal', 'ekipman'],
                'analysis_rules' => [
                    'inventory_management' => [
                        'stock_tracking' => true,
                        'reorder_points' => true,
                        'condition_monitoring' => true
                    ],
                    'cost_efficiency' => [
                        'bulk_purchase_options' => true,
                        'used_material_consideration' => true,
                        'sharing_possibilities' => true
                    ],
                    'digital_transformation' => [
                        'e_book_alternatives' => true,
                        'digital_subscription_tracking' => true,
                        'device_compatibility' => true
                    ]
                ]
            ]
        ],
        'icon' => 'fa-graduation-cap',
        'color' => 'info',
        'analysis_rules' => [
            'education_planning' => [
                'short_term' => [
                    'monthly_expenses' => true,
                    'term_payments' => true,
                    'material_costs' => true
                ],
                'long_term' => [
                    'degree_completion' => true,
                    'certification_paths' => true,
                    'career_development' => true
                ]
            ],
            'financial_optimization' => [
                'payment_methods' => [
                    'installment_options' => true,
                    'early_payment_benefits' => true,
                    'scholarship_opportunities' => true
                ],
                'tax_benefits' => [
                    'education_deductions' => true,
                    'expense_documentation' => true,
                    'dependent_benefits' => true
                ]
            ],
            'performance_metrics' => [
                'academic_progress' => true,
                'certification_completion' => true,
                'skill_development' => true,
                'career_advancement' => true
            ]
        ]
    ]
    // Diğer kategoriler bir sonraki parçada devam edecek...
];
$category_groups += [
    'Seyahat ve Ulaşım' => [
        'keywords' => [
            // Toplu Taşıma ve Günlük Ulaşım
            'otobüs', 'metro', 'metrobüs', 'tramvay', 'marmaray',
            'dolmuş', 'minibüs', 'taksi', 'uber', 'bitaksi',
            'istanbulkart', 'ankarakart', 'izmirimkart', 'kentkart',
            'akbil', 'aylık abonman', 'öğrenci kartı', 'bilet',
            'durak', 'terminal', 'gar', 'istasyon', 'aktarma',
            
            // Şehirlerarası Ulaşım
            'uçak bileti', 'thy', 'pegasus', 'anadolujet', 'sunexpress',
            'tren bileti', 'tcdd', 'yht', 'hızlı tren', 'ekspres',
            'otobüs bileti', 'pamukkale', 'metro turizm', 'kamil koç',
            'nilüfer', 'ulusoy', 'varan', 'has turizm', 'setra',
            'feribot', 'vapur', 'deniz otobüsü', 'hızlı feribot',
            
            // Araç Kiralama
            'rent a car', 'araç kiralama', 'günlük kiralık', 'aylık kiralık',
            'avis', 'budget', 'enterprise', 'hertz', 'sixt', 'garenta',
            'yolcu360', 'elektrikli scooter', 'martı', 'bin', 'link',
            'bisiklet kiralama', 'motosiklet kiralama', 'karavan kiralama',
            'tekne kiralama', 'yat kiralama', 'transfer hizmeti',
            
            // Tatil ve Konaklama
            'otel', 'hotel', 'pansiyon', 'apart', 'villa', 'bungalov',
            'tatil köyü', 'resort', 'spa hotel', 'butik otel',
            'airbnb', 'booking.com', 'trivago', 'hotels.com', 'agoda',
            'tatilbudur', 'tatilsepeti', 'jollytur', 'etstur', 'anı tur',
            'erken rezervasyon', 'son dakika', 'all inclusive',
            'yarım pansiyon', 'oda kahvaltı', 'ultra her şey dahil',
            
            // Yurtdışı Seyahat
            'pasaport', 'vize', 'seyahat sigortası', 'yurtdışı çıkış',
            'duty free', 'tax free', 'döviz', 'exchange', 'euro',
            'dolar', 'pound', 'international', 'abroad', 'overseas',
            'roaming', 'yurtdışı internet', 'tercüme', 'rehberlik',
            
            // Seyahat Ekipmanları
            'valiz', 'bavul', 'sırt çantası', 'el çantası', 'laptop çantası',
            'seyahat yastığı', 'göz bandı', 'kulak tıkacı', 'adaptör',
            'powerbank', 'şarj aleti', 'kamera', 'fotoğraf makinesi',
            'tripod', 'harita', 'pusula', 'seyahat kitabı', 'rehber kitap',
            'ilk yardım çantası', 'güneş kremi', 'şemsiye', 'yağmurluk'
        ],
        'sub_categories' => [
            'Günlük Ulaşım' => [
                'icon' => 'fa-bus',
                'keywords' => ['otobüs', 'metro', 'taksi', 'dolmuş'],
                'analysis_rules' => [
                    'route_optimization' => [
                        'frequent_routes' => true,
                        'peak_hours_analysis' => true,
                        'alternative_routes' => true
                    ],
                    'cost_tracking' => [
                        'daily_limit' => 100,
                        'monthly_pass_analysis' => true,
                        'transfer_optimization' => true
                    ],
                    'usage_patterns' => [
                        'time_based_analysis' => true,
                        'weather_impact' => true,
                        'special_events' => true
                    ]
                ]
            ],
            'Seyahat Planlama' => [
                'icon' => 'fa-plane',
                'keywords' => ['uçak', 'otel', 'rezervasyon', 'tatil'],
                'analysis_rules' => [
                    'booking_optimization' => [
                        'price_tracking' => true,
                        'seasonal_analysis' => true,
                        'early_booking_benefits' => true
                    ],
                    'loyalty_programs' => [
                        'miles_tracking' => true,
                        'status_benefits' => true,
                        'point_optimization' => true
                    ],
                    'travel_insurance' => [
                        'coverage_analysis' => true,
                        'claim_tracking' => true,
                        'risk_assessment' => true
                    ]
                ]
            ],
            'Araç Kiralama' => [
                'icon' => 'fa-car',
                'keywords' => ['rent a car', 'kiralık araç', 'scooter'],
                'analysis_rules' => [
                    'rental_optimization' => [
                        'duration_analysis' => true,
                        'vehicle_type_selection' => true,
                        'insurance_options' => true
                    ],
                    'cost_comparison' => [
                        'provider_analysis' => true,
                        'package_deals' => true,
                        'membership_benefits' => true
                    ],
                    'usage_tracking' => [
                        'mileage_monitoring' => true,
                        'fuel_efficiency' => true,
                        'damage_reporting' => true
                    ]
                ]
            ],
            'Konaklama' => [
                'icon' => 'fa-hotel',
                'keywords' => ['otel', 'pansiyon', 'airbnb', 'resort'],
                'analysis_rules' => [
                    'accommodation_selection' => [
                        'location_analysis' => true,
                        'amenity_requirements' => true,
                        'review_analysis' => true
                    ],
                    'price_optimization' => [
                        'seasonal_rates' => true,
                        'length_of_stay' => true,
                        'package_inclusions' => true
                    ],
                    'quality_metrics' => [
                        'cleanliness_rating' => true,
                        'service_quality' => true,
                        'value_for_money' => true
                    ]
                ]
            ]
        ],
        'icon' => 'fa-route',
        'color' => 'warning',
        'analysis_rules' => [
            'travel_patterns' => [
                'frequency_analysis' => [
                    'daily_commute' => true,
                    'business_travel' => true,
                    'leisure_trips' => true
                ],
                'seasonal_trends' => [
                    'peak_seasons' => true,
                    'off_peak_opportunities' => true,
                    'weather_impact' => true
                ],
                'destination_analysis' => [
                    'popular_routes' => true,
                    'cost_comparison' => true,
                    'duration_patterns' => true
                ]
            ],
            'budget_optimization' => [
                'transportation' => [
                    'mode_selection' => true,
                    'ticket_timing' => true,
                    'loyalty_benefits' => true
                ],
                'accommodation' => [
                    'advance_booking' => true,
                    'location_vs_price' => true,
                    'amenity_value' => true
                ],
                'package_deals' => [
                    'combination_savings' => true,
                    'inclusive_benefits' => true,
                    'flexibility_value' => true
                ]
            ],
            'risk_management' => [
                'insurance_coverage' => [
                    'travel_insurance' => true,
                    'health_coverage' => true,
                    'cancellation_protection' => true
                ],
                'safety_measures' => [
                    'location_safety' => true,
                    'health_requirements' => true,
                    'emergency_contacts' => true
                ],
                'documentation' => [
                    'visa_requirements' => true,
                    'health_certificates' => true,
                    'booking_confirmations' => true
                ]
            ]
        ]
    ]
    // Diğer kategoriler bir sonraki parçada devam edecek...
];
$category_groups += [
    'Yatırım ve Tasarruf' => [
        'keywords' => [
            // Banka ve Finans İşlemleri
            'mevduat', 'vadeli hesap', 'vadesiz hesap', 'birikim hesabı',
            'yatırım hesabı', 'emeklilik hesabı', 'çocuk hesabı',
            'altın hesabı', 'döviz hesabı', 'bitcoin hesabı', 'kripto hesap',
            'eft', 'havale', 'swift', 'western union', 'moneygram',
            'banka komisyonu', 'hesap işletim ücreti', 'kart aidatı',
            
            // Yatırım Araçları
            'hisse senedi', 'borsa', 'bist', 'tahvil', 'bono',
            'repo', 'ters repo', 'viop', 'forex', 'eurobond',
            'yatırım fonu', 'borsa yatırım fonu', 'emeklilik fonu',
            'portföy yönetimi', 'risk yönetimi', 'varlık yönetimi',
            'temettü', 'kar payı', 'faiz geliri', 'kira geliri',
            
            // Altın ve Değerli Madenler
            'altın', 'gram altın', 'çeyrek altın', 'yarım altın',
            'tam altın', 'cumhuriyet altını', 'reşat altını',
            'gümüş', 'platin', 'paladyum', 'külçe altın',
            'altın bilezik', 'altın kolye', 'altın küpe',
            'sarrafiye', 'kuyumcu', 'darpane', 'has altın',
            
            // Gayrimenkul Yatırımları
            'ev alımı', 'daire', 'villa', 'arsa', 'tarla',
            'dükkan', 'ofis', 'depo', 'fabrika', 'plaza',
            'gayrimenkul', 'emlak', 'tapu', 'mortgage', 'ipotek',
            'kira geliri', 'aidat', 'apartman', 'site', 'rezidans',
            
            // Kripto Paralar
            'bitcoin', 'ethereum', 'ripple', 'litecoin', 'dogecoin',
            'binance', 'btcturk', 'paribu', 'coinbase', 'kraken',
            'blockchain', 'mining', 'madencilik', 'wallet', 'cüzdan',
            'defi', 'nft', 'token', 'ico', 'airdrop',
            
            // Sigorta ve Güvence
            'hayat sigortası', 'sağlık sigortası', 'emeklilik sigortası',
            'bireysel emeklilik', 'bes', 'tamamlayıcı sağlık sigortası',
            'işsizlik sigortası', 'ferdi kaza sigortası', 'yatırım sigortası',
            'prim ödemesi', 'poliçe', 'sigorta teminatı', 'risk primi'
        ],
        'sub_categories' => [
            'Banka İşlemleri' => [
                'icon' => 'fa-university',
                'keywords' => ['mevduat', 'hesap', 'eft', 'havale'],
                'analysis_rules' => [
                    'account_management' => [
                        'balance_tracking' => [
                            'minimum_balance' => true,
                            'optimal_balance' => true,
                            'excess_funds_alert' => true
                        ],
                        'fee_analysis' => [
                            'monthly_fees' => true,
                            'transaction_costs' => true,
                            'service_charges' => true
                        ],
                        'interest_optimization' => [
                            'rate_comparison' => true,
                            'term_selection' => true,
                            'compound_interest' => true
                        ]
                    ],
                    'transaction_patterns' => [
                        'frequency_analysis' => true,
                        'amount_patterns' => true,
                        'timing_optimization' => true
                    ],
                    'service_utilization' => [
                        'digital_banking' => true,
                        'atm_usage' => true,
                        'branch_services' => true
                    ]
                ]
            ],
            'Yatırım Portföyü' => [
                'icon' => 'fa-chart-line',
                'keywords' => ['hisse', 'borsa', 'fon', 'tahvil'],
                'analysis_rules' => [
                    'portfolio_management' => [
                        'asset_allocation' => [
                            'risk_profile' => true,
                            'diversification' => true,
                            'rebalancing' => true
                        ],
                        'performance_tracking' => [
                            'return_analysis' => true,
                            'benchmark_comparison' => true,
                            'risk_metrics' => true
                        ],
                        'market_analysis' => [
                            'technical_analysis' => true,
                            'fundamental_analysis' => true,
                            'market_sentiment' => true
                        ]
                    ],
                    'trading_strategy' => [
                        'entry_points' => true,
                        'exit_strategy' => true,
                        'position_sizing' => true
                    ],
                    'tax_efficiency' => [
                        'tax_loss_harvesting' => true,
                        'dividend_strategy' => true,
                        'holding_period' => true
                    ]
                ]
            ],
            'Değerli Varlıklar' => [
                'icon' => 'fa-coins',
                'keywords' => ['altın', 'gümüş', 'gayrimenkul'],
                'analysis_rules' => [
                    'asset_tracking' => [
                        'physical_storage' => [
                            'security_measures' => true,
                            'insurance_coverage' => true,
                            'inventory_management' => true
                        ],
                        'valuation_updates' => [
                            'market_price_tracking' => true,
                            'appreciation_analysis' => true,
                            'depreciation_factors' => true
                        ],
                        'maintenance_costs' => [
                            'storage_fees' => true,
                            'insurance_premiums' => true,
                            'security_expenses' => true
                        ]
                    ],
                    'market_timing' => [
                        'buying_opportunities' => true,
                        'selling_signals' => true,
                        'market_cycles' => true
                    ],
                    'portfolio_balance' => [
                        'allocation_percentage' => true,
                        'risk_distribution' => true,
                        'liquidity_needs' => true
                    ]
                ]
            ],
            'Emeklilik ve Sigorta' => [
                'icon' => 'fa-shield-alt',
                'keywords' => ['emeklilik', 'sigorta', 'bes'],
                'analysis_rules' => [
                    'retirement_planning' => [
                        'contribution_strategy' => [
                            'optimal_amount' => true,
                            'frequency_optimization' => true,
                            'employer_match' => true
                        ],
                        'fund_selection' => [
                            'risk_profile' => true,
                            'age_based_allocation' => true,
                            'performance_tracking' => true
                        ],
                        'tax_benefits' => [
                            'deduction_tracking' => true,
                            'contribution_limits' => true,
                            'withdrawal_rules' => true
                        ]
                    ],
                    'insurance_coverage' => [
                        'policy_review' => true,
                        'premium_optimization' => true,
                        'benefit_analysis' => true
                    ],
                    'long_term_planning' => [
                        'goal_setting' => true,
                        'milestone_tracking' => true,
                        'adjustment_strategy' => true
                    ]
                ]
            ]
        ],
        'icon' => 'fa-piggy-bank',
        'color' => 'success',
        'analysis_rules' => [
            'portfolio_management' => [
                'asset_allocation' => [
                    'risk_assessment' => true,
                    'diversification_metrics' => true,
                    'rebalancing_triggers' => true
                ],
                'performance_tracking' => [
                    'return_calculation' => true,
                    'risk_adjusted_returns' => true,
                    'benchmark_comparison' => true
                ],
                'tax_efficiency' => [
                    'tax_loss_harvesting' => true,
                    'tax_advantaged_accounts' => true,
                    'capital_gains_management' => true
                ]
            ]
        ]
    ]
    // Diğer kategoriler bir sonraki parçada devam edecek...
];
class SmartCategorizer {
    private $db;
    private $categories;
    private $learning_data;
    private $user_id;
    
    public function __construct($db, $categories, $user_id) {
        $this->db = $db;
        $this->categories = $categories;
        $this->user_id = $user_id;
        $this->loadLearningData();
    }
    
    private function loadLearningData() {
        $query = "SELECT learning_data FROM user_preferences WHERE u_id = ?";
        $result = $this->db->query($query, [$this->user_id]);
        
        if($result && $row = $result->fetch()) {
            $this->learning_data = json_decode($row['learning_data'], true) ?? [];
        } else {
            $this->learning_data = [];
        }
    }
    
    public function categorizeTransaction($description, $amount, $date = null) {
        $description = mb_strtolower(trim($description), 'UTF-8');
        $matches = [];
        $confidence_scores = [];
        
        // Öğrenilmiş verilerden eşleştirme
        if(isset($this->learning_data[$description])) {
            return [
                'category_id' => $this->learning_data[$description]['category_id'],
                'confidence' => 0.95,
                'matched_keywords' => ['learned_pattern'],
                'suggested_categories' => []
            ];
        }
        
        // Her kategori için anahtar kelime kontrolü
        foreach($this->categories as $category_name => $category_data) {
            $category_score = 0;
            $matched_keywords_for_category = [];
            
            // Ana kategori anahtar kelimeleri kontrolü
            foreach($category_data['keywords'] as $keyword) {
                if(mb_strpos($description, $keyword) !== false) {
                    $category_score += 1;
                    $matched_keywords_for_category[] = $keyword;
                }
            }
            
            // Alt kategori anahtar kelimeleri kontrolü
            foreach($category_data['sub_categories'] as $sub_cat_name => $sub_cat_data) {
                foreach($sub_cat_data['keywords'] as $keyword) {
                    if(mb_strpos($description, $keyword) !== false) {
                        $category_score += 1.5; // Alt kategori eşleşmelerine daha yüksek ağırlık
                        $matched_keywords_for_category[] = $keyword;
                    }
                }
            }
            
            // Metin benzerliği analizi
            foreach($category_data['keywords'] as $keyword) {
                $similarity = $this->calculateSimilarity($description, $keyword);
                if($similarity > 80) { // %80 üzeri benzerlik
                    $category_score += ($similarity / 100);
                    $matched_keywords_for_category[] = $keyword . "(similarity: {$similarity}%)";
                }
            }
            
            // Tutar bazlı analiz
            if(isset($category_data['amount_ranges'])) {
                foreach($category_data['amount_ranges'] as $range => $score) {
                    list($min, $max) = explode('-', $range);
                    if($amount >= $min && $amount <= $max) {
                        $category_score += $score;
                    }
                }
            }
            
            // Tarih bazlı analiz
            if($date && isset($category_data['date_patterns'])) {
                $month = date('m', strtotime($date));
                $day = date('d', strtotime($date));
                
                if(isset($category_data['date_patterns']['months'][$month])) {
                    $category_score += 0.5;
                }
                
                if(isset($category_data['date_patterns']['days'][$day])) {
                    $category_score += 0.3;
                }
            }
            
            if($category_score > 0) {
                $confidence = min(($category_score / 5) * 100, 100); // Maksimum 100%
                $confidence_scores[$category_name] = [
                    'score' => $confidence,
                    'matched_keywords' => array_unique($matched_keywords_for_category)
                ];
            }
        }
        
        // En iyi eşleşmeleri sırala
        arsort($confidence_scores);
        
        // En yüksek güven skoruna sahip kategoriyi seç
        $best_match = key($confidence_scores);
        $best_score = current($confidence_scores);
        
        // Diğer önerilen kategorileri hazırla
        $suggested_categories = [];
        $count = 0;
        foreach($confidence_scores as $cat_name => $score_data) {
            if($count++ >= 3) break; // En iyi 3 öneriyi al
            if($cat_name !== $best_match) {
                $suggested_categories[$cat_name] = $score_data['score'];
            }
        }
        
        // Sonuçları döndür
        return [
            'category_id' => array_search($best_match, array_keys($this->categories)),
            'confidence' => $best_score['score'],
            'matched_keywords' => $best_score['matched_keywords'],
            'suggested_categories' => $suggested_categories
        ];
    }
    
    public function learnFromCorrection($description, $correct_category_id) {
        $this->learning_data[$description] = [
            'category_id' => $correct_category_id,
            'learned_at' => date('Y-m-d H:i:s'),
            'confidence' => 1
        ];
        
        // Öğrenilen verileri kaydet
        $query = "UPDATE user_preferences SET 
                 learning_data = ?,
                 last_updated = NOW()
                 WHERE u_id = ?";
                 
        $this->db->query($query, [
            json_encode($this->learning_data),
            $this->user_id
        ]);
    }
    
    private function calculateSimilarity($str1, $str2) {
        $str1 = mb_strtolower(trim($str1), 'UTF-8');
        $str2 = mb_strtolower(trim($str2), 'UTF-8');
        
        similar_text($str1, $str2, $percent);
        return $percent;
    }
    
    public function analyzeTransactionPatterns($transactions) {
        $patterns = [
            'frequency' => [],
            'amount_ranges' => [],
            'time_patterns' => [],
            'category_correlations' => []
        ];
        
        foreach($transactions as $transaction) {
            // Frekans analizi
            $date = date('Y-m', strtotime($transaction->event_date));
            $patterns['frequency'][$date][$transaction->category_id] = 
                ($patterns['frequency'][$date][$transaction->category_id] ?? 0) + 1;
            
            // Tutar aralıkları analizi
            $amount_range = $this->getAmountRange($transaction->amount);
            $patterns['amount_ranges'][$transaction->category_id][$amount_range] = 
                ($patterns['amount_ranges'][$transaction->category_id][$amount_range] ?? 0) + 1;
            
            // Zaman kalıpları analizi
            $time_pattern = date('H', strtotime($transaction->created_at));
            $patterns['time_patterns'][$transaction->category_id][$time_pattern] = 
                ($patterns['time_patterns'][$transaction->category_id][$time_pattern] ?? 0) + 1;
            
            // Kategori korelasyonları
            if(isset($prev_transaction)) {
                $key = $prev_transaction->category_id . '-' . $transaction->category_id;
                $patterns['category_correlations'][$key] = 
                    ($patterns['category_correlations'][$key] ?? 0) + 1;
            }
            $prev_transaction = $transaction;
        }
        
        return $patterns;
    }
    
    private function getAmountRange($amount) {
        $ranges = [
            '0-50' => 50,
            '51-100' => 100,
            '101-250' => 250,
            '251-500' => 500,
            '501-1000' => 1000,
            '1001-2500' => 2500,
            '2501-5000' => 5000,
            '5001+' => PHP_FLOAT_MAX
        ];
        
        foreach($ranges as $range => $max) {
            if($amount <= $max) return $range;
        }
        
        return '5001+';
    }
}
class FinanceManager {
    private $db;
    private $user_id;
    private $categorizer;
    
    public function __construct($db, $user_id, $categorizer) {
        $this->db = $db;
        $this->user_id = $user_id;
        $this->categorizer = $categorizer;
    }
    
    public function addTransaction($data) {
        try {
            // Akıllı kategorizasyon
            if(empty($data['category_id'])) {
                $categorization = $this->categorizer->categorizeTransaction(
                    $data['description'],
                    $data['price'],
                    $data['event_date']
                );
                
                $data['category_id'] = $categorization['category_id'];
                $data['ai_categorized'] = 1;
                $data['ai_confidence'] = $categorization['confidence'];
                $data['ai_matched_keywords'] = json_encode($categorization['matched_keywords']);
                $data['ai_suggested_categories'] = json_encode($categorization['suggested_categories']);
            }
            
            // İşlem ekleme
            $query = "INSERT INTO finances (
                u_id, category_id, description, price, event_type,
                event_date, payment_type, ai_categorized, ai_confidence,
                ai_matched_keywords, ai_suggested_categories, notes,
                created_at, updated_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )";
            
            $params = [
                $this->user_id,
                $data['category_id'],
                $data['description'],
                $data['price'],
                $data['event_type'],
                $data['event_date'],
                $data['payment_type'],
                $data['ai_categorized'] ?? 0,
                $data['ai_confidence'] ?? 0,
                $data['ai_matched_keywords'] ?? null,
                $data['ai_suggested_categories'] ?? null,
                $data['notes'] ?? null
            ];
            
            $this->db->query($query, $params);
            return $this->db->lastInsertId();
            
        } catch (Exception $e) {
            error_log("İşlem ekleme hatası: " . $e->getMessage());
            throw new Exception("İşlem eklenirken bir hata oluştu");
        }
    }
    
    public function updateTransaction($id, $data) {
        try {
            $updates = [];
            $params = [];
            
            // Güncellenebilir alanları kontrol et
            $updatable_fields = [
                'category_id', 'description', 'price', 'event_type',
                'event_date', 'payment_type', 'notes'
            ];
            
            foreach($updatable_fields as $field) {
                if(isset($data[$field])) {
                    $updates[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if(empty($updates)) {
                throw new Exception("Güncellenecek veri bulunamadı");
            }
            
            $updates[] = "updated_at = NOW()";
            $params[] = $this->user_id;
            $params[] = $id;
            
            $query = "UPDATE finances SET " . implode(', ', $updates) . 
                    " WHERE u_id = ? AND id = ?";
            
            return $this->db->query($query, $params);
            
        } catch (Exception $e) {
            error_log("İşlem güncelleme hatası: " . $e->getMessage());
            throw new Exception("İşlem güncellenirken bir hata oluştu");
        }
    }
    
    public function deleteTransaction($id) {
        try {
            $query = "DELETE FROM finances WHERE u_id = ? AND id = ?";
            return $this->db->query($query, [$this->user_id, $id]);
            
        } catch (Exception $e) {
            error_log("İşlem silme hatası: " . $e->getMessage());
            throw new Exception("İşlem silinirken bir hata oluştu");
        }
    }
    
    public function getTransactions($filters = []) {
        try {
            $where = ["u_id = ?"];
            $params = [$this->user_id];
            
            // Filtre parametrelerini işle
            if(!empty($filters['category_id'])) {
                $where[] = "category_id = ?";
                $params[] = $filters['category_id'];
            }
            
            if(!empty($filters['date_start'])) {
                $where[] = "event_date >= ?";
                $params[] = $filters['date_start'];
            }
            
            if(!empty($filters['date_end'])) {
                $where[] = "event_date <= ?";
                $params[] = $filters['date_end'];
            }
            
            if(!empty($filters['event_type'])) {
                $where[] = "event_type = ?";
                $params[] = $filters['event_type'];
            }
            
            if(!empty($filters['payment_type'])) {
                $where[] = "payment_type = ?";
                $params[] = $filters['payment_type'];
            }
            
            if(!empty($filters['search'])) {
                $where[] = "description LIKE ?";
                $params[] = "%{$filters['search']}%";
            }
            
            // Sıralama ve limit
            $order = !empty($filters['order']) ? $filters['order'] : 'event_date DESC';
            $limit = !empty($filters['limit']) ? (int)$filters['limit'] : 100;
            $offset = !empty($filters['offset']) ? (int)$filters['offset'] : 0;
            
            $query = "SELECT * FROM finances 
                     WHERE " . implode(' AND ', $where) . "
                     ORDER BY {$order}
                     LIMIT {$limit} OFFSET {$offset}";
            
            return $this->db->query($query, $params)->fetchAll();
            
        } catch (Exception $e) {
            error_log("İşlem listeleme hatası: " . $e->getMessage());
            throw new Exception("İşlemler listelenirken bir hata oluştu");
        }
    }
    
    public function getAnalytics($period = 'month') {
        try {
            $date_format = $period == 'month' ? '%Y-%m' : '%Y';
            
            $query = "SELECT 
                        DATE_FORMAT(event_date, ?) as period,
                        category_id,
                        event_type,
                        COUNT(*) as transaction_count,
                        SUM(price) as total_amount,
                        AVG(price) as avg_amount,
                        MIN(price) as min_amount,
                        MAX(price) as max_amount
                     FROM finances
                     WHERE u_id = ?
                     GROUP BY period, category_id, event_type
                     ORDER BY period DESC, category_id";
            
            return $this->db->query($query, [$date_format, $this->user_id])->fetchAll();
            
        } catch (Exception $e) {
            error_log("Analiz hatası: " . $e->getMessage());
            throw new Exception("Analiz yapılırken bir hata oluştu");
        }
    }
    
    public function getBudgetStatus() {
        try {
            $query = "SELECT 
                        category_id,
                        SUM(CASE WHEN event_type = 'income' THEN price ELSE 0 END) as total_income,
                        SUM(CASE WHEN event_type = 'expense' THEN price ELSE 0 END) as total_expense,
                        COUNT(*) as transaction_count
                     FROM finances
                     WHERE u_id = ?
                     AND event_date >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH)
                     GROUP BY category_id";
            
            return $this->db->query($query, [$this->user_id])->fetchAll();
            
        } catch (Exception $e) {
            error_log("Bütçe durumu hatası: " . $e->getMessage());
            throw new Exception("Bütçe durumu alınırken bir hata oluştu");
        }
    }
    
    public function getTrends() {
        try {
            // Son 12 ayın trend analizi
            $query = "SELECT 
                        DATE_FORMAT(event_date, '%Y-%m') as month,
                        event_type,
                        SUM(price) as total_amount,
                        COUNT(*) as transaction_count
                     FROM finances
                     WHERE u_id = ?
                     AND event_date >= DATE_SUB(CURRENT_DATE, INTERVAL 12 MONTH)
                     GROUP BY month, event_type
                     ORDER BY month DESC";
            
            return $this->db->query($query, [$this->user_id])->fetchAll();
            
        } catch (Exception $e) {
            error_log("Trend analizi hatası: " . $e->getMessage());
            throw new Exception("Trend analizi yapılırken bir hata oluştu");
        }
    }
}
// Ana uygulama işleyici
class FinanceApp {
    private $db;
    private $user_id;
    private $manager;
    private $categorizer;
    
    public function __construct($db, $user_id) {
        $this->db = $db;
        $this->user_id = $user_id;
        $this->categorizer = new SmartCategorizer($db, $GLOBALS['category_groups'], $user_id);
        $this->manager = new FinanceManager($db, $user_id, $this->categorizer);
    }
    
    public function handleRequest($action, $params = []) {
        try {
            switch($action) {
                case 'add_transaction':
                    return $this->handleAddTransaction($params);
                
                case 'update_transaction':
                    return $this->handleUpdateTransaction($params);
                
                case 'delete_transaction':
                    return $this->handleDeleteTransaction($params);
                
                case 'get_transactions':
                    return $this->handleGetTransactions($params);
                
                case 'get_analytics':
                    return $this->handleGetAnalytics($params);
                
                case 'get_budget_status':
                    return $this->handleGetBudgetStatus();
                
                case 'get_trends':
                    return $this->handleGetTrends();
                
                case 'get_suggestions':
                    return $this->handleGetSuggestions($params);
                
                default:
                    throw new Exception("Geçersiz işlem");
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    
    private function handleAddTransaction($params) {
        $required = ['description', 'price', 'event_type', 'event_date', 'payment_type'];
        foreach($required as $field) {
            if(empty($params[$field])) {
                throw new Exception("$field alanı zorunludur");
            }
        }
        
        $id = $this->manager->addTransaction($params);
        
        return [
            'status' => 'success',
            'message' => 'İşlem başarıyla eklendi',
            'data' => ['id' => $id]
        ];
    }
    
    private function handleUpdateTransaction($params) {
        if(empty($params['id'])) {
            throw new Exception("İşlem ID'si gereklidir");
        }
        
        $this->manager->updateTransaction($params['id'], $params);
        
        return [
            'status' => 'success',
            'message' => 'İşlem başarıyla güncellendi'
        ];
    }
    
    private function handleDeleteTransaction($params) {
        if(empty($params['id'])) {
            throw new Exception("İşlem ID'si gereklidir");
        }
        
        $this->manager->deleteTransaction($params['id']);
        
        return [
            'status' => 'success',
            'message' => 'İşlem başarıyla silindi'
        ];
    }
    
    private function handleGetTransactions($params) {
        $transactions = $this->manager->getTransactions($params);
        
        return [
            'status' => 'success',
            'data' => $transactions
        ];
    }
    
    private function handleGetAnalytics($params) {
        $period = $params['period'] ?? 'month';
        $analytics = $this->manager->getAnalytics($period);
        
        return [
            'status' => 'success',
            'data' => $analytics
        ];
    }
    
    private function handleGetBudgetStatus() {
        $budget = $this->manager->getBudgetStatus();
        
        return [
            'status' => 'success',
            'data' => $budget
        ];
    }
    
    private function handleGetTrends() {
        $trends = $this->manager->getTrends();
        
        return [
            'status' => 'success',
            'data' => $trends
        ];
    }
    
    private function handleGetSuggestions($params) {
        if(empty($params['description'])) {
            throw new Exception("Açıklama alanı gereklidir");
        }
        
        $categorization = $this->categorizer->categorizeTransaction(
            $params['description'],
            $params['amount'] ?? null,
            $params['date'] ?? null
        );
        
        return [
            'status' => 'success',
            'data' => $categorization
        ];
    }
}

// API Endpoint'leri
if(isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // Kullanıcı kontrolü ve oturum yönetimi
    session_start();
    if(!isset($_SESSION['user_id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Oturum açmanız gerekiyor'
        ]);
        exit;
    }
    
    // Veritabanı bağlantısı
    try {
        require('_class/config.php');
        
        // Ana uygulama başlatma
        $app = new FinanceApp($pia, $_SESSION['user_id']);
        
        // İsteği işle
        $result = $app->handleRequest(
            $_POST['action'],
            array_merge($_POST, $_FILES)
        );
        
        // Sonucu döndür
        echo json_encode($result);
        
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Sistem hatası: ' . $e->getMessage()
        ]);
    }
}

// Örnek kullanım:
/*
$.post('finances.php', {
    action: 'add_transaction',
    description: 'Market alışverişi',
    price: 250.50,
    event_type: 'expense',
    event_date: '2024-02-15',
    payment_type: 'credit_card'
}, function(response) {
    if(response.status == 'success') {
        // İşlem başarılı
        console.log('İşlem ID:', response.data.id);
    } else {
        // Hata durumu
        console.error(response.message);
    }
}, 'json');
*/