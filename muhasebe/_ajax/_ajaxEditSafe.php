<?php 
require('../_class/config.php'); 
require('../session.php');

if(isset($_POST['frm']) && $_POST['frm']=='frmEditSafe'){
    $edit_id = $pia->control($_POST['edit_id'], 'int');
    $title = $pia->control($_POST['title'], 'text');
    $description = $pia->control($_POST['description'], 'text');
    
    if($pia->update("UPDATE safes SET title='$title', description='$description' WHERE id=$edit_id")){
        $pia->alertify('success', 'Kasa başarıyla güncellendi!','safe.php');
    }else{
        $pia->alertify('error', 'Bir hata oluştu!');
    }
} 
?>