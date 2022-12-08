<?php 
require realpath(dirname(__FILE__) . '/../libs/functions.php');

$id = $_POST['comment_id'];

$query = "SELECT * FROM comments WHERE id = :id";
$selectStmt = $con->prepare($query);
$selectStmt->bindValue('id', $id);
$selectStmt->execute();

if($selectStmt->rowCount() > 0)
{
    $query = "DELETE FROM comments WHERE id = :id";
    $deleteStmt = $con->prepare($query);
    $deleteStmt->bindValue('id', $id);
    $deleteStmt->execute();

    $query = "DELETE FROM comments WHERE parent_id = :id";
    $deleteStmt = $con->prepare($query);
    $deleteStmt->bindValue('id', $id);
    $deleteStmt->execute();

    echo "success";
    exit();
}

exit();
?>