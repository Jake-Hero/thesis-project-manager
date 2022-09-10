<?php
    require "includes/functions.php";

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
            <section class="vh-100">
                <div class="container-fluid h-100">
                    <div class="row d-flex justify-content-center align-items-center h-100">
                        
                        <div class="col-lg-4 col-xs-2">
                            <h1 style="font-size: 50px; font-family: 'Lemon/Milk', sans-serif; color: white;">THESIS AND CAPSTONE MANAGER</h1>
                            <h5 style="font-size: 14px; color: white;">A website for managing your thesis and capstone</h5>
                            <button id ="signup" onclick="document.location.href = 'signup.php'" type="button" class="rounded-pill btn btn-warning border border-dark btn-lg btn-block">Sign Up</button>
                            <button id ="login" onclick="document.location.href = 'login.php'" type="button" class="rounded-pill btn btn-warning border border-dark btn-lg">Login</button>
                        </div>

                        <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                            <img src="assets/images/lpu-b-logo.png" alt="LPU-B Logo" width="210vw" height="210vh">
                            <img src="assets/images/lpu-ccs-logo.png" alt="LPU-B Logo" width="210vw" height="210vh">
                        </div>
                        
                    </div>
                </div>
            </section>
        </div>
    </body>

    <?php require('footer.php'); ?>
</html>