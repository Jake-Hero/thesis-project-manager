<?php

require '../libs/functions.php';

$query = "UPDATE tasks SET taskstatus = :status WHERE taskid = :id";
$updateStmt = $con->prepare($query);
$updateStmt->execute(['status' => $_POST['taskstatus'], 'id' => $_POST['taskid']]);

echo 'success';
exit();

?>