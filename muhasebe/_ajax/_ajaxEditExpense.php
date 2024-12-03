<?php 
require('../_class/config.php'); 
require('../session.php');

if(isset($_POST['frm']) && $_POST['frm']=='frmEditExpense'){
    try {
        $edit_id = intval($_POST['edit_id']);
        
        // Önce mevcut kaydı alalım
        $current_expense = $pia->get_row("SELECT * FROM finances WHERE id = $edit_id AND u_id = $admin->id");
        if(!$current_expense) {
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
        $old_safe = $pia->get_row("SELECT * FROM safes WHERE id = $current_expense->safe_id");
        
        // Tutarı hazırla
        $amount = floatval(str_replace(',', '.', $_POST['price']));

        // Eğer kasa değiştiyse veya tutar değiştiyse bakiye kontrolleri yapılmalı
        if($safe_id != $current_expense->safe_id || $amount != $current_expense->price) {
            
            // Eski kasaya eski tutarı geri ekle
            $old_safe_new_balance = $old_safe->balance + $current_expense->price;
            $pia->query("UPDATE safes SET balance = $old_safe_new_balance WHERE id = $old_safe->id");

            // Yeni kasa için limit/bakiye kontrolü
            if($safe->type == 'credit_card') {
                $used_amount = $safe->credit_limit - $safe->balance;
                $available = $safe->credit_limit - $used_amount;
                
                if($amount > $available) {
                    // Eski işlemi geri al
                    $pia->query("UPDATE safes SET balance = $old_safe->balance WHERE id = $old_safe->id");
                    echo 'error: Yetersiz limit!';
                    exit;
                }
            } else {
                if($amount > $safe->balance) {
                    // Eski işlemi geri al
                    $pia->query("UPDATE safes SET balance = $old_safe->balance WHERE id = $old_safe->id");
                    echo 'error: Yetersiz bakiye!';
                    exit;
                }
            }
        }

        // Gider kaydını güncelle
        $sql = "UPDATE finances SET 
                customer_id = ".intval($_POST['customer_id']).",
                category_id = ".intval($_POST['category_id']).",
                safe_id = $safe_id,
                price = $amount,
                description = '".addslashes($_POST['description'])."',
                adddate = '".date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $_POST['adddate'])))."',
                is_recurring = ".(isset($_POST['is_recurring']) ? 1 : 0);

        if(isset($_POST['is_recurring']) && $_POST['is_recurring'] == 1) {
            $sql .= ",
                recurring_start_date = '".date('Y-m-d', strtotime($_POST['recurring_start_date']))."',
                recurring_months = ".intval($_POST['recurring_months']).",
                recurring_count = ".intval($_POST['recurring_count']);
        }

        $sql .= " WHERE id = $edit_id AND u_id = $admin->id";

        $result = $pia->query($sql);
        
        if($result) {
            // Yeni kasanın bakiyesini güncelle
            if($safe_id != $current_expense->safe_id || $amount != $current_expense->price) {
                $new_balance = $safe->balance - $amount;
                $update_sql = "UPDATE safes SET balance = $new_balance WHERE id = $safe_id";
                $pia->query($update_sql);
            }

            echo 'success';
            exit;
        } else {
            // Hata durumunda eski bakiyeleri geri yükle
            if($safe_id != $current_expense->safe_id) {
                $pia->query("UPDATE safes SET balance = $old_safe->balance WHERE id = $old_safe->id");
            }
            echo 'error: Güncelleme yapılırken bir hata oluştu!';
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