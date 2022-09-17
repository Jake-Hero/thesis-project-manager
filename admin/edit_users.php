<?php
    require "../includes/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: " . ROOT_FOLDER . "/dashboard.php");
        die;
    }

    if(!isset($_GET['id']))
    {
        header("Location: " . ROOT_FOLDER . "/admin/members.php?page=1");
        die;
    }

    if(isset($_GET['id']))
	{
		$userid = $_GET['id'];

        $query = "SELECT * FROM users WHERE id = :id LIMIT 1;";
        $selectStm = $con->prepare($query);
        $selectStm->execute(['id' => $userid]);
        $row = $selectStm->fetch(PDO::FETCH_ASSOC);

        if(!$row)
        {
            header("Location: " . ROOT_FOLDER . "/admin/members.php?page=1");
            die;
        }
	}

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['save']))
        {
            $row = adminEditProfile($row['username']);
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
        <div class="wrapper">
            <div class="container mt-4 mb-5">
                <div class="row">
                    <div class="col mb-3">
                        <button onclick="history.back()" type="submit" class="btn btn-warning btn-md">Go Back to the List</button>
                    </div>
                </div>

                <div class="card container-fluid">
                    <div class="card-header"><?php echo "You are now viewing and editing " .$row['fullname']. "'s Profile"; ?></div>
                    <div class="card-body">
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

                        <div class="alert alert-warning d-flex align-items-center fade show">
                            <div class ="mx-3">
                                This user was last seen on <?php echo $row['last_seen']; ?>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-3 border-end">
                                <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                                    <div class="alert alert-success d-flex align-items-center fade show">
                                        <div class ="mx-3">
                                            Registration Date: <strong><?php echo $row['date']; ?></strong></label>
                                        </div>
                                    </div>
                                
                                    <img src="<?php echo ROOT_FOLDER . '/assets/profile_pictures/' .$row['image'] ?>" class="rounded-circle border border-light btn-lg" style="width: 150px; height: 150px;" alt="Avatar" />
                                    <span class="text-black-50 mt-2">Profile Picture</span>
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
                                                <?php echo '<div class="text-black-50">' .$row['email']. '</div>'; ?>

                                                <?php 
                                                    if($row['email'] == $row['email_verified'])
                                                        echo '<div class="text-success"><strong>Verified</strong></div>';
                                                    else
                                                        echo '<div class="text-danger"><strong>Not Verified</strong></div>';
                                                ?>
                                            </label>
                                        </div>
                                    </div>

                                    <label for="" class="mt-4">Assign Role (current role: <?php echo getUserRole($row['role']) ?>)</label>

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
                                        <label for="" class="mt-4">Change Full Name</label>
                                        <div class="input-group"> 
                                            <input type="text" name="fullname" class="col-sm-10 form-control" placeholder="<?php echo $row['fullname']; ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="" class="mt-4">Change Username</label>
                                        <div class="input-group"> 
                                            <input type="text" name="username" class="col-sm-10 form-control" placeholder="<?php echo $row['username']; ?>">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="" class="mt-4">Change Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fa-solid fa-envelope"></i>
                                            </span>         
                                            <input type="text" name="email" class="col-sm-10 form-control" placeholder=<?php echo $row['email'] ?>>
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
                                    
                                    <input type="file" name="image" id="image" class="col-sm-10 form-control">

                                    <div class="row mt-5 mx-auto">
                                        <div class="col">
                                            <input type="submit" name="save" value="Save Changes" class="rounded-pill btn btn-lg btn-warning">
                                        </div>
                                        <div class="col">
                                            <button type="button" id="delete" class="rounded-pill btn btn-lg btn-danger">Delete</button>
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