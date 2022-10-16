<?php

require '../includes/functions.php';

$date = date("Y-m-d H:i:s");

$query = "INSERT INTO comments (group_id, parent_id, comment, posted_by, posted_date) VALUES (:groupid, :id, :comment, :author, :date)";
$result = $con->prepare($query);
$result->bindValue('groupid', $_POST['groupid'], PDO::PARAM_INT);
$result->bindValue('id', $_POST['comment_id'], PDO::PARAM_INT);
$result->bindParam('comment', $_POST['comment']);
$result->bindParam('author', $_POST['author']);
$result->bindParam('date', $date);
echo($result->execute());

?>