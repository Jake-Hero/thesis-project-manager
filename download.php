<?php
require 'libs/functions.php';

if(isset($_GET['file'])){

    $fileName  = basename($_GET['file']);
    $filePath  = "./uploads/group_" . $_GET['groupid'] . '/' . $fileName;

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.$fileName);
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    readfile($filePath);
    die;
} else {
    header("Location: " . ROOT_FOLDER . "/index.php");
    die;
}

exit();
?>