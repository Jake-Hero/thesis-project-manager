<?php

require '../libs/functions.php';

$code = bin2hex(random_bytes(6));
$query = "UPDATE groups SET group_code = :code WHERE groupid = :id";
$updateStmt = $con->prepare($query);
$updateStmt->execute(['code' => $code, 'id' => $_POST['groupid']]);

echo 'success';
exit();

?>