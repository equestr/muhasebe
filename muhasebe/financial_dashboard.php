<?php 
require('_class/config.php'); 
require('session.php');   
?>
<!DOCTYPE html>
<html>
<head>
    <?php require('_inc/head.php'); ?>
    <title>Finansal Gösterge Paneli</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .widget { margin-bottom:50px; }  
        .widget .title h1 { font-size:20px; } 
        .widget .title { margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; }
        
        .card { 
            display: block; 
            position: relative; 
            background:#FFF; 
            padding:30px; 
            margin-bottom:30px; 
            border-radius: 4px; 
            overflow: hidden; 
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card .card-title { 
            display:block; 
            font-size:13px; 
            font-weight: 600; 
            color: #A1A5B7; 
            margin-bottom:15px; 
        }
        .price { 
            display:block; 
            font-size: 1.50em; 
            font-weight: 900; 
            color:#666666; 
        }
        .date { 
            display:block; 
            color:#A1A5B7; 
            font-size: 1em; 
            margin-top:5px; 
        }
        .progress { 
            height: 10px; 
            margin-bottom: 10px; 
            border-radius: 5px;
        }
        .alert-custom {
            padding: 15px;
            border-left: 4px solid;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-warning-custom {
            background-color: #fff8e1;
            border-left-color: #ffc107;
        }
        .alert-danger-custom {
            background-color: #fde9e9;
            border-left-color: #dc3545;
        }
        .alert-info-custom {
            background-color: #e3f2fd;
            border-left-color: #2196f3;
        }
    </style>
</head>
<body>
    <?php require('_inc/header.php'); ?>
    <section class="content">
        <div class="title">
            <div class="left">
                <h1>Finansal Gösterge Paneli</h1>
                <ul class="breadcrumb">
                    <li><a href="main.php">Anasayfa</a></li>
                    <li>Finansal Gösterge Paneli</li>
                </ul>
            </div>
        </div>

        <!-- Kredi Kartı Takibi -->
        <div class="widget">
            <div class="title">
                <div class="left"><h1>Kredi Kartı Takibi</h1></div>
            </div>
            <div class="row">
                <?php 
                $credit_cards = $pia->query("
                    SELECT *, 
                           DATEDIFF(
                               DATE_ADD(
                                   DATE_FORMAT(NOW(), '%Y-%m-' || LPAD(statement_day, 2, '0')),
                                   INTERVAL CASE 
                                       WHEN DAY(NOW()) > statement_day 
                                       THEN 1 
                                       ELSE 0 
                                   END MONTH
                               ),
                               NOW()
                           ) as days_to_statement,
                           DATEDIFF(
                               DATE_ADD(
                                   DATE_FORMAT(NOW(), '%Y-%m-' || LPAD(due_day, 2, '0')),
                                   INTERVAL CASE 
                                       WHEN DAY(NOW()) > due_day 
                                       THEN 1 
                                       ELSE 0 
                                   END MONTH
                               ),
                               NOW()
                           ) as days_to_due
                    FROM safes 
                    WHERE u_id = $admin->id 
                    AND type = 'credit_card' 
                    AND status = 1
                ");

                if($credit_cards) {
                    foreach($credit_cards as $card) {
                        $used_amount = $card->credit_limit - $card->balance;
                        if($used_amount > 0) {
                            ?>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-title"><?=$card->title?></div>
                                    <div class="card-body">
                                        <strong class="price text-danger">
                                            <?=number_format($used_amount, 2)?> TL
                                        </strong>
                                        <div class="progress mt-2">
                                            <div class="progress-bar bg-danger" 
                                                 style="width: <?=($used_amount/$card->credit_limit)*100?>%">
                                            </div>
                                        </div>
                                        <span class="date">
                                            <?php
                                            if($card->days_to_statement <= 5 && $card->days_to_statement >= 0) {
                                                echo "<span class='text-warning'>Hesap kesim tarihine {$card->days_to_statement} gün kaldı</span>";
                                            } 
                                            elseif($card->days_to_statement < 0 && $card->days_to_due >= 0) {
                                                echo "<span class='text-info'>Son ödeme tarihine {$card->days_to_due} gün kaldı</span>";
                                            }
                                            elseif($card->days_to_due < 0) {
                                                echo "<span class='text-danger'>Son ödeme tarihi " . abs($card->days_to_due) . " gün geçti!</span>";
                                            }
                                            ?>
                                            <br>
                                            Limit: <?=number_format($card->credit_limit, 2)?> TL<br>
                                            Kullanılabilir: <?=number_format($card->balance, 2)?> TL
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                }
                ?>
            </div>
        </div>

        <!-- Yaklaşan Tekrarlı Ödemeler -->
        <div class="widget">
            <div class="title">
                <div class="left"><h1>Yaklaşan Tekrarlı Ödemeler</h1></div>
            </div>
            <div class="row">
                <?php
                $recurring_payments = $pia->query("
                    SELECT f.*, c.title as customer_name, cat.title as category_name, s.title as safe_name
                    FROM finances f 
                    LEFT JOIN customers c ON f.customer_id = c.id 
                    LEFT JOIN categories cat ON f.category_id = cat.id 
                    LEFT JOIN safes s ON f.safe_id = s.id 
                    WHERE f.u_id = $admin->id 
                    AND f.is_recurring = 1 
                    AND f.recurring_start_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)
                    ORDER BY f.recurring_start_date ASC
                    LIMIT 5
                ");

                if($recurring_payments) {
                    foreach($recurring_payments as $payment) {
                        $days_until = floor((strtotime($payment->recurring_start_date) - time()) / (60 * 60 * 24));
                        ?>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-title"><?=$payment->description?></div>
                                <div class="card-body">
                                    <strong class="price text-danger">
                                        <?=number_format($payment->price, 2)?> TL
                                    </strong>
                                    <span class="date">
                                        Ödeme Tarihi: <?=date('d.m.Y', strtotime($payment->recurring_start_date))?><br>
                                        <?=$days_until <= 0 ? 'Bugün' : $days_until . ' gün kaldı'?><br>
                                        <?=$payment->category_name?> - <?=$payment->safe_name?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="col-md-12">
                        <div class="alert alert-info-custom">
                            Yaklaşan tekrarlı ödeme bulunmuyor.
                        </div>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <!-- Kategori Bazlı Harcama Analizi -->
<div class="widget">
    <div class="title">
        <div class="left"><h1>Kategori Bazlı Harcama Analizi</h1></div>
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-title">Bu Ay Kategorilere Göre Harcamalar</div>
                <div style="position: relative; height: 300px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-title">En Çok Harcama Yapılan Kategoriler</div>
                <div class="card-body">
                    <?php
                    $top_categories = $pia->query("
                        SELECT 
                            c.title,
                            c.type,
                            SUM(f.price) as total,
                            COUNT(*) as count
                        FROM finances f
                        JOIN categories c ON f.category_id = c.id
                        WHERE f.u_id = $admin->id 
                        AND f.event_type = 0
                        AND MONTH(f.adddate) = MONTH(CURRENT_DATE())
                        AND YEAR(f.adddate) = YEAR(CURRENT_DATE())
                        GROUP BY c.id, c.title, c.type
                        ORDER BY total DESC
                        LIMIT 5
                    ");

                    $chart_data = [];
                    $chart_labels = [];
                    $chart_colors = [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 206, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)'
                    ];

                    $max_total = 0;
                    $categories_data = [];

                    if($top_categories && $top_categories->rowCount() > 0) {
                        foreach($top_categories as $cat) {
                            $categories_data[] = $cat;
                            if($cat->total > $max_total) {
                                $max_total = $cat->total;
                            }
                        }

                        foreach($categories_data as $index => $cat) {
                            ?>
                            <div class="category-item mb-3">
                                <div class="d-flex justify-content-between">
                                    <strong><?=$cat->title?></strong>
                                    <span class="text-danger">
                                        <?=number_format($cat->total, 2)?> TL
                                    </span>
                                </div>
                                <small class="text-muted"><?=$cat->count?> işlem</small>
                                <div class="progress mt-1">
                                    <div class="progress-bar bg-danger" 
                                         style="width: <?=($cat->total/$max_total)*100?>%">
                                    </div>
                                </div>
                            </div>
                            <?php
                            // Chart.js için veri hazırlama
                            $chart_labels[] = $cat->title;
                            $chart_data[] = floatval($cat->total);
                        }
                    } else {
                        echo "<div class='alert alert-info'>Bu ay henüz harcama yapılmamış.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pasta grafik için Chart.js konfigürasyonu
var ctx = document.getElementById('categoryChart').getContext('2d');
var categoryChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: <?=json_encode($chart_labels)?>,
        datasets: [{
            data: <?=json_encode($chart_data)?>,
            backgroundColor: <?=json_encode($chart_colors)?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    font: {
                        size: 12
                    }
                }
            },
            title: {
                display: true,
                text: 'Kategori Dağılımı',
                font: {
                    size: 16
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let value = context.raw;
                        return context.label + ': ' + new Intl.NumberFormat('tr-TR', {
                            style: 'currency',
                            currency: 'TRY'
                        }).format(value);
                    }
                }
            }
        }
    }
});
</script>

<style>
.category-item {
    padding: 10px;
    border-radius: 5px;
    background: #f8f9fa;
}
.progress {
    height: 5px;
    margin-top: 5px;
}
.progress-bar {
    transition: width 0.3s ease;
}
</style>

    <!-- Kategori Detay Tablosu -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-title">Tüm Kategoriler</div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Tip</th>
                                <th>İşlem Sayısı</th>
                                <th>Toplam Tutar</th>
                                <th>Ortalama İşlem</th>
                                <th>Son İşlem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $all_categories = $pia->query("
                                SELECT 
                                    c.title,
                                    c.type,
                                    COUNT(f.id) as transaction_count,
                                    SUM(f.price) as total_amount,
                                    AVG(f.price) as avg_amount,
                                    MAX(f.adddate) as last_transaction
                                FROM categories c
                                LEFT JOIN finances f ON f.category_id = c.id 
                                    AND f.u_id = $admin->id
                                    AND MONTH(f.adddate) = MONTH(CURRENT_DATE())
                                    AND YEAR(f.adddate) = YEAR(CURRENT_DATE())
                                GROUP BY c.id, c.title, c.type
                                ORDER BY total_amount DESC
                            ");

                            foreach($all_categories as $cat) {
                                if($cat->transaction_count > 0) {
                                    ?>
                                    <tr>
                                        <td><?=$cat->title?></td>
                                        <td>
                                            <span class="badge badge-<?=$cat->type == 'income' ? 'success' : 'danger'?>">
                                                <?=$cat->type?>
                                            </span>
                                        </td>
                                        <td><?=$cat->transaction_count?></td>
                                        <td><?=number_format($cat->total_amount, 2)?> TL</td>
                                        <td><?=number_format($cat->avg_amount, 2)?> TL</td>
                                        <td><?=date('d.m.Y H:i', strtotime($cat->last_transaction))?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Pasta grafik için verileri hazırla
<?php
$chart_data = [];
$chart_labels = [];
$chart_colors = [];

// Standart renkler
$default_colors = [
    'rgb(255, 99, 132)',
    'rgb(54, 162, 235)',
    'rgb(255, 206, 86)',
    'rgb(75, 192, 192)',
    'rgb(153, 102, 255)'
];

if($top_categories) {
    $i = 0;
    foreach($top_categories as $cat) {
        $chart_labels[] = $cat->title;
        $chart_data[] = floatval($cat->total);
        $chart_colors[] = $default_colors[$i % count($default_colors)];
        $i++;
    }
}

// Chart.js için veri hazırlama
$chart_config = [
    'labels' => $chart_labels,
    'datasets' => [[
        'data' => $chart_data,
        'backgroundColor' => $chart_colors,
        'borderWidth' => 1
    ]]
];
?>

new Chart(document.getElementById('categoryChart'), {
    type: 'doughnut',
    data: {
        labels: <?=json_encode($chart_labels)?>,
        datasets: [{
            data: <?=json_encode($chart_data)?>,
            backgroundColor: <?=json_encode($chart_colors)?>,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
            },
            title: {
                display: true,
                text: 'Kategori Dağılımı'
            }
        }
    }
});
</script>

<style>
.category-item {
    padding: 10px;
    border-radius: 5px;
    background: #f8f9fa;
}
.progress {
    height: 5px;
    margin-top: 5px;
}
.badge {
    padding: 5px 10px;
}
</style>
<!-- AI Nakit Akışı Tahmini -->
<div class="widget">
    <div class="title">
        <div class="left"><h1>Gelecek 30 Gün Nakit Akışı Tahmini</h1></div>
    </div>
    <div class="row">
        <?php
        // Tekrarlı ödemeleri getir
        $recurring = $pia->get_row("
            SELECT 
                COALESCE(SUM(CASE WHEN event_type = 1 THEN price ELSE 0 END), 0) as recurring_income,
                COALESCE(SUM(CASE WHEN event_type = 0 THEN price ELSE 0 END), 0) as recurring_expense
            FROM finances 
            WHERE u_id = $admin->id 
            AND is_recurring = 1
            AND recurring_start_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
        ");

        // Ortalama günlük işlemleri hesapla
        $daily_averages = $pia->get_row("
            SELECT 
                COALESCE(AVG(CASE WHEN event_type = 1 THEN daily_total ELSE 0 END), 0) as avg_daily_income,
                COALESCE(AVG(CASE WHEN event_type = 0 THEN daily_total ELSE 0 END), 0) as avg_daily_expense
            FROM (
                SELECT 
                    DATE(adddate) as trans_date,
                    event_type,
                    SUM(price) as daily_total
                FROM finances
                WHERE u_id = $admin->id 
                AND adddate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(adddate), event_type
            ) daily_totals
        ");

        // Varsayılan değerleri ayarla
        $recurring_income = isset($recurring->recurring_income) ? $recurring->recurring_income : 0;
        $recurring_expense = isset($recurring->recurring_expense) ? $recurring->recurring_expense : 0;
        $avg_daily_income = isset($daily_averages->avg_daily_income) ? $daily_averages->avg_daily_income : 0;
        $avg_daily_expense = isset($daily_averages->avg_daily_expense) ? $daily_averages->avg_daily_expense : 0;

        // Tahminleri hesapla
        $predicted_income = ($avg_daily_income * 30) + $recurring_income;
        $predicted_expense = ($avg_daily_expense * 30) + $recurring_expense;
        $predicted_balance = $predicted_income - $predicted_expense;

        // Güven skoru hesapla (basit bir örnek)
        $total_predicted = $predicted_income + $predicted_expense;
        $confidence_score = $total_predicted > 0 ? 
            min(($recurring_income + $recurring_expense) / $total_predicted * 100, 100) : 0;
        ?>

        <div class="col-md-4">
            <div class="card">
                <div class="card-title">Tahmini Gelirler</div>
                <div class="card-body">
                    <strong class="price text-success">
                        <?=number_format($predicted_income, 2)?> TL
                    </strong>
                    <span class="date">
                        Tekrarlı: <?=number_format($recurring_income, 2)?> TL<br>
                        Tahmini: <?=number_format($predicted_income - $recurring_income, 2)?> TL
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-title">Tahmini Giderler</div>
                <div class="card-body">
                    <strong class="price text-danger">
                        <?=number_format($predicted_expense, 2)?> TL
                    </strong>
                    <span class="date">
                        Tekrarlı: <?=number_format($recurring_expense, 2)?> TL<br>
                        Tahmini: <?=number_format($predicted_expense - $recurring_expense, 2)?> TL
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-title">Net Nakit Akışı</div>
                <div class="card-body">
                    <strong class="price <?=$predicted_balance >= 0 ? 'text-success' : 'text-danger'?>">
                        <?=number_format($predicted_balance, 2)?> TL
                    </strong>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-info" style="width: <?=$confidence_score?>%">
                            Güven Skoru: %<?=number_format($confidence_score, 1)?>
                        </div>
                    </div>
                    <div class="alert alert-info mt-2">
                        <?php
                        if($predicted_balance >= 0) {
                            echo "Gelecek 30 günde pozitif nakit akışı bekleniyor.";
                        } else {
                            echo "Dikkat! Gelecek 30 günde nakit açığı oluşabilir.";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- AI Anormal Harcama Tespiti -->
<div class="widget">
    <div class="title">
        <div class="left"><h1>Anormal Harcama Tespiti</h1></div>
    </div>
    <div class="row">
        <?php
        // Her kategori için normal harcama aralığını belirle
        $anomalies = $pia->query("
            WITH CategoryStats AS (
                SELECT 
                    category_id,
                    c.title as category_name,
                    COALESCE(AVG(price), 0) as avg_amount,
                    COALESCE(STDDEV(price), 0) as std_dev
                FROM finances f
                JOIN categories c ON f.category_id = c.id
                WHERE f.u_id = $admin->id 
                AND f.event_type = 0
                AND f.adddate >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
                GROUP BY category_id, c.title
                HAVING COUNT(*) >= 1
            )
            SELECT 
                f.*,
                c.title as category_name,
                COALESCE(cs.avg_amount, 0) as avg_amount,
                COALESCE(cs.std_dev, 0) as std_dev,
                CASE 
                    WHEN cs.std_dev = 0 THEN 0 
                    ELSE ((f.price - cs.avg_amount) / cs.std_dev) 
                END as z_score
            FROM finances f
            JOIN categories c ON f.category_id = c.id
            JOIN CategoryStats cs ON f.category_id = cs.category_id
            WHERE f.u_id = $admin->id 
            AND f.event_type = 0
            AND f.adddate >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
            HAVING ABS(z_score) > 0
            ORDER BY f.adddate DESC
            LIMIT 5
        ");

        if($anomalies && $anomalies->rowCount() > 0) {
            foreach($anomalies as $anomaly) {
                $severity = abs($anomaly->z_score);
                $severity_class = $severity > 3 ? 'danger' : 'warning';
                ?>
                <div class="col-md-4">
                    <div class="card border-<?=$severity_class?>">
                        <div class="card-title">
                            Anormal Harcama Tespit Edildi
                        </div>
                        <div class="card-body">
                            <strong class="price text-<?=$severity_class?>">
                                <?=number_format($anomaly->price, 2)?> TL
                            </strong>
                            <span class="date">
                                Kategori: <?=$anomaly->category_name?><br>
                                Tarih: <?=date('d.m.Y', strtotime($anomaly->adddate))?><br>
                                <small>
                                    Normal aralık: 
                                    <?=number_format(max(0, $anomaly->avg_amount - $anomaly->std_dev), 2)?> TL -
                                    <?=number_format($anomaly->avg_amount + $anomaly->std_dev, 2)?> TL
                                </small>
                            </span>
                            <div class="alert alert-<?=$severity_class?> mt-2">
                                <?php
                                if($severity > 0) {
                                    echo "Bu harcama normal ortalamanın " . 
                                         number_format(abs($anomaly->z_score), 1) . 
                                         " standart sapma dışında!";
                                } else {
                                    echo "Bu kategoride henüz yeterli veri yok.";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Anormal harcama tespit edilmedi veya yeterli veri bulunmuyor.
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<!-- AI Tasarruf Önerileri -->
<div class="widget">
    <div class="title">
        <div class="left"><h1>Akıllı Tasarruf Önerileri</h1></div>
    </div>
    <div class="row">
        <?php
        // Kategorilere göre tasarruf potansiyelini analiz et
        $savings_potential = $pia->query("
            WITH MonthlySpending AS (
                SELECT 
                    category_id,
                    c.title as category_name,
                    DATE_FORMAT(adddate, '%Y-%m') as month,
                    COALESCE(SUM(price), 0) as monthly_total
                FROM finances f
                JOIN categories c ON f.category_id = c.id
                WHERE f.u_id = $admin->id 
                AND f.event_type = 0
                AND f.adddate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY category_id, c.title, DATE_FORMAT(adddate, '%Y-%m')
            )
            SELECT 
                category_id,
                category_name,
                COALESCE(AVG(monthly_total), 0) as avg_monthly,
                COALESCE(MIN(monthly_total), 0) as min_monthly,
                COALESCE(MAX(monthly_total), 0) as max_monthly,
                COALESCE(STDDEV(monthly_total), 0) as std_dev
            FROM MonthlySpending
            GROUP BY category_id, category_name
            HAVING COUNT(*) >= 1
            ORDER BY (MAX(monthly_total) - MIN(monthly_total)) DESC
            LIMIT 5
        ");

        if($savings_potential && $savings_potential->rowCount() > 0) {
            foreach($savings_potential as $potential) {
                $savings_amount = $potential->max_monthly - $potential->min_monthly;
                if($savings_amount > 0) {
                    ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-title"><?=$potential->category_name?></div>
                            <div class="card-body">
                                <strong class="price text-success">
                                    <?=number_format($savings_amount, 2)?> TL
                                </strong>
                                <span class="date">
                                    Potansiyel Aylık Tasarruf<br>
                                    <small>
                                        En düşük: <?=number_format($potential->min_monthly, 2)?> TL<br>
                                        Ortalama: <?=number_format($potential->avg_monthly, 2)?> TL<br>
                                        En yüksek: <?=number_format($potential->max_monthly, 2)?> TL
                                    </small>
                                </span>
                                <div class="alert alert-info mt-2">
                                    <?php
                                    if($potential->max_monthly > 0) {
                                        $percentage = ($savings_amount / $potential->max_monthly) * 100;
                                        echo "Bu kategoride harcamalarınızı en düşük seviyeye çekerseniz %" . 
                                             number_format($percentage, 1) . " tasarruf yapabilirsiniz.";
                                    } else {
                                        echo "Bu kategoride henüz yeterli veri yok.";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        } else {
            ?>
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Henüz tasarruf önerisi oluşturmak için yeterli veri bulunmuyor.
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<!-- AI Tahmin Sistemi -->
<div class="widget">
    <div class="title">
        <div class="left"><h1>Yapay Zeka Finansal Öngörüler</h1></div>
    </div>
    <div class="row">
        <?php
        // Geçmiş harcama verilerini analiz et
        $spending_patterns = $pia->query("
            SELECT 
                MONTH(adddate) as month,
                category_id,
                SUM(price) as total
            FROM finances 
            WHERE u_id = $admin->id 
            AND event_type = 0
            AND adddate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY MONTH(adddate), category_id
        ");

        // Basit bir AI tahmini
        $predictions = [];
        foreach($spending_patterns as $pattern) {
            if(!isset($predictions[$pattern->category_id])) {
                $predictions[$pattern->category_id] = [
                    'avg' => 0,
                    'trend' => 0,
                    'count' => 0
                ];
            }
            $predictions[$pattern->category_id]['avg'] += $pattern->total;
            $predictions[$pattern->category_id]['count']++;
        }

        // Tahminleri göster
        foreach($predictions as $cat_id => $pred) {
            $category = $pia->get_row("SELECT title FROM categories WHERE id = ?", [$cat_id]);
            $monthly_avg = $pred['avg'] / $pred['count'];
            ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-title"><?=$category->title?> - Tahmin</div>
                    <div class="card-body">
                        <strong class="price">
                            <?=number_format($monthly_avg, 2)?> TL
                        </strong>
                        <span class="date">
                            Tahmini Aylık Harcama<br>
                            <small>Son 6 aylık veriye dayalı tahmin</small>
                        </span>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<!-- Kasa Performans Analizi -->
<div class="widget">
    <div class="title">
        <div class="left"><h1>Kasa Performans Analizi</h1></div>
    </div>
    <div class="row">
        <?php
        $safes = $pia->query("
            SELECT 
                s.*,
                (
                    SELECT SUM(price) 
                    FROM finances 
                    WHERE safe_id = s.id 
                    AND event_type = 1
                    AND MONTH(adddate) = MONTH(CURRENT_DATE())
                ) as monthly_income,
                (
                    SELECT SUM(price) 
                    FROM finances 
                    WHERE safe_id = s.id 
                    AND event_type = 0
                    AND MONTH(adddate) = MONTH(CURRENT_DATE())
                ) as monthly_expense
            FROM safes s
            WHERE s.u_id = $admin->id 
            AND s.status = 1
        ");

        foreach($safes as $safe) {
            $net_flow = ($safe->monthly_income ?? 0) - ($safe->monthly_expense ?? 0);
            ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-title"><?=$safe->title?></div>
                    <div class="card-body">
                        <strong class="price <?=$net_flow >= 0 ? 'text-success' : 'text-danger'?>">
                            <?=number_format($net_flow, 2)?> TL
                        </strong>
                        <span class="date">
                            Bu Ay Gelir: +<?=number_format($safe->monthly_income ?? 0, 2)?> TL<br>
                            Bu Ay Gider: -<?=number_format($safe->monthly_expense ?? 0, 2)?> TL<br>
                            Mevcut Bakiye: <?=number_format($safe->balance, 2)?> TL
                        </span>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
<!-- Finansal Sağlık Skoru -->
<div class="widget">
    <div class="title">
        <div class="left"><h1>Finansal Sağlık Durumu</h1></div>
    </div>
    <div class="row">
        <?php
        // Çeşitli finansal metrikleri hesapla
        
        // 1. Gelir/Gider Oranı
        $monthly_income = $pia->get_row("
            SELECT COALESCE(SUM(price), 0) as total
            FROM finances 
            WHERE u_id = $admin->id 
            AND event_type = 1
            AND MONTH(adddate) = MONTH(CURRENT_DATE())
        ")->total;

        $monthly_expense = $pia->get_row("
            SELECT COALESCE(SUM(price), 0) as total
            FROM finances 
            WHERE u_id = $admin->id 
            AND event_type = 0
            AND MONTH(adddate) = MONTH(CURRENT_DATE())
        ")->total;

        $income_expense_ratio = $monthly_expense > 0 ? ($monthly_income / $monthly_expense) : 0;
        $income_score = min(($income_expense_ratio * 25), 25);

        // 2. Kredi Kartı Kullanım Oranı
        $total_credit_limit = 0;
        $total_credit_used = 0;
        $credit_cards = $pia->query("
            SELECT credit_limit, balance 
            FROM safes 
            WHERE u_id = $admin->id 
            AND type = 'credit_card'
        ");

        foreach($credit_cards as $card) {
            $total_credit_limit += $card->credit_limit;
            $total_credit_used += ($card->credit_limit - $card->balance);
        }

        $credit_usage_ratio = $total_credit_limit > 0 ? ($total_credit_used / $total_credit_limit) : 0;
        $credit_score = (1 - $credit_usage_ratio) * 25;

        // 3. Tasarruf Oranı
        $savings_ratio = $monthly_income > 0 ? (($monthly_income - $monthly_expense) / $monthly_income) : 0;
        $savings_score = max(min(($savings_ratio * 100), 25), 0);

        // 4. Düzenli Ödeme Performansı
        $payment_performance = $pia->get_row("
            SELECT 
                COUNT(CASE WHEN payment_status = 'completed' THEN 1 END) as completed,
                COUNT(*) as total
            FROM finances 
            WHERE u_id = $admin->id 
            AND is_recurring = 1
            AND adddate >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
        ");

        $payment_ratio = $payment_performance->total > 0 ? 
            ($payment_performance->completed / $payment_performance->total) : 1;
        $payment_score = $payment_ratio * 25;

        // Toplam skor
        $total_score = $income_score + $credit_score + $savings_score + $payment_score;
        
        // Skor rengi
        $score_color = $total_score >= 80 ? 'success' : 
                      ($total_score >= 60 ? 'warning' : 'danger');
        ?>

        <div class="col-md-4">
            <div class="card">
                <div class="card-title">Finansal Sağlık Skoru</div>
                <div class="card-body text-center">
                    <div class="display-4 text-<?=$score_color?>">
                        <?=number_format($total_score, 1)?>
                    </div>
                    <div class="progress mt-3">
                        <div class="progress-bar bg-<?=$score_color?>" 
                             style="width: <?=$total_score?>%">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-title">Skor Detayları</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Gelir/Gider Dengesi</strong>
                            <div class="progress mb-3">
                                <div class="progress-bar" style="width: <?=$income_score*4?>%">
                                    <?=number_format($income_score, 1)?>/25
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong>Kredi Kartı Kullanımı</strong>
                            <div class="progress mb-3">
                                <div class="progress-bar" style="width: <?=$credit_score*4?>%">
                                    <?=number_format($credit_score, 1)?>/25
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong>Tasarruf Oranı</strong>
                            <div class="progress mb-3">
                                <div class="progress-bar" style="width: <?=$savings_score*4?>%">
                                    <?=number_format($savings_score, 1)?>/25
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <strong>Ödeme Performansı</strong>
                            <div class="progress mb-3">
                                <div class="progress-bar" style="width: <?=$payment_score*4?>%">
                                    <?=number_format($payment_score, 1)?>/25
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <h5>Öneriler</h5>
                        <ul>
                            <?php if($income_score < 20): ?>
                                <li>Gelir/gider dengenizi iyileştirmeye çalışın.</li>
                            <?php endif; ?>
                            
                            <?php if($credit_score < 20): ?>
                                <li>Kredi kartı kullanımınızı azaltmayı düşünün.</li>
                            <?php endif; ?>
                            
                            <?php if($savings_score < 20): ?>
                                <li>Tasarruf oranınızı artırmaya çalışın.</li>
                            <?php endif; ?>
                            
                            <?php if($payment_score < 20): ?>
                                <li>Ödemelerinizi düzenli yapmaya özen gösterin.</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Gelir-Gider Grafiği -->
<div class="widget">
    <div class="title">
        <div class="left"><h1>Aylık Gelir-Gider Analizi</h1></div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
<?php
// Son 6 ayın verilerini çek
$months = [];
$incomes = [];
$expenses = [];

for($i = 5; $i >= 0; $i--) {
    $date = date('Y-m', strtotime("-$i months"));
    $months[] = date('F Y', strtotime($date));
    
    $income = $pia->get_row("
        SELECT COALESCE(SUM(price), 0) as total 
        FROM finances 
        WHERE u_id = $admin->id 
        AND event_type = 1 
        AND DATE_FORMAT(adddate, '%Y-%m') = ?", 
        [$date]
    )->total;
    
    $expense = $pia->get_row("
        SELECT COALESCE(SUM(price), 0) as total 
        FROM finances 
        WHERE u_id = $admin->id 
        AND event_type = 0 
        AND DATE_FORMAT(adddate, '%Y-%m') = ?", 
        [$date]
    )->total;
    
    $incomes[] = $income;
    $expenses[] = $expense;
}
?>

new Chart(document.getElementById('incomeExpenseChart'), {
    type: 'line',
    data: {
        labels: <?=json_encode($months)?>,
        datasets: [{
            label: 'Gelirler',
            data: <?=json_encode($incomes)?>,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }, {
            label: 'Giderler',
            data: <?=json_encode($expenses)?>,
            borderColor: 'rgb(255, 99, 132)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            title: {
                display: true,
                text: 'Aylık Gelir-Gider Grafiği'
            }
        }
    }
});
</script>
        <!-- Nakit Akış Tahmini -->
<div class="widget">
    <div class="title">
        <div class="left"><h1>30 Günlük Nakit Akış Tahmini</h1></div>
    </div>
    <div class="row">
        <?php
        // Gelecek 30 günlük tahminler
        $expected_income = $pia->get_row("
            SELECT COALESCE(SUM(price), 0) as total 
            FROM finances 
            WHERE u_id = $admin->id 
            AND event_type = 1 
            AND (
                (is_recurring = 1 AND recurring_start_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY))
                OR
                (is_recurring = 0 AND adddate BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY))
            )
        ")->total;

        $expected_expense = $pia->get_row("
            SELECT COALESCE(SUM(price), 0) as total 
            FROM finances 
            WHERE u_id = $admin->id 
            AND event_type = 0 
            AND (
                (is_recurring = 1 AND recurring_start_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY))
                OR
                (is_recurring = 0 AND adddate BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY))
            )
        ")->total;

        // Bekleyen ödemeleri de ekle
        $pending_payments = $pia->get_row("
            SELECT COALESCE(SUM(amount), 0) as total
            FROM pending_payments
            WHERE u_id = $admin->id 
            AND status = 'pending'
            AND due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
        ")->total;

        $expected_expense += $pending_payments;

        // Son 30 günün ortalamasını al
        $last_30_days_avg = $pia->get_row("
            SELECT 
                COALESCE(AVG(CASE WHEN event_type = 1 THEN daily_total ELSE 0 END), 0) as avg_income,
                COALESCE(AVG(CASE WHEN event_type = 0 THEN daily_total ELSE 0 END), 0) as avg_expense
            FROM (
                SELECT 
                    DATE(adddate) as date,
                    event_type,
                    SUM(price) as daily_total
                FROM finances
                WHERE u_id = $admin->id 
                AND adddate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY DATE(adddate), event_type
            ) as daily_totals
        ");

        // Tahminleri hesapla
        $predicted_income = $expected_income + ($last_30_days_avg->avg_income * 30);
        $predicted_expense = $expected_expense + ($last_30_days_avg->avg_expense * 30);
        $net_flow = $predicted_income - $predicted_expense;

        // Debug için
        /*
        echo "<pre>";
        echo "Beklenen Gelir: " . $expected_income . "\n";
        echo "Beklenen Gider: " . $expected_expense . "\n";
        echo "Bekleyen Ödemeler: " . $pending_payments . "\n";
        echo "Ortalama Günlük Gelir: " . $last_30_days_avg->avg_income . "\n";
        echo "Ortalama Günlük Gider: " . $last_30_days_avg->avg_expense . "\n";
        echo "</pre>";
        */
        ?>

        <div class="col-md-4">
            <div class="card">
                <div class="card-title">Beklenen Gelirler</div>
                <div class="card-body">
                    <strong class="price text-success">
                        +<?=number_format($predicted_income, 2)?> TL
                    </strong>
                    <span class="date">
                        Kesin: <?=number_format($expected_income, 2)?> TL<br>
                        Tahmini: <?=number_format($predicted_income - $expected_income, 2)?> TL
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-title">Beklenen Giderler</div>
                <div class="card-body">
                    <strong class="price text-danger">
                        -<?=number_format($predicted_expense, 2)?> TL
                    </strong>
                    <span class="date">
                        Kesin: <?=number_format($expected_expense, 2)?> TL<br>
                        Tahmini: <?=number_format($predicted_expense - $expected_expense, 2)?> TL
                    </span>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-title">Net Nakit Akışı</div>
                <div class="card-body">
                    <strong class="price <?=$net_flow >= 0 ? 'text-success' : 'text-danger'?>">
                        <?=($net_flow >= 0 ? '+' : '')?><?=number_format($net_flow, 2)?> TL
                    </strong>
                    <div class="progress mt-2">
                        <div class="progress-bar <?=$net_flow >= 0 ? 'bg-success' : 'bg-danger'?>" 
                             style="width: <?=min(abs($net_flow / max($predicted_income, $predicted_expense) * 100), 100)?>%">
                        </div>
                    </div>
                    <div class="alert <?=$net_flow >= 0 ? 'alert-success' : 'alert-warning'?> mt-2">
                        <?php if($net_flow >= 0): ?>
                            <i class="fas fa-check-circle"></i> Pozitif nakit akışı bekleniyor
                        <?php else: ?>
                            <i class="fas fa-exclamation-triangle"></i> Dikkat! Nakit açığı oluşabilir
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    </section>
    <?php require('_inc/footer.php'); ?>
</body>
</html>