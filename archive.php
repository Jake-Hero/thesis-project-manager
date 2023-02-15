<?php
    require "./libs/functions.php";
    
    is_user_valid();
    is_user_login();

    $currentPage = 'archives';
    require('./libs/header.php');
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
        <script type="text/javascript" src="./js/lastseen.js"></script>
        <script type="text/javascript" src="./js/archive_filter.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script src="./bootstrap/js/bootstrap.min.js"></script>

        <title>Thesis & Capstone Manager - Archives</title> 

        <script>
            $(document).ready(function () {
                displayUpdatedList();

                $("#search").keyup(function() {

                    var name = $('#search').val();

                    if (name == "") 
                    {
                        displayUpdatedList();
                    }
                });

                $('#code_spinner').hide();
            });

            function displayUpdatedList() {
                $.ajax({
                    url:"./src/archive_list.php",
                    method:"POST",
                    beforeSend : function () {  
                        $('#code_spinner').show(); 
                    },
                    success:function(response)
                    {
                        $('#display').html(response);
                    },
                    complete : function () {  
                        $('#code_spinner').hide(); 
                    },
                })
            }
        </script>
    </head>

    <body>
        <div class="wrapper-follow">
            <div class="container mt-3 mb-3 bg-white border border-dark rounded-start rounded-end px-5 py-4" style="--bs-bg-opacity: .5;">
            
                <div class="col-lg-12">
                    <div class="text-center">
                        <h1 style="font-size: 35px; font-family: 'Lemon/Milk', sans-serif; color: white; -webkit-text-stroke: 1px black;">WELCOME TO ARCHIVES!</h1>
                        <h5 style="font-size: 14px;">View most of the submitted and published Thesis Research in the CCS Department.</h5>
                    
                        <?php if(!empty($_SESSION['success_message'])):?>
                            <div class="alert alert-success alert-dismissible d-flex align-items-center fade show">
                                <i class="fas fa-check-circle"></i>
                                <div class ="mx-3">
                                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']) ?>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <div class="mt-3 mb-3">
                            <a href="./upload_archive.php">
                                <button type="button" class="btn btn-primary btn-sm">
                                    Upload a Document
                                </button>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" id="search" class="col-sm-10 form-control" placeholder="Search ... (Title, Year Published)" style="text-align: center;">
                </div>

                <div class="mt-4">
                    <div id="code_spinner" class="loader"></div>
                    <div id="display"></div>
                </div>
            </div>
        </div>
    </body>
</html>