<?php
    require "../libs/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: ../dashboard.php");
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

    require('./header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
        <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" media="nope!" onload="this.media='all'">
        <link rel="stylesheet" href="../css/style.css">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript" src="../js/lastseen.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.11/dist/sweetalert2.all.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>

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
                                    <img src="../assets/profile_pictures/default_profile.jpg" id="preview" class="rounded-circle border border-light btn-lg" style="width: 150px; height: 150px;" alt="Avatar" />
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