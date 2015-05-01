<?php
require_once('config.inc.php');
require_once('manage_filters_and_sort.inc.php');
require_once('utils.inc.php');

function init_filter_session()
{
	require_once("config_tools.inc.php");
	
	if(!isset($_SESSION['filter_id_session']))
	{
		$ok = $_SESSION['filter_section'];
		$id = get_config("current_session");
		$sql = "SELECT * FROM ".sessions_db." WHERE `id`='".$id."' AND `section`='". real_escape_string($ok)."' ORDER BY date DESC;";
		$result = sql_request($sql);
		if(mysqli_num_rows($result) == 0)
		{
			$sql = "SELECT * FROM ".sessions_db." WHERE `section`='". real_escape_string($ok)."' ORDER BY date DESC;";
			$result = sql_request($sql);
			if(mysqli_num_rows($result) == 0)
			{
				$sql = "SELECT * FROM ".sessions_db." WHERE `section`='0' ORDER BY date DESC;";
				$result = sql_request($sql);
				if(mysqli_num_rows($result) == 0)
				{
					createSession("Printemps", "2015", 0);
					$result = sql_request($sql);
				}
				if(	$row = mysqli_fetch_object($result))
				{
					createSession($row->nom, date('Y', strtotime($row->date)), $ok);
					$sql = "SELECT * FROM ".sessions_db." WHERE `section`='". real_escape_string($ok)."' ORDER BY date DESC;";
					$result = sql_request($sql);
					$row = mysqli_fetch_object($result);
					set_config("current_session", $row->id);
					$id = get_config("current_session");
				}
				else
					rr();
			}
			$id = $row->id;
		}
		$_SESSION['filter_id_session'] = $id;
	}

}

function sessionArrays($force = false)
{
	global $dbh;
	if($force || !isset($_SESSION['all_sessions']))
	{
		$sessions = array();
		$ok = $_SESSION['filter_section'];
		$sql = "SELECT * FROM ".sessions_db." WHERE `section`='". real_escape_string($ok)."' ORDER BY date DESC;";
		$result = sql_request($sql);
		date_default_timezone_set("Europe/Paris");
		while ($row = mysqli_fetch_object($result))
			$sessions[$row->id] = $row->id;
		$sessions[-1] = "Toutes les sessions";
		$_SESSION['all_sessions'] = $sessions;
	}
	return $_SESSION['all_sessions'];
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
	if($result= sql_request($sql))
		while ($row = mysqli_fetch_object($result))
		{
			$finalResult[$row->id] = array( "id" => $row->id, "nom" => $row->nom, "date" => $row->date, "prettyprint" => $row->nom.' '.date("Y",strtotime($row->date)));
			echo $row->id."<br/>";
		}
		return	$finalResult;
} ;

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
		throw new Exception("Vous n'avez pas les droits suffisants pour effacer une session");
}

?>