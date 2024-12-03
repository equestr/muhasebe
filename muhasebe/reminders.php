<?php 
require('_class/config.php'); 
require('session.php');   
?>
<!DOCTYPE html>
<html>
<head>
    <?php require('_inc/head.php'); ?>
    <title>Hatırlatmalar</title>
</head>
<body>
    <?php require('_inc/header.php'); ?>
    <section class="content">
        <div class="title">
            <div class="left">
                <h1>Hatırlatmalar</h1>
                <ul class="breadcrumb">
                    <li><a href="main.php">Anasayfa</a></li>
                    <li><a href="reminders.php">Hatırlatmalar</a></li>
                </ul>
            </div>
            <div class="right"></div>
        </div>

        <?php 
        // Düzenleme modalı
        if(isset($_GET['edit'])){ 
            $edit_id = $pia->control($_GET['edit'],'int');
            
            // Hatırlatma ve ilişkili tüm verileri tek sorguda al
            $edit_row = $pia->get_row("SELECT r.*, 
                f.event_type, f.price, f.description as finance_description, f.adddate,
                c.title as customer_name,
                cat.title as category_name,
                s.title as safe_name,
                s.type as safe_type
                FROM reminders r
                LEFT JOIN finances f ON r.finance_id = f.id
                LEFT JOIN customers c ON f.customer_id = c.id
                LEFT JOIN categories cat ON f.category_id = cat.id
                LEFT JOIN safes s ON f.safe_id = s.id
                WHERE r.id = $edit_id");

            if($edit_row) {
        ?>
            <div class="modal active">
                <form class="panel-default modal-medium" method="post" action="_ajax/_ajaxEditReminders.php" onsubmit="return updateReminder(this);">
                    <div class="panel-title">
                        <i class="fal fa-pen"></i> Düzenle
                        <a class="panel-close" href="reminders.php"><i class="fal fa-times"></i></a>
                    </div>
                    <div class="panel-body">
                        <!-- İlişkili işlem bilgileri -->
                        <?php if(!empty($edit_row->customer_name)){ ?>
                        <div class="form-group">
                            <strong>Cari : </strong>
                            <?=$edit_row->customer_name?>
                        </div>
                        <?php } ?>

                        <?php if(!empty($edit_row->category_name)){ ?>
                        <div class="form-group">
                            <strong>Kategori : </strong>
                            <?=$edit_row->category_name?>
                        </div>
                        <?php } ?>

                        <div class="form-group">
                            <strong>Tutar : </strong>
                            <span class="<?=$edit_row->event_type==0 ? 'text-danger' : 'text-success'?>">
                                <?=$edit_row->event_type==0 ? '-' : '+'?><?=number_format($edit_row->price, 2)?> TL
                            </span>
                        </div>

                        <?php if(!empty($edit_row->safe_name)){ ?>
                        <div class="form-group">
                            <strong>Ödeme : </strong>
                            <?=$edit_row->safe_type == 'credit_card' ? 'Kredi Kartı' : 'Nakit'?> 
                            (<?=$edit_row->safe_name?>)
                        </div>
                        <?php } ?>

                        <!-- Hatırlatma bilgileri -->
                        <div class="form-group" style="border-top:1px solid #EEE;padding-top:20px;">
                            <label>Hatırlatma Notu</label>
                            <textarea name="description" class="form-control" required><?=$edit_row->description?></textarea>
                        </div>

                        <div class="form-group">
                            <label>Hatırlatma Tarihi</label>
                            <input type="date" name="reminder_date" class="form-control" 
                                value="<?=date('Y-m-d', strtotime($edit_row->reminder_date))?>" required>
                        </div>

                        <div class="form-group">
                            <label>Durum</label>
                            <select name="status" class="form-control">
                                <option value="0" <?=$edit_row->status==0?'selected':''?>>Hatırlatma bekliyor</option>
                                <option value="1" <?=$edit_row->status==1?'selected':''?>>Hatırlatma yapıldı</option>
                            </select>
                        </div>

                        <button type="submit" class="btn-primary btn-block">Kaydet</button>
                    </div>
                    <input type="hidden" name="edit_id" value="<?=$edit_row->id?>"/>
                    <input type="hidden" name="frm" value="frmEditReminders"/>
                </form>
            </div>
        <?php 
            }
        } 
        ?>

        <!-- Liste tablosu -->
        <table class="table">
            <thead>
                <tr>
                    <th width="50px">ID</th>
                    <th>İşlem Özeti</th>
                    <th>Hatırlatma Notu</th>
                    <th>Hatırlatma Tarihi</th>
                    <th>Durum</th>
                    <th width="150px">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $list = $pia->query("SELECT r.*, 
                    f.event_type, f.price, f.id as finance_id,
                    c.title as customer_name,
                    cat.title as category_name,
                    s.title as safe_name,
                    s.type as safe_type
                    FROM reminders r
                    LEFT JOIN finances f ON r.finance_id = f.id
                    LEFT JOIN customers c ON f.customer_id = c.id
                    LEFT JOIN categories cat ON f.category_id = cat.id
                    LEFT JOIN safes s ON f.safe_id = s.id
                    ORDER BY r.reminder_date DESC");

                foreach($list as $item){ 
                ?>
                    <tr>
                        <td><?=$item->id?></td>
                        <td>
                            <?php if(!empty($item->customer_name)){ ?>
                                <?=$item->customer_name?> - 
                                <?=$item->category_name?><br>
                                <span class="<?=$item->event_type==0 ? 'text-danger' : 'text-success'?>">
                                    <?=$item->event_type==0 ? '-' : '+'?><?=number_format($item->price, 2)?> TL
                                </span>
                            <?php } ?>
                        </td>
                        <td><?=nl2br($item->description)?></td>
                        <td><?=date('d.m.Y', strtotime($item->reminder_date))?></td>
                        <td>
                            <?=$item->status==1 ? 
                                '<label class="label-success">Hatırlatma yapıldı</label>' : 
                                '<label class="label-info">Hatırlatma bekliyor</label>'?>
                        </td>
                        <td>
                            <a href="reminders.php?edit=<?=$item->id?>" class="btn-default btn-xs">Düzenle</a>
                            <a href="javascript:void(0)" onclick="deleteReminder(<?=$item->id?>)" 
                               class="btn-default btn-xs">Sil</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

    <?php require('_inc/footer.php'); ?>

    <script>
    function updateReminder(form) {
        $.ajax({
            type: 'POST',
            url: form.action,
            data: $(form).serialize(),
            success: function(response) {
                if(response.includes('success')) {
                    window.location.href = 'reminders.php';
                } else {
                    alert('Bir hata oluştu: ' + response);
                }
            }
        });
        return false;
    }

   // Önce JavaScript fonksiyonunu değiştirelim
function deleteReminder(id) {
    if(confirm('Bu hatırlatmayı silmek istediğinizden emin misiniz?')) {
        $.post('_ajax/_ajaxDelete.php', {
            table: 'reminders',
            value: id  // 'id' yerine 'value' kullanıyoruz
        }, function(response) {
            if(response.includes('success')) {  // response kontrolünü değiştirdik
                location.reload();
            } else {
                alert('Silme işlemi başarısız!');
            }
        });
    }
}
    </script>
</body>
</html>