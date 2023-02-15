<?php
    require "./libs/functions.php";
    
    is_user_valid();
    is_user_login();

    $currentPage = 'archives';
    require('./libs/header.php');

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['createarchive']))
        {
            createArchive();
        }
    }

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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script src="./bootstrap/js/bootstrap.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js" integrity="sha512-K/oyQtMXpxI4+K0W7H25UopjM8pzq0yrVdFdG21Fh5dBe91I40pDd9A4lzNlHPHBIP2cwZuoxaUSX0GJSObvGA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css" integrity="sha512-MQXduO8IQnJVq1qmySpN87QQkiR1bZHtorbJBD0tzy7/0U9+YIC93QWHeGTEoojMVHWWNkoCp8V6OzVSYrX0oQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        <title>Thesis & Capstone Manager - Archives</title> 
    </head>

    <body>
        <div class="wrapper">
            <div class="container mt-3 mb-3 bg-white border border-dark rounded-start rounded-end px-5 py-4" style="--bs-bg-opacity: .5;">
            
                <div class="col-lg-12">
                    <form method="post" enctype="multipart/form-data">
                        <div class="text-center">
                            <h1 style="font-size: 35px; font-family: 'Lemon/Milk', sans-serif; color: white; -webkit-text-stroke: 1px black;">Create a new Archive Document.</h1>
                            <h5 style="font-size: 14px;">Please specify the details about the document before we display it in The Archives.</h5>
                        
                            <div class="mx-auto col-10 col-md-8 col-lg-6">
                                
                                <?php if(!empty($_SESSION['error_message'])):?>
                                <div class="alert alert-danger alert-dismissible d-flex align-items-center fade show">
                                    <i class='fas fa-exclamation-triangle'></i>
                                    <div class ="mx-3">
                                        <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']) ?>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="" class="mt-4">Research Title</label>
                                    <div class="input-group"> 
                                        <input type="text" name="title" class="col-sm-10 form-control" placeholder="Research Title">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="" class="mt-4">Published Year</label>
                                    <div class="input-group"> 
                                        <select name="year" class="form-select">
                                            <?php 
                                                for($i = date('Y'); $i >= 2000; $i--){
                                                    echo "<option value='". $i ."'>$i</option>";
                                                }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="" class="mt-4">Department</label>
                                    <div class="input-group"> 
                                        <select name="department" class="form-select">
                                            <option value="<?php echo DEPT_IT; ?>">Information Technology (IT)</option>
                                            <option value="<?php echo DEPT_CS; ?>">Computer Science (CS)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="" class="mt-4">Upload Document</label>
                                    <input type="file" name="document" id="document" class="col-sm-10 form-control" accept="application/pdf,application">
                                </div>
               
                                <div class="row mt-5 mx-auto">
                                    <div class="col text-center">
                                        <input type="submit" name="createarchive" class="btn btn-lg btn-primary" value="Create Archive">
                                    </div>
                                </div>
                                
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>