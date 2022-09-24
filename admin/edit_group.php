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
        if(isset($_POST['editbtn']))
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
            .card-body #members {
                font-size: 85%;
            }
        </style>
    </head>

    <body> 
        <div class="grey-wrapper">
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

                                    <?php
                                        $query = "SELECT fullname, image FROM users WHERE id = :id LIMIT 1;";
                                        $selectStm = $con->prepare($query);
                                        $selectStm->execute(['id' => $row['group_leader']]);
                                        if($selectStm->rowCount() > 0):
                                        $rows = $selectStm->fetch(PDO::FETCH_ASSOC);
                                    ?>

                                    <label for="" class="border-bottom border-dark text-center mt-4">Thesis Leader</label>
                                    
                                    <div class="text-center mt-3">
                                        <img src="<?php echo ROOT_FOLDER . '/assets/profile_pictures/' .$rows['image'] ?>" class="rounded-circle shadow-sm border-info img-sm mr-3" style="width: 40px; height: 40px;" alt="Avatar" />
                                        <p class="border-bottom border-light mt-2"><?php echo $rows['fullname']; ?></p>        
                                    </div>

                                    <?php endif; ?>

                                    <label for="" class="border-bottom border-info border-4 text-center mt-4">Members</label>

                                    <div class="card">
                                        <div class="card-body" id="members">
                                            <div class="row d-flex justify-content-start">
                                                <?php
                                                $query = "SELECT id, fullname, image FROM users WHERE group_id = :id";
                                                $selectStm = $con->prepare($query);
                                                $selectStm->execute(['id' => $row['groupid']]);
                                                if($selectStm->rowCount() >= 2):
                                                while($rows = $selectStm->fetch(PDO::FETCH_ASSOC)): 
                                                    if($row['group_leader'] == $rows['id']) continue;
                                                ?>

                                                <div class="col text-center">
                                                    <img src="<?php echo ROOT_FOLDER . '/assets/profile_pictures/' .$rows['image'] ?>" class="rounded-circle shadow-sm border-info img-sm mr-3" style="width: 40px; height: 40px;" alt="Avatar" />
                                                    <p><?php echo $rows['fullname']; ?></p>
                                                </div>
                                                
                                                <?php endwhile; ?>

                                                <?php else: ?>

                                                <div class="col">
                                                    No other group members were found.
                                                </div>
                                            
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>  
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
                                        <input type="text" name="group_leader" class="col-sm-10 form-control" placeholder="Enter ID, Full Name or Username (<?php echo getFullName($row['group_leader']); ?>)">
                                    </div>                        

                                    <div class="form-group">
                                        <label for="" class="mt-4">Add Member</label>
                                        <input type="text" name="add_member" class="col-sm-10 form-control" placeholder="Enter ID, Full Name or Username)">
                                    </div>       

                                    <div class="form-group">
                                        <label for="" class="mt-4">Remove Member</label>
                                        <input type="text" name="remove_member" class="col-sm-10 form-control" placeholder="Enter ID, Full Name or Username)">
                                    </div>       

                                    <div class="d-flex justify-content-center">
                                        <div class="row mt-5 col-md-5">
                                            <input type="submit" name="editbtn" value="Edit" class="rounded-pill btn btn-warning btn-lg">
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