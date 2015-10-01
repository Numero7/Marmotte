
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


	$sql =  "UPDATE ".reports_db." marmotte JOIN ".dsidbname.".".dsi_people_db." dsi ";
	$sql .= " ON marmotte.NUMSIRHUS=dsi.numsirhus";
	$sql .= " SET marmotte.nom=dsi.nom, marmotte.prenom=dsi.prenom";
	$sql .= " WHERE section=\"".$section."\" AND marmotte.nom=\"\";";
	$res = sql_request($sql);
	global $dbh;
	$num = mysqli_affected_rows($dbh);
	if($num > 0)
	  {
	    $answer .= "<B>Synchro des noms et prénoms de chercheurs de la section ".$section."</B><br/>\n";
	  $answer .= $num." nom et prenoms ont &eacute;t&eacute; mis &agrave; jour.<br/>";
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
	$sql .= " eval.EVALUATION_CN=\"Soumis\" AND (eval.ETAT_EVAL=\"En cours\" OR eval.ETAT_EVAL=\"Terminée\")";
	$sql .=" AND  eval.LIB_SESSION=\"".$lib."\" AND eval.ANNEE=\"".$year."\" AND ";
	$sql .=" (eval.CODE_SECTION =\"".$section."\" OR eval.CODE_SECTION_2=\"".$section."\" OR eval.CODE_SECTION_EXCPT=\"".$section."\");";
	
	$result = sql_request($sql);
	
	//	$answer .= "La base dsi contient ".mysqli_num_rows($result);
	//$answer .= " DE chercheurs pour la section ".$section." et la session ".$session."<br/>\n";// qui n'apparaissent pas encore dans Marmotte.<br/>\n";

	$changed = false;
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
		
		addReportToDatabase($row);

		global $typesRapportsAll;
		$type = isset($typesRapportsAll[$row->type]) ? $typesRapportsAll[$row->type] : $row->type;	
		$answer .= "Ajout de la DE ".$row->DKEY." de type '".$type;
		$answer .= "' pour le chercheur '".$row->nom." ".$row->prenom."' de NUMSIRHUS '".$row->NUMSIRHUS."'<br/>\n";
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
      $msg .= "La DE chercheur de DKEY '".$row->DKEY."' est associée au chercheur de NUMSIRHUS '".$row->NUMSIRHUS."'";
      $msg .= " qui n'apparaît pas dans la base ".dsidbname.".".dsi_people_db."<br/>\n";
    }

  $sql = "SELECT * FROM ".dsidbname.".".dsi_docs_db." WHERE 1;";
  $result = sql_request($sql);
  $missing = array();
  $total = 0;
  while($row = mysqli_fetch_object($result))
    {
      $total++;      
      $dossier_stockage_dsi = "/home/dsi/data/docs";
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

	$answer .=synchronize_colleges();

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
