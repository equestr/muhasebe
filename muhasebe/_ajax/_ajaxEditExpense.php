<?php 
    require('../_class/config.php'); 
    require('../session.php');
    
    if(isset($_POST['frm']) && $_POST['frm']=='frmEditExpense'){
        try {
            // ID kontrolü
            $id = intval($pia->control($_POST['edit_id'], 'int'));
            if(!$id) {
                $pia->alertify('error', 'Geçersiz ID!');
                exit;
            }

            // Temel alanları hazırla
            $customer_id = intval($pia->control($_POST['customer_id'], 'int'));
            $category_id = intval($pia->control($_POST['category_id'], 'int'));
            $method_id = intval($pia->control($_POST['method_id'], 'int'));
            $price = str_replace(",", ".", $_POST['price']); // control fonksiyonunu kaldırdık
            $event_type = 0;
            $description = trim($_POST['description']); // Sadece trim kullanıyoruz
            $adddate = !empty($_POST['adddate']) ? date('Y-m-d H:i:s', strtotime($_POST['adddate'])) : date('Y-m-d H:i:s');
            $is_recurring = isset($_POST['is_recurring']) ? intval($_POST['is_recurring']) : 0;

            if($is_recurring == 1) {
                $sql = "UPDATE finances SET 
                       customer_id = $customer_id,
                       category_id = $category_id,
                       method_id = $method_id,
                       price = $price,
                       event_type = $event_type,
                       description = '$description',
                       adddate = '$adddate',
                       is_recurring = $is_recurring,
                       recurring_start_date = '" . date('Y-m-d', strtotime($_POST['recurring_start_date'])) . "',
                       recurring_months = " . intval($_POST['recurring_months']) . ",
                       recurring_count = " . intval($_POST['recurring_count']) . "
                       WHERE id = $id";
            } else {
                $sql = "UPDATE finances SET 
                       customer_id = $customer_id,
                       category_id = $category_id,
                       method_id = $method_id,
                       price = $price,
                       event_type = $event_type,
                       description = '$description',
                       adddate = '$adddate',
                       is_recurring = $is_recurring
                       WHERE id = $id";
            }

            // Debug için SQL'i logla
            error_log("SQL Query: " . $sql);

            // Sorguyu çalıştır
            if($pia->exec($sql)){
                $pia->alertify('success', 'İşlem başarıyla güncellendi!', 'expense.php');
            } else {
                error_log("SQL Update Error: " . $sql);
                $pia->alertify('error', 'Veritabanı hatası oluştu!');
            }

        } catch (Exception $e) {
            error_log("Exception in EditExpense: " . $e->getMessage());
            $pia->alertify('error', 'Bir hata oluştu: ' . $e->getMessage());
        }
    } 
?>