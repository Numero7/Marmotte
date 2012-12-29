<?php

function unitsList()
{
	$units = array();
	$sql = "SELECT * FROM units ORDER BY nickname ASC;";
	if($result=mysql_query($sql))
	{
		while ($row = mysql_fetch_object($result))
		{
			$units[$row->code] = $row;
		}
	}
	return $units;
}

function prettyUnitsList()
{
	$units = array();
	$sql = "SELECT * FROM units ORDER BY nickname ASC;";
	if($result=mysql_query($sql))
	{
		while ($row = mysql_fetch_object($result))
		{
			$units[$row->code] = $row;
		}
	}
	$maxsize = 0;
	foreach($units as $unit)
		$maxsize = max($maxsize, strlen($unit->nickname));
	foreach($units as $unit)
	{
		$l = strlen($unit->nickname);
		$unit->nickname .= str_pad("", $maxsize +10 - $l , " ")."(".$unit->code.")";
		$unit->nickname = str_replace(" ","&nbsp;", $unit->nickname);
	}
	
	return $units;
}

function ajout_unite($nickname, $code, $fullname, $directeur)
{
	$sql = "DELETE FROM units WHERE code = \"".$code."\";";
	mysql_query($sql);
	
	$values = "\"".mysql_real_escape_string($nickname)."\",";
	$values .= "\"".mysql_real_escape_string($code)."\",";
	$values .= "\"".mysql_real_escape_string($fullname)."\",";
	$values .= "\"".mysql_real_escape_string($directeur)."\"";
			
	$sql = "INSERT INTO units (nickname, code, fullname, directeur) VALUES ($values);";
	return mysql_query($sql);
	
}
?>