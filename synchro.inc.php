<?php

require_once("manage_users.inc.php");
require_once("manage_sessions.inc.php");

function synchronizeEmailsUpdates()
{
	if(!isSecretaire())
		throw new Exception("Vous n'avez pas les droits sffisnats pour cette opération");

	$result = "<B>Synchronisation des changements d'emails</B><br/>\n";

	$users = listUsers(true);

	$changed = false;

	/* changements d'emails */
	foreach($users as $user)
	{
		$fields = array("section_numchaire", "CID_numchaire");
		foreach($fields as $field)
		{
			if($user->$field != "")
			{
				$sql = "SELECT * FROM ".dsidbname.".".dsi_users_db;
				$sql .= " WHERE `mailpro`!=\"".$user->login."\" AND `".$field."` =\"".$user->$field ."\" ";
				if (isSuperUser())
					$sql .= ";";
				else
					$sql .= " AND ( `CID_code`=\"".currentSection()."\" OR `section_code`=\"".currentSection()."\");";
				$res = sql_request($sql);
				while ($row = mysqli_fetch_object($res))
				{
					$changed = true;
					$result .= "Migation des dossers de '".$user->login."' vers '".$row->mailpro."' pour le numéro de chaire '".$user->$field."'<br/>";
					mergeUsers($user->login, $row->mailpro);
					$sql = "UPDATE ".users_db." SET `login`='".$row->mailpro."', `email`='".$row->mailpro."' WHERE `".$field."`='".$row->$field."';";
					sql_request($sql);
				}
			}
		}
	}
	if(!$changed)
		$result .= "Aucun email n'a été mis à jour.<br/>";
	return $result;
}


function synchronizeWithDsiMembers($section)
{
	$result = "";

	$users = listUsers(true, $section);

	//	$result .= "La base marmotte contient ".count($users)." membres.<br/>\n";

	$result .= synchronizeEmailsUpdates();
	$result .= "<B>Synchronisation des membres de la section</B><br/>\n";

	if (isSuperUser())
		$sql = "SELECT * FROM ".dsidbname.".".dsi_users_db." WHERE 1;";
	else
		$sql = "SELECT * FROM ".dsidbname.".".dsi_users_db." WHERE CID_code=\"".$section."\" OR section_code=\"".$section."\";";

	$res = sql_request($sql);
	//	$result .= "La base dsi contient ". mysqli_num_rows($res)." membres.<br/>\n";

	$changed = false;
	$added = false;

	while ($row = mysqli_fetch_object($res))
	{
		$login = $row->mailpro;
		$fields = array("section_numchaire","CID_numchaire","section_code","CID_code","section_role_code", "CID_role_code");
		try
		{
			$user = getUserByLogin($login, true);
			if($user != null)
			{
				foreach($fields as $field)
				{
					if($row->$field != $user->$field)
					{
						$changed = true;
						$result .= "Mise à jour du champ '".$field."' du membre '".$login."' de '". $user->$field."' vers '".$row->$field."'<br/>\n";
						$sql = "UPDATE ".users_db." SET `".$field."`='".$row->$field."' WHERE `login`='".$login."';";
						sql_request($sql);
					}
				}
			}
			else
			{
				$result .= "Ajout du compte ".$login." à la base marmotte.<br/>";
				$sql = "INSERT INTO ".users_db." (login,sections,permissions,section_code,section_role_code,CID_code,CID_role_code,section_numchaire,CID_numchaire, passHash,description,email,tel) ";
				$sql .= "VALUES ('";
				$sql .= real_escape_string($login)."','','0','".$row->section_code."','".$row->section_role_code."','".$row->CID_code."','".$row->CID_role_code."','".$row->section_numchaire."','".$row->CID_numchaire."','','".real_escape_string($row->nom." ".$row->prenom)."','".$login."','');";
				sql_request($sql);
			}
		}
		catch(Exception $exc)
		{
			$result .= "Erreur: ".$exc->getMessage()."<br/>\n";
		}
	}
	if(!$added)
		$result .= "Aucun utilisateur n'a été ajouté à la base<br/>";
	if(!$changed)
		$result .= "Aucune donnée utilisateur n'a été mise à jour<br/>";
	unset($_SESSION['all_users']);

	return $result;
}

