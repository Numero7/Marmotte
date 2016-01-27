<?php
require_once("db.inc.php");
require_once('config_tools.inc.php');

define("NIVEAU_PERMISSION_BASE", 0);
define("NIVEAU_PERMISSION_BUREAU", 100);
define("NIVEAU_PERMISSION_ACN", 400);
define("NIVEAU_PERMISSION_SECRETAIRE", 500);
define("NIVEAU_PERMISSION_PRESIDENT", 700);
define("NIVEAU_PERMISSION_SUPER_UTILISATEUR", 1000);
define("NIVEAU_PERMISSION_INFINI", 10000000);

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
				if($row = mysqli_fetch_object($result))
				{
					createSession($row->nom, date('Y', strtotime($row->date)), $ok);
					$sql = "SELECT * FROM ".sessions_db." WHERE `section`='". real_escape_string($ok)."' ORDER BY date DESC;";
					$result = sql_request($sql);
					$row = mysqli_fetch_object($result);
					set_config("current_session", $row->id);
					$id = get_config("current_session");
				}
			}
			$row = mysqli_fetch_object($result);
			$result = sql_request($sql);
			$id = $row->id;
		}
		$_SESSION['filter_id_session'] = $id;
	}
}

function get_user_object($login)
{
	$sql = "SELECT * FROM `".users_db."`  WHERE `login`=\"".real_escape_string($login)."\";";
	return sql_request($sql);
}

function createAdminPasswordIfNeeded()
{
	global $dbh;
	$result = get_user_object("admin");
	if( mysqli_num_rows($result) == 0)
	{
		$sql = "INSERT INTO `".mysqli_real_escape_string($dbh, users_db);
		$sql .="`(`login`, `sections`, `last_section_selected`, `passHash`, `description`, `permissions`, `email`, `tel`) ";
		$sql .= "VALUES ('admin','0','0','".crypt("password")."','admin',1000,'','');";
		sql_request($sql);
	}
}

function checkPasswords($password)
{
	$users = listUsers();
	foreach($users as $login => $data)
		if(authenticateBase($login, $password))
			echo $login." a le mot de passe '". $password."'<br/>";
}


function getPassHash($login)
{
	global $dbh;
	$result = get_user_object($login);
	if ($row = mysqli_fetch_object($result))
	{
		//There is no upper/lower case filter in sql requests
		if($row->login == $login)
			return $row->passHash;
	}
	return NULL;
}

function getSections($login)
{
	global $dbh;
	$result = get_user_object($login);
	if ($row = mysqli_fetch_object($result))
	{
		$sections = explode(";", $row->sections);
		if($row->section_code != "")
			$sections[] = $row->section_code;
		if($row->CID_code != "")
			$sections[] = $row->CID_code;
		return $sections;
	}
	else
		throw new Exception("Failed to query the list of all sections for user '" + $login+"'");
}

function addCredentials($login,$pwd,$janus = false)
{
	global $dbh;
	$_SESSION['login'] = $login;
	$_SESSION['pass'] = $pwd;
	$_SESSION['janus'] = $janus;
}

function removeCredentials()
{
	$_SESSION = array();
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
		);
	}
		
	if(session_id() != "")
		session_destroy();
}

function authenticateBase($login,$pwd)
{

	$realPassHash = getPassHash($login);
	if ($realPassHash != NULL)
	  return (crypt($pwd, $realPassHash) == $realPassHash);
	return false;
}

function roleToPermission($role)
{
	switch($role)
	{
		case "ADM": return NIVEAU_PERMISSION_SUPER_UTILISATEUR;
		case "ACN": return NIVEAU_PERMISSION_ACN;
		case "PRE": return NIVEAU_PERMISSION_PRESIDENT;
		case "SSC": return NIVEAU_PERMISSION_SECRETAIRE;
		case "BUR": return NIVEAU_PERMISSION_BUREAU;
		case "INV": return NIVEAU_PERMISSION_BUREAU;
		default: return NIVEAU_PERMISSION_BASE;
	}
}

