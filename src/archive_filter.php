<?php

require_once "../libs/functions.php";

if (isset($_POST['search'])) 
{
    $search = '%' . $_POST['search'] . '%';
    $query = "SELECT * FROM archives WHERE CONCAT(title, publishedyear) LIKE :name ORDER BY publishedyear DESC";
    $statement = $con->prepare($query);
    $statement->bindValue(':name', $search); 
    $statement->execute();

    echo 'Search result for: ' . $_POST['search'] . '<br>' . 'Results found: ' . $statement->rowCount() . '<br>';

    $output = 
    '
    <table class="table table-hover">
    <thead>
        <tr class="table-light text-center">
            <th scope="col" class="text-center">Title</th>
            <th scope="col" class="text-center">Year Published</th>
            <th scope="col" class="text-center">Uploaded on</th>
            <th scope="col" class="text-center">Department</th>
            <th scope="col" class="text-center">Action</th>
        </tr>
    </thead>
    <tbody>
    ';

    if($statement->rowCount() > 0)
    {
        while($result = $statement->fetch(PDO::FETCH_ASSOC))  
        {
            $output.=
            '
            <tr class="table-light text-center">
                <td>
                    <a href="./view_archive.php?id=' .$result['id']. '">
                        '.$result['title'].'
                    </a>
                </td>
                <td>'.$result['publishedyear'].'</td>
                <td>'.$result['date'].'</td>
                <td>'.
                    categorizedDepartment($result['department'])
                .'</td>
                <td>
                    <a href="./view_archive.php?id=' .$result['id']. '">
                        <input type="button" value="View" />
                    </a>
                </td>
            </tr>
            ';
        }
    }
    else 
    {
        $output.=
        '
        <tr class="table-light">
            <td colspan="5" class="text-center">No search result found.</td>
        </tr>
        ';    
    }
    
    $output.='</tbody>';
    $output.='</table>';
    $output.='</div>';
    
    echo $output;
}

die;
?>