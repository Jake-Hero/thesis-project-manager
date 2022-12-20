<?php
    require "./libs/functions.php";

    is_user_valid();
    $errors = array('fullname'=>'','email'=>'','username'=>'');

    if(is_user_login(false))
    {
        header("Location: ./dashboard.php");
        die;
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['login']))
        {
            $errors = login_user($_POST);
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
        <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet" media="nope!" onload="this.media='all'">
        <link rel="stylesheet" href="./css/style.css">
        <link rel="shortcut icon" type="image/jpg" href="./favicon.ico"/>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" media="nope!" onload="this.media='all'" />
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" media="nope!" onload="this.media='all'"/>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript" src="./js/custom.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.11/dist/sweetalert2.all.js"></script>
        <script src="./bootstrap/js/bootstrap.min.js"></script>

        <title>Thesis & Capstone Manager - Login</title>
    </head>

    <body>
        <div class="wrapper">
            <section class="vh-100">
                <?php 
                    if(isset($_SESSION['result_popup']))
                    {
                        echo $_SESSION['result_popup'];
                    }
                ?>

                <div class="container-fluid h-100">
                    <div class="row d-flex justify-content-center align-items-center h-100">
                        <div class="col-lg-4 col-xs-2">
                            <h1 style="font-size: 50px; font-family: 'Lemon/Milk', sans-serif; color: white;">THESIS AND CAPSTONE MANAGER</h1>
                            <h5 style="font-size: 14px; color: white;">A website for managing your thesis and capstone</h5>
                        </div>

                        <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                            <form method="post" class="bg-white border border-dark rounded-start rounded-end px-5 py-4" style="--bs-bg-opacity: .5;">
                                <div class="form-group">
                                    <?php if(!empty($errors['all'])):?>
                                        <div class="alert alert-danger alert-dismissible d-flex align-items-center fade show">
                                            <i class='fas fa-exclamation-triangle'></i>
                                            <div class ="mx-3">
                                                <?php echo $errors['all'];?>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="" class="col col-form-label">Username or Email</label>

                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fa-solid fa-envelope"></i>
                                        </span>

                                        <?php if(!empty($errors['username'])):?>
                                            <input type="text" name ="username" class="col-sm-10 form-control is-invalid" placeholder="Username or Email">
                                        <?php else: ?>   
                                            <input type="text" name ="username" class="col-sm-10 form-control" placeholder="Username or Email">
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="" class="col col-form-label">Password</label>
                                    
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fa-solid fa-key"></i>
                                        </span>
                                        
                                        <?php if(!empty($errors['password'])):?>
                                            <input type="password" name="password" class="col-sm-10 form-control is-invalid" placeholder="Password">
                                        <?php else: ?>   
                                            <input type="password" name="password" class="col-sm-10 form-control" placeholder="Password">
                                        <?php endif; ?>

                                        <span class="input-group-text">
                                            <div class="toggle_hide_password">
                                                <i class="far fa-eye-slash" id="togglePassword" style="cursor: pointer"></i>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <div class="row mt-5 mx-auto">
                                    <button type="submit" name="login" class="rounded-pill btn btn-warning border border-light btn-lg">Login</button>
                                </div>

                                <div class="align-items-center">
                                    <div class="row mt-3">
                                        <a href="./forgot_password.php" class="text-danger"><strong>Forgot your password?</strong></a>
                                    </div>
                                    <div class="row mt-2">
                                        <a href="./signup.php" class="text-danger"><strong>Don't have an account?</strong></a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php unset($_SESSION['result_popup']); ?>
    </body>
</html>