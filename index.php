<?php
    require "functions.php";

    if(is_user_login(false))
    {
        header("Location: dashboard.php");
        die;
    }

    // Visit Detection
    recordUserVisit();
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Index Page</title>
    </head>
    
    <body>
        <div class="wrapper">
            <div class="container h-100 px-4">
                <div class="row align-items-center h-100 gx-5">
                    <div class="col">
                        <h1 style="font-size: 50px; font-family: 'Lemon/Milk', sans-serif; color: white;">THESIS AND CAPSTONE MANAGER</h1>
                        <h5 style="font-size: 14px; color: white;">A website for managing your thesis and capstone</h5>
                        <button id ="signup" onclick="document.location.href = 'signup.php'" type="button" class="rounded-pill btn btn-warning border border-dark btn-lg btn-block">Sign Up</button>
                        <button id ="login" onclick="document.location.href = 'login.php'" type="button" class="rounded-pill btn btn-warning border border-dark btn-lg">Login</button>
                    </div>

                    <div class="col ms-auto">
                        <img src="./images/lpu-b-logo.png" alt="LPU-B Logo" width="210vw" height="210vh">
                        <img src="./images/lpu-ccs-logo.png" alt="LPU-B Logo" width="210vw" height="210vh">
                    </div>
                </div>
            </div>
        </div>
    </body>

    <?php require('footer.php'); ?>
</html>