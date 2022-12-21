<?php
    require "./libs/functions.php";
    is_user_valid();
    is_user_login();
    
    if(!isset($_GET['id']))
    {
        header("Location: ./group.php");
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
            header("Location: ./group.php");
            die;
        }

        if($_SESSION['user']['role'] < ROLE_PANELIST && $row['taskgroup'] != $_SESSION['user']['group_id'])
        {
            header("Location: ./group.php");
            die;
        }
    }

    $_SESSION['taskid'] = $taskid;
    $currentPage = 'view_task';
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
        <script type="text/javascript" src="./js/lastseen.js"></script>
        <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous"/>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script src="./bootstrap/js/bootstrap.min.js"></script>

        <title>Thesis & Capstone Manager - Dashboard</title>
        <style>
            #ddArea {
                height: 200px;
                border: 2px dashed #ccc;
                line-height: 200px;
                text-align: center;
                font-size: 20px;
                background: #f9f9f9;
                margin-bottom: 15px;
            }

            .drag_over {
                color: #000;
                border-color: #000;
            }

            .thumbnail {
                width: 100px;
                height: 100px;
                padding: 2px;
                margin: 2px;
                border: 2px solid lightgray;
                border-radius: 3px;
                float: left;
            }

            .d-none {
                display: none;
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
        </style>
    </head>

    <body>
        <div class="grey-wrapper">
            <div class="container-fluid mt-4 mb-5">

                <div class="card">
                    <div class="card-header text-white" style="background-color: #800000; font-family: cursive;"><?php echo "Viewing " .$row['tasktitle'] . " assigned to " . getFullName($row['taskassignedto']); ?></div>
                    <div class="card-body">            
                        <div class="row mx-auto d-flex justify-content-evenly mb-4">
                            <div class="col-md-12">   
                                <?php if($row['taskassignedto'] == $_SESSION['user']['id'] || $_SESSION['user']['role'] >= ROLE_ADVISOR): ?>
                                <div class="mb-3">
                                    <button id="undone" class="btn border"><i class="fa fa-times-circle"></i> Mark as Incomplete</button>
                                    <button id="pending" class="btn border">Mark as Pending</button>
                                    <button id="done" class="btn border"><i class="fa fa-check-circle" aria-hidden="true"></i> Mark as Finished</button>
                                </div>    
                                <?php endif; ?>
                            
                                <div class="row">
                                    <div class="col-sm-6">
                                        <dl>
                                            <dt><b class="border-bottom border-primary">Status</b></dt>
                                            <dd>
                                                <div id="code_spinner" class="loader"></div>
                                                <div id="task_status"></div>
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

                                                <?php if(time() >= strtotime($row['taskdue'])): ?>
                                                <dd class="text-danger">
                                                    <strong>This task is past-due!</strong>
                                                </dd>
                                                <?php endif; ?>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h4 class="text-start border-bottom border-3 border-danger" style="font-family: 'Times New Roman'; font-weight: bold;">View File Submission / Upload or Download Files</h4>
                        <div class="row mx-auto d-flex justify-content-evenly mb-4">
                            <div class="col-md-12">
                                <?php if($row['taskassignedto'] == $_SESSION['user']['id']): ?>
                                    <?php if(time() < strtotime($row['taskdue'])): ?>
                                        <div id="ddArea">
                                            Drag and Drop Files Here or
                                            <a class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded">
                                                Select File(s)
                                            </a>
                                        </div>
                                        <input type="file" class="d-none" id="selectfile" multiple />
                                        
                                        <div class="progress" id="progress_bar" style="display:none; ">
                                            <div class="progress-bar" id="progress_bar_process" role="progressbar" style="width:0%">0%</div>
                                        </div>
                                    <?php else : ?>

                                        This task is past-due, You can no longer upload a file! Contact your Adviser as soon as possible.

                                    <?php endif; ?>

                                <?php else: ?>

                                    This task is not assigned to you, You are only allowed to view and download files.

                                <?php endif; ?>

                                <div id="file_content"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    
    <script>
        function displayStatus() {
            $("#undone").click(function () {
                $.ajax({
                    dataType: 'text',
                        type: 'POST',
                        url: "./src/refresh_status.php",
                        data: {
                            taskid: <?php echo $taskid; ?>,
                            taskstatus: 0
                        },
                        success: function (response)
                        {
                            displayStatus();
                        }
                    });
            });
            $("#pending").click(function () {
                $.ajax({
                    dataType: 'text',
                        type: 'POST',
                        url: "./src/refresh_status.php",
                        data: {
                            taskid: <?php echo $taskid; ?>,
                            taskstatus: 1
                        },
                        success: function (response)
                        {
                            displayStatus();
                        }
                    });
            });
            $("#done").click(function () {
                $.ajax({
                    dataType: 'text',
                        type: 'POST',
                        url: "./src/refresh_status.php",
                        data: {
                            taskid: <?php echo $taskid; ?>,
                            taskstatus: 2
                        },
                        success: function (response)
                        {
                            displayStatus();
                        }
                    });
            });

            $.ajax({
                url:"./src/display_status.php",
                data: {'taskid' : <?php echo $taskid; ?>},
                method:"POST",
                beforeSend : function () {  
                    $('#code_spinner').show(); 
                },
                success:function(response)
                {
                    $('#task_status').html(response);
                },
                complete : function () {  
                    $('#code_spinner').hide(); 
                },
            })
        }

        $(document).ready(function() {
            $('#code_spinner').hide();
            displayStatus();
            listFiles();

            $("#ddArea").on("dragover", function() {
                $(this).addClass("drag_over");
                return false;
            });

            $("#ddArea").on("dragleave", function() {
                $(this).removeClass("drag_over");
                return false;
            });

            $("#ddArea").on("click", function(e) {
                file_explorer();
            });

            $("#ddArea").on("drop", function(e) {
                e.preventDefault();
                $(this).removeClass("drag_over");
                var formData = new FormData();
                var files = e.originalEvent.dataTransfer.files;
                for (var i = 0; i < files.length; i++) {
                    formData.append("file[]", files[i]);
                }
                formData.append("groupid", <?php echo $groupid; ?>);
                uploadFormData(formData);
            });

            function file_explorer() {
                document.getElementById("selectfile").click();
                document.getElementById("selectfile").onchange = function() {
                    files = document.getElementById("selectfile").files;
                    var formData = new FormData();

                    for (var i = 0; i < files.length; i++) {
                    formData.append("file[]", files[i]);
                    }
                    formData.append("groupid", <?php echo $groupid; ?>);
                    uploadFormData(formData);
                };
            }

            function uploadFormData(form_data) {
                document.getElementById('progress_bar').style.display = 'block';

                var ajax_request = new XMLHttpRequest();
                ajax_request.open("POST", "./src/file_upload.php");

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
        });

        function listFiles() {
            $.ajax({
                dataType: 'text',
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded',
                url:"src/file_list.php",
                data: {
                    'task_id' : <?php echo $taskid; ?>,
                    'assigned_to' : <?php echo $row['taskassignedto']; ?>,
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