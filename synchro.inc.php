<?php

require_once("manage_users.inc.php");
require_once("manage_sessions.inc.php");
require_once("manage_rapports.inc.php");


function synchronizeEmailsUpdates($email = true)
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
					$result .= "Migration des dossers de '".$user->login."' vers '".$row->mailpro;
					$result .= "' pour le num&egrave;ro de chaire '".$user->$field."'<br/>";
					mergeUsers($user->login, $row->mailpro, $email);
					$sql = "UPDATE ".users_db." SET `login`='".$row->mailpro."', `email`='".$row->mailpro."'";
					$sql .= " WHERE `".$field."`='".$row->$field."';";
					sql_request($sql);
				}
			}
		}
	}
	if(!$changed)
		$result .= "Aucun email n'a &eacute;t&eacute; mis &egrave; jour.<br/>";
	return $result;
}


function synchronizeWithDsiMembers($section,$email = true)
{
	$result = "";
	$users = listUsers(true, $section);

	$result .= synchronizeEmailsUpdates($email);
	$result .= "<br/><B>Synchronisation des membres de la section</B><br/>\n";
	
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
						$result .= "Mise &agrave; jour du champ '".$field."' du membre '".$login."' ";
						$result .= " de '". $user->$field."' vers '".$row->$field."'<br/>\n";
						$sql = "UPDATE ".users_db." SET `".$field."`='".$row->$field."' WHERE `login`='".$login."';";
						sql_request($sql);
					}
				}
			}
			else
			{
				$result .= "Ajout du compte ".$login." &agrave; la base marmotte.<br/>";
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
		$result .= "Liste d&eacute;j&agrave; &agrave; jour: aucun utilisateur n'a &eacute;t&eacute; ajout&eacute; &agrave; la base.<br/>";
	if(!$changed)
		$result .= "Donn&eacute;es d&eacute;j&agrave; &agrave; jour: aucune donn&egrave;e utilisateur n'a &eacute;t&eacute; mise &agrave; jour.<br/>";
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
			$answer .= "Cr&eacute;ation de la session ".$session. ".<br/>";
			createSession($row->LIB_SESSION, $row->ANNEE, $section);
		}
	}
	if(!$changed)
		$answer .= "Aucune session n'a &egrave;t&egrave; ajout&egrave;e.<br/>";
	sessionArrays(true);
	return $answer;
}

