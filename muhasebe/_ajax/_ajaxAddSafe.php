<?php 
require('../_class/config.php'); 
require('../session.php');

// Hata ayıklama için
error_reporting(E_ALL);
ini_set('display_errors', 1);

if(isset($_POST['frm']) && $_POST['frm']=='frmAddSafe'){
    try {
        $title = str_replace(array("'", '"'), '', trim($pia->control($_POST['title'], 'text')));
        $type = str_replace(array("'", '"'), '', trim($pia->control($_POST['type'], 'text'))); 
        
        // Debug için POST verilerini logla
        error_log("POST verileri: " . print_r($_POST, true));
        
        // Kredi limiti kontrolü
        $credit_limit = 0;
        $statement_day = 'NULL';
        $due_day = 'NULL';
        $bank_name = "''";

        if($type == 'credit_card') {
            if(empty($_POST['credit_limit'])) {
                echo 'error: Kredi kartı için limit zorunludur';
                exit;
            }
            
            $credit_limit = floatval(str_replace(',', '.', $_POST['credit_limit']));
            $statement_day = !empty($_POST['statement_day']) ? intval($_POST['statement_day']) : 'NULL';
            $due_day = !empty($_POST['due_day']) ? intval($_POST['due_day']) : 'NULL';
            $bank_name = !empty($_POST['bank_name']) ? "'" . str_replace(array("'", '"'), '', trim($pia->control($_POST['bank_name'], 'text'))) . "'" : "''";
        }

        $sql = "INSERT INTO safes SET 
                u_id = " . $admin->id . ",
                title = '" . $title . "',
                type = '" . $type . "', 
                balance = 0.00,
                credit_limit = " . $credit_limit . ",
                statement_day = " . $statement_day . ",
                due_day = " . $due_day . ",
                bank_name = " . $bank_name . ",
                status = 1,
                created_at = '" . date('Y-m-d H:i:s') . "'";

        error_log("SQL sorgusu: " . $sql);

        // Query sonucunu detaylı logla
        $result = $pia->query($sql);
        error_log("Query result: " . print_r($result, true));
        
        if($result){
            echo 'success';
        } else {
            // MySQL hata mesajını logla
            error_log("MySQL Error: " . mysqli_error($pia->connection));
            echo 'error: insert failed - ' . mysqli_error($pia->connection);
        }

    } catch (Exception $e) {
        error_log("Hata: " . $e->getMessage());
        echo 'error: ' . $e->getMessage();
    }
} else {
    echo 'error: invalid form';
}
exit;
?>