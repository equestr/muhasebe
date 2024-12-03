<?php require('_class/config.php'); require('session.php');  ?>
<!DOCTYPE html>
<html>
      <head>
            <?php require('_inc/head.php'); ?>
            <title>Anasayfa</title>
            <style>
                .widget { margin-bottom:50px; }  
                .widget .title h1 { font-size:20px; } 
                .widget .title { margin-bottom:20px; display:flex; justify-content:space-between; align-items:center; }

                .card { display: block; position: relative; background:#FFF; padding:30px; margin-bottom:30px; border-radius: 4px; overflow: hidden; }
                .card .card-title { display:block; font-size:13px; font-weight: 600; color: #A1A5B7; margin-bottom:15px; }
                .price { display:block; font-size: 1.50em; font-weight: 900; color:#666666; }
                .date { display:block; color:#A1A5B7; font-size: 1em; margin-top:5px; }
            </style>
      </head>
      <body>
            <?php require('_inc/header.php'); ?>
            <section class="content">
                
                
                <!-- Kasa Durumları -->
                <div class="widget">
                    <div class="title">
                        <div class="left"><h1>Kasa Durumları</h1></div>
                        <div class="right">
                            <a href="/muhasebe/safes.php" class="btn-primary btn-sm">Tüm Kasalar</a>
                        </div>
                    </div>
                    <div class="row">
                        <?php 
                        $safes = $pia->query("SELECT * FROM safes WHERE u_id = $admin->id AND status = 1");
                        foreach($safes as $safe){ 
                        ?>
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="card">
                                <div class="card-title"><?=$safe->title;?></div>
                                <div class="card-body">
                                    <?php if($safe->type == 'credit_card'): 
                                        $used_amount = $safe->credit_limit - $safe->balance;
                                    ?>
                                        <strong class="price text-danger">
                                            <?=number_format($used_amount, 2);?> TL
                                        </strong>
                                        <span class="date">Kalan Limit: <?=number_format($safe->balance, 2);?> TL</span>
                                    <?php else: ?>
                                        <strong class="price text-success">
                                            <?=number_format($safe->balance, 2);?> TL
                                        </strong>
                                        <span class="date">Güncel Bakiye</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Gelirler -->
                <div class="widget">
                    <div class="title">
                        <div class="left"><h1>Son Gelirler</h1></div>
                        <div class="right">
                            <a href="/muhasebe/income.php" class="btn-success btn-sm">Tümünü Gör</a>
                        </div>
                    </div>
                    <table class="table" data-ordering="false" data-paging="false" data-searching="false" data-info="false">
                        <thead>
                            <tr>
                                <th>Cari adı</th>
                                <th>Kategori</th>
                                <th>Tutar</th>
                                <th>Kasa</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $list = $pia->query("SELECT f.*, c.title as customer_name, 
                                cat.title as category_name, s.title as safe_name 
                                FROM finances f 
                                LEFT JOIN customers c ON f.customer_id = c.id 
                                LEFT JOIN categories cat ON f.category_id = cat.id 
                                LEFT JOIN safes s ON f.safe_id = s.id 
                                WHERE f.event_type = 1 AND f.u_id = $admin->id 
                                ORDER BY f.adddate DESC LIMIT 3");  
                            foreach($list as $item){ 
                            ?>    
                            <tr>
                                <td><?=$item->customer_name;?></td>
                                <td><?=$item->category_name;?></td>
                                <td class="text-success">+<?=number_format($item->price, 2);?> TL</td>
                                <td><?=$item->safe_name;?></td>
                                <td><?=$pia->time_tr($item->adddate);?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
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
                ?>
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="card">
                        <div class="card-title"><?=$payment->description?></div>
                        <div class="card-body">
                            <strong class="price text-danger">
                                <?=number_format($payment->price, 2)?> TL
                            </strong>
                            <span class="date">
                                Ödeme Tarihi: <?=date('d.m.Y', strtotime($payment->recurring_start_date))?><br>
                                Kategori: <?=$payment->category_name?><br>
                                Kasa: <?=$payment->safe_name?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center">
                        Yaklaşan tekrarlı ödeme bulunmuyor.
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
                
                <!-- Kredi Kartı Bildirimleri -->
<div class="widget">
    <div class="title">
        <div class="left"><h1>Kredi Kartı Bildirimleri</h1></div>
    </div>
    <div class="row">
        <?php 
        // Kredi kartlarını getir
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

        $notification_count = 0;

        if($credit_cards) {
            foreach($credit_cards as $card) {
                $used_amount = $card->credit_limit - $card->balance;
                
                // Hesap kesim ve son ödeme tarihlerini hesapla
                $statement_date = date('Y-m-d', strtotime(date('Y-m-') . $card->statement_day));
                if(date('d') > $card->statement_day) {
                    $statement_date = date('Y-m-d', strtotime($statement_date . ' +1 month'));
                }
                
                $due_date = date('Y-m-d', strtotime(date('Y-m-') . $card->due_day));
                if(date('d') > $card->due_day) {
                    $due_date = date('Y-m-d', strtotime($due_date . ' +1 month'));
                }

                if($used_amount > 0) { // Sadece borcu olan kartları göster
                    $notification_count++;
                    ?>
                    <div class="col-md-4 col-sm-4 col-xs-12">
                        <div class="card">
                            <div class="card-title"><?=$card->title?></div>
                            <div class="card-body">
                                <strong class="price text-danger">
                                    <?=number_format($used_amount, 2)?> TL
                                </strong>
                                <span class="date">
                                    <?php
                                    if($card->days_to_statement <= 5 && $card->days_to_statement >= 0) {
                                        // Hesap kesim tarihi yaklaşanlar
                                        echo "Hesap kesim tarihine {$card->days_to_statement} gün kaldı<br>";
                                        echo "Hesap Kesim: " . date('d.m.Y', strtotime($statement_date));
                                    } 
                                    elseif($card->days_to_statement < 0 && $card->days_to_due >= 0) {
                                        // Hesap kesimi yapılmış, ödeme bekleyenler
                                        echo "Son ödeme tarihine {$card->days_to_due} gün kaldı<br>";
                                        echo "Son Ödeme: " . date('d.m.Y', strtotime($due_date));
                                    }
                                    elseif($card->days_to_due < 0) {
                                        // Son ödeme tarihi geçmiş olanlar
                                        echo "<span class='text-danger'>Son ödeme tarihi " . abs($card->days_to_due) . " gün geçti!</span><br>";
                                        echo "Son Ödeme: " . date('d.m.Y', strtotime($due_date));
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        }
        
        if($notification_count == 0) {
            ?>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center">
                        Aktif kredi kartı bildirimi bulunmuyor.
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>
                <!-- Giderler -->
                <div class="widget">
                    <div class="title">
                        <div class="left"><h1>Son Giderler</h1></div>
                        <div class="right">
                            <a href="/muhasebe/expense.php" class="btn-danger btn-sm">Tümünü Gör</a>
                        </div>
                    </div>
                    <table class="table" data-ordering="false" data-paging="false" data-searching="false" data-info="false">
                        <thead>
                            <tr>
                                <th>Cari adı</th>
                                <th>Kategori</th>
                                <th>Tutar</th>
                                <th>Kasa</th>
                                <th>Tarih</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $list = $pia->query("SELECT f.*, c.title as customer_name, 
                                cat.title as category_name, s.title as safe_name 
                                FROM finances f 
                                LEFT JOIN customers c ON f.customer_id = c.id 
                                LEFT JOIN categories cat ON f.category_id = cat.id 
                                LEFT JOIN safes s ON f.safe_id = s.id 
                                WHERE f.event_type = 0 AND f.u_id = $admin->id 
                                ORDER BY f.adddate DESC LIMIT 3");  
                            foreach($list as $item){ 
                            ?>    
                            <tr>
                                <td><?=$item->customer_name;?></td>
                                <td><?=$item->category_name;?></td>
                                <td class="text-danger">-<?=number_format($item->price, 2);?> TL</td>
                                <td><?=$item->safe_name;?></td>
                                <td><?=$pia->time_tr($item->adddate);?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Raporlar -->
                <div class="widget">
                    <div class="title">
                        <div class="left"><h1>Raporlar</h1></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="card">
                                <div class="card-title">BUGÜN</div>
                                <div class="card-body">
                                    <?php 
                                    $today_income = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 1 
                                        AND DATE(adddate) = CURDATE()")->total ?? 0;
                                    $today_expense = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 0 
                                        AND DATE(adddate) = CURDATE()")->total ?? 0;
                                    $today_total = $today_income - $today_expense;
                                    ?>
                                    <strong class="price <?=$today_total >= 0 ? 'text-success' : 'text-danger';?>">
                                        <?=number_format($today_total, 2);?> TL
                                    </strong>
                                    <span class="date">
                                        Gelir: +<?=number_format($today_income, 2);?> TL<br>
                                        Gider: -<?=number_format($today_expense, 2);?> TL
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="card">
                                <div class="card-title">DÜN</div>
                                <div class="card-body">
                                    <?php 
                                    $yesterday_income = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 1 
                                        AND DATE(adddate) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)")->total ?? 0;
                                    $yesterday_expense = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 0 
                                        AND DATE(adddate) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)")->total ?? 0;
                                    $yesterday_total = $yesterday_income - $yesterday_expense;
                                    ?>
                                    <strong class="price <?=$yesterday_total >= 0 ? 'text-success' : 'text-danger';?>">
                                        <?=number_format($yesterday_total, 2);?> TL
                                    </strong>
                                    <span class="date">
                                        Gelir: +<?=number_format($yesterday_income, 2);?> TL<br>
                                        Gider: -<?=number_format($yesterday_expense, 2);?> TL
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="card">
                                <div class="card-title">SON 15 GÜN</div>
                                <div class="card-body">
                                    <?php 
                                    $last15_income = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 1 
                                        AND adddate >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)")->total ?? 0;
                                    $last15_expense = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 0 
                                        AND adddate >= DATE_SUB(CURDATE(), INTERVAL 15 DAY)")->total ?? 0;
                                    $last15_total = $last15_income - $last15_expense;
                                    ?>
                                    <strong class="price <?=$last15_total >= 0 ? 'text-success' : 'text-danger';?>">
                                        <?=number_format($last15_total, 2);?> TL
                                    </strong>
                                    <span class="date">
                                        Gelir: +<?=number_format($last15_income, 2);?> TL<br>
                                        Gider: -<?=number_format($last15_expense, 2);?> TL<br>
                                        <?=$pia->time_tr(date('Y-m-d', strtotime('-15 days')),'date');?> - 
                                        <?=$pia->time_tr(date('Y-m-d'),'date');?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="card">
                                <div class="card-title">BU HAFTA</div>
                                <div class="card-body">
                                    <?php 
                                    $week_income = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 1 
                                        AND YEARWEEK(adddate) = YEARWEEK(NOW())")->total ?? 0;
                                    $week_expense = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 0 
                                        AND YEARWEEK(adddate) = YEARWEEK(NOW())")->total ?? 0;
                                    $week_total = $week_income - $week_expense;
                                    ?>
                                    <strong class="price <?=$week_total >= 0 ? 'text-success' : 'text-danger';?>">
                                        <?=number_format($week_total, 2);?> TL
                                    </strong>
                                    <span class="date">
                                        Gelir: +<?=number_format($week_income, 2);?> TL<br>
                                        Gider: -<?=number_format($week_expense, 2);?> TL<br>
                                        <?=date('d', strtotime('monday this week'));?> - 
                                        <?=$pia->time_tr(date('Y-m-d', strtotime('sunday this week')),'date');?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="card">
                                <div class="card-title">BU AY</div>
                                <div class="card-body">
                                    <?php 
                                    $month_income = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 1 
                                        AND MONTH(adddate) = MONTH(CURRENT_DATE())
                                        AND YEAR(adddate) = YEAR(CURRENT_DATE())")->total ?? 0;
                                    $month_expense = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 0 
                                        AND MONTH(adddate) = MONTH(CURRENT_DATE())
                                        AND YEAR(adddate) = YEAR(CURRENT_DATE())")->total ?? 0;
                                    $month_total = $month_income - $month_expense;
                                    ?>
                                    <strong class="price <?=$month_total >= 0 ? 'text-success' : 'text-danger';?>">
                                        <?=number_format($month_total, 2);?> TL
                                    </strong>
                                    <span class="date">
                                        Gelir: +<?=number_format($month_income, 2);?> TL<br>
                                        Gider: -<?=number_format($month_expense, 2);?> TL<br>
                                        <?=$pia->time_tr(date('Y-m'),'month');?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 col-sm-4 col-xs-12">
                            <div class="card">
                                <div class="card-title">BU YIL</div>
                                <div class="card-body">
                                    <?php 
                                    $year_income = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 1 
                                        AND YEAR(adddate) = YEAR(CURRENT_DATE())")->total ?? 0;
                                    $year_expense = $pia->get_row("SELECT SUM(price) as total FROM finances 
                                        WHERE u_id = $admin->id AND event_type = 0 
                                        AND YEAR(adddate) = YEAR(CURRENT_DATE())")->total ?? 0;
                                    $year_total = $year_income - $year_expense;
                                    ?>
                                    <strong class="price <?=$year_total >= 0 ? 'text-success' : 'text-danger';?>">
                                        <?=number_format($year_total, 2);?> TL
                                    </strong>
                                    <span class="date">
                                        Gelir: +<?=number_format($year_income, 2);?> TL<br>
                                        Gider: -<?=number_format($year_expense, 2);?> TL<br>
                                        <?=date('Y');?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </section>
            <?php require('_inc/footer.php'); ?>
      </body>
</html>