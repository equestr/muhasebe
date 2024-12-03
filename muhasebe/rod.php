<?php
// config.php ve session.php gerekli
require('_class/config.php');
require('session.php');

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

/**
 * AI Destekli Finansal Analiz Sistemi
 * Version: 1.0.0
 */

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

    // SQL güvenliği için genel temizleme fonksiyonu
    public static function cleanInput($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = self::cleanInput($value);
            }
        } else {
            $data = strip_tags(trim($data));
        }
        return $data;
    }

    // Dosya adı güvenliği
    public static function secureFileName($filename) {
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
    }
    
    // CSRF token oluşturma ve kontrol
    public static function generateCSRFToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validateCSRFToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

// Veritabanı bağlantısı için örnek config
define('DB_HOST', 'localhost');
define('DB_NAME', 'u1934376_den');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_CHARSET', 'utf8mb4');

// Temel güvenlik sabitleri
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('SESSION_LIFETIME', 3600); // 1 saat

// Oturum güvenlik ayarları
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
session_set_cookie_params([
    'lifetime' => SESSION_LIFETIME,
    'path' => '/',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

// Zaman dilimi ayarı
date_default_timezone_set('Europe/Istanbul');
<?php
class FinanceManager {
    private $db;
    private $user_id;
    private $categorizer;
    
    public function __construct($db, $user_id) {
        $this->db = $db;
        $this->user_id = $user_id;
        $this->categorizer = new SmartCategorizer($GLOBALS['category_groups']);
    }
    
    // Yeni işlem ekleme
    public function addTransaction($data) {
        try {
            // Input temizleme ve validasyon
            $data = AIHelper::cleanInput($data);
            
            // Kategori tespiti
            $categorization = $this->categorizer->categorize($data['description'], $data['amount']);
            
            // SQL sorgusu
            $query = "INSERT INTO finances (
                u_id,
                customer_id,
                category_id,
                method_id,
                price,
                event_type,
                description,
                adddate,
                is_recurring,
                recurring_start_date,
                recurring_months,
                safe_id,
                ai_categorized,
                ai_confidence,
                ai_matched_keywords
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";
            
            return $this->db->query($query, [
                $this->user_id,
                $data['customer_id'] ?? 0,
                $data['category_id'] ?? $this->ensureCategory($categorization['category']),
                $data['method_id'] ?? 0,
                $data['amount'],
                $data['type'], // 1: gelir, 0: gider
                $data['description'],
                $data['is_recurring'] ?? 0,
                $data['recurring_start_date'] ?? null,
                $data['recurring_months'] ?? null,
                $data['safe_id'] ?? null,
                $categorization ? 1 : 0,
                $categorization ? $categorization['match_info']['confidence'] : 0,
                $categorization ? json_encode($categorization['match_info']['matched_keywords']) : null
            ]);
            
        } catch (Exception $e) {
            error_log("Finansal işlem eklenirken hata: " . $e->getMessage());
            return false;
        }
    }
    
    // Kategori kontrolü/oluşturma
    private function ensureCategory($category_name) {
        $existing = $this->db->get_row("
            SELECT id FROM categories 
            WHERE title = ? AND type = ?
        ", [$category_name, 'expense']);
        
        if($existing) {
            return $existing->id;
        }
        
        // Yeni kategori oluştur
        $this->db->query("
            INSERT INTO categories (title, type) 
            VALUES (?, ?)
        ", [
            $category_name,
            'expense'
        ]);
        
        return $this->db->lastInsertId();
    }
    
    // İşlem listesi
    public function getTransactions($filters = []) {
        $where = ["f.u_id = ?"];
        $params = [$this->user_id];
        
        if(!empty($filters['start_date'])) {
            $where[] = "DATE(f.adddate) >= ?";
            $params[] = $filters['start_date'];
        }
        
        if(!empty($filters['end_date'])) {
            $where[] = "DATE(f.adddate) <= ?";
            $params[] = $filters['end_date'];
        }
        
        if(isset($filters['type']) && $filters['type'] !== '') {
            $where[] = "f.event_type = ?";
            $params[] = $filters['type'];
        }
        
        if(!empty($filters['category_id'])) {
            $where[] = "f.category_id = ?";
            $params[] = $filters['category_id'];
        }

        if(!empty($filters['customer_id'])) {
            $where[] = "f.customer_id = ?";
            $params[] = $filters['customer_id'];
        }
        
        if(!empty($filters['safe_id'])) {
            $where[] = "f.safe_id = ?";
            $params[] = $filters['safe_id'];
        }
        
        $whereStr = implode(" AND ", $where);
        
        return $this->db->get_results("
            SELECT 
                f.*,
                c.title as category_name,
                cu.title as customer_name,
                cu.phone as customer_phone,
                s.title as safe_name,
                s.type as safe_type
            FROM finances f
            LEFT JOIN categories c ON f.category_id = c.id
            LEFT JOIN customers cu ON f.customer_id = cu.id
            LEFT JOIN safes s ON f.safe_id = s.id
            WHERE {$whereStr}
            ORDER BY f.adddate DESC, f.id DESC
        ", $params);
    }
    
    // Özet rapor
    public function getSummary($period = 'month') {
        $date_filter = "";
        switch($period) {
            case 'month':
                $date_filter = "AND DATE(adddate) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $date_filter = "AND DATE(adddate) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                break;
        }
        
        return $this->db->get_row("
            SELECT 
                SUM(CASE WHEN event_type = 1 THEN price ELSE 0 END) as total_income,
                SUM(CASE WHEN event_type = 0 THEN price ELSE 0 END) as total_expense,
                COUNT(*) as total_transactions,
                COUNT(CASE WHEN ai_categorized = 1 THEN 1 END) as ai_categorized_count
            FROM finances
            WHERE u_id = ? {$date_filter}
        ", [$this->user_id]);
    }
    
    // Kategori bazlı analiz
    public function getCategoryAnalysis($period = 'month') {
        $date_filter = "";
        switch($period) {
            case 'month':
                $date_filter = "AND DATE(f.adddate) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $date_filter = "AND DATE(f.adddate) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                break;
        }
        
        return $this->db->get_results("
            SELECT 
                c.title as category_name,
                c.type as category_type,
                COUNT(*) as transaction_count,
                SUM(f.price) as total_amount,
                AVG(f.price) as average_amount,
                MIN(f.price) as min_amount,
                MAX(f.price) as max_amount
            FROM finances f
            LEFT JOIN categories c ON f.category_id = c.id
            WHERE f.u_id = ? {$date_filter}
            GROUP BY f.category_id
            ORDER BY total_amount DESC
        ", [$this->user_id]);
    }
    
    // Müşteri bazlı analiz
    public function getCustomerAnalysis($period = 'month') {
        $date_filter = "";
        switch($period) {
            case 'month':
                $date_filter = "AND DATE(f.adddate) >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $date_filter = "AND DATE(f.adddate) >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
                break;
        }
        
        return $this->db->get_results("
            SELECT 
                c.title as customer_name,
                c.phone as customer_phone,
                COUNT(*) as transaction_count,
                SUM(CASE WHEN f.event_type = 1 THEN f.price ELSE 0 END) as total_income,
                SUM(CASE WHEN f.event_type = 0 THEN f.price ELSE 0 END) as total_expense
            FROM finances f
            LEFT JOIN customers c ON f.customer_id = c.id
            WHERE f.u_id = ? {$date_filter}
            GROUP BY f.customer_id
            ORDER BY total_income DESC
        ", [$this->user_id]);
    }

    // Kasa/Banka analizi
    public function getSafeAnalysis() {
        return $this->db->get_results("
            SELECT 
                s.*,
                (SELECT SUM(price) FROM finances 
                 WHERE safe_id = s.id AND event_type = 1) as total_income,
                (SELECT SUM(price) FROM finances 
                 WHERE safe_id = s.id AND event_type = 0) as total_expense
            FROM safes s
            WHERE s.u_id = ?
            ORDER BY s.type, s.title
        ", [$this->user_id]);
    }
}
<?php
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
    
    // Ana kategorizasyon fonksiyonu
    public function categorize($description, $amount) {
        $description = mb_strtolower($description, 'UTF-8');
        $matches = [];
        
        // Önce öğrenilmiş düzeltmeleri kontrol et
        if ($learned_category = $this->checkLearningData($description)) {
            return $learned_category;
        }
        
        // Normal kategori eşleştirme
        foreach($this->categories as $category_name => $category_info) {
            $score = 0;
            $matched_keywords = [];
            
            // Ana keyword kontrolü
            foreach($category_info['keywords'] as $keyword) {
                if(mb_stripos($description, mb_strtolower($keyword, 'UTF-8')) !== false) {
                    $score += 2;
                    $matched_keywords[] = $keyword;
                }
            }
            
            // Alt kategori kontrolü
            if (isset($category_info['sub_categories'])) {
                foreach($category_info['sub_categories'] as $sub_cat => $sub_info) {
                    if (isset($sub_info['keywords'])) {
                        foreach($sub_info['keywords'] as $keyword) {
                            if(mb_stripos($description, mb_strtolower($keyword, 'UTF-8')) !== false) {
                                $score += 1;
                                $matched_keywords[] = $keyword;
                            }
                        }
                    }
                }
            }
            
            // Tutar bazlı analiz
            if (isset($category_info['amount_ranges'])) {
                foreach ($category_info['amount_ranges'] as $range) {
                    if ($amount >= $range['min'] && $amount <= $range['max']) {
                        $score += $range['score'];
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
    
    // Öğrenme verilerini kontrol et
    private function checkLearningData($description) {
        foreach($this->learning_data['corrections'] as $pattern => $category) {
            if(mb_stripos($description, $pattern) !== false) {
                return [
                    'category' => $category,
                    'match_info' => [
                        'score' => 100,
                        'confidence' => 100,
                        'matched_keywords' => ['learned_pattern'],
                        'category_info' => $this->categories[$category] ?? []
                    ],
                    'source' => 'learning'
                ];
            }
        }
        return null;
    }
    
    // Yeni öğrenme verisi ekle
    public function addLearningData($pattern, $category) {
        $this->learning_data['corrections'][mb_strtolower($pattern, 'UTF-8')] = $category;
    }
}

// İlk kategori tanımlamaları
$category_groups = [
    'Araç Giderleri' => [
        'keywords' => [
            // Yakıt Terimleri
            'benzin', 'mazot', 'dizel', 'lpg', 'yakıt', 'akaryakıt', 
            'motorin', 'euro diesel', 'kurşunsuz', '95 oktan', '97 oktan',
            
            // Marka Bazlı Yakıt İstasyonları
            'shell', 'bp', 'opet', 'total', 'petrol ofisi', 'po', 'lukoil', 
            'aytemiz', 'go', 'alpet', 'petrol', 'akpet', 'sunpet',
            
            // Araç Bakım
            'servis', 'bakım', 'tamir', 'onarım', 'revizyon', 'kontrol',
            'periyodik bakım', 'yağ değişimi', 'yağ bakım', 'motor bakım',
            'fren bakım', 'balata değişim', 'lastik değişim', 'rot balans'
        ],
        'sub_categories' => [
            'Yakıt' => [
                'keywords' => ['benzin', 'mazot', 'dizel', 'lpg', 'yakıt']
            ],
            'Bakım' => [
                'keywords' => ['servis', 'bakım', 'tamir', 'onarım']
            ]
        ],
        'amount_ranges' => [
            ['min' => 0, 'max' => 1000, 'score' => 1],
            ['min' => 1001, 'max' => 5000, 'score' => 2],
            ['min' => 5001, 'max' => 999999, 'score' => 3]
        ]
    ],
    
    'Pazarlama Giderleri' => [
        'keywords' => [
            // Online Reklamlar
            'google ads', 'facebook reklam', 'instagram reklam',
            'linkedin reklam', 'youtube reklam', 'twitter reklam',
            'tiktok reklam', 'dijital reklam', 'sosyal medya',
            
            // Web Hizmetleri
            'hosting', 'domain', 'ssl', 'website', 'web sitesi',
            'email marketing', 'e-posta pazarlama', 'crm',
            
            // Platformlar
            'sahibinden', 'hürriyet emlak', 'emlakjet',
            'arabam', 'letgo', 'n11', 'trendyol'
        ],
        'sub_categories' => [
            'Online Reklam' => [
                'keywords' => ['reklam', 'ads', 'kampanya']
            ],
            'Web Hizmetleri' => [
                'keywords' => ['hosting', 'domain', 'web']
            ]
        ],
        'amount_ranges' => [
            ['min' => 0, 'max' => 500, 'score' => 1],
            ['min' => 501, 'max' => 2000, 'score' => 2],
            ['min' => 2001, 'max' => 999999, 'score' => 3]
        ]
    ]
];
<?php
// Kategori tanımlamalarının devamı
$category_groups += [
    'Emlak Giderleri' => [
        'keywords' => [
            // Emlak İşlemleri
            'kira', 'depozito', 'emlak komisyonu', 'stopaj',
            'emlakçı', 'gayrimenkul', 'konut', 'daire', 'apartman',
            'tapu', 'noter', 'ipotek', 'kredi', 'komisyon',
            'ekspertiz', 'değerleme', 'emlak vergisi',
            
            // Sahibinden.com ile ilgili
            'sahibinden', 'sahibinden.com', 'emlak paketi',
            'doping', 'vitrin', 'acil acil', 'hızlı teklif',
            
            // Diğer Platformlar
            'hürriyet emlak', 'emlakjet', 'zingat', 'milliyetemlak',
            'hemnal', 'propertytr', 'yanında', 'emlaksepeti'
        ],
        'sub_categories' => [
            'Kira' => [
                'keywords' => ['kira', 'stopaj', 'depozito']
            ],
            'Emlak Portalleri' => [
                'keywords' => ['sahibinden', 'emlak paketi', 'doping']
            ],
            'Komisyonlar' => [
                'keywords' => ['komisyon', 'emlakçı', 'aracılık']
            ]
        ],
        'amount_ranges' => [
            ['min' => 0, 'max' => 1000, 'score' => 1],
            ['min' => 1001, 'max' => 10000, 'score' => 2],
            ['min' => 10001, 'max' => 999999, 'score' => 3]
        ]
    ],

    'Personel Giderleri' => [
        'keywords' => [
            // Maaş ve Ödemeler
            'maaş', 'ücret', 'avans', 'ikramiye', 'prim',
            'mesai', 'fazla mesai', 'ek ödeme', 'harcırah',
            
            // SGK ve Vergi
            'sgk', 'bağkur', 'emeklilik', 'sigorta primi',
            'gelir vergisi', 'damga vergisi', 'stopaj',
            'muhtasar', 'işsizlik sigortası',
            
            // İsimler (örnek personel)
            'enes türk', 'mehmet ersin türk'
        ],
        'sub_categories' => [
            'Maaşlar' => [
                'keywords' => ['maaş', 'ücret', 'avans']
            ],
            'Vergi ve SGK' => [
                'keywords' => ['sgk', 'vergi', 'sigorta']
            ]
        ],
        'amount_ranges' => [
            ['min' => 0, 'max' => 5000, 'score' => 1],
            ['min' => 5001, 'max' => 15000, 'score' => 2],
            ['min' => 15001, 'max' => 999999, 'score' => 3]
        ]
    ],

    'Yakıt Giderleri' => [
        'keywords' => [
            // Araç Tanımları
            '21adg438', '38ahm097', 'mazot', 'yakıt gideri',
            'benzin', 'motorin', 'lpg', 'opet', 'bp', 'shell',
            'total', 'petrol ofisi', 'po', 'aytemiz'
        ],
        'sub_categories' => [
            'Araç 1' => [
                'keywords' => ['21adg438', 'mazot gideri']
            ],
            'Araç 2' => [
                'keywords' => ['38ahm097', 'mazot gideri']
            ]
        ],
        'amount_ranges' => [
            ['min' => 0, 'max' => 1000, 'score' => 1],
            ['min' => 1001, 'max' => 3000, 'score' => 2],
            ['min' => 3001, 'max' => 999999, 'score' => 3]
        ]
    ],

    'Sabit Giderler' => [
        'keywords' => [
            // Faturalar
            'elektrik', 'su', 'doğalgaz', 'telefon', 'internet',
            'gsm', 'mobil', 'fiber', 'adsl', 'aidat', 'kira',
            
            // Hizmet Sağlayıcılar
            'türk telekom', 'türkcell', 'vodafone', 'superonline',
            'ttnet', 'digiturk', 'dsmart', 'netflix',
            
            // Vergiler
            'emlak vergisi', 'çevre temizlik', 'mtv',
            'belediye vergisi', 'stopaj'
        ],
        'sub_categories' => [
            'Faturalar' => [
                'keywords' => ['elektrik', 'su', 'doğalgaz', 'telefon']
            ],
            'Vergiler' => [
                'keywords' => ['vergi', 'mtv', 'stopaj']
            ]
        ],
        'amount_ranges' => [
            ['min' => 0, 'max' => 500, 'score' => 1],
            ['min' => 501, 'max' => 2000, 'score' => 2],
            ['min' => 2001, 'max' => 999999, 'score' => 3]
        ]
    ],

    'Sabit Gelirler' => [
        'keywords' => [
            // Komisyon Gelirleri
            'komisyon', 'daire komisyon', 'emlak komisyonu',
            'aracılık', 'danışmanlık', 'hizmet bedeli',
            
            // Müşteriler
            'enes türk', 'mehmet ersin türk'
        ],
        'sub_categories' => [
            'Emlak Komisyonları' => [
                'keywords' => ['komisyon', 'daire', 'emlak']
            ],
            'Danışmanlık' => [
                'keywords' => ['danışmanlık', 'hizmet']
            ]
        ],
        'amount_ranges' => [
            ['min' => 0, 'max' => 10000, 'score' => 1],
            ['min' => 10001, 'max' => 50000, 'score' => 2],
            ['min' => 50001, 'max' => 999999, 'score' => 3]
        ]
    ]
];

// Özel analiz kuralları
$analysis_rules = [
    'high_frequency_alerts' => [
        'daily_limit' => 3,
        'weekly_limit' => 10,
        'monthly_limit' => 30
    ],
    'amount_thresholds' => [
        'warning_threshold' => 10000,
        'alert_threshold' => 50000,
        'approval_required' => 100000
    ],
    'category_limits' => [
        'Yakıt Giderleri' => [
            'monthly_max' => 15000,
            'alert_percentage' => 80
        ],
        'Personel Giderleri' => [
            'monthly_max' => 100000,
            'alert_percentage' => 90
        ]
    ],
    'customer_rules' => [
        'max_credit_limit' => 50000,
        'payment_due_days' => 30,
        'late_payment_interest' => 1.5
    ]
];
<?php
// Ana sayfa için gerekli bileşenleri yükle
require_once('includes/header.php');
require_once('includes/sidebar.php');

class FinancialAnalyzer {
    private $db;
    private $user_id;
    
    public function __construct($db, $user_id) {
        $this->db = $db;
        $this->user_id = $user_id;
    }
    
    // Kasa/Banka durumu analizi
    public function analyzeSafes() {
        return $this->db->get_results("
            SELECT 
                s.*,
                (
                    SELECT COALESCE(SUM(price), 0)
                    FROM finances
                    WHERE safe_id = s.id 
                    AND event_type = 1
                ) as total_income,
                (
                    SELECT COALESCE(SUM(price), 0)
                    FROM finances
                    WHERE safe_id = s.id 
                    AND event_type = 0
                ) as total_expense
            FROM safes s
            WHERE s.u_id = ?
        ", [$this->user_id]);
    }
    
    // Bekleyen ödemeler analizi
    public function analyzePendingPayments() {
        return $this->db->get_results("
            SELECT 
                p.*,
                c.title as category_name,
                cu.title as customer_name,
                cu.phone as customer_phone,
                s.title as safe_name
            FROM pending_payments p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN customers cu ON p.customer_id = cu.id
            LEFT JOIN safes s ON p.safe_id = s.id
            WHERE p.u_id = ? AND p.status = 'pending'
            ORDER BY p.due_date
        ", [$this->user_id]);
    }
    
    // Müşteri bazlı analiz
    public function analyzeCustomers() {
        return $this->db->get_results("
            SELECT 
                c.*,
                COUNT(f.id) as transaction_count,
                SUM(CASE WHEN f.event_type = 1 THEN f.price ELSE 0 END) as total_income,
                SUM(CASE WHEN f.event_type = 0 THEN f.price ELSE 0 END) as total_expense,
                MAX(f.adddate) as last_transaction_date
            FROM customers c
            LEFT JOIN finances f ON c.id = f.customer_id
            WHERE c.status = 1
            GROUP BY c.id
            ORDER BY transaction_count DESC
        ");
    }

    // Nakit akışı analizi
    public function analyzeCashFlow($period = 'month') {
        $sql_period = "INTERVAL 1 MONTH";
        if($period == 'year') {
            $sql_period = "INTERVAL 1 YEAR";
        }

        return $this->db->get_results("
            SELECT 
                DATE(adddate) as date,
                SUM(CASE WHEN event_type = 1 THEN price ELSE 0 END) as income,
                SUM(CASE WHEN event_type = 0 THEN price ELSE 0 END) as expense,
                SUM(CASE WHEN event_type = 1 THEN price ELSE -price END) as net
            FROM finances
            WHERE u_id = ? 
            AND adddate >= DATE_SUB(CURRENT_DATE, {$sql_period})
            GROUP BY DATE(adddate)
            ORDER BY date
        ", [$this->user_id]);
    }
}

// Frontend işleyici sınıfı
class UIHandler {
    private $finance_manager;
    private $analyzer;
    
    public function __construct($finance_manager, $analyzer) {
        $this->finance_manager = $finance_manager;
        $this->analyzer = $analyzer;
    }
    
    // Ana dashboard
    public function renderDashboard() {
        $safes = $this->analyzer->analyzeSafes();
        $pending = $this->analyzer->analyzePendingPayments();
        $cash_flow = $this->analyzer->analyzeCashFlow();
        
        include('templates/dashboard.php');
    }
    
    // İşlem listesi
    public function renderTransactions($filters = []) {
        $transactions = $this->finance_manager->getTransactions($filters);
        $categories = $this->getCategories();
        $customers = $this->getCustomers();
        $safes = $this->getSafes();
        
        include('templates/transactions.php');
    }
    
    // Yeni işlem formu
    public function renderNewTransactionForm() {
        $categories = $this->getCategories();
        $customers = $this->getCustomers();
        $safes = $this->getSafes();
        
        include('templates/new_transaction.php');
    }
    
    // Yardımcı metodlar
    private function getCategories() {
        global $pia;
        return $pia->get_results("SELECT * FROM categories WHERE status = 1 ORDER BY title");
    }
    
    private function getCustomers() {
        global $pia;
        return $pia->get_results("SELECT * FROM customers WHERE status = 1 ORDER BY title");
    }
    
    private function getSafes() {
        global $pia;
        return $pia->get_results("SELECT * FROM safes WHERE status = 1 ORDER BY title");
    }
}

// Ana işleyici
$finance = new FinanceManager($pia, $admin->id);
$analyzer = new FinancialAnalyzer($pia, $admin->id);
$ui = new UIHandler($finance, $analyzer);

// Sayfa yönlendirmesi
$page = $_GET['page'] ?? 'dashboard';

switch($page) {
    case 'dashboard':
        $ui->renderDashboard();
        break;
        
    case 'transactions':
        $filters = [
            'start_date' => $_GET['start_date'] ?? date('Y-m-01'),
            'end_date' => $_GET['end_date'] ?? date('Y-m-t'),
            'type' => $_GET['type'] ?? null,
            'category_id' => $_GET['category_id'] ?? null,
            'customer_id' => $_GET['customer_id'] ?? null,
            'safe_id' => $_GET['safe_id'] ?? null
        ];
        $ui->renderTransactions($filters);
        break;
        
    case 'new_transaction':
        $ui->renderNewTransactionForm();
        break;
        
    default:
        header('Location: index.php?page=dashboard');
        exit;
}
?>

<!-- Frontend şablonu -->
<?php if($page == 'dashboard'): ?>
<div class="container-fluid">
    <!-- Özet Kartları -->
    <div class="row mb-4">
        <?php foreach($safes as $safe): ?>
        <div class="col-md-3">
            <div class="card <?= $safe->type == 'bank_account' ? 'bg-primary' : 'bg-success' ?> text-white">
                <div class="card-body">
                    <h5><?= htmlspecialchars($safe->title) ?></h5>
                    <h3><?= number_format($safe->balance, 2) ?> ₺</h3>
                    <?php if($safe->type == 'credit_card'): ?>
                    <small>Limit: <?= number_format($safe->credit_limit, 2) ?> ₺</small>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Bekleyen Ödemeler -->
    <?php if(!empty($pending)): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h5>Bekleyen Ödemeler</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Vade</th>
                            <th>Kategori</th>
                            <th>Müşteri</th>
                            <th>Tutar</th>
                            <th>Kasa</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($pending as $payment): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($payment->due_date)) ?></td>
                            <td><?= htmlspecialchars($payment->category_name) ?></td>
                            <td><?= htmlspecialchars($payment->customer_name) ?></td>
                            <td><?= number_format($payment->amount, 2) ?> ₺</td>
                            <td><?= htmlspecialchars($payment->safe_name) ?></td>
                            <td>
                                <button class="btn btn-sm btn-success">Öde</button>
                                <button class="btn btn-sm btn-danger">İptal</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Nakit Akışı Grafiği -->
    <div class="card">
        <div class="card-header">
            <h5>Nakit Akışı</h5>
        </div>
        <div class="card-body">
            <canvas id="cashFlowChart"></canvas>
        </div>
    </div>
</div>

<script>
// Nakit akışı grafiği
var ctx = document.getElementById('cashFlowChart').getContext('2d');
var cashFlowData = <?= json_encode($cash_flow) ?>;

new Chart(ctx, {
    type: 'line',
    data: {
        labels: cashFlowData.map(item => item.date),
        datasets: [{
            label: 'Gelir',
            data: cashFlowData.map(item => item.income),
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }, {
            label: 'Gider',
            data: cashFlowData.map(item => item.expense),
            borderColor: 'rgb(255, 99, 132)',
            tension: 0.1
        }, {
            label: 'Net',
            data: cashFlowData.map(item => item.net),
            borderColor: 'rgb(54, 162, 235)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});
</script>
<?php endif; ?>