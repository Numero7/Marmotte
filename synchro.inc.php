<?php

require_once("manage_users.inc.php");
require_once("manage_sessions.inc.php");
require_once("manage_rapports.inc.php");

function synchronizeStatutsConcours($year = "")
{
  if($year == "")
    $year = date("Y");
  $log = "<h3>Synchronisation des statuts</h3>";

  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.statut_celcc='non admis à concourir' ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.admis_concourir='non'"; 
  sql_request($sql);
  global $dbh;
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." statuts celcc ont été basculés à 'non admis a concourir'<br/>";

  // mise a jour des statuts cel_cc
  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.statut_celcc='soumis à IE' ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.dde_equiv='soumis au IE' AND admis_concourir='non traité'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." statuts celcc ont été basculés à 'IE'<br/>";

  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.statut_celcc='soumis au CS' ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.dde_equiv='soumis au CS' AND admis_concourir='non traité'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." statuts celcc ont été basculés à 'CS'<br/>";

  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.statut_celcc='admis à concourir' ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.admis_concourir='oui' AND admis_poursuivre='non traité'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." statuts celcc ont été basculés à 'admis a concourir'<br/>";

  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.statut_celcc='admis à poursuivre' ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.admis_poursuivre='oui' AND dsi.admissible='non traité'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." statuts celcc ont été basculés à 'admis a poursuivre'<br/>";

  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.statut_celcc='non admis à poursuivre' ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.admis_concourir='oui' AND dsi.admis_poursuivre='non'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." statuts celcc ont été basculés à 'non admis a poursuivre'<br/>";

  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.statut_celcc='non-admissible' ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.admissible='non'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." statuts celcc ont été basculés à 'non admissible'<br/>";

  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.statut_celcc=CONCAT('admissible: ',dsi.classement_admissibilite) ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.admissible='oui' AND dsi.admis='non traité'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." statuts celcc ont été basculés à 'admissible'<br/>";

  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.statut_celcc=CONCAT('non-admis, admissible: ',dsi.classement_admissibilite) ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.admissible='oui' AND dsi.admis='non'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." statuts celcc ont été basculés à 'non-admis'<br/>";

  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.statut_celcc=CONCAT('admis: ',dsi.classement_admission,' admissible: ',dsi.classement_admissibilite) ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.admissible='oui' AND dsi.admis!='non'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." statuts celcc ont été basculés à 'admis'<br/>";


  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi SET marmotte.avis='".avis_non_classe."' ";
  $sql .= "WHERE marmotte.avis='' AND dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.admis_concourir='non'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." avis marmotte ont été basculés à 'non classé'<br/>";

  $sql = "UPDATE reports marmotte, ".dsidbname.".".celcc_statuts." dsi ";
  $sql .= "SET marmotte.avis='".avis_non_classe."', marmotte.statut_celcc='retrait candidature' ";
  $sql .= "WHERE dsi.num_conc=marmotte.concours AND dsi.user_id=marmotte.concoursid AND dsi.retrait_candidature!='non'"; 
  sql_request($sql);
  $changed = mysqli_affected_rows($dbh);
  if($changed > 0)
    $log .= $changed." avis marmotte ont été basculés à 'retrait candidature'<br/>";

  return $log;
}

