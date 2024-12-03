<?php require('_class/config.php'); require('session.php'); ?>
<!DOCTYPE html>
<html>
<head>
    <?php require('_inc/head.php'); ?>
    <title>Kasalar</title>
</head>
<body>
    <?php require('_inc/header.php'); ?>
    <section class="content">
        <div class="title">
            <div class="left">
                <h1>Kasalar</h1>
                <ul class="breadcrumb">
                    <li><a href="main.php">Anasayfa</a></li>
                    <li><a href="safes.php">Kasalar</a></li>
                </ul>
            </div>
            <div class="right">
                <a href="safes.php?add" class="btn-success btn-sm">Yeni Kasa Ekle</a>
            </div>
        </div>

        <?php if(isset($_GET['add'])){ ?>
        <div class="modal active">
            <form method="post" action="_ajax/_ajaxAddSafe.php" class="panel-default modal-medium" id="safeForm">
                <div class="panel-title">
                    <i class="fal fa-plus-circle"></i> Yeni Kasa Ekle
                    <a class="panel-close" href="safes.php"><i class="fal fa-times"></i></a>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kasa Adı</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Kasa Tipi</label>
                                <select name="type" class="form-control" id="safeType">
                                    <option value="cash">Nakit Kasa</option>
                                    <option value="credit_card">Kredi Kartı</option>
                                    <option value="bank_account">Banka Hesabı</option>
                                    <option value="check">Çek Hesabı</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="creditLimitGroup" style="display:none;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kredi Limiti</label>
                                    <input type="text" name="credit_limit" class="form-control" pattern="[0-9]*\.?[0-9]+" placeholder="Örn: 1000.00" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Hesap Kesim Günü</label>
                                    <input type="number" name="statement_day" class="form-control" min="1" max="31" placeholder="Örn: 15" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Banka Adı</label>
                                    <input type="text" name="bank_name" class="form-control" placeholder="Örn: Ziraat Bankası" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Son Ödeme Günü</label>
                                    <input type="number" name="due_day" class="form-control" min="1" max="31" placeholder="Örn: 1" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="submitBtn" class="btn-primary">Kaydet</button>
                    <a href="safes.php" class="btn-danger">Vazgeç</a>
                </div>
                <input type="hidden" name="frm" value="frmAddSafe" />
            </form>
        </div>
        <?php } elseif(isset($_GET['edit'])) { 
            $edit_id = $pia->control($_GET['edit'],'int');
            $edit_row = $pia->get_row("SELECT * FROM safes WHERE id=$edit_id AND u_id=$admin->id");
        ?>
        <div class="modal active">
            <div class="panel-default modal-medium">
                <div class="panel-title">
                    <i class="fal fa-pen"></i> Kasa Düzenle
                    <a class="panel-close" href="safes.php"><i class="fal fa-times"></i></a>
                </div>
                <div class="panel-body">
                    <form method="post" action="_ajax/_ajaxEditSafe.php" id="editSafeForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kasa Adı</label>
                                    <input type="text" name="title" class="form-control" value="<?=$edit_row->title;?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Kasa Tipi</label>
                                    <select name="type" class="form-control" id="editSafeType">
                                        <option value="cash" <?=$edit_row->type=='cash'?'selected':'';?>>Nakit Kasa</option>
                                        <option value="credit_card" <?=$edit_row->type=='credit_card'?'selected':'';?>>Kredi Kartı</option>
                                        <option value="bank_account" <?=$edit_row->type=='bank_account'?'selected':'';?>>Banka Hesabı</option>
                                        <option value="check" <?=$edit_row->type=='check'?'selected':'';?>>Çek Hesabı</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="editCreditCardDetails" style="display:<?=$edit_row->type=='credit_card'?'block':'none';?>">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Kredi Limiti</label>
                                        <input type="text" name="credit_limit" class="form-control" value="<?=$edit_row->credit_limit;?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Hesap Kesim Günü</label>
                                        <input type="number" name="statement_day" class="form-control" min="1" max="31" value="<?=$edit_row->statement_day ?? '';?>" placeholder="Örn: 15" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Banka Adı</label>
                                        <input type="text" name="bank_name" class="form-control" value="<?=$edit_row->bank_name ?? '';?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Son Ödeme Günü</label>
                                        <input type="number" name="due_day" class="form-control" min="1" max="31" value="<?=$edit_row->due_day ?? '';?>" placeholder="Örn: 1" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="editSubmitBtn" class="btn-primary">Kaydet</button>
                        <a href="safes.php" class="btn-danger">Vazgeç</a>
                        <input type="hidden" name="edit_id" value="<?=$edit_row->id;?>" />
                        <input type="hidden" name="frm" value="frmEditSafe" />
                    </form>
                </div>
            </div>
        </div>
        <?php } ?>

        <table class="table">
            <thead>
                <tr>
                    <th width="50px">ID</th>
                    <th>Kasa Adı</th>
                    <th>Tipi</th>
                    <th>Bakiye</th>
                    <th>Limit</th>
                    <th>Tarih</th>
                    <th width="150px">İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $list = $pia->query("SELECT * FROM safes WHERE u_id=$admin->id ORDER BY id DESC");
                foreach($list as $item){ 
                    $type_text = array(
                        'cash' => 'Nakit',
                        'credit_card' => 'Kredi Kartı',
                        'bank_account' => 'Banka Hesabı',
                        'check' => 'Çek Hesabı'
                    );
                    $shown_type = isset($type_text[$item->type]) ? $type_text[$item->type] : $item->type;
                ?>
                <tr>
                    <td><?=$item->id;?></td>
                    <td><?=$item->title;?></td>
                    <td><?=$shown_type;?></td>
                    <td><?=number_format($item->balance, 2);?> TL</td>
                    <td><?=$item->type == 'credit_card' ? number_format($item->credit_limit, 2).' TL' : '-';?></td>
                    <td><?=$item->created_at;?></td>
                    <td>
                        <a href="safes.php?edit=<?=$item->id;?>" class="btn-default btn-xs">Düzenle</a>
                        <a onclick="_delete('safes', <?=$item->id;?>);" class="btn-default btn-xs">Sil</a>
                        <a href="/muhasebe/safe_transactions.php?safe_id=<?=$item->id;?>" class="btn-info btn-xs">
                            <i class="fal fa-list"></i> Hesap Hareketleri
                        </a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>

    <?php require('_inc/footer.php'); ?>
    <script>
    $(document).ready(function() {
        // Kaydet butonuna tıklandığında
        $('#submitBtn').click(function() {
            var btn = $(this);
            var form = $('#safeForm');
            
            if($('#safeType').val() == 'credit_card') {
                var limit = $('input[name="credit_limit"]').val();
                var statement_day = $('input[name="statement_day"]').val();
                var bank_name = $('input[name="bank_name"]').val();
                var due_day = $('input[name="due_day"]').val();

                if(!limit || limit == '') {
                    alert('Kredi kartı için limit girmelisiniz!');
                    $('input[name="credit_limit"]').focus();
                    return false;
                }
                if(!statement_day || statement_day == '') {
                    alert('Hesap kesim günü girmelisiniz!');
                    $('input[name="statement_day"]').focus();
                    return false;
                }
                if(!bank_name || bank_name == '') {
                    alert('Banka adı girmelisiniz!');
                    $('input[name="bank_name"]').focus();
                    return false;
                }
                if(!due_day || due_day == '') {
                    alert('Son ödeme günü girmelisiniz!');
                    $('input[name="due_day"]').focus();
                    return false;
                }
            }
            
            btn.prop('disabled', true).text('Kaydediliyor...');
            
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: form.serialize(),
                success: function(response) {
                    if(response.includes('success')) {
                        window.location.replace('safes.php');
                    } else {
                        btn.prop('disabled', false).text('Kaydet');
                        alert('Bir hata oluştu!');
                    }
                },
                error: function() {
                    btn.prop('disabled', false).text('Kaydet');
                    alert('Bir hata oluştu!');
                }
            });
        });

        // Kasa tipi değiştiğinde
        $('#safeType, #editSafeType').change(function() {
            var target = $(this).attr('id') == 'safeType' ? '#creditLimitGroup' : '#editCreditCardDetails';
            if($(this).val() == 'credit_card') {
                $(target).slideDown();
                $(target).find('input').prop('required', true);
            } else {
                $(target).slideUp();
                $(target).find('input').prop('required', false);
            }
        });

        // Düzenleme formu kaydet
        $('#editSubmitBtn').click(function() {
            var btn = $(this);
            var form = $('#editSafeForm');
            
            if($('#editSafeType').val() == 'credit_card') {
                var limit = form.find('input[name="credit_limit"]').val();
                var statement_day = form.find('input[name="statement_day"]').val();
                var bank_name = form.find('input[name="bank_name"]').val();
                var due_day = form.find('input[name="due_day"]').val();

                if(!limit || limit == '') {
                    alert('Kredi kartı için limit girmelisiniz!');
                    form.find('input[name="credit_limit"]').focus();
                    return false;
                }
                if(!statement_day || statement_day == '') {
                    alert('Hesap kesim günü girmelisiniz!');
                    form.find('input[name="statement_day"]').focus();
                    return false;
                }
                if(!bank_name || bank_name == '') {
                    alert('Banka adı girmelisiniz!');
                    form.find('input[name="bank_name"]').focus();
                    return false;
                }
                if(!due_day || due_day == '') {
                    alert('Son ödeme günü girmelisiniz!');
                    form.find('input[name="due_day"]').focus();
                    return false;
                }
            }
            
            btn.prop('disabled', true).text('Kaydediliyor...');
            
            $.ajax({
    url: form.attr('action'),
    type: 'POST',
    data: form.serialize(),
    success: function(response) {
        console.log('Server response:', response); // Hata ayıklama için
        if(response.includes('success')) {
            window.location.replace('safes.php');
        } else {
            btn.prop('disabled', false).text('Kaydet');
            alert('Bir hata oluştu: ' + response);
        }
    },
    error: function(xhr, status, error) {
        console.log('Ajax error:', error); // Hata ayıklama için
        btn.prop('disabled', false).text('Kaydet');
        alert('Bir hata oluştu: ' + error);
    }
});
        });
    });
    </script>
</body>
</html>