<?php

require_once('config.inc.php');
require_once('manage_sessions.inc.php');
require_once('generate_xml.inc.php');
require_once('authenticate_tools.inc.php');
require_once('manage_files.php');


function belongsToSection($login, $section)
{
	$all_sections = getSections($login);
	return in_array($section, $all_sections);
};

function getSection($login)
{
	$users = listUsers();
	if (isset($users[$login]))
		return $users[$login]->section_code;
	else
		throw new Exception("Unknown user '" + $login+"'");
}

function getCID($login)
{
	$users = listUsers();
	if (isset($users[$login]))
		return $users[$login]->CID_code;
	else
		throw new Exception("Unknown user '" + $login+"'");
}

function isCurrentSectionACID()
{
	return getCID(getLogin()) == currentSection();
}

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
		$sousjurys = getSousJuryMap();

		$concours = getConcours();

		/* pour chaque niveau, pour chaque rapporteur, nombre de candidats par rapporteurs */

		$stats = array("Candidats CR"=>array(), "Candidats DR"=>array());
		$fields = array("rapporteur","rapporteur2","rapporteur3");

		$sql = "SELECT * FROM reports WHERE section=\"".currentSection()."\" AND id_session=\"".current_session();
		$sql .="\" AND type=\"Candidature\" AND id=id_origine AND statut!=\"supprime\"";
		$result= sql_request($sql);

		$iid_seen = array();

		while( $row = mysqli_fetch_object($result))
		{
			if(isset($concours[$row->concours]))
				$pref = substr($concours[$row->concours]->intitule,0,2);
			else
				$pref = $row->concours;
			$iid = $row->nom.$row->prenom;

			if($row->avis == "nonauditionne")
				continue;

			foreach($fields as $field)
			{
				if($row->$field != "" && !isset($stats[$pref][$row->$field][$field][$iid]))
				{
					$key = "Candidats ".$pref;
					if(!isset($stats[$key]["Total"][$field]["counter"]))
						$stats[$key]["Total"][$field]["counter"] = 0;
					if(!isset($stats[$key][$row->$field][$field][$iid]))
					{
						$stats[$key]["Total"][$field]["counter"]++;
						$stats[$key][$row->$field][$field][$iid] = "ok";
						if(!isset($stats[$key][$row->$field][$field]["counter"]))
							$stats[$key][$row->$field][$field]["counter"] = 0;
						$stats[$key][$row->$field][$field]["counter"]++;
					}
					//echo "add 1 to ".$iid." ".$pref." ".$row->$field." ".$field." tot ".$stats[$pref][$row->$field][$field]["counter"]."<br/>";
				}
			}

			$already_seen = isset($iid_seen[$iid]);
			$iid_seen[$iid] = true;

			if(!$already_seen && isset($sousjurys[$row->rapporteur][$row->concours]) && ($row->avis == "oral" ||  $row->avis == "nonclasse" || is_numeric($row->avis)))
			{
				$sj = $sousjurys[$row->rapporteur][$row->concours];
				$key = "Sousjury ".$sj;
				if(!isset($stats[$key]["Total"]["rapporteur"]["counter"]))
					$stats[$key]["Total"]["rapporteur"]["counter"] = 0;
				$stats[$key]["Total"]["rapporteur"]["counter"]++;
				if( !isset( $stats[$key][$row->rapporteur]["rapporteur"]["counter"] ) )
					$stats[$key][$row->rapporteur]["rapporteur"]["counter"] = 0;
				$stats[$key][$row->rapporteur]["rapporteur"]["counter"]++;
			}
		}
	}
	return $stats;
}

/* Caching users list for performance */


function permissionToRole($perm)
{
	switch($perm)
	{
		case NIVEAU_PERMISSION_SUPER_UTILISATEUR: return "ADM";
		case NIVEAU_PERMISSION_PRESIDENT: return "PRE";
		case NIVEAU_PERMISSION_SECRETAIRE: return "SSC";
		case NIVEAU_PERMISSION_BUREAU: return "BUR";
		default: return "";
	}
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
			if(isSuperUser())
			{
				$listusers[$row->login] = $row;
			}
			else if ($section == $row->section_code)
			{
				$row->permissions = roleToPermission($row->section_role_code);
				$listusers[$row->login] = $row;
			}
			else if ($section == $row->CID_code)
			{
				$row->permissions = roleToPermission($row->CID_role_code);
				$listusers[$row->login] = $row;
			}
			else
			{
				$sections = explode(";", $row->sections);
				if(in_array($section,$sections))
					$listusers[$row->login] = $row;
			}
		}
		$_SESSION['all_users'] = $listusers;
	}
	$all_users = $_SESSION['all_users'];

	return $all_users;
}
//SESSION['permi
function simpleListUsers()
{
	$users = listUsers();
	$result = array();
	foreach($users as $user => $row)
		$result[$row->login] = $row->description;
	return $result;
}

