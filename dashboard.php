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

        <div class ="blank-wrapper">
            <div class="container pt-5">
                <div class="row d-flex justify-content-center pt-5">
                    <div class="col-md-3 offset-sm-2">
                        i love u
                    </div>
                    <div class="col-md-3 offset-sm-2">
                        i love u
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>