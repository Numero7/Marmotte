<?php

require_once('config.inc.php');
require_once('manage_sessions.inc.php');
require_once('generate_xml.inc.php');

function init_session()
{
	set_current_session_id(get_config("current_session"));

	ini_set("session.gc_maxlifetime", 3600);
	//echo "Timeout: ". (ini_get("session.gc_maxlifetime")/60)." minutes<br/>";

}

function createhtpasswd()
{
	$list = listUsers(true);
	if($handle=fopen(".htpasswd","w"))
	{
		foreach($list as $user => $data)
			fwrite($handle,$user.":".$data->passHash."\n");
		fclose($handle);
		echo "Generated htpasswd.<br/>";
	}
	else
	{
		throw new Exception("Failed to open htpasswd file for writing");
	}

}

function getDescription($login)
{
	$sql = "SELECT * FROM ".users_db." WHERE login='$login';";
	$result=mysql_query($sql);
	if ($row = mysql_fetch_object($result))
	{
		return $row->description;
	}
	return NULL;
} ;


/* Caching users list for performance */

function listRapporteurs()
{
	global $users_not_rapporteur;

	$empty[''] = (object) array();
	$empty['']->description = "";
	$result = array_merge($empty,listUsers());

	foreach($users_not_rapporteur as $user)
		unset($result[$user]);

	return $result;
}

function listNomRapporteurs()
{
	global $users_not_rapporteur;

	$result = array();
	$result[''] = "";
	$users = listUsers();

	foreach($users as $login => $data)
		if(!in_array($login, $users_not_rapporteur))
		$result[$login] = $data->description;

	return $result;
}

function listUsers($forcenew = false)
{
	if($forcenew)
		unset($_SESSION['all_users']);

	if(!isset($_SESSION['all_users']))
	{
		$listusers = array();
		$sql = "SELECT * FROM ".users_db." ORDER BY description ASC;";
		$result=mysql_query($sql);
		if($result ==  false)
			throw new Exception("Failed to process query sql ".$sql);

		while ($row = mysql_fetch_object($result))
			$listusers[$row->login] = $row;

			
		$_SESSION['all_users'] = $listusers;
	}
	return $_SESSION['all_users'];
}

function createAdminPasswordIfNeeded()
{

	$users = listUsers(true);
	if(!isset($users['admin']))
		createUser('admin','password','admin','admin@admin.org',false,false);

	if(authenticateBase('admin','password'))
		echo "The 'admin' password is 'password', please change it right after login.";

}

function simpleListUsers()
{
	$users = listUsers();
	$result = array();
	foreach($users as $user => $row)
		$result[$row->login] = $row->description;
	return $result;
}

function getUserPermissionLevel($login = "")
{
	if ($login=="" && isset($_SESSION["login"]))
		$login = $_SESSION["login"];

	$login = strtolower($login);

	if ($login == "admin")
		return NIVEAU_PERMISSION_SUPER_UTILISATEUR;
	$users = listUsers();
	if (isset($users[$login]))
	{
		$data = $users[$login];
		return $data->permissions;
	}
	else
	{
		removeCredentials();
		throw new Exception("Unknown user");
	}
}

function genere_motdepasse($len=10)
{
	/*return openssl_random_pseudo_bytes($len);*/
	date_default_timezone_set("Europe/Paris");
	return substr(crypt(date("%l %u")),3,13);
}

function isSuperUser($login = "")
{
	if($login == "")
		$login = getLogin();
	return getUserPermissionLevel($login) >= NIVEAU_PERMISSION_SUPER_UTILISATEUR;
};

function isSecretaire($login = "")
{
	if($login == "")
		$login = getLogin();
	return getUserPermissionLevel($login) >= NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE;
};

function getLogin()
{
	if (isset($_SESSION["login"]))
		return strtolower($_SESSION["login"]);
	else
		return "";
}

function isBureauPresidencyUser($login = "")
{
	return getUserPermissionLevel($login) >= NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE;
};

function isBureauUser($login = "")
{
	return getUserPermissionLevel($login) >= NIVEAU_PERMISSION_BUREAU;
};

function isRapporteurUser($login = "")
{
	return getUserPermissionLevel($login) >= NIVEAU_PERMISSION_BASE;
};

function isSousJury($sousjury, $login = "")
{
	if($login == "" )
		$login = getLogin();
	$users = listUsers();
	if($sousjury != "" && $users[$login]->sousjury != "")
	{
		$test = strpos($users[$login]->sousjury, $sousjury);
		return ($test === 0 || $test != false);
	}
	else if($sousjury == "" && $users[$login]->sousjury == "")
		return true;
	else
		return false;
}

function isPresidentSousJury($sousjury)
{
	global $presidents_sousjurys;
	return (isset($presidents_sousjurys[$sousjury]['login']) && getLogin() == $presidents_sousjurys[$sousjury]['login']);
}


function addCredentials($login,$pwd)
{
	$_SESSION = array();
	$_SESSION['login'] = $login;
	$_SESSION['pass'] = $pwd;
} ;

function removeCredentials()
{
	$_SESSION = array();
} ;

