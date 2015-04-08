<?php
require_once("db.inc.php");

define("NIVEAU_PERMISSION_BASE", 0);
define("NIVEAU_PERMISSION_BUREAU", 100);
define("NIVEAU_PERMISSION_SECRETAIRE", 500);
define("NIVEAU_PERMISSION_PRESIDENT", 700);
define("NIVEAU_PERMISSION_SUPER_UTILISATEUR", 1000);
define("NIVEAU_PERMISSION_INFINI", 10000000);

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
			$result = mysqli_query($dbh, $sql);
			if(!$result)
				throw new Exception("Failed to create admin user: ". mysql_error() );
		}
}

function checkPasswords($password)
{
	$users = listUsers();
	foreach($users as $login => $data)
	{
		if(authenticateBase($login, $password))
			echo $login." a le mot de passe '". $password."'<br/>";
		else
			echo "Checked ".$login."<br/>";
	}
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

function addCredentials($login,$pwd)
{
	global $dbh;
	$_SESSION['login'] = $login;
	$_SESSION['pass'] = $pwd;
	
}

function removeCredentials()
{
	unset($_SESSION['login']);
	unset($_SESSION['permission']);
	unset($_SESSION['pass']);
	unset($_SESSION['all_units']);
	unset($_SESSION['filter_id_session']);
	unset($_SESSION['filter_section']);
	unset($_SESSION['all_sessions']);
	unset($_SESSION['current_session']);
	unset($_SESSION["config"]);
	unset($_SESSION["all_users"]);
	unset($_SESSION["rows_id"]);
	unset($_SESSION["lose_secretary_status"]);
	unset($_SESSION["permission_mask"]);
}

function authenticateBase($login,$pwd)
{
	$realPassHash = getPassHash($login);
	if ($realPassHash != NULL)
		if (crypt($pwd, $realPassHash) == $realPassHash)
			return true;
	return false;
}

function authenticate()
{
	
	if (isset($_SESSION['login']) and isset($_SESSION['pass']))
	{
		$login  = $_SESSION['login'];
		$pwd = $_SESSION['pass'];
		$result = authenticateBase($login,$pwd);
		if(!$result) return false;
		if(!isset($_SESSION['permission']))
		{
			global $dbh;
			$sql = "SELECT * FROM ".users_db." WHERE login='".mysqli_real_escape_string($dbh, $login)."';";
			$result=mysqli_query($dbh, $sql);
			if ($row = mysqli_fetch_object($result))
			{
				$_SESSION['permission'] = $row->permissions;
				if($row->sections == "" && $row->permissions < NIVEAU_PERMISSION_SUPER_UTILISATEUR )
					return false;
				$last  = $row->last_section_selected;
				$all = explode(";",$row->sections);
				if( array_search($last,$all) === false)
					$last = $all[0];
				$_SESSION['filter_section'] = $last;
				if($row->permissions < NIVEAU_PERMISSION_SUPER_UTILISATEUR)
					$_SESSION["permission_mask"] = 0;
			}
			else
				return false;
		}
		return true;
	}
	return false;
};

?>