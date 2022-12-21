<?php
require '../libs/functions.php';

$selectStmt = $con->prepare('SELECT * FROM uploads WHERE group_id = :id ORDER BY file_id DESC');
$selectStmt->execute(['id' => $_POST['groupid']]);
$result = $selectStmt->fetchAll();

$output = '';

$output.= 
'
<div class="mt-3">
    <table class="table table-hover">
        <thead>
            <tr class="table-light text-center">
                <th scope="col" class="text-center">File Name</th>
                <th scope="col" class="text-center">Uploaded By</th>
                <th scope="col" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
';

if($selectStmt->rowCount() > 0)
{
    foreach($result as $row)
    {
        $output.=
        '
        <tr class="table-light text-center">
            <td>'.$row['file_name'].'</td>
            <td>'.getFullName($row['file_uploaded_by']).'</td>
            <td colspan="2">
                <a href="../download.php?groupid='.$_POST['groupid'].'&file='.$row['file_name'].'"><span class="badge bg-primary text-white">Download</span></a>
                <a href="../admin/admin_delete_file.php?file='.$row['file_name'].'"><span class="badge bg-danger text-white">Delete</span></a>
            </td>
        </tr>
        ';
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