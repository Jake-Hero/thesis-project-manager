<?php
    require "libs/functions.php";
    is_user_valid();
    is_user_login();

    $currentPage = 'about';
    require('libs/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <link rel="shortcut icon" type="image/jpg" href="./favicon.ico"/>
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
        <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet" media="nope!" onload="this.media='all'">
        <link rel="stylesheet" href="./css/style.css">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script src="./bootstrap/js/bootstrap.min.js"></script>

        <title>Thesis & Capstone Manager - Dashboard</title>
    </head>

    <body>
        <div class="wrapper">
            <div class="container mt-3 mb-3 bg-white border border-dark rounded-start rounded-end px-5 py-4" style="--bs-bg-opacity: .5;">
                <div class="row d-flex justify-content-center align-items-center h-90">
                    <div class="col-md-5">
                        <img src="./assets/images/lpu-b-logo.png" alt="LPU-B Logo" width="180" height="180">
                        <img src="./assets/images/lpu-ccs-logo.png" alt="LPU-B Logo" width="180" height="180">
                    </div>
                    
                    <div class="col-lg-6">
                        <div class="text-end">
                            <h1 style="font-size: 35px; font-family: 'Lemon/Milk', sans-serif; color: white; -webkit-text-stroke: 1px black;">WELCOME TO THESIS AND CAPSTONE MANAGER</h1>
                            <h5 style="font-size: 14px;">A website for managing your thesis and capstone</h5>

                            <div class="mt-5">
                                <p class="text-black" style="font-size: 18px; font-family: 'Sans-Serif';">
                                    Thesis & Capstone Project Manager was started as group collaboration project from the students of Lyceum of the Philippines University Batangas.
                                    This group consisted (alphabetical order) of Mervin Jesse S. Dimaano, RV Angel B. Mayuga, Alliah J. Pureza and Justine Rainiel A. Ramos.
                                </p>

                                <p class="text-black" style="font-size: 18px; font-family: 'Sans-Serif';">
                                    Our goal is to make the research collaborations easy for students who has thesis research.
                                </p>

                                <p class="text-black" style="font-size: 18px; font-family: 'Sans-Serif';">
                                    Please give it a try, and thank you in advance for joining our beta test.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>
                <h1 class="text-center" style="font-size: 35px; font-family: 'Lemon/Milk', sans-serif; color: white; -webkit-text-stroke: 1px black;">GROUP 3</h1>
                <hr>

                <div class="row d-flex justify-content-center align-items-center">
                    <div class="col">
                        <div class='jumbotron rounded-start rounded-end text-center'>
                            <div class='container bg-secondary' style="--bs-bg-opacity: .5;">
                                <img class="mt-3" src="./assets/images/dimaano.jpg" alt="Mervin" width="160" height="160">
                                <p style="margin-top: 3vh; font-family: 'Sans-serif';">Mervin Jesse S. Dimaano</p>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class='jumbotron rounded-start rounded-end text-center'>
                            <div class='container bg-secondary' style="--bs-bg-opacity: .5;">
                                <img class="mt-3" src="./assets/images/mayuga.png" alt="RV Angel" width="160" height="160">
                                <p style="margin-top: 3vh; font-family: 'Sans-serif';">RV Angel B. Mayuga</p>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class='jumbotron rounded-start rounded-end text-center'>
                            <div class='container bg-secondary' style="--bs-bg-opacity: .5;">
                                <img class="mt-3" src="./assets/images/pureza.png" alt="Alliah" width="160" height="160">
                                <p style="margin-top: 3vh; font-family: 'Sans-serif';">Alliah J. Pureza</p>
                            </div>
                        </div>
                    </div>

                    <div class="col">
                        <div class='jumbotron rounded-start rounded-end text-center'>
                            <div class='container bg-secondary' style="--bs-bg-opacity: .5;">
                                <img class="mt-3" src="./assets/images/ramos.png" alt="Justine" width="160" height="160">
                                <p style="margin-top: 3vh; font-family: 'Sans-serif';">Justine Rainiel A. Ramos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>