function synchronizeConcours($year = "")
{
  $log = "";

  if($year == "")
    $year = date("Y");

  //  if($year==2015) $year=2016;

  /*liste des sesessions */
  $sessions = array();

  $sql = "SELECT * FROM ".sessions_db." WHERE id=\"Concours".$year."\"";
  $result = sql_request($sql);
  while($row = mysqli_fetch_object($result))
      $sessions[] = $row->section;

  /*************** AJOUT DES CONCOURS ***********************************/
  $sql = "SELECT * FROM ".dsidbname.".".celcc_concours." WHERE annee=\"".$year."\"";
  $result = sql_request($sql);
  while($row = mysqli_fetch_object($result))
    {
      /* special hack for formations */
      if($row->annee == "2015") $row->annee = "2016";
      //      $year=$row->annee;
      $dsi_concours[$row->n_public] = $row;
      $section = ltrim($row->numsect_conc,'0');
      if(!in_array($section,$sessions))
	{
try
  {
	  $log .= "Création de la session de concours ".$year." pour la section ".$section."<br/>";
	  createSession("Concours",$row->annee, $section);
	  $sessions[] = $section;
  }catch(Exception $e){};
	}
    }
  $result = sql_request($sql);

  $sql = "SELECT * FROM ".marmottedbname.".concours WHERE session=\"Concours".$year."\"";
  $result2 = sql_request($sql);
  $all_concours = array();
  while($row = mysqli_fetch_object($result2))
      $all_concours[$row->code] = $row;

  while($row = mysqli_fetch_object($result))
    {
      //      $code = str_replace("/","",$row->n_public);
      $code = $row->n_public;
      if(!isset($all_concours[$code]))
	{
	  $sec = ltrim($row->numsect_conc, '0');
	  $msg = "Ajout du concours ".$code." de la section ".$sec."<br/>\n";
	  $log .= $msg;
	  //echo $msg;
	  $sql = "INSERT INTO ".marmottedbname.".concours (section,session,code,intitule,statut) ";
	  $sql .= "VALUES (\"".$sec."\",\"Concours".$row->annee."\",";
	  $sql .= "\"".$code."\",\"\",\"IE\")";
	  sql_request($sql);
	}
    }

  /* AJOUT DES CANDIDATS  ET CANDidATURES */

  // abandon des NOT IN car trop lent à la place on précalcule des tableaux
  //  $sql = "SELECT * FROM ".dsidbname.".".celcc_candidats;
  //  $sql .= " WHERE user_id NOT IN (SELECT DISTINCT concoursid FROM ".marmottedbname.".people WHERE concoursid!=\"\")";

  /* mse a jour des candidats ayant changé de nom */
  $sql = "UPDATE ".marmottedbname.".".peopledb." marmotte, ".dsidbname.".".celcc_candidats." dsi ";
  $sql .= "SET marmotte.nom=dsi.nom,marmotte.prenom =dsi.prenom ON marmotte.concoursid=dsi.user_id";
  sql_request($sql);

  /* calcul des candidats déjà connus */
  $sql = "SELECT concoursid,section FROM ".marmottedbname.".people WHERE concoursid!=\"\"";
  $result = sql_request($sql);
  $candidatsids = array();
  while($row = mysqli_fetch_object($result))
    {
      if(!isset($candidatsid[$row->concoursid]))
	$candidatsid[$row->concoursid] = array();
      $candidatsid[$row->concoursid][]= $row->section; 
    }
  
  /* calcul des candidatures déjà connues */
  $sql = "SELECT concoursid, concours FROM ".marmottedbname.".reports WHERE concoursid!=\"\"";
  $result = sql_request($sql);
  $candidatures = array();
  while($row = mysqli_fetch_object($result))
    {
      if(!isset($candidatures[$row->concours]))
	$candidatures[$row->concours] = array();
      $candidatures[$row->concours][] = $row->concoursid;
    }

  /* cle unique cote dsi userid + concours */
  $sql = "SELECT * FROM ".dsidbname.".".celcc_candidatures." candidatures ";
  $sql .= "LEFT JOIN ".dsidbname.".".celcc_candidats." candidats ON candidatures.user_id=candidats.user_id WHERE 1";
  
  $result = sql_request($sql);
  while($row = mysqli_fetch_object($result))
    {
      //si la candidature est déjà connue, on ignore
      if(isset($candidatures[$row->num_conc]) && in_array($row->user_id,$candidatures[$row->num_conc]))
	continue;

      $section = ltrim( substr($row->num_conc,0,2), '0');      

      //ajout du candidat si nécessaire, ou mise à jour du concoursid si déjà connu
      if(!isset($candidatsids[$row->user_id]) || !in_array($section, $candidatsids[$row->user_id]))
	{
	  if(!isset($candidatsids[$row->user_id]))
	    $candidatsids[$row->user_id] = array();
	  $candidatsids[$row->user_id][] = $section;

	  $msg = "Ajout du candidat ".$row->prenom." ".$row->nom." de user_id ".$row->user_id." a la section ".$section."<br/>";
	  //  echo $msg;
	  $log .= $msg;
	  $genre = ($row->titre == "Monsieur") ? "homme" : "femme";
	  $sql = "INSERT INTO ".marmottedbname.".".people_db." (concoursid,section,nom,prenom,genre,diploma,birth) ";
	  $sql .= "VALUES (\"".$row->user_id."\",\"".$section."\",\"".ucfirst(strtolower($row->nom))."\",\"".ucfirst(strtolower($row->prenom));
	  $sql .= "\",\"".$genre."\",\"".$row->date_dip."\",\"".$row->datnaiss."\") ";
	  $sql .= "ON DUPLICATE KEY UPDATE genre=\"".$genre."\", concoursid=\"".$row->user_id."\",";
	  $sql .= "birth=\"".$row->datnaiss."\", diploma=\"".$row->date_dip."\"";
	  sql_request($sql);		
	}

      $msg .= "Ajout de la candidature de ".$row->prenom." ".$row->nom." de user_id ".$row->user_id." concours ".$row->num_conc."<br/>";
      //      echo $msg;
      $log .= $msg;

      $sql = "INSERT INTO ".marmottedbname.".".reports_db." (concoursid,type,section,avis,concours,id_session,grade_rapport,nom,prenom) ";
      $sql .= "VALUES (";
      $sql .= "\"".$row->user_id."\",\"".REPORT_CANDIDATURE."\",\"".$section."\",";
      $sql .= "\"\",";
      $sql .= "\"".$row->num_conc."\",";
      $sql .= "\"Concours".$dsi_concours[$row->num_conc]->annee."\",";
      $sql .= "\"".$dsi_concours[$row->num_conc]->grade_conc."\",";
      $sql .= "\"".ucfirst(strtolower($row->nom))."\",\"".ucfirst(strtolower($row->prenom))."\")";
      sql_request($sql);
      global $dbh;
      $new_id = mysqli_insert_id($dbh);
      $sql = "UPDATE ".marmottedbname.".".reports_db." SET id_origine=id WHERE id=\"".$new_id."\"";
      sql_request($sql);
    }
  $log .= synchronizeStatutsConcours($year);


  
  return $log;
}


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
					$result .= "Suite à un changement d'email, migration des dossers de '".$user->login."' vers '".$row->mailpro;
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
	  return "";
	//		$result .= "Aucun email n'a &eacute;t&eacute; mis &egrave; jour.<br/>";
	return $result;
}


