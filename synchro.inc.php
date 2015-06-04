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
					$sql = "UPDATE ".users_db." SET `login`='".$row->mailpro."', `email`='".$row->mailpro."'";
					$sql .= " WHERE `".$field."`='".$row->$field."';";
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

	$result .= synchronizeEmailsUpdates();
	$result .= "<B>Synchronisation des membres de la section</B><br/>\n";
	
	if (isSuperUser())
		$sql = "SELECT * FROM ".dsidbname.".".dsi_users_db." WHERE 1;";
	else
		$sql = "SELECT * FROM ".dsidbname.".".dsi_users_db." WHERE CID_code=\"".$section."\" OR section_code=\"".$section."\";";

	$res = sql_request($sql);
	
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
						$result .= "Mise à jour du champ '".$field."' du membre '".$login."' ";
						$result .= " de '". $user->$field."' vers '".$row->$field."'<br/>\n";
						$sql = "UPDATE ".users_db." SET `".$field."`='".$row->$field."' WHERE `login`='".$login."';";
						sql_request($sql);
					}
				}
			}
			else
			{
				$result .= "Ajout du compte ".$login." à la base marmotte.<br/>";
				$sql = "INSERT INTO ".users_db;
				$sql .= " (login,sections,permissions,section_code,section_role_code,CID_code";
				$sql .= ",CID_role_code,section_numchaire,CID_numchaire, passHash,description,email,tel) ";
				$sql .= "VALUES ('";
				$sql .= real_escape_string($login)."','','0','".$row->section_code."','";
				$sql .= $row->section_role_code."','".$row->CID_code."','".$row->CID_role_code."','";
				$sql .= $row->section_numchaire."','".$row->CID_numchaire."','','";
				$sql .= real_escape_string($row->nom." ".$row->prenom)."','".$login."','');";
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
//	foreach($sessions as $session)
//	 $answer .= "Marmotte ".$session."<br/>";

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
	$answer = "<B>Synchronisation des numeros SIRHUS de chercheurs (toutes sections)</B><br/>\n";
	$sql =  "UPDATE ".people_db." marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ";
	$sql .= " ON marmotte.nom=dsi.nom AND marmotte.prenom=dsi.prenom";
	$sql .= " AND marmotte.NUMSIRHUS=\"\" AND marmotte.section=\"".$section."\"";
	$sql .= " SET marmotte.NUMSIRHUS=dsi.numsirhus;";
//	$sql .= " WHERE marmotte.NUMSIRHUS=\"\" AND marmotte.section=\"".$section."\";";
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
	$answer =   date('H:i:s')."<B>Synchronisation des rapports chercheurs</B><br/>\n";

	//	$sql = "SELECT * FROM ".marmottedbname.".".reports_db." WHERE DKEY != \"\"";
        //$sql .= " INNER JOIN ".dsidbname.".".dsi_evaluation_units_db.", dsi ON ";
        //$sql .= " dsi.DKEY NOT IN marmotte)";
	//$sql .= " AND marmotte.DKEY=\"\" AND";
	//$sql .= " (`dsi.CODE_SECTION` =\"".$section."\" OR `dsi.CODE_SECTION_2`=\"".$section."\" OR `dsi.CODE_SECTION_EXCPT`=\"".$section." \
	//\";";

	$sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_db;
	$sql .=" WHERE";
	// (DKEY NOT IN (SELECT DKEY FROM ".marmottedbname.".".reports_db."  WHERE DKEY != \"\")) AND ";
	$sql .=" (`CODE_SECTION` =\"".$section."\" OR `CODE_SECTION_2`=\"".$section."\" OR `CODE_SECTION_EXCPT`=\"".$section."\");";
	
	$answer .= date('H:i:s')."Executing ".$sql."<br/>";

	$result = sql_request($sql);
	
	$answer .= date('H:i:s')."Done<br/>";

	$answer .= "La base dsi contient ".mysqli_num_rows($result);
	$answer .= " DE chercheurs.<br/>\n";// qui n'apparaissent pas encore dans Marmotte.<br/>\n";

	$changed = false;
	while($row = mysqli_fetch_object($result))
	{
	  $sql = "SELECT * FROM ".marmottedbname.".".reports_db." WHERE DKEY=".$row->DKEY.";";
	  $res2 = sql_request($sql);
	  if(mysqli_num_rows($res2) == 0)
	    {
	      //	      $answer .= date('H:i:s')."New row, getting user<br/>";
	      $session = $row->LIB_SESSION.$row->ANNEE;
	      $user = get_candidate_from_SIRHUS($row->NUMSIRHUS);
	      //$answer .= date('H:i:s')."New row, getting user<br/>";
	      if($user != null)
		{
		  //  $answer .= date('H:i:s')."User no null<br/>";
			$sql  = "UPDATE ".reports_db;
			$sql .= " SET NUMSIRHUS=\"".$row->NUMSIRHUS."\", DKEY=\"".$row->DKEY."\" WHERE id_session=\"".$session."\"";
			$sql .= " AND section=\"".$section."\" AND DKEY=\"\" AND type=\"".$row->TYPE_EVAL."\"";
			$sql .= " AND nom=\"".$user->nom."\" AND prenom=\"".$user->prenom."\";";
			$res = sql_request($sql);
			global $dbh;
			$num = mysqli_affected_rows($dbh);
			if($num > 0)
			{
			  $answer .= date('H:i:s').": ".$num." evaluations chercheur ont reçu le DKEY ".$row->DKEY." w<br/>\n";
			  $changed = true;
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
		$row->type = $row->TYPE_EVAL;
		
		$answer .= date('H:i:s')."Ajout d'un nouveau rapport a la base Marmotte<br/>";
		addReportToDatabase($row);
		$changed = true;
	    }
		else
		{
//		$answer .= "Le rapport de DKEY ".$row->DKEY." a deja ete importe dans Marmotte<br/>\n";
		}
	}
	  if(!$changed) $answer.= "Pas de modification de la base marmotte<br/>";
	return $answer;
}

function synchronizeUnitReports($section = "")
{
	if($section == "") $section = currentSection();
	$answer = "<B>Synchronisation des rapports unités</B><br/>\n";
	
	$sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_units_db;
	$sql .=" WHERE ";
	$sql .= "(`CODE_SECTION1`=\"".$section."\" OR `CODE_SECTION2`=\"".$section."\"  OR `CODE_SECTION3`=\"".$section."\"";
	$sql .= " OR `CODE_SECTION4`=\"".$section."\" OR `CODE_SECTION5`=\"".$section."\"  OR `CODE_SECTION6`=\"".$section."\"";
	$sql .= " OR `CODE_SECTION7`=\"".$section."\" OR `CODE_SECTION8`=\"".$section."\"  OR `CODE_SECTION9`=\"".$section."\"";
	$sql .= ") ";
	$sql .= " AND ";
	$sql .= " ( DKEY NOT IN (SELECT DKEY FROM ".marmottedbname.".".reports_db." WHERE DKEY != \"\" AND section=\"".$section."\") )";

	//	$answer .= $sql."<br/>";
	$res = sql_request($sql);
//	echo $sql."<br/>";

	$answer .= "La base dsi contient ".mysqli_num_rows($res). " DE unités qui n'apparaissent pas encore dans Marmotte.<br/>\n";
	while($row = mysqli_fetch_object($res))
	{
	  //		$answer .= "Synchronisation de la DKEY ".$row->DKEY."<br/>\n";
		$session = $row->LIB_SESSION.$row->ANNEE;
		$sql  = "UPDATE ".reports_db;
		$sql .= " SET DKEY=\"".$row->DKEY."\" WHERE id_session=\"".$session."\"";
		$sql .= " AND section=\"".$section."\" AND DKEY=\"\" ";
		$sql .= " AND (type=\"".$row->TYPE_EVAL."\") ";
		$sql .= " AND unite=\"".$row->UNITE_EVAL."\";";
		//$answer .= $sql."<br/>";
		sql_request($sql);
		global $dbh;
		$num = mysqli_affected_rows($dbh);

		global $dbh;
		if($num >0)
		{
			$answer .= $num." rapports Marmotte ont ete synchronisee (KEY ".$row->DKEY.")<br/>\n";
			continue;
		}

		$sql = "SELECT * FROM ".reports_db." WHERE";
		$sql .= " DKEY=\"\" AND id=id_origine AND  id_session=\"".$session."\" AND section=\"".$section."\"";
		$sql .= " AND unite=\"".$row->UNITE_EVAL."\" AND id=id_origine;";
		$num = mysqli_num_rows(sql_request($sql));
		if($num == 1)
		  {
		    $sql = "UPDATE ".reports_db." SET type=\"".$row->TYPE_EVAL."\", DKEY=\"".$row->DKEY."\" WHERE";
		    $sql .= " DKEY=\"\" AND id_session=\"".$session."\" AND section=\"".$section."\"";
		    $sql .= " AND unite=\"".$row->UNITE_EVAL."\" ;";		    
		    //	    $answer .= $sql;

		    sql_request($sql);
		    global $dbh;
		    $num = mysqli_affected_rows($dbh);

		    $answer .= $num." evaluations unites a ete synchronisee (KEY ".$row->DKEY."<br/>\n";
		    continue;
		  }


		$answer .= "Import de l'evaluations unite de DKEY ".$row->DKEY."<br/>\n";
		$row->unite = $row->UNITE_EVAL;
		$row->type = $row->TYPE_EVAL;
		$row->id_session = $session;
		$row->section = $section;
		$row->id_origine=0;
		addReportToDatabase($row);
	}
	return $answer;
}


/* performs synchro with evaluation and returns diagnostic , empty string if nothing happaned */
function synchronize_with_evaluation($section = "")
{
  if($section == "") $section = currentSection();
	$answer = "<B>Synchronisation avec e-valuation de la section ".$section."</B><br/>\n";
	if(isSecretaire())
	{
	  $answer = date('H:i:s'); 
		$answer .= synchronizeWithDsiMembers($section)."<br/>";
	  $answer .= date('H:i:s'); 
		$answer .= synchronizeSessions($section)."<br/>";
	  $answer .= date('H:i:s'); 
		$answer .= synchronizePeople($section)."<br/>";
	  $answer .= date('H:i:s'); 
		$answer .= synchronizePeopleReports($section)."<br/>";
	  $answer .= date('H:i:s'); 
		$answer .= synchronizeUnitReports($section)."<br/>";
	}
	return $answer;
}
