<?php require('_class/config.php'); require('session.php');   ?>
<!DOCTYPE html>
<html>
      <head>
            <?php require('_inc/head.php'); ?>
            <title>Kasa Yönetimi</title>
      </head>
      <body>
            <?php require('_inc/header.php'); ?>

            <section class="content">
                   <div class="title">
                        <div class="left">
                              <h1>Kasa Yönetimi</h1>
                              <ul class="breadcrumb">
                                    <li><a href="main.php">Anasayfa</a></li>
                                    <li><a href="safe.php">Kasa Yönetimi</a></li>
                              </ul>
                        </div>
                        <div class="right">
                              <a href="safe.php?add" class="btn-success btn-sm">Yeni Kasa Ekle</a>
                        </div>
                  </div>

                  <?php if(isset($_GET['add'])){ ?>
                        <div class="modal">
                                <form method="post" action="_ajax/_ajaxAddSafe.php" class="panel-default modal-medium">
                                          <div class="panel-title"><i class="fal fa-plus-circle"></i> Yeni Kasa Ekle</div> 
                                          <div class="panel-body">
                                                <div class="form-group">
                                                    <label>Kasa Adı</label>
                                                    <input type="text" name="title" class="form-control" required />
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Başlangıç Bakiyesi</label>
                                                    <input type="number" name="balance" class="form-control" value="0" step="0.01" />
                                                </div>
                                                
                                                <div class="form-group">
                                                    <label>Açıklama</label>
                                                    <textarea name="description" class="form-control"></textarea>
                                                </div>
                                                
                                                <button type="submit" class="btn-primary">Kaydet</button>
                                                <a href="safe.php" class="btn-danger">Vazgeç</a>
                                          </div>
                                          <input type="hidden" name="frm" value="frmAddSafe" />
                                    </form>
                        </div>
                                    
                  <?php }elseif(isset($_GET['edit'])){ 
                        $edit_id = $pia->control($_GET['edit'],'int');
                        $edit_row = $pia->get_row("SELECT * FROM safes WHERE id=$edit_id");
                  ?>    
                        <div class="modal">
                        <form method="post" action="_ajax/_ajaxEditSafe.php" class="panel-default modal-medium">
                             <div class="panel-title"><i class="fal fa-pen"></i> Kasa Düzenle</div> 
                             <div class="panel-body">
                                    <div class="form-group">
                                        <label>Kasa Adı</label>
                                        <input type="text" name="title" class="form-control" value="<?=$edit_row->title;?>" required />
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Açıklama</label>
                                        <textarea name="description" class="form-control"><?=$edit_row->description;?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn-primary">Güncelle</button>
                                    <a href="safe.php" class="btn-danger">Vazgeç</a>
                             </div>
                             <input type="hidden" name="edit_id" value="<?=$edit_id;?>" />
                             <input type="hidden" name="frm" value="frmEditSafe" />
                        </form>
                        </div>
                  <?php } ?>

                  <!-- Kasa Listesi -->
                  <div class="row">
                      <?php 
                      $list = $pia->query("SELECT * FROM safes ORDER BY id DESC");  
                      foreach($list as $item){ 
                          // Son 24 saatteki hareketleri al
                          $last24h = $pia->query("
                              SELECT SUM(CASE WHEN type = 1 THEN amount ELSE 0 END) as total_in,
                                     SUM(CASE WHEN type = 0 THEN amount ELSE 0 END) as total_out
                              FROM safe_transactions 
                              WHERE safe_id = $item->id 
                              AND transaction_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                          ");
                          $last24h = $last24h[0];
                      ?>
                      <div class="col-md-4">
                          <div class="panel-default">
                              <div class="panel-title">
                                  <?=$item->title;?>
                                  <div class="panel-right">
                                      <a href="safe.php?edit=<?=$item->id;?>" class="btn-default btn-xs">Düzenle</a>
                                  </div>
                              </div>
                              <div class="panel-body">
                                  <h3 class="m0">₺<?=number_format($item->balance, 2);?></h3>
                                  <small>Güncel Bakiye</small>
                                  
                                  <div class="mt-3">
                                      <div class="row">
                                          <div class="col-6">
                                              <span class="text-success">+₺<?=number_format($last24h->total_in ?? 0, 2);?></span>
                                              <small>Son 24s Giriş</small>
                                          </div>
                                          <div class="col-6">
                                              <span class="text-danger">-₺<?=number_format($last24h->total_out ?? 0, 2);?></span>
                                              <small>Son 24s Çıkış</small>
                                          </div>
                                      </div>
                                  </div>
                                  
                                  <div class="mt-3">
                                      <a href="safe_transactions.php?safe_id=<?=$item->id;?>" class="btn-default btn-block btn-sm">Hareketleri Görüntüle</a>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <?php } ?>
                  </div>

           </section>

           <?php require('_inc/footer.php'); ?>
     </body>
</html>