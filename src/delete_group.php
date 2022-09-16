<?php 
require realpath(dirname(__FILE__) . '/../includes/functions.php');

$id = $_GET['group_id'];

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

    $query = "DELETE FROM groups WHERE groupid = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    echo "success";
    exit();
}

exit();
?>