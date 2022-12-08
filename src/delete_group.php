<?php 
require realpath(dirname(__FILE__) . '/../libs/functions.php');

$id = $_POST['group_id'];

$query = "SELECT * FROM groups WHERE groupid = :id";
$selectStmt = $con->prepare($query);
$selectStmt->bindValue('id', $id);
$selectStmt->execute();

if($selectStmt->rowCount() > 0)
{
    $query = "UPDATE users SET group_id = 0 WHERE group_id = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    $query = "DELETE FROM grades WHERE groupid = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    $query = "DELETE FROM group_logs WHERE groupid = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    $query = "DELETE FROM comments WHERE group_id = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    $query = "DELETE FROM tasks WHERE taskgroup = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    $query = "DELETE FROM uploads WHERE group_id = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    $query = "DELETE FROM groups WHERE groupid = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    foreach (glob("../uploads/group_" . $id . "/*.*_group_" . $id . "*.*") as $filename) {
        unlink($filename);
    }

    rmdir("../uploads/group_" . $id);

    echo "success";
    exit();
}

exit();
?>