<?php
    require "./libs/functions.php";
    is_user_valid();
    
    if(is_user_login(false))
    {
        header("Location: ./dashboard.php");
        die;
    }

    // Visit Detection
    recordUserVisit();
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
        <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet" media="nope!" onload="this.media='all'">
        <link rel="stylesheet" href="./css/style.css">
        <link rel="shortcut icon" type="image/jpg" href="./favicon.ico"/>

        <title>Thesis & Capstone Manager - Index Page</title>
    </head>
    
    <body>
        <div class="wrapper">
            <div class="container-fluid vh-100">
                <div class="row d-flex justify-content-center align-items-center h-100">
                    
                    <div class="col-lg-4 col-xs-2">
                        <h1 style="font-size: 50px; font-family: 'Lemon/Milk', sans-serif; color: white;">THESIS AND CAPSTONE MANAGER</h1>
                        <h5 style="font-size: 14px; color: white;">A website for managing your thesis and capstone</h5>
                        <button id ="signup" onclick="document.location.href = './signup.php'" type="button" class="rounded-pill btn btn-warning border border-dark btn-lg btn-block">Sign Up</button>
                        <button id ="login" onclick="document.location.href = './login.php'" type="button" class="rounded-pill btn btn-warning border border-dark btn-lg">Login</button>
                    </div>

                    <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                        <img src="./assets/images/lpu-b-logo.png" alt="LPU-B Logo" width="210" height="210">
                        <img src="./assets/images/lpu-ccs-logo.png" alt="LPU-B Logo" width="210" height="210">
                    </div>
                    
                </div>
            </div>
        </div>
    </body>

    <?php require('./footer.php'); ?>
</html>