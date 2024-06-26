<?php
    require "./libs/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_PANELIST)
    {
        header("Location: ./group.php");
        die;
    }

    if(!isset($_GET['id']))
    {
        header("Location: ./panelist_group.php");
        die;
    }

    if(isset($_GET['id']))
	{
		$groupid = $_GET['id'];
        $query = "SELECT * FROM groups WHERE groupid = :id LIMIT 1;";
        $selectStmt = $con->prepare($query);
        $selectStmt->execute(['id' => $groupid]);
        $group_row = $selectStmt->fetch();
        if(!$group_row)
        {
            header("Location: ./panelist_group.php");
            die;
        }
    }

    $currentPage = 'group';
    require('libs/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
        <link href="./bootstrap/css/bootstrap.min.css" rel="stylesheet" media="nope!" onload="this.media='all'">
        <link rel="stylesheet" href="./css/style.css">
        <link rel="shortcut icon" type="image/jpg" href="./favicon.ico"/>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript" src="./js/custom.js"></script>
        <script type="text/javascript" src="./js/lastseen.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script src="./bootstrap/js/bootstrap.min.js"></script>

        <title>Thesis & Capstone Manager - Group</title>

        <style>
            table.table tr th, table.table tr td {
                border: 1px solid rgba(0, 0, 0, 0.1);
                background-color: rgba(240, 240, 240, 0.1) !important
            }

            table.table td:last-child {
                width: 130px;
            }

            table.table td a.edit {
                color: #2196F3;
            }

            table.table td a.delete {
                color: #ff0000;
            }

            table.table td i {
                font-size: 19px;
            }

            #group-content
            { 
                height: 70vh; 
                width: 100%; 
            }

            #comment-content
            { 
                height: 70vh; 
                overflow-x: scroll; 
                overflow-y: auto;
                width: 100%; 
            }

            #task-content
            { 
                height: 55vh; 
                overflow-x: scroll; 
                overflow-y: auto;
                width: 100%; 
            }

            #group-logs
            { 
                height: 30vh; 
                overflow-x: scroll; 
                overflow-y: auto;
                width: 100%; 
            }

            button,
            button:active,
            button:focus, 
            button:hover,
            .btn,
            .btn:active, 
            .btn:focus, 
            .btn:hover{   
                border:none !important;
                outline:none !important;
            }
        </style>
    </head>

    <body>
        
        <?php if($selectStmt->rowCount() > 0): ?>

            <div class="grey-wrapper">
                <div class="container-fluid header mt-4 mb-3">    
                    <div class="row">
                        <div class="col mb-3">
                            <a href="../admin/grades.php"><button type="button" class="btn btn-warning btn-md">Go to Grading</button></a>
                        </div>
                    </div>

                    <div class="row mx-auto d-flex justify-content-evenly mb-4">
                        <div class="col-md-12">
                            <?php if(!empty($_SESSION['success_message'])):?>
                            <div class="alert alert-success alert-dismissible d-flex align-items-center fade show">
                                <i class="fas fa-check-circle"></i>
                                <div class ="mx-3">
                                    <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']) ?>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                            <?php endif; ?>
  
                            <div class="card">
                                <div class="card-header text-white" style="background-color: #800000; font-family: 'Lemon/Milk', sans-serif;">Active Tasks</div>
                                <div id="task-content" class="card-body">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr class="table-light">
                                                <th scope="col" class="text-center">Status</th>
                                                <th scope="col" class="text-center" colspan="1">Task</th>
                                                <th scope="col" class="text-center">Assigned To</th>
                                                <th scope="col" class="text-center">Due Date</th>
                                                <th scope="col" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $selectStmt = $con->prepare('SELECT * FROM tasks WHERE taskgroup = :id AND taskstatus <= 1 ORDER BY taskid DESC');
                                            $selectStmt->execute(['id' => $_SESSION['user']['group_id']]);

                                            if($selectStmt->rowCount() > 0):
                                                while($row = $selectStmt->fetch(PDO::FETCH_ASSOC)): 
                                            ?>

                                            <tr class="table-light text-center">
                                                <td>
                                                    <?php
                                                    switch($row['taskstatus']) {
                                                        case 0: 
                                                            echo '<span class="badge bg-danger tg-white">Incomplete</span>';
                                                            break;
                                                        case 1: 
                                                            echo '<span class="badge bg-warning text-white">In-Progress</span>';
                                                            break;
                                                        case 2: 
                                                            echo '<span class="badge bg-success text-white">Complete</span>';
                                                            break;
                                                    }
                                                    ?>
                                                </td>
                                                <td><?php echo $row['tasktitle'] . ' - ' . $row['taskdetail']; ?></td>
                                                <td><?php echo getFullName($row['taskassignedto']); ?></td>
                                                <td><?php echo getweekDay($row['taskdue']) . ', ' . date("F j g:i a", strtotime($row['taskdue'])); ?></td>
                                                <td><a href="<?php echo './task.php?id=' . $row['taskid']; ?>"><span class="badge bg-primary text-white">View</span></a></td>
                                            </tr>   
        
                                            <?php endwhile; ?>
                                            <?php else: ?>
                                            <tr class="table-light">
                                                <td colspan="8" class="text-center">No task found</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mx-auto">
                        <div class="col-lg-8">
                            <div class="card">                            
                                <div class="card-header text-black-50" style="background-color: #A020F0; font-family: 'Lemon/Milk', sans-serif;">Group Info</div>
                                <div id="group-content" class="card-body">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <dl>
                                                <dt><b class="border-bottom border-primary">Thesis Title</b></dt>
                                                <dd><p class="mt-3"><?php echo $group_row['group_title']; ?></p></dd>
                                            </dl>

                                            <dl>
                                                <dt><b class="border-bottom border-primary">Thesis Leader</b></td>
                                                <dd class="mt-2">
                                                    <?php
                                                        $query = "SELECT id, fullname, image FROM users WHERE id = :id";
                                                        $selectStm = $con->prepare($query);
                                                        $selectStm->execute(['id' => $group_row['group_leader']]);
                                                        if($selectStm->rowCount() > 0):
                                                            $rows = $selectStm->fetch();
                                                    ?>

                                                        <a href="" class="edit" title="<?php echo $rows['fullname'] ?>" data-toggle="tooltip">
                                                            <img src="<?php echo './assets/profile_pictures/' .$rows['image'] ?>" class="rounded-circle shadow-sm border-info img-sm mr-3" style="width: 40px; height: 40px;" alt="Avatar" />
                                                        </a>

                                                    <?php else: ?>

                                                        <p>No group leader</p>

                                                    <?php endif; ?>
                                                </dd>
                                            </dl>
                                        </div>

                                        <div class="col-md-6">
                                            <dl>
                                                <dt><b class="border-bottom border-primary">Members</b></dt>
                                                <dd>
                                                    <p class="mt-2">
                                                        <?php
                                                            $query = "SELECT id, fullname, image FROM users WHERE group_id = :id";
                                                            $selectStm = $con->prepare($query);
                                                            $selectStm->execute(['id' => $groupid]);
                                                            if($selectStm->rowCount() >= 2):
                                                            while($rows = $selectStm->fetch(PDO::FETCH_ASSOC)): 
                                                                if($group_row['group_leader'] == $rows['id']) continue;
                                                            ?>
                                                            
                                                            <a href="" class="edit" title="<?php echo $rows['fullname'] ?>" data-toggle="tooltip">
                                                                <img src="<?php echo './assets/profile_pictures/' .$rows['image'] ?>" class="rounded-circle shadow-sm border-info img-sm mr-3" style="width: 40px; height: 40px;" alt="Avatar" />
                                                            </a>

                                                            <?php endwhile; ?>

                                                            <?php else: ?>
                                                            
                                                            <p class="mt-3">This thesis group has no member.</p>
                                                        <?php endif; ?>
                                                    </p>
                                                </dd>
                                            </dl>

                                            <dl>
                                                <dt><b class="border-bottom border-primary">Created on</b></dt>
                                                <dd class="mt-2">
                                                    <?php echo $group_row['creation'] ?>
                                                </dd>
                                            </dl>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <dl>
                                            <dt><b class="border-bottom border-danger">Recent Group Log</b></dt>
                                        </dl>

                                        <div id="group-logs">
                                            <dl>
                                                <?php 
                                                    $query = "SELECT * FROM group_logs WHERE groupid = :id ORDER BY id DESC LIMIT 5;";
                                                    $selectStmt = $con->prepare($query);
                                                    $selectStmt->bindValue('id', $groupid, PDO::PARAM_INT);
                                                    $selectStmt->execute();
                                                ?>

                                                <?php if($selectStmt->rowCount() > 0): ?>
                                                    <?php while($activities_row = $selectStmt->fetch(PDO::FETCH_ASSOC)): ?>
                                                        <dd><?php echo $activities_row['log_details'] . " <strong>(" . $activities_row['log_date'] . ")</strong>"; ?></dd>
                                                    <?php endwhile; ?>
                                                <?php else: ?>
                                                    <tr class="table-light text-center">
                                                        <dd>No recent activities.</dd>
                                                    </tr>
                                                <?php endif; ?>
                                            </dl>
                                        </div>
                                    </div>

                                </div>
                            </div>  
                        </div>

                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header text-black-50" style="background-color: #FFD700; font-family: 'Lemon/Milk', sans-serif;">Panelist Comments</div>
                                <div id="comment-content" class="card-body">

                                    <form id="form_comment">
                                        <div id="replying_to" class="alert alert-dismissible" role="alert" style="display:none;">
                                            <strong></strong>
                                            <button type="button" id="replying_hide" class="btn-close"></button>
                                        </div>

                                        <input type="hidden" name="groupid" value="<?php echo $_SESSION['user']['group_id']; ?>">
                                        <input type="hidden" name="comment_id" id="commentId" />
                                        <input type="hidden" name="author" value="<?php echo $_SESSION['user']['fullname'] ?>" />

                                        <div class="input-group">
                                            <img id="commentPic" src="<?php echo './assets/profile_pictures/' .$_SESSION['user']['image'] ?>" id="preview" class="rounded-circle mt-3 mx-2" style="width: 40px; height: 40px;" alt="Avatar" />
                                            <textarea class="form-control mx-3" name="comment" id="comment" placeholder="Your comment here"></textarea>
                                            <button id="publishBtn" class="btn" type="button">
                                                <img src="<?php echo './assets/images/send_button.svg'; ?>" style="height: 32px; width: 32px;">
                                            </button>
                                        </div>
                                    </form>

                                    <div id="view_comment"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    </body>
</html>

<script>
    $(function(){
        $("#replying_hide").on("click", function(){
            $('#replying_to').hide();
            $("#commentId").val(0);
        });
    });

    function postReply(commentId, posted_by) {
        $('#commentId').val(commentId);
        $('#comment-content').scrollTop(0);

        ShowAlert('You are replying to ' + posted_by);
    }

    function ShowAlert(text) {
        var AlertMsg = $('div[role="alert"]');
        $(AlertMsg).find('strong').html(text);
        $(AlertMsg).removeAttr('class');
        $(AlertMsg).addClass('alert alert-primary');
        $(AlertMsg).show();
    }

    function deleteReply(commentId) {
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
                        url: './src/comment_delete.php',
                        data: {'comment_id' : commentId},
                        success: function(response) {
                            if(response=="success") {
                                listComment();
                                Swal.fire(
                                    'Deleted',
                                    'Comment Deleted.',
                                    'success'
                                )
                            } else {
                                Swal.fire(
                                    'Error',
                                    'Something went wrong.',
                                    'error'
                                )
                            }
                        }
                    });
                }
            });
    }

    $("#publishBtn").click(function () {
        var str = $("#form_comment").serialize();
        if($("#comment").val()) {
            $.ajax({
                dataType: 'text',
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded',
                url: "./src/comment_add.php",
                data: str,
                success: function (response)
                {
                    var result = eval('(' + response + ')');
                    if (response)
                    {
                        if($("#comment").val()) {
                            $("#comment").val("");
                            $("#commentId").val("");
                            
                            listComment();
                            $('html, body').animate({ scrollTop: $("#comment").offset().top }, 0);
                        }
                    } 
                }
            });
        }
    });

    $(document).ready(function () {
        listComment();
        listGrades(1);
    });

    function listGrades(semester) {
        $.ajax({
            dataType: 'text',
            type: 'POST',
            contentType: 'application/x-www-form-urlencoded',
            url:"./src/grade_display.php",
            data: {'groupid' : <?php echo $groupid; ?>, 'semester' : semester},
            success:function(response)
            {
                $('#grades').html(response);
            }
        })
    }

    function listComment() {
        $('#replying_to').show();
        $('#replying_to').hide();

        $.ajax({
            dataType: 'text',
            type: 'POST',
            contentType: 'application/x-www-form-urlencoded',
            url:"./src/comment_list.php",
            data: {'groupid' : <?php echo $groupid; ?>},
            success:function(response)
            {
                $('#view_comment').html(response);
            }
        })
    }
</script>