<?php
require_once("db.inc.php");


function createAdminPasswordIfNeeded()
{
	global $dbh;
		$listusers = array();
		$sql = "SELECT * FROM ". mysqli_real_escape_string($dbh, users_db)." WHERE login='admin';";
		$result=mysqli_query($dbh, $sql);
		if( mysqli_fetch_lengths($result) == 0)
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
	$sql = "SELECT * FROM ".users_db." WHERE login='".mysqli_real_escape_string($dbh, $login)."';";
	$result=mysqli_query($dbh, $sql);
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
	$sql = "SELECT * FROM ".users_db." WHERE login='$login';";
	$result=mysqli_query($dbh,$sql);
	if ($row = mysqli_fetch_object($result))
		$sections = $row->sections;
	else
		throw new Exception("Failed to query the list of all sections for user '" + $login+"'");
	return explode(";", $sections);
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
			if($result && !isset($_SESSION['permission']))
			{
				global $dbh;
				$sql = "SELECT * FROM ".users_db." WHERE login='".mysqli_real_escape_string($dbh, $login)."';";
				$result=mysqli_query($dbh, $sql);
				if ($row = mysqli_fetch_object($result))
				{
					$_SESSION['permission'] = $row->permissions;
					$_SESSION['filter_section'] = $row->last_section_selected;
				}
				else
					return false;
			}
		return $result;
	}
	return false;
} ;

?>