function synchronizeSessions($section)
{
	$changed = false;
	$answer = "<B>Synchronization des sessions </B><br/>\n";
	$sql = "SELECT DISTINCT LIB_SESSION,ANNEE FROM ".dsidbname.".".dsi_evaluation_db;
	$sql.=" WHERE `CODE_SECTION` =\"".$section."\" OR `CODE_SECTION_2`=\"".$section."\" OR `CODE_SECTION_EXCPT`=\"".$section."\";";
	$res = sql_request($sql);
	$sessions = get_all_sessions($section);
	while($row = mysqli_fetch_object($res))
	{
		$session = $row->LIB_SESSION.$row->ANNEE;
		if( ! in_array($session, $sessions) )
		{
			$changed = true;
			$answer .= "Création de la session ".$session. ".<br/>";
			createSession($row->LIB_SESSION, $row->ANNEE, $section);
		}
	}
	if(!$changed)
		$answer .= "Aucune session n'a été ajoutée.<br/>";
	return $answer;
}

function synchronizePeople($section)
{
	$answer = "<B>Synchronisation des numéros SIRHUS de chercheurs (toutes sections)</B><br/>\n";
	$sql =  "UPDATE ".people_db." marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ";
	$sql .= " ON marmotte.nom=dsi.nom AND marmotte.prenom=dsi.prenom";
	$sql .= " SET marmotte.NUMSIRHUS=dsi.numsirhus";
	$sql .= " WHERE marmotte.NUMSIRHUS=\"\";";
	$res = sql_request($sql);
	global $dbh;
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
		$answer = "Mise a jour de ".$num." numéros SIRHUS<br/>";
	else
		$answer .= "Aucune numéro SIRHUS n'a été mis à jour.<br/>";
	return 	$answer;
}
//id_unite
function synchronizePeopleReports($section)
{
	$answer = "<B>Synchronisation des rapports chercheurs</B><br/>\n";
	$sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_db;
	$sql.=" WHERE (DKEY NOT IN (SELECT DISTINCT DKEY FROM ".marmottedbname.".".reports_db."  WHERE DKEY != \"\")) ";
	$sql .=" AND (`CODE_SECTION` =\"".$section."\" OR `CODE_SECTION_2`=\"".$section."\" OR `CODE_SECTION_EXCPT`=\"".$section."\");";

	$result = sql_request($sql);
	$answer .= "La base dsi contient ".mysqli_num_rows($result). " DE chercheurs qui n'apparaissent pas encore dans Marmotte.<br/>\n";

	while($row = mysqli_fetch_object($result))
	{
		$session = $row->LIB_SESSION.$row->ANNEE;
		$user = get_candidate_from_SIRHUS($row->NUMSIRHUS);

		if($section == $row->CODE_SECTION)
		{
			$rapporteur = $row->RAPPORTEUR1;
			$rapporteur2 = $row->RAPPORTEUR2;
			$rapporteur3 = $row->RAPPORTEUR3;
		}
		else if($section == $row->CODE_SECTION_2)
		{
			$rapporteur = $row->RAPPORTEUR4;
			$rapporteur2 = $row->RAPPORTEUR5;
			$rapporteur3 = $row->RAPPORTEUR6;
		}
		else if($section == $row->CODE_SECTION_EXCPT)
		{
			$rapporteur = $row->RAPPORTEUR7;
			$rapporteur2 = $row->RAPPORTEUR8;
			$rapporteur3 = $row->RAPPORTEUR9;
		}

		if($user != null)
		{
			$sql  = "UPDATE ".reports_db;
			$sql .= " SET NUMSIRHUS=\"".$row->NUMSIRHUS."\", DKEY=\"".$row->DKEY."\" WHERE id_session=\"".$session."\"";
			$sql .= " AND section=\"".$section."\" AND DKEY=\"\" AND type=\"".$row->TYPE_EVAL."\" AND nom=\"".$user->nom."\" AND prenom=\"".$user->prenom."\";";
			$res = sql_request($sql);
			global $dbh;
			$num = mysqli_affected_rows($dbh);
			if($num > 0)
			{
				if($rapporteur != "")
					sql_request("UPDATE ".reports_db." SET rapporteur=\"".$rapporteur."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur=\"\";");
				if($rapporteur2 != "")
					sql_request("UPDATE ".reports_db." SET rapporteur2=\"".$rapporteur2."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur2=\"\";");
				if($rapporteur3 != "")
					sql_request("UPDATE ".reports_db." SET rapporteur3=\"".$rapporteur3."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur3=\"\";");
				$answer .= $num." evaluations chercheur ont reçu le DKEY ".$row->DKEY." with request<br/>".$sql."<br/>\n";
				continue;
			}
		}
		if($user != null)
		{
			$row->nom = $user->nom;
			$row->prenom = $user->prenom;
		}
		$answer .= "Import de l'evaluations chercheur de DKEY ".$row->DKEY."<br/>\n";
		$row->id_origine=0;
		$row->id_session = $session;
		$row->section = $section;
		$row->rapporteur = $rapporteur;
		$row->rapporteur2 = $rapporteur2;
		$row->rapporteur3 = $rapporteur3;
		$row->type = $row->TYPE_EVAL;

		addReportToDatabase($row);
	}
	return $answer;
}

