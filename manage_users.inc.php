<?php

require_once('config.inc.php');
require_once('manage_sessions.inc.php');
require_once('generate_xml.inc.php');
require_once('authenticate_tools.inc.php');
require_once('manage_files.php');

function createhtpasswd()
{
	$list = listUsers(true);
	global $dossier_temp;
	global $dossier_stockage;
	foreach(array($dossier_temp,$dossier_stockage) as $dir)
	{
		create_dir_if_needed2($dir);
	if( $handle = fopen($dir.".htpasswd" , "w" ) )
	{
		foreach($list as $user => $data)
			fwrite($handle,$user.":".$data->passHash."\n");
		fclose($handle);
	}
	else
		throw new Exception("Failed to open htpasswd file for writing");
	
	$realp = realpath($dir.".htpasswd");
	if($realp == false)
		throw new Exception("Warning, security breach, htaccess could not be properly generated");

	if( $handle = fopen($dir.".htaccess" , "w" ) )
	{
		fwrite($handle,
"AuthUserFile ".$realp."\n
AuthName \"Veuillez vous identifier avec votre login et votre mot de passe Marmotte\"\n
AuthType Basic\n
Require valid-user\n"
);
		fclose($handle);
	}
	else
		throw new Exception("Failed to open htaccess file for writing");
	
	}
	echo "Regenerated access files.<br/>";
}

function belongsToSection($login, $section)
{
	$all_sections = getSections($login);
	return in_array($section, $all_sections);
};

function currentSection()
{
	return $_SESSION['filter_section'];	
}

function change_current_section($section)
{
	if(!belongsToSection(getLogin(), $section))
		throw new Exception("Cannot change current section, user ".$login." does not belong to section ".$section);

	$sql = "UPDATE ".users_db." SET last_section_selected='";
	$sql .= real_escape_string($section);
	$sql .= "'WHERE login='".real_escape_string(getLogin())."';";
	sql_request($sql);
	$_SESSION['filter_section'] = $section;
	unset($_SESSION["config"]);
	unset($_SESSION['all_units']);
	unset($_SESSION["all_users"]);
	unset($_SESSION["rows_id"]);
	$_SESSION['filter_id_session'] = get_config("current_session");
}

function get_bureau_stats()
{
	if(is_current_session_concours())
	{
	/* pour chaque niveau, pour chaque rapporteur, nombre de candidats par rapporteurs */
	$sql = "SELECT * FROM reports WHERE section=\"".currentSection()."\" AND id_session=\"".current_session()."\" AND type=\"Candidature\"";
	$stats = array();
	$fields = array("rapporteur","rapporteur2","rapporteur3");

	$result= sql_request($sql);
	while($row = mysqli_fetch_object($result))
	{
		if(!isset($stats[substr($row->concours,0,2)]))
			$stats[substr($row->concours,0,2)] = array();
		foreach($fields as $field)
		{
		if(!isset($stats[substr($row->concours,0,2)][$row->$field]))
			$stats[substr($row->concours,0,2)][$row->$field] = array();
		if(!isset($stats[substr($row->concours,0,2)][$row->$field]["1"]))
			$stats[substr($row->concours,0,2)][$row->$field][$field] = array();
		if(!isset($stats[substr($row->concours,0,2)][$row->$field][$field][$row->nom.$row->prenom]))
		{
			$stats[substr($row->concours,0,2)][$row->$field][$field][$row->nom.$row->prenom] = "";
			if(!isset($stats[substr($row->concours,0,2)][$row->$field][$field]["counter"]))
				$stats[substr($row->concours,0,2)][$row->$field][$field]["counter"] = 0;
			$stats[substr($row->concours,0,2)][$row->$field][$field]["counter"]++;
		}
		}
	}
	}
	return $stats;
	
}

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
		//$sql = "SELECT * FROM ".users_db." WHERE `section`='". real_escape_string($_SESSION['filter_section'])."' ORDER BY description ASC;";
		$sql = "SELECT * FROM ".users_db." ORDER BY description ASC;";
		$result= sql_request($sql);
		if($result ==  false)
			throw new Exception("Failed to process sql query ".$sql.": ".mysql_error());
		$section = currentSection();
		while ($row = mysqli_fetch_object($result))
		{
			$sections = explode(";", $row->sections);
			if(isSuperUser() or in_array($section,$sections) )
				$listusers[$row->login] = $row;
		}
		$_SESSION['all_users'] = $listusers;
	}
	$all_users = $_SESSION['all_users'];
	return $all_users;
}

