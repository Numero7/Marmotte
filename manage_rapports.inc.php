<?php

require_once('manage_sessions.inc.php');
require_once('manage_unites.inc.php');
require_once("config.inc.php");


function getIDOrigine($id_rapport)
{
	$sql = "SELECT id_origine FROM ".evaluations_db." WHERE id=$id_rapport";
	$result=sql_request($sql);
	$report = mysql_fetch_object($result);
	if($report == false)
		throw new Exception("No report with id ".$id_rapport);
	else
		return $report->id_origine;
}

/*
 * returns an object
*/

function getReport($id_rapport, $most_recent = true)
{
	if($most_recent)
		$id_rapport = getIDOrigine($id_rapport);
	$sql = "SELECT * FROM ".evaluations_db." WHERE id=$id_rapport";
	$result=sql_request($sql);
	$report = mysql_fetch_object($result);

	if($report == false)
		throw new Exception("No report with id ".$id_rapport);
	else
		return normalizeReport($report);
}

function reportShortSummary($report)
{
	global $typesRapportsUnites;

	$nom = $report->nom;
	$prenom = $report->prenom;
	$grade = $report->grade;
	$unite = $report->unite;
	$type = $report->type;
	$session = "Session ".$report->id_session;

	if($type == "Promotion")
	{
		switch($grade)
		{
			case "CR2": $grade = "CR1"; break;
			case "DR2": $grade = "DR1"; break;
			case "DR1": $grade = "DRCE1"; break;
			case "DRCE1": $grade = "DRCE2"; break;
		}
		$grade .= " - ".$avis;
	}

	if(array_key_exists($type,$typesRapportsUnites))
		return $session." - ".$type." - ".$report->unite;
	else
		return $session." - ".$type." - ".$grade." - ".$nom."_".$prenom;

}


function getAllReportsOfType($type,$id_session=-1)
{

	if($id_session==-1)
		$id_session = current_session_id();
	$filter_values = array('type'=> $type,'id_session' => $id_session);

	return filterSortReports(getCurrentFiltersList(), $filter_values);
}

function filterSortReports($filters, $filter_values = array(), $sorting_values = array(), $rapporteur_or = true)
{

	$sql = "SELECT * FROM ".evaluations_db." WHERE id = id_origine AND statut!=\"supprime\"";
	//$sql = "SELECT * FROM ".evaluations_db." WHERE date = (SELECT MAX(date) FROM evaluations AS mostrecent WHERE mostrecent.id_origine = evaluations.id_origine AND statut!=\"supprime\")";
	//$sql = "SELECT * FROM ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date";
	//$sql = "SELECT * FROM evaluations WHERE (SELECT id, MAX(date) AS date FROM evaluations GROuP BY id_origine) AS X "
	//$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt INNER JOIN ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, sessions ss WHERE ss.id=tt.id_session ";
	//$sql = "SELECT * FROM evaluations WHERE 1 ";


	$sql .= filtersCriteriaToSQL($filters,$filter_values, $rapporteur_or);
	$sql .= sortCriteriaToSQL($sorting_values);
	$sql .= ";";
	//echo $sql;
	$result=sql_request($sql);

	if($result == false)
		throw new Exception("Echec de l'execution de la requete <br/>".$sql."<br/>");

	$rows = array();
	//echo $sql."<br/>".count($rows)." rows ".mysql_num_rows($result)." sqlrows<br/>";

	while ($row = mysql_fetch_object($result))
		$rows[] = $row;


	return $rows;
}


function sortCriteriaToSQL($sorting_values)
{
	$sql = "";

	foreach($sorting_values as $crit => $value)
	{
		if ($sql == "")
			$sql = "ORDER BY ";
		else
			$sql .= ", ";
		$sql .= $crit." ".( ( substr($sorting_values[$crit],strlen($sorting_values[$crit]) -1) == "+" ) ? "ASC" : "DESC");
	}
	if ($sql =="")
		$sql = "ORDER BY nom ";

	return $sql;
}

