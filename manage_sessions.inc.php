<?php

require_once('config.inc.php');
require_once('manage_filters_and_sort.inc.php');
require_once('utils.inc.php');


function sessionArrays($force = false)
{
	global $dbh;
	if($force || !isset($_SESSION['all_sessions']))
	{
		$sessions = array();
		$ok = $_SESSION['filter_section'];
		$sql = "SELECT * FROM ".sessions_db." WHERE `section`='". real_escape_string($ok)."' ORDER BY date DESC;";
		$result = sql_request($sql);
		if($result == false)
			throw new Exception("Failed to process sql query: <br/>\t".mysqli_error($dbh)."<br/>".$sql);		
		date_default_timezone_set("Europe/Paris");
		while ($row = mysqli_fetch_object($result))
			$sessions[$row->id] = $row->id;
		$sessions[-1] = "Toutes les sessions";
		$_SESSION['all_sessions'] = $sessions;
	}

	return $_SESSION['all_sessions'];
}

function sessionShortArray()
{
		global $dbh;
		$sessions = array();
		$sql = "SELECT * FROM ".sessions_db." ORDER BY date DESC;";
		$result = sql_request($sql);
		date_default_timezone_set("Europe/Paris");
		while ($row = mysqli_fetch_object($result))
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
		$sessions = sessionArrays(true);
		return current_session() != "Hors Session";
}

function get_session($id)
{
	$sessions = sessionArrays();
	$id = current_session_id();
	if(isset($sessions[$id]))
		return $sessions[$id];
	else
		return "Unknown session";
}

function current_session()
{
	$sessions = sessionArrays();
	$id = current_session_id();
	if(isset($sessions[$id]))
		return $sessions[$id];
	else
		return "Hors Session";
}

function is_current_session_concours()
{
	if(current_session() == "")
		return false;
	$pref = substr(current_session(),0,4);
	return  ($pref == "Conc") || ($pref == "conc");
}

function is_current_session_delegation()
{
	if(current_session() == "")
		return false;
	$pref = substr(current_session(),0,4);
	return  ($pref == "Dele") || ($pref == "dele");
}

function showSessions()
{
	$finalResult = array();
	date_default_timezone_set('Europe/Paris');
	$sql = "SELECT * FROM ".sessions_db." WHERE `section`='". real_escape_string($_SESSION['filter_section'])."' ORDER BY date DESC;";
	echo $sql."<br/>";
	if($result= sql_request($sql))
		while ($row = mysqli_fetch_object($result))
{
			$finalResult[$row->id] = array( "id" => $row->id, "nom" => $row->nom, "date" => $row->date, "prettyprint" => $row->nom.' '.date("Y",strtotime($row->date)));
echo $row->id."<br/>";
}
	return	$finalResult;
} ;



function createSession($name,$annee, $section ="")
{
	if($section == "") $section = $_SESSION['filter_section'];
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
			case "PES":
				$date = "01/05/".$annee; break;
			default:
				$date = "01/07/".$annee; break;
		}
		$sql = "INSERT INTO ".sessions_db."(id,section,nom,date) VALUES ('".real_escape_string($name.$annee)."','".$section."','".real_escape_string($name)."','".date("Y-m-d h:m:s",strtotime($date))."');";
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
		$sql = "DELETE FROM ".sessions_db." WHERE id='$id' AND section='".$_SESSION['filter_section']."';";
		sql_request($sql);
		unset($_SESSION['all_sessions']);
		
		if($supprimerdossiers)
		{
			$sql = "DELETE FROM ".reports_db." WHERE id_session='$id' AND section='".$_SESSION['filter_section']."';";
			sql_request($sql);
		}
	}
	else
	{
		throw new Exception("Vous n'avez pas les droits suffisants pour effacer une session");
	}
}

?>