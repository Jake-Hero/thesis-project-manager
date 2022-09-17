<?php
    require "includes/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] >= ROLE_ADVISOR)
    {
        header("Location: " . ROOT_FOLDER . "/admin/group.php");
        die;
    }

    $currentPage = 'group';
    require('includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Group</title>
    </head>

    <body>
    </body>
</html>