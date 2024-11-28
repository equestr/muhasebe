<?php 
require('../_class/config.php'); 
require('../session.php');

if(isset($_POST['frm']) && $_POST['frm']=='frmAddSafe'){
    $title = $pia->control($_POST['title'], 'text');
    $balance = $pia->control($_POST['balance'], 'float');
    $description = $pia->control($_POST['description'], 'text');
    
    // Yeni kasa ekle
    if($pia->insert("INSERT INTO safes(title, balance, description) VALUES('$title', $balance, '$description')")){
        $safe_id = $pia->lastInsertId();
        
        // Eğer başlangıç bakiyesi varsa, ilk hareketi ekle
        if($balance > 0){
            $pia->insert("INSERT INTO safe_transactions(
                safe_id, type, amount, description, transaction_date
            ) VALUES(
                $safe_id, 1, $balance, 'Başlangıç bakiyesi', NOW()
            )");
        }
        
        $pia->alertify('success', 'Kasa başarıyla eklendi!','safe.php');
    }else{
        $pia->alertify('error', 'Bir hata oluştu!');
    }
} 
?>