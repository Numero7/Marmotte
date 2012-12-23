<?php

function sessionArrays()
{
	$sessions = array();
	$sql = "SELECT * FROM sessions ORDER BY id ASC;";
	if($result=mysql_query($sql))
	{
		while ($row = mysql_fetch_object($result))
		{
			$sessions[$row->id] = $row->nom." ".date("Y", strtotime($row->date));
		}
	}
	return $sessions;
}



function createSession($name,$date)
{
	if (isSuperUser())
	{
		echo $date."<br>";
		echo strtotime($date)."<br>";
		echo date("Y-m-d h:m:s",strtotime($date));
		$sql = "INSERT INTO sessions(nom,date) VALUES ('".mysql_real_escape_string($name)."','".date("Y-m-d h:m:s",strtotime($date))."');";
		mysql_query($sql);
		return true;
	}
}

function deleteSession($id)
{
	if (isSuperUser())
	{
		$sql = "DELETE FROM sessions WHERE id=$id;";
		mysql_query($sql);
	}
}

?>