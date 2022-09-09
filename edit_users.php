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
        <div class="container mt-3">
            <div class="card container-fluid">
                <div class="card-header"><?php echo $row['fullname']. "'s Profile"; ?></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 border-end">
                            <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                                <img src="<?php echo 'profile_pictures/' .$row['image'] ?>" class="rounded-circle border border-light btn-lg" style="width: 150px; height: 150px;" alt="Avatar" />
                                <span class="text-black mt-2"><?php echo $row['fullname'] ?></span>
                                <span class="text-black"><?php echo $row['email'] ?></span>
                                <span class="text-black">Role: <?php echo getUserRole($row['role']) ?></span>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="p-3 py-5">
                                Change Password
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>