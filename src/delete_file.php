<?php 
require realpath(dirname(__FILE__) . '/../includes/functions.php');

$id = $_POST['file_id'];

$query = "SELECT * FROM uploads WHERE file_id = :id";
$selectStmt = $con->prepare($query);
$selectStmt->bindValue('id', $id);
$selectStmt->execute();

if($selectStmt->rowCount() > 0)
{
    $query = "DELETE FROM uploads WHERE file_id = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $id);
    $updateStmt->execute();

    foreach (glob("../uploads/group_" . $_POST['group_id'] . "*.*") as $filename) {
        unlink($filename);
    }

    echo "success";
    exit();
}

exit();
?>