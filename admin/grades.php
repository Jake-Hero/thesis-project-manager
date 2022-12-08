<?php
    require "../libs/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_ADVISOR)
    {
        header("Location: " . ROOT_FOLDER . "/grades.php");
        die;
    }

    if(isset($_GET['page']))
    {
        $page_number = $_GET['page'];
    }
    else
    {
        header("Location: " . ROOT_FOLDER . "/admin/grades.php?page=1");
        die;
    }

    $no_of_records_per_page = 10;
    $offset = ($page_number-1) * $no_of_records_per_page; 
    $prev_page = $page_number - 1;
    $next_page = $page_number + 1;

    $currentPage = 'grades';
    require('../libs/header.php');
?>

<!DOCTYPE html>
<html>
    <head>
        <?php require('../head.php')?>
        <link rel="stylesheet" href="<?php echo ROOT_FOLDER . '/css/fade.css'; ?>">
        <title>Thesis & Capstone Manager - Group</title>

        <style>
            .list-group-item.active {
                color: black;
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
        <div class="grey-wrapper">
            <div class="mt-4 mb-4 container-fluid">
                <div class="row">
                    <div class="col-md-2">
                        <ul class="list-group">
                            <a href="<?php echo ROOT_FOLDER . "/admin/index.php"; ?>"><li class="list-group-item bg-warning <?php if($currentPage =="admin") echo 'active'; ?>">Admin Dashboard</li></a>
                            <a href="<?php echo ROOT_FOLDER . "/admin/members.php"; ?>"><li class="list-group-item bg-warning <?php if($currentPage =="members") echo 'active'; ?>">Members List</li></a>
                            <a href="<?php echo ROOT_FOLDER . "/admin/group.php"; ?>"><li class="list-group-item bg-warning <?php if($currentPage =="group") echo 'active'; ?>">Group List</li></a>
                            <a href="<?php echo ROOT_FOLDER . "/admin/grades.php"; ?>"><li class="list-group-item bg-warning <?php if($currentPage =="grades") echo 'active'; ?>">Grading</li></a>
                        </ul>
                    </div>

                    <div class="col">
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
                                                                    <option>Sort</option>
                                                                    <option value="first" <?php if(isset($_GET['sort']) && $_GET['sort'] == "a-z") echo 'selected' ?>>Sort by Group Name (A-Z)</option>
                                                                    <option value="second" <?php if(isset($_GET['sort']) && $_GET['sort'] == "z-a") echo 'selected' ?>>Sort by Group Name (Z-A)</option>
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
                                    </div>

                                    <table class="table table-hover">
                                        <thead>
                                            <tr class="table-light text-center">
                                                <th scope="col">ID</th>
                                                <th scope="col" class="w-50">Title</th>
                                                <th scope="col">First Grading</th>
                                                <th scope="col">Second Grading</th>
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
                                                        $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid WHERE CONCAT(groups.group_title, groups.group_code) LIKE :keyword  ORDER BY groups.group_title ASC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "z-a":
                                                        $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid WHERE CONCAT(groups.group_title, groups.group_code) LIKE :keyword  ORDER BY groups.group_title DESC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "id_desc":
                                                        $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid WHERE CONCAT(groups.group_title, groups.group_code) LIKE :keyword  ORDER BY groups.groupid DESC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "id_asc":
                                                        $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid WHERE CONCAT(groups.group_title, groups.group_code) LIKE :keyword  ORDER BY groups.groupid ASC LIMIT :offset, :no_of_records'); 
                                                        break;
                                                    default: 
                                                        $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid WHERE CONCAT(groups.group_title, groups.group_code) LIKE :keyword  ORDER BY groups.groupid ASC LIMIT :offset, :no_of_records');
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
                                            
                                                $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid WHERE CONCAT(groups.group_title, groups.group_code) LIKE :keyword  LIMIT :offset, :no_of_records');
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
                                                        $selectStmt = $con->prepare('SELECTgroups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid ORDER BY groups.group_title ASC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "z-a":
                                                        $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid ORDER BY groups.group_title DESC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "id_desc":
                                                        $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid ORDER BY groups.groupid DESC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "id_asc":
                                                        $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid ORDER BY groups.groupid ASC LIMIT :offset, :no_of_records'); 
                                                        break;
                                                    default: 
                                                        $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid ORDER BY groups.groupid ASC LIMIT :offset, :no_of_records');
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

                                                $selectStmt = $con->prepare('SELECT groups.*, grades.* FROM groups INNER JOIN grades ON groups.groupid = grades.groupid ORDER BY groups.groupid ASC LIMIT :offset, :no_of_records');
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
                                                    <td>
                                                        <?php
                                                        $avg = ($row["first_prelims"] + $row['first_midterms'] + $row['first_semis'] + $row['first_finals']) / 4;
                                                        echo $avg;
                                                        ?>
                                                        %
                                                    </td>
                                                    <td>
                                                        <?php
                                                        $avg = ($row["second_prelims"] + $row['second_midterms'] + $row['second_semis'] + $row['second_finals']) / 4;
                                                        echo $avg;
                                                        ?>
                                                        %
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="<?php echo ROOT_FOLDER; ?>/admin/edit_grade.php?id=<?php echo $row['groupid']; ?>&semester=1" class="edit" title="Give Grade" data-toggle="tooltip"><span class="badge bg-primary text-white">Give Grade</span></a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr class="table-light">
                                                    <td colspan="6" class="text-center">No groups were found in the database, Create one first!</td>
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
                                                                    echo ROOT_FOLDER. '/admin/grades.php';

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
                                                            echo ROOT_FOLDER. '/admin/grades.php?page=' .$i;
                                                            
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
                                                                echo ROOT_FOLDER. '/admin/grades.php'; 
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
            </div>
        </div>
    </body>
</html>