function synchronizePeople($section)
{
  $answer = "<B>Synchro des num SIRHUS de chercheurs </B><br/>\n";
	$sql =  "UPDATE ".people_db." marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ";
	$sql .= " ON marmotte.nom=dsi.nom AND marmotte.prenom=dsi.prenom";
	//	$sql .= " AND marm""otte.NUMSIRHUS=\"\" AND marmotte.section=\"".$section."\"";
	$sql .= " SET marmotte.NUMSIRHUS=dsi.numsirhus";
	$sql .= " WHERE section=\"".$section."\" AND marmotte.NUMSIRHUS=\"\";";
	$res = sql_request($sql);
	global $dbh;
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
	  $answer .= $num." num&eacute;ros SIRHUS ont &eacute;t&eacute; mis &agrave; jour.<br/>";
	else
	  $answer .= "Aucune num&eacute;ro SIRHUS n'a &eacute;t&eacute; mis &agrave; jour.<br/>";
	//$sql =  "DELETE FROM ".people_db." WHERE NUMSIRHUS=\"\" AND section=\"".$section."\";";
	//sql_request($sql);
	return 	$answer;
}
//id_unite
function synchronizePeopleReports($section, $session = "")
{
	//LIB_SESSION,ANNEE 
	if($session === "")
	  $session = current_session_id();
	$year = session_year($session);
	$lib = session_lib($session);

	$answer =   "<B>Synchro DE chercheurs section ".$section." session ".$session."</B><br/>\n";

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
	$sql .= " eval.EVALUATION_CN!=\"Annulé\" AND  eval.LIB_SESSION=\"".$lib."\" AND eval.ANNEE=\"".$year."\" AND ";
	$sql .=" (eval.CODE_SECTION =\"".$section."\" OR eval.CODE_SECTION_2=\"".$section."\" OR eval.CODE_SECTION_EXCPT=\"".$section."\");";
	
	$result = sql_request($sql);
	
	$answer .= "La base dsi contient ".mysqli_num_rows($result);
	$answer .= " DE chercheurs pour la section ".$section." et la session ".$session."<br/>\n";// qui n'apparaissent pas encore dans Marmotte.<br/>\n";

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
			$sql .= " AND LOWER(prenom)=LOWER(\"".$row->prenom."\")";
			$sql .= " AND section=\"".$section."\";";
			$res = sql_request($sql);
			global $dbh;
			$num = mysqli_affected_rows($dbh);
			if($num == 0)
			  {
				$sql  = "INSERT INTO ".people_db;
				$sql .= " (NUMSIRHUS,labo1,nom,prenom,section) VALUES ";
				$sql .= "(\"".$row->NUMSIRHUS."\",\"".$row->code_unite."\",\"".$row->nom."\",\"".$row->prenom."\",\"".$section."\");";
				try{ 
				  sql_request($sql);
				}
				catch(Exception $e)
				  {
				    //	    $answer .= "Exception ".$e->getMessage()." when adding NUMSIRHUS ".$row->NUMSIRHUS."<br/>";
				  }
			  }

	  $sql = "SELECT * FROM ".marmottedbname.".".reports_db." WHERE DKEY=".$row->DKEY." AND section=\"".$section."\";";
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
			  $answer .= "Changement type DE chercheur<br/>\n";
			$sql  = "UPDATE ".reports_db;
			$sql .= " SET type=\"".$row->TYPE_EVAL."\" WHERE id_session=\"".$session."\"";
			$sql .= " AND section=\"".$section."\" AND DKEY=\"\"";
			$sql .= " AND LOWER(nom)=LOWER(\"".$row->nom."\") AND LOWER(prenom)=LOWER(\"".$row->prenom."\");";				
			sql_request($sql);
			}
			//			else
			//echo "More than  one report ".mysqli_num_rows($res5). " for ".$row->nom."<br/>";
			//			if($row->nom == "CHANARON")die(0);

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

			  $answer .= $num." evaluations chercheur ont recu le DKEY".$row->DKEY;
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
		$row->rapporteur2 = $rapporteur2;
		$row->rapporteur3 = $rapporteur3;

		$row->type = $row->TYPE_EVAL;

		if($section == $row->CODE_SECTION)
		  $row->avis = $row->AVIS_EVAL;
		else if($section == $row->CODE_SECTION_2)
		  $row->avis = $row->AVIS_EVAL2;
		else if($section == $row->CODE_SECTION_EXCPT)
		  $row->avis = $row->AVIS_EVAL_EXCPT;
		
		addReportToDatabase($row);

		$answer .= "+DKEY ".$row->DKEY." ";
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

function synchronizeUnitReports($section = "", $session = "")
{
  if($session === "")
    $session = current_session_id();
	$year = session_year($session);
	$lib = session_lib($session);

	if($section === "") $section = currentSection();
	$answer = "<B>Synchro DE unit&eacute;s section ".$section." session ".$session."</B><br/>\n";
	
	$sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_units_db;
	$sql .=" WHERE ";
	$sql .= " EVALUATION_CN!=\"Annulé\" AND LIB_SESSION=\"".$lib."\" AND ANNEE=\"".$year."\" AND ";
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

	$answer .= "La base dsi contient ".mysqli_num_rows($res);
	$answer .= " DE unit&eacute;s qui n'apparaissent pas encore dans Marmotte.<br/>\n";
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
		  $answer .= "=rapporteurs ".$rapporteur." - ".$rapporteur2;
		  $answer .= " DKEY ".$row->DKEY." ";
			if($rapporteur != "")
				sql_request(
				"UPDATE ".reports_db." SET rapporteur=\"".$rapporteur."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur=\"\";");
			if($rapporteur2 != "")
				sql_request(
				"UPDATE ".reports_db." SET rapporteur2=\"".$rapporteur2."\" WHERE DKEY=\"".$row->DKEY."\" AND rapporteur2=\"\";");
			$answer .= $num." =DKEY ".$row->DKEY." ";
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

		    
		    $answer .= $num."=DKEY ".$row->DKEY." ";
		    continue;
		  }

		$answer .= "+DKEY ".$row->DKEY." ";
		$row->unite = $row->UNITE_EVAL;
		$row->type = $row->TYPE_EVAL;
		$row->id_session = $session;
		$row->section = $section;
		$row->id_origine=0;
		$row->rapporteur = $rapporteur;
		$row->rapporteur2 = $rapporteur2;

		for($i = 1; $i <= 9; $i++)
		{
			$field = "CODE_SECTION".$i;
			if($section == $row->$field)
			{
				$field = "AVIS".$i;
				$row->avis = $row->$field;
				break;
			}
		}

		addReportToDatabase($row);
	}
	return $answer;
}

