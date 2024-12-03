<?php require('_class/config.php'); require('session.php');   ?>
<!DOCTYPE html>
<html>
      <head>
            <?php require('_inc/head.php'); ?>
            <title>Gelirler</title>

      </head>
      <body>
            <?php require('_inc/header.php'); ?>


            <section class="content">

                   <div class="title">
                        <div class="left">
                              <h1>Gelirler</h1>
                              <ul class="breadcrumb">
                                    <li><a href="main.php">Anasayfa</a></li>
                                    <li><a href="income.php">Gelirler</a></li>
                              </ul>
                        </div>
                        <div class="right">

                              <a href="income.php?add" class="btn-success btn-sm">Yeni ekle</a>
                        </div>
                  </div>




                  <?php if(isset($_GET['add'])){ ?>
<div class="modal active">
    <form method="post" action="_ajax/_ajaxAddIncome.php" class="panel-default modal-medium" id="frmAddIncome">
        <div class="panel-title">
            <i class="fal fa-plus-circle"></i> Yeni Gelir Ekle
            <a class="panel-close" href="income.php"><i class="fal fa-times"></i></a>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Cariler</label>
                        <select name="customer_id" class="form-control selectpicker" data-live-search="true" data-size="5" data-title="Lütfen seçiniz">
                            <?php $list = $pia->query("SELECT * FROM customers ORDER BY title ASC"); 
                            foreach($list as $item){ ?>
                                <option value="<?=$item->id;?>"><?=$item->title;?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kategoriler</label>
                        <select name="category_id" class="form-control">
                            <?php $list = $pia->query("SELECT * FROM categories WHERE type='income' ORDER BY title ASC"); 
                            foreach($list as $item){ ?>
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
                        <input type="text" name="price" class="form-control" required />
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
                                    echo '<option value="'.$safe->id.'">'.$safe->title.' ('.$shown_type.' - Limit: '.number_format($safe->credit_limit, 2).' TL)</option>';
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
            <button type="button" onclick="saveForm()" class="btn-primary">Kaydet</button>
            <a href="income.php" class="btn-danger">Vazgeç</a>
        </div>
        <input type="hidden" name="frm" value="frmAddIncome" />
    </form>
</div>

                                    
<?php }elseif(isset($_GET['edit'])){ 
    $edit_id = $pia->control($_GET['edit'],'int');
    $edit_row = $pia->get_row("SELECT f.*, 
        c.title as customer_name,
        cat.title as category_name,
        s.title as safe_name,
        s.type as safe_type,
        f.price,
        f.description,
        f.adddate,
        f.safe_id,
        f.customer_id,
        f.category_id
        FROM finances f 
        LEFT JOIN customers c ON f.customer_id = c.id 
        LEFT JOIN categories cat ON f.category_id = cat.id 
        LEFT JOIN safes s ON f.safe_id = s.id 
        WHERE f.id = $edit_id");
?>    
<div class="modal active">
    <div class="panel-default modal-medium">
        <div class="panel-title">
            <i class="fal fa-pen"></i> Düzenle
            <a class="panel-close" href="income.php"><i class="fal fa-times"></i></a>
        </div>
        <div class="panel-body">
            <form method="post" action="_ajax/_ajaxEditIncome.php" id="frmEditIncome">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Cariler</label>
                            <select name="customer_id" class="form-control selectpicker" data-live-search="true" data-size="5">
                                <?php $list = $pia->query("SELECT * FROM customers ORDER BY title ASC"); 
                                foreach($list as $item){ ?>
                                    <option value="<?=$item->id;?>" <?=$edit_row->customer_id==$item->id?'selected':'';?>><?=$item->title;?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Kategoriler</label>
                            <select name="category_id" class="form-control">
                                <?php $list = $pia->query("SELECT * FROM categories WHERE type='income' ORDER BY title ASC"); 
                                foreach($list as $item){ ?>
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
                            <input type="text" name="price" class="form-control" value="<?=$edit_row->price;?>" required />
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
                                        echo '<option value="'.$safe->id.'" '.($edit_row->safe_id==$safe->id?'selected':'').'>'.$safe->title.' ('.$shown_type.' - Limit: '.number_format($safe->credit_limit, 2).' TL)</option>';
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
                    <label>Notlar</label>
                    <textarea name="description" class="form-control"><?=$edit_row->description;?></textarea>
                </div>
                <div class="form-group">
                    <label>Tarih&Saat</label>
                    <input type="datetime-local" name="adddate" class="form-control" value="<?=date('Y-m-d\TH:i', strtotime($edit_row->adddate));?>" />
                </div>
                <input type="hidden" name="edit_id" value="<?=$edit_id;?>" />
                <input type="hidden" name="frm" value="frmEditIncome" />
                <button type="button" onclick="saveEditForm()" class="btn-primary">Kaydet</button>
                <a href="income.php" class="btn-danger">Vazgeç</a>
            </form>
        </div>
    </div>
</div>

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
            <a class="panel-close" href="income.php"><i class="fal fa-times"></i></a>
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
                    <span class="text-success">+<?=number_format($edit_row->price, 2);?> TL</span>
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
                    <strong>Notlar : </strong>
                    <?=!empty($edit_row->description) ? nl2br($edit_row->description) : 'Not girilmemiş';?>
                </div>
                <div class="form-group m0">
                    <strong>Tarih : </strong>
                    <?=$pia->time_tr($edit_row->adddate);?>
                </div>
            </div>
        </div>
    </div>
              <?php }elseif(isset($_GET['add_reminder'])){ 
    $edit_id = $pia->control($_GET['add_reminder'],'int');
    // Tek sorguda tüm bilgileri alalım
    $edit_row = $pia->get_row("SELECT f.*, 
        c.title as customer_name,
        cat.title as category_name,
        s.title as safe_name,
        s.type as safe_type
        FROM finances f 
        LEFT JOIN customers c ON f.customer_id = c.id 
        LEFT JOIN categories cat ON f.category_id = cat.id 
        LEFT JOIN safes s ON f.safe_id = s.id 
        WHERE f.id = $edit_id");
?>
    <div class="modal active">
        <form class="panel-default modal-medium" method="post" action="_ajax/_ajaxAddReminders.php">
            <div class="panel-title">#<?=$edit_row->id;?> numaralı işleme hatırlatma ekle</div>
            <a class="panel-close" href="income.php"><i class="fal fa-times"></i></a>
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
                    <span class="text-success">+<?=number_format($edit_row->price, 2);?> TL</span>
                </div>
                <div class="form-group">
                    <strong>Ödeme Yöntemi : </strong>
                    <?php 
                    if($edit_row->safe_type == 'credit_card') {
                        echo 'Kredi Kartı';
                    } else {
                        echo 'Nakit';
                    }
                    ?> 
                    (<?=$edit_row->safe_name;?>)
                </div>
                
                <div class="form-group">
                    <strong>Notlar : </strong>
                    <?=!empty($edit_row->description) ? nl2br($edit_row->description) : 'Not girilmemiş';?>
                </div>
                <div class="form-group">
                    <strong>Tarih : </strong>
                    <?=$pia->time_tr($edit_row->adddate);?>
                </div>
                <div class="form-group" style="border-top:1px solid #EEE;padding-top:20px;">
                    <label>Hatırlatma notu</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label>Hatırlatma tarihi</label>
                    <input type="date" name="reminder_date" class="form-control" value="<?=date('Y-m-d', strtotime('-1 day'));?>" required>
                </div>
                <button type="submit" class="btn-primary btn-block">Gönder</button>
            </div>
            <input type="hidden" name="finance_id" value="<?=$edit_row->id;?>"/>
            <input type="hidden" name="frm" value="frmAddReminders"/>
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
                <th>Kasa</th>
                <th width="150px">Tarih</th>
                <th width="150px">İşlemler</th>
          </tr>
    </thead>
    <tbody>

                                          <?php 
                                       
                                                $list = $pia->query("SELECT f.*, 
    c.title as customer_title, 
    cat.title as category_title,
    s.title as safe_title,
    s.type as safe_type
    FROM finances f 
    LEFT JOIN customers c ON c.id = f.customer_id
    LEFT JOIN categories cat ON cat.id = f.category_id
    LEFT JOIN safes s ON s.id = f.safe_id
    WHERE f.event_type = 1 
    ORDER BY f.id DESC");  
foreach($list as $item){ 
?>
    <tr>
        <td><?=$item->id;?></td>
        <td><?=$item->customer_title;?></td>
        <td><?=$item->category_title;?></td>
        <td class="text-success">+<?=number_format($item->price, 2);?> TRY</td>
<td>
    <?php 
    $type_text = array(
        'cash' => 'Nakit',
        'credit_card' => 'Kredi Kartı',
        'bank_account' => 'Banka Hesabı',
        'check' => 'Çek Hesabı'
    );
    $shown_type = isset($type_text[$item->safe_type]) ? $type_text[$item->safe_type] : $item->safe_type;
    echo $shown_type . ' ('.$item->safe_title.')';
    ?>
</td>
        <td width="200px"><?=date('d.m.Y H:i', strtotime($item->adddate));?></td>
        <td width="250px">

                                                            <a href="income.php?add_reminder=<?=$item->id;?>" class="btn-warning btn-xs">Hatırlatma ekle</a>
                                                            <a href="income.php?view_id=<?=$item->id;?>" class="btn-default btn-xs">İncele</a>
                                                            <a href="income.php?edit=<?=$item->id;?>" class="btn-default btn-xs">Düzenle</a>
                                                            <a onclick="_delete('finances', <?=$item->id;?>);" class="btn-default btn-xs">Sil</a>
                                                      </td>
                                                </tr>

                                          <?php } ?>

                                    </tbody>

                              </table>
                             

                
                       


           </section>


                  


<script>
function saveForm() {
    var formData = $('#frmAddIncome').serialize();
    
    $.post('_ajax/_ajaxAddIncome.php', formData, function(response) {
        if(response.includes('success')) {
            window.location.href = 'income.php';
        } else {
            alert(response);
        }
    });
}

function saveEditForm() {
    var formData = $('#frmEditIncome').serialize();
    
    $.post('_ajax/_ajaxEditIncome.php', formData, function(response) {
        if(response.includes('success')) {
            window.location.href = 'income.php';
        } else {
            alert(response);
        }
    });
}
</script>


           <?php require('_inc/footer.php'); ?>
            

     </body>

</html>

