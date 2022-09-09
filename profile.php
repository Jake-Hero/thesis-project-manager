<?php
    $count = 0;
    require "functions.php";
    is_user_login();

    require('header.php');

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['save']))
        {
            profileSave();
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
            <div class="container mt-4">
                <div class="card container-fluid">
                    <div class="card-header">Profile Settings</div>
                    <div class="card-body">
                        <?php if(!is_user_verified()): ?>
                        <div class="alert alert-warning d-flex align-items-center fade show">
                            <i class='fas fa-exclamation-triangle'></i>
                            <div class ="mx-3">
                                Your email address is not verified. <a href="verify.php" class="alert-link">Click here</a> to verify your account.
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($_SESSION['success_message'])):?>
                        <div class="alert alert-success alert-dismissible d-flex align-items-center fade show">
                        <i class="fas fa-check-circle"></i>
                            <div class ="mx-3">
                                <?php echo $_SESSION['success_message']; ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($_SESSION['error_message'])):?>
                        <div class="alert alert-danger alert-dismissible d-flex align-items-center fade show">
                            <i class='fas fa-exclamation-triangle'></i>
                            <div class ="mx-3">
                                <?php echo $_SESSION['error_message']; ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-3 border-end">
                                <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                                    <img src="<?php echo 'profile_pictures/' .$_SESSION['user']['image'] ?>" class="rounded-circle border border-light btn-lg" style="width: 150px; height: 150px;" alt="Avatar" />
                                    <span class="text-black-50 mt-2">Preview Profile Picture</span>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <form method="post" enctype="multipart/form-data">

                                    <div class="row">
                                        <div class="col">
                                            <label for="" class="form-label">Email</label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col">
                                            <label for="" class="form-label">
                                                <?php echo '<div class="text-black-50">' .$_SESSION['user']['email']. '</div>'; ?>

                                                <?php 
                                                    if(is_user_verified())
                                                        echo '<div class="text-success"><strong>Verified</strong></div>';
                                                    else
                                                        echo '<div class="text-danger"><strong>Not Verified</strong></div>';
                                                ?>
                                            </label>
                                        </div>
                                    </div>

                                    <label for="" class="mt-4">Change Email</label>
                                    
                                    <?php 
                                        if(is_user_verified()):
                                    ?>
                                        <input type="text" name="email" class="col-sm-10 form-control" placeholder="Change Email">
                                    <?php 
                                        else:
                                    ?>
                                        <input type="text" id="disabledTextInput" class="form-control" placeholder="Verify Your Account First!" disabled>
                                    <?php endif; ?>

                                    <label for="" class="mt-4">Change Password</label>
                                    <input type="password" name="password" class="col-sm-10 form-control" placeholder="Change Password">

                                    <label for="" class="mt-4">Change Avatar</label>
                                    <input type="file" name="image" id="image" class="col-sm-10 form-control" >

                                    <label for="" class="mt-4">Verify Password</label>
                                    <input type="password" name="verifypassword" class="col-sm-10 form-control" placeholder="Verify Password">

                                    <div class="row mt-5 mx-auto">
                                        <input type="submit" name="save" value="Save Changes" class="rounded-pill btn btn-warning border border-light btn-lg">
                                    </div>
                                </form>
                            </div>
                        </div>
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