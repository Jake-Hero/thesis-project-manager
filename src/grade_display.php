<?php

require '../libs/functions.php';

$user_fullname = $_SESSION['user']['fullname'];
$image = $_SESSION['user']['image'];

$query = "SELECT * FROM grades WHERE groupid = :id";
$result = $con->prepare($query);
$result->execute(['id' => $_POST['groupid']]);
$output = '';

$statement = $result->fetchAll();

if($result->rowCount() > 0) 
{
    foreach($statement as $row)
    {
        if($_POST['semester'] == 1) {
            $output.='
            <table class="table table-hover">
                <thead>
                    <tr class="table-light text-center">
                        <th scope="col" class="col-md-7">Grading Period</th>
                        <th scope="col" class="col-md-2">Current Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-light text-center">
                        <td>Pre-Lim</td>
                        <td>'.$row['first_prelims'].'%</td>
                    </tr>                                            
                    <tr class="table-light text-center">
                        <td>Midterms</td>
                        <td>'.$row['first_midterms'].'%</td>
                    </tr>                                            
                    <tr class="table-light text-center">
                        <td>Semi-Finals</td>
                        <td>'.$row['first_semis'].'%</td>
                    </tr>                                            
                    <tr class="table-light text-center">
                        <td>Finals</td>
                        <td>'.$row['first_finals'].'%</td>
                    </tr>

                    <tr class="table-light text-center">
                        <td class="fw-bold">Overall</td>
                        <td>
                            '.
                            $avg = ($row["first_prelims"] + $row['first_midterms'] + $row['first_semis'] + $row['first_finals']) / 4;
                            $avg
                            .'
                            %
                        </td>
                    </tr>
                </tbody>
            </table>
            ';
        } else {
            $output.='
            <table class="table table-hover">
                <thead>
                    <tr class="table-light text-center">
                        <th scope="col" class="col-md-7">Grading Period</th>
                        <th scope="col" class="col-md-2">Current Grade</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-light text-center">
                        <td>Pre-Lim</td>
                        <td>'.$row['second_prelims'].'%</td>
                    </tr>                                            
                    <tr class="table-light text-center">
                        <td>Midterms</td>
                        <td>'.$row['second_midterms'].'%</td>
                    </tr>                                            
                    <tr class="table-light text-center">
                        <td>Semi-Finals</td>
                        <td>'.$row['second_semis'].'%</td>
                    </tr>                                            
                    <tr class="table-light text-center">
                        <td>Finals</td>
                        <td>'.$row['second_finals'].'%</td>
                    </tr>

                    <tr class="table-light text-center">
                        <td class="fw-bold">Overall</td>
                        <td>
                            '.
                            $avg = ($row["second_prelims"] + $row['second_midterms'] + $row['second_semis'] + $row['second_finals']) / 4;
                            $avg
                            .'
                            %
                        </td>
                    </tr>
                </tbody>
            </table>
            ';
        }
    }    
}

echo $output;
?>

<script>
    $('#semester').on('change',function() {
        var val = $(this).val();
        listGrades(val);
    });
</script>