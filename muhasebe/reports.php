<?php require('_class/config.php'); require('session.php');  ?>
<!DOCTYPE html >
<html>
      <head>
    <?php require('_inc/head.php');  ?>
    <title>Raporlar</title>
    <style>
        /* Ana Container Stilleri */
        .reports {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.03);
            margin: 20px 0;
            overflow: hidden;
        }

        /* Sol Panel (Filtreler) */
        .reports .left {
            background: #f8fafc;
            padding: 25px;
            border-right: 1px solid #eef2f7;
            height: 100%;
        }

        /* Form Elemanları */
        .reports .form-group {
            margin-bottom: 25px;
        }

        .reports label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 10px;
            display: block;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .reports .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #ffffff;
            color: #4a5568;
        }

        .reports .form-control:focus {
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
            outline: none;
        }

        /* Sağ Panel (Tablo) */
        .reports .right {
            padding: 25px;
            background: #ffffff;
        }

        /* Tablo Tasarımı */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 1rem;
        }

        .table thead th {
            background: #f7fafc;
            color: #2d3748;
            font-weight: 600;
            padding: 15px;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #edf2f7;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .table tbody tr td {
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
            color: #4a5568;
            font-size: 0.95rem;
            vertical-align: middle;
            transition: all 0.2s ease;
        }

        .table tbody tr:hover td {
            background-color: #f7fafc;
        }

        /* Toplam Satırları */
        .table tfoot tr td {
            padding: 15px;
            border-top: 2px solid #edf2f7;
            font-weight: 600;
            color: #2d3748;
        }

        /* Metin Renkleri */
        .text-success {
            color: #48bb78 !important;
            font-weight: 600;
        }

        .text-danger {
            color: #f56565 !important;
            font-weight: 600;
        }

        /* Buton Tasarımı */
        .btn-success {
            background: linear-gradient(145deg, #48bb78, #38a169);
            border: none;
            padding: 12px 25px;
            color: white;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.9rem;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 7px 14px rgba(50, 50, 93, 0.1), 0 3px 6px rgba(0, 0, 0, 0.08);
            background: linear-gradient(145deg, #38a169, #2f855a);
        }

        /* Select Picker Özelleştirme */
        .bootstrap-select .dropdown-toggle {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
        }

        .bootstrap-select .dropdown-toggle:focus {
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
            outline: none !important;
        }

        .bootstrap-select .dropdown-menu {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(50, 50, 93, 0.11), 0 1px 3px rgba(0, 0, 0, 0.08);
            padding: 10px;
        }

        .bootstrap-select .dropdown-item {
            padding: 8px 15px;
            border-radius: 5px;
            transition: all 0.2s ease;
        }

        .bootstrap-select .dropdown-item:hover {
            background-color: #f7fafc;
        }

        /* Responsive Tasarım */
        @media (max-width: 992px) {
            .reports .left {
                border-right: none;
                border-bottom: 1px solid #eef2f7;
                margin-bottom: 20px;
            }

            .table-responsive {
                margin: 0 -15px;
                padding: 0 15px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .table td, .table th {
                white-space: nowrap;
            }
        }

        @media (max-width: 576px) {
            .reports {
                border-radius: 0;
                margin: 0 -15px;
            }

            .reports .left, .reports .right {
                padding: 15px;
            }
        }

        /* Yazdırma Stilleri */
        @media print {
            .content { 
                padding: 0; 
                margin: 0;
            }
            .header, .sidebar, .left, .title .right { 
                display: none !important; 
            }
            .reports {
                box-shadow: none;
                margin: 0;
                padding: 0;
            }
            .reports .right { 
                width: 100%;
                padding: 0;
            }
            .table {
                border: 1px solid #edf2f7;
            }
            .table th, .table td {
                padding: 10px;
                color: #000;
                font-size: 12px;
            }
            .text-success, .text-danger {
                color: #000 !important;
            }
        }

        /* Animasyonlar */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .reports {
            animation: fadeIn 0.5s ease-out;
        }

        /* Scrollbar Özelleştirme */
        .table-responsive::-webkit-scrollbar {
            height: 6px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f7fafc;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
    </style>
</head>
      <body>
            <?php require('_inc/header.php'); ?>
            <section class="content">

                   <div class="title">
                        <div class="left">
                              <h1>Raporlar</h1>
                              <ul class="breadcrumb">
                                    <li><a href="main.php">Anasayfa</a></li>
                                    <li><a href="reports.php">Raporlar</a></li>
                              </ul>
                        </div>
                        <div class="right">
                        </div>
                  </div>
                  <div class="reports">
                        <div class="row">
                              <div class="col-md-3 col-xs-12 left">
                                    <form class="panel-default no" method="post" action="reports.php">
                                                <div class="form-group">
                                                      <label>Tarih Aralığı</label>
                                                                  <input type="date" name="startdate" class="form-control" value="<?=date('Y-m-d', strtotime('-30 days'));?>" />
                                                                  <input type="date" name="enddate" class="form-control" value="<?=date('Y-m-d');?>" />
                                                </div>
                                                 <div class="form-group">
                                                      <label>Cariler</label>
                                                      <select name="customers[]" class="form-control selectpicker" multiple data-selected-text-format="count" data-none-selected-text="Tümü" >
                                                            <?php $list = $pia->query("SELECT * FROM customers ORDER BY title ASC"); foreach($list as $item){ ?>
                                                                  <option value="<?=$item->id;?>" ><?=$item->title;?></option>
                                                            <?php } ?>
                                                      </select>
                                                </div>

                                                <div class="form-group">
                                                      <label>Kategoriler </label>
                                                      <select name="categories[]" class="form-control selectpicker" multiple data-selected-text-format="count" data-none-selected-text="Tümü" >
                                                            <?php $list = $pia->query("SELECT * FROM categories ORDER BY title ASC"); foreach($list as $item){ ?>
                                                                  <option value="<?=$item->id;?>" ><?=$item->title;?></option>
                                                            <?php } ?>
                                                      </select>
                                                </div>
                                               
                                              
                                                <!-- Eski ödeme yöntemi seçimi yerine -->
<div class="form-group">
    <label>Kasa</label>
    <select name="safes[]" class="form-control selectpicker" multiple 
            data-selected-text-format="count" data-none-selected-text="Tümü">
        <?php 
        $list = $pia->query("SELECT * FROM safes WHERE u_id = $admin->id AND status = 1 ORDER BY title ASC"); 
        foreach($list as $item){ 
            // Kasa tipine göre ekstra bilgi göster
            $extra_info = $item->type == 'credit_card' ? 
                ' (Kredi Kartı)' : 
                ' (Nakit)';
        ?>
            <option value="<?=$item->id;?>"><?=$item->title . $extra_info;?></option>
        <?php } ?>
    </select>
</div>
                                               
                                                <button type="submit" class="btn-success btn-sm">Raporla</button>
                                          <input type="hidden" name="frm" value="frmReports" />
                                    </form>
                              </div>
                              <div class="col-md-9 col-xs-12 right">

                                    <?php 
      
      
      if(isset($_POST['frm']) and $_POST['frm']=='frmReports'){



                  $startdate = $pia->control($_POST['startdate'],'text');
                  $enddate = $pia->control($_POST['enddate'],'text');
            

                  $sql = null;
                  $customers_sql = null;
                  $categories_sql = null;
                  $methods_sql = null;
                  $date_sql = null;

                  $table = null;
                  $income_total = 0;
                  $expense_total = 0;
      

                  if(isset($_POST['customers'])){

                        $customers = $_POST['customers'];

                        foreach($customers as $item ){

                              $customer_id = $pia->control($item,'int');
                              $customers_sql[] = "customer_id=$customer_id";

                        }

                        $sql[] = ' ( '.implode(' or ', $customers_sql).' ) ';


                  }


                  if(isset($_POST['categories'])){

                        $categories = $_POST['categories'];

                        foreach($categories as $item ){

                              $category_id = $pia->control($item,'int');

                              $categories_sql[] =  "category_id=$category_id";

                        }

                        $sql[] = ' ( '.implode(' or ', $categories_sql).' ) ';


                  }


                  if(isset($_POST['safes'])){
    $safes = $_POST['safes'];
    foreach($safes as $item){
        $safe_id = $pia->control($item,'int');
        $safes_sql[] = "safe_id=$safe_id";
    }
    if(!empty($safes_sql)){
        $sql[] = '(' . implode(' OR ', $safes_sql) . ')';
    }
}

                  
                  if(isset($_POST['startdate'])){

                        $startdate = $pia->control($_POST['startdate'],'text');

                        $date_sql[] = "DATE(adddate) >= $startdate ";

                  }

                  if(isset($_POST['enddate'])){

                        $enddate = $pia->control($_POST['enddate'],'text');

                        $date_sql[] = "DATE(adddate) <= $enddate";

                  }

                  $sql[] = implode(' and ', $date_sql);



                  


                  $sql = implode(' and ', $sql);

$row = $pia->get_row("SELECT * FROM finances WHERE $sql ORDER BY adddate DESC");
if(isset($row->id)){
    $list = $pia->query("SELECT * FROM finances WHERE $sql ORDER BY adddate DESC");
    foreach($list as $item){
        // Müşteri ve kategori bilgilerini al
        $customers_row = $pia->get_row("SELECT * FROM customers WHERE id = $item->customer_id");
        $category_row = $pia->get_row("SELECT * FROM categories WHERE id = $item->category_id");
        
        // Kasa bilgisini al
        $safe_row = $pia->get_row("SELECT * FROM safes WHERE id = $item->safe_id");
        $payment_info = '';
        if($safe_row) {
            $payment_info = $safe_row->title;
            $payment_info .= $safe_row->type == 'credit_card' ? ' (Kredi Kartı)' : ' (Nakit)';
        }

        // Gelir/Gider hesaplaması
        if($item->event_type==1){
            $income_total += $item->price;
            $price = '<span class="text-success">+'.number_format($item->price, 2).' TRY</span>';
        }elseif($item->event_type==0){
            $expense_total += $item->price;
            $price = '<span class="text-danger">-'.number_format($item->price, 2).' TRY</span>';
        }

        // Tablo satırını oluştur
        $table .= '<tr>
            <td>'.@$customers_row->title.'</td>
            <td>'.@$category_row->title.'</td>
            <td>'.$price.'</td>
            <td>'.$payment_info.'</td>
            <td>'.$pia->time_tr($item->adddate).'</td>
        </tr>';

                        }

                  }else{
                        $table .= '<tr><td colspan="2">Geliriniz bulunmuyor.</td></tr>';
                  }
                  


                  $price = $pia->price_format($income_total-$expense_total);

            if($price>0){
                  $price = '<span class="text-success">+'.$price.' TRY</span>';
            }elseif($price<0){
                  $price = '<span class="text-danger">'.$price.' TRY</span>';
            }else{
                  $price = $price; 
            }

             }
?>
  <table class="table" data-searching="false" data-ordering="false" data-paging="false" data-info="false">
                              <thead>
                                    <tr>
                                          <th>Cari adı</th>
                                          <th>Kategoriler</th>
                                          <th>Fiyat</th>
                                          <th>Ödeme Y.</th>
                                          <th>Tarih</th>
                                    </tr>
                              </thead>
                              <tbody>
                                    <tbody>
    <?=@$table;?>
</tbody>
<tfoot>
    <tr>
        <td></td>
        <td><strong>Toplam Gelir</strong></td>
        <td class="text-success"><strong>+<?=number_format(@$income_total, 2)?> TRY</strong></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td><strong>Toplam Gider</strong></td>
        <td class="text-danger"><strong>-<?=number_format(@$expense_total, 2)?> TRY</strong></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td><strong>Net Toplam</strong></td>
        <td><strong><?=@$price?></strong></td>
        <td></td>
        <td></td>
    </tr>
</tfoot>
      





                              </div>      
                        </div>
                  </div>
                 
                  
                        

           </section>
           <?php require('_inc/footer.php'); ?>
           

           

     </body>
</html>
