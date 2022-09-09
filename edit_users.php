<?php
    require "functions.php";
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: dashboard.php");
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
            header("Location: members.php");
            die;
        }
	}

    $currentPage = 'admin';

    // Visit Detection
    recordUserVisit();

    require('header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Admin Panel</title>      
    </head>

    <body> 
        <div class="wrapper">
            <div class="container mt-4">
                <div class="card container-fluid">
                    <div class="card-header"><?php echo "You are now viewing and editing " .$row['fullname']. "'s Profile"; ?></div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 border-end">
                                <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                                    <img src="<?php echo 'profile_pictures/' .$row['image'] ?>" class="rounded-circle border border-light btn-lg" style="width: 150px; height: 150px;" alt="Avatar" />
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
                                                    if(is_user_verified($row['id']))
                                                        echo '<div class="text-success"><strong>Verified</strong></div>';
                                                    else
                                                        echo '<div class="text-danger"><strong>Not Verified</strong></div>';
                                                ?>
                                            </label>
                                        </div>
                                    </div>

                                    <label for="" class="mt-4">Change Email</label>

                                    <input type="text" name="email" class="col-sm-10 form-control" placeholder=<?php echo $row['email'] ?>>

                                    <label for="" class="mt-4">Change Password</label>
                                    <input type="password" name="password" class="col-sm-10 form-control" placeholder="Change Password">

                                    <label for="" class="mt-4">Change Avatar</label>
                                    
                                    <input type="file" name="image" id="image" class="col-sm-10 form-control">

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
</html>