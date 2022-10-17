<?php
if(isset($_GET['file'])){

    $fileName  = basename($_GET['file']);
    $filePath  = "uploads/".$fileName;
    
    if(!empty($fileName) && file_exists($filePath)){
        header("Cache-Control: public");
        header("Content-Description: File Transfer");
        header("Content-Disposition: attachment; filename=$fileName");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: binary");
        readfile($filePath);
        exit;
    } else {
        exit;
    }
} else {
    header("Location: " . ROOT_FOLDER . "/index.php");
    exit;
}

die;
?>