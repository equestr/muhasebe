<?php
require('../_class/config.php'); 
require('../session.php');

if(isset($_POST['payment_id']) && isset($_POST['action'])) {
    try {
        $payment_id = intval($_POST['payment_id']);
        $action = $_POST['action'];

        // Ödeme kaydını al
        $payment = $pia->get_row("SELECT * FROM pending_payments WHERE id = $payment_id AND u_id = $admin->id");
        if(!$payment) {
            echo 'error: Ödeme kaydı bulunamadı!';
            exit;
        }

        // Kasa kontrolü
        $safe = $pia->get_row("SELECT * FROM safes WHERE id = $payment->safe_id AND u_id = $admin->id");
        if(!$safe) {
            echo 'error: Kasa bulunamadı!';
            exit;
        }

        if($action == 'process') {
            // Bakiye kontrolü
            if($safe->type == 'credit_card') {
                $used_amount = $safe->credit_limit - $safe->balance;
                $available = $safe->credit_limit - $used_amount;
                
                if($payment->amount > $available) {
                    // Bekleyen ödeme kaydını güncelle
                    $pending_sql = "UPDATE pending_payments SET 
                        customer_id = " . ($payment->customer_id ?: 'NULL') . ",
                        category_id = " . ($payment->category_id ?: 'NULL') . "
                        WHERE id = $payment_id";
                    $pia->exec($pending_sql);

                    echo 'error: Yetersiz kredi limiti!';
                    exit;
                }
            } else {
                if($payment->amount > $safe->balance) {
                    echo 'error: Yetersiz bakiye!';
                    exit;
                }
            }

            // Basit INSERT sorgusu
            $insert_result = $pia->exec("INSERT INTO finances 
                (u_id, safe_id, price, event_type, description, adddate, customer_id, category_id) 
                VALUES 
                ($admin->id, $payment->safe_id, $payment->amount, 0, 'Ödeme işlemi', NOW(),
                " . ($payment->customer_id ?: 'NULL') . ", " . ($payment->category_id ?: 'NULL') . ")");

            if($insert_result) {
                // Kasa bakiyesini güncelle
                $new_balance = $safe->balance - $payment->amount;
                $update_safe = $pia->exec("UPDATE safes SET balance = $new_balance WHERE id = $payment->safe_id");

                if($update_safe) {
                    // Bekleyen ödemeyi güncelle
                    $update_payment = $pia->exec("UPDATE pending_payments SET 
                        status = 'completed',
                        completed_at = NOW(),
                        customer_id = " . ($payment->customer_id ?: 'NULL') . ",
                        category_id = " . ($payment->category_id ?: 'NULL') . "
                        WHERE id = $payment_id");

                    if($update_payment) {
                        echo 'success';
                        exit;
                    }
                }
            }

            echo 'error: Gider kaydı eklenirken bir hata oluştu!';
            exit;
        }
        elseif($action == 'cancel') {
            // Ödemeyi iptal et
            $update = $pia->exec("UPDATE pending_payments SET 
                status = 'cancelled',
                completed_at = NOW(),
                customer_id = " . ($payment->customer_id ?: 'NULL') . ",
                category_id = " . ($payment->category_id ?: 'NULL') . "
                WHERE id = $payment_id");

            if($update) {
                echo 'success';
                exit;
            }
            echo 'error: İptal işlemi gerçekleştirilemedi!';
            exit;
        }

    } catch (Exception $e) {
        echo 'error: ' . $e->getMessage();
        exit;
    }
} else {
    echo 'error: Geçersiz istek!';
    exit;
}
?>