<?php
    require "../libs/functions.php";
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: ../dashboard.php");
        die;
    }

    $currentPage = 'admin';

    // Visit Detection
    recordUserVisit();

    require('./header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
        <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet" media="nope!" onload="this.media='all'">
        <link rel="stylesheet" href="../css/style.css">
        <link rel="shortcut icon" type="image/jpg" href="https://thesiscapstonemanager.com/favicon.ico"/>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.1/jquery.min.js" integrity="sha512-aVKKRRi/Q/YV+4mjoKBsE4x3H+BkegoM/em46NNlCqNTmUYADjBbeNefNxYV7giUp0VxICtqdrbqU7iVaeZNXA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script type="text/javascript" src="../js/lastseen.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@7.28.11/dist/sweetalert2.all.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" integrity="sha512-xh6O/CkQoPOWDdYTDqeRdPCVd1SpvCA9XXcUnZS2FmJNp1coAFzvtCN9BmamE+4aHK8yyUHUSCcJHgXloTyT2A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <script src="../bootstrap/js/bootstrap.min.js"></script>

        <title>Thesis & Capstone Manager - Admin Panel</title>  
        
        <style>
            .list-group-item.active {
                color: black;
            }
        </style>
    </head>

    <body> 
        <div class="grey-wrapper">
            <div class="mt-4 container-fluid">      
                <div class="row">

                    <div class="col-md-2">
                        <ul class="list-group">
                            <a href="./index.php"><li class="list-group-item bg-warning <?php if($currentPage =="admin") echo 'active'; ?>">Admin Dashboard</li></a>
                            <a href="./members.php"><li class="list-group-item bg-warning <?php if($currentPage =="members") echo 'active'; ?>">Members List</li></a>
                            <a href="./group.php"><li class="list-group-item bg-warning <?php if($currentPage =="group") echo 'active'; ?>">Group List</li></a>
                            <a href="./grades.php"><li class="list-group-item bg-warning <?php if($currentPage =="grades") echo 'active'; ?>">Grading</li></a>
                        </ul>
                    </div>

                    <div class="col-lg-10">
                        <div class="card">
                            <div class="card-header">Administrative Panel - Dashboard</div>
                            <div class="card-body">
                                The date and time is <?php echo date("F j, Y, g:i a"); ?>
                                <div class="row">
                                    <div class="col-xl-3 col-sm-6 col-12 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between px-md-1">
                                                    <div class="align-self-center">
                                                        <i class="fa fa-users fa-3x"></i>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php 
                                                            $query = "SELECT COUNT(*) FROM users";
                                                            $countStm = $con->prepare($query);
                                                            $countStm->execute();
                                                            $count = $countStm->fetchColumn(); 
                                                            echo '<h3>' .$count. '</h3>';
                                                        ?>
                                                        <p class="mb-0">Registered Users</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6 col-12 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between px-md-1">
                                                    <div class="align-self-center">
                                                        <i class="fa-solid fa-user-check text-success fa-3x"></i>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php 
                                                            $query = "SELECT COUNT(*) FROM users WHERE email_verified IS NOT NULL";
                                                            $countStm = $con->prepare($query);
                                                            $countStm->execute();
                                                            $count = $countStm->fetchColumn(); 
                                                            echo '<h3>' .$count. '</h3>';
                                                        ?>
                                                        <p class="mb-0">Verified Users</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6 col-12 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between px-md-1">
                                                    <div class="align-self-center">
                                                        <i class="fa-solid fa-users-slash text-danger fa-3x"></i>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php 
                                                            $query = "SELECT COUNT(*) FROM users WHERE email_verified IS NULL";
                                                            $countStm = $con->prepare($query);
                                                            $countStm->execute();
                                                            $count = $countStm->fetchColumn(); 
                                                            echo '<h3>' .$count. '</h3>';
                                                        ?>
                                                        <p class="mb-0">Non-Verified Users</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6 col-12 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between px-md-1">
                                                    <div class="align-self-center">
                                                        <i class="fa-solid fa-user-secret text-primary fa-3x"></i>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php 
                                                            $query = "SELECT COUNT(*) FROM users WHERE role = " .ROLE_ADMIN;
                                                            $countStm = $con->prepare($query);
                                                            $countStm->execute();
                                                            $count = $countStm->fetchColumn(); 
                                                            echo '<h3>' .$count. '</h3>';
                                                        ?>
                                                        <p class="mb-0">Administrators</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6 col-12 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between px-md-1">
                                                    <div class="align-self-center">
                                                        <i class="fa-solid fa-chalkboard-user text-info fa-3x"></i>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php 
                                                            $query = "SELECT COUNT(*) FROM users WHERE role = " .ROLE_ADVISOR;
                                                            $countStm = $con->prepare($query);
                                                            $countStm->execute();
                                                            $count = $countStm->fetchColumn(); 
                                                            echo '<h3>' .$count. '</h3>';
                                                        ?>
                                                        <p class="mb-0">Advisors</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6 col-12 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between px-md-1">
                                                    <div class="align-self-center">
                                                        <i class="fa-solid fa-user-tag text-warning fa-3x"></i>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php 
                                                            $query = "SELECT COUNT(*) FROM users WHERE role = " .ROLE_PANELIST;
                                                            $countStm = $con->prepare($query);
                                                            $countStm->execute();
                                                            $count = $countStm->fetchColumn(); 
                                                            echo '<h3>' .$count. '</h3>';
                                                        ?>
                                                        <p class="mb-0">Panelists</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xl-3 col-sm-6 col-12 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between px-md-1">
                                                    <div class="align-self-center">
                                                        <i class="fa-solid fa-eye text-secondary fa-3x"></i>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php 
                                                            $query = "SELECT COUNT(*) FROM visitor_data WHERE date >= CURDATE() && date < (CURDATE() + INTERVAL 1 DAY)";
                                                            $countStm = $con->prepare($query);
                                                            $countStm->execute();
                                                            $count = $countStm->fetchColumn(); 
                                                            echo '<h3>' .$count. '</h3>';
                                                        ?>
                                                        <p class="mb-0">Visits for Today</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-sm-6 col-12 mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between px-md-1">
                                                    <div class="align-self-center">
                                                        <i class="fa-solid fa-people-group fa-3x"></i>
                                                    </div>

                                                    <div class="text-end">
                                                        <?php 
                                                            $query = "SELECT COUNT(*) FROM groups";
                                                            $countStm = $con->prepare($query);
                                                            $countStm->execute();
                                                            $count = $countStm->fetchColumn(); 
                                                            echo '<h3>' .$count. '</h3>';
                                                        ?>
                                                        <p class="mb-0">Groups</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <hr />

                                <h3>Statistics</h3>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between px-md-1">
                                            <div class="text-start">
                                                <p class="mb-0">
                                                    <a href="./members.php?page=1">Click me to view all the registered users in this website.</a>
                                                </p>

                                                <p class="mb-0">
                                                The latest registered student is: 
                                                    <?php 
                                                        $query = "SELECT fullname, date FROM users ORDER BY id DESC LIMIT 1;";
                                                        $countStm = $con->prepare($query);
                                                        $countStm->execute();
                                                        $row = $countStm->fetch(); 

                                                        if($countStm->rowCount() > 0) {
                                                            echo '<strong>' .$row['fullname']. '</strong> ';
                                                            echo '(' .$row['date']. ')';
                                                        } else {
                                                            echo '<strong>No one is registered yet</strong>';
                                                        }
                                                    ?>
                                                </p>

                                                <p class="mb-0">
                                                The latest group is: 
                                                    <?php 
                                                        $query = "SELECT creation, group_title FROM groups ORDER BY groupid DESC LIMIT 1;";
                                                        $countStm = $con->prepare($query);
                                                        $countStm->execute();
                                                        $row = $countStm->fetch(); 
                                                        if($countStm->rowCount() > 0) {
                                                            echo '<strong>' .$row['group_title']. '</strong> ';
                                                            echo '(' .$row['creation']. ')';
                                                        } else {
                                                            echo '<strong>No group is created yet</strong>';    
                                                        }
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </body>
</html>