function synchronizeWithDsiMembers($section,$email = true)
{
	$result = "";
	$users = listUsers(true, $section);

	$result .= synchronizeEmailsUpdates($email);
	$result .= "<br/><B>Synchronisation des membres de la section</B><br/>\n";

	global $dbh;

	/* effacement des utilissateurs ayant disparu du référentiel dsi */	
	$sql = "DELETE FROM ".users_db." WHERE (section_numchaire = \"".$section."\" OR CID_numchaire = \"".$section."\")";
	$sql .= " AND login NOT IN (SELECT mailpro FROM ".dsidbname.".".dsi_users_db.")";
	$res = sql_request($sql);
	$num = mysqli_affected_rows($dbh);
	if($num> 0)
	  $result .= $num . "membre(s) importé(s) de Ambre vers Marmotte ont été supprimés de Ambre donc de Marmotte<br/>\n";
	

	//	if (isSuperUser())
	//	$sql = "SELECT * FROM ".dsidbname.".".dsi_users_db." WHERE 1;";
	//else

	/* mise a jour ou ajout des membres de la section */
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
			  $added = true;
				$result .= "Ajout du compte ".$login." &agrave; la base marmotte.<br/>";
				$sql = "INSERT INTO ".users_db;
				$sql .= " (login,sections,permissions,section_code,college,section_role_code,CID_code";
				$sql .= ",CID_role_code,section_numchaire,CID_numchaire, passHash,description,email,tel,dsi) ";
				$sql .= "VALUES ('";
				$sql .= real_escape_string($login)."','','0','".$row->section_code."','";
				$sql .= $row->college_code."','";
				$sql .= $row->section_role_code."','".$row->CID_code."','".$row->CID_role_code."','";
				$sql .= $row->section_numchaire."','".$row->CID_numchaire."','','";
				$sql .= real_escape_string($row->nom." ".$row->prenom)."','".$login."','','1');";
				sql_request($sql);
			}
		}
		catch(Exception $exc)
		{
			$result .= "Erreur: ".$exc->getMessage()."<br/>\n";
		}
	}
	if(!$added && !$changed)
	  $result = "";
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
	  $answer = "";//.= "Aucune session n'a &egrave;t&egrave; ajout&egrave;e.<br/>";
	sessionArrays(true);
	return $answer;
}

