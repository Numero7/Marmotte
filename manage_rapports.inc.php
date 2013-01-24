<?php

require_once('manage_sessions.inc.php');
require_once('manage_unites.inc.php');
require_once("config.inc.php");


function getIDOrigine($id_rapport)
{
	$sql = "SELECT id_origine FROM ".evaluations_db." WHERE id=$id_rapport";
	$result=mysql_query($sql);
	if($result == false)
		throw new Exception("Fail to process sql request ".$sql);
	$report = mysql_fetch_object($result);
	return $report->id_origine;

}

/*
 * returns an object
*/

function getReport($id_rapport)
{
	$id_origine = getIDOrigine($id_rapport);
	$sql = "SELECT * FROM ".evaluations_db." WHERE id=$id_origine";
	$result=sql_request($sql);
	if($result == false)
		throw new Exception("Fail to process sql request ".$sql);
	$report = mysql_fetch_object($result);

	$report = normalizeReport($report);

	if($report == false)
		throw new Exception("No report with id ".$id_rapport);
	else
		return $report;
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

function getAllReportsOfType($type,$id_session=-1)
{

	if($id_session==-1)
		$id_session = current_session_id();
	$filter_values = array('type'=> $type,'id_session' => $id_session);

	return filterSortReports(getCurrentFiltersList(), $filter_values);
}

function filterSortReports($filters, $filter_values = array(), $sorting_values = array())
{

	$sql = "SELECT * FROM ".evaluations_db." WHERE id = id_origine AND statut!=\"supprime\"";
	//$sql = "SELECT * FROM ".evaluations_db." WHERE date = (SELECT MAX(date) FROM evaluations AS mostrecent WHERE mostrecent.id_origine = evaluations.id_origine AND statut!=\"supprime\")";
	//$sql = "SELECT * FROM ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date";
	//$sql = "SELECT * FROM evaluations WHERE (SELECT id, MAX(date) AS date FROM evaluations GROuP BY id_origine) AS X "
	//$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt INNER JOIN ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, sessions ss WHERE ss.id=tt.id_session ";
	//$sql = "SELECT * FROM evaluations WHERE 1 ";


	$sql .= filtersCriteriaToSQL($filters,$filter_values);
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

	$rows_id = array();
	foreach($rows as $row)
		$rows_id[] = $row->id;
	$_SESSION['rows_id'] = $rows_id;

	return $rows;
}

function parseSortCriteria($sort_crit)
{
	$result = array();
	$pieces = explode(";", $sort_crit);
	foreach($pieces as $crit)
	{
		$firstChar = substr($crit,0,1);
		$crit = substr($crit,1);
		if ($firstChar=="*")
			$result[$crit]= "ASC";
		else if ($firstChar=="-")
			$result[$crit]= "DESC";
	}
	return $result;
}

function sortCriteriaToSQL($sorting_values)
{
	$sql = "";

	foreach($sorting_values as $crit => $value)
	{
		if ($sql == "")
		{
			$sql = "ORDER BY ";
		}
		else
		{ $sql .= ", ";
		}
		$sql .= $crit." ".( ( substr($sorting_values[$crit],strlen($sorting_values[$crit]) -1) == "+" ) ? "ASC" : "DESC");
	}
	if ($sql =="")
	{
		$sql = "ORDER BY name ";
	}

	return $sql;
}

function filtersCriteriaToSQL($filters, $filter_values)
{
	$sql = "";
	foreach($filters as $filter => $data)
	{
		//rrr();
		if(isset($filter_values[$filter]) && ($filter_values[$filter] != $data['default_value']))
		{
			if($filter == "login_rapp")
			{
				$val = $filter_values[$filter];
				$sql .= " AND (rapporteur=\"".$val."\" OR rapporteur2=\"".$val."\") ";
			}
			else if($filter !="login_rapp2")
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

function updateRapportAvis($id_origine,$avis,$rapport)
{
	global $fieldsAll;


	try
	{
		$row = getReport($id_origine);
	}
	catch(Exception $exc)
	{
		throw new Exception("Cannot update report: ".$exc->getMessage());
	}

	checkReportIsEditable($row);

	$specialRule = array(
			"auteur"=>0,
			"date"=>0
	);

	$row->avis = $avis;
	$row->rapport = $rapport;

	if($row->statut == "vierge")
		$row->statut = "prerapport";

	$fields = "auteur,id_session,id_origine";
	$values = "\"".getLogin()."\",".$row->id_session.",".$row->id_origine;
	foreach($fieldsAll as  $fieldID => $title)
	{
		if (!isset($specialRule[$fieldID]))
		{
			$fields.=",";
			$fields.=$fieldID;
			$values.=",";
			$values.="\"".mysql_real_escape_string(nl2br(trim($row->$fieldID)))."\"";
		}
	}
	$sql = "INSERT INTO ".evaluations_db." ($fields) VALUES ($values);";
	//echo $sql."<br>";
	$result = sql_request($sql);

	if($result == false)
	{
		echo "Failed to update rapport: failed to insert new report in DB: failed to process SQL request".$sql;
		throw new Exception("Failed to update rapport: failed to insert new report in DB: failed to process SQL request".$sql);
	}

	//echo $sql;

	$newid = mysql_insert_id();
	$sql = "UPDATE ".evaluations_db." SET id_origine=$newid WHERE id_origine=$id_origine;";

	//echo $sql;

	$result = sql_request($sql);
	if($result == false)
	{
		echo "Failed to update rapport: failed to update id_origine: failed to process SQL request".$sql;
		throw new Exception("Failed to update rapport: failed to update id_origine: failed to process SQL request".$sql);
	}

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

	if (!isSecretaire() && $rapport->statut == 'publie')
	{
		throw new Exception("Les rapports publies ne sont pas modifiables, changer d'abord le statut du rapport");
	}
	else if (isSecretaire())
	{
		return true;
	}
	else if( ($rapport->rapporteur != "") && ($rapport->rapporteur != $login) && ($rapport->rapporteur2 != $login))
	{
		if($rapport->rapporteur2 != "")
			throw new Exception("Les rapporteurs de ce rapport sont '".$rapport->rapporteur."' et '".$rapport->rapporteur2."' mais vous êtes loggés sous l'identité '".$login."'.<br/> Si nécessaire veuillez demander un changement de rapporteur au bureau.");
		else
			throw new Exception("Le rapporteur de ce rapport est '".$rapport->rapporteur."' mais vous êtes loggés sous l'identité '".$login."'.<br/> Si nécessaire veuillez demander un changement de rapporteur au bureau.");
	}
	else if ($rapport->statut != 'vierge' && $rapport->statut != 'prerapport')
	{
		throw new Exception("Ce rapport a le statut ".$rapport->statut." et n'est donc pas éditable.");
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
	if(!isSecretaire())
		throw new Exception("Seuls les présidents et secrétaires ont droits de crétaion de rapports");
	else return true;
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
		$sql = "UPDATE ".evaluations_db." SET id_origine=$previous_id WHERE id_origine=$id_rapport ;";
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

	$sql = "UPDATE ".evaluations_db." SET statut=\"supprime\"WHERE id=$id_rapport ;";
	sql_request($sql);
	$sql = "UPDATE ".evaluations_db." SET date=NOW() WHERE id=$id_rapport ;";
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
	if($id_origine != 0)
	{
		$report = getReport($id_origine);
		if(!checkReportIsEditable($report))
			throw new Exception("Le compte ".$login." n'a pas la permission de mettre à jour le rapport, veuillez contacter le bureau");
	}

	$report = createReportFromRequest($id_origine, $request);

	$create_new = isset($request['create_new']) ? $request['create_new'] : true;

	return addReportToDatabase($report, $create_new);
}

function createReportFromRequest($id_origine, $request)
{
	global $empty_report;
	//$row = $empty_report;

	// Origin ID might have changed while report was being edited
	$id_origine = getIDOrigine($id_origine);
	$row = get_object_vars(getReport($id_origine));


	$type = "";
	if(isset($request["type"]))
		$type = $request["type"];

	$row['id_session'] = current_session_id();
	$row['id_origine'] = $id_origine;
	$row['auteur'] = getLogin();

	foreach($row as  $field => $value)
		if (isset($request["field".$field]))
		$row[$field] = nl2br(trim($request["field".$field]),true);

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


function addReportToDatabase($report, $create_new = true)
{
	$report = normalizeReport($report);

	if($report->statut == "vierge" && $report->id_origine != 0)
		$report->statut = "prerapport";

	createUnitIfNeeded($report->unite);

	$id_origine = isset($report->id_origine) ? $report->id_origine : 0;

	global $fieldsAll;
	$specialRule = array(
			"date"=>0,
			"id" =>0,
	);

	if($create_new)
	{

		$sqlfields = "";
		$sqlvalues = "";

		$first = true;
		foreach($report as  $field => $value)
		{
			if (!isset($specialRule[$field]))
			{
				$sqlfields.= ($first ? "" : ",").$field;
				$first = false;
			}
		}

		$first = true;
		foreach($report as  $field => $value)
		{
			if (!isset($specialRule[$field]))
			{
				$sqlvalues.=($first ? "" : ",")."\"".mysql_real_escape_string($value)."\"";
				$first = false;
			}
		}

		$sql = "INSERT INTO ".evaluations_db." ($sqlfields) VALUES ($sqlvalues);";

		
		sql_request($sql);

		$new_id = mysql_insert_id();
		
		
		$sql = "UPDATE ".evaluations_db." SET id_origine=$new_id WHERE id_origine=$id_origine OR id=$new_id ;";
		sql_request($sql);

		if(isset($_SESSION['rows_id']))
		{
			$rows_id = $_SESSION['rows_id'];
			$n = count($rows_id) -1;
			for($i = 0; $i < $n; $i++)
			{
				if($rows_id[$i] == $id_origine)
				{
					$_SESSION['rows_id'][$i] = $new_id;
					break;
				}
			}
		}

		return $new_id;

	}
	else
	{
		$sqldata = "";


		$first = true;
		foreach($report as  $field => $value)
		{
			if (!isset($specialRule[$field]))
			{
				$sqldata.= ($first ? "" : ",").$field."=\"".$value."\" ";
				$first = false;
			}
		}

		$sql = "UPDATE ".evaluations_db." SET ".$sqldata." WHERE id_origine=$id_origine;";
		sql_request($sql);
		return $id_origine;
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

	change_report_properties($id_origine, $data);
}

function change_report_properties($id_origine, $data)
{
	global $fieldsAll;

	$row = NULL;
	//echo "Changing status of ".$id_origine." to " .$statut." <br/>";
	if($id_origine == 0)
		$id_origine = addReportFromRequest(0,$data);

	$row = getReport($id_origine);

	$data["auteur"] = getLogin();
	$data["id_session"] = $row->id_session;
	$data["id_origine"] = $id_origine;


	foreach($data as $property_name => $newvalue)
	{
		if(property_exists($row,$property_name))
			$row->$property_name = $newvalue;
		else
			throw new Exception("No property '".$property_name."' in report object");
	}

	$fields = "";
	$values = "";
	$first = true;
	foreach($fieldsAll as  $fieldID => $title)
	{
		if (isset($row->$fieldID) && $fieldID!="id" && $fieldID!="date")
		{
			$fields.=$first ? "" : ",";
			$fields.=$fieldID;
			$values.=$first ? "" : ",";
			$values.="\"".mysql_real_escape_string(trim($row->$fieldID))."\"";
			$first = false;
		}
	}
	$sql = "INSERT INTO ".evaluations_db." ($fields) VALUES ($values);";
	//echo $sql."<br>";
	sql_request($sql);

	$newid = mysql_insert_id();
	$sql = "UPDATE ".evaluations_db." SET id_origine=$newid WHERE id_origine=$id_origine;";
	
	sql_request($sql);

	return $newid;
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


?>