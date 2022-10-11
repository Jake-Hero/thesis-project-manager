<?php
    require "../includes/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: " . ROOT_FOLDER . "/dashboard.php");
        die;
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['save']))
        {
            $row = createUserProfile();
        }
    }

    $currentPage = 'admin';

    require('../includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('../head.php')?>
        <title>Thesis & Capstone Manager - Admin Panel</title>      
    </head>

    <body> 
        <div class="grey-wrapper">
            <div class="container mt-4 mb-5">
                <div class="row">
                    <div class="col mb-3">
                        <button onclick="history.back()" type="submit" class="btn btn-warning btn-md">Go Back to the List</button>
                    </div>
                </div>

                <div class="card container-fluid">
                    <div class="card-header"> Add New User</div>
                    <div class="card-body">
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
                                    <img src="<?php echo ROOT_FOLDER . '/assets/profile_pictures/default_profile.jpg' ?>" id="preview" class="rounded-circle border border-light btn-lg" style="width: 150px; height: 150px;" alt="Avatar" />
                                    <span class="text-black-50 mt-2">Profile Picture</span>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <form method="post" enctype="multipart/form-data">

                                    <label for="" class="mt-4">Assign Role</label>

                                    <div class="form-group">
                                        <select name="role" class="form-select me-2" aria-label="Default select example">
                                            <option value="-1" selected>Assign Role</option>
                                            <option value="0">Student</option>
                                            <option value="1">Panelist</option>
                                            <?php if($_SESSION['user']['role'] >= ROLE_ADMIN): ?>
                                            <option value="2">Adviser</option>
                                            <option value="3">Site Admin</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="" class="mt-4">Full Name</label>
                                        <div class="input-group"> 
                                            <input type="text" name="fullname" class="col-sm-10 form-control" placeholder="Full Name">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="" class="mt-4">Username</label>
                                        <div class="input-group"> 
                                            <input type="text" name="username" class="col-sm-10 form-control" placeholder="Username">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="" class="mt-4">Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa-solid fa-envelope"></i>
                                            </span>         
                                            <input type="text" name="email" class="col-sm-10 form-control" placeholder="Email">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="" class="mt-4">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa-solid fa-key"></i>
                                            </span>                                      
                                            <input type="password" name="password" class="col-sm-10 form-control" placeholder="Password">
                                            <span class="input-group-text">
                                                <div class="toggle_hide_password">
                                                    <i class="far fa-eye-slash" id="togglePassword" style="cursor: pointer"></i>
                                                </div>
                                            </span>                                        
                                        </div>
                                    </div>

                                    <label for="" class="mt-4">Avatar</label>
                                    
                                    <input type="file" name="image" id="image" class="col-sm-10 form-control" onchange="preview(this);">

                                    <div class="row mt-5 mx-auto">
                                        <div class="col text-center">
                                            <input type="submit" name="save" value="Create User" class="rounded-pill btn btn-lg btn-warning">
                                        </div>
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

        document.getElementById('delete').onclick = function(){
            var id = <?php echo $_GET['id']; ?>;
            var your_id = <?php echo $_SESSION['user']['id']; ?>;

            if(id == your_id)
            {
                Swal.fire(
                            'Error',
                            'You are deleting your own profile!',
                            'error'
                        )
                return false;
            }

            swal({
                title: 'Are you sure?',
                text: "You won't be able to undo this action.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Delete'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        type: 'GET', 
                        url: '/thesis-project-manager/src/delete_user.php',
                        data: {'user_id' : id},
                        success: function(response) {
                            if(response=="success") {
                                Swal.fire(
                                    'Deleted',
                                    'You have deleted the user.',
                                    'success'
                                ).then(function() {
                                    window.location.href = "/thesis-project-manager/admin/members.php?page=1";
                                });
                            } else {
                                Swal.fire(
                                    'Error',
                                    'Something went wrong on deleting the user.',
                                    'error'
                                )
                            }
                        }
                    });
                }
            });
        }
    </script>
</html>