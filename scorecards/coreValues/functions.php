<?php
 include_once("../../config/config_mysqli.php");
 function numberToRomanRepresentation($number) 
 {
    $map = array('M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'x' => 10, 'ix' => 9, 'v' => 5, 'iv' => 4, 'i' => 1);
    $returnValue = '';
    while ($number > 0) {
        foreach ($map as $roman => $int) 
        {
            $number = (int)$number; // Ensure $number is treated as an integer
            if($number >= $int) 
            {
                $number -= $int;
                $returnValue .= $roman;
                break;
            }
        }
    }
    return $returnValue;
}
function getCoreValues($mainMenuState, $staff) 
{
   global $connect;
    // Retrieve and display all the strategic results
    $result = mysqli_query($connect, "SELECT * FROM core_value ORDER BY id ASC");
    $output = '<table class="table table-striped table-bordered table-sm table-hover table-responsive mb-0">';
    
    if($mainMenuState == "Scorecard")//Only show main edits when in Admin state
    $output .= '<thead class="bg-primary" style="--bs-bg-opacity: .1;"><tr><th scope="col">ID</th><th scope="col">Core Value</th><th scope="col">Description</th><th scope="col">Core Value Attributes</th></tr></thead>';
    
    else $output .= '<thead class="table-secondary"><tr><th scope="col">ID</th><th scope="col">Core Value</th><th scope="col">Description</th><th scope="col">Attributes</th><th scope="col">Edit</th></tr></thead>';
    $output .= '<tbody>';
    $count = 1;
    while ($row = mysqli_fetch_assoc($result)) {
        $output .= '<tr>';
        $output .= '<td>' . $count . '</td>';
        $output .= '<td>' . htmlspecialchars($row['value']) . '</td>';
        $output .= '<td>' . htmlspecialchars($row['description']) . '</td>';

        $id = $row['id'];
        $attributesQuery = mysqli_query($connect, "SELECT * FROM core_value_attribute WHERE core_value_id = '$id' ORDER BY id ASC");
        $attributes = "<table class='table mb-0 table-sm table-condensed'><tr>";
        $attributes .= '<tr><th colspan="2"></th><th>Name</th><th>Description</th><th scope="col">Score</th></tr>';
        $attributeCount = 1;
        while ($attributeRow = mysqli_fetch_assoc($attributesQuery)) {
            $attributes .= '<tr>';
            $attributes .= '<td scope="col" class="col-1">';
            if($mainMenuState == "Scorecard")
            {
                $attributes .= '<a href="javascript:void(0)" title="Edit Score" onclick="addAttributeScore(\'' . $attributeRow["id"] . '\')"><i class="bi bi-pencil-square"></i></a>';
            }
            else
            {
                $attributes .= '<a href="javascript:void(0)" title="Edit Attribute" onclick="editAttribute(\'' . $attributeRow["id"] . '\')"><i class="bi bi-pencil-square"></i></a>';
                $attributes .= ' <a href="javascript:void(0)" title="Delete Attribute" onclick="deleteAttribute(\'' . $attributeRow["id"] . '\')"><i class="bi bi-trash"></i></a>';
            }
            $attributes .= '</td>';
            $attributeCountRoman = numberToRomanRepresentation($attributeCount);
            $attributes .= '<td scope="col" class="col-1">' . $attributeCountRoman . '</td>';
            $attributes .= '<td scope="col" class="col-3">' . htmlspecialchars($attributeRow['attribute']) . '</td>';
            $attributes .= '<td scope="col" class="col-3">' . htmlspecialchars($attributeRow['description']) . '</td>';

            $attributeScore = mysqli_query($connect, "SELECT * FROM core_value_attribute_score WHERE attribute_id = '" . $attributeRow['id'] . "' AND updater = '".$staff."' ORDER BY id DESC LIMIT 1");
            $scoreRow = mysqli_fetch_assoc($attributeScore);
            if (!$scoreRow) {
                $scoreRow = ''; // Default value if no score is found
            }
            else
            {
                $scoreRow = htmlspecialchars($scoreRow['score']);
            }
            $attributes .= '<td scope="col" class="col-3">' . $scoreRow . '</td>';
            
            $attributes .= '</tr>';
            $attributeCount++;
        }
        $attributes .= '</tr></table>';

        $output .= '<td>' . $attributes . '</td>';
        if($mainMenuState == "Scorecard")//Only show main edits when in Admin state
        {}
        else
        {
            $output .= '<td>';
            $output .= '<a href="javascript:void(0)" title="Edit Core Value" onclick="editCoreValue(\'' . $row["id"] . '\')"><i class="bi bi-pencil-square"></i></a>';
            $output .= ' <a href="javascript:void(0)" title="Add Attribute" onclick="attributeDialogShow(\'' . $row["id"] . '\')"><i class="bi bi-plus-square"></i></a>';
            $output .= ' <a href="javascript:void(0)" title="Delete Core Value" onclick="deleteCoreValue(\'' . $row["id"] . '\')"><i class="bi bi-trash"></i></a>';
            $output .= '</td>';
        }
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