function filtersCriteriaToSQL($filters, $filter_values, $rapporteur_or = true)
{
	global $fieldsTypes;

	$sql = "";
	foreach($filters as $filter => $data)
	{
		if(isset($filter_values[$filter]) && (!isset($data['default_value']) || $filter_values[$filter] != $data['default_value']))
		{
			if($filter == "login_rapp" && $rapporteur_or)
			{
				//dirty hack to have an OR clause on rapporteurs...
				$val = $filter_values[$filter];
				$sql .= " AND (rapporteur=\"".$val."\" OR rapporteur2=\"".$val."\") ";
			}
			else if($rapporteur_or && $filter =="login_rapp2")
			{
				continue;
			}
			else if($filter == "avancement")
			{
				$login = "";
				if(isset($filter_values["login_rapp"]))
					$login = $filter_values["login_rapp"];

				//dirty hack tfor "Mes rapport sà faire/ faits"
				$val = $filter_values[$filter];

				if($login != "")
					$basesql = "( (rapporteur=\"$login\" AND avis1 != \"\") OR (rapporteur2=\"$login\" AND avis2 != \"\") OR  (rapporteur!=\"$login\" AND rapporteur2!=\"$login\")) ";
				else
					$basesql = "( (rapporteur=\"\" OR avis1 != \"\") AND (rapporteur2=\"\" OR avis2 != \"\")) ";


				if($val == "todo")
					$sql .= " AND NOT ".$basesql;
				else if($val == "done")
					$sql .= " AND ".$basesql;
			}
			else if($filter == "concours")
			{
				global $concours_ouverts;
				if(isset($filter_values[$filter]))
				{
					$val = $filter_values[$filter];
					$listeconcours = array();
					foreach($concours_ouverts as $code => $intitule)
						if($val == $code ||  strncmp($intitule, $val, strlen($val)) == 0)
						$listeconcours[] = $code;
					if(count($listeconcours) == 0)
						continue;
					$first = true;
					$sql .= " AND (";
					$field = (isset($data['sql_col']) ?  $data['sql_col'] : $filter); 
					foreach($listeconcours as $code)
					{
						$sql .= ($first ? "" : " OR ") . $field."=\"$code\" ";
						$first = false;
					}
					$sql .= " ) ";
				}
			}
			else if(isset($fieldsTypes[$filter]) && $fieldsTypes[$filter] == "avis" && $filter_values[$filter] == "classe")
			{
				$sql .= " AND $filter REGEXP \"^[0-9]\" ";
			}
			else
			{
				$sql .= " AND ". (isset($data['sql_col']) ?  $data['sql_col'] : $filter)."=\"$filter_values[$filter]\" ";
			}

		}
	}
	return $sql;
}

function getTriTypes($sorting_values)
{
	$result = array();
	$result[(count($sorting_values) + 5)."+"] = "";
	for($i = 1; $i < count($sorting_values) + 5 ; $i++)
	{
		$result[ $i."+"] = $i."+";
		$result[ $i."-"] = $i."-";
	}
	return $result;
}


function getStatus($id_rapport)
{
	$report = getReport($id_rapport);
	return $report->statut;
}

function isReportEditable($rapport)
{
	try
	{
		checkReportIsEditable($rapport);
		return true;
	}
	catch(Exception $exc)
	{
		return false;
	}
}

function checkReportIsEditable($rapport)
{
	$login = getLogin();

	if (isSecretaire())
	{
		return true;
	}
	else if($rapport->statut != "prerapport")
	{
		throw new Exception("Ce rapport n'a plus le statut de prerapport et n'est donc plus éditable par ses rapporteurs. Si nécessaire veuillez demander un changement de statut au secrétaire.");
	}
	else if( ($rapport->rapporteur != "") && ($rapport->rapporteur != $login) && ($rapport->rapporteur2 != $login))
	{
		if($rapport->rapporteur2 != "")
			throw new Exception("Les rapporteurs de ce rapport sont '".$rapport->rapporteur."' et '".$rapport->rapporteur2."' mais vous êtes loggés sous l'identité '".$login."'.<br/> Si nécessaire veuillez demander un changement de rapporteur au bureau.");
		else
			throw new Exception("Le rapporteur de ce rapport est '".$rapport->rapporteur."' mais vous êtes loggés sous l'identité '".$login."'.<br/> Si nécessaire veuillez demander un changement de rapporteur au bureau.");
	}
	else
	{
		return true;
	}
}

