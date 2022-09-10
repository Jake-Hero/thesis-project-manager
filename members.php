<?php
    require "includes/functions.php";
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: dashboard.php");
        die;
    }

    $currentPage = 'members';

    require('header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('head.php')?>
        <title>Thesis & Capstone Manager - Admin Panel</title>      

        <style>
            table.table-striped tbody tr:nth-of-type(odd) {
                background-color: #fcfcfc;
            }
            table.table-striped.table-hover tbody tr:hover {
                background: #f5f5f5;
            }

            table.table th i {
                font-size: 13px;
                margin: 0 5px;
                cursor: pointer;
            }

            table.table tr th, table.table tr td {
                border-color: #e9e9e9;
            }

            table.table td:last-child {
                width: 130px;
            }

            table.table td a.edit {
                color: #2196F3;
            }

            table.table td i {
                font-size: 19px;
            }
        </style>
    </head>

    <body> 
        <div class="mt-3 container">  
            <div class="card container-fluid">
                <div class="card-header">Administrative Panel - Members</div>
                <div class="card-body">
                    <div class="table table-responsive w-100 d-block d-md-table">
                        <div class="table-title mb-3">
                            <div class="row d-flex justify-content-between align-items-center">
                                <div class="col-lg-4">
                                    <div class="form-check mb-0">
                                        <form method="post">
                                            <div class="row">
                                                <div class="col">
                                                    <select class="form-select me-2" aria-label="Default select example">
                                                        <option selected>Sort users</option>
                                                        <option value="1">Sort by Name</option>
                                                        <option value="2">Sort by ID</option>
                                                        <option value="3">Sort by Date Registration</option>
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <input class="btn btn-secondary" type="submit" name="sortbtn" value="Sort">
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <form method="post">
                                        <div class="row">
                                            <div class="col">
                                                <input type="text" name="searchfield" class="col-sm-10 form-control" placeholder="Search User">
                                            </div>
                                            <div class="col">
                                                <input class="btn btn-secondary" type="submit" name="sortbtn" value="Sort">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr class="table-light">
                                    <th scope="col"></th>
                                    <th scope="col">Full Name</th>
                                    <th scope="col">Username</th>
                                    <th scope="col">Email</th>
                                    <th scope="col">Last Seen</th>
                                    <th scope="col">Role</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $selectStmt = $con->prepare('SELECT * FROM users');
                                $selectStmt->execute();
                                while($row = $selectStmt->fetch(PDO::FETCH_ASSOC)): 
                                ?>
                                <tr class="table-light">
                                    <td class="text-center">    
                                        <img src="assets/profile_pictures/<?php echo $row['image']; ?>" class="rounded-circle btn-lg" height="60" alt="Avatar" />
                                    </td>
                                    <td><?php echo $row['fullname']; ?></td>
                                    <td><?php echo $row['username']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <?php $timestamp = strtotime($row['last_seen']); ?>
                                    <td>
                                        <?php echo date("F j, Y, g:i a", $timestamp); ?>
                                    </td>
                                    <td><?php echo getUserRole($row['role']); ?></td>
                                    <td class="text-center">
                                        <a href="edit_users.php?id=<?php echo $row['id']; ?>" class="edit" title="Edit" data-toggle="tooltip"><i class="fa fa-pencil-square "></i></a>
                                        </div>                                
                                    </td>
                                </tr>                        
                                <?php
                                endwhile;
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</html>