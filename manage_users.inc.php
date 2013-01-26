<?php

require_once('config.inc.php');
require_once('manage_sessions.inc.php');
require_once('generate_xml.inc.php');

function init_session()
{
	global $current_session;
	set_current_session_id(get_config("current_session"));
}

function createhtpasswd()
{
	$list = listUsers();
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

function listUsers()
{
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
	$sql = "SELECT * FROM ".users_db." WHERE login='$login';";
	$result=mysql_query($sql);
	if ($row = mysql_fetch_object($result))
	{
		return $row->passHash;
	}
	return NULL;
} ;

function changePwd($login,$old,$new1,$new2)
{
	$currLogin = getLogin();
	if ($currLogin==$login or isSuperUser())
	{
		if (authenticateBase($login,$old) or isSuperUser())
		{
			$oldPassHash = getPassHash($login);
			if ($oldPassHash != NULL)
			{
				$newPassHash = crypt($new1, $oldPassHash);
				$sql = "UPDATE ".users_db." SET passHash='$newPassHash' WHERE login='$login';";
				mysql_query($sql);
				createhtpasswd();
				return true;
			}
		}
		else
		{
			echo "<p><strong>Erreur :</strong> La saisie du mot de passe courant est incorrecte, veuillez réessayer.</p>";
			return false;
		};
	}
	else
	{
		echo "<p><strong>Erreur :</strong> Seuls les administrateurs du site peuvent modifier les mots de passes d'autres utilisateurs, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
		return false;
	}
}


function changeUserPermissions($login,$permissions)
{
	if (isSuperUser())
	{
		if ($permissions<=getUserPermissionLevel())
		{
			$sql = "UPDATE ".users_db." SET permissions=$permissions WHERE login='$login';";
			sql_request($sql);
			unset($_SESSION['all_users']);
		}
	}
	else
	{
		echo "<p><strong>Erreur :</strong> Seuls les administrateurs du site peuvent modifier les mots de passes d'autres utilisateurs, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
	}
}

function existsUser($login)
{
	$users = listUsers();
	return array_key_exists($login, $users);
}

function createUser($login,$pwd,$desc,$email, $envoiparemail)
{
	if (isSuperUser())
	{
		if(existsUser($login))
			throw new Exception("Failed to create user: le login '".$login."' est déja utilisé.");
		if($desc == "")
			throw new Exception("Failed to create user: empty description.");

		unset($_SESSION['all_users']);

		$passHash = crypt($pwd);
		$sql = "INSERT INTO ".users_db." (login,passHash,description,email) VALUES ('".mysql_real_escape_string($login)."','".mysql_real_escape_string($passHash)."','".mysql_real_escape_string($desc)."','".mysql_real_escape_string($email)."');";
		mysql_query($sql);
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
		createhtpasswd();
		return "Utilisateur ".$login." créé avec succès.";
	}
}

function deleteUser($login)
{
	if (isSuperUser())
	{
		unset($_SESSION['all_users']);
		$sql = "DELETE FROM ".users_db." WHERE login='".mysql_real_escape_string($login)."';";
		mysql_query($sql);
		createhtpasswd();
	}
}

?>