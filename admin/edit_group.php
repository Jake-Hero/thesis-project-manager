<?php
    require "../libs/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: ../dashboard.php");
        die;
    }

    if(!isset($_GET['id']))
    {
        header("Location: ./group.php");
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
            header("Location: ./group.php");
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

        <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js" integrity="sha512-K/oyQtMXpxI4+K0W7H25UopjM8pzq0yrVdFdG21Fh5dBe91I40pDd9A4lzNlHPHBIP2cwZuoxaUSX0GJSObvGA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css" integrity="sha512-MQXduO8IQnJVq1qmySpN87QQkiR1bZHtorbJBD0tzy7/0U9+YIC93QWHeGTEoojMVHWWNkoCp8V6OzVSYrX0oQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <title>Thesis & Capstone Manager - Edit Group</title>   
        
        <style>
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

            .card-body #members {
                font-size: 85%;
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
                height: 60vh; 
                overflow-x: scroll; 
                overflow-y: auto;
                width: 100%; 
            }
        </style>
    </head>

    <body> 
        <div class="modal fade" id="modalAddTask" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    <div class="modal-header text-center">
                        <h4 class="modal-title w-100 font-weight-bold">Assign New Task</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form id="create_form" method="post" enctype="multipart/form-data">
                        <div class="modal-body mx-3">
                            <div class="form-group has-error md-form mb-4">
                                <label data-error="wrong" data-success="right" for="">Assign to</label>
                                <select id="assigned_to" name="assigned_to" class="form-select" aria-label="">
                                    <option selected>Search ...</option>
                                    <?php 
                                    $selectStmt = $con->prepare('SELECT id, fullname FROM users WHERE group_id = :id');
                                    $selectStmt->execute(['id' => $groupid]);   

                                    if($selectStmt->rowCount() > 0):
                                        while($rows = $selectStmt->fetch()): 
                                    ?>

                                    <option value="<?php echo $rows['id']; ?>"><?php echo $rows['fullname']; ?></option>

                                    <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group has-error md-form mt-3 mb-4">
                                <label data-error="wrong" data-success="right" for="task_title">Title</label>
                                <input type="text" id="task_title" name="task_title" class="form-control validate" placeholder="Title">
                            </div>

                            <div class="form-group has-error md-form mt-3 mb-4">
                                <label data-error="wrong" data-success="right" for="task_detail">Details</label>
                                <textarea class="form-control" id="task_detail" rows="3"></textarea>
                            </div>

                            <div class="form-group row has-error md-form mt-3 mb-4">
                                <label data-error="wrong" data-success="right" for="task_start">Start Date</label>
                                <input type="text" name="task_start" id="task_start" class="form-control">
                            </div>

                            <div class="form-group row has-error md-form mt-3 mb-4">
                                <label data-error="wrong" data-success="right" for="task_end">End Date</label><br>
                                <input type="text" name="task_end" id="task_end" class="form-control">
                            </div>
                        </div>

                        <div class="modal-footer d-flex justify-content-center">
                            <button type="submit" id="createbtn" class="btn text-white" style="background-color: #A020F0;">Assign Task</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="grey-wrapper">
            <div class="container-fluid mt-4 mb-5">
                <div class="row">
                    <div class="col mb-3">
                        <a href="./group.php"><button type="button" class="btn btn-warning btn-md">Go Back to the List</button></a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header text-black-50" style="background-color: #FFD700; font-family: cursive;"><?php echo "You are now viewing and editing Thesis Title/Group: " .$row['group_title']; ?></div>
                    <div class="card-body">            
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
                                    <div class="col-md-3 border-end border-bottom">   
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
                                                <img src="<?php echo '../assets/profile_pictures/' .$rows['image'] ?>" class="rounded-circle shadow-sm border-info img-sm mr-3" style="width: 40px; height: 40px;" alt="Avatar" />
                                                <p class="border-bottom border-light mt-2"><?php echo $rows['fullname']; ?></p>        
                                            </div>

                                            <?php endif; ?>

                                            <label for="" class="border-bottom border-info border-4 mt-4">Members</label>

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
                                                            <img src="<?php echo '../assets/profile_pictures/' .$rows['image'] ?>" class="rounded-circle shadow-sm border-info img-sm mr-3" style="width: 40px; height: 40px;" alt="Avatar" />
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

                                            <label for="" class="border-bottom border-info border-4 mt-4">Group Code</label>
                                            <div class="card text-center">
                                                <div class="mt-2 d-flex justify-content-center">
                                                    <div id="code_spinner" class="loader"></div>
                                                    <div id="group_code"></div>
                                                </div>

                                                <div class="mt-2 mb-2">
                                                    <button id="refresh_code" type="button" class="btn btn-sm btn-warning mx-auto">Refresh</button>
                                                </div>
                                            </div>
                                        </div>  

                                    </div>

                                    <div class="col-md-5 border-end border-bottom">
                                        <form method="post" enctype="multipart/form-data">
                                            <div class="form-group">
                                                <label for="" class="mt-4">Title</label>
                                                <input type="text" name="group_title" class="col-sm-10 form-control" placeholder="New Title (<?php echo $row['group_title']?>)">
                                            </div>

                                            <div class="form-group">
                                                <label for="" class="mt-4">Change Group Leader</label>
                                                <input type="text" id="group_leader" name="group_leader" class="col-sm-10 form-control" placeholder="Search... (<?php echo getFullName($row['group_leader']); ?>)">
                                                <div class="list-group" id="show-leader" style="overflow: hidden;">
                                                </div>
                                            </div>                        

                                            <div class="form-group">
                                                <label for="" class="mt-4">Add Member</label>
                                                <input type="text" id="add_member" name="add_member" class="col-sm-10 form-control" placeholder="Search...">
                                                <div class="list-group" id="show-add-member" style="overflow: hidden;">
                                                </div>                                    
                                            </div>       

                                            <div class="form-group">
                                                <label for="" class="mt-4">Remove Member</label>
                                                <input type="text" id="remove_member" name="remove_member" class="col-sm-10 form-control" placeholder="Search...">
                                                <div class="list-group" id="show-remove-member" style="overflow: hidden;">
                                                </div>          
                                            </div>       

                                            <div class="d-flex justify-content-center">
                                                <div class="row mt-5 col-md-5">
                                                    <button type="submit" id="editbtn" name="editbtn" class="btn btn-warning btn-lg text-white" style="background-color: #A020F0;">Edit</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="col-md-4 border-bottom">
                                        <h4 style="font-family: 'Lemon/Milk', sans-serif;">Tasks</h4>

                                        <div class="mt-3 mb-2">
                                            <a href="" data-toggle="modal" data-target="#modalAddTask"><button class="btn btn-sm text-light" style="background-color: #A020F0;" type="submit"><i class="fa-sharp fa-solid fa-plus"></i> Assign New Task</button></a>
                                        </div>

                                        <ul id="task-content" class="list-group">
                                            <?php
                                            $selectStmt = $con->prepare('SELECT * FROM tasks WHERE taskgroup = :id ORDER BY taskid DESC;');
                                            $selectStmt->execute(['id' => $groupid]);

                                            if($selectStmt->rowCount() > 0):
                                                while($rows = $selectStmt->fetch(PDO::FETCH_ASSOC)): 
                                            ?>

                                            <a href="./edit_task.php?id=<?php echo $rows['taskid']; ?>" title="Edit" data-toggle="tootlip">
                                                <li class="list-group-item">
                                                    <?php 
                                                        echo $rows['tasktitle'] . ' '; 

                                                        switch($rows['taskstatus']) {
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
                                                </li>
                                            </a>

                                            <?php endwhile; ?>
                                            <?php else: ?>
                                                <li class="list-group-item">No task found</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header text-black-50" style="background-color: #A020F0; font-family: 'Lemon/Milk', sans-serif;">Group Info</div>
                                    <div id="group-content" class="card-body">
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

                            <div class="col-lg-6">
                                <div class="card">
                                    <div class="card-header text-black-50" style="background-color: #FFD700; font-family: 'Lemon/Milk', sans-serif;">Panelist Comments</div>
                                    <div id="comment-content" class="card-body">
                                        <form id="form_comment">
                                            <div id="replying_to" class="alert alert-dismissible" role="alert" style="display:none;">
                                                <strong></strong>
                                                <button type="button" id="replying_hide" class="btn-close"></button>
                                            </div>

                                            <input type="hidden" name="groupid" id="groupid" value="<?php echo $groupid; ?>" />
                                            <input type="hidden" name="comment_id" id="commentId" />
                                            <input type="hidden" name="author" value="<?php echo $_SESSION['user']['fullname'] ?>" />

                                            <div class="input-group">
                                                <img id="commentPic" src="<?php echo '../assets/profile_pictures/' .$_SESSION['user']['image'] ?>" id="preview" class="rounded-circle mt-3 mx-2" style="width: 40px; height: 40px;" alt="Avatar" />
                                                <textarea class="form-control mx-3" name="comment" id="comment" placeholder="Your comment here"></textarea>
                                                <button id="publishBtn" class="btn" type="button">
                                                    <img src="<?php echo '../assets/images/send_button.svg'; ?>" style="height: 32px; width: 32px;">
                                                </button>
                                            </div>
                                        </form>
                                        <div id="comment_spinner" class="loader"></div>
                                        <div id="view_comment"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script>
        var taskStart = $("#task_start").flatpickr({
            static: true,
            enableTime: true,
            minDate: "today",
            dateFormat: "y-m-d H:i",
            onChange: function(selectedDates) {
                taskEnd.flatpickr({
                    disable: new Date(selectedDates)
                });
            }
        });

        var taskEnd = $("#task_end").flatpickr({
            static: true,
            enableTime: true,
            minDate: "today",
            dateFormat: "y-m-d H:i"
        });

        function selectName(val) {
            $("#group_leader").val(val);
            $("#show-leader").html("");
        }

        function selectAddMember(val) {
            $("#add_member").val(val);
            $("#show-add-member").html("");
        }

        function selectRemoveMember(val) {
            $("#remove_member").val(val);
            $("#show-remove-member").html("");
        }

        $("#refresh_code").click(function () {
            $.ajax({
                dataType: 'text',
                    type: 'POST',
                    url: "../src/refresh_code.php",
                    data: {
                        groupid: <?php echo $groupid; ?>
                    },
                    success: function (response)
                    {
                        displayCode();
                    }
                });
        });

        $("#createbtn").click(function (e) {
            e.preventDefault();

            var str = { 
                assigned_to: $('#assigned_to').val(),
                task_title: $('#task_title').val(),
                task_detail: $('#task_detail').val(),
                task_start: $('#task_start').val(),
                task_end: $('#task_end').val(),
                id: <?php echo $groupid ?>,
                adminid: <?php echo $_SESSION['user']['id'] ?>,
            };

            $.ajax({
                dataType: 'text',
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded',
                url: "../src/create_task.php",
                data: str,
                success: function (response)
                {
                    if(response=="empty_field") {
                        Swal.fire(
                                    'Error',
                                    'Please fill out all the fields!',
                                    'error'
                                )
                    }
                    else if (response=="time_conflict") {
                        Swal.fire(
                                    'Error',
                                    'Your end date cannot conflict with start date!',
                                    'error'
                                )
                    }
                    else {
                        location.reload();
                    }
                }
                
            });
        });

        $("#group_leader").keyup(function () {
            let searchText = $(this).val();
            if (searchText != "") {
                $.ajax({
                    dataType: 'text',
                    type: 'POST',
                    contentType: 'application/x-www-form-urlencoded',
                    url: "../src/search_leader.php", // reuse.
                    data: {
                        query: searchText,
                    },
                    success: function (response) {
                        $("#show-leader").html(response);
                    },
                });
            } else {
                $("#show-leader").html("");
            }
        });

        $("#add_member").keyup(function () {
            let searchText = $(this).val();
            if (searchText != "") {
                $.ajax({
                    dataType: 'text',
                    type: 'POST',
                    contentType: 'application/x-www-form-urlencoded',
                    url: "../src/search_add_member.php",
                    data: {
                        query: searchText,
                    },
                    success: function (response) {
                        $("#show-add-member").html(response);
                    },
                });
            } else {
                $("#show-add-member").html("");
            }
        });

        $("#remove_member").keyup(function () {
            let searchText = $(this).val();
            if (searchText != "") {
                $.ajax({
                    dataType: 'text',
                    type: 'POST',
                    contentType: 'application/x-www-form-urlencoded',
                    url: "../src/search_member.php",
                    data: {
                        id: <?php echo $groupid; ?>,
                        query: searchText,
                    },
                    success: function (response) {
                        $("#show-remove-member").html(response);
                    },
                });
            } else {
                $("#show-remove-member").html("");
            }
        });

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
                            type: 'POST', 
                            url: '../src/comment_delete.php',
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
                    url: "../src/comment_add.php",
                    data: str,
                    type: 'POST',
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
            displayCode();

            $('#code_spinner').hide();
            $('#comment_spinner').hide();
        });

        function displayCode() {
            $.ajax({
                url:"../src/display_code.php",
                data: {'groupid' : <?php echo $groupid; ?>},
                method:"POST",
                beforeSend : function () {  
                    $('#code_spinner').show(); 
                },
                success:function(response)
                {
                    $('#group_code').html(response);
                },
                complete : function () {  
                    $('#code_spinner').hide(); 
                },
            })
        }

        function listComment() {
            $('#replying_to').show();
            $('#replying_to').hide();

            $.ajax({
                url:"../src/comment_list.php",
                data: {'groupid' : <?php echo $groupid; ?>},
                method:"POST",
                beforeSend : function () {  
                    $('#comment_spinner').show(); 
                },
                success:function(response)
                {
                    $('#view_comment').html(response);
                },
                complete : function () {  
                    $('#comment_spinner').hide(); 
                },
            })
        }
    </script>
</html>