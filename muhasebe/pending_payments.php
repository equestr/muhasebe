<?php 
require('_class/config.php'); 
require('session.php');   
?>
<!DOCTYPE html>
<html>
<head>
    <?php require('_inc/head.php'); ?>
    <title>Bekleyen Ödemeler</title>
</head>
<body>
    <?php require('_inc/header.php'); ?>
    <section class="content">
        <div class="title">
            <div class="left">
                <h1>Bekleyen Ödemeler</h1>
                <ul class="breadcrumb">
                    <li><a href="main.php">Anasayfa</a></li>
                    <li><a href="pending_payments.php">Bekleyen Ödemeler</a></li>
                </ul>
            </div>
        </div>

        <div class="panel-default">
            <div class="panel-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Kasa</th>
                            <th>Cari</th>
                            <th>Kategori</th>
                            <th>Tutar</th>
                            <th>Ödeme Tarihi</th>
                            <th>Durum</th>
                            <th>Oluşturma Tarihi</th>
                            <th width="150">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $list = $pia->query("SELECT 
                            p.*,
                            s.title as safe_title,
                            c.title as customer_title,
                            cat.title as category_title
                        FROM pending_payments p
                        LEFT JOIN safes s ON s.id = p.safe_id
                        LEFT JOIN customers c ON c.id = p.customer_id
                        LEFT JOIN categories cat ON cat.id = p.category_id
                        WHERE p.u_id = $admin->id
                        ORDER BY p.due_date ASC");

                        foreach($list as $item){ 
                            // Durum sınıfını belirle
                            $status_class = '';
                            $status_text = '';
                            switch($item->status) {
                                case 'pending':
                                    $status_class = 'warning';
                                    $status_text = 'Bekliyor';
                                    break;
                                case 'completed':
                                    $status_class = 'success';
                                    $status_text = 'Tamamlandı';
                                    break;
                                case 'cancelled':
                                    $status_class = 'danger';
                                    $status_text = 'İptal Edildi';
                                    break;
                            }
                        ?>
                        <tr>
                            <td><?=$item->id;?></td>
                            <td><?=$item->safe_title;?></td>
                            <td><?=$item->customer_title;?></td>
                            <td><?=$item->category_title;?></td>
                            <td><?=number_format($item->amount, 2);?> TL</td>
                            <td><?=date('d.m.Y H:i', strtotime($item->due_date));?></td>
                            <td><span class="badge badge-<?=$status_class;?>"><?=$status_text;?></span></td>
                            <td><?=date('d.m.Y H:i', strtotime($item->created_at));?></td>
                            <td>
                                <?php if($item->status == 'pending'){ ?>
                                    <button onclick="processPayment(<?=$item->id;?>)" class="btn-success btn-xs">
                                        Öde
                                    </button>
                                    <button onclick="cancelPayment(<?=$item->id;?>)" class="btn-danger btn-xs">
                                        İptal
                                    </button>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <script>
    function processPayment(id) {
        if(confirm('Bu ödemeyi gerçekleştirmek istediğinize emin misiniz?')) {
            $.ajax({
                url: '_ajax/_ajaxProcessPendingPayment.php',
                type: 'POST',
                data: {
                    payment_id: id,
                    action: 'process'
                },
                success: function(response) {
                    if(response.includes('success')) {
                        window.location.reload();
                    } else {
                        alert(response.replace('error:', ''));
                    }
                }
            });
        }
    }

    function cancelPayment(id) {
        if(confirm('Bu ödemeyi iptal etmek istediğinize emin misiniz?')) {
            $.ajax({
                url: '_ajax/_ajaxProcessPendingPayment.php',
                type: 'POST',
                data: {
                    payment_id: id,
                    action: 'cancel'
                },
                success: function(response) {
                    if(response.includes('success')) {
                        window.location.reload();
                    } else {
                        alert(response.replace('error:', ''));
                    }
                }
            });
        }
    }
    </script>

    <?php require('_inc/footer.php'); ?>
</body>
</html>