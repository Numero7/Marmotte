<?php

function getReportsAsCSV($filter_values, $sort_criteria = "", $sep ="," , $del="\n")
{
	global $fieldsAll;

	$specialfields = array("id");

	$result = "";

	$first = true;
	foreach($fieldsAll as $field => $value)
	{
		if(!in_array($field,$specialfields))
		{
			$result.= ($first ? "" : $sep) .'"'.$field.'"';
			$first = false;
		}
	}
	$result.=$del;

	$rows = filterSortReports(getCurrentFiltersList(), $filter_values, $sort_criteria);

	foreach($rows as $row)
	{
	$first = true;
		foreach($fieldsAll as $field => $value)
		{
			if(!in_array($field,$specialfields))
			{
				$result.= ($first ? "" : $sep).'"' .addslashes($row->$field).'"';
				$first = false;
			}
		}
		$result.=$del;
	}

	return $result;
}

?>