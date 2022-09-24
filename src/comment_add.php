<?php

require '../includes/functions.php';

$date = date("Y-m-d H:i:s");

$query = "INSERT INTO comments (parent_id, comment, posted_by, posted_date) VALUES (:id, :comment, :author, :date)";
$result = $con->prepare($query);
$result->bindValue('id', $_GET['comment_id'], PDO::PARAM_INT);
$result->bindParam('comment', $_GET['comment']);
$result->bindParam('author', $_GET['author']);
$result->bindParam('date', $date);
echo($result->execute());

?>