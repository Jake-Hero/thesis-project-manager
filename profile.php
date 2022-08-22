<?php
    require "functions.php";
    is_user_login();

    if(!is_user_verified())
    {
        header("Location: verify.php");
        die;
    }

    require('header.php');
    
    $errors = array();

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['save']))
        {
            $errors = profileSave();
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Dashboard</title>
    </head>

    <body>
        <div class ="wrapper">
            <div class="container h-100">
                <div class="row d-flex justify-content-sm-center justify-content-md-center justify-content-lg-center align-items-center h-100">
                    <div class="col-md-6">
                        <form method="post" enctype="multipart/form-data" class="profile-form px-4 py-3 border border-dark">
                            <?php if(!is_user_verified()): ?>
                                <div class="alert alert-warning d-flex align-items-center fade show">
                                    <i class='fas fa-exclamation-triangle'></i>
                                    <div class ="mx-3">
                                        Your email address is not verified. <a href="verify.php" class="alert-link">Click here</a> to verify your account.
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="row">
                                <div class="col mt-5">
                                    <div id="preview">
                                        <img src="<?php echo 'profile_pictures/' .$_SESSION['user']['image'] ?>" class="border border-dark" style="width: 150px; height: 150px;" alt="Avatar" />
                                    </div>
                                    <label for="" class="col col-form-label px-4 text-white">Preview Profile Picture</label>
                                </div>
                                <div class="col">
                                    <?php if(!empty($errors['success'])):?>
                                        <div class="alert alert-success alert-dismissible d-flex align-items-center fade show">
                                        <i class="fas fa-check-circle"></i>
                                            <div class ="mx-3">
                                                <?php echo $errors['success'];?>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>

                                    <?php if(!empty($errors['all'])):?>
                                        <div class="alert alert-danger alert-dismissible d-flex align-items-center fade show">
                                            <i class='fas fa-exclamation-triangle'></i>
                                            <div class ="mx-3">
                                                <?php echo $errors['all'];?>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                        </div>
                                    <?php endif; ?>

                                    <label for="" class="col col-form-label mt-5">Change Password</label>
                                    <input type="password" name="password" class="col-sm-10 form-control" placeholder="Change Password">

                                    <label for="" class="col col-form-label">Change Avatar</label>
                                    <input type="file" name="image" id="image">

                                    <label for="" class="col col-form-label mt-5">Verify Password</label>
                                    <input type="password" name="verifypassword" class="col-sm-10 form-control" placeholder="Verify Password">
                                </div>
                            </div>
                            <div class="row mt-5 mx-auto">
                                    <input type="submit" name="save" value="Save Changes" class="rounded-pill btn btn-warning border border-light btn-lg">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script>
        function imagePreview(fileInput) {
            if (fileInput.files && fileInput.files[0]) {
                var fileReader = new FileReader();
                fileReader.onload = function (event) {
                    $('#preview').html('<img src="'+event.target.result+'" class="rounded-circle border border-light btn-lg" style="width: 150px; height: 150px;"/>');
                };
                fileReader.readAsDataURL(fileInput.files[0]);
            }
        }
        $("#image").change(function () {
            imagePreview(this);
        });            
    </script>
</html>