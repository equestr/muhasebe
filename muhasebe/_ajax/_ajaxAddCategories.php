<?php 
    require('../_class/config.php'); 
    require('../session.php');

    if(isset($_POST['frm']) and $_POST['frm']=='frmAddCategories'){
        $title = $pia->control($_POST['title'], 'text');
        $type = $pia->control($_POST['type'], 'text'); // yeni eklenen type değişkeni
        
        if($pia->insert("INSERT INTO categories(title, type) VALUES($title, $type)")){
            $pia->alertify('success', 'İşlem başarıyla gerçekleştirildi!','categories.php');
        }else{
            $pia->alertify('error', 'Bir hata oluştu!');
        }
    } 
?>