<?php
include_once("../config/config_mysqli.php");


// Input validation and sanitization
$tree_id = isset($_POST['tree_id']) ? mysqli_real_escape_string($connect, $_POST['tree_id']) : '';
$objectType = isset($_POST['tree_type']) ? mysqli_real_escape_string($connect, $_POST['tree_type']) : '';

// Validate inputs
if (empty($tree_id) || empty($objectType)) {
    die("Error: Missing required parameters");
}
/*
@$tree_id = "org3";
@$objectType = "organization";
*/
switch($objectType)
{
	case "organization":
	{
		// Update child organizations that cascade from this organization (set to root)
		@mysqli_query($connect, "UPDATE organization SET cascadedfrom = 'root' WHERE cascadedfrom = '$tree_id'");
		// Update individual records that are cascaded from this organization (set to root)
		@mysqli_query($connect, "UPDATE individual SET cascadedFrom = 'root' WHERE cascadedFrom = '$tree_id'");
		// Update user department field if it references this organization
		@mysqli_query($connect, "UPDATE uc_users SET department = NULL WHERE department = '$tree_id'");

		// Now delete the organization and related records
		@mysqli_query($connect, "DELETE FROM organization WHERE id = '$tree_id'");
		@mysqli_query($connect, "DELETE FROM tree WHERE id = '$tree_id'");
		@mysqli_query($connect, "DELETE FROM uc_permissions WHERE orgId = '$tree_id'");
		@mysqli_query($connect, "DELETE FROM perspective WHERE parentId = '$tree_id'");
		@mysqli_query($connect, "DELETE FROM objective WHERE linkedObject = '$tree_id'");
		$kpiId = @mysqli_query($connect, "SELECT id FROM measure WHERE linkedObject = '$tree_id'");
		while($kpiId = @mysqli_fetch_assoc($kpiId))
		{
			$kpiId = $kpiId["id"];
			$getCalendar = mysqli_query($connect, "SELECT calendarType FROM measure WHERE id = '$kpiId'");
			$getCalendar = mysqli_fetch_assoc($getCalendar);
			$getCalendar = $getCalendar["calendarType"];
			$table = "measuremonths";
			switch($getCalendar)
			{
				case 'Daily':
				{
					$table = 'measuredays';
					break;	
				}
				case 'Weekly':
				{
					$table = 'measureweeks';
					break;	
				}
				case 'Monthly':
				{
					$table = 'measuremonths';
					break;	
				}
				case 'Quarterly':
				{
					$table = 'measurequarters';
					break;	
				}
				case 'Bi-Annually':
				{
					$table = 'measurehalfyear';
					break;	
				}
				case 'Yearly':
				{
					$table = 'measureyears';
					break;	
				}
			}
			@mysqli_query($connect, "DELETE FROM $table WHERE measureId = '$kpiId'");
			@mysqli_query($connect, "DELETE FROM measure WHERE linkedObject = '$kpiId'");
			@mysqli_query($connect, "DELETE FROM measurelinks WHERE measure_id = '$tree_id'");
		}
		break;
	}
	case "individual":
	{
		@mysqli_query($connect, "DELETE FROM individual WHERE id = '$tree_id'");
		@mysqli_query($connect, "DELETE FROM tree WHERE id = '$tree_id'");
		@mysqli_query($connect, "DELETE FROM perspective WHERE parentId = '$tree_id'");
		@mysqli_query($connect, "DELETE FROM objective WHERE linkedObject = '$tree_id'");
		$kpiId = @mysqli_query($connect, "SELECT id FROM measure WHERE linkedObject = '$tree_id'");
		while($kpiId = @mysqli_fetch_assoc($kpiId))
		{
			$kpiId = $kpiId["id"];
			$getCalendar = mysqli_query($connect, "SELECT calendarType FROM measure WHERE id = '$kpiId'");
			$getCalendar = mysqli_fetch_assoc($getCalendar);
			$getCalendar = $getCalendar["calendarType"];
			$table = "measuremonths";
			switch($getCalendar)
			{
				case 'Daily':
				{
					$table = 'measuredays';
					break;	
				}
				case 'Weekly':
				{
					$table = 'measureweeks';
					break;	
				}
				case 'Monthly':
				{
					$table = 'measuremonths';
					break;	
				}
				case 'Quarterly':
				{
					$table = 'measurequarters';
					break;	
				}
				case 'Bi-Annually':
				{
					$table = 'measurehalfyear';
					break;	
				}
				case 'Yearly':
				{
					$table = 'measureyears';
					break;	
				}
			}
			@mysqli_query($connect, "DELETE FROM $table WHERE measureId = '$kpiId'");
			@mysqli_query($connect, "DELETE FROM measure WHERE linkedObject = '$kpiId'");
			@mysqli_query($connect, "DELETE FROM measurelinks WHERE measure_id = '$tree_id'");
		}
		@mysqli_query($connect, "DELETE FROM uc_permissions WHERE orgId = '$tree_id'");
		break;
	}
	case "perspective":
	{
		@mysqli_query($connect, "DELETE FROM perspective WHERE id = '$tree_id'");
		@mysqli_query($connect, "DELETE FROM tree WHERE id = '$tree_id'");
		$kpiId = @mysqli_query($connect, "SELECT id FROM measure WHERE linkedObject = '$tree_id'");
		while($kpiId = @mysqli_fetch_assoc($kpiId))
		{
			$kpiId = $kpiId["id"];
			$getCalendar = mysqli_query($connect, "SELECT calendarType FROM measure WHERE id = '$kpiId'");
			$getCalendar = mysqli_fetch_assoc($getCalendar);
			$getCalendar = $getCalendar["calendarType"];
			$table = "measuremonths";
			switch($getCalendar)
			{
				case 'Daily':
				{
					$table = 'measuredays';
					break;	
				}
				case 'Weekly':
				{
					$table = 'measureweeks';
					break;	
				}
				case 'Monthly':
				{
					$table = 'measuremonths';
					break;	
				}
				case 'Quarterly':
				{
					$table = 'measurequarters';
					break;	
				}
				case 'Bi-Annually':
				{
					$table = 'measurehalfyear';
					break;	
				}
				case 'Yearly':
				{
					$table = 'measureyears';
					break;	
				}
			}
			@mysqli_query($connect, "DELETE FROM $table WHERE measureId = '$kpiId'");
			@mysqli_query($connect, "DELETE FROM measure WHERE linkedObject = '$kpiId'");
			@mysqli_query($connect, "DELETE FROM measurelinks WHERE measure_id = '$tree_id'");
		}
		
		@mysqli_query($connect, "DELETE FROM objective WHERE linkedObject = '$tree_id'");
		break;
	}
	case "objective":
	{
		@mysqli_query($connect, "DELETE FROM objective WHERE id = '$tree_id'");
		@mysqli_query($connect, "DELETE FROM tree WHERE id = '$tree_id'");
		$kpiId = @mysqli_query($connect, "SELECT id FROM measure WHERE linkedObject = '$tree_id'");
		while($kpiId = @mysqli_fetch_assoc($kpiId))
		{
			$kpiId = $kpiId["id"];
			$getCalendar = mysqli_query($connect, "SELECT calendarType FROM measure WHERE id = '$kpiId'");
			$getCalendar = mysqli_fetch_assoc($getCalendar);
			$getCalendar = $getCalendar["calendarType"];
			$table = "measuremonths";
			switch($getCalendar)
			{
				case 'Daily':
				{
					$table = 'measuredays';
					break;	
				}
				case 'Weekly':
				{
					$table = 'measureweeks';
					break;	
				}
				case 'Monthly':
				{
					$table = 'measuremonths';
					break;	
				}
				case 'Quarterly':
				{
					$table = 'measurequarters';
					break;	
				}
				case 'Bi-Annually':
				{
					$table = 'measurehalfyear';
					break;	
				}
				case 'Yearly':
				{
					$table = 'measureyears';
					break;	
				}
			}
			@mysqli_query($connect, "DELETE FROM $table WHERE measureId = '$kpiId'");
			@mysqli_query($connect, "DELETE FROM measure WHERE linkedObject = '$kpiId'");
			@mysqli_query($connect, "DELETE FROM measurelinks WHERE measure_id = '$tree_id'");
		}
		break;
	}
	case "measure":
	{
		@$linkedQuery = @mysqli_query($connect, "SELECT linked FROM tree WHERE id = '$tree_id' AND linked = 'yes'");
		$linkedResult = @mysqli_fetch_assoc($linkedQuery);
		$linked = $linkedResult["linked"];
		if($linked == 'yes')
		{
			@mysqli_query($connect, "DELETE FROM measurelinks WHERE measure_id = '$tree_id'");
			@mysqli_query($connect, "DELETE FROM tree WHERE id = '$tree_id' AND linked = 'yes'");
		}
		else
		{
			@mysqli_query($connect, "DELETE FROM measure WHERE id = '$tree_id'");
			@mysqli_query($connect, "DELETE FROM measuremonths WHERE measureId = '$tree_id'");
			@mysqli_query($connect, "DELETE FROM tree WHERE id = '$tree_id'");
			@$kpiId = @mysqli_query($connect, "SELECT id FROM measure WHERE linkedObject = '$tree_id'");
			$kpiId = @mysqli_fetch_assoc($kpiId);
			$kpiId = $kpiId["id"];
			@mysqli_query($connect, "DELETE FROM measure WHERE linkedObject = '$tree_id'");
			$getCalendar = mysqli_query($connect, "SELECT calendarType FROM measure WHERE id = '$tree_id'");
			$getCalendar = mysqli_fetch_assoc($getCalendar);
			$getCalendar = $getCalendar["calendarType"];
			$table = "measuremonths";
			switch($getCalendar)
			{
				case 'Daily':
				{
					$table = 'measuredays';
					break;	
				}
				case 'Weekly':
				{
					$table = 'measureweeks';
					break;	
				}
				case 'Monthly':
				{
					$table = 'measuremonths';
					break;	
				}
				case 'Quarterly':
				{
					$table = 'measurequarters';
					break;	
				}
				case 'Bi-Annually':
				{
					$table = 'measurehalfyear';
					break;	
				}
				case 'Yearly':
				{
					$table = 'measureyears';
					break;	
				}
			}
			@mysqli_query($connect, "DELETE FROM $table WHERE measureId = '$tree_id'");
			@mysqli_query($connect, "DELETE FROM measurelinks WHERE measure_id = '$tree_id'");
		}
		break;
	}
}
//echo $tree_id;
?>