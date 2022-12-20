<?php
    require "../libs/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADMIN)
    {
        header("Location: ../dashboard.php");
        die;
    }

    if(!isset($_GET['id']))
    {
        header("Location: ./members.php?page=1");
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
            header("Location: ./members.php?page=1");
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

    $currentPage = 'edit_user';

    require('./header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
        <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" media="nope!" onload="this.media='all'">
        <link rel="stylesheet" href="../css/style.css">
        <link rel="icon" type="image/png" href="../favicon.png">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript" src="../js/custom.js"></script>
        <script type="text/javascript" src="../js/lastseen.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.11/dist/sweetalert2.all.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>

        <title>Thesis & Capstone Manager - Admin Panel</title>      
    </head>

    <body> 
        <div class="grey-wrapper">
            <div class="container mt-4 mb-5">
                <div class="row">
                    <div class="col mb-3">
                        <a href="./members.php"><button type="button" class="btn btn-warning btn-md">Go Back to the List</button></a>
                    </div>
                </div>

                <div class="card container">
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
                                
                                    <img src="<?php echo '../assets/profile_pictures/' .$row['image'] ?>" id="preview" class="rounded-circle border border-light btn-lg" style="width: 150px; height: 150px;" alt="Avatar" />
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
                                    
                                    <input type="file" name="image" id="image" class="col-sm-10 form-control" onchange="preview(this);">

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
        if ( window.history.replaceState ) {
            window.history.replaceState( null, null, window.location.href );
        }

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
                        dataType: 'text',
                        type: 'POST',
                        contentType: 'application/x-www-form-urlencoded',
                        url: '../src/delete_user.php',
                        data: {'user_id' : id},
                        success: function(response) {
                            if(response=="success") {
                                Swal.fire(
                                    'Deleted',
                                    'You have deleted the user.',
                                    'success'
                                ).then(function() {
                                    window.location.href = "./members.php?page=1";
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