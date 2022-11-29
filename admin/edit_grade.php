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
        header("Location: " . ROOT_FOLDER . "/admin/grades.php");
        die;
    }

    $semester = 1;

    if(isset($_GET['id']))
	{
		$groupid = $_GET['id'];

        $query = "SELECT * FROM groups WHERE groupid = :id LIMIT 1;";
        $selectStm = $con->prepare($query);
        $selectStm->execute(['id' => $groupid]);
        $row = $selectStm->fetch(PDO::FETCH_ASSOC);

        if(!$row)
        {
            header("Location: " . ROOT_FOLDER . "/admin/grades.php");
            die;
        }
    }

    if(!isset($_GET['semester']))
    {
        header("Location: " . ROOT_FOLDER . "/admin/edit_grade.php?id=" . $_GET['id'] . "&semester=1");
        die;        
    }

    if($_GET['semester'] < 1)
    {
        header("Location: " . ROOT_FOLDER . "/admin/edit_grade.php?id=" . $_GET['id'] . "&semester=1");
        die;        
    }

    if($_GET['semester'] > 2)
    {
        header("Location: " . ROOT_FOLDER . "/admin/edit_grade.php?id=" . $_GET['id'] . "&semester=2");
        die;        
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        if(isset($_POST['submitbtn']))
        {
            $validFields = array('prelims', 'midterms', 'semis', 'finals');
        
            foreach ($validFields as $field) {
                if(!empty($_POST[$field]) && (!is_float($_POST[$field]) && !is_numeric($_POST[$field]))) {
                    $_SESSION['error_message'] = "The field must be in integer or decimal form.";
                } else if(!empty($_POST[$field]) && ($_POST[$field] > 100 || $_POST[$field] < 50)) {
                    $_SESSION['error_message'] = "The minimum grade that can be given is 50%, while the highest is 100%";
                }
            }

            if(empty($_SESSION['error_message'])) {
                if(!empty($_POST['prelims'])) {
                    $_SESSION['success_message'] = "Grade for Prelims has been updated!";

                    $query = "UPDATE grades SET prelims = :grade WHERE groupid = :id AND semester = :sem";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['prelims'], 'id' => $groupid, 'sem' => $_GET['semester']]);
                }
                if(!empty($_POST['midterms'])) {
                    $_SESSION['success_message'] = "Grade for Midterms has been updated!";

                    $query = "UPDATE grades SET midterms = :grade WHERE groupid = :id AND semester = :sem";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['midterms'], 'id' => $groupid, 'sem' => $_GET['semester']]);
                }
                if(!empty($_POST['semis'])) {
                    $_SESSION['success_message'] = "Grade for Semis has been updated!";

                    $query = "UPDATE grades SET semis = :grade WHERE groupid = :id AND semester = :sem";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['semis'], 'id' => $groupid, 'sem' => $_GET['semester']]);
                }
                if(!empty($_POST['finals'])) {
                    $_SESSION['success_message'] = "Grade for Finals has been updated!";

                    $query = "UPDATE grades SET finals = :grade WHERE groupid = :id AND semester = :sem";
                    $updateStmt = $con->prepare($query);
                    $updateStmt->execute(['grade' => $_POST['finals'], 'id' => $groupid, 'sem' => $_GET['semester']]);
                }
            }
        }
    }

    $currentPage = 'edit_grade';

    require('../includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('../head.php')?>
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
        <div class="grey-wrapper">
            <div class="container-fluid mt-4 mb-5">
                <div class="row">
                    <div class="col mb-3">
                        <a href="<?php echo ROOT_FOLDER . "/admin/grades.php"; ?>"><button type="button" class="btn btn-warning btn-md">Go Back to the List</button></a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header text-black-50" style="background-color: #FFD700; font-family: cursive;"><?php echo "You are now viewing the grades of " .$row['group_title']; ?></div>
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
                                            <option value="<?php echo 'http://localhost/' . ROOT_FOLDER . '/admin/edit_grade.php?id=' . $groupid . '&semester=1'; ?>" <?php if(isset($_GET['semester']) && $_GET['semester'] == 1) echo 'selected' ?>>First Semester</option>
                                            <option value="<?php echo 'http://localhost/' . ROOT_FOLDER . '/admin/edit_grade.php?id=' . $groupid . '&semester=2'; ?>" <?php if(isset($_GET['semester']) && $_GET['semester'] == 2) echo 'selected' ?>>Second Semester</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <form method="post" enctype="multipart/form-data">
                                    <?php if($_GET['semester'] == 1): ?>
                                        <?php
                                            $selectStmt = $con->prepare('SELECT * FROM grades WHERE semester = :semester AND groupid = :groupid');
                                            $selectStmt->bindValue(':groupid', $groupid, PDO::PARAM_INT);
                                            $selectStmt->bindValue(':semester', $_GET['semester'], PDO::PARAM_INT);
                                            $selectStmt->execute();
                                        ?>
                                    <?php else: ?>
                                        <?php
                                            $selectStmt = $con->prepare('SELECT * FROM grades WHERE semester = 2 AND groupid = :groupid');
                                            $selectStmt->bindValue(':groupid', $groupid, PDO::PARAM_INT);
                                            $selectStmt->execute();
                                        ?>
                                    <?php endif; ?>

                                    <table class="table table-hover">
                                        <thead>
                                            <tr class="table-light text-center">
                                                <th scope="col" class="col-md-7">Grading Period</th>
                                                <th scope="col" class="col-md-2">Current Grade</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while($row = $selectStmt->fetch()): ?>
                                            <tr class="table-light text-center">
                                                <td>Pre-Lim</td>
                                                <td><?php echo $row['prelims']; ?>%</td>
                                                <td>
                                                    <input type="text" id="prelims" name="prelims" class="form-control" placeholder="Enter Grade">
                                                </td>
                                            </tr>                                            
                                            <tr class="table-light text-center">
                                                <td>Midterms</td>
                                                <td><?php echo $row['midterms']; ?>%</td>
                                                <td>
                                                    <input type="text" id="midterms" name="midterms" class="form-control" placeholder="Enter Grade">
                                                </td>
                                            </tr>                                            
                                            <tr class="table-light text-center">
                                                <td>Semi-Finals</td>
                                                <td><?php echo $row['semis']; ?>%</td>
                                                <td>
                                                    <input type="text" id="semis" name="semis" class="form-control" placeholder="Enter Grade">
                                                </td>
                                            </tr>                                            
                                            <tr class="table-light text-center">
                                                <td>Finals</td>
                                                <td><?php echo $row['finals']; ?>%</td>
                                                <td>
                                                    <input type="text" id="finals" name="finals" class="form-control" placeholder="Enter Grade">
                                                </td>
                                            </tr>

                                            <tr class="table-light text-center">
                                                <td class="fw-bold">Overall</td>
                                                <td colspan="2">
                                                <?php 
                                                    $avg = ($row['prelims'] + $row['midterms'] + $row['semis'] + $row['finals']) / 4;

                                                    echo $avg; 
                                                
                                                ?>%
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>

                                <div class="text-center">
                                    <button type="submit" name="submitbtn" class="btn btn-lg text-white" style="background-color: #A020F0;">Submit</button>
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
        $('#redirect_select').on('change', function(){
            window.location = $(this).val();
        });
    </script>
</html>