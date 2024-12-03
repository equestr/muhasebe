<?php 
require('_class/config.php'); 
require('session.php');   

// Debug için
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html>
<head>
    <?php require('_inc/head.php'); ?>
    <title>Kasa Hareketleri</title>
    <!-- jQuery ve DataTables -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <style>
        .mb-3 { margin-bottom: 1rem; }
        .text-right { text-align: right; }
        .summary-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .summary-box .title {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .mt-4 { margin-top: 2rem; }
.analysis-box {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 5px;
    padding: 20px;
    margin-bottom: 20px;
}
.analysis-box h4 {
    margin-top: 0;
    margin-bottom: 20px;
    color: #333;
}
.chart-container {
    position: relative;
    height: 300px;
}
    </style>
</head>
<body>
    <?php require('_inc/header.php'); ?>
    <section class="content">
        <?php 
        $safe_id = intval($_GET['safe_id']); // Güvenlik için intval ekledik
        $safe = $pia->get_row("SELECT * FROM safes WHERE id = $safe_id AND u_id = $admin->id");
        if(!$safe) die("Kasa bulunamadı!");

        // Kredi kartı için hesap kesim ve son ödeme tarihi hesaplama
        if($safe->type == 'credit_card') {
            $current_month = date('Y-m');
            $statement_day = str_pad($safe->statement_day, 2, '0', STR_PAD_LEFT);
            $due_day = str_pad($safe->due_day, 2, '0', STR_PAD_LEFT);
            
            $statement_date = date('Y-m-d', strtotime($current_month . '-' . $statement_day));
            $due_date = date('Y-m-d', strtotime($current_month . '-' . $due_day));
            
            if(date('d') > $safe->statement_day) {
                $statement_date = date('Y-m-d', strtotime($statement_date . ' +1 month'));
                $due_date = date('Y-m-d', strtotime($due_date . ' +1 month'));
            }
            
            $prev_statement_date = date('Y-m-d', strtotime($statement_date . ' -1 month'));
        }
        ?>

        <div class="title">
            <div class="left">
                <h1><?=$safe->title;?> - Hesap Hareketleri</h1>
                <ul class="breadcrumb">
                    <li><a href="main.php">Anasayfa</a></li>
                    <li><a href="safes.php">Kasalar</a></li>
                    <li>Hesap Hareketleri</li>
                </ul>
            </div>
            <div class="right">
                <span class="badge <?=$safe->type == 'credit_card' ? 'badge-danger' : 'badge-success';?>">
                    <?php
                    if($safe->type == 'credit_card') {
                        $used_amount = $safe->credit_limit - $safe->balance;
                        echo 'Kalan Borç: ' . number_format($used_amount, 2) . ' TL';
                    } else {
                        echo 'Güncel Bakiye: ' . number_format($safe->balance, 2) . ' TL';
                    }
                    ?>
                </span>
            </div>
        </div>

        <?php if($safe->type == 'credit_card'): ?>
        <!-- Kredi Kartı Özet Bilgileri -->
        <div class="panel-default mb-3">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="summary-box">
                            <div class="title">Kart Limiti</div>
                            <div><?=number_format($safe->credit_limit, 2)?> TL</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-box">
                            <div class="title">Kullanılabilir Limit</div>
                            <div><?=number_format($safe->balance, 2)?> TL</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-box">
                            <div class="title">Hesap Kesim Tarihi</div>
                            <div><?=date('d.m.Y', strtotime($statement_date))?></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="summary-box">
                            <div class="title">Son Ödeme Tarihi</div>
                            <div><?=date('d.m.Y', strtotime($due_date))?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
<!-- Ekstre Dönemi Seçimi -->
<div class="panel-default">
    <div class="panel-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label>Ekstre Dönemi</label>
                    <select id="statement_period" class="form-control">
                        <?php
                        for($i = 0; $i < 12; $i++) {
                            $period_start = date('Y-m-d', strtotime($statement_date . " -$i month -1 month"));
                            $period_end = date('Y-m-d', strtotime($statement_date . " -$i month"));
                            echo "<option value='$period_start,$period_end'>" . 
                                 date('d.m.Y', strtotime($period_start)) . " - " . 
                                 date('d.m.Y', strtotime($period_end)) . "</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>İşlem Tipi</label>
                    <select id="event_type" class="form-control">
                        <option value="">Tümü</option>
                        <option value="1">Ödeme</option>
                        <option value="0">Harcama</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" onclick="filterTransactions()" class="btn btn-primary btn-block">Filtrele</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function filterTransactions() {
    var period = document.getElementById('statement_period').value;
    var eventType = document.getElementById('event_type').value;
    var currentUrl = window.location.href.split('?')[0];
    
    var url = currentUrl + '?safe_id=<?=$safe_id?>';
    if(period) url += '&statement_period=' + period;
    if(eventType) url += '&event_type=' + eventType;
    
    window.location.href = url;
}
</script>
        <?php else: ?>
        <!-- Normal Kasa Filtreleme -->
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Başlangıç Tarihi</label>
            <input type="date" id="start_date" class="form-control" value="<?=date('Y-m-01')?>">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Bitiş Tarihi</label>
            <input type="date" id="end_date" class="form-control" value="<?=date('Y-m-t')?>">
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>İşlem Tipi</label>
            <select id="event_type" class="form-control">
                <option value="">Tümü</option>
                <option value="1">Gelir</option>
                <option value="0">Gider</option>
            </select>
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>&nbsp;</label>
            <button type="button" onclick="filterTransactions()" class="btn btn-primary btn-block">Filtrele</button>
        </div>
    </div>
</div>

<script>
function filterTransactions() {
    var startDate = document.getElementById('start_date').value;
    var endDate = document.getElementById('end_date').value;
    var eventType = document.getElementById('event_type').value;
    var currentUrl = window.location.href.split('?')[0];
    
    var url = currentUrl + '?safe_id=<?=$safe_id?>';
    if(startDate) url += '&start_date=' + startDate;
    if(endDate) url += '&end_date=' + endDate;
    if(eventType) url += '&event_type=' + eventType;
    
    window.location.href = url;
}
</script>
        <?php endif; ?>

        <!-- İşlem Listesi -->
        <table class="table">
            <thead>
                <tr>
                    <th>Tarih</th>
                    <th>İşlem Tipi</th>
                    <th>Cari</th>
                    <th>Kategori</th>
                    <th>Açıklama</th>
                    <th>Tutar</th>
                    <?php if($safe->type != 'credit_card'): ?>
                    <th>Bakiye</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                // Filtreleme koşulları
$where = "WHERE safe_id = $safe_id";

if($safe->type == 'credit_card' && !empty($_GET['statement_period'])) {
    list($start_date, $end_date) = explode(',', $_GET['statement_period']);
    $where .= " AND DATE(adddate) BETWEEN '$start_date' AND '$end_date'";
} elseif(!empty($_GET['start_date']) && !empty($_GET['end_date'])) {
    $where .= " AND DATE(adddate) BETWEEN '{$_GET['start_date']}' AND '{$_GET['end_date']}'";
}

if(isset($_GET['event_type']) && $_GET['event_type'] !== '') {
    $where .= " AND event_type = " . intval($_GET['event_type']);
}

                // İşlemleri listele
                $running_balance = 0;
                $total_debit = 0;
                $total_credit = 0;

                $transactions = $pia->query("SELECT f.*, 
                    c.title as customer_name, 
                    cat.title as category_name 
                    FROM finances f 
                    LEFT JOIN customers c ON f.customer_id = c.id 
                    LEFT JOIN categories cat ON f.category_id = cat.id 
                    $where 
                    ORDER BY f.adddate ASC");

                if($transactions) {
                    foreach($transactions as $t) {
                        if($t->event_type == 1) {
                            $running_balance += $t->price;
                            $total_credit += $t->price;
                            $amount_class = 'text-success';
                            $amount_prefix = '+';
                        } else {
                            $running_balance -= $t->price;
                            $total_debit += $t->price;
                            $amount_class = 'text-danger';
                            $amount_prefix = '-';
                        }
                ?>
                <tr>
                    <td><?=date('d.m.Y H:i', strtotime($t->adddate));?></td>
                    <td><?=$t->event_type == 1 ? ($safe->type == 'credit_card' ? 'Ödeme' : 'Gelir') : ($safe->type == 'credit_card' ? 'Harcama' : 'Gider');?></td>
                    <td><?=$t->customer_name;?></td>
                    <td><?=$t->category_name;?></td>
                    <td><?=$t->description;?></td>
                    <td class="<?=$amount_class;?>"><?=$amount_prefix . number_format($t->price, 2);?> TL</td>
                    <?php if($safe->type != 'credit_card'): ?>
                    <td><?=number_format($running_balance, 2);?> TL</td>
                    <?php endif; ?>
                </tr>
                <?php 
                    }
                }
                ?>
            </tbody>
            <tfoot>
                <?php if($safe->type == 'credit_card'): ?>
                    <tr>
                        <td colspan="5" class="text-right"><strong>Toplam Harcama:</strong></td>
                        <td class="text-danger">-<?=number_format($total_debit, 2)?> TL</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right"><strong>Toplam Ödeme:</strong></td>
                        <td class="text-success">+<?=number_format($total_credit, 2)?> TL</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right"><strong>Ekstre Tutarı:</strong></td>
                        <td class="<?=$total_debit > $total_credit ? 'text-danger' : 'text-success'?>">
                            <?=number_format(abs($total_debit - $total_credit), 2)?> TL
                        </td>
                    </tr>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-right"><strong>Toplam Gider:</strong></td>
                        <td class="text-danger">-<?=number_format($total_debit, 2)?> TL</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right"><strong>Toplam Gelir:</strong></td>
                        <td class="text-success">+<?=number_format($total_credit, 2)?> TL</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-right"><strong>Net Bakiye:</strong></td>
                        <td class="<?=$total_credit > $total_debit ? 'text-success' : 'text-danger'?>">
                            <?=($total_credit > $total_debit ? '+' : '-')?><?=number_format(abs($total_credit - $total_debit), 2)?> TL
                        </td>
                        <td></td>
                    </tr>
                <?php endif; ?>
            </tfoot>
        </table>
        
        <!-- Harcama Analizi -->
<div class="panel-default mt-4">
    <div class="panel-heading">
        <h3 class="panel-title">Harcama Analizi</h3>
    </div>
    <div class="panel-body">
        <?php
        // Kategori bazlı harcama analizi için SQL sorgusu
        $category_sql = "SELECT 
            cat.title as category_name,
            SUM(f.price) as total_amount,
            COUNT(*) as transaction_count
            FROM finances f 
            LEFT JOIN categories cat ON f.category_id = cat.id 
            WHERE f.safe_id = " . intval($safe_id) . "
            AND f.event_type = 0 "; // Sadece harcamalar

        // Tarih filtrelerini ekle
        if($safe->type == 'credit_card' && isset($_GET['statement_period'])) {
            list($start_date, $end_date) = explode(',', $_GET['statement_period']);
            $category_sql .= " AND DATE(f.adddate) BETWEEN '" . date('Y-m-d', strtotime($start_date)) . 
                         "' AND '" . date('Y-m-d', strtotime($end_date)) . "'";
        } elseif(isset($_GET['start_date']) && isset($_GET['end_date'])) {
            $start_date = date('Y-m-d', strtotime($_GET['start_date']));
            $end_date = date('Y-m-d', strtotime($_GET['end_date']));
            $category_sql .= " AND DATE(f.adddate) BETWEEN '$start_date' AND '$end_date'";
        }

        $category_sql .= " GROUP BY cat.id, cat.title ORDER BY total_amount DESC";

        // Debug için SQL'i göster
        // echo "<pre>$category_sql</pre>";

        $category_results = $pia->query($category_sql);
        
        $category_expenses = [];
        $total_expenses = 0;

        if($category_results) {
            foreach($category_results as $row) {
                $category = $row->category_name ?: 'Kategorisiz';
                $category_expenses[$category] = $row->total_amount;
                $total_expenses += $row->total_amount;
            }
        }
        ?>

        <div class="row">
            <div class="col-md-6">
                <div class="analysis-box">
                    <h4>Kategori Bazlı Harcama Dağılımı</h4>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Kategori</th>
                                    <th>Tutar</th>
                                    <th>Oran</th>
                                    <th>Grafik</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if(!empty($category_expenses)):
                                    foreach($category_expenses as $category => $amount): 
                                        $percentage = ($amount / $total_expenses) * 100;
                                ?>
                                <tr>
                                    <td><?=$category?></td>
                                    <td><?=number_format($amount, 2)?> TL</td>
                                    <td><?=number_format($percentage, 1)?>%</td>
                                    <td>
                                        <div class="progress" style="margin-bottom: 0;">
                                            <div class="progress-bar bg-info" role="progressbar" 
                                                 style="width: <?=$percentage?>%"></div>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
                                    endforeach;
                                else:
                                ?>
                                <tr>
                                    <td colspan="4" class="text-center">Seçili dönemde harcama bulunmamaktadır.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="analysis-box">
                    <h4>Görsel Dağılım</h4>
                    <div style="height: 300px;">
                        <canvas id="expenseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // Aylık trend analizi için SQL sorgusu
        $trend_sql = "SELECT 
            DATE_FORMAT(adddate, '%Y-%m') as month,
            SUM(price) as total_amount
            FROM finances 
            WHERE safe_id = " . intval($safe_id) . "
            AND event_type = 0
            AND adddate >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(adddate, '%Y-%m')
            ORDER BY month ASC";

        $trend_results = $pia->query($trend_sql);
        
        $monthly_expenses = [];
        $labels = [];
        
        // Son 6 ayı hazırla
        for($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $labels[] = date('F Y', strtotime($month));
            $monthly_expenses[$month] = 0;
        }

        // Verileri doldur
        if($trend_results) {
            foreach($trend_results as $row) {
                if(isset($monthly_expenses[$row->month])) {
                    $monthly_expenses[$row->month] = $row->total_amount;
                }
            }
        }
        ?>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="analysis-box">
                    <h4>Aylık Harcama Trendi</h4>
                    <div style="height: 300px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js kütüphanesini ekleyin -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Pasta grafik
const ctxPie = document.getElementById('expenseChart').getContext('2d');
new Chart(ctxPie, {
    type: 'pie',
    data: {
        labels: <?=json_encode(array_keys($category_expenses))?>,
        datasets: [{
            data: <?=json_encode(array_values($category_expenses))?>,
            backgroundColor: [
                '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                '#9966FF', '#FF9F40', '#00CC99', '#FF99CC'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'right'
            }
        }
    }
});

// Çizgi grafik
const ctxLine = document.getElementById('trendChart').getContext('2d');
new Chart(ctxLine, {
    type: 'line',
    data: {
        labels: <?=json_encode($labels)?>,
        datasets: [{
            label: 'Aylık Harcama',
            data: <?=json_encode(array_values($monthly_expenses))?>,
            borderColor: '#36A2EB',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return value.toLocaleString('tr-TR', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + ' TL';
                    }
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});
</script>
        <script>
// Sayfa yüklendiğinde URL'den değerleri al ve form elemanlarına set et
window.onload = function() {
    var urlParams = new URLSearchParams(window.location.search);
    
    if(document.getElementById('statement_period')) {
        var period = urlParams.get('statement_period');
        if(period) document.getElementById('statement_period').value = period;
    }
    
    if(document.getElementById('start_date')) {
        var startDate = urlParams.get('start_date');
        if(startDate) document.getElementById('start_date').value = startDate;
    }
    
    if(document.getElementById('end_date')) {
        var endDate = urlParams.get('end_date');
        if(endDate) document.getElementById('end_date').value = endDate;
    }
    
    if(document.getElementById('event_type')) {
        var eventType = urlParams.get('event_type');
        if(eventType) document.getElementById('event_type').value = eventType;
    }
}
</script>
    </section>
    <?php require('_inc/footer.php'); ?>
</body>
</html>