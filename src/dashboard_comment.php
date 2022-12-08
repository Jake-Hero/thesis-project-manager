<?php

require '../includes/functions.php';

$query = "SELECT comments.*, users.image FROM comments INNER JOIN users ON comments.posted_by = users.fullname WHERE comments.group_id = :id ORDER BY id DESC LIMIT 10;";
$result = $con->prepare($query);
$result->execute(['id' => $_POST['groupid']]);
$output = '';

$statement = $result->fetchAll();

if($result->rowCount() > 0) 
{
    foreach($statement as $row)
    {
        $output .= "
                    <div class='jumbotron border-bottom border-3 mb-3'>
                        <div class='container bg-light' style='background-image: var(--bs-gradient);'>
                            <img id='commentPic' src='" . ROOT_FOLDER . '/assets/profile_pictures/' .$row['image'] ."' id='preview' class='rounded-circle mt-3 mx-2' style='width: 40px; height: 40px;' alt='Avatar' />
                            <b>".$row['posted_by']."</b>
                            
                            <div class='mt-3'>
                                " .$row['comment']. "
                            </div>

                            <div class='text-end mt-3 mx-2'>
                                <i>posted on ".$row['posted_date']."</i>
                            </div>
                        </div>
                    </div>
                    ";
    }    
} 
else 
{
    $output .= "
    <div class='jumbotron border-bottom border-3 mb-3'>
        <div class='container bg-light' style='background-image: var(--bs-gradient);'>
            No comments yet.
        </div>
    </div>
    ";    
}

echo $output;

?>