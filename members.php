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
                                        <form method="GET">
                                            <div class="row">
                                                <div class="col">
                                                    <select name="sort" class="form-select me-2" aria-label="Default select example">
                                                        <option>Sort users</option>
                                                        <option value="a-z" <?php if(isset($_GET['sort']) && $_GET['sort'] == "a-z") echo 'selected' ?>>Sort by Full Name (A-Z)</option>
                                                        <option value="z-a" <?php if(isset($_GET['sort']) && $_GET['sort'] == "z-a") echo 'selected' ?>>Sort by Full Name (Z-A)</option>
                                                        <option value="id_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == "id_desc") echo 'selected' ?>>Sort by User ID (Highest to Lowest)</option>
                                                        <option value="id_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == "id_asc") echo 'selected' ?>>Sort by User ID (Lowest to Highest)</option>
                                                        <option value="date_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == "date_desc") echo 'selected' ?>>Sort by Date Registration (Newest to Oldest)</option>
                                                        <option value="date_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == "date_asc") echo 'selected' ?>>Sort by Date Registration (Oldest to Newest)</option>
                                                    </select>
                                                </div>
                                                <div class="col">
                                                    <button class="btn btn-secondary" type="submit">Sort</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="col-lg-3">
                                    <form method="get">
                                        <div class="row">
                                            <div class="col">
                                                <input type="text" name="search" class="col-sm-10 form-control" placeholder="<?php if(isset($_GET['search'])) echo $_GET['search'];?>">
                                            </div>
                                            <div class="col">
                                                <button class="btn btn-secondary" type="submit">Search</search>
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
                                if(isset($_GET['search']))
                                {
                                    $search = '%' . $_GET['search'] . '%';
                                    $selectStmt = $con->prepare("SELECT * FROM users WHERE CONCAT(fullname, username, email) LIKE :keyword");
                                    $selectStmt->bindParam(':keyword', $search);
                                }
                                else if(isset($_GET['sort']))
                                {
                                    switch($_GET['sort'])
                                    {
                                        case "a-z":
                                            $selectStmt = $con->prepare('SELECT * FROM users ORDER BY fullname ASC');                                    
                                            break;
                                        case "z-a":
                                            $selectStmt = $con->prepare('SELECT * FROM users ORDER BY fullname DESC');                                    
                                            break;
                                        case "id_desc":
                                            $selectStmt = $con->prepare('SELECT * FROM users ORDER BY id DESC');                                    
                                            break;
                                        case "id_asc":
                                            $selectStmt = $con->prepare('SELECT * FROM users ORDER BY id ASC'); 
                                            break;
                                        case "date_desc":
                                            $selectStmt = $con->prepare('SELECT * FROM users ORDER BY date DESC');                                    
                                            break;
                                        case "date_asc":
                                            $selectStmt = $con->prepare('SELECT * FROM users ORDER BY date ASC'); 
                                            break;
                                        default: 
                                            $selectStmt = $con->prepare('SELECT * FROM users ORDER BY id ASC');
                                            break;
                                    }
                                }
                                else 
                                    $selectStmt = $con->prepare('SELECT * FROM users ORDER BY id ASC');
                                
                                $selectStmt->execute();
                                if($selectStmt->rowCount() > 0):
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

                                <?php endwhile; ?>
                                <?php else: ?>
                                    <td colspan="7" class="text-center">No user found.</td>
                                <?php endif; ?>
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