function synchronizeUnitReports($section = "")
{
	if($section == "") $section = currentSection();
	$answer = "<B>Synchronisation des rapports unités</B><br/>\n";
	$sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_units_db;
	$sql.=" WHERE ( DKEY NOT IN (SELECT DISTINCT DKEY FROM ".marmottedbname.".".reports_db." WHERE DKEY != \"\") ";
	$sql .= " AND (";
	$sql .= "`CODE_SECTION1`=\"".$section."\" OR `CODE_SECTION2`=\"".$section."\"  OR `CODE_SECTION3`=\"".$section."\"";
	$sql .= " OR `CODE_SECTION4`=\"".$section."\" OR `CODE_SECTION5`=\"".$section."\"  OR `CODE_SECTION6`=\"".$section."\"";
	$sql .= " OR `CODE_SECTION7`=\"".$section."\" OR `CODE_SECTION8`=\"".$section."\"  OR `CODE_SECTION9`=\"".$section."\"";
	$sql .= ") );";

	$res = sql_request($sql);
	//	echo $sql."<br/>";

	$answer .= "La base dsi contient ".mysqli_num_rows($res). " DE unités qui n'apparaissent pas encore dans Marmotte.<br/>\n";
	while($row = mysqli_fetch_object($res))
	{
		for($i = 1; $i <= 9; $i++)
		{
			$field = "CODE_SECTION".$i;
			if($section == $row->$field)
			{
				$field = "RAPPORTEUR".$i."1";
				$rapporteur = $row->$field;
				$field = "RAPPORTEUR".$i."2";
				$rapporteur2 = $row->$field;
				break;
			}
		}
		
		$session = $row->LIB_SESSION.$row->ANNEE;
		$sql  = "UPDATE ".reports_db;
		$sql .= " SET DKEY=\"".$row->DKEY."\" WHERE id_session=\"".$session."\"";
		$sql .= " AND section=\"".$section."\" AND DKEY=\"\" AND type=\"".$row->TYPE_EVAL."\" AND unite=\"".$row->UNITE_EVAL."\";";
		$result = sql_request($sql);

		global $dbh;
		$num = mysqli_affected_rows($dbh);
		if($num > 0)
		{
			if($rapporteur != "")
				sql_request("UPDATE ".reports_db." SET rapporteur=\"".$rapporteur."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur=\"\";");
			if($rapporteur2 != "")
				sql_request("UPDATE ".reports_db." SET rapporteur2=\"".$rapporteur2."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur2=\"\";");
			$answer .= $num." evaluations unites ont reçu le DKEY ".$row->DKEY."<br/>\n";
			continue;
		}

		$answer .= "Import de l'evaluations unite de DKEY ".$row->DKEY."<br/>\n";
		$row->unite = $row->UNITE_EVAL;
		$row->type = $row->TYPE_EVAL;
		$row->id_session = $session;
		$row->section = $section;
		$row->rapporteur = $rapporteur;
		$row->rapporteur2 = $rapporteur2;
		$row->id_origine=0;
		addReportToDatabase($row);
	}
	return $answer;
}

/* performs synchro with evaluation and returns diagnostic , empty string if nothing happaned */
function synchronize_with_evaluation($section = "")
{
	$answer = "<B>Synchronisation avec e-valuation de la section ".$section."</B><br/>\n";
	if(isSecretaire())
	{
		$answer .= synchronizeWithDsiMembers($section)."<br/>";
		$answer .= synchronizeSessions($section)."<br/>";
		$answer .= synchronizePeople($section)."<br/>";
		$answer .= synchronizePeopleReports($section)."<br/>";
		$answer .= synchronizeUnitReports($section)."<br/>";
	}
	return $answer;
}