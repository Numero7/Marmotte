<?php


function compileObjectsAsCSV($fields, $rows,$sep =";" , $enc='"', $del="\n")
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
				$result.= ($first ? "" : $sep).$enc.(isset($row->$field) ? str_replace('"', '#', $row->$field) :"").$enc;
				$first = false;
		}
		$result.=$del;
	}
	return $result;
}

function compileReportsAsCSV($rows)
{
	global $empty_report;
	global $mandatory_export_fields;
	
	if(count($rows) < 1)
		throw new Exception("Nothing to export");
	
	$type = $rows[0]->type;
	foreach($rows as $report)
		if($report->type != $type && !isSecretaire())
			throw new Exception("Cannot export different type of reports as csv, please filter one report type exclusively");
	
	$activefields = array_unique(array_merge($mandatory_export_fields, get_editable_fields($rows[0])));
	
	return compileObjectsAsCSV($activefields, $rows);
}

function compileUnitsAsCSV($rows)
{
	global $fieldsUnitsDB;
	$fields = array();
	foreach($fieldsUnitsDB as $field => $value)
		$fields[] = $field;
	
	return compileObjectsAsCSV($fields, $rows);
}


?>