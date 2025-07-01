<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");

header('Content-Type: application/json');

// Initialize response array
$response = array();

try {
    // Get all active departments
    $departmentsQuery = mysqli_query($connect, "
        SELECT 
            o.id,
            o.name,
            o.mission,
            o.vision,
            COUNT(DISTINCT u.user_id) as staffCount,
            COUNT(DISTINCT i.id) as initiativeCount
        FROM organization o
        LEFT JOIN uc_users u ON u.department = o.id AND u.active = 1
        LEFT JOIN initiative i ON i.projectManager = u.user_id
        WHERE o.id != 'org0' 
        AND (o.showInTree = 'Yes' OR o.showInTree IS NULL)
        GROUP BY o.id, o.name, o.mission, o.vision
        ORDER BY o.name
    ");
    
    $departments = array();
    
    while ($dept = mysqli_fetch_assoc($departmentsQuery)) {
        $departments[] = array(
            'id' => $dept['id'],
            'name' => $dept['name'],
            'mission' => $dept['mission'] ?? '',
            'vision' => $dept['vision'] ?? '',
            'staffCount' => $dept['staffCount'] ?? 0,
            'initiativeCount' => $dept['initiativeCount'] ?? 0
        );
    }
    
    $response = array(
        'departments' => $departments,
        'total' => count($departments)
    );
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching department list: ' . $e->getMessage();
}

echo json_encode($response);
?>
