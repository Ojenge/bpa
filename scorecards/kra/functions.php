<?php
 include_once("../../config/config_mysqli.php");
function getKRAs() 
{
   global $connect;
    // Retrieve and display all the strategic results
    $result = mysqli_query($connect, "SELECT * FROM strategic_results ORDER BY id ASC");
    $output = '<table class="table table-striped table-sm table-hover table-responsive">';
    $output .= '<thead class="table-primary"><tr><th scope="col">ID</th><th scope="col">Strategic Priority</th><th scope="col">Strategic Result</th><th scope="col">Edit</th></tr></thead>';
    $output .= '<tbody>';
    $count = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $output .= '<tr>';
        $output .= '<td>' . $count . '</td>';
        $output .= '<td>' . htmlspecialchars($row['priority']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['result']) . '</td>';

        $output .= '<td>';
        $output .= '<a href="javascript:void(0)" title="Edit KRA" onclick="editKRA(\'' . $row["id"] . '\')"><i class="bi bi-pencil-square"></i></a>';
        $output .= ' <a href="javascript:void(0)" title="Delete KRA" onclick="deleteKRA(\'' . $row["id"] . '\')"><i class="bi bi-trash"></i></a>';
        $output .= '</td>';

        $output .= '</tr>';

        $count++;
    }
    
    $output .= '</tbody>';
    $output .= '</table>';

    // Close the database connection
    mysqli_close($connect);
    
    return $output;
}
?>