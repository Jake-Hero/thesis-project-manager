<?php 
require realpath(dirname(__FILE__) . '/../libs/functions.php');

$id = $_POST['userid'];

$query = "SELECT g.*, u.email_verified FROM grades AS g INNER JOIN users AS u ON u.id = g.userid WHERE userid = :id";
$selectStmt = $con->prepare($query);
$selectStmt->bindValue('id', $id); 
$selectStmt->execute();

if($selectStmt->rowCount() > 0)
{
    $row = $selectStmt->fetch(PDO::FETCH_ASSOC);

    if($_POST['grading'] == 'preoral')
    {
        $total = ($row["pre_rubrics_1"] + $row['pre_rubrics_2'] + $row['pre_rubrics_3'] + $row['pre_rubrics_4'] + $row['pre_rubrics_5'] + $row['pre_rubrics_6']);

        $message = "Your grades for Pre-Oral Defense has been released.";
        $message.= "\r\n";
        $message.= "You are graded based from these rubrics below.";
        $message.= "\r\n\n";

        $message.= "<table>";
        $message.= "<tr>";
        $message.= "<th>Aspect</th>";
        $message.= "<th>Score</th>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Introduction</td><td>" .$row['pre_rubrics_1']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Completeness</td><td>" .$row['pre_rubrics_2']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Organization</td><td>" .$row['pre_rubrics_3']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Speaking Skills</td><td>" .$row['pre_rubrics_4']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Presentation</td><td>" .$row['pre_rubrics_5']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Questions And Answers</td><td>" .$row['pre_rubrics_6']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Score: <strong>" .$total. "</strong></td><td>Percentage: <strong>" .getGradeConversion(1, $total). "%</strong></td>";
        $message.= "</tr>";
        $message.= "</table>";

        $message.= "\r\n\nPlease ignore this E-Mail if you aren't the one who requested for this code.";
        $message.= "\r\nThis message is automated, Please do not reply to this email.";
        $message = nl2br($message);
        
        send_mail($row['email_verified'], "Pre-Oral Defense Grades released!", $message);
    }
    else 
    {
        $total = ($row["oral_rubrics_1"] + $row['oral_rubrics_2'] + $row['oral_rubrics_3'] + $row['oral_rubrics_4'] + $row['oral_rubrics_5'] + $row['oral_rubrics_6'] + $row['oral_rubrics_7']);

        $message = "Your grades for Oral Defense has been released.";
        $message.= "\r\n";
        $message.= "You are graded based from these rubrics below.";
        $message.= "\r\n\n";

        $message.= "<table>";
        $message.= "<tr>";
        $message.= "<th>Aspect</th>";
        $message.= "<th>Score</th>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Introduction</td><td>" .$row['oral_rubrics_1']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Completeness</td><td>" .$row['oral_rubrics_2']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Organization</td><td>" .$row['oral_rubrics_3']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Findings</td><td>" .$row['oral_rubrics_7']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Speaking Skills</td><td>" .$row['oral_rubrics_4']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Presentation</td><td>" .$row['oral_rubrics_5']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Questions And Answers</td><td>" .$row['oral_rubrics_6']. "</td>";
        $message.= "</tr>";
        $message.= "<tr>";
        $message.= "<td>Score: <strong>" .$total. "</strong></td><td>Percentage: <strong>" .getGradeConversion(2, $total). "%</strong></td>";
        $message.= "</tr>";
        $message.= "</table>";

        $message.= "\r\n\nPlease ignore this E-Mail if you aren't the one who requested for this code.";
        $message.= "\r\nThis message is automated, Please do not reply to this email.";
        $message = nl2br($message);
        
        send_mail($row['email_verified'], "Oral Defense Grades released!", $message);
    }

    echo "success";
    exit();
}

exit();
?>