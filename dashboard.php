<?php
    require "functions.php";
    is_user_login();

    if(!is_user_verified())
    {
        header("Location: verify.php");
        die;
    }

    require('header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Dashboard</title>
    </head>

    <body>
    </body>
</html>