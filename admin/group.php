<?php
    require "../includes/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: " . ROOT_FOLDER . "/group.php");
        die;
    }

    if(isset($_GET['page']))
    {
        $page_number = $_GET['page'];
    }
    else
    {
        header("Location: " . ROOT_FOLDER . "/admin/group.php?page=1");
        die;
    }

    if(isset($_POST['createbtn']))
    {
        $errors = create_group($_POST);
    }

    $no_of_records_per_page = 10;
    $offset = ($page_number-1) * $no_of_records_per_page; 
    $prev_page = $page_number - 1;
    $next_page = $page_number + 1;

    $currentPage = 'group';
    require('../includes/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('../head.php')?>
        <link rel="stylesheet" href="<?php echo ROOT_FOLDER . '/css/fade.css'; ?>">
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

            .pagination > li > a
            {
                background-color: white;
                color: #5A4181;
            }

            .pagination > li > a:focus,
            .pagination > li > a:hover,
            .pagination > li > span:focus,
            .pagination > li > span:hover
            {
                color: #5a5a5a;
                background-color: #eee;
                border-color: #ddd;
            }

            .pagination > .active > a
            {
                color: white;
                background-color: #A020F0 !Important;
                border: solid 1px #A020F0 !Important;
            }

            .pagination > .active > a:hover
            {
                background-color: #A020F0 !Important;
                border: solid 1px #A020F0;
            }
        </style>
    </head>

    <body>
        <div class="modal fade" id="modalCreateGroup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">

                    <div class="modal-header text-center">
                        <h4 class="modal-title w-100 font-weight-bold">Create Group</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form id="create_form" method="post" enctype="multipart/form-data">
                        <div class="modal-body mx-3">
                            <p>
                                You are now about to create a new Thesis Group, please fill up the form.
                            </p>

                            <div class="form-group has-error md-form mt-5 mb-4">
                                <label data-error="wrong" data-success="right" for="group_title">Research (Thesis) Title</label>
                                <input type="text" id="group_title" name="group_title" class="form-control validate" placeholder="Title">
                            </div>

                            <div class="form-group has-error md-form mb-4">
                                <label data-error="wrong" data-success="right" for="group_leader">Leader</label>
                                <input type="text" id="group_leader" name="group_leader" class="form-control validate" placeholder="Enter the ID, Full Name or User Name">
                            </div>

                        </div>

                        <div class="modal-footer d-flex justify-content-center">
                            <button type="submit" id="createbtn" class="btn btn-warning">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="grey-wrapper">
            <div class="mt-4 mb-4 container">
                <div class="card">
                    <div class="card-header border-bottom border-5 border-warning">Groups</div>
                    <div class="card-body">
                        <div class="table table-responsive w-100 d-block d-md-table">
                            <div class="table-title border-bottom border-3 mb-3">
                                <div class="mb-4 row d-flex justify-content-between align-items-center">
                                    <div class="col-lg-4">
                                        <div class="form-check mb-0">
                                            <form action="" method="get">
                                                <input type="hidden" name="page" value="1">

                                                <?php if(isset($_GET['search'])): ?>
                                                    <input type="hidden" name="search" value="<?php echo $_GET['search']; ?>">
                                                <?php endif; ?>

                                                <div class="row">
                                                    <div class="col">
                                                        <select name="sort" class="form-select me-2" aria-label="Default select example">
                                                            <option>Sort group</option>
                                                            <option value="a-z" <?php if(isset($_GET['sort']) && $_GET['sort'] == "a-z") echo 'selected' ?>>Sort by Full Name (A-Z)</option>
                                                            <option value="z-a" <?php if(isset($_GET['sort']) && $_GET['sort'] == "z-a") echo 'selected' ?>>Sort by Full Name (Z-A)</option>
                                                            <option value="id_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == "id_desc") echo 'selected' ?>>Sort by Group ID (Highest to Lowest)</option>
                                                            <option value="id_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == "id_asc") echo 'selected' ?>>Sort by Group ID (Lowest to Highest)</option>
                                                        </select>
                                                    </div>
                                                    <div class="col">
                                                        <button class="btn text-white" style="background-color: #A020F0;" type="submit"><i class="fa-solid fa-filter"></i> Sort</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <form action="" method="get">
                                            <input type="hidden" name="page" value="1">

                                            <?php if(isset($_GET['sort'])): ?>
                                                <input type="hidden" name="sort" value="<?php echo $_GET['sort']; ?>">
                                            <?php endif; ?>

                                            <div class="row">
                                                <div class="col">
                                                    <input type="text" name="search" class="col-sm-10 form-control" placeholder="<?php if(isset($_GET['search'])) echo $_GET['search'];?>">
                                                </div>
                                                <div class="col">
                                                    <button class="btn text-white" style="background-color: #A020F0;" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</search>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <div class="row d-flex justify-content-between align-items-center">
                                    <div class="col-lg-4"></div>

                                    <div class="col-lg-2">
                                        <a href="" data-toggle="modal" data-target="#modalCreateGroup"><button class="btn text-light" style="background-color: #A020F0;" type="submit"><i class="fa-sharp fa-solid fa-plus"></i> Create Group</button></a>
                                    </div>
                                </div>
                            </div>

                            <table class="table table-hover">
                                <thead>
                                    <tr class="table-light text-center">
                                        <th scope="col">ID</th>
                                        <th scope="col" class="w-50">Title</th>
                                        <th scope="col">Leader</th>
                                        <th scope="col">Members</th>
                                        <th scope="col">Join Code</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if(isset($_GET['sort']) && isset($_GET['search']))
                                    {
                                        $search = '%' . $_GET['search'] . '%';

                                        $query = "SELECT COUNT(*) FROM groups WHERE CONCAT(group_title, group_code) LIKE :keyword ";
                                        $selectStmt = $con->prepare($query);
                                        $selectStmt->bindValue(':keyword', $search);
                                        $selectStmt->execute();
                                        
                                        $total_rows = $selectStmt->fetchColumn();
                                        $total_pages = ceil($total_rows / $no_of_records_per_page);

                                        switch($_GET['sort'])
                                        {
                                            case "a-z":
                                                $selectStmt = $con->prepare('SELECT * FROM groups WHERE CONCAT(group_title, group_code) LIKE :keyword  ORDER BY group_title ASC LIMIT :offset, :no_of_records');                                    
                                                break;
                                            case "z-a":
                                                $selectStmt = $con->prepare('SELECT * FROM groups WHERE CONCAT(group_title, group_code) LIKE :keyword  ORDER BY group_title DESC LIMIT :offset, :no_of_records');                                    
                                                break;
                                            case "id_desc":
                                                $selectStmt = $con->prepare('SELECT * FROM groups WHERE CONCAT(group_title, group_code) LIKE :keyword  ORDER BY groupid DESC LIMIT :offset, :no_of_records');                                    
                                                break;
                                            case "id_asc":
                                                $selectStmt = $con->prepare('SELECT * FROM groups WHERE CONCAT(group_title, group_code) LIKE :keyword  ORDER BY groupid ASC LIMIT :offset, :no_of_records'); 
                                                break;
                                            default: 
                                                $selectStmt = $con->prepare('SELECT * FROM groups WHERE CONCAT(group_title, group_code) LIKE :keyword  ORDER BY groupid ASC LIMIT :offset, :no_of_records');
                                                break;
                                        }

                                        $selectStmt->bindParam(':keyword', $search);
                                    }
                                    else if(isset($_GET['search']))
                                    {
                                        $search = '%' . $_GET['search'] . '%';

                                        $query = "SELECT COUNT(*) FROM groups WHERE CONCAT(group_title, group_code) LIKE :keyword ";
                                        $selectStmt = $con->prepare($query);
                                        $selectStmt->bindValue(':keyword', $search);
                                        $selectStmt->execute();
                                        
                                        $total_rows = $selectStmt->fetchColumn();
                                        $total_pages = ceil($total_rows / $no_of_records_per_page);
                                    
                                        $selectStmt = $con->prepare('SELECT * FROM groups WHERE CONCAT(group_title, group_code) LIKE :keyword  LIMIT :offset, :no_of_records');
                                        $selectStmt->bindParam(':keyword', $search);
                                    }
                                    else if(isset($_GET['sort']))
                                    {
                                        $query = "SELECT COUNT(*) FROM groups";
                                        $selectStmt = $con->prepare($query);
                                        $selectStmt->execute();
                                        
                                        $total_rows = $selectStmt->fetchColumn();
                                        $total_pages = ceil($total_rows / $no_of_records_per_page);

                                        switch($_GET['sort'])
                                        {
                                            case "a-z":
                                                $selectStmt = $con->prepare('SELECT * FROM groups ORDER BY group_title ASC LIMIT :offset, :no_of_records');                                    
                                                break;
                                            case "z-a":
                                                $selectStmt = $con->prepare('SELECT * FROM groups ORDER BY group_title DESC LIMIT :offset, :no_of_records');                                    
                                                break;
                                            case "id_desc":
                                                $selectStmt = $con->prepare('SELECT * FROM groups ORDER BY groupid DESC LIMIT :offset, :no_of_records');                                    
                                                break;
                                            case "id_asc":
                                                $selectStmt = $con->prepare('SELECT * FROM groups ORDER BY groupid ASC LIMIT :offset, :no_of_records'); 
                                                break;
                                            default: 
                                                $selectStmt = $con->prepare('SELECT * FROM groups ORDER BY groupid ASC LIMIT :offset, :no_of_records');
                                                break;
                                        }
                                    }
                                    else 
                                    {
                                        $query = "SELECT COUNT(*) FROM groups";
                                        $selectStmt = $con->prepare($query);
                                        $selectStmt->execute();
                                        
                                        $total_rows = $selectStmt->fetchColumn();
                                        $total_pages = ceil($total_rows / $no_of_records_per_page);      

                                        $selectStmt = $con->prepare('SELECT * FROM groups ORDER BY groupid ASC LIMIT :offset, :no_of_records');
                                    }

                                    $selectStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                                    $selectStmt->bindValue(':no_of_records', $no_of_records_per_page, PDO::PARAM_INT);
                                    $selectStmt->execute();

                                    if($selectStmt->rowCount() > 0):
                                    while($row = $selectStmt->fetch()): 
                                    ?>
                                        <tr class="table-light text-center">
                                            <td><?php echo $row['groupid']; ?></td>
                                            <td><?php echo '<strong>' .$row['group_title']. '</strong>'; ?></td>
                                            <td><?php echo ($row['group_leader'] >= 1) ? getFullName($row['group_leader']) : "No Leader"; ?></td>
                                            <td>
                                                <?php 
                                                    $countStmt = $con->prepare('SELECT COUNT(*) FROM users WHERE group_id = :groupid');
                                                    $countStmt->execute(['groupid' => $row['groupid']]);
                                                    $count = $countStmt->fetchColumn();

                                                    echo $count;
                                                ?>
                                            </td>

                                            <td><?php echo $row['group_code']; ?></td>

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

                            <div class="container-fluid">
                                <div class="mb-3 text-center text-black-50">
                                    Page <?php echo $page_number." of ".$total_pages; ?>
                                </div>

                                <div class="mb-3 text-center text-black-50">
                                    Queries found: <?php echo $total_rows; ?>
                                </div>

                                <div class="text-center">
                                    <nav aria-label="Page navigation example mt-5">
                                        <ul class="pagination justify-content-center">
                                            <li class="page-item <?php if($page_number <= 1) echo 'disabled'; ?>">
                                                <a class="page-link"
                                                    href="<?php 
                                                            echo ROOT_FOLDER. '/admin/group.php';

                                                            if($page_number <= 1)
                                                            { 
                                                                echo '?page=1'; 
                                                                
                                                                if(isset($_GET['sort']))
                                                                    echo '&sort=' .$_GET['sort'];

                                                                if(isset($_GET['search']))
                                                                    echo '&search=' .$_GET['search'];
                                                            } 
                                                            else 
                                                            { 
                                                                echo '?page=' . $prev_page; 
                                                                
                                                                if(isset($_GET['sort']))
                                                                    echo '&sort=' .$_GET['sort'];

                                                                if(isset($_GET['search']))
                                                                    echo '&search=' .$_GET['search'];
                                                            } 
                                                        ?>">Previous
                                                </a>
                                            </li>
                                            <?php for($i = 1; $i <= $total_pages; $i++ ): ?>
                                            <li class="page-item <?php if($page_number == $i) echo 'active'; ?>">
                                                <a class="page-link" href="
                                                <?php 
                                                    echo ROOT_FOLDER. '/admin/group.php?page=' .$i;
                                                    
                                                    if(isset($_GET['sort']))
                                                        echo '&sort=' .$_GET['sort'];

                                                    if(isset($_GET['search']))
                                                        echo '&search=' .$_GET['search'];
                                                
                                                ?>"> <?= $i; ?> </a>
                                            </li>
                                            <?php endfor; ?>
                                            <li class="page-item <?php if($page_number >= $total_pages) echo 'disabled'; ?>">
                                                <a class="page-link"
                                                    href="
                                                    <?php 
                                                        echo ROOT_FOLDER. '/admin/group.php'; 
                                                        if($page_number >= $total_pages)
                                                        {
                                                            echo '?page=1'; 

                                                            if(isset($_GET['sort']))
                                                                echo '&sort=' .$_GET['sort'];

                                                            if(isset($_GET['search']))
                                                                echo '&search=' .$_GET['search'];
                                                        } 
                                                        else 
                                                        {
                                                        
                                                            echo "?page=". $next_page; 

                                                            if(isset($_GET['sort']))
                                                                echo '&sort=' .$_GET['sort'];

                                                            if(isset($_GET['search']))
                                                                echo '&search=' .$_GET['search'];
                                                        } 
                                                    ?>">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script>
        $("#createbtn").click(function (e) {
            e.preventDefault();

            var str = $("#create_form").serialize();
            $.ajax({
                url: "../src/create_group.php",
                data: str,
                type: 'GET',
                success: function (response)
                {
                    if(!$('#group_leader').val() || !$('#group_title').val()) {
                        Swal.fire(
                                    'Error',
                                    'Please fill out all the fields!',
                                    'error'
                                )
                    }
                    else if(response=="leader_taken") {
                        Swal.fire(
                            'Error',
                            'This user is already in another research group.',
                            'error'
                        )
                    }
                    else if(response=="leader_invalid") {
                        Swal.fire(
                            'Error',
                            'The specified user is not valid in the system.',
                            'error'
                        )
                    }
                    else {
                        location.reload();
                    }
                }
                
            });
        });

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