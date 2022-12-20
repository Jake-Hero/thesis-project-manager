<?php
    $errors = array();

    require "./libs/functions.php";

    is_user_valid();
    is_user_login();

    if(is_user_verified())
    {
        header("Location: ./dashboard.php");
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
                header("Location: ./dashboard.php");
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

                    if($row['expiry'] > time())
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

                        $_SESSION['result_popup'] =
                        "
                            <script type=\"text/javascript\">
                                swal({
                                    title: \"Verification\",
                                    type: \"success\",
                                    text: \"You have successfully verified your account!\",
                                    allowOutsideClick: false,
                                    showConfirmButton: true,
                                    confirmButtonText: 'OK'
                                    });
                            </script>                        
                        ";

                        header("Location: ./profile.php");
                    }
                    else 
                    {
                        $query = "DELETE FROM verified WHERE email = :email";
                        $insert_stm = $con->prepare($query);
                        $insert_stm->bindValue(':email', $vars['email']);
                        $insert_stm->execute();

                        $errors['error_message'] = "The code has expired!";
                    }
                }
                else 
                {
                    $errors['error_message'] = "You have typed the wrong code!";
                }                
            }
        }
    }

    $vars['code'] = rand(pow(10, 5-1), pow(10, 5)-1);
    $email = $_SESSION['user']['email'];
    $vars['expiry'] = time() + (60 * 5); // 5 minutes expiration

    $query = "SELECT * FROM verified WHERE email = :email";
    $select_stm = $con->prepare($query);
    $select_stm->bindValue(':email', $email);
    $select_stm->execute();

    if($select_stm->rowCount() > 0)
    {
        $row = $select_stm->fetch(PDO::FETCH_ASSOC);
        $now = time();
        if($row['expiry'] < $now)
        {
            $query = "UPDATE verified SET code = :code, expiry = :expiry, email = :email WHERE email = :email";
            $insert_stm = $con->prepare($query);
            $insert_stm->execute(['code' => $vars['code'], 'expiry' => $vars['expiry'], 'email' => $email]);

            unset($_SESSION['error_message']);
            $_SESSION['message'] = "A code was sent to your email address. Check your <strong>inbox</strong> or the <strong>spam folder</strong>.";
            $message = "Your verification code is: ". $vars['code'];
            $message.= "\r\n\nPlease ignore this E-Mail if you aren't the one who requested for this code.";
            $message.= "\r\nThis message is automated, Please do not reply to this email.";
            $message = nl2br($message);
            
            send_mail($email, "Verify your account! - Verification Code", $message);  
        }
    }
    else 
    {
        $query = "INSERT INTO verified (code, expiry, email) VALUES(:code, :expiry, :email)";
        $insert_stm = $con->prepare($query);
        $insert_stm->execute(['code' => $vars['code'], 'expiry' => $vars['expiry'], 'email' => $email]);

        unset($_SESSION['error_message']);
        $_SESSION['message'] = "A code was sent to your email address. Check your <strong>inbox</strong> or the <strong>spam folder</strong>.";
        $message = "Your verification code is: ". $vars['code'];
        $message.= "\r\n\nPlease ignore this E-Mail if you aren't the one who requested for this code.";
        $message.= "\r\nThis message is automated, Please do not reply to this email.";
        $message = nl2br($message);

        send_mail($email, "Verify your account! - Verification Code", $message);  
    }

    require('libs/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <link rel="shortcut icon" type="image/jpg" href="./favicon.ico"/>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Verification</title>
    </head>

    <body>  
        <div class="wrapper">
            <section class="vh-100">
                <div class="container py-5 h-100">
                    <div class="row d-flex justify-content-sm-center justify-content-md-center justify-content-lg-center align-items-center h-100">
                        <div class="col-md-6">
                            <form method="post" enctype="multipart/form-data" class="bg-white px-4 py-3 border rounded-start rounded-end" style="--bs-bg-opacity: .5;">
                                <h1 class="border border-bottom text-center" style="font-size: 40px; font-family: 'Lemon/Milk', sans-serif; color: black;">Please Verify Your Account</h1>

                                <?php if(!empty($_SESSION['error_message'])): ?>
                                    <div class="alert alert-danger d-flex align-items-center fade show">
                                        <i class='fas fa-exclamation-triangle'></i>
                                        <div class ="mx-3">
                                            <?php echo $_SESSION['error_message']; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if(!empty($_SESSION['message'])): ?>
                                    <div class="alert alert-success d-flex align-items-center fade show">
                                        <div class ="mx-3">
                                            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="col">
                                    <label for="" class="text-center col col-form-label">Please put the code that you received from your email here.</label>
                                    <input type="text" name="code" class="col form-control" placeholder="Code">
                                </div>

                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="mt-3">
                                        <a href="./profile.php" class="text-danger"><strong>Go back to Edit My Profile</strong></a>
                                    </div>
                                </div>

                                <div class="row mt-5 mx-auto">
                                    <input type="submit" name="resend" value="Resend Code" class="rounded-pill btn btn-warning border border-light btn-lg">
                                    <input type="submit" name="submit" value="Verify" class="rounded-pill btn btn-warning border border-light btn-lg">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </body>
</html>