function synchronizePeople($section)
{
  $answer = "";
	$sql =  "UPDATE ".people_db." marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ";
	$sql .= " ON marmotte.nom=dsi.nom AND marmotte.prenom=dsi.prenom";
	$sql .= " SET marmotte.NUMSIRHUS=dsi.numsirhus";
	$sql .= " WHERE section=\"".$section."\" AND marmotte.NUMSIRHUS=\"\";";
	$res = sql_request($sql);
	global $dbh;
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
	  {
	    $answer = "<B>Synchro des num SIRHUS de chercheurs de la section ".$section."</B><br/>\n";
	  $answer .= $num." num&eacute;ros SIRHUS ont &eacute;t&eacute; mis &agrave; jour.<br/>";
	  }

	/*	$sql =  "UPDATE ".people_db." marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ";
	$sql .= " ON marmotte.NUMSIRHUS=dsi.numsirhus";
	$sql .= " SET marmotte.nom=dsi.nom, marmotte.prenom=dsi.prenom";
	$sql .= " WHERE marmotte.NUMSIRHUS!='' AND section=\"".$section."\" AND marmotte.nom=\"\";";
	$res = sql_request($sql);
	global $dbh;
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
	  {
	    $answer .= "<B>Synchro des noms et prénoms de chercheurs de la section ".$section."</B><br/>\n";
	  $answer .= $num." nom et prenoms ont &eacute;t&eacute; mis &agrave; jour.<br/>";
	  }
	*/

	$sql =  "DELETE marmotte FROM ".people_db." marmotte LEFT JOIN ".dsidbname.".".dsi_people_db." dsi ";
	$sql .= " ON marmotte.NUMSIRHUS=dsi.numsirhus";
	$sql .= " WHERE marmotte.NUMSIRHUS!='' AND section=\"".$section."\" AND marmotte.nom!=dsi.nom AND marmotte.prenom!=dsi.prenom;";
	$res = sql_request($sql);

	$sql =  "UPDATE ".people_db." marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ";
	$sql .= " ON marmotte.NUMSIRHUS=dsi.numsirhus";
	$sql .= " SET marmotte.labo1=dsi.code_unite";
	$sql .= " WHERE marmotte.NUMSIRHUS!='' AND section=\"".$section."\" AND dsi.code_unite!='' AND  marmotte.labo1!=dsi.code_unite;";
	$res = sql_request($sql);
	global $dbh;
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
	  {
	    $answer .= "<B>Synchro des unit&eacute;s des chercheurs de la section ".$section."</B><br/>\n";
	  $answer .= $num." unit&eacute;(s) ont &eacute;t&eacute; mises &agrave; jour.<br/>";
	  }

	$sql =  "UPDATE ".people_db." marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ";
	$sql .= " ON marmotte.NUMSIRHUS=dsi.numsirhus";
	$sql .= " SET marmotte.grade=dsi.grade";
	$sql .= " WHERE  marmotte.NUMSIRHUS!='' AND section=\"".$section."\" AND dsi.grade!='' AND  marmotte.grade!=dsi.grade;";
	$res = sql_request($sql);
	global $dbh;
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
	  {
	    $answer .= "<B>Synchro des grades des chercheurs de la section ".$section."</B><br/>\n";
	  $answer .= $num." grades ont &eacute;t&eacute; mis &agrave; jour.<br/>";
	  }


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

	$changed = false;

	//Recherche des DE qui ont changé de section
	$sql = "SELECT * FROM ".marmottedbname.".".reports_db." marmotte ";
        $sql .= " JOIN ".dsidbname.".".dsi_evaluation_db." dsi ";
        $sql .= " ON dsi.DKEY=marmotte.DKEY ";
	$sql .= "WHERE marmotte.section='".$section."'";
	$sql .= "AND marmotte.id_session='".$session."'";
	$sql .= "AND dsi.CODE_SECTION !='".$section."'";
	$sql .= "AND dsi.CODE_SECTION_2 !='".$section."'";;
	$sql .= "AND dsi.CODE_SECTION_EXCPT !='".$section."';";
	$result = sql_request($sql);
	global $typesRapportsAll;
	while($row = mysqli_fetch_object($result))
	  {
	    $changed = true;
		$row->type = $row->TYPE_EVAL;
		$type = isset($typesRapportsAll[$row->type]) ? $typesRapportsAll[$row->type] : $row->type;	
		$answer .= "La DE chercheur ".$row->DKEY." de la session ".$session." de type '".$type;
		$answer .= "' pour le chercheur '".$row->nom." ".$row->prenom."' a &eacute;t&eacute; retir&eacute;e de e-valuation ";
		$answer .= "pour la section ".$section.".";
		$answer .= "Veuillez supprimer manuellement cette DE de Marmotte et pr&eacute;venir le bureau et les rapporteurs.<br/>\n";
	  }

	//$sql .= " AND marmotte.DKEY=\"\" AND";
	//$sql .= " (`dsi.CODE_SECTION` =\"".$section."\" OR `dsi.CODE_SECTION_2`=\"".$section."\" OR `dsi.CODE_SECTION_EXCPT`=\"".$section." \
	//\";";
	
       

	$sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_db." AS eval";
	$sql .= " LEFT JOIN ".dsidbname.".".dsi_people_db." AS people";
	$sql .= " ON eval.NUMSIRHUS=people.numsirhus WHERE ";
	//$sql .= "(DKEY NOT IN (SELECT DKEY FROM ".marmottedbname.".".reports_db.")) AND ";
	$sql .= " eval.EVALUATION_CN=\"Soumis\" AND (eval.ETAT_EVAL=\"En cours\" OR eval.ETAT_EVAL=\"Terminée\")";
	$sql .= " AND  eval.LIB_SESSION=\"".$lib."\" AND eval.ANNEE=\"".$year."\" AND ";
	$sql .= " (eval.CODE_SECTION =\"".$section."\" OR eval.CODE_SECTION_2=\"".$section."\" OR eval.CODE_SECTION_EXCPT=\"".$section."\");";
	
	$result = sql_request($sql);
	
	//	$answer .= "La base dsi contient ".mysqli_num_rows($result);
	//$answer .= " DE chercheurs pour la section ".$section." et la session ".$session." qui n'apparaissent pas encore dans Marmotte.<br/>\n";

	while($row = mysqli_fetch_object($result))
	{
	  $row->unite = $row->code_unite;

		/* mises a jour des données du chercheur concerné */
		$sql  = "UPDATE ".people_db;
		$sql .= " SET labo1=\"".$row->code_unite."\", NUMSIRHUS=\"".$row->NUMSIRHUS."\"";
		$sql .= " WHERE LOWER(nom)=LOWER(\"".$row->nom."\")";
		$sql .= " AND LOWER(prenom)=LOWER(\"".$row->prenom."\")";
		$sql .= " AND section=\"".$section."\";";
		$res = sql_request($sql);
		global $dbh;
		$num = mysqli_affected_rows($dbh);
		/* et création du chercheur concerné si nécesaire */
		if($num == 0)
		  {
			$sql  = "INSERT INTO ".people_db;
			$sql .= " (NUMSIRHUS,labo1,nom,prenom,section) VALUES ";
			$sql .= "(\"".$row->NUMSIRHUS."\",\"".$row->code_unite."\",\"".$row->nom."\",\"".$row->prenom."\",\"".$section."\");";
			try
			  { 
			  sql_request($sql);
			}
			catch(Exception $e)
			  {}
		  }

	  $sql = "SELECT * FROM ".marmottedbname.".".reports_db." WHERE DKEY=".$row->DKEY." AND section=\"".$section."\";";
	  $res2 = sql_request($sql);
	  if(mysqli_num_rows($res2) == 0)
	    {
		$row->id_origine=0;
		$row->id_session = $session;
		$row->section = $section;

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
		
		$new_id = addReportToDatabase($row);
		$new_report = getReport($new_id);

		global $typesRapportsAll;
		$type = isset($typesRapportsAll[$row->type]) ? $typesRapportsAll[$row->type] : $row->type;	
		$answer .= "Ajout de la DE ".$row->DKEY." de type '".$type;
		$answer .= "' pour la session '".$session."' ";
		$answer .= "et le chercheur '".$new_report->nom." ".$new_report->prenom."' de NUMSIRHUS '".$row->NUMSIRHUS."'<br/>\n";
		$changed = true;

	        }
	}
	if(!$changed) $answer = "";
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
	
	
	//Recherche des DE qui ont changé de section
	$sql = "SELECT * FROM ".marmottedbname.".".reports_db." marmotte ";
        $sql .= " JOIN ".dsidbname.".".dsi_evaluation_units_db." dsi ";
        $sql .= " ON dsi.DKEY=marmotte.DKEY ";
	$sql .= "WHERE marmotte.section='".$section."'";
	$sql .= "AND marmotte.id_session='".$session."'";
	$sql .= "AND dsi.CODE_SECTION1 != '".$section."'";
	$sql .= "AND dsi.CODE_SECTION2 != '".$section."'";
	$sql .= "AND dsi.CODE_SECTION3 != '".$section."'";
	$sql .= "AND dsi.CODE_SECTION4 != '".$section."'";
	$sql .= "AND dsi.CODE_SECTION5 != '".$section."'";
	$sql .= "AND dsi.CODE_SECTION6 != '".$section."'";
	$sql .= "AND dsi.CODE_SECTION7 != '".$section."'";
	$sql .= "AND dsi.CODE_SECTION8 != '".$section."'";
	$sql .= "AND dsi.CODE_SECTION9 != '".$section."';";
	$result = sql_request($sql);
	global $typesRapportsAll;
	while($row = mysqli_fetch_object($result))
	  {
	    $changed = true;
		$row->type = $row->TYPE_EVAL;
		$type = isset($typesRapportsAll[$row->type]) ? $typesRapportsAll[$row->type] : $row->type;	
		$answer .= "La DE unit&eacute; ".$row->DKEY." de la session ".$session." de type '".$row->type;
		$answer .= "' pour l'unit&eacute, '".$row->unite."' a &eacute;t&eacute; retir&eacute;e de e-valuation, ";
		$answer .= " pour la section ".$section.".";
		$answer .= "Veuillez supprimer manuellement cette DE de Marmotte et prévenir le bureau et les rapporteurs.<br/>\n";
		$changed = true;
	  }

	/* import des DE non existentes */
	$sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_units_db;
	$sql .=" WHERE ";
	$sql .= " EVALUATION_CN=\"Soumis\" AND (ETAT_EVAL=\"En cours\" OR ETAT_EVAL=\"Terminée\")";
	$sql .= " AND LIB_SESSION=\"".$lib."\" AND ANNEE=\"".$year."\" AND ";
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

	$n = mysqli_num_rows($res);
	if($n > 0)
	  {
	$answer .= "La base dsi contient ".$n;
	$answer .= " DE unit&eacute;s qui n'apparaissent pas encore dans Marmotte.<br/>\n";
	  }
	else
	  {
	    return "";
	  }
	global $typesRapportsAll;
	while($row = mysqli_fetch_object($res))
	  {
		$row->unite = $row->UNITE_EVAL;
		$row->type = $row->TYPE_EVAL;

		$type = isset($typesRapportsAll[$row->type]) ? $typesRapportsAll[$row->type] : $row->type;	
		$answer .= "Ajout de la DE ".$row->DKEY." de type '".$type."' pour l'unité '".$row->unite."'<br/>\n";

		$row->id_session = $session;
		$row->section = $section;
		$row->id_origine=0;

		for($i = 1; $i <= 9; $i++)		{
			$field = "CODE_SECTION".$i;
			if($section == $row->$field)
			{
				$field = "AVIS".$i;
				$row->avis = $row->$field;
				$field = "RAPPORTEUR".$i."1";
				$row->rapporteur = getEmailFromChaire($row->$field);
				$field = "RAPPORTEUR".$i."2";
				$row->rapporteur2 = getEmailFromChaire($row->$field);
			break;
			}
		}

		addReportToDatabase($row);
	}

	/* synchronizing school, conference and revues naes */
	$sql= "UPDATE ".marmottedbname.".".reports_db." marmotte JOIN ".dsidbname.".".dsi_evaluation_units_db." dsi ON ";
	$sql.= "marmotte.DKEY=dsi.DKEY SET marmotte.nom=dsi.TITRE, marmotte.prenom=dsi.RESP_PRINCIPAL WHERE ";
	$sql .= "(dsi.TYPE_EVAL=\"8515\" OR dsi.TYPE_EVAL=\"8510\" OR dsi.TYPE_EVAL=\"8505\")";
	$sql .= " AND marmotte.nom=\"\" AND marmotte.prenom=\"\" AND marmotte.section=".$section.";";
	sql_request($sql);

	global $dbh;
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
	  $answer .= "Synchronization du nom des &eacute;coles et revues:".$num." DE affectées<br/>"; 

	return $answer;
}

