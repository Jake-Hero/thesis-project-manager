<?php

require '../libs/functions.php';

$query = "SELECT fullname FROM users WHERE id = :id";
$result = $con->prepare($query);
$result->execute(['id' => $_POST['userid']]);
$output = '';

$statement = $result->fetchAll();

if($result->rowCount() > 0) 
{
    foreach($statement as $row)
    {
        $output.=$row['fullname'];
    }    
}

echo $output;
?>