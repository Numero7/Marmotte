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
			$unit->prettyname .= str_pad("", $maxsize +10 - $l , " ")."(".$unit->code.")";
			$unit->prettyname = str_replace(" ","&nbsp;", $unit->nickname);
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

function simpleUnitsList()
{
	$units = unitsList();
	$result = array();
	foreach($units as $unit => $row)
		$result[$unit] = $row->nickname;
	return $result;
}

function addUnit($nickname, $code, $fullname, $directeur)
{
	unset($_SESSION['all_units']);
	$sql = "DELETE FROM ".units_db." WHERE code = \"".$code."\";";
	mysql_query($sql);

	$values = "\"".mysql_real_escape_string($nickname)."\",";
	$values .= "\"".mysql_real_escape_string($code)."\",";
	$values .= "\"".mysql_real_escape_string($fullname)."\",";
	$values .= "\"".mysql_real_escape_string($directeur)."\"";
		
	$sql = "INSERT INTO ".units_db." (nickname, code, fullname, directeur) VALUES ($values);";
	return mysql_query($sql);

}
?>