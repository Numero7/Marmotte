<?php

function unitsList()
{
	if(!isset($_SESSION['all_units']))
	{
		$units = array();
		$sql = "SELECT * FROM ".units_db." ORDER BY nickname ASC;";
		if($result=mysql_query($sql))
			while ($row = mysql_fetch_object($result))
			$units[$row->code] = $row;

		$maxsize = 0;
		foreach($units as $unit)
			$maxsize = max($maxsize, strlen($unit->nickname));
		foreach($units as $unit)
		{
			$l = strlen($unit->nickname);
			$unit->prettyname = str_replace(" ","&nbsp;", $unit->nickname);
			$unit->prettyname .= str_pad("", $maxsize +10 - $l , " ")."- ".$unit->code;
		}

		$_SESSION['all_units'] = $units;
	}
	return $_SESSION['all_units'];
}

function unitExists($code)
{
	return array_key_exists($code, unitsList());
}

function createUnitIfNeeded($code)
{
	if(!unitExists($code))
		addUnit($code,$code,$code,"");
}

function updateUnitData($unite, $data)
{
	global $fieldsUnitsDB;
	if(unitExists($unite))
	{
		$sql = "";
		foreach($data as $field => $value)
			if(isset($fieldsUnitsDB[$field]) && $value != "")
			$sql .= " $field='$value' ";
		if($sql != "")
		{
				
			$sql = "UPDATE FROM ".units_db." SET ".$sql;
			$sql .=  " WHERE code='$unite';";
			mysql_query($sql);
		}
	}
	else
	{
		$sql = "INSERT INTO ".reports_db." ($sqlfields) VALUES ($sqlvalues);";

	}
}

function updateUnitDirecteur($unite, $directeur)
{
	$sql = "UPDATE FROM ".units_db." SET directeur='$directeur' WHERE code='$unite';";
	mysql_query($sql);
}

function simpleUnitsList($short = false)
{
	$units = unitsList();
	$result = array();
	foreach($units as $unit => $row)
		$result[$unit] = $short ? $row->nickname : $row->prettyname;
	return $result;
}

function addUnit($nickname, $code, $fullname, $directeur)
{
	unset($_SESSION['all_units']);
	$sql = "DELETE FROM ".units_db." WHERE code = \"".$code."\";";
	sql_request($sql);

	$values = "\"".mysql_real_escape_string($nickname)."\",";
	$values .= "\"".mysql_real_escape_string($code)."\",";
	$values .= "\"".mysql_real_escape_string($fullname)."\",";
	$values .= "\"".mysql_real_escape_string($directeur)."\"";

	$sql = "INSERT INTO ".units_db." (nickname, code, fullname, directeur) VALUES ($values);";
	sql_request($sql);
}

function deleteUnit($code)
{
	unset($_SESSION['all_units']);
	$sql = "DELETE FROM ".units_db." WHERE code = \"".$code."\";";
	sql_request($sql);
}


/*
 * Unit can be code or nickname
*/
function fromunittocode($unitdata)
{
	$units = unitsList();
	if(key_exists($unitdata, $units))
	{
		$answer = $unitdata;
		return $unitdata;
	}
	foreach($units as $unit)
	{
		if(strcasecmp($unit->nickname,$unitdata) == 0 )
		{
			$answer = $unit->code;
			return $unit->code;
		}
	}
	addUnit($unitdata, $unitdata,$unitdata,"");
	$answer = $unitdata;
	return $unitdata;
}

?>