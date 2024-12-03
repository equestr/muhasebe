<?php 
require('../_class/config.php'); 
require('../session.php');

if(isset($_POST['frm']) && $_POST['frm']=='frmAddIncome'){
    
    // Form verilerini al
    $customer_id = $_POST['customer_id'];
    $category_id = $_POST['category_id'];
    $safe_id = $_POST['safe_id'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $adddate = $_POST['adddate'];

    // Kasa bilgisini al
    $safe = $pia->get_row("SELECT * FROM safes WHERE id = $safe_id");
    if(!$safe) {
        die('error: Kasa bulunamadı!');
    }

    // Gelir kaydını ekle
    $sql = "INSERT INTO finances SET 
        customer_id = '$customer_id',
        category_id = '$category_id',
        safe_id = '$safe_id',
        price = '$price',
        description = '$description',
        adddate = '$adddate',
        event_type = 1,
        u_id = '$admin->id'";
    
    $insert = $pia->query($sql);

    if($insert){
        // Kasa bakiyesini güncelle
        if($safe->type == 'credit_card') {
            // Kredi kartı ödemesi ise borç azalır (balance artar)
            $new_balance = $safe->balance + $price;
        } else {
            // Normal kasa ise bakiye artar
            $new_balance = $safe->balance + $price;
        }

        $update = $pia->query("UPDATE safes SET balance = '$new_balance' WHERE id = '$safe_id'");
        
        if($update){
            echo 'success';
        } else {
            echo 'Kasa güncellenirken hata oluştu!';
        }
    } else {
        echo 'Gelir kaydı eklenirken hata oluştu!';
    }
} else {
    echo 'Geçersiz form gönderimi!';
}
?>