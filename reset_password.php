<?php 

require "libs/functions.php";

if(is_user_login(false))
{
    header("Location: " . ROOT_FOLDER . "/dashboard.php");
    die;
}

$token = NULL;

if(isset($_GET['token']))
{
    $token = $_GET['token'];

    $query = "SELECT * FROM password_reset WHERE token = :token";
    $countStmt = $con->prepare($query);
    $countStmt->execute(['token' => $token]); 

    if($countStmt->rowCount() > 0) {
        $row = $countStmt->fetch();

        if($row['expiration'] < time())
        {
            $_SESSION['error_load'] = "The link has expired. Your link has exceed the 5 minutes mark, get a new link.";
        
            $query = "DELETE FROM password_reset WHERE token = :token";
            $deleteStmt = $con->prepare($query);
            $deleteStmt->execute(['token' => $token]); 
        }

    } else {
        $_SESSION['error_load'] = "The link has expired. You may have not copy the right link from the email or you have already used the link.";
    }
}
else 
    $_SESSION['error_load'] = "The link has expired. You may have not copy the right link from the email or you have already used the link.";

if(!empty($_SESSION['error_load'])): ?>
    <div class="bg-white px-4 py-3 border border-dark" style="--bs-bg-opacity: .5;">
        <h1 class="text-center mb-5">Invalid or Expired Link</h1>

        <p class="text-center">You will be redirected in 3 seconds, click the link below if you want to be redirected instantly.</p>

        <div class="alert alert-danger d-flex align-items-center fade show">
            <i class='fas fa-exclamation-triangle'></i>
            <div class ="mx-3">
                <?php echo $_SESSION['error_load']; unset($_SESSION['error_load']); ?>
            </div>
        </div>

        <div class="text-center">
            <a href="<?php echo ROOT_FOLDER; ?>/forgot_password.php" class="text-danger">Click here to get redirected to Forgot Password page.</a>
        </div>

        <?php header('Refresh: 3; URL=' . ROOT_FOLDER . '/forgot_password.php'); ?>
    </div>

<?php 
    unset($_SESSION['error_load']);

die;
endif; 
?>

<?php 
if(isset($_POST['submit']))
{
    if(empty($_POST['new_pass']))
        $_SESSION['error_message'] = "The new password field is empty!";
    else if(empty($_POST['retype_pass']))
        $_SESSION['error_message'] = "The re-type password field is empty!";

    if(!empty($_POST['new_pass']) && !empty($_POST['retype_pass']))
    {
        if($_POST['new_pass'] == $_POST['retype_pass'])
        {
            $password = password_hash($_POST['new_pass'], PASSWORD_DEFAULT);

            $query = "UPDATE users SET password = :password WHERE username = :username OR email = :email";
            $updateStmt = $con->prepare($query);
            $updateStmt->bindValue(':password', $password, PDO::PARAM_STR);
            $updateStmt->bindValue(':username', $row['username_email'], PDO::PARAM_STR);
            $updateStmt->bindValue(':email', $row['username_email'], PDO::PARAM_STR);
            $updateStmt->execute();

            $_SESSION['result_popup'] = 
            '
                <script type="text/javascript">
                    swal({
                        title: "Changed Password",
                        type: "success",
                        text: "You have successfully changed your password, You may now login.",
                        allowOutsideClick: false,
                        showConfirmButton: true,
                        confirmButtonText: "OK"
                    })
                </script>   
            ';

            header("Location: " . ROOT_FOLDER . "/login.php");
        }
        else 
        {
            $_SESSION['error_message'] = "Re-type password is not the same as the new password!";
        }
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Reset Password</title>
    </head>

    <body>  
        <div class="wrapper">
            <section class="vh-100">
                <div class="container py-5 h-100">
                    <div class="row d-flex justify-content-sm-center justify-content-md-center justify-content-lg-center align-items-center h-100">
                        <div class="col-md-6">
                            <form method="post" enctype="multipart/form-data" class="bg-white px-4 py-3 border border-dark" style="--bs-bg-opacity: .5;">
                                <?php if(!empty($_SESSION['error_message'])): ?>
                                    <div class="alert alert-danger d-flex align-items-center fade show">
                                        <i class='fas fa-exclamation-triangle'></i>
                                        <div class ="mx-3">
                                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>    

                                <h1 class="text-center mb-3">Reset Password</h1>

                                <div class="col">
                                    <label for="" class="col col-form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fa-solid fa-key"></i>
                                        </span>
                                        <input type="password" name="new_pass" class="col-sm-10 form-control" placeholder="New Password">

                                        <span class="input-group-text">
                                            <div class="toggle_hide_password">
                                                <i class="far fa-eye-slash" id="togglePassword" style="cursor: pointer"></i>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <div class="col">
                                <label for="" class="col col-form-label mt-3">Retype Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <i class="fa-solid fa-key"></i>
                                        </span>

                                        <input type="password" name="retype_pass" class="col-sm-10 form-control" placeholder="Retype Password">

                                        <span class="input-group-text">
                                            <div class="toggle_hide_password">
                                                <i class="far fa-eye-slash" id="togglePassword" style="cursor: pointer"></i>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <div class="row mt-5 mx-auto">
                                    <input type="submit" name="submit" value="Change" class="rounded-pill btn btn-warning border border-light btn-lg">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </body>
</html>