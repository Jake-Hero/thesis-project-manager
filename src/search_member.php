<?php
require '../libs/functions.php';

if (isset($_POST['query'])) {
    $inpText = $_POST['query'];
    $query = 'SELECT fullname FROM users WHERE group_id = :id AND fullname LIKE :username';
    $stmt = $con->prepare($query);
    $stmt->execute(['id' => $_POST['id'], 'username' => '%' . $inpText . '%']);
    $result = $stmt->fetchAll();

    if ($result) {
        foreach ($result as $row) {
            echo '<a href="#" class="list-group-item list-group-item-action border-1" onClick="selectRemoveMember(\'' . $row['fullname'] . '\');">' . $row['fullname'] . '</a>';
        }
    } else {
        echo '<p class="list-group-item border-1">No Record</p>';
    }
}
?>