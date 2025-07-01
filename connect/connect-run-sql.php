<?php
@$dbType = $_POST["dbType"];
@$host = $_POST["host"];
@$user = $_POST["user"];
@$pass = $_POST["password"];
@$dbName = $_POST["dbName"];
@$port = $_POST["port"];
@$sql = $_POST["sql"];

// $dbType = 'Oracle';
// $host = 'localhost';
// $user = 'oracle';
// $pass = 'oracle';
// $dbName = '';
// $port = '1521';
// $sql = "SELECT * FROM ACCOUNTS WHERE ITEM = 'profit' AND VALUE_DATE = '11-FEB-2015'";

file_put_contents("connect.txt", "$dbType, $host, $user, $pass, $dbName, $port, $sql");

switch($dbType)
{
	case "MySQL":
	{
		$dbc = new PDO("mysql:host=$host;dbname=$dbName", $user, $pass);
		break;
	}
	case "Oracle":
	{
		$dbName = '';
		$dbc = new PDO('oci:dbname='.$dbName, $user, $pass);
		break;
	}
	case "MS SQL":
	{
		$dbc = new PDO('dblib:dbname='.$dbName, $user, $pass);
		break;
	}
	case "PG SQL":
	{
		$dbc = new PDO('pgsql:dbname='.$dbName, $user, $pass);
		break;
	}
}

$query = $dbc->prepare($sql);
$query->execute();
//first get the column names
$result = $query->fetch(PDO::FETCH_ASSOC);

//build the table headers in a single array string
////the headers also include the style for the table
/////the table will also be in a div
/////this will allow it to be responsive in case there is a lot of data
$data = "<style>
table {
    border-collapse: collapse;
    width: 100%;
}

th, td {
    text-align: left;
    padding: 8px;
}

tr:nth-child(even){background-color: #f2f2f2}
</style>
<div style='overflow-x:auto;'>
<table id='table_data' class=''><thead><tr>";

foreach($result as $cname => $cvalue){
	$data .="<th>".$cname."</th>";

}

$data .="</tr></thead>";
//done with the header for the table
//build the body
$data .="<tbody>";

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
	$data .="<tr>";
	foreach($row as $column => $value){
		$data .="<td>".$value."</td>";
	}

	$data .="</tr>";

}

$data .="</table></div> ";
//
echo json_encode($data);
//return $data;

$dbc = NULL;
?>
