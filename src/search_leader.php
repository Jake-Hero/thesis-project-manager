<?php
require '../libs/functions.php';

if (isset($_POST['query'])) {
    $inpText = $_POST['query'];
    $query = 'SELECT fullname FROM users WHERE fullname LIKE :username';
    $stmt = $con->prepare($query);
    $stmt->execute(['username' => '%' . $inpText . '%']);
    $result = $stmt->fetchAll();

    if ($result) {
        foreach ($result as $row) {
            echo '<a href="#" class="list-group-item list-group-item-action border-1" onClick="selectName(\'' . $row['fullname'] . '\');">' . $row['fullname'] . '</a>';
        }
    } else {
        echo '<p class="list-group-item border-1">No Record</p>';
    }
}

?>