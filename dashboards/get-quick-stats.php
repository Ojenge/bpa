<?php
include_once("../config/config_mysqli.php");

header('Content-Type: application/json');

$response = array();

try {
    // Get total departments
    $deptQuery = mysqli_query($connect, "
        SELECT COUNT(*) as count 
        FROM organization 
        WHERE id != 'org0' 
        AND showInTree = 'Yes'
    ");
    $deptResult = mysqli_fetch_assoc($deptQuery);
    $response['totalDepartments'] = $deptResult['count'] ?? 0;
    
    // Get total active staff
    $staffQuery = mysqli_query($connect, "
        SELECT COUNT(*) as count 
        FROM uc_users 
        WHERE active = 1 
        AND title != 'Executive Assistant'
        AND department != 'org0'
    ");
    $staffResult = mysqli_fetch_assoc($staffQuery);
    $response['totalStaff'] = $staffResult['count'] ?? 0;
    
    // Get active initiatives
    $initiativesQuery = mysqli_query($connect, "
        SELECT COUNT(*) as count 
        FROM initiative 
        WHERE completionDate IS NULL
    ");
    $initiativesResult = mysqli_fetch_assoc($initiativesQuery);
    $response['activeInitiatives'] = $initiativesResult['count'] ?? 0;
    
    // Get average performance across all departments
    $avgPerformanceQuery = mysqli_query($connect, "
        SELECT AVG(ist.percentageCompletion) as avgPerformance
        FROM initiative_status ist
        INNER JOIN initiative i ON ist.initiativeId = i.id
        WHERE ist.updatedOn >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
        AND ist.updatedOn = (
            SELECT MAX(ist2.updatedOn) 
            FROM initiative_status ist2 
            WHERE ist2.initiativeId = ist.initiativeId
        )
    ");
    $avgPerformanceResult = mysqli_fetch_assoc($avgPerformanceQuery);
    $response['avgPerformance'] = round($avgPerformanceResult['avgPerformance'] ?? 0, 1);
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching quick stats: ' . $e->getMessage();
}

echo json_encode($response);
?>
