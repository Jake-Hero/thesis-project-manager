<?php
    require "../includes/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: " . ROOT_FOLDER . "/dashboard.php");
        die;
    }

    $currentPage = 'group';
    require('../includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('../head.php')?>
        <title>Thesis & Capstone Manager - Group</title>

        <style>
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
        <div class="container-fluid">
            <div class="container mt-4 mb-5" style="background-color: #fde151; padding-top: 10px; padding-bottom: 10px;">
                
                <div class="container-fluid" style="background-color: #fcd101; padding-top: 10px; padding-bottom: 10px;">
                
                    <div class="container-fluid" style="background-color: #f7e795; padding-top: 20px; padding-bottom: 10px;">

                        <table class="table table-hover table-responsive w-100 d-block d-md-table">
                            <div class="table-title mb-3">
                                <div class="mb-4 row d-flex justify-content-between align-items-center">
                                    <div class="col-lg-4">
                                        
                                    </div>

                                    <div class="col-lg-2">
                                        <a href="<?php echo ROOT_FOLDER;?>/admin/create_group.php"><button class="btn text-light" style="background-color: #A020F0;" type="submit"><i class="fa-sharp fa-solid fa-plus"></i> Create Group</button></a>
                                    </div>
                                </div>
                            </div>

                            <thead>
                                <tr class="table-light text-center">
                                    <th scope="col">ID</th>
                                    <th scope="col" class="w-50">Title</th>
                                    <th scope="col">Leader</th>
                                    <th scope="col">Members</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $selectStmt = $con->prepare('SELECT * FROM groups ORDER BY groupid ASC');
                                $selectStmt->execute();
                                if($selectStmt->rowCount() > 0):
                                while($row = $selectStmt->fetch()): 
                                ?>
                                    <tr class="table-light text-center">
                                        <td><?php echo $row['groupid']; ?></td>
                                        <td><?php echo '<strong>' .$row['group_title']. '</strong>'; ?></td>
                                        <td><?php echo getFullName($row['group_leader']); ?></td>
                                        <td>
                                            <?php 
                                                $countStmt = $con->prepare('SELECT COUNT(*) FROM users WHERE group_id = :groupid');
                                                $countStmt->execute(['groupid' => $row['groupid']]);
                                                $count = $countStmt->fetchColumn();

                                                echo $count;
                                            ?>
                                        </td>

                                        <td class="text-center">
                                            <a href="<?php echo ROOT_FOLDER; ?>/admin/edit_group.php?id=<?php echo $row['groupid']; ?>" class="edit" title="Edit" data-toggle="tooltip"><i class="fa fa-pencil-square "></i></a>                            
                                            <a href="#" class="delete" onclick="showAlertGroupDelete(<?php echo $row['groupid']; ?>);" title="Delete" data-toggle="tooltip"><i class="fa-sharp fa-solid fa-trash "></i></a>     
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                <?php else: ?>
                                    <tr class="table-light">
                                        <td colspan="5" class="text-center">No groups were found in the database, Create one first!</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>

                </div>

            </div>
        </div>
    </body>

    <script>
        function showAlertGroupDelete(id) {
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
                        type: 'GET', 
                        url: '../src/delete_group.php',
                        data: {'group_id' : id},
                        success: function(response) {
                            if(response=="success") {
                                Swal.fire(
                                    'Deleted',
                                    'You have deleted the group.',
                                    'success'
                                ).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error',
                                    'Something went wrong on deleting the group.',
                                    'error'
                                )
                            }
                        }
                    });
                }
            });
        }
    </script>
</html>