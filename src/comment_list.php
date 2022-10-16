<?php

require '../includes/functions.php';

$user_fullname = $_SESSION['user']['fullname'];
$image = $_SESSION['user']['image'];

$query = "SELECT * FROM comments WHERE group_id = :id AND parent_id = 0 ORDER BY id DESC";
$result = $con->prepare($query);
$result->execute(['id' => $_POST['groupid']]);
$output = '';

$statement = $result->fetchAll();

if($result->rowCount() > 0) 
{
    foreach($statement as $row)
    {
        if($user_fullname == $row['posted_by'])
        {
            $output .= "
                        <div class='jumbotron border-start border-3 border-danger mb-3'>
                            <div class='card-header mb-1'>
                                posted on <i> ".$row['posted_date']."</i>
                                by <b>".$row['posted_by']."</b>
                            </div>

                            <div class='container bg-light' style='background-image: var(--bs-gradient);'>
                                " .$row['comment']. "

                                <div class='mt-2 row'>
                                    <div class='col text-end'>
                                        <button class='reply btn shadow-none' onClick='postReply(" . $row['id'] . " , \"" . $row['posted_by'] . "\")'>
                                            Reply
                                        </button>

                                        <button class='btn shadow-none' onClick='deleteReply(" . $row['id'] . ")'>
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ";
        }
        else 
        {
            $output .= "
                        <div class='jumbotron border-start border-3 border-danger mb-3'>
                            <div class='card-header mb-1'>
                                posted on <i> ".$row['posted_date']."</i>
                                by <b>".$row['posted_by']."</b>
                            </div>

                            <div class='container bg-light' style='background-image: var(--bs-gradient);'>
                                " .$row['comment']. "

                                <div class='mt-2 row'>
                                    <div class='col text-end'>
                                        <button class='reply btn shadow-none' onClick='postReply(" . $row['id'] . " , \"" . $row['posted_by'] . "\")'>
                                            Reply
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        ";
        }

        $output .= get_reply_comment($row['id']);
    }    
}

echo $output;

function get_reply_comment($parent_id = 0, $marginleft = 0)
{
    global $con;
    $output = '';

    $user_fullname = $_SESSION['user']['fullname'];
    $query = "SELECT * FROM comments WHERE parent_id = :id";
    $stmt = $con->prepare($query);
    $stmt->bindValue('id', $parent_id, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetchAll();

    if($parent_id == 0)
    {
        $marginleft = 0;
    }
    else
    {
        $marginleft = $marginleft + 48;
    }

    if($stmt->rowCount() > 0)
    {
        foreach($result as $row)
        {
            if($user_fullname == $row['posted_by']) 
            {
                $output .= "
                            <div class='jumbotron border-start border-3 border-danger mb-3' style='margin-left: " . $marginleft . "px'>
                                <div class='card-header mb-1'>
                                    posted on <i> ".$row['posted_date']."</i>
                                    by <b>".$row['posted_by']."</b>
                                </div>

                                <div class='container-fluid bg-light' style='background-image: var(--bs-gradient);'>
                                    " .$row['comment']. "
                                    
                                    <div class='mt-2 row'>
                                        <div class='col text-end'>
                                            <button class='btn shadow-none' onClick='deleteReply(" . $row['id'] . ")'>
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            ";
            }
            else 
            {
                $output .= "
                            <div class='jumbotron border-start border-3 border-danger mb-3' style='margin-left: " . $marginleft . "px'>
                                <div class='card-header mb-1'>
                                    posted on <i> ".$row['posted_date']."</i>
                                    by <b>".$row['posted_by']."</b>
                                </div>

                                <div class='container-fluid bg-light' style='background-image: var(--bs-gradient);'>
                                    " .$row['comment']. "
                                </div>
                            </div>
                            ";
            }

            $output .= get_reply_comment($row['id']);
        }
    }
    return $output;
}

?>