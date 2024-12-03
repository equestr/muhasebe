<?php require('_class/config.php'); require('session.php');   ?>
<!DOCTYPE html>
<html>
      <head>
            <?php require('_inc/head.php'); ?>
            <title>Giderler</title>
            
      </head>
      <body>
            <?php require('_inc/header.php'); ?>
            <section class="content">
                  

                   <div class="title">
                        <div class="left">
                              <h1>Giderler</h1>
                              <ul class="breadcrumb">
                                    <li><a href="main.php">Anasayfa</a></li>
                                    <li><a href="expense.php">Giderler</a></li>
                              </ul>
                        </div>
                        <div class="right">
                              <a href="expense.php?add" class="btn-success btn-sm">Yeni ekle</a>
                        </div>
                  </div>

<?php if(isset($_GET['add'])){ ?>
<div class="modal active">
    <form method="post" action="_ajax/_ajaxAddExpense.php" class="panel-default modal-medium" id="frmAddExpense" onsubmit="return false">
        <div class="panel-title"><i class="fal fa-plus-circle"></i> Yeni Gider Ekle</div> 
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cariler</label>
                        <select name="customer_id" class="form-control selectpicker" data-live-search="true" data-size="5" data-title="Lütfen seçiniz">
                            <?php $list = $pia->query("SELECT * FROM customers ORDER BY title ASC"); foreach($list as $item){ ?>
                            <option value="<?=$item->id;?>"><?=$item->title;?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kategoriler</label>
                        <select name="category_id" class="form-control">
                            <?php $list = $pia->query("SELECT * FROM categories WHERE type='expense' ORDER BY title ASC"); foreach($list as $item){ ?>
                            <option value="<?=$item->id;?>"><?=$item->title;?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Tutar</label>
                        <input type="text" name="price" class="form-control" />
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kasa Seçimi</label>
                        <select name="safe_id" class="form-control" required>
                            <option value="">Kasa Seçiniz</option>
                            <?php 
                            $safes = $pia->query("SELECT * FROM safes WHERE u_id = $admin->id AND status = 1 ORDER BY title ASC");
                            foreach($safes as $safe){
                                $type_text = array(
                                    'cash' => 'Nakit',
                                    'credit_card' => 'Kredi Kartı',
                                    'bank_account' => 'Banka Hesabı',
                                    'check' => 'Çek Hesabı'
                                );
                                $shown_type = isset($type_text[$safe->type]) ? $type_text[$safe->type] : $safe->type;

                                if($safe->type == 'credit_card') {
                                    $used_amount = $safe->credit_limit - $safe->balance;
                                    $available = $safe->credit_limit - $used_amount;
                                    echo '<option value="'.$safe->id.'">'.$safe->title.' ('.$shown_type.' - Limit: '.number_format($available, 2).' TL)</option>';
                                } else {
                                    echo '<option value="'.$safe->id.'">'.$safe->title.' ('.$shown_type.' - Bakiye: '.number_format($safe->balance, 2).' TL)</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label>Notlar</label>
                <textarea name="description" class="form-control"></textarea>
            </div>
            <div class="form-group">
                <label>Tarih&Saat</label>
                <input type="datetime-local" name="adddate" class="form-control" value="<?=date('Y-m-d H:i:s');?>" />
            </div>

            <div class="form-group">
                <label>Sabit Gider mi?</label>
                <select name="is_recurring" id="is_recurring" class="form-control" onchange="toggleRecurringDetails(this.value)">
                    <option value="0">Hayır</option>
                    <option value="1">Evet</option>
                </select>
            </div>

            <div id="recurring_details" style="display:none;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Başlangıç Tarihi</label>
                            <input type="date" name="recurring_start_date" class="form-control" value="<?=date('Y-m-d');?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tekrar Sıklığı (Ay)</label>
                            <input type="number" name="recurring_months" class="form-control" min="1" value="1">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Vade (Kaç Kez Tekrarlansın?)</label>
                    <input type="number" name="recurring_count" class="form-control" min="1" value="12">
                </div>
            </div>

            <button type="button" onclick="saveForm()" class="btn-primary">Kaydet</button>
            <a href="expense.php" class="btn-danger">Vazgeç</a>
        </div>
        <input type="hidden" name="frm" value="frmAddExpense" />
    </form>
</div>


                        
                  <?php }elseif(isset($_GET['edit'])){ 
    $edit_id = $pia->control($_GET['edit'],'int');
    $edit_row = $pia->get_row("SELECT f.*, 
        c.title as customer_name,
        cat.title as category_name,
        s.title as safe_name,
        s.type as safe_type,
        s.id as safe_id
        FROM finances f 
        LEFT JOIN customers c ON f.customer_id = c.id 
        LEFT JOIN categories cat ON f.category_id = cat.id 
        LEFT JOIN safes s ON f.safe_id = s.id 
        WHERE f.id = $edit_id");
?>
    <div class="modal active">
        <form method="post" action="_ajax/_ajaxEditExpense.php" class="panel-default modal-medium">
            <div class="panel-title"><i class="fal fa-pen"></i> Düzenle</div> 
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cariler</label>
                            <select name="customer_id" class="form-control selectpicker" data-live-search="true" data-size="5" data-title="Lütfen seçiniz">
                                <?php 
                                $list = $pia->query("SELECT * FROM customers ORDER BY title ASC"); 
                                foreach($list as $item){  
                                ?>
                                <option value="<?=$item->id;?>" <?=$edit_row->customer_id==$item->id?'selected':'';?>><?=$item->title;?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kategoriler</label>
                            <select name="category_id" class="form-control">
                                <?php 
                                $list = $pia->query("SELECT * FROM categories WHERE type='expense' ORDER BY title ASC"); 
                                foreach($list as $item){  
                                ?>
                                <option value="<?=$item->id;?>" <?=$edit_row->category_id==$item->id?'selected':'';?>><?=$item->title;?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tutar</label>
                            <input type="text" name="price" class="form-control" value="<?=number_format($edit_row->price, 2, '.', '');?>" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kasa Seçimi</label>
                            <select name="safe_id" class="form-control" required>
                                <option value="">Kasa Seçiniz</option>
                                <?php 
                                $safes = $pia->query("SELECT * FROM safes WHERE u_id = $admin->id AND status = 1 ORDER BY title ASC");
                                foreach($safes as $safe){
                                    if($safe->type == 'credit_card') {
                                        $used_amount = $safe->credit_limit - $safe->balance;
                                        $available = $safe->credit_limit - $used_amount;
                                        echo '<option value="'.$safe->id.'" '.($edit_row->safe_id==$safe->id?'selected':'').'>'.$safe->title.' (Limit: '.number_format($available, 2).' TL)</option>';
                                    } else {
                                        echo '<option value="'.$safe->id.'" '.($edit_row->safe_id==$safe->id?'selected':'').'>'.$safe->title.' (Bakiye: '.number_format($safe->balance, 2).' TL)</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Açıklama</label>
                    <textarea name="description" class="form-control" rows="3"><?=$edit_row->description;?></textarea>
                </div>

                <div class="form-group">
                    <label>Tarih & Saat</label>
                    <input type="datetime-local" name="adddate" class="form-control" 
                        value="<?=date('Y-m-d\TH:i', strtotime($edit_row->adddate));?>" />
                </div>

                <div class="form-group">
                    <label>Sabit Gider mi?</label>
                    <select name="is_recurring" id="is_recurring" class="form-control" onchange="toggleRecurringDetails(this.value)">
                        <option value="0" <?=$edit_row->is_recurring==0?'selected':'';?>>Hayır</option>
                        <option value="1" <?=$edit_row->is_recurring==1?'selected':'';?>>Evet</option>
                    </select>
                </div>

                <div id="recurring_details" style="display:<?=$edit_row->is_recurring==1?'block':'none';?>;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Başlangıç Tarihi</label>
                                <input type="date" name="recurring_start_date" class="form-control" 
                                    value="<?=$edit_row->recurring_start_date;?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tekrar Sıklığı (Ay)</label>
                                <input type="number" name="recurring_months" class="form-control" min="1" 
                                    value="<?=$edit_row->recurring_months;?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Vade (Kaç Kez Tekrarlansın?)</label>
                        <input type="number" name="recurring_count" class="form-control" min="1" 
                            value="<?=$edit_row->recurring_count;?>">
                    </div>
                </div>

                <button type="button" onclick="updateForm()" class="btn-primary">Kaydet</button>
                <a href="expense.php" class="btn-danger">Vazgeç</a>
            </div>
            <input type="hidden" name="edit_id" value="<?=$edit_row->id;?>" />
            <input type="hidden" name="frm" value="frmEditExpense" />
        </form>
    </div>

    <script>
    function toggleRecurringDetails(value) {
        if(value == '1') {
            document.getElementById('recurring_details').style.display = 'block';
        } else {
            document.getElementById('recurring_details').style.display = 'none';
        }
    }
    </script>
                 
              <?php }elseif(isset($_GET['view_id'])){ 
    $edit_id = $pia->control($_GET['view_id'],'int');
    $edit_row = $pia->get_row("SELECT f.*, 
        c.title as customer_name,
        cat.title as category_name,
        s.title as safe_name,
        s.type as safe_type,
        s.balance as safe_balance,
        f.description as description
        FROM finances f 
        LEFT JOIN customers c ON f.customer_id = c.id 
        LEFT JOIN categories cat ON f.category_id = cat.id 
        LEFT JOIN safes s ON f.safe_id = s.id 
        WHERE f.id = $edit_id");
?>
    <div class="modal active">
        <div class="panel-default modal-medium">
            <div class="panel-title">#<?=$edit_row->id;?> numaralı işlem özeti</div>
            <a class="panel-close" href="expense.php"><i class="fal fa-times"></i></a>
            <div class="panel-body">
                <div class="form-group">
                    <strong>Cari adı : </strong>
                    <?=$edit_row->customer_name;?>
                </div>
                <div class="form-group">
                    <strong>Kategoriler : </strong>
                    <?=$edit_row->category_name;?>
                </div>
                <div class="form-group">
                    <strong>Tutar : </strong>
                    <span class="text-danger">-<?=number_format($edit_row->price, 2);?> TL</span>
                </div>
                <div class="form-group">
                    <strong>Ödeme Yöntemi : </strong>
                    <?php 
                    $type_text = array(
                        'cash' => 'Nakit',
                        'credit_card' => 'Kredi Kartı',
                        'bank_account' => 'Banka Hesabı',
                        'check' => 'Çek Hesabı'
                    );
                    $shown_type = isset($type_text[$edit_row->safe_type]) ? $type_text[$edit_row->safe_type] : $edit_row->safe_type;
                    echo $shown_type . ' ('.$edit_row->safe_name.')';
                    ?>
                </div>

                <div class="form-group">
                    <strong>Açıklama : </strong>
                    <?=!empty($edit_row->description) ? nl2br($edit_row->description) : 'Açıklama girilmemiş';?>
                </div>
                
                <div class="form-group">
                    <strong>Sabit Gider : </strong>
                    <?=$edit_row->is_recurring==1?'Evet':'Hayır';?>
                </div>
                
                <?php if($edit_row->is_recurring==1){ ?>
                    <div class="form-group">
                        <strong>Başlangıç Tarihi : </strong>
                        <?php 
                        if(!empty($edit_row->recurring_start_date) && $edit_row->recurring_start_date != '0000-00-00') {
                            echo $pia->time_tr($edit_row->recurring_start_date);
                        } else {
                            echo date('d.m.Y', strtotime($edit_row->adddate));
                        }
                        ?>
                    </div>
                    <div class="form-group">
                        <strong>Tekrar Sıklığı : </strong>
                        <?=$edit_row->recurring_months;?> Ay
                    </div>
                    <div class="form-group">
                        <strong>Vade : </strong>
                        <?=$edit_row->recurring_count;?> Kez
                    </div>
                <?php } ?>
                
                <div class="form-group m0">
                    <strong>Tarih : </strong>
                    <?=$pia->time_tr($edit_row->adddate);?>
                </div>

                <div class="form-group" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                    <strong>İşlem Detayları : </strong><br>
                    <small>
                        İşlem ID: #<?=$edit_row->id;?><br>
                        Oluşturulma Tarihi: <?=$pia->time_tr($edit_row->adddate);?><br>
                        <?php if($edit_row->is_recurring==1): ?>
                        Tekrarlanan Ödeme: Evet (<?=$edit_row->recurring_months;?> ay süreyle, <?=$edit_row->recurring_count;?> kez)<br>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

                 
              <?php }elseif(isset($_GET['edit'])){ 
    $edit_id = $pia->control($_GET['edit'],'int');
    $edit_row = $pia->get_row("SELECT f.*, 
        c.title as customer_name,
        cat.title as category_name,
        s.title as safe_name,
        s.type as safe_type,
        s.id as safe_id
        FROM finances f 
        LEFT JOIN customers c ON f.customer_id = c.id 
        LEFT JOIN categories cat ON f.category_id = cat.id 
        LEFT JOIN safes s ON f.safe_id = s.id 
        WHERE f.id = $edit_id");
?>
    <div class="modal active">
        <form method="post" action="_ajax/_ajaxEditExpense.php" class="panel-default modal-medium">
            <div class="panel-title"><i class="fal fa-pen"></i> Düzenle</div> 
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cariler</label>
                            <select name="customer_id" class="form-control selectpicker" data-live-search="true" data-size="5" data-title="Lütfen seçiniz">
                                <?php 
                                $list = $pia->query("SELECT * FROM customers ORDER BY title ASC"); 
                                foreach($list as $item){  
                                ?>
                                <option value="<?=$item->id;?>" <?=$edit_row->customer_id==$item->id?'selected':'';?>><?=$item->title;?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kategoriler</label>
                            <select name="category_id" class="form-control">
                                <?php 
                                $list = $pia->query("SELECT * FROM categories WHERE type='expense' ORDER BY title ASC"); 
                                foreach($list as $item){  
                                ?>
                                <option value="<?=$item->id;?>" <?=$edit_row->category_id==$item->id?'selected':'';?>><?=$item->title;?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tutar</label>
                            <input type="text" name="price" class="form-control" value="<?=number_format($edit_row->price, 2, '.', '');?>" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kasa Seçimi</label>
                            <select name="safe_id" class="form-control" required>
                                <option value="">Kasa Seçiniz</option>
                                <?php 
                                $safes = $pia->query("SELECT * FROM safes WHERE u_id = $admin->id AND status = 1 ORDER BY title ASC");
                                foreach($safes as $safe){
                                    $type_text = array(
                                        'cash' => 'Nakit',
                                        'credit_card' => 'Kredi Kartı',
                                        'bank_account' => 'Banka Hesabı',
                                        'check' => 'Çek Hesabı'
                                    );
                                    $shown_type = isset($type_text[$safe->type]) ? $type_text[$safe->type] : $safe->type;

                                    if($safe->type == 'credit_card') {
                                        $used_amount = $safe->credit_limit - $safe->balance;
                                        $available = $safe->credit_limit - $used_amount;
                                        echo '<option value="'.$safe->id.'" '.($edit_row->safe_id==$safe->id?'selected':'').'>'.$safe->title.' ('.$shown_type.' - Limit: '.number_format($available, 2).' TL)</option>';
                                    } else {
                                        echo '<option value="'.$safe->id.'" '.($edit_row->safe_id==$safe->id?'selected':'').'>'.$safe->title.' ('.$shown_type.' - Bakiye: '.number_format($safe->balance, 2).' TL)</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Açıklama</label>
                    <textarea name="description" class="form-control" rows="3"><?=$edit_row->description;?></textarea>
                </div>

                <div class="form-group">
                    <label>Tarih & Saat</label>
                    <input type="datetime-local" name="adddate" class="form-control" 
                        value="<?=date('Y-m-d\TH:i', strtotime($edit_row->adddate));?>" />
                </div>

                <div class="form-group">
                    <label>Sabit Gider mi?</label>
                    <select name="is_recurring" id="is_recurring" class="form-control" onchange="toggleRecurringDetails(this.value)">
                        <option value="0" <?=$edit_row->is_recurring==0?'selected':'';?>>Hayır</option>
                        <option value="1" <?=$edit_row->is_recurring==1?'selected':'';?>>Evet</option>
                    </select>
                </div>

                <div id="recurring_details" style="display:<?=$edit_row->is_recurring==1?'block':'none';?>;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Başlangıç Tarihi</label>
                                <input type="date" name="recurring_start_date" class="form-control" 
                                    value="<?=$edit_row->recurring_start_date;?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tekrar Sıklığı (Ay)</label>
                                <input type="number" name="recurring_months" class="form-control" min="1" 
                                    value="<?=$edit_row->recurring_months;?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Vade (Kaç Kez Tekrarlansın?)</label>
                        <input type="number" name="recurring_count" class="form-control" min="1" 
                            value="<?=$edit_row->recurring_count;?>">
                    </div>
                </div>

                <button type="button" onclick="updateForm()" class="btn-primary">Kaydet</button>
                <a href="expense.php" class="btn-danger">Vazgeç</a>
            </div>
            <input type="hidden" name="edit_id" value="<?=$edit_row->id;?>" />
            <input type="hidden" name="frm" value="frmEditExpense" />
        </form>
    </div>
<?php } ?>

                       <table class="table">
    <thead>
        <tr>
            <th width="50px">ID</th>
            <th>Cari adı</th>
            <th>Kategoriler</th>
            <th>Tutar</th>
            <th>Ödeme Y.</th>
            <th>Sabit Gider</th>
            <th width="150px">Tarih</th>
            <th width="150px">İşlemler</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        // YENİ SORGU
        $list = $pia->query("SELECT f.*, 
            c.title as customer_name, 
            cat.title as category_name,
            s.title as safe_name,
            s.type as safe_type 
            FROM finances f 
            LEFT JOIN customers c ON f.customer_id = c.id 
            LEFT JOIN categories cat ON f.category_id = cat.id 
            LEFT JOIN safes s ON f.safe_id = s.id 
            WHERE f.event_type = 0 AND f.u_id = $admin->id 
            ORDER BY f.adddate DESC");

        foreach($list as $item){ ?>
            <tr>
                <td><?=$item->id;?></td>
                <td><?=$item->customer_name;?></td>
                <td><?=$item->category_name;?></td>
                <td class="text-danger">-<?=number_format($item->price, 2);?> TL</td>
                <td>
    <?php 
    $type_text = array(
        'cash' => 'Nakit',
        'credit_card' => 'Kredi Kartı',
        'bank_account' => 'Banka Hesabı',
        'check' => 'Çek Hesabı'
    );
    $shown_type = isset($type_text[$item->safe_type]) ? $type_text[$item->safe_type] : $item->safe_type;
    echo $shown_type . ' ('.$item->safe_name.')';
    ?>
</td>
                <td><?=$item->is_recurring==1?'Evet':'Hayır';?></td>
                <td><?=$pia->time_tr($item->adddate);?></td>
                <td>
                    <a href="expense.php?add_reminder=<?=$item->id;?>" class="btn-warning btn-xs">Hatırlatma ekle</a>
                    <a href="expense.php?view_id=<?=$item->id;?>" class="btn-default btn-xs">İncele</a>
                    <a href="expense.php?edit=<?=$item->id;?>" class="btn-default btn-xs">Düzenle</a>
                    <a onclick="_delete('finances', <?=$item->id;?>);" class="btn-default btn-xs">Sil</a>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
                  </section>
<script>
// Yeni ekleme formu için
function saveForm() {
    var formData = $('#frmAddExpense').serialize();
    
    $.post('_ajax/_ajaxAddExpense.php', formData, function(response) {
        if(response.includes('success')) {
            window.location.href = 'expense.php';
        } else {
            alert(response);
        }
    });
}

// Düzenleme formu için
function updateForm() {
    var formData = $('form[action="_ajax/_ajaxEditExpense.php"]').serialize();
    
    $.post('_ajax/_ajaxEditExpense.php', formData, function(response) {
        if(response.includes('success')) {
            window.location.href = 'expense.php';
        } else {
            alert(response.replace('error:', 'Hata: '));
        }
    });
}

// Sabit gider alanlarını göster/gizle
function toggleRecurringDetails(value) {
    if(value == '1') {
        document.getElementById('recurring_details').style.display = 'block';
    } else {
        document.getElementById('recurring_details').style.display = 'none';
    }
}

function _delete(table, id) {
    if(confirm('Bu kaydı silmek istediğinizden emin misiniz?')) {
        $.post('_ajax/_ajaxDelete.php', {
            table: table,
            id: id
        }, function(response) {
            if(response == 'success') {
                location.reload();
            }
        });
    }
}

</script>
<?php require('_inc/footer.php'); ?>
</body>
</html>