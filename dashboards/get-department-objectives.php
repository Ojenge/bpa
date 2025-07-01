<?php
include_once("../config/config_mysqli.php");
include_once("../functions/functions.php");

header('Content-Type: application/json');

// Get parameters
$departmentId = $_POST['departmentId'] ?? 'org1';
$objectPeriod = $_POST['objectPeriod'] ?? 'months';
$objectDate = $_POST['objectDate'] ?? date("Y-m");

// Initialize response array
$response = array();

try {
    // Get department objectives
    $objectivesQuery = mysqli_query($connect, "
        SELECT 
            o.id,
            o.name,
            o.outcome,
            o.linkedObject
        FROM objective o
        WHERE o.linkedObject = '$departmentId'
        ORDER BY o.name
    ");
    
    $objectives = array();
    
    while ($objective = mysqli_fetch_assoc($objectivesQuery)) {
        $objectiveId = $objective['id'];
        $objectiveName = $objective['name'];
        $objectiveOutcome = $objective['outcome'];
        
        // Get measures linked to this objective
        $measuresQuery = mysqli_query($connect, "
            SELECT 
                m.id,
                m.name as measureName,
                m.calendarType,
                CASE 
                    WHEN m.calendarType = 'Monthly' THEN mm.actual
                    WHEN m.calendarType = 'Quarterly' THEN mq.actual
                    WHEN m.calendarType = 'Yearly' THEN my.actual
                    ELSE NULL
                END as actual,
                CASE 
                    WHEN m.calendarType = 'Monthly' THEN mm.green
                    WHEN m.calendarType = 'Quarterly' THEN mq.green
                    WHEN m.calendarType = 'Yearly' THEN my.green
                    ELSE m.green
                END as target,
                CASE 
                    WHEN m.calendarType = 'Monthly' THEN mm.3score
                    WHEN m.calendarType = 'Quarterly' THEN mq.3score
                    WHEN m.calendarType = 'Yearly' THEN my.3score
                    ELSE 0
                END as score
            FROM measure m
            LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
                AND DATE_FORMAT(mm.date, '%Y-%m') = '" . date('Y-m', strtotime($objectDate)) . "'
            LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
                AND QUARTER(mq.date) = " . ceil(date('n', strtotime($objectDate)) / 3) . " 
                AND YEAR(mq.date) = " . date('Y', strtotime($objectDate)) . "
            LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
                AND YEAR(my.date) = " . date('Y', strtotime($objectDate)) . "
            WHERE m.linkedObject = '$objectiveId'
        ");
        
        $measures = array();
        $totalScore = 0;
        $measureCount = 0;
        
        while ($measure = mysqli_fetch_assoc($measuresQuery)) {
            $score = $measure['score'] ?? 0;
            $measures[] = array(
                'id' => $measure['id'],
                'name' => $measure['measureName'],
                'actual' => $measure['actual'] ?? 0,
                'target' => $measure['target'] ?? 0,
                'score' => $score,
                'achievement' => round($score * 20, 1), // Convert to percentage
                'calendarType' => $measure['calendarType']
            );
            
            $totalScore += $score;
            $measureCount++;
        }
        
        // Calculate objective progress
        $progress = $measureCount > 0 ? round(($totalScore / $measureCount) * 20, 1) : 0;
        
        // Get initiatives linked to this objective
        $initiativesQuery = mysqli_query($connect, "
            SELECT 
                i.id,
                i.name as initiativeName,
                i.completionDate,
                ist.percentageCompletion,
                ist.status,
                u.display_name as managerName
            FROM initiative i
            LEFT JOIN initiative_status ist ON i.id = ist.initiativeId
            LEFT JOIN uc_users u ON i.projectManager = u.user_id
            WHERE i.linkedObjectId = '$objectiveId'
            AND ist.updatedOn = (
                SELECT MAX(ist2.updatedOn) 
                FROM initiative_status ist2 
                WHERE ist2.initiativeId = ist.initiativeId
            )
            ORDER BY i.name
        ");
        
        $initiatives = array();
        $totalInitiativeProgress = 0;
        $initiativeCount = 0;
        
        while ($initiative = mysqli_fetch_assoc($initiativesQuery)) {
            $completion = $initiative['percentageCompletion'] ?? 0;
            $initiatives[] = array(
                'id' => $initiative['id'],
                'name' => $initiative['initiativeName'],
                'completion' => $completion,
                'status' => $initiative['status'] ?? 'Not Started',
                'manager' => $initiative['managerName'] ?? 'Unassigned',
                'completionDate' => $initiative['completionDate'] ? date('M j, Y', strtotime($initiative['completionDate'])) : 'Not set'
            );
            
            $totalInitiativeProgress += $completion;
            $initiativeCount++;
        }
        
        // Calculate combined progress (measures + initiatives)
        $initiativeProgress = $initiativeCount > 0 ? $totalInitiativeProgress / $initiativeCount : 0;
        $combinedProgress = $measureCount > 0 && $initiativeCount > 0 ? 
            round(($progress + $initiativeProgress) / 2, 1) : 
            ($measureCount > 0 ? $progress : $initiativeProgress);
        
        // Determine status
        $status = 'at-risk';
        if ($combinedProgress >= 90) {
            $status = 'excellent';
        } elseif ($combinedProgress >= 75) {
            $status = 'on-track';
        } elseif ($combinedProgress >= 60) {
            $status = 'needs-attention';
        }
        
        // Calculate estimated completion date
        $estimatedCompletion = 'Unknown';
        if ($combinedProgress > 0 && $combinedProgress < 100) {
            $remainingProgress = 100 - $combinedProgress;
            $currentDate = new DateTime();
            $startOfPeriod = new DateTime(date('Y-m-01', strtotime($objectDate)));
            $daysPassed = $currentDate->diff($startOfPeriod)->days;
            
            if ($daysPassed > 0) {
                $progressRate = $combinedProgress / $daysPassed;
                $daysToComplete = $remainingProgress / $progressRate;
                $completionDate = clone $currentDate;
                $completionDate->add(new DateInterval('P' . round($daysToComplete) . 'D'));
                $estimatedCompletion = $completionDate->format('M j, Y');
            }
        } elseif ($combinedProgress >= 100) {
            $estimatedCompletion = 'Completed';
        }
        
        $objectives[] = array(
            'id' => $objectiveId,
            'name' => $objectiveName,
            'description' => $objectiveOutcome,
            'progress' => $combinedProgress,
            'status' => $status,
            'measures' => $measures,
            'initiatives' => $initiatives,
            'measureCount' => $measureCount,
            'initiativeCount' => $initiativeCount,
            'estimatedCompletion' => $estimatedCompletion,
            'lastUpdate' => date('M j, Y') // Current date as placeholder
        );
    }
    
    // Calculate summary statistics
    $totalObjectives = count($objectives);
    $onTrackObjectives = count(array_filter($objectives, function($obj) {
        return in_array($obj['status'], ['excellent', 'on-track']);
    }));
    $atRiskObjectives = $totalObjectives - $onTrackObjectives;
    $avgProgress = $totalObjectives > 0 ? 
        round(array_sum(array_column($objectives, 'progress')) / $totalObjectives, 1) : 0;
    
    $response = array(
        'objectives' => $objectives,
        'summary' => array(
            'total' => $totalObjectives,
            'onTrack' => $onTrackObjectives,
            'atRisk' => $atRiskObjectives,
            'avgProgress' => $avgProgress
        )
    );
    
    // Get objective categories/perspectives
    $perspectivesQuery = mysqli_query($connect, "
        SELECT 
            p.id,
            p.name as perspectiveName,
            COUNT(o.id) as objectiveCount,
            AVG(
                CASE 
                    WHEN m.calendarType = 'Monthly' THEN mm.3score
                    WHEN m.calendarType = 'Quarterly' THEN mq.3score
                    WHEN m.calendarType = 'Yearly' THEN my.3score
                    ELSE 0
                END
            ) as avgScore
        FROM perspective p
        LEFT JOIN objective o ON o.linkedObject = p.id
        LEFT JOIN measure m ON m.linkedObject = o.id
        LEFT JOIN measuremonths mm ON m.id = mm.measureId AND m.calendarType = 'Monthly'
        LEFT JOIN measurequarters mq ON m.id = mq.measureId AND m.calendarType = 'Quarterly'
        LEFT JOIN measureyears my ON m.id = my.measureId AND m.calendarType = 'Yearly'
        WHERE p.parentId = '$departmentId'
        GROUP BY p.id, p.name
        ORDER BY p.name
    ");
    
    $perspectives = array();
    while ($perspective = mysqli_fetch_assoc($perspectivesQuery)) {
        $perspectives[] = array(
            'id' => $perspective['id'],
            'name' => $perspective['perspectiveName'],
            'objectiveCount' => $perspective['objectiveCount'] ?? 0,
            'avgScore' => round($perspective['avgScore'] ?? 0, 1),
            'progress' => round(($perspective['avgScore'] ?? 0) * 20, 1)
        );
    }
    $response['perspectives'] = $perspectives;
    
} catch (Exception $e) {
    $response['error'] = 'Error fetching department objectives: ' . $e->getMessage();
}

echo json_encode($response);
?>
