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
        <?php 
            if(isset($_SESSION['result_popup']))
                echo $_SESSION['result_popup'];

            unset($_SESSION['result_popup']);
        ?>

        <div class ="wrapper">
            <div class="container h-100">
                <div class="row d-flex justify-content-sm-center justify-content-md-center justify-content-lg-center row justify-content-between h-100"> 
                    <div class="profile-form px-4 py-3 border border-dark">
                        test
                    </div>
                    <div class="profile-form px-4 py-3 border border-dark">
                        test
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>