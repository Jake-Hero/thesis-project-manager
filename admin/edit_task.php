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
		$taskid = $_GET['id'];

        $query = "SELECT * FROM tasks WHERE taskid = :id LIMIT 1;";
        $selectStm = $con->prepare($query);
        $selectStm->execute(['id' => $taskid]);
        $row = $selectStm->fetch(PDO::FETCH_ASSOC);

        $groupid = $row['taskgroup'];

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
            $row = adminEditTask($taskid);
        }
    }

    $_SESSION['taskid'] = $taskid;
    $currentPage = 'edit_task';

    require('../includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('../head.php')?>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js" integrity="sha512-K/oyQtMXpxI4+K0W7H25UopjM8pzq0yrVdFdG21Fh5dBe91I40pDd9A4lzNlHPHBIP2cwZuoxaUSX0GJSObvGA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css" integrity="sha512-MQXduO8IQnJVq1qmySpN87QQkiR1bZHtorbJBD0tzy7/0U9+YIC93QWHeGTEoojMVHWWNkoCp8V6OzVSYrX0oQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <title>Thesis & Capstone Manager - Edit Task</title>   
        <style>
            #ddArea {
                height: auto;
                width: auto;
            }

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

            #file_content
            { 
                height: 110vh; 
                overflow-x: scroll; 
                overflow-y: auto;
                width: 100%; 
            }
        </style>   
    </head>

    <body> 
        <div class="grey-wrapper">
            <div class="container-fluid mt-4 mb-5">
                <div class="row">
                    <div class="col mb-3">
                        <a href="<?php echo ROOT_FOLDER . "/admin/edit_group.php?id=" . $groupid; ?>"><button type="button" class="btn btn-warning btn-md">Go Back to the Group</button></a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header text-white" style="background-color: #A020F0; font-family: cursive;"><?php echo "Viewing Task " .$row['tasktitle'] . ' (assigned to: ' . getFullName($row['taskassignedto']) . ')'; ?></div>
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
                            <div class="col-md-5 col-md-offset-1 border-end">
                            <h4 class="text-center border-bottom border-3 border-danger" style="font-family: 'Times New Roman'; font-weight: bold;">Edit Task</h4>

                                <div class="p-3">
                                    <form method="post" enctype="multipart/form-data">

                                        <div class="form-group has-error md-form mb-4">
                                            <label data-error="wrong" data-success="right" for="">Assign to</label>
                                            <select id="assigned_to" name="assigned_to" class="form-select" aria-label="">
                                                <option selected><?php echo 'current: ' . getFullName($row['taskassignedto']); ?></option>
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
                                            <label data-error="wrong" data-success="right" for="">Status</label>
                                            <select id="task_status" name="task_status" class="form-select" aria-label="">
                                                <option selected>Status ...</option>

                                                <option value="0" class="text-danger">
                                                    Incomplete
                                                </option>

                                                <option value="1" class="text-warning">
                                                    Pending
                                                </option>

                                                <option value="2" class="text-success">
                                                    Complete
                                                </option>
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

                                        <div class="form-group row has-error mt-3 mb-4">
                                            <label data-error="wrong" data-success="right" for="task_start">Start Date</label>
                                            <input type="text" name="task_start" id="task_start" class="form-control" placeholder="<?php echo $row['taskstart']; ?>" disabled>
                                        </div>

                                        <div class="form-group row has-error mt-3 mb-4">
                                            <label data-error="wrong" data-success="right" for="task_end">End Date</label><br>
                                            <input type="text" name="task_end" id="task_end" class="form-control">
                                        </div>

                                        <div class="row mt-5">
                                            <div class="col d-flex justify-content-center">
                                                <input type="submit" name="save" value="Save Changes" class="rounded-pill btn btn-lg btn-warning">
                                            </div>
                                            <div class="col d-flex justify-content-center">
                                                <button type="button" id="delete" class="rounded-pill btn btn-lg btn-danger">Delete</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="col-md-7">
                                <h4 class="text-center border-bottom border-3 border-danger" style="font-family: 'Times New Roman'; font-weight: bold;">File Management</h4>
                                
                                <div id="ddArea" class="mt-3 mb-2 text-end">
                                    <a class="btn btn-warning btn-sm" type="submit"><i class="fa-sharp fa-solid fa-plus"></i> Upload File</a>
                                    <input type="file" class="d-none" id="selectfile" multiple />
                                </div>                                        
                                <div class="progress" id="progress_bar" style="display:none; ">
                                    <div class="progress-bar" id="progress_bar_process" role="progressbar" style="width:0%">0%</div>
                                </div>

                                <div id="file_content"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script>
        $(document).ready(function() {
            listFiles();

            $("#ddArea").on("click", function(e) {
                file_explorer();
            });

            $("#ddArea").on("drop", function(e) {
                e.preventDefault();

                var formData = new FormData();
                var files = e.originalEvent.dataTransfer.files;
                for (var i = 0; i < files.length; i++) {
                    formData.append("file[]", files[i]);
                }
                uploadFormData(formData);
            });
        });

        var taskStart = $("#task_start").flatpickr({
            static: true,
            enableTime: true,
            minDate: "today",
            dateFormat: "F j, Y h:i K",
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
            dateFormat: "F j, Y h:i K"
        });

        document.getElementById('delete').onclick = function(){
            var taskid = <?php echo $taskid; ?>;
            var groupid = <?php echo $groupid; ?>;

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
                        url: '../src/delete_task',
                        data: {'taskid' : taskid},
                        success: function(response) {
                            if(response=="success") {
                                Swal.fire(
                                    'Deleted',
                                    'You have deleted the task.',
                                    'success'
                                ).then(function() {
                                    window.location.href = "/thesis-project-manager/admin/edit_group.php?id=" + groupid;
                                });
                            } else {
                                Swal.fire(
                                    'Error',
                                    'Something went wrong on deleting the task.',
                                    'error'
                                )
                            }
                        }
                    });
                }
            });
        }

        function file_explorer() {
            document.getElementById("selectfile").click();
            document.getElementById("selectfile").onchange = function() {
                files = document.getElementById("selectfile").files;
                var formData = new FormData();

                for (var i = 0; i < files.length; i++) {
                formData.append("file[]", files[i]);
                }
                uploadFormData(formData);
            };
        }

        function uploadFormData(form_data) {
            document.getElementById('progress_bar').style.display = 'block';

            var ajax_request = new XMLHttpRequest();
            ajax_request.open("POST", "../src/file_upload");

            ajax_request.upload.addEventListener('progress', function(event){
                var percent_completed = Math.round((event.loaded / event.total) * 100);
                document.getElementById('progress_bar_process').style.width = percent_completed + '%';
                document.getElementById('progress_bar_process').innerHTML = percent_completed + '% completed';
            
                if(percent_completed >= 100)
                {
                    listFiles();
                }
            });

            ajax_request.addEventListener('load', function(event){
                document.getElementById('selectfile').value = '';
            });

            ajax_request.send(form_data);
        }

        function listFiles() {
            $.ajax({
                dataType: 'text',
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded',
                url:"../src/admin_file_list",
                data: {
                    'groupid' : <?php echo $groupid; ?>
                },
                success:function(response)
                {
                    $('#file_content').html(response);
                }
            })
        }
    </script>
</html>