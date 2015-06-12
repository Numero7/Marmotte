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
				$sql .= " WHERE `mailpro`!=\"".$user->login."\" AND `";
				$sql .= $field."` =\"".$user->$field ."\" ";
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
  $answer = date('H:i:s')."<B>Synchronisation des numeros SIRHUS de chercheurs </B><br/>\n";
	$sql =  "UPDATE ".people_db." marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ";
	$sql .= " ON marmotte.nom=dsi.nom AND marmotte.prenom=dsi.prenom";
	//	$sql .= " AND marm""otte.NUMSIRHUS=\"\" AND marmotte.section=\"".$section."\"";
	$sql .= " SET marmotte.NUMSIRHUS=dsi.numsirhus";
	$sql .= " WHERE section=\"".$section."\" AND marmotte.NUMSIRHUS=\"\";";
	$res = sql_request($sql);
	global $dbh;
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
	  $answer .= date('H:i:s')."Mise a jour de ".$num." numéros SIRHUS<br/>";
	else
	  $answer .= date('H:i:s')."Aucune numéro SIRHUS n'a été mis à jour.<br/>";
	//$sql =  "DELETE FROM ".people_db." WHERE NUMSIRHUS=\"\" AND section=\"".$section."\";";
	//sql_request($sql);
	return 	$answer;
}
//id_unite
function synchronizePeopleReports($section)
{
	$answer =   date('H:i:s')."<B>Synchronisation des rapports chercheurs</B><br/>\n";
	//LIB_SESSION,ANNEE 
	$session = current_session_id();
	$year = session_year($session);
	$lib = session_lib($session);


	// 		Inefficient
	//	$sql = "SELECT * FROM ".marmottedbname.".".reports_db." WHERE DKEY != \"\"";
        //$sql .= " INNER JOIN ".dsidbname.".".dsi_evaluation_units_db.", dsi ON ";
        //$sql .= " dsi.DKEY NOT IN marmotte)";
	//$sql .= " AND marmotte.DKEY=\"\" AND";
	//$sql .= " (`dsi.CODE_SECTION` =\"".$section."\" OR `dsi.CODE_SECTION_2`=\"".$section."\" OR `dsi.CODE_SECTION_EXCPT`=\"".$section." \
	//\";";

	$sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_db." AS eval";
	$sql .= " LEFT JOIN ".dsidbname.".".dsi_people_db." AS people";
	$sql .= " ON eval.NUMSIRHUS=people.numsirhus WHERE ";
	// (DKEY NOT IN (SELECT DKEY FROM ".marmottedbname.".".reports_db."  WHERE DKEY != \"\")) AND ";
	$sql .= " eval.LIB_SESSION=\"".$lib."\" AND eval.ANNEE=\"".$year."\" AND ";
	$sql .=" (eval.CODE_SECTION =\"".$section."\" OR eval.CODE_SECTION_2=\"".$section."\" OR eval.CODE_SECTION_EXCPT=\"".$section."\");";
	
	$answer .= date('H:i:s')."Executing ".$sql."<br/>";

	$result = sql_request($sql);
	
	$answer .= date('H:i:s')."Done<br/>";

	$answer .= "La base dsi contient ".mysqli_num_rows($result);
	$answer .= " DE chercheurs pour la section.<br/>\n";// qui n'apparaissent pas encore dans Marmotte.<br/>\n";

	$changed = false;
	while($row = mysqli_fetch_object($result))
	{
	  $row->unite = $row->code_unite;
		if($section == $row->CODE_SECTION)
		{
			$rapporteur = getEmailFromChaire($row->RAPPORTEUR1);
			$rapporteur2 = getEmailFromChaire($row->RAPPORTEUR2);
			$rapporteur3 = getEmailFromChaire($row->RAPPORTEUR3);
		}
		else if($section == $row->CODE_SECTION_2)
		{
			$rapporteur = getEmailFromChaire($row->RAPPORTEUR4);
			$rapporteur2 = getEmailFromChaire($row->RAPPORTEUR5);
			$rapporteur3 = getEmailFromChaire($row->RAPPORTEUR6);
		}
		else if($section == $row->CODE_SECTION_EXCPT)
		{
			$rapporteur = getEmailFromChaire($row->RAPPORTEUR7);
			$rapporteur2 = getEmailFromChaire($row->RAPPORTEUR8);
			$rapporteur3 = getEmailFromChaire($row->RAPPORTEUR9);
		}

			$sql  = "UPDATE ".people_db;
			$sql .= " SET labo1=\"".$row->code_unite."\", NUMSIRHUS=\"".$row->NUMSIRHUS."\"";
			$sql .= " WHERE LOWER(nom)=LOWER(\"".$row->nom."\")";
			$sql .= " AND LOWER(prenom)=LOWER(\"".$row->prenom."\");";
			$res = sql_request($sql);
			global $dbh;
			$num = mysqli_affected_rows($dbh);
			if($num == 0)
			  {
				$sql  = "INSERT INTO ".people_db;
				$sql .= " (NUMSIRHUS,labo1,nom,prenom,section) VALUES ";
				$sql .= "(\"".$row->NUMSIRHUS."\",\"".$row->code_unite."\",\"".$row->nom."\",\"".$row->prenom."\",\"".$section."\");";
				try{ sql_request($sql); }catch(Exception $e){}
			  }

	  $sql = "SELECT * FROM ".marmottedbname.".".reports_db." WHERE DKEY=".$row->DKEY.";";
	  $res2 = sql_request($sql);
	  if(mysqli_num_rows($res2) == 0)
	    {
	//pas de DKEY corr dans la base
			$sql  = "SELECT * FROM ".reports_db;
			$sql .= " WHERE id_session=\"".$session."\"";
			$sql .= " AND section=\"".$section."\" AND DKEY=\"\"";
			$sql .= " AND LOWER(nom)=LOWER(\"".$row->nom."\") AND LOWER(prenom)=LOWER(\"".$row->prenom."\");";
			$res5 = sql_request($sql);
	//if only one this is the one
			if(mysqli_num_rows($res5) == 1)
			{
			  //			  echo "Oly one report for ".$row->nom."<br/>";
			  $answer .= "Changement du type de la DE chercheur <br/>\n";
			$sql  = "UPDATE ".reports_db;
			$sql .= " SET type=\"".$row->TYPE_EVAL."\" WHERE id_session=\"".$session."\"";
			$sql .= " AND section=\"".$section."\" AND DKEY=\"\"";
			$sql .= " AND LOWER(nom)=LOWER(\"".$row->nom."\") AND LOWER(prenom)=LOWER(\"".$row->prenom."\");";				
			sql_request($sql);
			}
			//			else
			//echo "More than  one report ".mysqli_num_rows($res5). " for ".$row->nom."<br/>";
			//			if($row->nom == "CHANARON")die(0);

	      $session = $row->LIB_SESSION.$row->ANNEE;
			$sql  = "UPDATE ".reports_db;
			$sql .= " SET NUMSIRHUS=\"".$row->NUMSIRHUS."\", DKEY=\"".$row->DKEY."\" WHERE id_session=\"".$session."\"";
			$sql .= " AND section=\"".$section."\" AND DKEY=\"\" AND type=\"".$row->TYPE_EVAL."\"";
			$sql .= " AND LOWER(nom)=LOWER(\"".$row->nom."\") AND LOWER(prenom)=LOWER(\"".$row->prenom."\");";
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

			  $answer .= date('H:i:s').": ".$num." evaluations chercheur ont recu le DKEY".$row->DKEY;
			  $answer .= " et le NUMSIRHUS ".$row->NUMSIRHUS."<br/>\n";
			  $changed = true;
				continue;
			}
			else
			{
			}

		$row->id_origine=0;
		$row->id_session = $session;
		$row->section = $section;
		$row->rapporteur = $rapporteur;

		$row->rapporteur = $rapporteur;
		$row->rapporteur3 = $rapporteur3;
		$row->type = $row->TYPE_EVAL;
		
		$answer .= date('H:i:s')." Ajout de l'evaluations chercheur de DKEY ".$row->DKEY." et Sirhus ".$row->NUMSIRHUS."<br/>\n";
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
	$session = current_session_id();
	$year = session_year($session);
	$lib = session_lib($session);

	if($section == "") $section = currentSection();
	$answer = date('H:i:s')."<B>Synchronisation des rapports unités</B><br/>\n";
	
	$sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_units_db;
	$sql .=" WHERE ";
	$sql .= " LIB_SESSION=\"".$lib."\" AND ANNEE=\"".$year."\" AND ";
	$sql .= "(`CODE_SECTION1`=\"".$section."\" OR `CODE_SECTION2`=\"";
	$sql .= $section."\"  OR `CODE_SECTION3`=\"".$section."\"";
	$sql .= " OR `CODE_SECTION4`=\"".$section."\" OR `CODE_SECTION5`=\"";
	$sql .= $section."\"  OR `CODE_SECTION6`=\"".$section."\"";
	$sql .= " OR `CODE_SECTION7`=\"".$section."\" OR `CODE_SECTION8`=\"";
	$sql .= $section."\"  OR `CODE_SECTION9`=\"".$section."\"";
	$sql .= ") ";
	$sql .= " AND ";
	$sql .= " ( DKEY NOT IN (SELECT DKEY FROM ";
	$sql .= marmottedbname.".".reports_db." WHERE id_session=\"".$session."\" AND DKEY != \"\" AND section=\"".$section."\") )";

	$res = sql_request($sql);

	$answer .= date('H:i:s')."La base dsi contient ".mysqli_num_rows($res);
	$answer .= " DE unités qui n'apparaissent pas encore dans Marmotte.<br/>\n";
	while($row = mysqli_fetch_object($res))
	{
		
		for($i = 1; $i <= 9; $i++)
		{
			$field = "CODE_SECTION".$i;
			if($section == $row->$field)
			{
				$field = "RAPPORTEUR".$i."1";
				$rapporteur = getEmailFromChaire($row->$field);
				$field = "RAPPORTEUR".$i."2";
				$rapporteur2 = getEmailFromChaire($row->$field);
				break;
			}
		}
		
	  //		$answer .= "Synchronisation de la DKEY ".$row->DKEY."<br/>\n";
		//		$session = $row->LIB_SESSION.$row->ANNEE;
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
		  $answer .= date('H:i:s')."Mise a jour des rapporteurs ".$rapporteur." - ".$rapporteur2;
		  $answer .= " du dossier unite ".$row->UNITE_EVAL."<br/>";
			if($rapporteur != "")
				sql_request(
				"UPDATE ".reports_db." SET rapporteur=\"".$rapporteur."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur=\"\";");
			if($rapporteur2 != "")
				sql_request(
				"UPDATE ".reports_db." SET rapporteur2=\"".$rapporteur2."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur2=\"\";");
			$answer .= $num." rapports Marmotte ont ete synchronisee (KEY ".$row->DKEY.")<br/>\n";
			continue;
		}

		$sql = "SELECT * FROM ".reports_db." WHERE";
		$sql .= " DKEY=\"\" AND id=id_origine AND  id_session=\"".$session."\" AND section=\"".$section."\"";
		$sql .= " AND unite=\"".$row->UNITE_EVAL."\" AND id=id_origine;";
		$num = mysqli_num_rows(sql_request($sql));
		if($num == 1)
		  {
		  $answer .= date('H:i:s')."Mise a jour des rapporteurs ".$rapporteur." - ".$rapporteur2;
		  $answer .= " du dossier unite 'repeche'".$row->UNITE_EVAL."<br/>";

		    $sql = "UPDATE ".reports_db." SET type=\"".$row->TYPE_EVAL."\", DKEY=\"".$row->DKEY."\" WHERE";
		    $sql .= " DKEY=\"\" AND id_session=\"".$session."\" AND section=\"".$section."\"";
		    $sql .= " AND unite=\"".$row->UNITE_EVAL."\" ;";		    
		    //	    $answer .= $sql;
		    sql_request($sql);

		    if($rapporteur != "")
		    	sql_request("UPDATE ".reports_db." SET rapporteur=\"".$rapporteur."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur=\"\";");
		    if($rapporteur2 != "")
		    	sql_request("UPDATE ".reports_db." SET rapporteur2=\"".$rapporteur2."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur2=\"\";");
		    
		    global $dbh;
		    $num = mysqli_affected_rows($dbh);

		    
		    $answer .= date('H:i:s').$num." evaluations unites a ete synchronisee (KEY ".$row->DKEY."<br/>\n";
		    continue;
		  }


		$answer .= date('H:i:s')."Import de l'evaluations unite de DKEY ".$row->DKEY."<br/>\n";
		$row->unite = $row->UNITE_EVAL;
		$row->type = $row->TYPE_EVAL;
		$row->id_session = $session;
		$row->section = $section;
		$row->id_origine=0;
		$row->rapporteur = $rapporteur;
		$row->rapporteur2 = $rapporteur2;
		addReportToDatabase($row);
	}
	return $answer;
}


/* performs synchro with evaluation and returns diagnostic , empty string if nothing happaned */
function synchronize_with_evaluation($section = "")
{
	if( ($section == "") and !isSuperUser())
		$section = currentSection();

	$answer = date('H:i:s')."<B>Synchronisation avec e-valuation de la section ".$section."</B><br/>\n";
	if(isSecretaire())
	{
	$sql = "DELETE FROM ".reports_db." WHERE id!=id_origine AND section=\"".$section."\";";
	sql_request($sql);
	global $dbh;
	$answer .= mysqli_affected_rows($dbh)." doublons ont ete retires de la base <br/>\n";
	  $answer .= date('H:i:s'); 
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
