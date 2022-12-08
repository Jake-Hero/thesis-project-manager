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
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Dashboard</title>
    </head>

    <body>
        <div class="wrapper">
            <div class="container-fluid mt-3 mb-3">
                <div class="container-fluid bg-white">
                    <p class="text-black">
                        Thesis & Capstone Project Manager was started as group collaboration project from the students of Lyceum of the Philippines University Batangas.
                        This group consisted (alphabetical order) of Mervin Jesse S. Dimaano, RV Angel B. Mayuga, Alliah J. Pureza and Justine Rainiel A. Ramos.
                    </p>

                    <p class="text-black">
                        The goal of this website is to make the research collaborations easy for students who has thesis research.
                    </p>
                </div>
            </div>
        </div>
    </body>
</html>