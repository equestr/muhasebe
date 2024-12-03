<?php
require('../_class/config.php');
require('../session.php');

define('HUGGINGFACE_API_KEY', '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');

class FinancialAnalyzer {
    private $db;
    private $user_id;
    private $start_date;
    private $end_date;

    public function __construct($db, $user_id) {
        $this->db = $db;
        $this->user_id = $user_id;
        $this->start_date = date('Y-m-d', strtotime('-30 days'));
        $this->end_date = date('Y-m-d');
    }

    // Gelir analizi
    public function analyzeIncome() {
        $income = $this->db->get_row("SELECT SUM(price) as total FROM finances 
            WHERE u_id = {$this->user_id} AND event_type = 1 
            AND DATE(adddate) BETWEEN '{$this->start_date}' AND '{$this->end_date}'")->total ?? 0;

        $categories_query = $this->db->query("SELECT c.title, SUM(f.price) as total 
            FROM finances f 
            JOIN categories c ON f.category_id = c.id 
            WHERE f.u_id = {$this->user_id} AND f.event_type = 1
            AND DATE(f.adddate) BETWEEN '{$this->start_date}' AND '{$this->end_date}'
            GROUP BY c.id 
            ORDER BY total DESC");

        $categories = [];
        if($categories_query) {
            foreach($categories_query as $cat) {
                $categories[] = [
                    'title' => $cat->title,
                    'total' => (float)$cat->total
                ];
            }
        }

        return [
            'total' => (float)$income,
            'categories' => $categories
        ];
    }

    // Gider analizi
    public function analyzeExpenses() {
        $expense = $this->db->get_row("SELECT SUM(price) as total FROM finances 
            WHERE u_id = {$this->user_id} AND event_type = 0 
            AND DATE(adddate) BETWEEN '{$this->start_date}' AND '{$this->end_date}'")->total ?? 0;

        $categories_query = $this->db->query("SELECT c.title, SUM(f.price) as total 
            FROM finances f 
            JOIN categories c ON f.category_id = c.id 
            WHERE f.u_id = {$this->user_id} AND f.event_type = 0
            AND DATE(f.adddate) BETWEEN '{$this->start_date}' AND '{$this->end_date}'
            GROUP BY c.id 
            ORDER BY total DESC");

        $categories = [];
        if($categories_query) {
            foreach($categories_query as $cat) {
                $categories[] = [
                    'title' => $cat->title,
                    'total' => (float)$cat->total
                ];
            }
        }

        return [
            'total' => (float)$expense,
            'categories' => $categories
        ];
    }

    // Nakit akışı analizi
    public function analyzeCashFlow() {
        $safes_query = $this->db->query("SELECT * FROM safes WHERE u_id = {$this->user_id} AND status = 1");
        $total_cash = 0;
        $total_bank = 0;
        $total_credit = 0;
        $total_credit_limit = 0;
        $safe_details = [];

        if($safes_query) {
            foreach($safes_query as $safe) {
                $safe_details[] = [
                    'title' => $safe->title,
                    'type' => $safe->type,
                    'balance' => (float)$safe->balance,
                    'credit_limit' => (float)($safe->credit_limit ?? 0)
                ];

                switch($safe->type) {
                    case 'cash':
                        $total_cash += (float)$safe->balance;
                        break;
                    case 'bank_account':
                        $total_bank += (float)$safe->balance;
                        break;
                    case 'credit_card':
                        $total_credit += (float)($safe->credit_limit - $safe->balance);
                        $total_credit_limit += (float)$safe->credit_limit;
                        break;
                }
            }
        }

        return [
            'total_cash' => $total_cash,
            'total_bank' => $total_bank,
            'total_credit' => $total_credit,
            'total_credit_limit' => $total_credit_limit,
            'safe_details' => $safe_details
        ];
    }

    // Trend analizi
    public function analyzeTrends($months = 6) {
        $trends_query = $this->db->query("SELECT 
            DATE_FORMAT(adddate, '%Y-%m') as month,
            SUM(CASE WHEN event_type = 1 THEN price ELSE 0 END) as income,
            SUM(CASE WHEN event_type = 0 THEN price ELSE 0 END) as expense
            FROM finances 
            WHERE u_id = {$this->user_id}
            AND adddate >= DATE_SUB(NOW(), INTERVAL {$months} MONTH)
            GROUP BY DATE_FORMAT(adddate, '%Y-%m')
            ORDER BY month ASC");

        $trends = [];
        if($trends_query) {
            foreach($trends_query as $trend) {
                $trends[] = [
                    'month' => $trend->month,
                    'income' => (float)$trend->income,
                    'expense' => (float)$trend->expense
                ];
            }
        }

        return $trends;
    }

    // Bekleyen ödemeler analizi
    public function analyzePendingPayments() {
        $payments_query = $this->db->query("SELECT pp.*, c.title as customer_name, cat.title as category_name 
            FROM pending_payments pp 
            LEFT JOIN customers c ON pp.customer_id = c.id 
            LEFT JOIN categories cat ON pp.category_id = cat.id 
            WHERE pp.u_id = {$this->user_id} AND pp.status = 'pending' 
            ORDER BY pp.due_date ASC");

        $payments = [];
        if($payments_query) {
            foreach($payments_query as $payment) {
                $payments[] = [
                    'id' => $payment->id,
                    'customer_name' => $payment->customer_name,
                    'category_name' => $payment->category_name,
                    'amount' => (float)$payment->amount,
                    'due_date' => $payment->due_date
                ];
            }
        }

        return $payments;
    }

    // Bütçe analizi
    public function analyzeBudget() {
        $categories_query = $this->db->query("SELECT 
            c.id, c.title,
            COALESCE(b.amount, 0) as budget_amount,
            COALESCE(SUM(f.price), 0) as spent_amount
            FROM categories c
            LEFT JOIN budgets b ON c.id = b.category_id AND b.u_id = {$this->user_id}
            LEFT JOIN finances f ON c.id = f.category_id 
                AND f.u_id = {$this->user_id} 
                AND f.event_type = 0
                AND DATE(f.adddate) BETWEEN '{$this->start_date}' AND '{$this->end_date}'
            GROUP BY c.id");

        $categories = [];
        if($categories_query) {
            foreach($categories_query as $category) {
                $categories[] = [
                    'id' => $category->id,
                    'title' => $category->title,
                    'budget_amount' => (float)$category->budget_amount,
                    'spent_amount' => (float)$category->spent_amount
                ];
            }
        }

        return $categories;
    }
}
class AIAnalyzer {
    private $api_key;
    private $model_url;

    public function __construct($api_key) {
        $this->api_key = $api_key;
        $this->model_url = "https://api-inference.huggingface.co/models/Helsinki-NLP/opus-mt-en-tr";
    }

    public function analyze($prompt) {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->model_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer " . $this->api_key,
                    "Content-Type: application/json"
                ],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([
                    "inputs" => $prompt,
                    "parameters" => [
                        "max_length" => 500,
                        "temperature" => 0.7,
                        "top_p" => 0.9,
                        "do_sample" => true
                    ]
                ])
            ]);

            $response = curl_exec($curl);
            
            if($response === false) {
                throw new Exception('Curl hatası: ' . curl_error($curl));
            }
            
            curl_close($curl);
            
            $result = json_decode($response, true);
            
            if (isset($result['error'])) {
                throw new Exception($result['error']);
            }

            return $result[0]['translation_text'] ?? $this->createBasicAnalysis($prompt);

        } catch (Exception $e) {
            error_log("AI Analiz Hatası: " . $e->getMessage());
            return $this->createBasicAnalysis($prompt);
        }
    }

    private function createBasicAnalysis($prompt) {
        // Sayısal değerleri çıkar
        preg_match_all('/(\d[\d\.,]*)\s*TL/', $prompt, $matches);
        $numbers = array_map(function($str) {
            return (float)str_replace([',', '.'], ['', '.'], $str);
        }, $matches[1]);

        $response = "📊 Finansal Analiz:\n\n";

        // Gelir-Gider Analizi
        if (strpos($prompt, 'gelir') !== false && strpos($prompt, 'gider') !== false) {
            $income = $numbers[0] ?? 0;
            $expense = $numbers[1] ?? 0;
            $net = $income - $expense;
            $ratio = $income > 0 ? ($expense / $income) * 100 : 0;

            $response .= $this->analyzeIncomeExpense($income, $expense, $net, $ratio);
        }

        // Borç Analizi
        if (strpos($prompt, 'kredi') !== false || strpos($prompt, 'borç') !== false) {
            $response .= $this->analyzeDebt($numbers[0] ?? 0);
        }

        // Nakit Analizi
        if (strpos($prompt, 'nakit') !== false) {
            $response .= $this->analyzeCash($numbers[0] ?? 0);
        }

        // Genel öneriler
        $response .= $this->getGeneralRecommendations();

        return $response;
    }

    private function analyzeIncomeExpense($income, $expense, $net, $ratio) {
        $response = "💰 Gelir-Gider Analizi:\n";
        $response .= "• Gelir/Gider Oranı: %" . number_format($ratio, 1) . "\n";
        
        if ($net > 0) {
            $response .= "✅ Pozitif nakit akışı\n";
            $response .= "💡 Öneriler:\n";
            $response .= "- Fazla miktarı yatırıma yönlendirin\n";
            $response .= "- Acil durum fonu oluşturun\n";
            if ($ratio > 70) {
                $response .= "⚠️ Gider oranı yüksek\n";
                $response .= "- Gider optimizasyonu yapın\n";
            }
        } else {
            $response .= "⚠️ Negatif nakit akışı\n";
            $response .= "💡 Öneriler:\n";
            $response .= "- Acil olmayan harcamaları erteleyin\n";
            $response .= "- Gelir artırıcı fırsatları değerlendirin\n";
            $response .= "- Detaylı bütçe planı yapın\n";
        }
        
        return $response . "\n";
    }

    private function analyzeDebt($total_debt) {
        $response = "\n💳 Borç Analizi:\n";
        
        if ($total_debt > 0) {
            $response .= "• Toplam Borç: " . number_format($total_debt, 2) . " TL\n";
            $response .= "💡 Öneriler:\n";
            $response .= "- Yüksek faizli borçlara öncelik verin\n";
            $response .= "- Minimum ödemelerin üzerinde ödeme yapın\n";
            $response .= "- Borç yapılandırma fırsatlarını değerlendirin\n";
            $response .= "- Yeni borçlanmadan kaçının\n";
        }
        
        return $response . "\n";
    }

    private function analyzeCash($cash) {
        $response = "\n💵 Nakit Durumu:\n";
        
        if ($cash < 1000) {
            $response .= "⚠️ Nakit seviyesi düşük\n";
            $response .= "💡 Öneriler:\n";
            $response .= "- Acil durum fonu oluşturun\n";
            $response .= "- Nakit akışını iyileştirin\n";
        } else {
            $response .= "✅ Nakit seviyesi yeterli\n";
            $response .= "💡 Öneriler:\n";
            $response .= "- Yatırım fırsatlarını değerlendirin\n";
            $response .= "- Paranızı çeşitlendirin\n";
        }
        
        return $response . "\n";
    }

    private function getGeneralRecommendations() {
        return "\n📌 Genel Öneriler:\n" .
               "• Düzenli bütçe takibi yapın\n" .
               "• Gereksiz harcamalardan kaçının\n" .
               "• Acil durum fonu oluşturun\n" .
               "• Uzun vadeli finansal hedefler belirleyin\n" .
               "• Yatırım fırsatlarını değerlendirin\n" .
               "• Finansal eğitiminize yatırım yapın";
    }
}
// Ana işlem kodu
if(isset($_POST['message'])) {
    $message = strtolower(trim($_POST['message']));
    $response = '';

    // Sınıf örneklerini oluştur
    $financial = new FinancialAnalyzer($pia, $admin->id);
    $ai = new AIAnalyzer(HUGGINGFACE_API_KEY);

    // Ödemeler Analizi
    if(strpos($message, 'ödeme') !== false) {
        $pending = $financial->analyzePendingPayments();
        
        $response = "<div class='pending-payments'>";
        $response .= "<h4>⏰ Bekleyen Ödemeler</h4>";
        
        if(!empty($pending)) {
            $response .= "<table class='data-table'>";
            $response .= "<tr><th>Açıklama</th><th>Tutar</th><th>Vade</th><th>Kalan</th></tr>";
            
            foreach($pending as $payment) {
                $days_left = floor((strtotime($payment['due_date']) - time()) / (60*60*24));
                $row_class = $days_left < 3 ? 'urgent' : ($days_left < 7 ? 'warning' : '');
                
                $response .= "<tr class='{$row_class}'>";
                $response .= "<td>{$payment['customer_name']} - {$payment['category_name']}</td>";
                $response .= "<td>" . number_format($payment['amount'], 2) . " TL</td>";
                $response .= "<td>" . date('d.m.Y', strtotime($payment['due_date'])) . "</td>";
                $response .= "<td>{$days_left} gün</td>";
                $response .= "</tr>";
            }
            $response .= "</table>";
            
            // Özet bilgi
            $total_pending = array_sum(array_column($pending, 'amount'));
            $response .= "<div class='summary'>";
            $response .= "<p>Toplam " . count($pending) . " adet ödeme bekliyor.</p>";
            $response .= "<p>Toplam Tutar: " . number_format($total_pending, 2) . " TL</p>";
            $response .= "</div>";
        } else {
            $response .= "<p>Bekleyen ödeme bulunmuyor.</p>";
        }
        $response .= "</div>";
    }

    // Nakit Akışı Analizi
    elseif(strpos($message, 'nakit') !== false) {
        $cash_flow = $financial->analyzeCashFlow();
        
        $response = "<div class='cash-flow-analysis'>";
        $response .= "<h4>💰 Nakit Durumu</h4>";
        
        // Özet kartlar
        $response .= "<div class='summary-cards'>";
        $response .= "<div class='card " . ($cash_flow['total_cash'] > 0 ? 'success' : 'warning') . "'>
            <h5>💵 Nakit</h5>
            <div class='amount'>" . number_format($cash_flow['total_cash'], 2) . " TL</div>
        </div>";
        $response .= "<div class='card " . ($cash_flow['total_bank'] > 0 ? 'success' : 'warning') . "'>
            <h5>🏦 Banka</h5>
            <div class='amount'>" . number_format($cash_flow['total_bank'], 2) . " TL</div>
        </div>";
        $response .= "<div class='card " . ($cash_flow['total_credit'] < $cash_flow['total_credit_limit'] * 0.7 ? 'success' : 'danger') . "'>
            <h5>💳 Kredi Kartı</h5>
            <div class='amount'>-" . number_format($cash_flow['total_credit'], 2) . " TL</div>
        </div>";
        $response .= "</div>";

        // Toplam durum
        $net_cash = $cash_flow['total_cash'] + $cash_flow['total_bank'] - $cash_flow['total_credit'];
        $response .= "<div class='net-position " . ($net_cash >= 0 ? 'success' : 'danger') . "'>";
        $response .= "<h5>Net Durum: " . number_format($net_cash, 2) . " TL</h5>";
        $response .= "</div>";
        
        $response .= "</div>";
    }

    // Gelir Analizi
    elseif(strpos($message, 'gelir') !== false) {
        $income_data = $financial->analyzeIncome();
        
        $response = "<div class='income-analysis'>";
        $response .= "<h4>📈 Gelir Analizi</h4>";
        
        if(!empty($income_data['categories'])) {
            $response .= "<div class='summary'>";
            $response .= "<h5>Toplam Gelir: " . number_format($income_data['total'], 2) . " TL</h5>";
            $response .= "</div>";
            
            $response .= "<table class='data-table'>";
            $response .= "<tr><th>Kategori</th><th>Tutar</th><th>Oran</th></tr>";
            
            foreach($income_data['categories'] as $category) {
                $ratio = ($category['total'] / $income_data['total']) * 100;
                $response .= "<tr>";
                $response .= "<td>{$category['title']}</td>";
                $response .= "<td>" . number_format($category['total'], 2) . " TL</td>";
                $response .= "<td>%" . number_format($ratio, 1) . "</td>";
                $response .= "</tr>";
            }
            $response .= "</table>";
        } else {
            $response .= "<p>Bu dönemde gelir kaydı bulunmuyor.</p>";
        }
        
        $response .= "</div>";
    }

    // Gider Analizi
    elseif(strpos($message, 'gider') !== false) {
        $expense_data = $financial->analyzeExpenses();
        
        $response = "<div class='expense-analysis'>";
        $response .= "<h4>📉 Gider Analizi</h4>";
        
        if(!empty($expense_data['categories'])) {
            $response .= "<div class='summary'>";
            $response .= "<h5>Toplam Gider: " . number_format($expense_data['total'], 2) . " TL</h5>";
            $response .= "</div>";
            
            $response .= "<table class='data-table'>";
            $response .= "<tr><th>Kategori</th><th>Tutar</th><th>Oran</th></tr>";
            
            foreach($expense_data['categories'] as $category) {
                $ratio = ($category['total'] / $expense_data['total']) * 100;
                $response .= "<tr>";
                $response .= "<td>{$category['title']}</td>";
                $response .= "<td>" . number_format($category['total'], 2) . " TL</td>";
                $response .= "<td>%" . number_format($ratio, 1) . "</td>";
                $response .= "</tr>";
            }
            $response .= "</table>";
        } else {
            $response .= "<p>Bu dönemde gider kaydı bulunmuyor.</p>";
        }
        
        $response .= "</div>";
    }

    // Yardım Menüsü
    elseif(strpos($message, 'yardım') !== false || strpos($message, 'komut') !== false) {
        $response = "<div class='help-menu'>";
        $response .= "<h4>🤖 Kullanılabilir Komutlar</h4>";
        $response .= "<ul>";
        $response .= "<li><strong>nakit</strong> - Nakit durumu ve kasa analizi</li>";
        $response .= "<li><strong>ödemeler</strong> - Bekleyen ödemeler listesi</li>";
        $response .= "<li><strong>gelir</strong> - Gelir analizi</li>";
        $response .= "<li><strong>gider</strong> - Gider analizi</li>";
        $response .= "<li><strong>yardım</strong> - Bu menüyü gösterir</li>";
        $response .= "</ul>";
        $response .= "</div>";
    }

    // Anlaşılamayan sorular için genel AI yanıtı
    else {
        $prompt = "Finansal asistan olarak şu soruyu yanıtla: " . $message;
        $ai_response = $ai->analyze($prompt);
        
        $response = "<div class='ai-response'>";
        $response .= "<h4>🤖 AI Yanıtı</h4>";
        $response .= "<div class='response-content'>" . nl2br($ai_response) . "</div>";
        $response .= "</div>";
    }

    echo $response;
}
?>