function export_to_evaluation($section)
{
  //$answer = "<B>Export des avis et rapporteurs vers e-valuation</B><br/>";
		$sql = "DELETE FROM ".dsidbname.".".dsi_marmotte_db." WHERE CODE_SECTION=\"".$section."\"";
		sql_request($sql);

		$sql = "insert into ".dsidbname.".".dsi_marmotte_db."(DKEY,AVIS_EVAL,CODE_SECTION,RAPPORTEUR1,RAPPORTEUR2,RAPPORTEUR3,statut)";
		$sql .=" select DKEY,avis,section,rapporteur,rapporteur2,rapporteur3,statut from ".marmottedbname.".".reports_db;
		$sql .=" WHERE DKEY!=\"\" AND id_origine=id AND (statut=\"avistransmis\" OR statut=\"publie\") AND section=\"".$section."\" ";
		$sql .=" ON DUPLICATE KEY UPDATE";
		$sql .=" ".dsidbname.".".dsi_marmotte_db.".AVIS_EVAL=".marmottedbname.".".reports_db.".avis,";
		$sql .=" ".dsidbname.".".dsi_marmotte_db.".RAPPORTEUR1=".marmottedbname.".".reports_db.".rapporteur,";
		$sql .=" ".dsidbname.".".dsi_marmotte_db.".RAPPORTEUR2=".marmottedbname.".".reports_db.".rapporteur2,";
		$sql .=" ".dsidbname.".".dsi_marmotte_db.".RAPPORTEUR3=".marmottedbname.".".reports_db.".rapporteur3,";
		$sql .=" ".dsidbname.".".dsi_marmotte_db.".statut=".marmottedbname.".".reports_db.".statut";
		sql_request($sql);
		//		$answer .= "<a href=\"https://www.youtube.com/watch?v=0rCrc6RoJg0\">Ca l'effectue</a>";
		return "";
}

