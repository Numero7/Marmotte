<?php

require_once('manage_sessions.inc.php');
require_once('manage_unites.inc.php');

function getReport($id_rapport)
{
	$sql = "SELECT * FROM evaluations WHERE id=$id_rapport";
	$result=mysql_query($sql);
	if($result == false)
		throw new Exception("Fail to process sql request ".$sql);
	$report = mysql_fetch_object($result);
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

function filterSortReports($filter_values, $sort_crit)
{
	global $filters;

	$sortCrit = parseSortCriteria($sort_crit);

	$sql = "SELECT * FROM evaluations WHERE date = (SELECT MAX(date) FROM evaluations AS mostrecent WHERE mostrecent.id_origine = evaluations.id_origine AND statut!=\"supprime\")";
	//$sql = "SELECT * FROM ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date";
	//$sql = "SELECT * FROM evaluations WHERE (SELECT id, MAX(date) AS date FROM evaluations GROuP BY id_origine) AS X "
	//$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt INNER JOIN ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, sessions ss WHERE ss.id=tt.id_session ";
	//$sql = "SELECT * FROM evaluations WHERE 1 ";
	$sql .= " AND statut!=\"supprime\" ";

	foreach($filters as $filter => $data)
		if($filter_values[$filter] != $data['default_value'])
			$sql .= " AND ". (isset($data['sql_col']) ?  $data['sql_col'] : $filter)."=\"$filter_values[$filter]\" ";

	$sql .= sortCriteriaToSQL($sortCrit);
	$sql .= ";";
	//echo $sql;
	$result=mysql_query($sql);
	if($result == false)
		throw new Exception("Echec de l'execution de la requete <br/>".$sql."<br/>");

	$rows = array();
	//echo $sql."<br/>".count($rows)." rows ".mysql_num_rows($result)." sqlrows<br/>";

	while ($row = mysql_fetch_object($result))
		$rows[] = $row;

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

function sortCriteriaToSQL($sortCrit)
{
	$sql = "";
	foreach($sortCrit as $crit => $order)
	{
		if ($sql == "")
		{
			$sql = "ORDER BY ";
		}
		else
		{ $sql .= ", ";
		}
		$sql .= $crit." ".$order;
	}
	if ($sql =="")
	{
		$sql = "ORDER BY id_origine ";
	}
	return $sql;
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
	$sql = "INSERT INTO evaluations ($fields) VALUES ($values);";
	//echo $sql."<br>";
	$result = mysql_query($sql);

	if($result == false)
	{
		echo "Failed to update rapport: failed to insert new report in DB: failed to process SQL request".$sql;
		throw new Exception("Failed to update rapport: failed to insert new report in DB: failed to process SQL request".$sql);
	}

	//echo $sql;

	$newid = mysql_insert_id();
	$sql = "UPDATE evaluations SET id_origine=$newid WHERE id_origine=$id_origine;";

	//echo $sql;

	$result = mysql_query($sql);
	if($result == false)
	{
		echo "Failed to update rapport: failed to update id_origine: failed to process SQL request".$sql;
		throw new Exception("Failed to update rapport: failed to update id_origine: failed to process SQL request".$sql);
	}

}

function addReport($request)
{
	if(!isReportCreatable())
		throw new Exception("Le compte ".$login." n'a pas la permission de créer un rapport, veuillez contacter le secrétaire scientifique.");

	$newid = update(0,$request);
	$sql = "UPDATE evaluations SET id_origine=$newid WHERE id=$newid;";
	mysql_query($sql);

	return $newid;
};

function addVirginReport($type,$unite,$nom,$prenom,$grade,$rapporteur)
{
	if(!isReportCreatable())
		throw new Exception("Le compte ".$login." n'a pas la permission de créer un rapport, veuillez contacter le secrétaire scientifique.");

	if($grade == "")
		$grade = "None";

	createUnitIfNeeded($unite);

	$fields = "id_session, id_origine, auteur, nom, prenom, grade, unite, rapporteur, type";
	$values = current_session_id().",0,\"".getLogin().'","'.$nom.'","'.$prenom.'","'.$grade.'","'.$unite.'","'.$rapporteur.'","'.$type.'"';

	$sql = "INSERT INTO evaluations ($fields) VALUES ($values);";
	$result = mysql_query($sql);

	if($result == false)
	{
		echo "Failed to process sql query ".$sql."<br/>";
		return false;
	}
	echo $sql."<br/>";

	$newid = mysql_insert_id();
	$sql = "UPDATE evaluations SET id_origine=$newid WHERE id=$newid;";
	mysql_query($sql);

	return $newid;
}

function getStatus($id_rapport)
{
	$report = getReport($id_rapport);
	return $report->statut;
}

function checkReportIsEditable($rapport)
{
	if (!isSecretaire() && $rapport->statut == 'publie')
		throw new Exception("Les rapports publies ne sont pas modifiables, changer d'abord le statut du rapport");
	else if (isSecretaire())
		return true;
	else if( $rapport->rapporteur != getLogin())
		throw new Exception("Le rapporteur de ce rapport est ".$rapport->rapporteur." mais vous êtes loggés sous l'identité ".getLogin().", veuillez demander un changement de rapporteur au bureau.");
	else if ($rapport->statut != 'vierge' && $rapport->statut != 'prerapport')
		throw new Exception("Ce rapport a le statut ".$rapport->statut." et n'est donc pas éditable.");
	else
		return true;
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
	$sql = "SELECT * FROM evaluations WHERE date = (SELECT MAX(date) FROM evaluations AS mostrecent WHERE mostrecent.id_origine=$id_rapport AND mostrecent.id != $id_rapport AND mostrecent.statut!=\"supprime\")";
	$result=mysql_query($sql);
	if($result == false)
		throw "Fail to process sql request ".$sql;
	$before = mysql_fetch_object($result);
	if($before != false)
	{
		$previous_id = $before->id;
		$sql = "UPDATE evaluations SET id_origine=$previous_id WHERE id_origine=$id_rapport ;";
		if(mysql_query($sql) == false)
			throw new Exception("Failed to delete report: failed to set previous report as newest: failed to process query \"$sql\"");
	}

	$sql = "UPDATE evaluations SET statut=\"supprime\"WHERE id=$id_rapport ;";
	if(mysql_query($sql) == false)
		throw new Exception("Failed to delete report: failed to set status of report to supprime: failed to process query \"$sql\"");
	$sql = "UPDATE evaluations SET date=NOW() WHERE id=$id_rapport ;";
	mysql_query($sql);

	return "Deleted report ".$id_rapport." <br/>";
};

function update($id_origine, $request)
{
	if($id_origine != 0)
	{
		$report = getReport($id_origine);
		if(!checkReportIsEditable($report))
			throw new Exception("Le compte ".$login." n'a pas la permission de mettre à jour le rapport, veuillez contacter le bureau");
	}

	if($request["fieldstatut"] == "vierge" && ($id_origine != 0))
		$request["fieldstatut"] = "prerapport";

	global $fieldsAll;
	$specialRule = array(
			"auteur"=>0,
			"date"=>0,
			"nom_session"=>0,
			"date_session"=>0,
	);

	$fields = "id_session, id_origine, auteur";
	foreach($fieldsAll as  $fieldID => $title)
	{
		if (!isset($specialRule[$fieldID]))
		{
			$fields.=",";
			$fields.=$fieldID;
		}
	}
	$values = mysql_real_escape_string($request["fieldid_session"]);
	$values .= ",".mysql_real_escape_string($id_origine);
	$values .= ",\"".mysql_real_escape_string(getLogin())."\"";

	foreach($fieldsAll as  $fieldID => $title)
	{
		if (!isset($specialRule[$fieldID]) )
		{
			$values.=",";
			if(isset($request["field".$fieldID]))
				$values.="\"".mysql_real_escape_string(nl2br(trim($request["field".$fieldID]), true))."\"";
			else
				$values.="\"".mysql_real_escape_string("")."\"";
		}
	}
	$sql = "INSERT INTO evaluations ($fields) VALUES ($values);";
	mysql_query($sql);

	$new_id = mysql_insert_id();

	$sql = "UPDATE evaluations SET id_origine=$new_id WHERE id_origine=$id_origine;";
	mysql_query($sql);

	return $new_id;
}

/* Hugo could be optimized in one sql update request?*/
function change_statuts($new_statut, $filter_values)
{
	//echo "Changing status to " .$new_statut." <br/>";
	$rows = filterSortReports($filter_values);

	foreach($rows as $row)
		change_statut($row->id, $new_statut);
}

function change_statut($id_origine, $statut)
{
	global $fieldsAll;

	//echo "Changing status of ".$id_origine." to " .$statut." <br/>";
	$row = getReport($id_origine);

	$specialRule = array(
			"auteur"=>0,
			"date"=>0,
	);

	$row->statut = $statut;

	$fields = "auteur,id_session,id_origine";
	$values = "\"".getLogin()."\",".$row->id_session.",".$row->id_origine;
	foreach($fieldsAll as  $fieldID => $title)
	{
		if (!isset($specialRule[$fieldID]))
		{
			$fields.=",";
			$fields.=$fieldID;
			$values.=",";
			$values.="\"".mysql_real_escape_string(trim($row->$fieldID))."\"";
		}
	}
	$sql = "INSERT INTO evaluations ($fields) VALUES ($values);";
	//echo $sql."<br>";
	mysql_query($sql);

	$newid = mysql_insert_id();
	$sql = "UPDATE evaluations SET id_origine=$newid WHERE id_origine=$id_origine;";
	mysql_query($sql);
}

function getVirginReports($rapporteur)
{
	global $empty_filter;

	$filter_values = $empty_filter;
	$filter_values['login_rapp'] = $rapporteur->login;
	$filter_values['statut'] = 'vierge';
	return filterSortReports($filter_values,"");
}



?>