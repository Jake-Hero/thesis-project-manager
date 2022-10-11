<?php

require '../includes/functions.php';

if(!empty($_GET['group_title']))
{    
    $query = "SELECT * FROM groups WHERE group_title = :title";
    $select_stm = $con->prepare($query);
    $select_stm->execute(['title' => $_GET['group_title']]);

    if($select_stm->rowCount() > 0)
    {
        $row = $select_stm->fetch(PDO::FETCH_ASSOC);

        echo 'title_taken';
        die;
    }
}

$query = "SELECT id, group_id FROM users WHERE id = :user_id OR fullname = :full_name OR username = :user_name";
$select_stm = $con->prepare($query);
$select_stm->bindValue('user_id', (int) $_GET['group_leader'], PDO::PARAM_INT);
$select_stm->bindValue('full_name', $_GET['group_leader']);
$select_stm->bindValue('user_name', $_GET['group_leader']);
$select_stm->execute();

if($select_stm->rowCount() > 0)
{
    $row = $select_stm->fetch(PDO::FETCH_ASSOC);   

    if($row['group_id'] > 0)
    {
        echo 'leader_taken';
        die;
    }
    else 
    {
        $code = bin2hex(random_bytes(6));

        $query = "INSERT INTO groups (creation, group_leader, group_title, group_code) VALUES(:creation, :leader, :title, :code)";
        $insert_stm = $con->prepare($query);
        $insert_stm->execute(['creation' => date("Y-m-d H:i:s"), 'leader' => $row['id'], 'title' => $_GET['group_title'], 'code' => $code]);
        $groupid = $con->lastInsertId();

        $query = "UPDATE users SET group_id = :groupid WHERE id = :leader";
        $updateStmt = $con->prepare($query);
        $updateStmt->execute(['groupid' => $groupid, 'leader' => $row['id']]);
    
        log_group($groupid, $_SESSION['user']['fullname'] . " created the thesis group.");
        log_group($groupid, $_SESSION['user']['fullname'] . " has assigned " . getFullName($row['id']) . " as the group leader.");

        header("Location: " . ROOT_FOLDER . "/admin/edit_group.php?id=" .$con->lastInsertId());
    }
}
else 
{
    echo 'leader_invalid';
    die;
}

echo 'success';
exit();

?>