<?php
    $errors = array();

    require "./libs/functions.php";

    is_user_valid();

    if(is_user_login(false))
    {
        header("Location: ./dashboard.php");
        die;
    }

    if($_SERVER['REQUEST_METHOD'] == "POST")
    {
        if(isset($_POST['submit']))
        {
            if(empty($_POST['forgot_field'])) {
                $_SESSION['error_message'] = "You haven't fill up the field yet!";
            } else {
                $username = NULL;
                $email = NULL;

                if(!filter_var($_POST['forgot_field'], FILTER_VALIDATE_EMAIL))
                    $username = $_POST['forgot_field'];
                else 
                    $email = filter_var($_POST['forgot_field'], FILTER_VALIDATE_EMAIL);

                $query = "SELECT email FROM users WHERE username = :username OR email = :email";
                $selectStmt = $con->prepare($query);
                $selectStmt->execute(['username' => $username, 'email' => $email]);
                $data_email = $selectStmt->fetch();

                if($selectStmt->rowCount() > 0)
                {
                    $token = bin2hex(random_bytes(50));
                    $expiration = time() + (60 * 5); // 5 minutes expiration

                    $query = "SELECT * FROM password_reset WHERE username_email = :username OR username_email = :email LIMiT 1;";
                    $countStmt = $con->prepare($query);
                    $countStmt->execute(['username' => $username, 'email' => $email]);

                    if($countStmt->rowCount() > 0)
                    {
                        $query = "UPDATE password_reset SET username_email = :username_email, token = :token, expiration = :expiration WHERE username_email = :username OR username_email = :email";
                        $insertStmt = $con->prepare($query);     
                        $insertStmt->bindParam('username', $username);
                        $insertStmt->bindParam('email', $email);                   
                    }
                    else 
                    {
                        $query = "INSERT INTO password_reset (username_email, token, expiration) VALUES(:username_email, :token, :expiration)";
                        $insertStmt = $con->prepare($query);
                    }

                    $insertStmt->bindValue('username_email', (!empty($username)) ? $username : $email);
                    $insertStmt->bindValue('token', $token);
                    $insertStmt->bindValue('expiration', $expiration);
                    $insertStmt->execute();

                    $message =  "Good Day!";
                    $message.=  "\r\n\nYou have requested to have your account's password reset. Please click the link below to reset your password";
                    $message.=  "\r\n<strong>If this is not you, Please ignore this email to prevent your password from getting reset!</strong>";
                    $message.=  "\r\nCopy the link provided in this email and paste in the Browser URL.";
                    $message.=  "\r\n\nhttps://thesiscapstonemanager.com/reset_password.php?token=" . $token;
                    $message.=  "\r\n\n<strong>This link is only valid for 5 minutes</strong>";
                    $message = nl2br($message);
            
                    send_mail($data_email['email'], "Forgot Password", $message);

                    $_SESSION['success_message'] = "An email containing the reset password link was sent, Please check your inbox or spam folder.";
                }
                else 
                {
                    $_SESSION['error_message'] = "That username or email doesn't exist.";
                }
            }
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

        <title>Thesis & Capstone Manager - Forgot Password</title>
    </head>

    <body>  
        <div class="wrapper">
            <section class="vh-100">
                <div class="container py-5 h-100">
                    <div class="row d-flex justify-content-sm-center justify-content-md-center justify-content-lg-center align-items-center h-100">
                        <div class="col-md-6">
                            <form method="post" enctype="multipart/form-data" class="bg-white px-4 py-3 border border-dark rounded-start rounded-end" style="--bs-bg-opacity: .5;">
                                <h1 class="text-center mb-3">Forgot Password</h1>

                                <?php if(!empty($_SESSION['success_message'])): ?>
                                <div class="alert alert-success alert-dismissible d-flex align-items-center fade show">
                                    <i class="fas fa-check-circle"></i>
                                    <div class ="mx-3">
                                        <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']) ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if(!empty($_SESSION['error_message'])): ?>
                                    <div class="alert alert-danger d-flex align-items-center fade show">
                                        <i class='fas fa-exclamation-triangle'></i>
                                        <div class ="mx-3">
                                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="col">
                                    <label for="" class="col col-form-label">Please put the email or username of your forgotten account</label>
                                    <input type="text" name="forgot_field" class="col form-control" placeholder="Email or Username">
                                </div>

                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="mt-3">
                                        <a href="./login.php" class="text-danger"><strong>Go back to Login Page</strong></a>
                                    </div>
                                </div>

                                <div class="row mt-3 mx-auto">
                                    <input type="submit" name="submit" value="Submit" class="rounded-pill btn btn-warning border border-light btn-lg">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </body>
</html>