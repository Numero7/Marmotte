<?php 
require_once('config.inc.php');
require_once('manage_unites.inc.php');

global $fieldsUnitsDB;

?>
<table>
<?php 

foreach($fieldsUnitsDB as $field => $intitule)
	echo "<th>".$intitule."</th>";

echo "\n";

$units = unitsList();
foreach($units as $unit => $data)
{
	echo "<tr>";
	foreach($fieldsUnitsDB as $field => $intitule)
		echo "<td>".(isset($data->$field) ? $data->$field : "")."</td>";
	echo "</tr>";
}
?>
</table>