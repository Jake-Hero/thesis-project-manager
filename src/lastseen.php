<?php 
require realpath(dirname(__FILE__) . '/../libs/functions.php');

if(is_user_login(false))
{
    global $con;
    $query = "UPDATE users SET last_seen = CURRENT_TIMESTAMP() WHERE id = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->bindValue('id', $_SESSION['user']['id']);
    $updateStmt->execute();
}

exit();
?>