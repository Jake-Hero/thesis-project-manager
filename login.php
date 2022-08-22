<?php
    require "functions.php";

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
            <div class="container h-100 px-4">
                <div class="row align-items-center h-100">
                    <div class="col-lg-4 col-xs-2">
                        <h1 style="font-size: 50px; font-family: 'Lemon/Milk', sans-serif; color: white;">THESIS AND CAPSTONE MANAGER</h1>
                        <h5 style="font-size: 14px; color: white;">A website for managing your thesis and capstone</h5>
                    </div>

                    <div class="col">
                        <div class="d-flex justify-content-end">
                            <form method="post" class="reg-log-form border border-dark px-5 py-4">
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
                                    <label for="" class="col col-form-label">Username/Email</label>
                                    <?php if(!empty($errors['username'])):?>
                                        <strong class="text-danger">
                                            <?php echo '<label for="" class="col col-form-label">' .$errors['username']. '</label>';?>
                                        </strong>
                                        <input type="text" name ="username" class="col-sm-10 form-control is-invalid" placeholder="Username or Email">
                                    <?php else: ?>   
                                        <input type="text" name ="username" class="col-sm-10 form-control" placeholder="Username or Email">
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="" class="col col-form-label">Password</label>
                                    <?php if(!empty($errors['username'])):?>
                                        <strong class="text-danger">
                                            <?php echo '<label for="" class="col col-form-label">' .$errors['password']. '</label>';?>
                                        </strong>
                                        <input type="password" name="password" class="col-sm-10 form-control is-invalid" placeholder="Password">
                                    <?php else: ?>   
                                        <input type="password" name="password" class="col-sm-10 form-control" placeholder="Password">
                                    <?php endif; ?>
                                </div>

                                <div class="row mt-5 mx-auto">
                                    <input type="submit" name="login" value="Login" class="rounded-pill btn btn-warning border border-light btn-lg">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>