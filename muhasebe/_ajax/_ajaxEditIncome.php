<?php 
require('../_class/config.php'); 
require('../session.php');

if(isset($_POST['frm']) && $_POST['frm']=='frmEditIncome'){
    try {
        $edit_id = intval($_POST['edit_id']);
        
        // Önce mevcut kaydı alalım
        $current_income = $pia->get_row("SELECT * FROM finances WHERE id = $edit_id AND u_id = $admin->id");
        if(!$current_income) {
            echo 'error: Kayıt bulunamadı!';
            exit;
        }

        // Yeni kasa kontrolü
        $safe_id = intval($_POST['safe_id']);
        $safe = $pia->get_row("SELECT * FROM safes WHERE id = $safe_id AND u_id = $admin->id");
        if(!$safe) {
            echo 'error: Kasa bulunamadı!';
            exit;
        }

        // Eski kasa
        $old_safe = $pia->get_row("SELECT * FROM safes WHERE id = $current_income->safe_id");
        
        // Tutarı hazırla
        $amount = floatval(str_replace(',', '.', $_POST['price']));

        // Transaction başlat
        $pia->query("START TRANSACTION");

        try {
            // Önce eski kasadan eski tutarı çıkar
            $pia->query("UPDATE safes SET balance = balance - {$current_income->price} WHERE id = {$current_income->safe_id}");

            // Eğer kasa değiştiyse
            if($safe_id != $current_income->safe_id) {
                // Yeni kasaya yeni tutarı ekle
                $pia->query("UPDATE safes SET balance = balance + {$amount} WHERE id = {$safe_id}");
            } else {
                // Aynı kasaya yeni tutarı ekle
                $pia->query("UPDATE safes SET balance = balance + {$amount} WHERE id = {$safe_id}");
            }

            // Gelir kaydını güncelle
            $sql = "UPDATE finances SET 
                    customer_id = ".intval($_POST['customer_id']).",
                    category_id = ".intval($_POST['category_id']).",
                    safe_id = $safe_id,
                    price = $amount,
                    description = '".addslashes($_POST['description'])."',
                    adddate = '".date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $_POST['adddate'])))."'
                    WHERE id = $edit_id AND u_id = $admin->id";

            $result = $pia->query($sql);
            
            if($result) {
                // İşlemleri onayla
                $pia->query("COMMIT");
                echo 'success';
                exit;
            } else {
                throw new Exception("Güncelleme yapılırken bir hata oluştu!");
            }

        } catch (Exception $e) {
            // Hata durumunda tüm işlemleri geri al
            $pia->query("ROLLBACK");
            echo 'error: ' . $e->getMessage();
            exit;
        }

    } catch (Exception $e) {
        echo 'error: ' . $e->getMessage();
        exit;
    }
} else {
    echo 'error: Geçersiz form gönderimi!';
    exit;
}
?>