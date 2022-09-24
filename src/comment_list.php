<?php

require '../includes/functions.php';

$query = "SELECT * FROM comments ORDER BY parent_id DESC, id DESC, posted_date DESC";
$result = $con->prepare($query);
$result->execute();

if($result->rowCount() > 0)
{
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $resultset[] = $row;
    }    
}

echo json_encode($resultset);

?>