function check_missing_data()
{
  //  echo "Checking missing data"; 

  $msg = "";

  $sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_db." WHERE NUMSIRHUS != \"\" AND NUMSIRHUS NOT IN  (SELECT `numsirhus` FROM ".dsidbname.".".dsi_people_db.")";
  $sql .= " AND `EVALUATION_CN`=\"Soumis\" AND (ETAT_EVAL=\"En cours\" OR ETAT_EVAL=\"Terminée\");";  
  $result = sql_request($sql);
  while($row = mysqli_fetch_object($result))
    {
      $msg .= "La DE chercheur de DKEY '".$row->DKEY."' pour la session ".$row->LIB_SESSION." ".$row->ANNEE."est associée au chercheur de NUMSIRHUS '".$row->NUMSIRHUS."'";
      $msg .= " qui n'apparaît pas dans la base ".dsidbname.".".dsi_people_db."<br/>\n";
    }

  $sql = "SELECT * FROM ".dsidbname.".".dsi_docs_db." WHERE 1;";
  $result = sql_request($sql);
  $missing = array();
  $total = 0;
  //  global $dossier_stockage_dsi;
  $dossier_stockage_dsi = "/home/gimbert/Panda/storage/evaluation";
  while($row = mysqli_fetch_object($result))
    {
      $total++;      
      $file = $dossier_stockage_dsi."/".$row->path_sas."/".$row->nom_document;
      if(!file_exists($file))
      	$missing[$row->dkey] = $file;
    }
  if(count($missing) > 0)
    {
      $msg .= "\n\nLa table ".dsidbname.".".dsi_docs_db." contient ".$total." liens vers des documents pdfs ";
      $msg .= " dont ".count($missing)." sont inaccessibles depuis Marmotte.\n<br/>";
      //foreach($missing as $dkey => $link)
      //      $msg.= "DKEY ".$dkey." ".$link."<br/>\n";
    }

  $sql = "SELECT * FROM ".dsidbname.".".celcc_docs." WHERE 1;";
  $result = sql_request($sql);
  $missing = array();
  $total = 0;
  while($row = mysqli_fetch_object($result))
    {
      $total++;      
      $file = $dossier_stockage_dsi."/".$row->path_sas."/".$row->nom_doc;
      if(!file_exists($file))
      	$missing[] = $file;
    }
 if(count($missing) > 0)
    {
      $msg .= "\n\nLa table ".dsidbname.".".celcc_docs." contient ".$total." liens vers des documents pdfs ";
      $msg .= " dont ".count($missing)." sont inaccessibles depuis Marmotte.\n<br/>";
      //foreach($missing as $dkey => $link)
      //      $msg.= "DKEY ".$dkey." ".$link."<br/>\n";
    }
  /*
  $sql = "SELECT * FROM ".dsidbname.".".dsi_evaluation_units_db." WHERE UNITE_EVAL NOT IN  (SELECT `CODEUNITE` FROM ".dsidbname.".".dsi_units_db.")";
  $sql .= " AND `EVALUATION_CN`=\"Soumis\" AND (ETAT_EVAL=\"En cours\" OR ETAT_EVAL=\"Terminée\");";  
  $result = sql_request($sql);
  while($row = mysqli_fetch_object($result))
    {
      $msg .= "La DE unité de DKEY '".$row->DKEY."' est associée à l'unité '".$row->UNITE_EVAL."'";
      $msg .= " qui n'aparaît pas dans la base ".dsidbname.".".dsi_units_db.".<br/>\n";
    }
  */
  return $msg;
}