function checkReportDeletable($rapport)
{
	if (isSecretaire() && $rapport->statut == 'publie')
		throw new Exception("Les rapports publies ne sont pas supprimables, changer d'abord le statut du rapport");
	else if (isSecretaire())
		return true;
	else if( $rapport->rapporteur != getLogin())
		throw new Exception("Le rapporteur de ce rapport est ".$rapport->rapporteur." mais vous êtes loggés sous l'identité ".getLogin());
	else if ($rapport->statut != 'prerapport')
		throw new Exception("Ce rapport a le statut ".$rapport->statut." et n'est donc pas supprimable, seuls les prérapports sont supprimables par un rapporteur.");
	else
		return true;
}

function isReportCreatable()
{
	return isSecretaire();
	/*
	 if(!isSecretaire())
		throw new Exception("Vous n'avez pas les permissions nécessaires pour créer un rapport<br/>");
	else return true;
	*/
}

//to migrate from previous system
//UPDATE evaluations SET statut="supprime" WHERE id<0
//UPDATE evaluations SET id_origine=-id_origine WHERE id_origine<0
//UPDATE evaluations SET id=-id WHERE id<0

function deleteReport($id_rapport)
{
	$report = getReport($id_rapport);

	checkReportDeletable($report);

	$report = getReport($id_rapport);

	//Finding newest report before this one, if exists, and making it the newest
	$sql = "SELECT * FROM ".evaluations_db." WHERE date = (SELECT MAX(date) FROM evaluations AS mostrecent WHERE mostrecent.id_origine=$id_rapport AND mostrecent.id != $id_rapport AND mostrecent.statut!=\"supprime\")";
	$result= sql_request($sql);

	$before = mysql_fetch_object($result);
	if($before != false)
	{
		$previous_id = $before->id;
		$sql = "UPDATE ".evaluations_db." SET id_origine=".intval($previous_id)." WHERE id_origine=".intval($id_rapport)." ;";
		sql_request($sql);
		if(isset($_SESSION['rows_id']))
		{
			$rows_id = $_SESSION['rows_id'];
			for($i = 0; $i < count($rows_id); $i++)
			{
				if($rows_id[$i] == $id_rapport)
				{
					$_SESSION['rows_id'][$i] = $before->id;
					break;
				}
			}
		}
	}
	else
	{
		if(isset($_SESSION['rows_id']))
		{
			$rows_id = $_SESSION['rows_id'];
			for($i = 0; $i < count($rows_id); $i++)
			{
				if($rows_id[$i] == $id_rapport)
				{
					array_splice($_SESSION['rows_id'],$i,1);
					break;
				}
			}
		}
	}

	$sql = "UPDATE ".evaluations_db." SET statut=\"supprime\"WHERE id=".intval($id_rapport)." ;";
	sql_request($sql);
	$sql = "UPDATE ".evaluations_db." SET date=NOW() WHERE id=".intval($id_rapport)." ;";
	sql_request($sql);

	return ($before !=false) ? $before->id : -1;
};


function newReport($type_rapport)
{
	if(!isReportCreatable())
		throw new Exception("Vous n'avez pas les droits nécessaires à la création d'un rapport. Veuillez contacter le secrétaire scientifique.");

	$row = array();
	$row['type'] = $type_rapport;
	return normalizeReport($row);
} ;

function addReport($report)
{
	if(!isReportCreatable())
		throw new Exception("Le compte ".$login." n'a pas la permission de créer un rapport, veuillez contacter le secrétaire scientifique.");

	return addReportToDatabase($report);
};


