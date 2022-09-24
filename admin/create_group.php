<?php
    require "../includes/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: " . ROOT_FOLDER . "/dashboard.php");
        die;
    }

    if(isset($_POST['createbtn']))
    {
        $errors = create_group($_POST);
    }

    $currentPage = 'group';
    require('../includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('../head.php')?>
        <title>Thesis & Capstone Manager - Group</title>
    </head>

    <body>
        <div class ="grey-wrapper">
            <div class="container mt-4">
                <div class="card container-fluid">
                    <div class="card-header">Create a new Group</div>
                    <div class="card-body">
                        <?php if(!empty($errors['error_message'])):?>
                        <div class="alert alert-danger alert-dismissible d-flex align-items-center fade show">
                            <i class='fas fa-exclamation-triangle'></i>
                            <div class ="mx-3">
                                <?php echo $errors['error_message']; ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <p>You are now about to create a new Thesis Group, please fill up the form.</p>

                        <form method="post" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="" class="mt-4">Title</label>
                                <input type="text" name="group_title" class="col-sm-10 form-control" placeholder="Title of the Group - Thesis Title">
                            </div>

                            <div class="form-group">
                                <label for="" class="mt-4">Group Leader</label>
                                <input type="text" name="group_leader" class="col-sm-10 form-control" placeholder="Enter the ID, Full Name or User Name">
                            </div>                    

                            <div class="d-flex justify-content-center">
                                <div class="row mt-5 col-md-5">
                                    <input type="submit" name="createbtn" value="Create" class="rounded-pill btn btn-lg btn-warning">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>