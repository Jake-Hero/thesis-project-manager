<?php

require '../libs/functions.php';

$query = "SELECT * FROM tasks WHERE taskid = :id LIMIT 1;";
$result = $con->prepare($query);
$result->execute(['id' => $_POST['taskid']]);
$output = '';

$statement = $result->fetch();

if($result->rowCount() > 0) 
{
    switch($statement['taskstatus']) {
        case 0: 
            $output.='<span class="mt-2 badge bg-danger tg-white">Incomplete</span>';
            break;
        case 1: 
            $output.='<span class="mt-2 badge bg-warning text-white">In-Progress</span>';
            break;
        case 2: 
            $output.='<span class="mt-2 badge bg-success text-white">Complete</span>';
            break;
    }
}

echo $output;
exit();

?>