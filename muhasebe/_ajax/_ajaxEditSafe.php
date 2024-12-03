<?php 
require('../_class/config.php'); 
require('../session.php');

if(isset($_POST['frm']) && $_POST['frm']=='frmEditSafe'){
    try {
        $edit_id = $pia->control($_POST['edit_id'], 'int');
        $title = str_replace(array("'", '"'), '', trim($pia->control($_POST['title'], 'text')));
        $type = str_replace(array("'", '"'), '', trim($pia->control($_POST['type'], 'text'))); 
        
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

        $sql = "UPDATE safes SET 
                title = '" . $title . "',
                type = '" . $type . "', 
                credit_limit = " . $credit_limit . ",
                statement_day = " . $statement_day . ",
                due_day = " . $due_day . ",
                bank_name = " . $bank_name . "
                WHERE id = " . $edit_id . " 
                AND u_id = " . $admin->id;

        error_log("SQL sorgusu: " . $sql);

        $result = $pia->query($sql);
        
        if($result){
            echo 'success';
        } else {
            echo 'error: update failed';
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