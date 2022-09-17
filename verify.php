<?php
    $errors = array();

    require "includes/functions.php";

    is_user_valid();
    is_user_login();

    if(is_user_verified())
    {
        header("Location: " . ROOT_FOLDER . "/dashboard.php");
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
                header("Location: " . ROOT_FOLDER . "/dashboard.php");
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

                        header("Location: " . ROOT_FOLDER . "/profile.php");
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

    require('includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Verification</title>
    </head>

    <body>  
        <div class="wrapper">
            <section class="vh-100">
                <div class="container py-5 h-100">
                    <div class="row d-flex justify-content-sm-center justify-content-md-center justify-content-lg-center align-items-center h-100">
                        <div class="col-md-6">
                            <form method="post" enctype="multipart/form-data" class="bg-white px-4 py-3 border border-dark" style="--bs-bg-opacity: .5;">
                                <h1 class="text-center">Verification Code</h1>

                                <?php if(!empty($_SESSION['error_message'])): ?>
                                    <div class="alert alert-danger d-flex align-items-center fade show">
                                        <i class='fas fa-exclamation-triangle'></i>
                                        <div class ="mx-3">
                                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="col">
                                    <label for="" class="col col-form-label">Please put your code here</label>
                                    <input type="text" name="code" class="col form-control" placeholder="Code">
                                </div>

                                <div class="d-flex justify-content-center align-items-center">
                                    <div class="mt-3">
                                        <a href="<?php echo ROOT_FOLDER; ?>/profile.php" class="text-danger"><strong>Go back to Edit My Profile</strong></a>
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