<?php

require '../includes/functions.php';

$query = "SELECT group_code FROM groups WHERE groupid = :id";
$result = $con->prepare($query);
$result->execute(['id' => $_POST['groupid']]);
$output = '';

$statement = $result->fetch();

if($result->rowCount() > 0) 
{
    $output.= $statement['group_code'];
}

echo $output;
exit();

?>