function addReportFromRequest($id_origine, $request)
{
	global $typesRapportsConcours;

	if($id_origine != 0)
	{
		$report = getReport($id_origine);
		if(!checkReportIsEditable($report))
			throw new Exception("Le compte ".$login." n'a pas la permission de mettre à jour le rapport, veuillez contacter le bureau");
	}
	else if(!isReportCreatable())
		throw new Exception("Le compte ".$login." n'a pas la permission de créer un rapport, veuillez contacter le bureau");



	$report = createReportFromRequest($id_origine, $request);

	$id_nouveau = addReportToDatabase($report,false);

	if(in_array($report->type, $typesRapportsConcours))
		$candidate = updateCandidateFromRequest($request);

	return getReport($id_nouveau);
}

function createReportFromRequest($id_origine, $request)
{
	global $fieldsRapportAll;

	/* Hugo : I changed for the system we discussed
	 *
	* also we may have problems whn creating report
	*
	// Origin ID might have changed while report was being edited
	$id_origine = getIDOrigine($id_origine);
	$row = get_object_vars(getReport($id_origine));


	$type = "";
	if(isset($request["type"]))
		$type = $request["type"];

	*/
	$row = (object) array();

	$row->id_session = current_session_id();

	foreach($fieldsRapportAll as  $field => $comment)
		if (isset($request["field".$field]))
		$row->$field = nl2br(trim($request["field".$field]),true);

	$row->id_origine = $id_origine;
	$row->auteur = getLogin();


	if(isset($row->nom) && isset($row->prenom))
	{
		$annee = annee_from_data($row);
		$nom = $row->nom;
		$prenom = $row->prenom;
		$cle = generateKey($annee,$nom ,$prenom );
		$row->cleindividu = $cle;
	}

	return $row;

}



function normalizeReport($report)
{
	global $report_prototypes;
	global $empty_report;

	$report = (object) $report;

	if(!isset($report->id_session) || ($report->id_session == $empty_report['id_session']))
		$report->id_session = current_session_id();

	if(!isset($report->auteur) || ($report->auteur == $empty_report['auteur']))
		$report->auteur = getLogin();

	foreach($empty_report as $field => $value)
		if(!key_exists($field, $report))
		$report->$field = $value;

	if($report->statut == "vierge")
	{
		if(isset($report_prototypes[$report->type]))
		{
			$prototype = $report_prototypes[$report->type];
			foreach($prototype as $field => $value)
				if($report->$field=="")
				$report->$field = $value;
		}
		if($report->type == "Equivalence" && $report->prerapport=="")
		{
			//$report->prerapport = "Raison de la demande: ". $raison."\n";
			$report->prerapport .= "Annees d'équivalence annoncées par le candidat: ". $report->anneesequivalence;
		}
	}
	return $report;
}


