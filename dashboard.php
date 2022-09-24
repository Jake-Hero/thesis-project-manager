<?php
    require "includes/functions.php";
    is_user_valid();
    is_user_login();
    
    // Visit Detection
    recordUserVisit();

    $currentPage = 'dashboard';
    require('includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Dashboard</title>
    </head>

    <body>
        <div class="grey-wrapper">
            <?php 
                if(!is_user_verified())
                {
                    echo 
                    "
                        <script type=\"text/javascript\">
                        swal({
                            title: \"Verification\",
                            type: \"warning\",
                            text: \"You are not verified yet, Please verify your account via 'Edit My Profile'.\",
                            allowOutsideClick: false,
                            showConfirmButton: true,
                            confirmButtonText: 'OK'
                            });
                        </script>   
                    "; 
                }
            ?>
        </div>
    </body>
</html>