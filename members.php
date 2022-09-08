<?php
    require "functions.php";
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: dashboard.php");
        die;
    }

    if(!is_user_verified())
    {
        header("Location: verify.php");
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
        <div class="mt-5 container-fluid">  
            <div class="d-flex justify-content-center">
                <div class="table-responsive">
                    <table class="table table-hover w-auto">
                        <thead>
                            <tr class="table-dark">
                                <th scope="col"></th>
                                <th scope="col">Full Name <i class="fa fa-sort"></i></th>
                                <th scope="col">Username <i class="fa fa-sort"></i></th>
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
                                    <img src="profile_pictures/<?php echo $row['image']; ?>" class="rounded-circle btn-lg" height="60" alt="Avatar" />
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
                                    <a href="#" class="edit" title="Edit" data-toggle="tooltip"><i class="fa fa-pencil-square "></i></a>
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
    </body>

    <script>
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</html>