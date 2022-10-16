<?php
    require "includes/functions.php";
    is_user_valid();
    is_user_login();
    
    if(!isset($_GET['id']))
    {
        header("Location: " . ROOT_FOLDER . "/group.php");
        die;
    }

    if(isset($_GET['id']))
    {
		$taskid = $_GET['id'];

        $query = "SELECT * FROM tasks WHERE taskid = :id LIMIT 1;";
        $selectStm = $con->prepare($query);
        $selectStm->execute(['id' => $taskid]);
        $row = $selectStm->fetch(PDO::FETCH_ASSOC);

        if(!$row)
        {
            header("Location: " . ROOT_FOLDER . "/group.php");
            die;
        }

        if($row['taskgroup'] != $_SESSION['user']['group_id'])
        {
            header("Location: " . ROOT_FOLDER . "/group.php");
            die;
        }
    }

    $currentPage = 'view_task';
    require('includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Dashboard</title>
    </head>

    <body>
        <div class="grey-wrapper">
            <div class="container-fluid mt-4 mb-5">

                <div class="card">
                    <div class="card-header text-white" style="background-color: #800000; font-family: cursive;"><?php echo "Viewing " .$row['tasktitle'] . " assigned to " . getFullName($row['taskassignedto']); ?></div>
                    <div class="card-body">            
                        <div class="row mx-auto d-flex justify-content-evenly mb-4">
                            <div class="col-md-12">                
                                <div class="row">
                                    <div class="col-sm-6">
                                        <dl>
                                            <dt><b class="border-bottom border-primary">Status</b></dt>
                                            <dd>
                                                <?php
                                                switch($row['taskstatus']) {
                                                    case 0: 
                                                        echo '<span class="mt-2 badge bg-danger tg-white">Incomplete</span>';
                                                        break;
                                                    case 1: 
                                                        echo '<span class="mt-2 badge bg-warning text-white">In-Progress</span>';
                                                        break;
                                                    case 2: 
                                                        echo '<span class="mt-2 badge bg-success text-white">Complete</span>';
                                                        break;
                                                }
                                                ?>
                                            </dd>
                                        </dl>

                                        <dl>
                                            <dt><b class="border-bottom border-primary">Task</b></td>
                                            <dd class="mt-2">
                                                <?php 
                                                    echo $row['tasktitle'];
                                                ?>
                                            </dd>
                                        </dl>

                                        <dl>
                                            <dt><b class="border-bottom border-primary">Details</b></td>
                                            <dd class="mt-2">
                                                <?php 
                                                    echo $row['taskdetail'];
                                                ?>
                                            </dd>
                                        </dl>
                                    </div>

                                    <div class="col-md-6">
                                        <dl>
                                            <dt><b class="border-bottom border-primary">Assigned to</b></dt>
                                            <dd class="mt-2">
                                                <?php 
                                                    if($row['taskassignedto'] == $_SESSION['user']['id'])
                                                        echo getFullName($row['taskassignedto']) . ' <strong>(you)</strong>';
                                                    else 
                                                        echo getFullName($row['taskassignedto']);
                                                ?>
                                            </dd>
                                        </dl>

                                        <dl>
                                            <dt><b class="border-bottom border-success">Started</b></dt>
                                            <dd class="mt-2">
                                                <?php echo getweekDay($row['taskstart']) . ', ' .  $row['taskstart']; ?>
                                            </dd>
                                        </dl>

                                        <dl>
                                            <dt><b class="border-bottom border-danger">Due Date (Deadline)</b></dt>
                                            <dd class="mt-2">
                                                <?php echo getweekDay($row['taskdue']) . ', ' .  $row['taskdue']; ?>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row mx-auto d-flex justify-content-evenly mb-4">
                            <div class="col-md-12">      
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>