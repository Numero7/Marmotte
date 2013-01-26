<?php


function compileObjectAsCSV($fields, $rows,$sep =";" , $enc='"', $del="\n")
{
	$result = "";

	$first = true;
	foreach($fields as $field)
	{
			$result.= ($first ? "" : $sep) .$enc.$field.$enc;
			$first = false;
	}
	
	$result.=$del;

	foreach($rows as $row)
	{
		$first = true;
		foreach($fields as $field)
		{
				$result.= ($first ? "" : $sep).$enc.(isset($row->$field) ? addslashes($row->$field) :"").$enc;
				$first = false;
		}
		$result.=$del;
	}
	return $result;
}

function compileReportsAsCSV($rows)
{
	global $empty_report;
	$specialfields = array("id","id_origine","date");

	$fields = array();
	foreach($empty_report as $field => $value)
		if(!in_array($field,$specialfields))
		$fields[] = $field;

	return compileObjectAsCSV($fields, $rows);
}

function compileUnitsAsCSV($rows)
{
	global $fieldsUnitsDB;
	$fields = array();
	foreach($fieldsUnitsDB as $field => $value)
		$fields[] = $field;
	
	return compileObjectAsCSV($fields, $rows);
}

function getReportsAsCSV($filter_values, $sort_criteria = "")
{
	global $fieldsAll;


	$rows = filterSortReports(getCurrentFiltersList(), $filter_values, $sort_criteria);

	return compileReportsAsCSV($rows);
}

?>