function export_to_evaluation($section)
{
		$answer = "<B>Export des avis et rapporteurs vers e-valuation</B><br/>";
		$sql = "DELETE FROM dsi.".dsi_marmotte_db." WHERE 1";
		sql_request($sql);

		$sql = "insert into ".dsidbname.".".dsi_marmotte_db."(DKEY,AVIS_EVAL,CODE_SECTION,RAPPORTEUR1,RAPPORTEUR2,RAPPORTEUR3,statut)";
		$sql .=" select DKEY,avis,section,rapporteur,rapporteur2,rapporteur3,statut from ".marmottedbname.".".reports_db;
		$sql .=" WHERE DKEY!=\"\" AND id_origine=id AND (avis=\"avistransmis\" OR avis=\"publie\") AND section=\"".$section."\" ";
		$sql .=" ON DUPLICATE KEY UPDATE";
		$sql .=" dsi.".dsi_marmotte_db.".AVIS_EVAL=".marmottedbname.".".reports_db.".avis,";
		$sql .=" dsi.".dsi_marmotte_db.".RAPPORTEUR1=".marmottedbname.".".reports_db.".rapporteur,";
		$sql .=" dsi.".dsi_marmotte_db.".RAPPORTEUR2=".marmottedbname.".".reports_db.".rapporteur2,";
		$sql .=" dsi.".dsi_marmotte_db.".RAPPORTEUR3=".marmottedbname.".".reports_db.".rapporteur3,";
		$sql .=" dsi.".dsi_marmotte_db.".statut=".marmottedbname.".".reports_db.".statut";
		sql_request($sql);
		$answer .= "<a href=\"https://www.youtube.com/watch?v=0rCrc6RoJg0\">Ca l'effectue</a>";
		return $answer;
}


/* performs synchro with evaluation and returns diagnostic , empty string if nothing happaned */
function synchronize_with_evaluation($section = "", $recursive = false, $email = true)
{
  if(isSuperUser() && isset($_SESSION["answer_dsi_sync"]) && !$recursive)
    echo $_SESSION["answer_dsi_sync"];

  if(isSuperUser() && $section == "")
    return synchronize_with_evaluation("1",$recursive,$email);

  if(!isSuperUser())
    $section = currentSection();


  $answer = "<h1>Synchronisation avec e-valuation de la section ".$section." - ".date('d/m/Y - H:i:s')."</h1>\n";
	if(isSecretaire())
	{
	  $answer .= synchronizeWithDsiMembers($section,$email)."<br/>";

		$answer .= synchronizeSessions($section);

		$new_sessions = explode(";",get_config("sessions_synchro"));
		

		$answer .= synchronizePeople($section)."<br/>";
		
			$answer .= "<B>Suppression de l'historique des rapports</B><br/>\n";
	$sql = "DELETE FROM ".reports_db." WHERE id!=id_origine AND section=\"".$section."\";";
	sql_request($sql);
	global $dbh;
	$answer .= mysqli_affected_rows($dbh)." doublons ont ete retires de la base <br/>\n";

		$answer .= "<br/>".synchronizePeopleReports($section)."<br/>";
		$answer .= synchronizeUnitReports($section)."<br/>";
		foreach($new_sessions as $session)
		  if($session != "")
		  {
		    $answer .= "<br/>".synchronizePeopleReports($section,$session)."<br/>";
		    $answer .= synchronizeUnitReports($section,$session)."<br/>";
		  }		  
		
try
  {
		$answer.= export_to_evaluation($section);
  }
catch(Exception $e)
  {
    $answer .=  "Failed to export to e-valuation\n<br/>".$e->getMessage()."\n<br/>"; 
  }

	}

	if(isSuperUser())
	  {
	    if($section > 55)
	      {
		if(!$recursive)
		  unset($_SESSION["answer_dsi_sync"]);
		else
		  return "Done";
	      }
	    else if(!$recursive)
	      {
		$_SESSION["answer_dsi_sync"] = $answer . $_SESSION["answer_dsi_sync"];
		echo "<script>window.location = 'index.php?action=synchronize_with_dsi&section=".($section+1)."'</script>";
	      }
	    else 
	      {
		echo $answer;
		synchronize_with_evaluation($section + 1, true);
	      }
	  }

	return $answer;
}
