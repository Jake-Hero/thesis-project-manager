<?php
require '../libs/functions.php';

$selectStmt = $con->prepare('SELECT * FROM uploads WHERE task_id = :t_id AND group_id = :id ORDER BY file_id DESC');
$selectStmt->execute(['t_id' => $_POST['task_id'], 'id' => $_POST['groupid']]);
$result = $selectStmt->fetchAll();

$output = '';

$output.= 
'<div class="mt-3">
    <table class="table table-hover">
        <thead>
            <tr class="table-light text-center">
                <th scope="col" class="text-center">File Name</th>
                <th scope="col" class="text-center">Uploaded By</th>
                <th scope="col" class="text-center">Uploaded On</th>
                <th scope="col" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
';

if($selectStmt->rowCount() > 0)
{
    foreach($result as $row)
    {
        if($_POST['assigned_to'] == $_SESSION['user']['id'])
        {
            $output.=
            '
            <tr class="table-light text-center">
                <td>'.$row["file_name"].'</td>
                <td>'.getFullName($row["file_uploaded_by"]).'</td>
                <td>'.getweekDay($row["file_date"]) . ' , ' . date("F j g:i a", strtotime($row["file_date"])) .'</td>
                <td colspan="2">
                    <a href="./download.php?groupid='.$_POST['groupid'].'&file='.$row['file_name'].'"><span class="badge bg-primary text-white">Download</span></a>
                    <a href="./delete_file.php?file='.$row['file_name'].'"><span class="badge bg-danger text-white">Delete</span></a>
                </td>
            </tr>   
            ';
        }
        else {
            $output.=
            '
            <tr class="table-light text-center">
                <td>'.$row["file_name"].'</td>
                <td>'.getFullName($row["file_uploaded_by"]).'</td>
                <td>'.getweekDay($row["file_date"]) . ' , ' . date("F j g:i a", strtotime($row["file_date"])) .'</td>
                <td colspan="2">
                    <a href="./download.php?file='.$row['file_name'].'&id='.$_SESSION['taskid'].'"><span class="badge bg-primary text-white">Download</span></a>
                </td>
            </tr>   
            ';
        }
    }
} else {

    $output.=
    '
    <tr class="table-light">
        <td colspan="4" class="text-center">No files uploaded for this group.</td>
    </tr>
    ';

}

$output.='</tbody>';
$output.='</table>';
$output.='</div>';

echo $output;
die;
?>