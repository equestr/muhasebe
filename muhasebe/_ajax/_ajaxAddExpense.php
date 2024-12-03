<?php 
require('../_class/config.php'); 
require('../session.php');

if(isset($_POST['frm']) && $_POST['frm']=='frmAddExpense'){
    try {
        // Kasa kontrolü
        $safe_id = intval($_POST['safe_id']);
        $safe = $pia->get_row("SELECT * FROM safes WHERE id=$safe_id AND u_id=$admin->id");
        
        if(!$safe) {
            echo 'error: Kasa bulunamadı!';
            exit;
        }

        // Tutarı hazırla
        $amount = floatval(str_replace(',', '.', $_POST['price']));

       // Description'ı güvenli hale getir
$description = isset($_POST['description']) ? htmlspecialchars($_POST['description'], ENT_QUOTES) : '';

// SQL sorgusunda description kısmını düzelt
$sql = "UPDATE finances SET 
        customer_id = ".intval($_POST['customer_id']).",
        category_id = ".intval($_POST['category_id']).",
        safe_id = $safe_id,
        price = $amount,
        description = '".$description."',  // Değişiklik burada
        adddate = '".date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $_POST['adddate'])))."',
        is_recurring = ".(isset($_POST['is_recurring']) ? 1 : 0);

       // Bakiye/Limit kontrolü
if($safe->type == 'credit_card') {
    $used_amount = $safe->credit_limit - $safe->balance;
    $available = $safe->credit_limit - $used_amount;
    
    if($amount > $available) {
        try {
            // Kategori ve müşteri ID'lerini al
            $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 'NULL';
            $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 'NULL';
            
            // Description'ı basit tut
            $description = isset($_POST['description']) && !empty($_POST['description']) 
                ? addslashes(strip_tags($_POST['description'])) 
                : 'Yetersiz limit nedeniyle bekleyen ödeme';

            // VALUES formatını kullanalım
            $pending_sql = "INSERT INTO pending_payments 
                (u_id, safe_id, amount, customer_id, category_id, due_date, description, status, created_at) 
                VALUES 
                ($admin->id, 
                $safe_id, 
                $amount, 
                $customer_id, 
                $category_id, 
                '".date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $_POST['adddate'])))."',
                '$description',
                'pending',
                NOW())";
            
            // Sorguyu çalıştır
            $result = $pia->query($pending_sql);
            
            if($result) {
                echo 'warning: Yetersiz limit! Ödeme beklemeye alındı.';
            } else {
                error_log("Pending Payment Insert Failed!");
                echo 'error: Bekleyen ödeme kaydedilemedi!';
            }
            exit;
        } catch (Exception $e) {
            error_log("Pending Payment Error: " . $e->getMessage());
            echo 'error: Bekleyen ödeme kaydedilirken hata oluştu!';
            exit;
        }
    }
} else {
    if($amount > $safe->balance) {
        try {
            // Kategori ve müşteri ID'lerini al
            $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 'NULL';
            $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 'NULL';
            
            // Description'ı basit tut
            $description = isset($_POST['description']) && !empty($_POST['description']) 
                ? addslashes(strip_tags($_POST['description'])) 
                : 'Yetersiz bakiye nedeniyle bekleyen ödeme';

            // VALUES formatını kullanalım
            $pending_sql = "INSERT INTO pending_payments 
                (u_id, safe_id, amount, customer_id, category_id, due_date, description, status, created_at) 
                VALUES 
                ($admin->id, 
                $safe_id, 
                $amount, 
                $customer_id, 
                $category_id, 
                '".date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $_POST['adddate'])))."',
                '$description',
                'pending',
                NOW())";
            
            // Sorguyu çalıştır
            $result = $pia->query($pending_sql);
            
            if($result) {
                echo 'warning: Yetersiz bakiye! Ödeme beklemeye alındı.';
            } else {
                error_log("Pending Payment Insert Failed!");
                echo 'error: Bekleyen ödeme kaydedilemedi!';
            }
            exit;
        } catch (Exception $e) {
            error_log("Pending Payment Error: " . $e->getMessage());
            echo 'error: Bekleyen ödeme kaydedilirken hata oluştu!';
            exit;
        }
    }
}

        // Gider kaydını ekle
        $sql = "INSERT INTO finances SET 
                u_id = $admin->id,
                customer_id = ".intval($_POST['customer_id']).",
                category_id = ".intval($_POST['category_id']).",
                safe_id = $safe_id,
                price = $amount,
                event_type = 0,
                description = '$description',
                adddate = '".date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $_POST['adddate'])))."'";

        if(isset($_POST['is_recurring']) && $_POST['is_recurring'] == 1) {
            $sql .= ",
                is_recurring = 1,
                recurring_start_date = '".date('Y-m-d', strtotime($_POST['recurring_start_date']))."',
                recurring_months = ".intval($_POST['recurring_months']).",
                recurring_count = ".intval($_POST['recurring_count']);
        }

        $result = $pia->query($sql);
        
        if($result) {
            // Kasa bakiyesini güncelle
            $new_balance = $safe->balance - $amount;
            $update_sql = "UPDATE safes SET balance = $new_balance WHERE id = $safe_id";
            $update_result = $pia->query($update_sql);

            if($update_result) {
                echo 'success';
                exit;
            } else {
                echo 'error: Kasa güncellenirken hata oluştu!';
                exit;
            }
        } else {
            echo 'error: Gider kaydı eklenirken hata oluştu!';
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