function addReportToDatabase($report,$normalize = true)
{
	global $fieldsRapportAll;
	global $fieldsPermissions;


	if($normalize)
	 $report = normalizeReport($report);


	if(isset($report->statut) && $report->statut == "vierge" && $report->id_origine != 0)
		$report->statut = "prerapport";

	if(isset($report->unite))
		createUnitIfNeeded($report->unite);

	$specialRule = array("date","id","id_origine","auteur");


	mysql_query("LOCK TABLES evaluations WRITE;");

	$level = getUserPermissionLevel();
	try
	{

		$id_origine = isset($report->id_origine) ? $report->id_origine : 0;

		$current_report = array();
		try
		{
			$previous_report = getReport($id_origine, false);
			$current_report = getReport($id_origine);

			//echo "id_origine $id_origine current_id ".$current_report->id."<br/>";
			foreach($fieldsRapportAll as $field => $comment)
			{
				if (!in_array($field,$specialRule))
				{
					if(isset($report->$field) && isset($previous_report->$field) &&($previous_report->$field != $report->$field) && isset($fieldsPermissions[$field]) &&  $fieldsPermissions[$field] > $level)
						throw new Exception("Vous n'avez pas les autorisations nécessaires (".$level."<".$fieldsPermissions[$field].") pour modifier le champ ".$field);

					/*
					 if( isset($current_report->$field) && isset($previous_report->$field) && $previous_report->$field != $current_report->$field)
					 {
					echo "Parallel edition of field '".$field."' by '".$current_report->auteur."' changed from '".$previous_report->$field."' to '".$current_report->$field."'<br/>";
					}
					*/
					if( isset($report->$field) && isset($previous_report->$field) && $previous_report->$field != $report->$field)
					{
						if( isset($current_report->$field) && ($previous_report->$field != $current_report->$field)  && ($current_report->$field != $report->$field))
						{
							global $mergeableTypes;
							global $crashableTypes;

							global $fieldsTypes;
							$type = isset($fieldsTypes[$field]) ? $fieldsTypes[$field] : "";
							if(in_array($type, $mergeableTypes))
							{
								echo "<h2>Merge with parallel edition of field $field by '".$current_report->auteur."':</h2>'".$current_report->$field."'";
								echo "<h2>Your edition:</h2> '".$report->$field."'";
								$current_report->$field ="!!!MERGE!!!\n* FROM '".$current_report->auteur."':\n".$current_report->$field;
								$current_report->$field .= "\n* FROM '".getLogin()."':\n".$report->$field;
							}
							else if(!in_array($type, $crashableTypes))
							{
								echo "<h2>Cannot merge with parallel edition of field '$field' by '".$current_report->auteur."':</h2>";
								echo "<h2>Parallel edition:</h2>".$current_report->$field;
								echo "<h2>Your edition:</h2>".$report->$field;
								echo "<h2>Erasing parallel edition...</h2>";
								$current_report->$field = $report->$field;
							}
						}
						else
						{
							//echo "Updating field ".$field." was '".$previous_report->$field."' now '".$report->$field."'<br/>";
							$current_report->$field = $report->$field;
						}

					}
				}
			}
		}
		catch(Exception $e)
		{
			if(!isReportCreatable())
				throw new Exception("Failed to add report to database<br/>".$e->getMessage());
			//may happen if $id_origine is 0 for example (when reports are creating)
			$current_report = $report;
		}


		$sqlfields = "";
		$sqlvalues = "";

		$specialRule = array("date","id");

		$current_report->auteur = getLogin();

		$first = true;
		foreach($current_report as  $field => $value)
		{
			if (key_exists($field, $fieldsRapportAll) && !in_array($field,$specialRule))
			{
				$sqlfields.= ($first ? "" : ",").$field;
				$sqlvalues.=($first ? "" : ",")."\"".mysql_real_escape_string($value)."\"";
				$first = false;
			}
		}

		$sql = "INSERT INTO ".evaluations_db." ($sqlfields) VALUES ($sqlvalues);";
		sql_request($sql);

		$new_id = mysql_insert_id();

		if($id_origine != 0 && isset($current_report->id))
		{
			$current_id = $current_report->id;
			$sql = "UPDATE ".evaluations_db." SET id_origine=".intval($new_id)." WHERE id_origine=".intval($id_origine)." OR id=".intval($new_id)." OR id=".intval($current_id)." OR id_origine=".intval($current_id).";";
			//echo $sql;
			sql_request($sql);
		}
		else
		{
			$sql = "UPDATE ".evaluations_db." SET id_origine=".intval($new_id)." WHERE id=".intval($new_id).";";
			//echo $sql;
			sql_request($sql);
		}

	}
	catch(Exception $e)
	{
		mysql_query("UNLOCK TABLES");
		throw $e;
	}

	refresh_row_ids();

	sql_request("UNLOCK TABLES");

	return $new_id;

}

function refresh_row_ids()
{
	if(isset($_SESSION['rows_id']))
	{
		$rows_id = $_SESSION['rows_id'];
		$n = count($rows_id) -1;
		for($i = 0; $i < $n; $i++)
			$_SESSION['rows_id'][$i] = 	getIDOrigine($_SESSION['rows_id'][$i]);
	}
}



/* Hugo could be optimized in one sql update request?*/
function change_statuts($new_statut, $filter_values)
{
	//echo "Changing status to " .$new_statut." <br/>";
	$rows = filterSortReports(getCurrentFiltersList(), $filter_values, getSortingValues());

	foreach($rows as $row)
		change_statut($row->id, $new_statut);
}

