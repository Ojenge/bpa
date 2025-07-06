<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Executive Summary Dashboard</title>

    
    <!-- Bootstrap CSS -->
    <link href="css/myBootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/dashboardTables.css" rel="stylesheet">

    <style>
        body {
            font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
            background-color: #f8f9fa;
        }

        .executive-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .metric-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .dashboard-card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .header .sign:after {
            font-family: "Font Awesome 6 Free";
            content: "\f146";
            display: inline-block;
            padding-right: 3px;
            vertical-align: middle;
            font-weight: 900;
        }

        .header.expand .sign:after {
            font-family: "Font Awesome 6 Free";
            content: "\f0fe";
            display: inline-block;
            padding-right: 3px;
            vertical-align: middle;
            font-weight: 900;
        }

        .table-executive {
            font-size: 0.9rem;
        }

        .trend-up { color: #28a745; }
        .trend-down { color: #dc3545; }
        .trend-neutral { color: #6c757d; }
    </style>
</head>
<body>

<?php
include_once("../config/config_mysqli.php");
include_once("../admin/models/config.php");
include_once("../functions/functions.php");
include_once("../functions/calendar-labels.php");
include_once("../functions/perspOrg-scores.php");

date_default_timezone_set('Africa/Nairobi');
@$objectId = "ind".$loggedInUser->user_id;
@$objectPeriod = $_GET['objectPeriod'];
@$objectDate = $_GET['objectDate'];

// Set defaults if not provided
if (!$objectPeriod) $objectPeriod = 'months';
if (!$objectDate) $objectDate = date("Y-m");

$todaysDay = date("d");
$objectDate = date("Y-m-d",strtotime($objectDate."-30")); //Changed this from 01 to 30 - was not accurate - excludes the rest of the month when getting results on a day past 01. Need to update this across the system. LTK 27 Jun 2021 0051 Hrs
//$objectDate = date("Y-m-d",strtotime($objectDate.$todaysDay)); //Possibly More precise

$userPermission = fetchUserPermissions($loggedInUser->user_id);

$showAllUsers = "False";
$permittedUsers = array();
$permCount = 0;
foreach($userPermission as $id)
{
	if($id["permission_id"] == "2") $showAllUsers = "True"; //Administrator Role
	$permittedUsers[$permCount] = $id["permission_id"];
	$permCount++;
}

// Execute staff query early for metrics calculation
$staffQuery = mysqli_query($connect, "SELECT uc_users.user_id, uc_users.user_name, uc_users.display_name, uc_users.reportsTo, uc_users.photo, uc_users.title, uc_users.last_sign_in_stamp, organization.name
FROM uc_users, organization
WHERE reportsTo = 'ind7'
AND title <> 'Executive Assistant'
AND uc_users.department = organization.id
ORDER by reportsTo");

if (!$staffQuery) {
    file_put_contents("error.txt", "Error=> ".mysqli_error($connect));
    $staffCount = 0;
} else {
    $staffCount = mysqli_num_rows($staffQuery);
}

function getColor($objectId, $objectDate = null)
{
	// Use provided date or default to current month
	if ($objectDate === null) {
		$objectDate = date("Y-m-01");
	} else {
		$objectDate = date("Y-m-d",strtotime($objectDate."-01"));
	}
	$objectPeriod = 'months';
	$score = individualScore($objectId, $objectDate, $objectPeriod);
	
	$color = "grey";
	
	if ($score >= 6.67) $color = "green";
	else if ($score >= 3.33 && $score < 6.67) $color = "amber";
	else if ($score < 3.33 && $score > 0) $color = "red";
	else $color = "grey";
	return $color;
}

function previousPeriodQuery($objectPeriod, $objectDate, $userId)
{
	switch($objectPeriod)
	{
		case "months":
		{
			$periodQuery = "SELECT AVG(initiative_status.percentageCompletion) AS indScore
		FROM initiative, initiative_status 
		WHERE initiative_status.updatedOn = 
		(SELECT MAX(updatedOn) FROM initiative_status WHERE initiative.projectManager = '$userId' 
		 AND initiative_status.initiativeId = initiative.id 
		 AND initiative_status.percentageCompletion != 0 
		 AND initiative_status.initiativeId = initiative.id
		 AND initiative_status.updatedOn <= '$objectDate' - INTERVAL 1 MONTH)";
			break;	
		}
		case "years":
		{
			$periodQuery = "SELECT AVG(initiative_status.percentageCompletion) AS indScore
		FROM initiative, initiative_status 
		WHERE initiative_status.updatedOn = 
		(SELECT MAX(updatedOn) FROM initiative_status WHERE initiative.projectManager = '$userId' 
		 AND initiative_status.initiativeId = initiative.id 
		 AND initiative_status.percentageCompletion != 0 
		 AND initiative_status.initiativeId = initiative.id
		 AND initiative_status.updatedOn <= '$objectDate' - INTERVAL 1 YEAR)";
			break;	
		}	
	}
	return $periodQuery;
}

function checkSubordinates($id, $connect)
{
	$childQuery = mysqli_query($connect, "SELECT user_id, user_name, display_name, reportsTo, photo, title, department  
					FROM uc_users 
					WHERE reportsTo = '$id'
					AND title <> 'Executive Assistant'
					ORDER by reportsTo") or file_put_contents("error.txt", "Error=> ".mysqli_error($connect));
	$childCount = mysqli_num_rows($childQuery);
	if($childCount > 0) return "Yes";
	else return "No";
}

function getSubordinates($id, $connect)
{
	global $objectPeriod, $objectDate;

	// Set defaults if not provided
	if (!isset($objectPeriod)) $objectPeriod = 'months';
	if (!isset($objectDate)) $objectDate = date("Y-m-d");

	$childQuery = mysqli_query($connect, "SELECT user_id, user_name, display_name, reportsTo, photo, title, department
					FROM uc_users
					WHERE reportsTo = '$id'
					AND title <> 'Executive Assistant'
					ORDER by reportsTo") or file_put_contents("error.txt", "Error=> ".mysqli_error($connect));
	$childCount = mysqli_num_rows($childQuery);
	if($childCount > 0)
	{
		echo '<tr style="display:none;"><td></td><td colspan="8">';
		echo '<table class="table table-responsive table-bordered table-sm table-condensed">';
		echo '<tr class="table-primary"><th>Name</th><th>Title</th><th style="text-align:center">Assigned Initiatives</th><th style="text-align:center">Initiatives Updated Within a Month</th><th style="text-align:center">Previous Period Score</th><th style="text-align:center" colspan="2">Current Score</th><th>Last Sign In</th></tr>';
		while($row = mysqli_fetch_array($childQuery))
		{
			if($row['last_sign_in_stamp'] == 0) $lastSignIn = "Never";
			else $lastSignIn = date("j M, Y", $row['last_sign_in_stamp']);
			
			$userId = $row["user_id"];
			$taskCount = mysqli_query($connect, "SELECT COUNT(id) AS count FROM initiative WHERE projectManager = '$userId'");
			$taskCount = mysqli_fetch_array($taskCount);
			$taskCount = $taskCount["count"];
			
			$updateCount = mysqli_query($connect, "SELECT COUNT(DISTINCT(initiative.id)) AS count 
			FROM initiative, initiative_status 
			WHERE initiative.projectManager = '$userId'
			AND initiative_status.initiativeId = initiative.id
			AND initiative_status.updatedOn > NOW() - INTERVAL 1 MONTH");
			$updateCount = mysqli_fetch_array($updateCount);
			$updateCount = $updateCount["count"];
			$indScore = NULL;
			$indScore = mysqli_query($connect, "SELECT AVG(initiative_status.percentageCompletion) AS indScore
				FROM initiative, initiative_status 
				WHERE initiative_status.updatedOn = (SELECT MAX(updatedOn) FROM initiative_status WHERE initiative.projectManager = '$userId' 
				AND initiative_status.initiativeId = initiative.id 
				AND initiative_status.percentageCompletion != 0 AND initiative_status.initiativeId = initiative.id )");
			$indScore = mysqli_fetch_array($indScore);
			
			if($indScore["indScore"] == NULL) $indScore = "";
			else $indScore = round($indScore["indScore"], 2)."%";
			
			$periodQuery = previousPeriodQuery($objectPeriod, $objectDate, $userId);
	
			$indScorePrevious = mysqli_query($connect, $periodQuery) or file_put_contents("error.txt", "Error => ".mysqli_error($connect));
			$indScorePrevious = mysqli_fetch_array($indScorePrevious);
			
			if($indScorePrevious["indScore"] == NULL) $indScorePrevious = "";
			else $indScorePrevious = round($indScorePrevious["indScore"], 2)."%";
			
			if($indScorePrevious == "" || $indScore == "") $indScoreTrend = "";
			else if($indScorePrevious < $indScore) $indScoreTrend = '<i class="fa fa-arrow-up text-success"></i>';
			else if($indScorePrevious > $indScore) $indScoreTrend = '<i class="fa fa-arrow-down text-danger"></i>';
			else $indScoreTrend = '<i class="fas fa-arrows-alt-h text-warning"></i>';
	
			$subordinates = checkSubordinates($row["user_id"], $connect);
			if($subordinates == "Yes")
				echo '<tr class="header expand" style="cursor:pointer;">
				<td>'.$row["display_name"]."<span class='sign' style='float:right;'></span></td>
				<td>".$row["title"]."</td>
				<td style='text-align:center'>".$taskCount."</td>
				<td style='text-align:center'>".$updateCount."</td>
				<td style='text-align:center'>".$indScorePrevious."</td>
				<td style='text-align:center' class='border-end-0'>".$indScore."</td>
				<td style='text-align:center' class='border-start-0'>".$indScoreTrend."</td>
				<td>".$lastSignIn."</td></tr>";
			else
				echo '<tr class="header expand">
				<td>'.$row["display_name"]."</td>
				<td>".$row["title"]."</td>
				<td style='text-align:center'>".$taskCount."</td>
				<td style='text-align:center'>".$updateCount."</td>
				<td style='text-align:center'>".$indScorePrevious."</td>
				<td style='text-align:center' class='border-end-0'>".$indScore."</td>
				<td style='text-align:center' class='border-start-0'>".$indScoreTrend."</td>
				<td>".$lastSignIn."</td></tr>";
			getSubordinates($row["user_id"], $connect);
		}
		echo "</table></td></tr>";
	}
}
?>

    <div class="container-fluid py-4">
        <!-- Executive Header -->
        <div class="executive-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">
                        <i class="fas fa-chart-line me-3"></i>
                        Executive Summary Dashboard
                    </h1>
                    <p class="mb-0 opacity-75">
                        High-level organizational performance overview and key metrics
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-light btn-sm" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt me-1"></i> Refresh
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="exportReport()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="totalStaff"><?php echo $staffCount; ?></div>
                    <div class="metric-label">Total Staff Members</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="activeInitiatives">--</div>
                    <div class="metric-label">Active Initiatives</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="avgPerformance">--</div>
                    <div class="metric-label">Average Performance</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="metric-value" id="lastUpdate">--</div>
                    <div class="metric-label">Last Updated</div>
                </div>
            </div>
        </div>

        <!-- Executive Team Performance -->
        <div class="card dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Executive Team Performance
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">

<?php
// Reset the query result pointer to the beginning
if ($staffQuery) {
    mysqli_data_seek($staffQuery, 0);
}

echo '<table class="table table-executive table-hover table-bordered">';
echo '<thead class="table-light">';
echo '<tr><th>Name</th><th>Title</th><th>Department</th><th style="text-align:center">Assigned Initiatives</th><th style="text-align:center">Updated This Month</th><th style="text-align:center">Previous Score</th><th style="text-align:center" colspan="2">Current Score</th><th>Last Sign In</th></tr>';
echo '</thead><tbody>';

if (!$staffQuery) {
    echo '<tr><td colspan="9" class="text-center text-muted">No staff data available</td></tr>';
} else {

while($row = mysqli_fetch_array($staffQuery))
{
	if($row['last_sign_in_stamp'] == 0) $lastSignIn = "Never";
	else $lastSignIn = date("j M, Y", $row['last_sign_in_stamp']);
	
	$userId = $row["user_id"];
	$taskCount = mysqli_query($connect, "SELECT COUNT(id) AS count FROM initiative WHERE projectManager = '$userId'");
	$taskCount = mysqli_fetch_array($taskCount);
	$taskCount = $taskCount["count"];
	
	/*$updateCount = mysqli_query($connect, "SELECT COUNT(DISTINCT(initiative.id)) AS count 
	FROM initiative, initiative_status 
	WHERE initiative.projectManager = '$userId'
	AND initiative_status.initiativeId = initiative.id
	AND initiative_status.updatedOn > NOW() - INTERVAL 1 MONTH");*/
	
	$updateCount = mysqli_query($connect, "SELECT COUNT(DISTINCT(initiative.id)) AS count 
	FROM initiative, initiative_status 
	WHERE initiative.projectManager = '$userId'
	AND initiative_status.initiativeId = initiative.id
	AND initiative_status.updatedOn > '$objectDate' - INTERVAL 1 MONTH");
	
	$updateCount = mysqli_fetch_array($updateCount);
	$updateCount = $updateCount["count"];
	$indScore = NULL;
	$indScore = mysqli_query($connect, "SELECT AVG(initiative_status.percentageCompletion) AS indScore
		FROM initiative, initiative_status 
		WHERE initiative_status.updatedOn = 
		(SELECT MAX(updatedOn) FROM initiative_status WHERE initiative.projectManager = '$userId' 
		 AND initiative_status.initiativeId = initiative.id 
		 AND initiative_status.percentageCompletion != 0 
		 AND initiative_status.initiativeId = initiative.id
		 AND initiative_status.updatedOn <= '$objectDate')");
	$indScore = mysqli_fetch_array($indScore);
	
	if($indScore["indScore"] == NULL) $indScore = "";
	else $indScore = round($indScore["indScore"], 2)."%";
	
	$periodQuery = previousPeriodQuery($objectPeriod, $objectDate, $userId);
	
	$indScorePrevious = mysqli_query($connect, $periodQuery) or file_put_contents("error.txt", "Error => ".mysqli_error($connect).mysqli_error($periodQuery));
	$indScorePrevious = mysqli_fetch_array($indScorePrevious);
	
	if($indScorePrevious["indScore"] == NULL) $indScorePrevious = "";
	else $indScorePrevious = round($indScorePrevious["indScore"], 2)."%";
	
	if($indScorePrevious == "" || $indScore == "") $indScoreTrend = "";
	else if($indScorePrevious < $indScore) $indScoreTrend = '<i class="fa fa-arrow-up text-success"></i>';
	else if($indScorePrevious > $indScore) $indScoreTrend = '<i class="fa fa-arrow-down text-danger"></i>';
	else  $indScoreTrend = '<i class="fas fa-arrows-alt-h text-warning"></i>';
	
	$subordinates = checkSubordinates($row["user_id"], $connect);
	if($subordinates == "Yes")
		echo '<tr class="header expand" style="cursor:pointer;">
		<td>'.$row["display_name"]."<span class='sign' style='float:right;'></span></td>
		<td>".$row["title"]."</td><td>".$row["name"]."</td>
		<td style='text-align:center'>".$taskCount."</td>
		<td style='text-align:center'>".$updateCount."</td>
		<td style='text-align:center'>".$indScorePrevious."</td>
		<td style='text-align:center' class='border-end-0'>".$indScore."</td>
		<td style='text-align:center' class='border-start-0'>".$indScoreTrend."</td>
		<td>".$lastSignIn."</td></tr>";
	else
		echo '<tr class="header expand">
		<td>'.$row["display_name"]."</td>
		<td>".$row["title"]."</td><td>".$row["name"]."</td>
		<td style='text-align:center'>".$taskCount."</td>
		<td style='text-align:center'>".$updateCount."</td>
		<td style='text-align:center'>".$indScorePrevious."</td>
		<td style='text-align:center' class='border-end-0'>".$indScore."</td>
		<td style='text-align:center' class='border-start-0'>".$indScoreTrend."</td>
		<td>".$lastSignIn."</td></tr>";
	getSubordinates($row["user_id"], $connect);
}
} // Close the if (!$staffQuery) else block
echo '</tbody></table>';
?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="js/bootstrap.bundle.min.js"></script>
    <!-- jQuery for legacy functionality -->
    <script src="js/jquery-3.2.1.min.js"></script>

    <script>
        // Legacy table expand/collapse functionality
        $(document).ready(function() {
            $('tr.header').click(function() {
                $(this).toggleClass('expand').nextUntil('tr.header').slideToggle(100);
            });
        });

        // Dashboard refresh functionality
        function refreshDashboard() {
            location.reload();
        }

        // Export functionality
        function exportReport() {
            window.print();
        }

        // Calculate and display metrics
        $(document).ready(function() {
            // Calculate active initiatives
            var totalInitiatives = 0;
            var totalPerformance = 0;
            var performanceCount = 0;

            $('table tbody tr').each(function() {
                var initiatives = $(this).find('td:nth-child(4)').text();
                if (initiatives && !isNaN(initiatives)) {
                    totalInitiatives += parseInt(initiatives);
                }

                var performance = $(this).find('td:nth-child(7)').text();
                if (performance && performance.includes('%')) {
                    var perfValue = parseFloat(performance.replace('%', ''));
                    if (!isNaN(perfValue)) {
                        totalPerformance += perfValue;
                        performanceCount++;
                    }
                }
            });

            $('#activeInitiatives').text(totalInitiatives);

            if (performanceCount > 0) {
                var avgPerf = (totalPerformance / performanceCount).toFixed(1);
                $('#avgPerformance').text(avgPerf + '%');
            } else {
                $('#avgPerformance').text('N/A');
            }

            $('#lastUpdate').text(new Date().toLocaleDateString());
        });
    </script>
</body>
</html>