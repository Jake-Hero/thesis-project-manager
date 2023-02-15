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
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
        <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet" media="nope!" onload="this.media='all'">
        <link rel="stylesheet" href="./css/style.css">
        <link rel="shortcut icon" type="image/jpg" href="./favicon.ico"/>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript" src="./js/custom.js"></script>
        <script type="text/javascript" src="./js/lastseen.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.11/dist/sweetalert2.all.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

        <link rel="stylesheet" href="./fullcalendar/lib/main.min.css">
        <script src="./fullcalendar/lib/main.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script src="./bootstrap/js/bootstrap.min.js"></script>

        <link rel="stylesheet" href="./validate_password/css/jquery.passwordRequirements.css" />
        <script src="./validate_password/js/jquery.passwordRequirements.min.js"></script>

        <title>Thesis & Capstone Manager - Profile Settings</title>

        <style>
            body 
            {
                width: 100%;
                min-height: 100%;
                background-color: rgb(169,169,169, 0.3);
                display: block;
            }
        </style>
    </head>

    <body>
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
                                        <input type="password" name="password" class="pr-password col-sm-10 form-control" placeholder="Change Password">

                                        <span class="input-group-text">
                                            <div class="toggle_hide_password">
                                                <i class="far fa-eye-slash" id="togglePassword" style="cursor: pointer"></i>
                                            </div>
                                        </span>
                                    </div>
                                </div>

                                <label for="" class="mt-4">Change Avatar</label>
                                
                                <?php if(is_user_verified()): ?>
                                    <input type="file" name="image" id="imageFile" class="col-sm-10 form-control" accept="image/png, image/jpg, image/jpeg" onchange="preview(this);">
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
    </body>

    <script>
        $(".pr-password").passwordRequirements({
            numCharacters: 8,
            useLowercase:true,
            useUppercase:true,
            useNumbers:true,
            useSpecial:true,
            style: "dark"
        });

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