function updateRapportAvis($id,$avis,$rapport)
{
	$data = array("avis" => $avis, "rapport" => $rapport);
	return change_report_properties($id, $data);
}

function change_rapporteur($id, $newrapporteur)
{
	return change_report_property($id, "rapporteur", $newrapporteur);
}

function change_rapporteur2($id, $newrapporteur)
{
	return change_report_property($id, "rapporteur2", $newrapporteur);
}

function change_statut($id, $newstatut)
{
	return change_report_property($id, "statut", $newstatut);
}

function change_report_property($id_origine, $property_name, $newvalue)
{

	$data = array($property_name => $newvalue);

	//echo "Changing property " .$property_name." of ". $id_origine." for new value ".$newvalue."<br/>";
	change_report_properties($id_origine, $data);
}

function change_report_properties($id_origine, $data)
{
	global $fieldsAll;

	$row = NULL;

	$request = array();

	$report = getReport($id_origine);

	foreach($report as $key => $value)
	{
		if(isset($data[$key]))
			$request["field".$key] = $data[$key];
	}


	return addReportFromRequest($id_origine,$request);

}


function getVirginReports($rapporteur)
{
	global $empty_filter;

	$filter_values = $empty_filter;
	$filter_values['login_rapp'] = $rapporteur->login;
	$filter_values['statut'] = 'vierge';
	$liste1 =  filterSortReports(getCurrentFiltersList(), $filter_values,getSortingValues());

	$filter_values = $empty_filter;
	$filter_values['login_rapp2'] = $rapporteur->login;
	$filter_values['statut'] = 'vierge';
	$liste12 =  filterSortReports(getCurrentFiltersList(), $filter_values,getSortingValues());

	return array_merge($liste1,$liste2);
}

function getRapporteurReports($login)
{
	return filterSortReports(getCurrentFiltersList(), array("login_rapp" => $login, "id_session=" => current_session_id()) ,getSortingValues() );
}

function getTodoReports($login)
{
	return filterSortReports(getCurrentFiltersList(), array("avancement" => "todo", login_rapp => $login, "id_session=" => current_session_id()) ,getSortingValues() );
}


function find_candidate_reports($candidate,$eval_type = "")
{
	if($eval_type == "")
		return  filterSortReports(array("cleindividu"=>""), array("cleindividu"=>$candidate->cle));
	else
		return  filterSortReports(array("cleindividu"=>"","type"=>""), array("cleindividu"=>$candidate->cle, "type" => $eval_type));
}

function update_report_from_concours($id_origine,$concours,$login)
{
	global $fieldsRapportsCandidat;
	global $fieldsRapportsCandidat1;
	global $fieldsRapportsCandidat2;

	$report = getReport($id_origine);

	$filters = array("concours"=>"","cleindividu"=>"","type"=>"");
	$filtersvalues = array("concours" => $concours,"cleindividu" => $report->cleindividu,"type"=>"Candidature");
	$reports = filterSortReports($filters,$filtersvalues);
	if(count($reports) < 1)
		throw new Exception("Cannot update report ".$id_origine." from concours ".$concours." : no such report for candidate ".$report->cle);

	$report2 = $reports[0];

	$fields = array();
	if($login == $report->rapporteur)
		$fields = $fieldsRapportsCandidat1;
	else if ($login == $report->rapporteur2)
		$fields = $fieldsRapportsCandidat2;
	else if(isSecretaire($login))
		$fields = $fieldsRapportsCandidat;

	$fields[] = "type";
	$fields[] = "nom";
	$fields[] = "prenom";


	$data = array();
	foreach($fields as $field)
		$data[$field] = $report2->$field;

	return change_report_properties($id_origine,$data);

}

function listOfAllVirginReports()
{
	$result = "";
	$users = listUsers();
	foreach($users as $rapporteur)
	{
		$reports  = getVirginReports($rapporteur);
		if(count($reports) > 0)
		{
			$result .= "<br/><B>Rapporteur \"".$rapporteur->description."\" (". $rapporteur->email.") :</B><br/>";
			foreach($reports as $report)
				$result .= reportShortSummary($report)."<br/>";
		}
	}
	return $result;
}


?>