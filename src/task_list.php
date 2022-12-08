<?php
require '../libs/functions.php';

$event_array = array();

if($_SESSION['user']['group_id'] >= 1) {
    $groupid = $_SESSION['user']['group_id'];

    $query = "SELECT * FROM tasks WHERE taskgroup = :id";
    $selectStmt = $con->prepare($query);
    $selectStmt->execute(['id' => $groupid]);
}

$result = $selectStmt->fetchAll();

foreach($result as $row)
{
    $event_array[] = array(
        'id'   => $row["taskid"],
        'title'   => $row["tasktitle"],
        'start'   => $row["taskstart"],
        'end'   => $row["taskdue"]
    );
}

echo json_encode($event_array);

?>