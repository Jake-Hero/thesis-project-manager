<?php
    require "functions.php";

    $errors = array('fullname'=>'','email'=>'','username'=>'');

    if(is_user_login(false))
    {
        header("Location: dashboard.php");
        die;
    }

    if(isset($_POST['signup']))
    {
        $errors = signup_user($_POST);
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Sign Up</title>
    </head>

    <body>
        <div class="wrapper">
            <div class="container h-100 px-4">
                <div class="row align-items-center h-100">
                    <div class="col-lg-4 col-xs-2">
                        <?php 
                            if(isset($_SESSION['result'])) 
                                echo $_SESSION['result']; 
                        
                            unset($_SESSION['result']);
                        ?>
                        <h1 style="font-size: 50px; font-family: 'Lemon/Milk', sans-serif; color: white;">THESIS AND CAPSTONE MANAGER</h1>
                        <h5 style="font-size: 14px; color: white;">A website for managing your thesis and capstone</h5>
                    </div>

                    <div class="col">
                        <div class="d-flex justify-content-end">
                            <form method="post" id="reg-form" class="reg-log-form border border-dark px-5 py-4">
                                <div class="form-group">
                                    <label for="" class="col col-form-label">Full Name</label>
                                    <?php if(!empty($errors['fullname'])):?>
                                        <div class="form-group">
                                            <strong class="text-danger">
                                                <?php echo '<label for="" class="col col-form-label">' .$errors['fullname']. '</label>';?>
                                            </strong>
                                        </div>
                                        <input type="text" name ="fullname" class="col-sm-10 form-control is-invalid" placeholder="Full Name">
                                    <?php else: ?>
                                        <input type="text" name ="fullname" class="col-sm-10 form-control" placeholder="Full Name">
                                    <?php endif;?>
                                </div>

                                <div class="form-group">
                                    <label for="" class="col col-form-label">Email</label>
                                    <?php if(!empty($errors['email'])):?>
                                        <div class="form-group">
                                            <strong class="text-danger">
                                                <?php echo '<label for="" class="col col-form-label">' .$errors['email']. '</label>';?>
                                            </strong>
                                        </div>
                                        <input type="text" name ="email" class="col-sm-10 form-control is-invalid" placeholder="youremail@domain">
                                    <?php else: ?>        
                                        <input type="text" name ="email" class="col-sm-10 form-control" placeholder="youremail@domain">
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="" class="col col-form-label">Username</label>
                                    <?php if(!empty($errors['username'])):?>
                                        <div class="form-group">
                                            <strong class="text-danger">
                                                <?php echo '<label for="" class="col col-form-label">' .$errors['username']. '</label>';?>
                                            </strong>
                                        </div>
                                        <input type="text" name ="username" class="col-sm-10 form-control is-invalid" placeholder="Username">
                                    <?php else: ?>   
                                        <input type="text" name ="username" class="col-sm-10 form-control" placeholder="Username">
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="" class="col col-form-label">Password</label>
                                    <?php if(!empty($errors['password'])):?>
                                        <div class="form-group">
                                            <strong class="text-danger">
                                                <?php echo '<label for="" class="col col-form-label">' .$errors['password']. '</label>';?>
                                            </strong>
                                        </div>
                                        <input type="password" name="password" class="col-sm-10 form-control is-invalid" placeholder="Password">
                                    <?php else: ?>
                                        <input type="password" name="password" class="col-sm-10 form-control" placeholder="Password">
                                    <?php endif; ?>
                                </div>

                                <div class="row mt-5 mx-auto">
                                    <input type="submit" id="signup" name="signup" value="Sign Up" class="rounded-pill btn btn-warning border border-light btn-lg">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>