function synchronize_units()
{

}

function synchronize_colleges()
{
  global $dbh;
  $answer = "";
	/* synchro des collèges */
	$num = 0;
	$sql = "UPDATE ".users_db." marmotte JOIN ".dsidbname.".".dsi_users_db." dsi SET ";
	$sql .= "marmotte.college=dsi.section_college_code ";
	$sql .= "WHERE marmotte.section_numchaire=dsi.section_numchaire AND dsi.section_numchaire != '' AND dsi.college_code='';";
	sql_request($sql);
	$num += mysqli_affected_rows($dbh);

	$sql = "UPDATE ".users_db." marmotte JOIN ".dsidbname.".".dsi_users_db." dsi SET ";
	$sql .= "marmotte.college=dsi.CID_acces_college_code ";
	$sql .= "WHERE marmotte.CID_numchaire=dsi.CID_numchaire AND dsi.CID_numchaire != '' AND dsi.college_code=''";
	sql_request($sql);
	$num += mysqli_affected_rows($dbh);

	$sql = "UPDATE ".users_db." marmotte JOIN ".dsidbname.".".dsi_users_db." dsi SET ";
	$sql .= "marmotte.college=dsi.college_code ";
	$sql .= "WHERE (";
	$sql .= "(marmotte.CID_numchaire=dsi.CID_numchaire AND dsi.CID_numchaire != '')";
	$sql .= " OR ";
	$sql .= "(marmotte.section_numchaire=dsi.section_numchaire AND dsi.section_numchaire != '')";
	$sql .= " ) AND dsi.college_code!=''";
	sql_request($sql);
	$num += mysqli_affected_rows($dbh);

	if($num> 0)
	  $answer .= $num . " collèges de membres ont été mis à jour<br/>\n";

  return $answer;
}

