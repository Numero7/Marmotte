<?php

require_once('config.inc.php');

function sessionArrays()
{
	$sessions = array();
	$sql = "SELECT * FROM sessions ORDER BY date DESC;";
	if($result=mysql_query($sql))
		while ($row = mysql_fetch_object($result))
			$sessions[$row->id] = $row->nom." ".date("Y", strtotime($row->date));
	$sessions[-1] = "Toutes les sessions";
	return $sessions;
}

function current_session_id()
{
	return getFilterValue('id_session');
}

function set_current_session_id()
{
	$_SESSION['id_session_filter'] = $id;
}

function current_session()
{
	$sessions = sessionArrays();
	return $sessions[current_session_id()];
}

function showSessions()
{
	$finalResult = array();
	$sql = "SELECT * FROM sessions ORDER BY date DESC;";
	if($result=mysql_query($sql))
	{
		while ($row = mysql_fetch_object($result))
		{
			$finalResult[$row->id] = array( "id" => $row->id, "nom" => $row->nom, "date" => $row->date, "prettyprint" => $row->nom.' '.date("Y",strtotime($row->date)));
		}
	}
	return	$finalResult;
} ;



function createSession($name,$date)
{
	if (isSecretaire())
	{
		echo $date."<br>";
		echo strtotime($date)."<br>";
		echo date("Y-m-d h:m:s",strtotime($date));
		$sql = "INSERT INTO sessions(nom,date) VALUES ('".mysql_real_escape_string($name)."','".date("Y-m-d h:m:s",strtotime($date))."');";
		mysql_query($sql);
		return true;
	}
	else
	{
		throw new Exception("Vous n'avez pas les droits suffisants pour creer une session");
	}
}

function deleteSession($id)
{
	if (isSecretaire())
	{
		$sql = "DELETE FROM sessions WHERE id=$id;";
		mysql_query($sql);
	}
	else
	{
		throw new Exception("Vous n'avez pas les droits suffisants pour effacer une session");
	}
}

?>