function authenticateBase($login,$pwd)
{
	$realPassHash = getPassHash($login);
	if ($realPassHash != NULL)
	{
		if (crypt($pwd, $realPassHash) == $realPassHash)
		{
			return true;
		}
	}
	return false;
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

function authenticate()
{
	if (isset($_SESSION['login']) and isset($_SESSION['pass']))
	{
		$login  = $_SESSION['login'];
		$pwd = $_SESSION['pass'];
		return authenticateBase($login,$pwd);
	}
	return false;
} ;

function getPassHash($login)
{
	$sql = "SELECT * FROM ".users_db." WHERE login='".mysql_real_escape_string($login)."';";
	$result=mysql_query($sql);
	if ($row = mysql_fetch_object($result))
	{
		//There is no upper/lower case filter in sql requests
		if($row->login == $login)
			return $row->passHash;
	}
	return NULL;
} ;

function changePwd($login,$old,$new1,$new2, $envoiparemail)
{
	$currLogin = getLogin();
	$users = listUsers();
	if (authenticateBase($login,$old) or isSecretaire())
	{
		$oldPassHash = getPassHash($login);
		if ($oldPassHash != NULL)
		{
			$newPassHash = crypt($new1, $oldPassHash);
			$sql = "UPDATE ".users_db." SET passHash='$newPassHash' WHERE login='".mysql_real_escape_string($login)."';";
			sql_request($sql);

			try
			{
				createhtpasswd();
			}
			catch(Exception $e)
			{
				echo $e->getMessage();
			}

			if(getLogin() == $login)
				addCredentials($login,$new1);

			if($envoiparemail)
			{
				$body = "Votre mot de passe pour le site \r\n".curPageURL()."\r\n a été mis à jour:\r\n";
				$body .= "\t\t\t login: '".$login."'\r\n";
				$body .= "\t\t\t motdepasse: '".$new1."'\r\n";
				$body .= "\r\n\r\n\t Amicalement, ".get_config("secretaire").".";
				$cc = "";
				foreach($users as $user)
				{
					if($user->login == $currLogin && $currLogin != $login)
					{
						$cc = $user->email;
						break;
					}
				}
				email_handler($users[$login]->email,"Votre compte Marmotte",$body,$cc);
			}

			return true;
		}
	}
	else
		throw new Exception("La saisie du mot de passe courant est incorrecte, veuillez réessayer.");
}



function changeUserInfos($login,$permissions, $sousjury)
{
	if (isSecretaire())
	{
		$sql = "UPDATE ".users_db." SET permissions=$permissions, sousjury=\"$sousjury\" WHERE login='".mysql_real_escape_string($login)."';";
	}
	else
	{
		$sql = "UPDATE ".users_db." SET sousjury=\"$soujury\" WHERE login='$login';";
	}
	sql_request($sql);
	unset($_SESSION['all_users']);

}


function existsUser($login)
{
	$users = listUsers();
	return array_key_exists($login, $users);
}

function createUser($login,$pwd,$desc,$email, $envoiparemail = false, $check_secretary = true)
{
	$login = strtolower($login);

	if (!$check_secretary || isSecretaire())
	{
		if(existsUser($login))
			throw new Exception("Failed to create user: le login '".$login."' est déja utilisé.");
		if($desc == "")
			throw new Exception("Failed to create user: empty description.");

		unset($_SESSION['all_users']);

		$passHash = crypt($pwd);
		$sql = "INSERT INTO ".users_db." (login,passHash,description,email,tel,sousjury) VALUES ('".mysql_real_escape_string($login)."','".mysql_real_escape_string($passHash)."','".mysql_real_escape_string($desc)."','".mysql_real_escape_string($email)."','','');";

		$result = mysql_query($sql);

		if($result == false)
			throw new Exception("Failed to process sql query: <br/>\t".mysql_error()."<br/>".$sql);
		
		createhtpasswd();

		if($envoiparemail)
		{
			$body = "Marmotte est un site web destiné à faciliter la répartition, le dépôt, l'édition et la production\r\n";
			$body .= "des rapports par les sections du comité national.\r\n";
			$body .= "\r\nLe site est accessible à l'adresse \r\n\t\t\t".curPageURL()."\r\n";
			$body .= "\r\nCe site a été développé par Hugo Gimbert et Yann Ponty.\r\n";
			$body .= "\r\nL'accès au site est restreint aux membres de la section ".get_config("section_nb")." qui doivent s'authentifier pour y accéder et déposer, éditer ou consulter des rapports.\r\n";
			$body .= "\r\nUn compte Marmotte vient d'être créé pour vous:\r\n\r\n";
			$body .= "\t\t\t login: '".$login."'\r\n";
			$body .= "\t\t\t motdepasse: '".$pwd."'\r\n";
			$body .= "\r\nLors de votre première connexion vous pourrez changer votre mot de passe.\r\n";
			$body .= "\r\n\r\n\t Amicalement, ".get_config("secretaire").".";
			email_handler($email,"Votre compte Marmotte",$body);
		}
		
		return "Utilisateur ".$login." créé avec succès.";
	}
}

function deleteUser($login)
{
	if (isSecretaire())
	{
		unset($_SESSION['all_users']);
		$sql = "DELETE FROM ".users_db." WHERE login='".mysql_real_escape_string($login)."';";
		mysql_query($sql);
		createhtpasswd();
	}
}

?>