/* performs synchro with evaluation and returns diagnostic , empty string if nothing happaned */
function synchronize_with_evaluation($section = "", $recursive = false, $email = true)
{
  $answer = "";
  //  echo "synchronize_with_evaluation '".$section."' '".$recursive."' '".$email."'\n"; 

  if(isSuperUser() && isset($_SESSION["answer_dsi_sync"]) && !$recursive)
    echo $_SESSION["answer_dsi_sync"];

  if(isSuperUser() && $section == "")
    {
try
  {
	$report = check_missing_data();
	if($report != "")
	  {
	    $emails = array(
			    "hugo.gimbert@cnrs.fr",
			    //			    "Laurent.CHAZALY@cnrs-dir.fr",
			    //"velazquez@icmcb-bordeaux.cnrs.fr",
			    "Rene.PELFRESNE@dsi.cnrs.fr",
			    "Wandrille.MEYRAND@dsi.cnrs.fr"
			    //"Marie-Claude.LABASTIE@cnrs-dir.fr"
			    );
	    foreach($emails as $email)
	       email_handler($email,"Alerte Marmotte: données manquantes",$report,email_sgcn,email_admin);
	  }

	/* mise a jour des meta données pour les DE unités déjà importées dans marmotte */
	$sql = "UPDATE ".marmottedbname.".".reports_db." marmotte JOIN ".dsidbname.".".dsi_evaluation_units_db." dsi ";
	$sql .= "ON marmotte.DKEY=dsi.DKEY ";
	$sql .= "SET marmotte.unite=dsi.UNITE_EVAL, marmotte.type=dsi.TYPE_EVAL ";
	$sql .= " WHERE ";
	$sql .= " dsi.EVALUATION_CN=\"Soumis\" AND (dsi.ETAT_EVAL=\"En cours\" OR dsi.ETAT_EVAL=\"Terminée\")";
	sql_request($sql);
	global $dbh;
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
	  $answer .= "Les meta données de ".$num." DE unités déjà existantes dans la base marmotte ont été mises à jour<br/>"; 

	/* mise a jour des meta données pour les DE chercheurs déjà importées dans marmotte */
	$sql = "UPDATE ".marmottedbname.".".reports_db." marmotte JOIN ".dsidbname.".".dsi_evaluation_db." dsi ";
	$sql .= "ON marmotte.DKEY=dsi.DKEY ";
	$sql .= "SET marmotte.NUMSIRHUS=dsi.NUMSIRHUS, marmotte.type=dsi.TYPE_EVAL ";
	$sql .= " WHERE ";
	$sql .= " dsi.EVALUATION_CN=\"Soumis\" AND (dsi.ETAT_EVAL=\"En cours\" OR dsi.ETAT_EVAL=\"Terminée\")";
	sql_request($sql);
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
	  $answer .= "Les meta données de ".$num." DE chercheurs déjà existantes dans la base marmotte ont été mises à jour<br/>"; 

	/* synchro des colleges */
	$answer .=synchronize_colleges();

	/* suppression des numsirhus dupliqués 
	$sql = "CREATE TABLE new_table as SELECT * FROM ".people_db." WHERE 1 GROUP BY section,NUMSIRHUS";
	sql_request($sql);
	$sql = "DROP TABLE ".people_db;
	sql_request($sql);
	$sql = "RENAME TABLE new_table TO ".people_db;
	sql_request($sql);*/

  }
catch(Exception $e)
  {
    echo "Error: ".$e->getMessage();
    
    return $e->getMessage();
  }

	return $report."<br/>\n".synchronize_with_evaluation("1",$recursive,$email);
    }

  if(!isSuperUser())
    $section = currentSection();

try
  {
	if(isSecretaire())
	{
	  $log = synchronizeWithDsiMembers($section,$email);
	  if($log != "")
	    $answer .= $log."<br/>";

		$answer .= synchronizeSessions($section);

		$new_sessions = explode(";",get_config("sessions_synchro"));
		
		$log = synchronizePeople($section);
		if($log != "")
		  $answer .= $log."<br/>";
		
		//		$answer .= "<B>Suppression de l'historique des rapports</B><br/>\n";
		$sql = "DELETE FROM ".reports_db." WHERE id!=id_origine AND section=\"".$section."\";";
		sql_request($sql);

		foreach($new_sessions as $session)
		  if($session != "")
		  {
		    $log = synchronizePeopleReports($section,$session);
		    if($log != "")
		      $answer .= $log."<br/>";
		    $log = synchronizeUnitReports($section,$session);
		    if($log  != "")
		      $answer .= $log."<br/>";
		  }		  
		

		$answer.= export_to_evaluation($section);
	}
  }
catch(Exception $e)
  {
    echo $e->getMessage();
    $answer .=  "Failed to synchronize\n<br/>".$e->getMessage()."\n<br/>"; 
  }

if($answer != "")
  {
    $body = html_entity_decode($answer);
    $subject = "Synchronisation quotidienne Marmotte/e-valuation: section ".$section;
    $emails = emailsACN($section);
    foreach($emails as $email)
      email_handler($email,$subject,$body,email_sgcn,email_admin);		
    
  }


$answer = "<h1>Synchronisation avec e-valuation de la section ".$section." - ".date('d/m/Y - H:i:s')."</h1>\n".$answer;


	if(isSuperUser())
	  {
	    if($section > 55)
	      {
		$answer .= synchronizeConcours();
		$answer.= "<h2>Renommage des intitulés</h2>";
		$sql = "UPDATE ".marmottedbname.".".reports_db." SET `intitule`=\"Evaluation à vague de chercheurs\" WHERE `DKEY`IN";
		$sql .= "(SELECT DKEY FROM ".dsidbname.".".dsi_evaluation_db." WHERE `type`='".REPORT_EVAL."' AND `PHASE_EVAL`=\"vague\")";
		sql_request($sql);
		$sql = "UPDATE ".marmottedbname.".".reports_db." SET `intitule`=\"Evaluation à mi-vague de chercheurs\" WHERE `DKEY`IN";
		$sql.="(SELECT DKEY FROM ".dsidbname.".".dsi_evaluation_db." WHERE `type`='".REPORT_EVAL."' AND `PHASE_EVAL`=\"mi-vague\")";
		sql_request($sql);

		if(!$recursive)
		  unset($_SESSION["answer_dsi_sync"]);
		else
		  return "Done";
	      }
	    else if(!$recursive)
	      {
		if(!isset($_SESSION["answer_dsi_sync"])) $_SESSION["answer_dsi_sync"] = "";		
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
?>