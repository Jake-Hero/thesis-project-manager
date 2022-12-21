<?php
    require "../libs/functions.php";
    is_user_valid();
    is_user_login();

    if($_SESSION['user']['role'] < ROLE_PANELIST)
    {
        header("Location: ../dashboard.php");
        die;
    }

    if(isset($_GET['page']))
    {
        $page_number = $_GET['page'];
    }
    else
    {
        header("Location: ./grades.php?page=1");
        die;
    }

    $no_of_records_per_page = 10;
    $offset = ($page_number-1) * $no_of_records_per_page; 
    $prev_page = $page_number - 1;
    $next_page = $page_number + 1;

    $currentPage = 'grades';

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

        <title>Thesis & Capstone Manager - Grading Students</title>      

        <style>
            .list-group-item.active {
                color: black;
            }

            table.table th i {
                font-size: 13px;
                margin: 0 5px;
                cursor: pointer;
            }

            table.table tr th, table.table tr td {
                border-color: #e9e9e9;
                font-size: 90%;
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
                            <?php if($_SESSION['user']['role'] >= ROLE_ADMIN): ?>
                            <a href="./index.php"><li class="list-group-item bg-warning <?php if($currentPage =="admin") echo 'active'; ?>">Admin Dashboard</li></a>
                            <a href="./members.php"><li class="list-group-item bg-warning <?php if($currentPage =="members") echo 'active'; ?>">Members List</li></a>
                            <?php endif; ?>    

                            <a href="./group.php"><li class="list-group-item bg-warning <?php if($currentPage =="group") echo 'active'; ?>">Group List</li></a>
                            <a href="./grades.php"><li class="list-group-item bg-warning <?php if($currentPage =="grades") echo 'active'; ?>">Grading</li></a>
                        </ul>
                    </div>

                    <div class="col">
                        <div class="card">
                            <div class="card-header border-bottom border-5 border-success">Administrative Panel - Members</div>
                            <div class="card-body">
                                <div class="table table-responsive w-100 d-block d-md-table">
                                    <div class="table-title border-bottom border-3 mb-3">
                                        <div class="row d-flex justify-content-between align-items-center">
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
                                                                    <option>Sort users</option>
                                                                    <option value="a-z" <?php if(isset($_GET['sort']) && $_GET['sort'] == "a-z") echo 'selected' ?>>Sort by Full Name (A-Z)</option>
                                                                    <option value="z-a" <?php if(isset($_GET['sort']) && $_GET['sort'] == "z-a") echo 'selected' ?>>Sort by Full Name (Z-A)</option>
                                                                    <option value="id_desc" <?php if(isset($_GET['sort']) && $_GET['sort'] == "id_desc") echo 'selected' ?>>Sort by User ID (Highest to Lowest)</option>
                                                                    <option value="id_asc" <?php if(isset($_GET['sort']) && $_GET['sort'] == "id_asc") echo 'selected' ?>>Sort by User ID (Lowest to Highest)</option>
                                                                </select>
                                                            </div>
                                                            <div class="col">
                                                                <button class="btn btn-warning" type="submit"><i class="fa-solid fa-filter"></i> Sort</button>
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
                                                            <button class="btn btn-warning" type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</search>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table-hover">
                                        <thead>
                                            <tr class="table-light text-center">
                                                <th scope="col">Name</th>
                                                <th scope="col">Group</th>
                                                <th scope="col">Adviser</th>
                                                <th scope="col">Pre-Oral</th>
                                                <th scope="col">Oral Defense</th>
                                                <th scope="col" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if(isset($_GET['sort']) && isset($_GET['search']))
                                            {
                                                $search = '%' . $_GET['search'] . '%';

                                                if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                    $query = "SELECT COUNT(*) FROM users WHERE advised_by = :id AND CONCAT(fullname, username, email) LIKE :keyword";
                                                else
                                                    $query = "SELECT COUNT(*) FROM users WHERE advised_by >= 1 AND CONCAT(fullname, username, email) LIKE :keyword";
                                                
                                                $selectStmt = $con->prepare($query);

                                                if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                    $selectStmt->bindParam(':id', $_SESSION['user']['id']);

                                                $selectStmt->bindValue(':keyword', $search);
                                                $selectStmt->execute();
                                                
                                                $total_rows = $selectStmt->fetchColumn();
                                                $total_pages = ceil($total_rows / $no_of_records_per_page);

                                                switch($_GET['sort'])
                                                {
                                                    case "a-z":
                                                        if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                            $selectStmt = $con->prepare('SELECT u.id, u.email, u.username, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id AND CONCAT(fullname, username, email) LIKE :keyword ORDER BY u.fullname ASC LIMIT :offset, :no_of_records');                                    
                                                        else
                                                            $selectStmt = $con->prepare('SELECT u.id, u.email, u.username, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 AND CONCAT(fullname, username, email) LIKE :keyword ORDER BY u.fullname ASC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "z-a":
                                                        if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                            $selectStmt = $con->prepare('SELECT u.id, u.email, u.username, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id AND CONCAT(fullname, username, email) LIKE :keyword ORDER BY u.fullname DESC LIMIT :offset, :no_of_records');                                    
                                                        else
                                                            $selectStmt = $con->prepare('SELECT u.id, u.email, u.username, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 AND CONCAT(fullname, username, email) LIKE :keyword ORDER BY u.fullname DESC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "id_desc":
                                                        if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                            $selectStmt = $con->prepare('SELECT u.id, u.email, u.username, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id AND CONCAT(fullname, username, email) LIKE :keyword ORDER BY u.id DESC LIMIT :offset, :no_of_records');                                    
                                                        else
                                                            $selectStmt = $con->prepare('SELECT u.id, u.email, u.username, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 AND CONCAT(fullname, username, email) LIKE :keyword ORDER BY u.id DESC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "id_asc":
                                                        if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                            $selectStmt = $con->prepare('SELECT u.id, u.email, u.username, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id AND CONCAT(fullname, username, email) LIKE :keyword ORDER BY u.id ASC LIMIT :offset, :no_of_records'); 
                                                        else
                                                            $selectStmt = $con->prepare('SELECT u.id, u.email, u.username, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 AND CONCAT(fullname, username, email) LIKE :keyword ORDER BY u.id ASC LIMIT :offset, :no_of_records'); 
                                                        break;
                                                    default: 
                                                        if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                            $selectStmt = $con->prepare('SELECT u.id, u.email, u.username, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id AND CONCAT(fullname, username, email) LIKE :keyword ORDER BY u.id ASC LIMIT :offset, :no_of_records');
                                                        else
                                                            $selectStmt = $con->prepare('SELECT u.id, u.email, u.username, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 AND CONCAT(fullname, username, email) LIKE :keyword ORDER BY u.id ASC LIMIT :offset, :no_of_records');
                                                        break;
                                                }

                                                $selectStmt->bindParam(':keyword', $search);
                                            }
                                            else if(isset($_GET['search']))
                                            {
                                                $search = '%' . $_GET['search'] . '%';

                                                if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                    $query = "SELECT COUNT(*) FROM users WHERE advised_by = :id AND CONCAT(fullname, username, email) LIKE :keyword";
                                                else
                                                    $query = "SELECT COUNT(*) FROM users WHERE advised_by >= 1 AND CONCAT(fullname, username, email) LIKE :keyword";
                                                
                                                $selectStmt = $con->prepare($query);

                                                if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                    $selectStmt->bindParam(':id', $_SESSION['user']['id']);

                                                $selectStmt->bindValue(':keyword', $search);
                                                $selectStmt->execute();
                                                
                                                $total_rows = $selectStmt->fetchColumn();
                                                $total_pages = ceil($total_rows / $no_of_records_per_page);

                                                if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                    $selectStmt = $con->prepare("SELECT u.id, u.fullname, u.username, u.email, u.image, u.advised_by, g.group_title, gr.* FROM users AS u JOIN groups AS g ON g.groupid = u.group_id JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id AND CONCAT(fullname, ' ', username, ' ', email) LIKE :keyword LIMIT :offset, :no_of_records");
                                                else
                                                    $selectStmt = $con->prepare("SELECT u.id, u.fullname, u.username, u.email, u.image, u.advised_by, g.group_title, gr.* FROM users AS u JOIN groups AS g ON g.groupid = u.group_id JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 AND CONCAT(fullname, ' ', username, ' ', email) LIKE :keyword LIMIT :offset, :no_of_records");

                                                $selectStmt->bindParam(':keyword', $search, PDO::PARAM_STR);
                                            }
                                            else if(isset($_GET['sort']))
                                            {
                                                if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                    $query = "SELECT COUNT(*) FROM users WHERE advised_by = :id";
                                                else
                                                    $query = "SELECT COUNT(*) FROM users WHERE advised_by >= 1";

                                                $selectStmt = $con->prepare($query);

                                                if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                    $selectStmt->bindParam(':id', $_SESSION['user']['id']);

                                                $selectStmt->execute();
                                                
                                                $total_rows = $selectStmt->fetchColumn();
                                                $total_pages = ceil($total_rows / $no_of_records_per_page);      

                                                switch($_GET['sort'])
                                                {
                                                    case "a-z":
                                                        if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                            $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id ORDER BY u.fullname ASC LIMIT :offset, :no_of_records');  
                                                        else
                                                            $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 ORDER BY u.fullname ASC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "z-a":
                                                        if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                            $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id ORDER BY u.fullname DESC LIMIT :offset, :no_of_records');                                    
                                                        else
                                                            $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 ORDER BY u.fullname DESC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "id_desc":
                                                        if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                            $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id ORDER BY u.id DESC LIMIT :offset, :no_of_records');                                    
                                                        else
                                                            $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 ORDER BY u.id DESC LIMIT :offset, :no_of_records');                                    
                                                        break;
                                                    case "id_asc":
                                                        if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                            $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id ORDER BY u.id ASC LIMIT :offset, :no_of_records'); 
                                                        else
                                                            $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 ORDER BY u.id ASC LIMIT :offset, :no_of_records'); 
                                                        break;
                                                    default: 
                                                        if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                            $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id ORDER BY u.id ASC LIMIT :offset, :no_of_records');
                                                        else
                                                            $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by >= 1 ORDER BY u.id ASC LIMIT :offset, :no_of_records');
                                                        break;
                                                }
                                            }
                                            else 
                                            {
                                                if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                    $query = "SELECT COUNT(*) FROM users WHERE advised_by = :id";
                                                else
                                                    $query = "SELECT COUNT(*) FROM users WHERE advised_by >= 1";

                                                $selectStmt = $con->prepare($query);

                                                if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                    $selectStmt->bindParam(':id', $_SESSION['user']['id']);

                                                $selectStmt->execute();
                                                
                                                $total_rows = $selectStmt->fetchColumn();
                                                $total_pages = ceil($total_rows / $no_of_records_per_page);      

                                                if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                    $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE advised_by = :id ORDER BY u.id ASC LIMIT :offset, :no_of_records');
                                                else
                                                    $selectStmt = $con->prepare('SELECT u.id, u.fullname, u.image, u.advised_by, g.group_title, gr.* FROM users AS u INNER JOIN groups AS g ON g.groupid = u.group_id INNER JOIN grades AS gr ON gr.userid = u.id WHERE u.advised_by >= 1 ORDER BY u.id ASC LIMIT :offset, :no_of_records');
                                            }
                                                
                                            if($_SESSION['user']['role'] == ROLE_ADVISOR)
                                                $selectStmt->bindValue(':id', $_SESSION['user']['id'], PDO::PARAM_INT);

                                            $selectStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
                                            $selectStmt->bindValue(':no_of_records', $no_of_records_per_page, PDO::PARAM_INT);
                                            $selectStmt->execute();

                                            if($selectStmt->rowCount() > 0):
                                            while($row = $selectStmt->fetch(PDO::FETCH_ASSOC)): 
                                            ?>

                                            <tr class="table-light text-center">
                                                <td>    
                                                    <img src="../assets/profile_pictures/<?php echo $row['image']; ?>" class="rounded-circle btn-lg" height="60" alt="Avatar" />
                                                </td>
                                                <td><?php echo $row['fullname']; ?></td>
                                                <td><?php echo $row['group_title']; ?></td>
                                                <td><?php echo getFullName($row['advised_by']); ?></td>
                                                <td>
                                                    <?php
                                                    $total = ($row["pre_rubrics_1"] + $row['pre_rubrics_2'] + $row['pre_rubrics_3'] + $row['pre_rubrics_4'] + $row['pre_rubrics_5'] + $row['pre_rubrics_6']);
                                                    echo getGradeConversion(1, $total);
                                                    ?>
                                                    %
                                                </td>                                                
                                                <td>
                                                    <?php
                                                    $total = ($row["oral_rubrics_1"] + $row['oral_rubrics_2'] + $row['oral_rubrics_3'] + $row['oral_rubrics_4'] + $row['oral_rubrics_5'] + $row['oral_rubrics_6'] + $row['oral_rubrics_7']);
                                                    echo getGradeConversion(2, $total);
                                                    ?>
                                                    %
                                                </td>
                                                <td class="text-center">
                                                    <a href="./edit_grade.php?id=<?php echo $row['id']; ?>" class="edit" title="Give Grade" data-toggle="tooltip"><span class="badge bg-primary text-white">Give Grade</span></a>                             
                                                </td>
                                            </tr>   

                                            <?php endwhile; ?>
                                            <?php else: ?>
                                            <tr class="table-light">
                                                <td colspan="7" class="text-center">No user is assigned to an adviser currently.</td>
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
                                                    <li class="page-item <?php if($page_number <= 1){ echo 'disabled'; } ?>">
                                                        <a class="page-link"
                                                            href="<?php 
                                                                    echo './members.php'; 
                                                                    
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
                                                                        echo "?page=" . $prev_page; 
                                                                        
                                                                        if(isset($_GET['sort']))
                                                                            echo '&sort=' .$_GET['sort'];

                                                                        if(isset($_GET['search']))
                                                                            echo '&search=' .$_GET['search'];
                                                                    } 
                                                                ?>">Previous</a>
                                                    </li>
                                                    <?php for($i = 1; $i <= $total_pages; $i++ ): ?>
                                                    <li class="page-item <?php if($page_number == $i) {echo 'active'; } ?>">
                                                        <a class="page-link" href="
                                                        <?php 
                                                            echo './members.php?page=' .$i;
                                                            
                                                            if(isset($_GET['sort']))
                                                                echo '&sort=' .$_GET['sort'];

                                                            if(isset($_GET['search']))
                                                                echo '&search=' .$_GET['search'];
                                                        
                                                        ?>"> <?= $i; ?> </a>
                                                    </li>
                                                    <?php endfor; ?>
                                                    <li class="page-item <?php if($page_number >= $total_pages) { echo 'disabled'; } ?>">
                                                        <a class="page-link"
                                                            href="
                                                            <?php 
                                                                echo './members.php'; 
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