function listNomRapporteurs()
{
	$result = array();
	$result[''] = "";
	$users = listUsers();
	foreach($users as $login => $data)
		$result[$login] = $data->description;
	return $result;
}
//Vous n'avez pas le niveau de permission suffisa
function getUserPermissionLevel($login = "", $use_mask = true )
{
	$mask = NIVEAU_PERMISSION_INFINI;

	if($login == "") $login = getLogin();
	
	if ($login == "admin")
		return NIVEAU_PERMISSION_SUPER_UTILISATEUR;
	
	if($use_mask && isset($_SESSION["permission_mask"]))
		$mask = $_SESSION["permission_mask"];
	
	if ($login=="" || $login == getLogin())
	{
		$result = 0;
		if(isset($_SESSION['permission']))
			$result = $_SESSION['permission'];
		return min($mask, $result);
	}

	if(!isset($_SESSION["login"]))
		throw new Exception("User not logged in !");
	$login = $_SESSION["login"];

	$login = strtolower($login);

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
	$level = getUserPermissionLevel($login, $use_mask);
	return ($level >= NIVEAU_PERMISSION_SECRETAIRE);
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
	$level = getUserPermissionLevel($login, $use_mask);
	return ($level >= NIVEAU_PERMISSION_BUREAU);
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

function isPresidentSousJury($concours = "", $sousjury = "")
{
	global $tous_sous_jury;
	return
	(isset($tous_sous_jury[$concours]))
	&&  (isset($tous_sous_jury[$concours][$sousjury]))
	&& isset($tous_sous_jury[$concours][$sousjury]["president"])
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

function changeUserInfos($login,$permissions, $sections, $section_code = "", $section_role = "", $CID_code = "", $CID_role = "")
{
	if(isSuperUser())
	{
		if($permissions >= NIVEAU_PERMISSION_SUPER_UTILISATEUR)
			$sections = "";
		$sql = "UPDATE `".users_db."`";
		$sql .= " SET `section_code`='".real_escape_string($section_code)."'";
		$sql .= ", `section_role_code`='".real_escape_string($section_role)."'";
		$sql .= ", `CID_code`='".real_escape_string($CID_code)."'";
		$sql .= ", `CID_role_code`='".real_escape_string($CID_role)."'";
		$sql .= ", `sections`='".real_escape_string($sections)."'";
		$sql .= ", `permissions`='".real_escape_string($permissions)."'";
		$sql .= " WHERE `login`='".real_escape_string($login)."';";
	}
	else if (isSecretaire())
	{
		$role = real_escape_string(permissionToRole($permissions));
		if(currentSection() == getSection($login))
			$sql = "UPDATE `".users_db."` SET `section_role_code`=\"".$role."\" WHERE `login`='".real_escape_string($login)."';";
		else if(currentSection() == getCID($login))
			$sql = "UPDATE `".users_db."` SET `CID_role_code`=\"".$role."\" WHERE `login`='".real_escape_string($login)."';";
		else
			$sql = "UPDATE `".users_db."` SET `permissions`=\"".roleToPermission($role)."\" WHERE `login`='".real_escape_string($login)."';";
	}
	sql_request($sql);
	unset($_SESSION['all_users']);
}

function existsUser($login)
{
	$users = listUsers();
	return array_key_exists($login, $users);
}


function createUser(
		$login,$pwd,$desc,
		$email,
		$sections, $permissions,
		$section_code, $section_role_code,
		$CID_code, $CID_role_code,
		$envoiparemail = false)
{
	$login = strtolower($login);

	if($login == "admin")
		$sections = "0";

	if (isSecretaire())
	{
		if(existsUser($login))
			throw new Exception("Failed to create user: login '".$login."' already in use.");

		if($desc == "")
			throw new Exception("Failed to create user: empty description.");

		$section = currentSection();

		$result = get_user_object($login);
		if($user = mysqli_fetch_object($result) && !isSuperUser())
		{
			$sql = "UPDATE ".users_db." SET sections='".($user->sections.";".$section)."' WHERE login='".$login."';";
			sql_request($sql);
		}
		else
		{
			$section_code = "";
			$CID_code = "";
			if(!isSuperUser())
				$sections = "";
			else
			{
				if(isCurrentSectionACID())
					$CID_code = currentSection();
				else
					$section_code = currentSection();
			}

			unset($_SESSION['all_users']);
			$passHash = crypt($pwd);
			$sql = "INSERT INTO ".users_db." (login,sections,permissions,section_code,CID_code,passHash,description,email,tel) VALUES ('";
			$sql .= real_escape_string($login)."','";
			$sql .= real_escape_string($sections)."','";
			$sql .= real_escape_string($permissions)."','";
			$sql .= real_escape_string($section_code)."','";
			$sql .= real_escape_string($CID_code)."','";
			$sql .= real_escape_string($passHash)."','";
			$sql .= real_escape_string($desc)."','";
			$sql .= real_escape_string($email)."','');";

			$result = sql_request($sql);

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
		}
		unset($_SESSION['all_users']);
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
		$sql = "DELETE FROM ".users_db." WHERE login='".real_escape_string($login)."';";
		sql_request($sql);
	}
	else if(isSecretaire())
	{
		$section = currentSection();
		$res = get_user_object($login);
		if ($user = mysqli_fetch_object($res))
		{

			if($user->section_code == $section)
				$user->section_code = "";
			if($user->CID_code == $section)
				$user->CID_code = "";
			$extra_sections = explode(";", $user->sections);
			$user->sections = "";
			foreach($extra_sections as $sec)
				if(($sec != $section) && ($sec != ""))
				$user->sections .= $sec.";";
			if($user->sections == "" && $user->section_code=="" && $user->CID_code=="")
			{
				$sql = "DELETE FROM `".users_db."` WHERE `login`=\"".real_escape_string($login)."\";";
			}
			else
			{
				$sql = "UPDATE `".users_db."` SET ";
				$sql .= "`section_code`=\"".real_escape_string($user->section_code)."\", ";
				$sql .= "`CID_code`=\"".real_escape_string($user->CID_code)."\", ";
				$sql .= "`sections`=\"$user->sections\" WHERE `login`=\"".real_escape_string($login)."\";";
			}
			sql_request($sql);
			if($login == getLogin())
				removeCredentials();
		}
	}
	unset($_SESSION['all_users']);
}


function deleteAllUsers()
{
	/* Since a user can be shared by several sections,
	 * only superuser can definitively delete a user
	*/
	if (isSuperUser())
	{
		unset($_SESSION['all_users']);
		$sql = "DELETE FROM ".users_db." WHERE NOT `login`='".real_escape_string(getLogin())."';";
		sql_request($sql);
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
	}

}

function importAllUsersFromJanus()
{
	if(!isSecretaire())
		throw new Exception("Vous n'avez pas les droits sffisnats pour cette opération");

	$users = listUsers();

	$errors = "";

	dsi_connect();
	if (isSuperUser())
		$sql = "SELECT * FROM ".dsi_users_db." WHERE 1;";
	else
		$sql = "SELECT * FROM ".dsi_users_db." WHERE section_code=\"".currentSection()."\";";

	$result = dsi_sql_request($sql);
	while ($row = mysqli_fetch_object($result))
	{
		$login = $row->mailpro;
		try
		{
			if(isset($users[$login]))
				changeUserInfos(
						$login,$users[$login]->permissions,$users[$login]->sections,
						$row->section_code,$row->section_role_code,
						$row->CID_code, (isset($row->CID_role_code) ? $row->CID_role_code : "")
				);
			else
			{
				$sql = "INSERT INTO ".users_db." (login,sections,permissions,section_code,CID_code,passHash,description,email,tel) ";
				$sql .= "VALUES ('";
				$sql .= real_escape_string($login)."','','0','".$row->section_code."','".$row->CID_code."','','".real_escape_string($row->nom." ".$row->prenom)."','".$login."','');";
				sql_request($sql);
			}
		}
		catch(Exception $exc)
		{
			$errors .= $exc->getMessage()."\n<br/>";
		}
	}
	unset($_SESSION['all_users']);
	dsi_disconnect();

	if($errors != "")
		throw new Exception($errors);
}

function mergeUsers($old_login, $new_login)
{
	$old_login = real_escape_string($old_login);
	$new_login = real_escape_string($new_login);
	$fields = array("rapporteur","rapporteur2","rapporteur3");
	foreach($fields as $field)
	{
		$sql = "UPDATE ".reports_db." SET `".$field."`='".$new_login."' WHERE `".$field."`='".$old_login."'";
		if(!isSuperUser())
			$sql .= " AND `section`='".currentSection()."'";
		$sql.=";";
		sql_request($sql);
	}
	deleteUser($old_login);
	unset($_SESSION['all_users']);
}

?>