<?php
    require "functions.php";
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: dashboard.php");
        die;
    }

    if(!is_user_verified())
    {
        header("Location: verify.php");
        die;
    }

    require('header.php');
    require('adminmenu.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Admin Panel</title>
    </head>

    <body>  
    </body>
</html>