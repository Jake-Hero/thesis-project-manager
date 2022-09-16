<?php
    require "includes/functions.php";

    is_user_valid();
    $errors = array('fullname'=>'','email'=>'','username'=>'');

    if(is_user_login(false))
    {
        header("Location: dashboard.php");
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
        <?php require('head.php')?>
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
                            <form method="post" class="bg-white border border-dark px-5 py-4" style="--bs-bg-opacity: .5;">
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

                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="mt-3">
                                        <a href="signup.php" class="text-danger"><strong>Don't have an account?</strong></a>
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