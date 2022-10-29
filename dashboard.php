<?php
    require "includes/functions.php";
    
    is_user_valid();
    is_user_login();
    
    // Visit Detection
    recordUserVisit();

    $currentPage = 'dashboard';
    require('includes/header.php');

    $event_array = array();

    if($_SESSION['user']['group_id'] >= 1) {
        $groupid = $_SESSION['user']['group_id'];
    
        $query = "SELECT * FROM tasks WHERE taskgroup = :id";
        $selectStmt = $con->prepare($query);
        $selectStmt->execute(['id' => $groupid]);
    }
    
    $result = $selectStmt->fetchAll();
    
    foreach($result as $row)
    {
        $row['sdate'] = date("F d, Y h:i A", strtotime($row['taskstart']));
        $row['edate'] = date("F d, Y h:i A", strtotime($row['taskdue']));
        $event_array[$row['taskid']] = $row;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Dashboard</title> 
        <link rel="stylesheet" href="fullcalendar/lib/main.min.css">
        <script src="fullcalendar/lib/main.min.js"></script>

        <style>
            #calendar .fc-view {
                background-color: #EEEEEE;
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
                        <div class="modal fade" tabindex="-1" data-bs-backdrop="static" id="event-details-modal">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-0">
                                    <div class="modal-header rounded-0">
                                        <h5 class="modal-title">Schedule Details</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body rounded-0">
                                        <div class="container-fluid">
                                            <dl>
                                                <dt class="text-muted">Title</dt>
                                                <dd id="title" class="fw-bold fs-4"></dd>
                                                <dt class="text-muted">Description</dt>
                                                <dd id="description" class=""></dd>
                                                <dt class="text-muted">Start</dt>
                                                <dd id="start" class=""></dd>
                                                <dt class="text-muted">End</dt>
                                                <dd id="end" class=""></dd>
                                            </dl>
                                        </div>
                                    </div>
                                    <div class="modal-footer rounded-0">
                                        <div class="text-end">
                                            <button type="button" class="btn btn-primary btn-sm rounded-0" id="edit" data-id="">Edit</button>
                                            <button type="button" class="btn btn-danger btn-sm rounded-0" id="delete" data-id="">Delete</button>
                                            <button type="button" class="btn btn-secondary btn-sm rounded-0" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3" style="background-color: #FFFFFF">
                        test</div>
                </div>       
            </div>
        </div>
    </body>

    <script>
        var scheds = <?= json_encode($event_array) ?>;

        var calendar;
        var Calendar = FullCalendar.Calendar;
        var events = [];

        $(document).ready(function () {
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
                eventColor: '#A020F0',
                eventBackgroundColor: '#A020F0',
                eventClick: function(info) {
                    var _details = $('#event-details-modal');
                    var id = info.event.id;
                    if (!!scheds[id]) {
                        _details.find('#title').text(scheds[id].tasktitle);
                        _details.find('#description').text(scheds[id].taskdetail);
                        _details.find('#start').text(scheds[id].sdate);
                        _details.find('#end').text(scheds[id].edate);
                        _details.find('#edit,#delete').attr('data-id', scheds[id].taskid);
                        _details.modal('show');
                    }
                },
                editable: true
            });

            calendar.render();
        });
    </script>
</html>