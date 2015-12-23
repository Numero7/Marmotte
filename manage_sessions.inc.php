<?php
require_once('manage_filters_and_sort.inc.php');
require_once('utils.inc.php');


function sessionArrays($force = false)
{
	if($force || !isset($_SESSION['all_sessions']))
	{
		$sessions = array();
		$ok = $_SESSION['filter_section'];
		$sql = "SELECT * FROM ".sessions_db." WHERE `section`='". real_escape_string($ok)."' ORDER BY date DESC;";
		$result = sql_request($sql);
		date_default_timezone_set("Europe/Paris");
		while ($row = mysqli_fetch_object($result))
			$sessions[$row->id] = $row->id;
		$_SESSION['all_sessions'] = $sessions;
	}
	return $_SESSION['all_sessions'];
}

function session_year($id_session)
{
  //$sessions = sessionArrays();
  //	$nom_session = $sessions[$id_session];
	//date_default_timezone_set('Europe/Paris');
	//	$result = date("Y", strtotime(substr($nom_session,strlen($nom_session) -4,4) ) );
	$result = substr($id_session,strlen($id_session) -4,4);
	return $result;
}

function session_lib($id_session)
{

	//date_default_timezone_set('Europe/Paris');
	//	$result = date("Y", strtotime(substr($nom_session,strlen($nom_session) -4,4) ) );
	$result = substr($id_session,0, strlen($id_session) -4);
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
	if(!isSuperuser())
	  $sql = "SELECT * FROM ".sessions_db." WHERE `section`='". real_escape_string($_SESSION['filter_section'])."' ORDER BY date DESC;";
	else
  	  $sql = "SELECT * FROM ".sessions_db." WHERE 1 ORDER BY date DESC;";

	if($result= sql_request($sql))
		while ($row = mysqli_fetch_object($result))
		{
			$finalResult[$row->id] = array( "id" => $row->id, "nom" => $row->nom, "date" => $row->date, "prettyprint" => $row->nom.' '.date("Y",strtotime($row->date)));
			//echo $row->id."<br/>";
		}
		return	$finalResult;
} ;

function deletePreRapports($id)
{
  $sql = "UPDATE ".reports_db." SET ";
  $sql .= "prerapport=\"\", prerapport2=\"\", prerapport3=\"\"";
  $sql .= ",avis1=\"\", avis2=\"\", avis3=\"\"";
  for($i = 0; $i <= 30; $i++)
    $sql .= ",Generic".$i." =\"\"";
  if(!isSuperUser())
    $sql .= " WHERE section=\"".real_escape_string($_SESSION['filter_section'])."\";";
  else
    $sql .= " WHERE 1;";
  sql_request($sql);
}

function deleteSession($id, $supprimerdossiers)
{
  if(isSuperUser())
    {
      echo "<p>Suppression des sessions d'intitule ".$id."<br/>";
		$sql = "DELETE FROM ".sessions_db." WHERE id='$id'";
		sql_request($sql);
		unset($_SESSION['all_sessions']);
		if($supprimerdossiers)
		{
      echo "<p>Suppression des dossiers de la sessions ".$id."<br/>";
			$sql = "DELETE FROM ".reports_db." WHERE id_session='$id'";
			sql_request($sql);
		}
		if(strpos($id,"Concours") !== FALSE)
		  {
      echo "<p>Suppression de toutes les fiches candidats<br/>";
		    	$sql = "DELETE FROM ".people_db." WHERE concoursid!=\"\"";
			sql_request($sql);
      echo "<p>Suppression de tous les sousjurys<br/>";
		    	$sql = "DELETE FROM ".concours_db." WHERE 1";
			sql_request($sql);
		  }
    }
  else
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

function get_all_sessions($section = "")
{
	if($section != "")
		$sql = "SELECT * FROM sessions WHERE `section`=\"".$section."\";";
	else
		$sql = "SELECT * FROM sessions WHERE 1;";
	$result = sql_request($sql);
	$sessions = array();
	while($row = mysqli_fetch_object($result))
	{
	if($section != "")
		$sessions[] = $row->id;
	else
		$sessions[$row->section][] = $row->id;
	}
	return $sessions;
}
?>