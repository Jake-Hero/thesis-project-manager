<?php 
require realpath(dirname(__FILE__) . '/../libs/functions.php');

$id = $_POST['taskid'];

$query = "SELECT taskgroup, tasktitle FROM tasks WHERE taskid = :id";
$selectStmt = $con->prepare($query);
$selectStmt->bindValue('id', $id);
$selectStmt->execute();
$row = $selectStmt->fetch(PDO::FETCH_ASSOC);

if($selectStmt->rowCount() > 0)
{
    log_group($row['taskgroup'], $_SESSION['user']['fullname'] . " has removed task " . $row['tasktitle']);

    $query = "DELETE FROM tasks WHERE taskid = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    foreach (glob("../uploads/group_" . $id . "/task_" . $id . "*.*") as $filename) {
        unlink($filename);
    }

    echo "success";
    die;
}

die;
?>