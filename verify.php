<?php
    $errors = array();

    require "functions.php";
    is_user_login();

    if(is_user_verified())
    {
        header("Location: dashboard.php");
        die;
    }

    if($_SERVER['REQUEST_METHOD'] == "POST")
    {
        if(isset($_POST['resend']))
        {
            $errors = sendVerificationCode();
        }
        if(isset($_POST['submit']))
        {
            if(is_user_verified())
            {
                header("Location: dashboard.php");
                die;
            }
            else
            {
                $vars = array();
                $vars['code'] = $_POST['code'];
                $vars['email'] = $_SESSION['user']['email'];

                $query = "SELECT * FROM verified WHERE (code = :code AND email = :email) limit 1;";
                $select_stm = $con->prepare($query);
                $select_stm->execute($vars);

                if($select_stm->rowCount() > 0)
                {
                    $row = $select_stm->fetch(PDO::FETCH_ASSOC);

                    if(time() > $row['expiry'])
                    {
                        $query = "UPDATE users SET email_verified = :email WHERE id = :id";
                        $insert_stm = $con->prepare($query);
                        $insert_stm->bindValue(':email', $vars['email']);
                        $insert_stm->bindValue(':id', $_SESSION['user']['id']);
                        $insert_stm->execute();

                        $query = "DELETE FROM verified WHERE code = :code";
                        $delStm = $con->prepare($query);
                        $delStm->bindValue(':code', $vars['code']);
                        $delStm->execute();

                        header("Location: dashboard.php");
                    }
                    else 
                    {
                        $query = "DELETE FROM verified WHERE email = :email";
                        $insert_stm = $con->prepare($query);
                        $insert_stm->bindValue(':email', $vars['email']);
                        $insert_stm->execute();

                        $errors['fail'] = "The code has expired!";
                    }
                }
                else 
                {
                    $errors['fail'] = "You have typed the wrong code!";
                }                
            }
        }
    }

    require('header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Admin Panel</title>
    </head>

    <body>  
        <div class="wrapper">
            <div class="container h-100">
                <div class="row d-flex justify-content-sm-center justify-content-md-center justify-content-lg-center align-items-center h-100">
                    <div class="col-md-6">
                        <form method="post" enctype="multipart/form-data" class="profile-form px-4 py-3 border border-dark">
                            <h1>Verification Code</h1>
                            <?php 
                                if(isset($_SESSION['result_popup']))
                                    echo $_SESSION['result_popup'];

                                unset($_SESSION['result_popup']);
                            ?>

                            <?php if(!empty($errors['fail'])): ?>
                                <div class="alert alert-danger alert-dismissible d-flex align-items-center fade show">
                                    <i class='fas fa-exclamation-triangle'></i>
                                    <div class ="mx-3">
                                        <?php echo $errors['fail'];?>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if(!empty($_SESSION['message'])): ?>
                                <div class="alert alert-success d-flex align-items-center fade show">
                                    <div class ="mx-3">
                                        <?php echo $_SESSION['message']; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if(!empty($_SESSION['message_error'])): ?>
                                <div class="alert alert-danger d-flex align-items-center fade show">
                                    <i class='fas fa-exclamation-triangle'></i>
                                    <div class ="mx-3">
                                        <?php echo $_SESSION['message_error'];?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="col">
                                <label for="" class="col col-form-label">Please put code here</label>
                                <input type="text" name="code" class="col form-control" placeholder="Code">
                            </div>

                            <div class="row mt-5 mx-auto">
                                <input type="submit" name="resend" value="Resend Code" class="rounded-pill btn btn-warning border border-light btn-lg">
                                <input type="submit" name="submit" value="Verify" class="rounded-pill btn btn-warning border border-light btn-lg">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>