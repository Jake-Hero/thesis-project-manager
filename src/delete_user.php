<?php 
require realpath(dirname(__FILE__) . '/../includes/functions.php');

$id = $_GET['user_id'];

$query = "SELECT id FROM users WHERE id = :id";
$selectStmt = $con->prepare($query);
$selectStmt->bindValue('id', $id);
$selectStmt->execute();

if($selectStmt->rowCount() > 0)
{
    $query = "DELETE FROM users WHERE id = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    echo "success";
    die;
}

die;
?>