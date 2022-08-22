<?php
    require "mail.php";
    require "functions.php";
    is_user_login();

    if(is_user_verified())
    {
        header("Location: dashboard.php");
        die;
    }

    $errors = array();

    if($_SERVER['REQUEST_METHOD'] == "POST")
    {
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
                    $cur_time = time();

                    if($row['expiry'] > $cur_time)
                    {
                        $query = "UPDATE users SET email_verified = :email WHERE id = :id";
                        $insert_stm = $con->prepare($query);
                        $insert_stm->bindValue(':email', $vars['email']);
                        $insert_stm->bindValue(':id', $_SESSION['user']['id']);
                        $insert_stm->execute();

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

    if($_SERVER['REQUEST_METHOD'] == "GET" && !is_user_verified())
    {
        $vars = array();

        $vars['code'] = rand(pow(10, 5-1), pow(10, 5)-1);
        $vars['expiry'] = time() + (60 * 5); // 5 minutes expiration
        $vars['email'] = $_SESSION['user']['email'];

        $query = "SELECT * FROM verified WHERE email = :email";
        $select_stm = $con->prepare($query);
        $select_stm->bindValue(':email', $vars['email']);
        $select_stm->execute();

        if($select_stm->rowCount() > 0)
        {
            $query = "UPDATE verified SET code = :code, expiry = :expiry, email = :email WHERE email = :email";
            $insert_stm = $con->prepare($query);
            $insert_stm->execute($vars);
        }
        else
        {
            $query = "INSERT INTO verified (code, expiry, email) VALUES(:code, :expiry, :email)";
            $insert_stm = $con->prepare($query);
            $insert_stm->execute($vars);
        }

        send_mail($vars['email'], "Test Subject", "Test Message");
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
                                if(isset($_SESSION['result']))
                                    echo $_SESSION['result'];

                                unset($_SESSION['result']);
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

                            <div class="alert alert-success d-flex align-items-center fade show">
                                <div class ="mx-3">
                                    A code was sent to your email address. Check your <strong>inbox</strong> or the <strong>spam folder</strong>.
                                </div>
                            </div>

                            <div class="col">
                                <label for="" class="col col-form-label">Please put code here</label>
                                <input type="text" name="code" class="col form-control" placeholder="Code">
                            </div>

                            <div class="row mt-5 mx-auto">
                                    <input type="submit" name="submit" value="Verify" class="rounded-pill btn btn-warning border border-light btn-lg">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>