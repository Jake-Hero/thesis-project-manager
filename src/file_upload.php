<?php
    require '../libs/functions.php';

    foreach($_FILES['file']['name'] as $keys => $values)
    {
        $ext = pathinfo($_FILES['file']['name'][$keys], PATHINFO_EXTENSION);
        $tmp_name = $_FILES['file']['tmp_name'][$keys];

        $file_name = "task_" . $_SESSION['taskid'] . "_group_" . $_REQUEST['groupid'] . "_" . uniqid() . "." . $ext;
        $file_up_name = "../uploads/group_" . $_REQUEST['groupid'] . '/' . $file_name;

        $query = "INSERT INTO uploads(group_id, task_id, file_date, file_name, file_uploaded_by) VALUES(:id, :t_id, :date, :name, :uploaded_by)";
        $insertStmt = $con->prepare($query);
        $insertStmt->execute(['id' => $_REQUEST['groupid'], 't_id' => $_SESSION['taskid'], 'date' => date("F j, Y h:i A"), 'name' => $file_name, 'uploaded_by' => $_SESSION['user']['id']]);

        move_uploaded_file($tmp_name, $file_up_name);
    }
    die;
?>