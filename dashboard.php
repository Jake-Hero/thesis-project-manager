<?php
    require "./libs/functions.php";
    
    is_user_valid();
    is_user_login();
    
    // Visit Detection
    recordUserVisit();

    $currentPage = 'dashboard';
    require('./libs/header.php');

    $event_array = array();

    $groupid = $_SESSION['user']['group_id'];

    if($_SESSION['user']['group_id'] >= 1) {    
        $query = "SELECT * FROM tasks WHERE taskgroup = :id";
        $selectStmt = $con->prepare($query);
        $selectStmt->execute(['id' => $groupid]);

        $result = $selectStmt->fetchAll();
    
        foreach($result as $row)
        {
            $row['sdate'] = date("F d, Y h:i A", strtotime($row['taskstart']));
            $row['edate'] = date("F d, Y h:i A", strtotime($row['taskdue']));
            $event_array[$row['taskid']] = $row;
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Dashboard</title> 
        <link rel="stylesheet" href="./fullcalendar/lib/main.min.css">
        <script src="./fullcalendar/lib/main.min.js"></script>

        <style>
            #calendar .fc-view {
                background-color: #EEEEEE;
            }

            .fc-event-today {
                background: #000000;
            }

            .fc .fc-toolbar-title
            {
                color: #FFFFFF;
            }

            .fc-day-today {
                background: #FFF !important;
            } 

            .btn-info.text-light:hover,
            .btn-info.text-light:focus {
                background: #000;
            }
            table, tbody, td, tfoot, th, thead, tr {
                border-color: #ededed !important;
                border-style: solid;
                border-width: 1px !important;
            }
        </style>
    </head>

    <body>
        <?php if($groupid > 0): ?>
        <div class="wrapper">
            <div class="container-fluid mt-3 mb-3">
                <?php 
                    if(!is_user_verified())
                    {
                        echo 
                        "
                            <script type=\"text/javascript\">
                            swal({
                                title: \"Verification\",
                                type: \"warning\",
                                text: \"You are not verified yet, Please verify your account via 'Edit My Profile'.\",
                                allowOutsideClick: false,
                                showConfirmButton: true,
                                confirmButtonText: 'OK'
                                });
                            </script>   
                        "; 
                    }
                ?>

                <div class="row">
                    <div class="col-md-9">
                        <div id="calendar"></div>

                        <!-- Event Details Modal -->
                        <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="task-modal">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-0">
                                    <div class="modal-header rounded-0">
                                        <h5 class="modal-title">Task Information</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body rounded-0">
                                        <div class="container-fluid">
                                            <dl>
                                                <dt class="text-muted">Task</dt>
                                                <dd id="title" class="fw-bold fs-4"></dd>
                                                <dt class="text-muted">Details</dt>
                                                <dd id="description" class=""></dd>
                                                <dt class="text-muted">Start</dt>
                                                <dd id="start" class=""></dd>
                                                <dt class="text-muted">Due Date</dt>
                                                <dd id="end" class=""></dd>
                                                <dt class="text-muted">Assigned To</dt>
                                                <dd id="assignedto" class=""></dd>
                                                <dt class="text-muted">Assigned By</dt>
                                                <dd id="assignedby" class=""></dd>
                                            </dl>
                                        </div>
                                    </div>
                                    <div class="modal-footer rounded-0">
                                        <div class="text-end">
                                            <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="col">
                            <div class="card">
                                <div class="card-header text-white" style="background-color: #800000; font-family: 'Lemon/Milk', sans-serif;">
                                    Group Deadlines
                                </div>
                                
                                <div class="card-body">
                                    <?php
                                    $selectStmt = $con->prepare('SELECT * FROM tasks WHERE taskgroup = :id ORDER BY taskid DESC;');
                                    $selectStmt->execute(['id' => $groupid]);
                                    $count = 0;

                                    if($selectStmt->rowCount() > 0):
                                        while($rows = $selectStmt->fetch(PDO::FETCH_ASSOC)): 
                                            if(time() > strtotime($row['taskdue']))
                                                continue;

                                            $count ++;
                                    ?>
          
                                            <div class='jumbotron border-bottom border-3 mb-3'>
                                                <div class='container bg-light' style='background-image: var(--bs-gradient);'>

                                                    <a href="task.php?id=<?php echo $rows['taskid']; ?>" title="View" data-toggle="tootlip">

                                                        <small>
                                                            <?php
                                                            switch($rows['taskstatus']) {
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
                                                        </small>

                                                    <p class="h5">
                                                            <?php 
                                                                echo $rows['tasktitle'];
                                                            ?>
                                                        </p>
                                                    </a>

                                                    <div class="text-secondary">
                                                        <small><b>Assigned to <?php echo getFullName($rows['taskassignedto']); ?></b></small><br>
                                                        <small><b><?php echo getweekDay($rows['taskdue']) . ', ' .  date("F j g:i a", strtotime($rows["taskdue"])); ?></b></small>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php endwhile; ?>
                                                
                                            <?php if($count <= 0): ?>
                                                No deadlines as of the moment.
                                            <?php endif; ?>

                                        <?php else: ?>

                                            No deadlines as of the moment.

                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>

                        <div class="col mt-5">
                            <div class="card">
                                <div class="card-header text-white" style="background-color: #800000; font-family: 'Lemon/Milk', sans-serif;">
                                    Comments on your Group
                                </div>
                                <div class="card-body">
                                    <div id="view_comment"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>       
            </div>
        </div>
        <?php else: ?>
        <div class="grey-wrapper">
            <div class="container py-5 h-100">
                <form method="post" enctype="multipart/form-data" class="px-4 py-3">
                    <h3 class="text-center border-bottom border-3 border-danger" style="font-family: 'Times New Roman'; font-weight: bold;">You don't have a group yet!</h3>
                    <p class="text-center">You are not assigned to a group yet, please message your <strong>Adviser</strong>. Your adviser will assign you to a group or give you a group code for you to type in the box below.</p>

                    <?php if(!empty($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger d-flex align-items-center fade show">
                            <i class='fas fa-exclamation-triangle'></i>
                            <div class ="mx-3">
                                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mt-5 row d-flex justify-content-sm-center justify-content-md-center justify-content-lg-center align-items-center">
                        <div class="col-md-6">
                            <div class="col">
                                <label for="" class="col col-form-label">Please type the group code given to you by your adviser.</label>
                                <input type="text" name="join_field" class="col form-control" placeholder="Group Code">
                            </div>

                            <div class="row mt-3 mx-auto">
                                <input type="submit" name="joinBtn" value="Join" class="rounded-pill btn btn-warning border border-light btn-lg">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </body>

    <script>
        var scheds = <?= json_encode($event_array) ?>;

        var calendar;
        var Calendar = FullCalendar.Calendar;
        var events = [];

        $(document).ready(function () {
            listComment();

            if (!!scheds) {
                Object.keys(scheds).map(k => {
                    var row = scheds[k]
                    events.push({ id: row.taskid, title: row.tasktitle, start: row.taskstart, end: row.taskdue });
                })
            }

            calendar = new Calendar(document.getElementById('calendar'), {
                selectable: true,
                themeSystem: 'bootstrap',

                events: events,
                eventColor: '#FFD700',
                eventBackgroundColor: '#FFD700',
                eventTextColor: '#000000',
                eventContent: function(event, el) {
                    if(event.isEnd) {
                        console.log('oh no');
                    }
                },
                eventClick: function(info) {
                    var _details = $('#task-modal');
                    var id = info.event.id;
                    if (!!scheds[id]) {
                        _details.find('#title').text(scheds[id].tasktitle);
                        _details.find('#description').text(scheds[id].taskdetail);
                        _details.find('#start').text(scheds[id].sdate);
                        _details.find('#end').text(scheds[id].edate);
                        $.ajax({
                            dataType: 'text',
                            type: 'POST',
                            contentType: 'application/x-www-form-urlencoded',
                            url:"./src/retrieve_name.php",
                            data: {'userid' : scheds[id].taskassignedto},
                            success:function(response)
                            {
                                _details.find('#assignedto').text(response);
                            }
                        })
                        $.ajax({
                            dataType: 'text',
                            type: 'POST',
                            contentType: 'application/x-www-form-urlencoded',
                            url:"./src/retrieve_name.php",
                            data: {'userid' : scheds[id].taskadmin},
                            success:function(response)
                            {
                                _details.find('#assignedby').text(response);
                            }
                        })

                        _details.modal('show');
                    }
                },
                editable: true
            });

            calendar.render();
        });

        function listComment() {
            $.ajax({
                dataType: 'text',
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded',
                url:"./src/dashboard_comment.php",
                data: {'groupid' : <?php echo $groupid; ?>},
                success:function(response)
                {
                    $('#view_comment').html(response);
                }
            })
        }
    </script>
</html>