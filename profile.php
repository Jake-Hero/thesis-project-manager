<?php
    $count = 0;
    require "./libs/functions.php";
    is_user_valid();
    is_user_login();

    require('./libs/header.php');

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
        <?php require('./head.php')?>
        <title>Thesis & Capstone Manager - Dashboard</title>
    </head>

    <body>
        <div class ="grey-wrapper">
            <div class="container mt-4 mb-5">
                <?php 
                    if(!empty($_SESSION['result_popup']))
                    {
                        echo $_SESSION['result_popup'];
                        unset($_SESSION['result_popup']);
                    }
                ?>

                <div class="card container-fluid">
                    <div class="card-header">Edit My Profile</div>
                    <div class="card-body">
                        <?php if(!is_user_verified()): ?>
                        <div class="alert alert-warning d-flex align-items-center fade show">
                            <i class='fas fa-exclamation-triangle'></i>
                            <div class ="mx-3">
                                Your email address is not verified. <a href="./verify.php" class="alert-link">Click here</a> to verify your account.
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($_SESSION['success_message'])):?>
                        <div class="alert alert-success alert-dismissible d-flex align-items-center fade show">
                        <i class="fas fa-check-circle"></i>
                            <div class ="mx-3">
                                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']) ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($_SESSION['error_message'])):?>
                        <div class="alert alert-danger alert-dismissible d-flex align-items-center fade show">
                            <i class='fas fa-exclamation-triangle'></i>
                            <div class ="mx-3">
                                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']) ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-3 border-end">
                                <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                                    <div class="alert alert-success d-flex align-items-center fade show">
                                        <div class ="mx-3">
                                            Registration Date: <strong><?php echo $_SESSION['user']['date']; ?></strong></label>
                                        </div>
                                    </div>
                                
                                    <img src="<?php echo './assets/profile_pictures/' .$_SESSION['user']['image'] ?>" id="preview" class="rounded-circle border border-light btn-lg" style="width: 150px; height: 150px;" alt="Avatar" />
                                    <span class="text-black-50 mt-2">Preview Profile Picture</span>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <form method="post" enctype="multipart/form-data">
                                    <h3><?php echo $_SESSION['user']['fullname'] ?></h3>

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

                                    <label for="" class="mt-4">Change Full Name</label>
                                    
                                    <div class="form-group">
                                        <div class="input-group">
                                            <?php if(is_user_verified()): ?>
                                                <input type="text" name="fullname" class="col-sm-10 form-control" placeholder="<?php echo $_SESSION['user']['fullname'] ?>">
                                            <?php else: ?>
                                                <input type="text" id="disabledTextInput" class="form-control" placeholder="You need to verify your account first." disabled>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <label for="" class="mt-4">Change Username</label>
                                    
                                    <div class="form-group">
                                        <div class="input-group">
                                            <?php if(is_user_verified()): ?>
                                                <input type="text" name="username" class="col-sm-10 form-control" placeholder=<?php echo $_SESSION['user']['username'] ?>>
                                            <?php else: ?>
                                                <input type="text" id="disabledTextInput" class="form-control" placeholder="You need to verify your account first." disabled>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <label for="" class="mt-4">Change Email</label>
                                    
                                    <div class="form-group">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa-solid fa-envelope"></i>
                                            </span>
                                    
                                            <?php if(is_user_verified()): ?>
                                                <input type="text" name="email" class="col-sm-10 form-control" placeholder="Change Email">
                                            <?php else: ?>
                                                <input type="text" id="disabledTextInput" class="form-control" placeholder="You need to verify your account first." disabled>
                                            <?php endif; ?>

                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="" class="mt-4">Change Password</label>

                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa-solid fa-key"></i>
                                            </span>
                                            <input type="password" name="password" class="col-sm-10 form-control" placeholder="Change Password">

                                            <span class="input-group-text">
                                                <div class="toggle_hide_password">
                                                    <i class="far fa-eye-slash" id="togglePassword" style="cursor: pointer"></i>
                                                </div>
                                            </span>
                                        </div>
                                    </div>

                                    <label for="" class="mt-4">Change Avatar</label>
                                    
                                    <?php if(is_user_verified()): ?>
                                        <input type="file" name="image" id="imageFile" class="col-sm-10 form-control" onchange="preview(this);">
                                    <?php else: ?>
                                        <input type="text" id="disabledTextInput" class="form-control" placeholder="You need to verify your account first." disabled>
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label for="" class="mt-4">Verify Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa-solid fa-key"></i>
                                            </span>

                                            <input type="password" name="verifypassword" class="col-sm-10 form-control" placeholder="Verify Password">
                                        </div>
                                    </div>

                                    <div class="row mt-5 mx-auto">
                                        <input type="submit" name="save" value="Save Changes" class="rounded-pill btn btn-lg btn-warning">
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
        function preview(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                $('#preview').attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</html>