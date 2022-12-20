<?php
    require "../libs/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_PANELIST)
    {
        header("Location: ../dashboard.php");
        die;
    }

    if(!isset($_GET['id']))
    {
        header("Location: ./grades.php");
        die;
    }

    $grading = 'preoral';

    if(isset($_GET['id']))
	{
		$userid = $_GET['id'];

        $query = "SELECT g.*, u.id, u.email_verified FROM grades AS g INNER JOIN users AS u ON u.id = g.userid WHERE g.userid = :id LIMIT 1;";
        $selectStmt = $con->prepare($query);
        $selectStmt->execute(['id' => $userid]);
        $row = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if(!$row)
        {
            header("Location: ./grades.php");
            die;
        }
    }

    if($_SESSION['user']['role'] == ROLE_ADVISOR && $row['advised_by'] != $_SESSION['user']['id'])
    {
        header("Location: ./grades.php");
        die;
    }

    if(!isset($_GET['grading']))
    {
        header("Location: ./edit_grade.php?id=" . $_GET['id'] . "&grading=preoral");
        die;        
    }

    if(!($_GET['grading'] == 'preoral' || $_GET['grading'] == 'oral'))
    {
        header("Location: ./edit_grade.php?id=" . $_GET['id'] . "&grading=preoral");
        die;        
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['submitbtn']))
        {
            if($_GET['grading'] == 'preoral')
                $validFields = array('introduction', 'completeness', 'organization', 'speaking', 'presentation', 'qna');
            else
                $validFields = array('introduction', 'completeness', 'organization', 'findings', 'speaking', 'presentation', 'qna');
        
            foreach ($validFields as $field) {
                if(!empty($_POST[$field]) && (!is_numeric($_POST[$field]))) {
                    $_SESSION['error_message'] = "The field must be in integer form.";
                } else if(!empty($_POST[$field]) && ($_POST[$field] > 5 || $_POST[$field] < 1)) {
                    $_SESSION['error_message'] = "The minimum score that can be given is 1, while the highest is 5";
                }
            }

            if(empty($_SESSION['error_message']))
                $_SESSION['success_message'] = "Score & Gradings has been updated!";

            if(empty($_SESSION['error_message'])) {
                if(!empty($_POST['introduction'])) {
                    if($_GET['grading'] == 'preoral') {
                        $query = "UPDATE grades SET pre_rubrics_1 = :grade WHERE userid = :id";
                    } else {
                        $query = "UPDATE grades SET oral_rubrics_1 = :grade WHERE userid = :id";
                    }

                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['introduction'], 'id' => $userid]);
                }
                if(!empty($_POST['completeness'])) {
                    if($_GET['grading'] == 'preoral') {
                        $query = "UPDATE grades SET pre_rubrics_2 = :grade WHERE userid = :id";
                    } else {
                        $query = "UPDATE grades SET oral_rubrics_2 = :grade WHERE userid = :id";
                    }

                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['completeness'], 'id' => $userid]);
                }
                if(!empty($_POST['organization'])) {
                    if($_GET['grading'] == 'preoral') {
                        $query = "UPDATE grades SET pre_rubrics_3 = :grade WHERE userid = :id";
                    } else {
                        $query = "UPDATE grades SET oral_rubrics_3 = :grade WHERE userid = :id";
                    }

                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['organization'], 'id' => $userid]);
                }
                if(!empty($_POST['findings'])) {
                    $query = "UPDATE grades SET oral_rubrics_7 = :grade WHERE userid = :id";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['findings'], 'id' => $userid]);
                }
                if(!empty($_POST['speaking'])) {
                    if($_GET['grading'] == 'preoral') {
                        $query = "UPDATE grades SET pre_rubrics_4 = :grade WHERE userid = :id";
                    } else {
                        $query = "UPDATE grades SET oral_rubrics_4 = :grade WHERE userid = :id";
                    }

                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['speaking'], 'id' => $userid]);
                }
                if(!empty($_POST['presentation'])) {
                    if($_GET['grading'] == 'preoral') {
                        $query = "UPDATE grades SET pre_rubrics_5 = :grade WHERE userid = :id";
                    } else {
                        $query = "UPDATE grades SET oral_rubrics_5 = :grade WHERE userid = :id";
                    }

                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['presentation'], 'id' => $userid]);
                }
                if(!empty($_POST['qna'])) {
                    if($_GET['grading'] == 'preoral') {
                        $query = "UPDATE grades SET pre_rubrics_6 = :grade WHERE userid = :id";
                    } else {
                        $query = "UPDATE grades SET oral_rubrics_6 = :grade WHERE userid = :id";
                    }

                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['qna'], 'id' => $userid]);
                }

                header("Refresh: 0;");
            }
        }
    }

    $currentPage = 'edit_grade';

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
        <title>Thesis & Capstone Manager - Edit Grade</title>   
        
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

            table.table tr th, table.table tr td {
                border: 1px solid rgba(0, 0, 0, 0.1);
                background-color: rgba(240, 240, 240, 0.1) !important;
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
        <?php $grading = $_GET['grading']; ?>
        <div class="grey-wrapper">
            <div class="container-fluid mt-4 mb-5">
                <div class="row">
                    <div class="col mb-3">
                        <a href="./grades.php"><button type="button" class="btn btn-warning btn-md">Go Back to the List</button></a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header text-black-50" style="background-color: #FFD700; font-family: cursive;"><?php echo "You are now viewing the grades of " . getFullName($row['id']); ?></div>
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

                                <div class="mt-2">
                                    <div class="col-md-3">
                                        <select id="redirect_select" class="form-select me-2">
                                            <option value="<?php echo './edit_grade.php?id=' . $userid . '&grading=preoral'; ?>" <?php if(isset($_GET['grading']) && $_GET['grading'] == 'preoral') echo 'selected' ?>>Pre-Oral Defense</option>
                                            <option value="<?php echo './edit_grade.php?id=' . $userid . '&grading=oral'; ?>" <?php if(isset($_GET['grading']) && $_GET['grading'] == 'oral') echo 'selected' ?>>Oral Defense</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <form method="post" enctype="multipart/form-data">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr class="table-light text-center">
                                                <th scope="col" class="col-md-3">Aspect</th>
                                                <th scope="col" class="col-md-2">Score</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="table-light text-center">
                                                <td>Introduction</td>
                                                <td>
                                                    <?php 
                                                        if($_GET['grading'] == 'preoral')
                                                            echo $row['pre_rubrics_1'];
                                                        else
                                                            echo $row['oral_rubrics_1'];
                                                    ?>
                                                </td>
                                                <td>
                                                    <input type="text" id="introduction" name="introduction" class="form-control" placeholder="(1 - 5)">
                                                </td>
                                            </tr>                                            
                                            <tr class="table-light text-center">
                                                <td>Completeness</td>
                                                <td>
                                                    <?php 
                                                        if($_GET['grading'] == 'preoral')
                                                            echo $row['pre_rubrics_2'];
                                                        else
                                                            echo $row['oral_rubrics_2'];
                                                    ?>
                                                </td>
                                                <td>
                                                    <input type="text" id="completeness" name="completeness" class="form-control" placeholder="(1 - 5)">
                                                </td>
                                            </tr>            

                                            <tr class="table-light text-center">
                                                <td>Organization</td>
                                                <td>
                                                    <?php 
                                                        if($_GET['grading'] == 'preoral')
                                                            echo $row['pre_rubrics_3'];
                                                        else
                                                            echo $row['oral_rubrics_3'];
                                                    ?>
                                                </td>
                                                <td>
                                                    <input type="text" id="organization" name="organization" class="form-control" placeholder="(1 - 5)">
                                                </td>
                                            </tr>    

                                            <?php if($_GET['grading'] == 'oral'): ?>

                                            <tr class="table-light text-center">
                                                <td>Findings</td>
                                                <td><?php echo $row['oral_rubrics_7']; ?></td>
                                                <td>
                                                    <input type="text" id="findings" name="findings" class="form-control" placeholder="(1 - 5)">
                                                </td>
                                            </tr>  

                                            <?php endif; ?>

                                            <tr class="table-light text-center">
                                                <td>Speaking Skills</td>
                                                <td>
                                                    <?php 
                                                        if($_GET['grading'] == 'preoral')
                                                            echo $row['pre_rubrics_4'];
                                                        else
                                                            echo $row['oral_rubrics_4'];
                                                    ?>
                                                </td>
                                                <td>
                                                    <input type="text" id="speaking" name="speaking" class="form-control" placeholder="(1 - 5)">
                                                </td>
                                            </tr>

                                            <tr class="table-light text-center">
                                                <td>Presentation</td>
                                                <td>
                                                    <?php 
                                                        if($_GET['grading'] == 'preoral')
                                                            echo $row['pre_rubrics_5'];
                                                        else
                                                            echo $row['oral_rubrics_5'];
                                                    ?>
                                                </td>
                                                <td>
                                                    <input type="text" id="presentation" name="presentation" class="form-control" placeholder="(1 - 5)">
                                                </td>
                                            </tr>

                                            <tr class="table-light text-center">
                                                <td>Questions and Answers</td>
                                                <td>
                                                    <?php 
                                                        if($_GET['grading'] == 'preoral')
                                                            echo $row['pre_rubrics_6'];
                                                        else
                                                            echo $row['oral_rubrics_6'];
                                                    ?>
                                                </td>
                                                <td>
                                                    <input type="text" id="qna" name="qna" class="form-control" placeholder="(1 - 5)">
                                                </td>
                                            </tr>

                                            <tr class="table-light text-center">
                                                <?php
                                                    if($_GET['grading'] == 'preoral') 
                                                        $total = ($row["pre_rubrics_1"] + $row['pre_rubrics_2'] + $row['pre_rubrics_3'] + $row['pre_rubrics_4'] + $row['pre_rubrics_5'] + $row['pre_rubrics_6']);
                                                    else 
                                                        $total = ($row["oral_rubrics_1"] + $row['oral_rubrics_2'] + $row['oral_rubrics_3'] + $row['oral_rubrics_4'] + $row['oral_rubrics_5'] + $row['oral_rubrics_6'] + $row['oral_rubrics_7']);
                                                ?>

                                                <td colspan="2" class="fw-bold">
                                                    <strong>Total Score: </strong>
                                                    <?php 
                                                        echo $total;
                                                    ?>
                                                </td>
                                                <td colspan="2" class="fw-bold">
                                                    <strong>Percentage: </strong>
                                                    <?php 
                                                        if($_GET['grading'] == 'preoral') 
                                                            echo getGradeConversion(1, $total);
                                                        else
                                                            echo getGradeConversion(2, $total);
                                                    ?>
                                                    %
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                <div class="text-center">
                                    <div class="row d-flex justify-content-center mt-5 mx-auto">
                                        <div class="col-md-1">
                                            <button type="submit" name="submitbtn" class="btn btn-lg text-white" style="background-color: #A020F0;">Submit</button>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" id="notifybtn" class="btn btn-lg text-white btn-danger" onclick="showAlertGrade(<?php echo $row['id']; ?>);">Notify</button>
                                        </div>
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

    <script>
        function showAlertGrade(id) {
            swal({
                title: 'Are you sure?',
                text: "You are about to release the grades to this user.",
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#33AA33',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Notify'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        dataType: 'text',
                        type: 'POST',
                        contentType: 'application/x-www-form-urlencoded',
                        url: '../src/notify_group.php',
                        data: {'userid' : id, 
                               'grading' : "<?php echo $grading; ?>"
                              },
                        success: function(response) {
                            if(response=="success") {
                                Swal.fire(
                                    'Notified',
                                    'You have released the grades through E-Mail to this user.',
                                    'success'
                                ).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error',
                                    'Something went wrong on releasing the grades.',
                                    'error'
                                )
                            }
                        }
                    });
                }
            });
        }

        $('#redirect_select').on('change', function(){
            window.location = $(this).val();
        });
    </script>
</html>