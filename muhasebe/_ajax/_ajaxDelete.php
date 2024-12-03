<?php 
    require('../_class/config.php'); 
    require('../session.php');
    
    if(isset($_POST['table']) and isset($_POST['value'])){
        $delete_id = $pia->control($_POST['value'], 'int'); 
        $table = trim($pia->control($_POST['table'], 'text'),"'"); 

        // Eğer finances tablosundan silme işlemi ise
        if($table == 'finances') {
            // Silinmeden önce kaydı ve kasa bilgisini alalım
            $finance = $pia->get_row("SELECT * FROM finances WHERE id = $delete_id");
            if($finance) {
                $safe = $pia->get_row("SELECT * FROM safes WHERE id = $finance->safe_id");
                if($safe) {
                    // event_type 0 ise gider, 1 ise gelir
                    if($finance->event_type == 0) {
                        // Gider siliniyorsa bakiyeye ekle
                        $new_balance = $safe->balance + $finance->price;
                    } else {
                        // Gelir siliniyorsa bakiyeden çıkar
                        $new_balance = $safe->balance - $finance->price;
                    }
                    // Kasayı güncelle
                    $pia->query("UPDATE safes SET balance = $new_balance WHERE id = $safe->id");
                }
            }
        }

        // Orijinal silme işlemi
        $pia->exec("DELETE FROM $table WHERE id=$delete_id");
        $pia->alertify('success', 'Veri başarıyla silindi!');
    } 
?>