function update_permissions($login, $section, $user = NULL)
{
	if($user == NULL)
	{
		$result  = get_user_object($login);
		$user = mysqli_fetch_object($result);
		if(!$user) throw new Exception("Unknown user");
	}//veuillez créer une session
	$row = $user;
	$last = $section;
	if ($last == $row->section_code)
		$_SESSION['permission'] = roleToPermission($row->section_role_code);
	else if ($last == $row->CID_code)
		$_SESSION['permission'] = roleToPermission($row->CID_role_code);
	else
		$_SESSION['permission'] = $row->permissions;

	$sections = explode(";", $row->sections);
	if(in_array($last,$sections) && $_SESSION['permission'] < $row->permissions)
	  $_SESSION['permission'] = $row->permissions;

	//	if($section == "1" && $_SESSION['permission'] ==  NIVEAU_PERMISSION_BUREAU)
	//  $_SESSION['permission'] =  NIVEAU_PERMISSION_BASE;

	if ($login == "admin" || $row->permissions == NIVEAU_PERMISSION_SUPER_UTILISATEUR)
		$_SESSION["permission_mask"] = NIVEAU_PERMISSION_SUPER_UTILISATEUR;
	//	else if($row->permissions == NIVEAU_PERMISSION_ACN)
	//	$_SESSION["permission_mask"] = NIVEAU_PERMISSION_ACN;
	else
		$_SESSION["permission_mask"] = NIVEAU_PERMISSION_BASE;
}

function get_last_user_section($user)
{
	$row = $user;
	$last  = $row->last_section_selected;
	$sections1 = explode(";", $row->sections);
	if($row->section_code != "")
		$sections1[] = $row->section_code;
	if($row->CID_code != "")
		$sections1[] = $row->CID_code;

	$sections = array();
	foreach ($sections1 as $section)
		if(is_numeric($section))
		$sections[] = $section;

	if(count($sections)  === 0)
	{
		if($row->permissions < NIVEAU_PERMISSION_SUPER_UTILISATEUR)
			throw new Exception("No section");
		else
			$sections[] = 0;
	}

	if( ($row->permissions < NIVEAU_PERMISSION_SUPER_UTILISATEUR) && array_search($last,$sections) === false)
		$last = $sections[0];
	$_SESSION['filter_section'] = $last;
	return $last;
}

function is_authenticated_with_JANUS()
{
	return (isset($_SESSION['janus']) && $_SESSION['janus']);
}

function authenticate()
{
	if (isset($_SESSION['login']) and isset($_SESSION['pass']))
	{
		$login  = $_SESSION['login'];
		$pwd = $_SESSION['pass'];
		
		//		if($login!="admin" && $login!="hugo.gimbert@labri.fr")
		//return false;

		if( !isset($_SESSION['REMOTE_USER']) || $_SESSION['REMOTE_USER']=='')
		{
			$result = authenticateBase($login,$pwd);
			if(!$result)
				return false;
		}
		//Whether we should update permissions
		if(!isset($_SESSION['permission']))
		{
			global $dbh;
			$sql = "SELECT * FROM ".users_db." WHERE login='".mysqli_real_escape_string($dbh, $login)."';";
			$result = mysqli_query($dbh, $sql);
			if ($row = mysqli_fetch_object($result))
			{
				$last  = $row->last_section_selected;
				try
				{
					$last = get_last_user_section($row);
				}
				catch(Exception $e)
				{
					removeCredentials();
					throw new Exception("Votre authentification est correcte mais le login '".$login."' n'est actuellement associé à aucune section ou CID dans Marmotte.");
				}
				update_permissions($login, $last, $row);
				if( (get_config("maintenance", "off", false, "0") == "on") && ($row->permissions < NIVEAU_PERMISSION_SUPER_UTILISATEUR) )
				{
					removeCredentials();
					throw new Exception("Le site est provisoirement fermé pour maintenance");
				}
				return true;
			}
			else
				return false;
		}
		return true;
	}
	return false;
};

function createSession($name,$annee, $section ="")
{
	if($section === "") $section = $_SESSION['filter_section'];
	//	if(!ctype_alnum($name))
	//	throw new Exception("Session names can be only alphanumeric");
	date_default_timezone_set('Europe/Paris');
	switch($name)
	{
		case "IE":
			$date = "01/01/".$annee; break;
		case "Concours":
			$date = "10/01/".$annee; break;
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
	unset($_SESSION['all_sessions']);
	return true;
}


?>