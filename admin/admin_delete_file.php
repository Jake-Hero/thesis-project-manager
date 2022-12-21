<?php 
require '../libs/functions.php';

if(isset($_GET['file']))
{
    global $con;

    $file_name = basename($_GET['file']);
    $filePath  = "../uploads/group_" . $_SESSION['user']['group_id'] . "/" . $file_name;

    $query = "SELECT * FROM uploads WHERE file_name = :name";
    $selectStmt = $con->prepare($query);
    $selectStmt->bindValue('name', $file_name);
    $selectStmt->execute();

    if($selectStmt->rowCount() > 0)
    {
        $query = "DELETE FROM uploads WHERE file_name = :name";
        $updateStmt = $con->prepare($query);
        $updateStmt->bindValue('name', $file_name);
        $updateStmt->execute();

        unlink($filePath);

        header("Location: ./edit_task.php?id=" . $_SESSION['taskid']);
        die;
    }
    else {
        die;
    }
} else {
    header("Location: ../index.php");
    die;
}

exit();
?>