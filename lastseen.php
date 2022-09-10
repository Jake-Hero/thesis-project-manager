<?php 
require "includes/functions.php";

if(is_user_login(false))
{
    global $con;
    $query = "UPDATE users SET last_seen = CURRENT_TIMESTAMP() WHERE id = :id";
    $updateStmt = $con->prepare($query);
    $updateStmt->execute(['id' => $_SESSION['user']['id']]);
}
?>