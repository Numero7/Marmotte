<?php

require_once('config.inc.php');
require_once('manage_filters_and_sort.inc.php');

function sessionArrays($force = false)
{
	if($force || !isset($_SESSION['all_sessions']))
	{
		$sessions = array();
		$sql = "SELECT * FROM ".sessions_db.";";
		$result = sql_request($sql);
		date_default_timezone_set("Europe/Paris");
		while ($row = mysql_fetch_object($result))
			$sessions[$row->id] = $row->id;
		$sessions[-1] = "Toutes les sessions";
		$_SESSION['all_sessions'] = $sessions;
	}

	return $_SESSION['all_sessions'];
}

function sessionShortArray()
{
		$sessions = array();
		$sql = "SELECT * FROM ".sessions_db." ORDER BY date DESC;";
		$result = sql_request($sql);
		date_default_timezone_set("Europe/Paris");
		while ($row = mysql_fetch_object($result))
			$sessions[$row->id] = $id;
		$_SESSION['all_sessions'] = $sessions;
		
		return $sessions;
}

function session_year($id_session)
{
	$sessions = sessionArrays();
	$nom_session = $sessions[$id_session];
	date_default_timezone_set('Europe/Paris');
	$result = date("Y", strtotime(substr($nom_session,strlen($nom_session) -4,4) ) );	
	return $result;
}

function current_session_id()
{
	return getFilterValue('id_session');
}

function set_current_session_id($id)
{
	$_SESSION['filter_id_session'] = $id;
}

function check_current_session_exists()
{
	try
	{
	$sessions = sessionArrays(true);
	current_session();
	return true;
	}catch(Exception $e)
	{
		return false;
	}
	
}

function current_session()
{
	$sessions = sessionArrays();
	$id = current_session_id();
	if(isset($sessions[$id]))
		return $sessions[$id];
	else
		throw new Exception("Pas de session avec l'id " . $id."\nVeuillez éditer le fichier de config ou ajouter une session avec l'id 2.");
}

function is_current_session_concours()
{
	if(current_session() == "")
		return false;
	$pref = substr(current_session(),0,4);
	return  ($pref == "Conc") || ($pref == "conc");
}

function showSessions()
{
	$finalResult = array();
	date_default_timezone_set('Europe/Paris');
	
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



function createSession($name,$annee)
{
	if (isSecretaire())
	{
		date_default_timezone_set('Europe/Paris');
		
		switch($name)
		{
			case "Concours":
				$date = "01/01/".$annee; break;
			case "Delegations":
				$date = "01/04/".$annee; break;
			case "Printemps":
				$date = "01/03/".$annee; break;
			case "Automne":
				$date = "01/10/".$annee; break;
			default:
				throw new Exception("Unknown session name: $name");
		}
		echo $date."<br>";
		echo strtotime($date)."<br>";
		echo date("Y-m-d h:m:s",strtotime($date));
		$sql = "INSERT INTO ".sessions_db."(id,nom,date) VALUES ('".mysql_real_escape_string($name).mysql_real_escape_string($annee)."','".mysql_real_escape_string($name)."','".date("Y-m-d h:m:s",strtotime($date))."');";
		sql_request($sql);
		sessionArrays(true);
		return true;
	}
	else
	{
		throw new Exception("Vous n'avez pas les droits suffisants pour creer une session");
	}
}

function deleteSession($id, $supprimerdossiers)
{
	if (isSecretaire())
	{
		$sql = "DELETE FROM ".sessions_db." WHERE id='$id';";
		sql_request($sql);
		unset($_SESSION['all_sessions']);
		
		if($supprimerdossiers)
		{
			$sql = "DELETE FROM ".reports_db." WHERE id_session='$id';";
			sql_request($sql);
		}
	}
	else
	{
		throw new Exception("Vous n'avez pas les droits suffisants pour effacer une session");
	}
}

?>