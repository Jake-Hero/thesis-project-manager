<?php
    require '../includes/functions.php';

    foreach($_FILES['file']['name'] as $keys => $values)
    {
        $file_name =  $_FILES['file']['name'][$keys];
        $tmp_name = $_FILES['file']['tmp_name'][$keys];

        $file_name = "group_" . $_SESSION['user']['group_id'] . "_" . $file_name;
        $file_up_name = "../uploads/" . $file_name;

        $query = "INSERT INTO uploads(group_id, file_date, file_name, file_uploaded_by) VALUES(:id, :date, :name, :uploaded_by)";
        $insertStmt = $con->prepare($query);
        $insertStmt->execute(['id' => $_SESSION['user']['group_id'], 'date' => date("F j, Y h:i"), 'name' => $file_name, 'uploaded_by' => $_SESSION['user']['id']]);

        move_uploaded_file($tmp_name, $file_up_name);
    }
    die;
?>