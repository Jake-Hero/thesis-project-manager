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
        header("Location: " . ROOT_FOLDER . "/admin/group.php");
        die;
    }

    if(isset($_GET['id']))
	{
		$groupid = $_GET['id'];

        $query = "SELECT * FROM groups WHERE groupid = :id LIMIT 1;";
        $selectStm = $con->prepare($query);
        $selectStm->execute(['id' => $groupid]);
        $row = $selectStm->fetch(PDO::FETCH_ASSOC);

        if(!$row)
        {
            header("Location: " . ROOT_FOLDER . "/admin/group.php");
            die;
        }
	}

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['save']))
        {
            $row = adminEditGroup($row['groupid']);
        }
    }

    $currentPage = 'group';

    require('../includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('../head.php')?>
        <title>Thesis & Capstone Manager - Edit Group</title>   
        
        <style>
            .list-group {
                font-size: 80%;
            }
        </style>
    </head>

    <body> 
        <div class="wrapper">
            <div class="container mt-4 mb-5">
                <div class="card container-fluid">
                    <div class="card-header"><?php echo "You are now viewing and editing Thesis Title/Group: " .$row['group_title']; ?></div>
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

                        <div class="row">
                            <div class="col-md-3 border-end">   
                                <div class="d-flex flex-column text-left">
                                    <label for="" class="mt-4"><strong>Members:</strong></label>

                                    <?php
                                        $query = "SELECT fullname FROM users WHERE group_id = :id LIMIT 1;";
                                        $selectStm = $con->prepare($query);
                                        $selectStm->execute(['id' => $row['groupid']]);
                                        if($selectStm->rowCount() > 0):
                                        $rows = $selectStm->fetch(PDO::FETCH_ASSOC);
                                    ?>

                                    <ul class="list-group">
                                        <li class="list-group-item"><?php echo $rows['fullname']; ?> <strong>(Leader)</strong></li>
                                        <?php endif; ?>

                                        <?php
                                        $query = "SELECT id, fullname FROM users WHERE group_id = :id";
                                        $selectStm = $con->prepare($query);
                                        $selectStm->execute(['id' => $row['groupid']]);
                                        if($selectStm->rowCount() >= 2):
                                        while($rows = $selectStm->fetch(PDO::FETCH_ASSOC)): 
                                        if($row['group_leader'] == $rows['id']) continue;
                                        ?>
                                        <li class="list-group-item"><?php echo $rows['fullname']; ?></li>
                                        <?php endwhile; ?>
                                        <?php else: ?>
                                        <li class="list-group-item">No other group members were found.</li>
                                        <?php endif; ?>
                                    </ul>  
                                </div>  
                            </div>

                            <div class="col-md-5">
                                <form method="post" enctype="multipart/form-data">
                                    <div class="form-group">
                                        <label for="" class="mt-4">Title</label>
                                        <input type="text" name="group_title" class="col-sm-10 form-control" placeholder="New Title (<?php echo $row['group_title']?>)">
                                    </div>

                                    <div class="form-group">
                                        <label for="" class="mt-4">Change Group Leader</label>
                                        <input type="text" name="group_leader" class="col-sm-10 form-control" placeholder="Enter ID or Username (<?php echo getFullName($row['group_leader']); ?>)">
                                    </div>                        

                                    <div class="d-flex justify-content-center">
                                        <div class="row mt-5 col-md-5">
                                            <input type="submit" name="createbtn" value="Edit" class="rounded-pill btn btn-lg text-light" style="background-color: #A020F0;">
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
</html>