function simpleListUsers()
{
	$users = listUsers();
	$result = array();
	foreach($users as $user => $row)
		$result[$row->login] = $row->description;
	return $result;
}

function getUserPermissionLevel($login = "", $use_mask = true )
{	
	$mask = NIVEAU_PERMISSION_INFINI;
	
	if($use_mask && isset($_SESSION["permission_mask"]))
		$mask = $_SESSION["permission_mask"];

	if ($login=="" || $login == getLogin())
	{
		$result = isset($_SESSION['permission']) ? $_SESSION['permission'] : 0;
		return min($mask, $result);
	}

		if(!isset($_SESSION["login"]))
			throw new Exception("User not logged in !");
		$login = $_SESSION["login"];
	
	$login = strtolower($login);
	if ($login == "admin")
		return NIVEAU_PERMISSION_SUPER_UTILISATEUR;

	$users = listUsers();
	if (isset($users[$login]))
	{
		$data = $users[$login];		
		return min($mask, $data->permissions);
	}
	else
	{
		removeCredentials();
		throw new Exception("Unknown user '" + $login + "'");
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
	if(isset($_SESSION["lose_secretary_status"]))
		return false;
	
	if($login == "")
		$login = getLogin();
	return getUserPermissionLevel($login) >= NIVEAU_PERMISSION_SUPER_UTILISATEUR;
};

function isSecretaire($login = "", $use_mask = true)
{
	if($login == "")
		$login = getLogin();
	return getUserPermissionLevel($login, $use_mask) >= NIVEAU_PERMISSION_SECRETAIRE;
};

function getLogin()
{
	if (isset($_SESSION["login"]))
		return strtolower($_SESSION["login"]);
	else
		return "";
}

function getSecretaire()
{
	$users = listUsers();
	foreach($users as $user)
		if($user->permissions == NIVEAU_PERMISSION_SECRETAIRE)
		return $user;
	return null;
}

function getPresident()
{
	$users = listUsers();
	foreach($users as $user)
		if($user->permissions == NIVEAU_PERMISSION_PRESIDENT)
		return $user;
	return null;
}

function isBureauUser($login = "", $use_mask = true)
{
	return getUserPermissionLevel($login, $use_mask) >= NIVEAU_PERMISSION_BUREAU;
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

function isPresidentSousJury($sousjury = "")
{
	global $tous_sous_jury;
	return 
		(isset($tous_sous_jury[$concours]))
	 &&  (isset($tous_sous_jury[$concours][$sousjury]))
	 && (getLogin() === $tous_sous_jury[$concours][$sousjury]["president"]);
}

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
			$sql = "UPDATE ".users_db." SET passHash='$newPassHash' WHERE login='".real_escape_string($login)."';";
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
				$body = "Votre mot de passe pour le site \r\n".adresse_du_site."\r\n a été mis à jour:\r\n";
				$body .= "\t\t\t login: '".$login."'\r\n";
				$body .= "\t\t\t motdepasse: '".$new1."'\r\n";
				$body .= "\r\n\r\n\t Amicalement, ".get_config("webmaster_nom").".";
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

function changeUserInfos($login,$permissions, $sections)
{
	if($permissions >= NIVEAU_PERMISSION_SUPER_UTILISATEUR)
		$sections = "";
	if(isSuperUser())
		$sql = "UPDATE `".users_db."` SET `sections`='".real_escape_string($sections)."', `permissions`='".real_escape_string($permissions)."' WHERE `login`='".real_escape_string($login)."';";
	else if (isSecretaire())
		$sql = "UPDATE `".users_db."` SET `permissions`=\"".real_escape_string($permissions)."\" WHERE `login`='".real_escape_string($login)."';";
	sql_request($sql);
	unset($_SESSION['all_users']);
}

function existsUser($login)
{
	$users = listUsers();
	return array_key_exists($login, $users);
}


function createUser($login,$pwd,$desc,$email, $sections, $permissions, $envoiparemail = false)
{
	$login = strtolower($login);

	if($login == "admin")
		$sections = "0";
		
	if (isSecretaire())
	{
		if(existsUser($login))
			throw new Exception("Failed to create user: le login '".$login."' est déja utilisé.");
		if($desc == "")
			throw new Exception("Failed to create user: empty description.");

		if(!isSuperUser())
			$sections = currentSection();
		
		unset($_SESSION['all_users']);

		$passHash = crypt($pwd);
		$sql = "INSERT INTO ".users_db." (login,sections,permissions,passHash,description,email,tel) VALUES ('";
		$sql .= real_escape_string($login)."','";
		$sql .= real_escape_string($sections)."','";
		$sql .= real_escape_string($permissions)."','";
		$sql .= real_escape_string($passHash)."','";
		$sql .= real_escape_string($desc)."','";
		$sql .= real_escape_string($email)."','');";

		$result = sql_request($sql);
		
		createhtpasswd();

		if($envoiparemail)
		{
			$body = "Marmotte est un site web destiné à faciliter la répartition, le dépôt, l'édition et la production\r\n";
			$body .= "des rapports par les sections du comité national.\r\n";
			$body .= "\r\nLe site est accessible à l'adresse \r\n\t\t\t".adresse_du_site."\r\n";
			$body .= "\r\nUn compte Marmotte vient d'être créé pour vous:\r\n\r\n";
			$body .= "\t\t\t login: '".$login."'\r\n";
			$body .= "\t\t\t motdepasse: '".$pwd."'\r\n";
			$body .= "\r\nLors de votre première connexion vous pourrez changer votre mot de passe.\r\n";
			$body .= "\r\n\r\n\t Amicalement, ".get_config("webmaster_nom").".";

			$cc = "";
			$currLogin = getLogin();
			$users = listUsers();
			foreach($users as $user)
			{
				if($user->login == $currLogin && $currLogin != $login)
				{
					$cc = $user->email;
					break;
				}
			}
			email_handler($email,"Votre compte Marmotte",$body,$cc);
		}
		
		return "Utilisateur ".$login." créé avec succès.";
	}
}

function deleteUser($login)
{
	/* Since a user can be shared by several sections,
	 * only superuser can definitively delete a user
	 */
	if (isSuperUser())
	{
		unset($_SESSION['all_users']);
		$sql = "DELETE FROM ".users_db." WHERE login='".real_escape_string($login)."';";
		sql_request($sql);
		createhtpasswd();
	}
	else if(isSecretaire())
	{
		unset($_SESSION['all_users']);
		
		$sections = getSections($login);
		$newsections ="";
		foreach($sections as $section)
			if($section != currentSection())
			$newsections .= $section.";";
		$sql = "UPDATE `".users_db."` SET `sections`=\"$newsections\" WHERE `login`=\"".real_escape_string($login)."\";";
		sql_request($sql);
		createhtpasswd();
	}
}


function affecte_sous_jurys($login, $sousjurys)
{
	$sql = "SELECT * FROM ".concours_db." WHERE section='".currentSection()."' and session='".current_session_id()."';";
	sql_request($sql);
	
	foreach($sousjurys as $concours => $sousjurys)
	{
		
	}
}

?>