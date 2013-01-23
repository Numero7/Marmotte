<?php

require_once('config.inc.php');

function sessionArrays()
{
	if(!isset($_SESSION['all_sessions']))
	{
		$sessions = array();
		$sql = "SELECT * FROM ".sessions_db." ORDER BY date DESC;";
		if($result=mysql_query($sql))
			while ($row = mysql_fetch_object($result))
			$sessions[$row->id] = $row->nom." ".date("Y", strtotime($row->date));
		$sessions[-1] = "Toutes les sessions";
		$_SESSION['all_sessions'] = $sessions;
	}

	return $_SESSION['all_sessions'];
}

function current_session_id()
{
	return getFilterValue('id_session');
}

function set_current_session_id($id)
{
	$_SESSION['id_session_filter'] = $id;
}

function current_session()
{
	$sessions = sessionArrays();
	$id = current_session_id();
	return $sessions[$id];
}

function is_current_session_concours()
{
	if(current_session() == "")
		return false;
	$pref = substr(current_session(),0,4);
	$result =  ($pref == "Conc") || ($pref == "conc");
	return $result;
	
}

function showSessions()
{
	$finalResult = array();
	$sql = "SELECT * FROM ".sessions_db." ORDER BY date DESC;";
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
		$sql = "INSERT INTO ".sessions_db."(nom,date) VALUES ('".mysql_real_escape_string($name)."','".date("Y-m-d h:m:s",strtotime($date))."');";
		mysql_query($sql);
		unset($_SESSION['all_sessions']);
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
		$sql = "DELETE FROM ".sessions_db." WHERE id=$id;";
		mysql_query($sql);
		unset($_SESSION['all_sessions']);
	}
	else
	{
		throw new Exception("Vous n'avez pas les droits suffisants pour effacer une session");
	}
}

?>