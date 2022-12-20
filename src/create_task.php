<?php

require '../libs/functions.php';

$query = "SELECT id, email_verified FROM users WHERE id = :id";
$selectStmt = $con->prepare($query);
$selectStmt->execute(['id' => $_POST['assigned_to']]);

if(empty($_POST['assigned_to']) || empty($_POST['task_title']) || empty($_POST['task_detail']) || empty($_POST['task_start']) || empty($_POST['task_end'])) 
{
    echo "empty_field";
    die;
}

if($_POST['task_end'] <= $_POST['task_start']) {
    echo "time_conflict";
    die;
}

$query = "INSERT INTO tasks (taskgroup, tasktitle, taskdetail, taskstart, taskdue, taskadmin, taskassignedto) VALUES(:id, :title, :detail, :start, :due, :adminid, :assignedto)";
$insert_stm = $con->prepare($query);
$insert_stm->execute(['id' => $_POST['id'], 'title' => $_POST['task_title'], 'detail' => $_POST['task_detail'], 'start' => $_POST['task_start'], 'due' => $_POST['task_end'], 'adminid' => $_POST['adminid'], 'assignedto' => $_POST['assigned_to']]);
log_group($_POST['id'], $_SESSION['user']['fullname'] . " has assigned " . $_POST['task_title'] . " to " . getFullName($_POST['assigned_to']));

echo "success";
exit();

?>