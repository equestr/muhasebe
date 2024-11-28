<?php 
    require('../_class/config.php'); 
    require('../session.php');
    
    if(isset($_POST['frm']) && $_POST['frm']=='frmAddExpense'){
        try {
            // Temel alanları hazırla
            $data = array(
                'u_id' => $admin->id,
                'customer_id' => intval($_POST['customer_id']),
                'category_id' => intval($_POST['category_id']),
                'method_id' => intval($_POST['method_id']),
                'price' => str_replace(',', '.', $_POST['price']),
                'event_type' => 0,
                'description' => $_POST['description'],
                'adddate' => date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $_POST['adddate']))),
                'is_recurring' => isset($_POST['is_recurring']) ? 1 : 0
            );

            if($data['is_recurring'] == 1) {
                $sql = "INSERT INTO finances 
                        (u_id, customer_id, category_id, method_id, price, event_type, description, 
                         adddate, is_recurring, recurring_start_date, recurring_months, recurring_count) 
                        VALUES 
                        (:u_id, :customer_id, :category_id, :method_id, :price, :event_type, :description, 
                         :adddate, :is_recurring, :recurring_start_date, :recurring_months, :recurring_count)";
                
                // Ek alanları ekle
                $data['recurring_start_date'] = date('Y-m-d', strtotime($_POST['recurring_start_date']));
                $data['recurring_months'] = intval($_POST['recurring_months']);
                $data['recurring_count'] = intval($_POST['recurring_count']);
            } else {
                $sql = "INSERT INTO finances 
                        (u_id, customer_id, category_id, method_id, price, event_type, description, 
                         adddate, is_recurring) 
                        VALUES 
                        (:u_id, :customer_id, :category_id, :method_id, :price, :event_type, :description, 
                         :adddate, :is_recurring)";
            }

            // Debug için veriyi logla
            error_log("SQL: " . $sql);
            error_log("Data: " . print_r($data, true));

            // Sorguyu çalıştır
            $result = $pia->insert($sql, $data);
            
            if($result){
                $pia->alertify('success', 'İşlem başarıyla gerçekleştirildi!', 'expense.php');
                exit;
            } else {
                error_log("Insert Error: " . print_r($pia->getLastError(), true));
                $pia->alertify('error', 'Veritabanı hatası oluştu!');
                exit;
            }

        } catch (Exception $e) {
            error_log("Exception in AddExpense: " . $e->getMessage());
            $pia->alertify('error', 'Bir hata oluştu: ' . $e->getMessage());
            exit;
        }
    } else {
        $pia->alertify('error', 'Geçersiz form gönderimi!');
        exit;
    }
?>