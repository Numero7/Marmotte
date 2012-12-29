<?php

require_once('manage_sessions.inc.php');
require_once('manage_unites.inc.php');

function getReport($id_rapport)
{
	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt, sessions ss WHERE tt.id=$id_rapport ORDER BY date DESC LIMIT 1;";
	$result=mysql_query($sql);
	return mysql_fetch_object($result);
}

function filterSortReports($statut, $id_session, $type_eval, $sort_crit, $login_rapp, $id_origin=-1)
{
	$sortCrit = parseSortCriteria($sort_crit);

	$sql = "SELECT * FROM evaluations WHERE date = (SELECT MAX(date) FROM evaluations AS mostrecent WHERE mostrecent.id_origine = evaluations.id_origine)";
	//$sql = "SELECT * FROM ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date";
	//$sql = "SELECT * FROM evaluations WHERE (SELECT id, MAX(date) AS date FROM evaluations GROuP BY id_origine) AS X "
	//$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt INNER JOIN ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, sessions ss WHERE ss.id=tt.id_session ";
	//$sql = "SELECT * FROM evaluations WHERE 1 ";
	if($statut != "")
	{
		$sql .= " AND statut=\"$statut\" ";
	}
	if ($id_session!=-1)
	{
		$sql .= " AND id_session=$id_session ";
	}
	if ($id_origin >= 0)
	{
		$sql .= " AND id_origine=$id_origin ";
	}
	if ($type_eval!="")
	{
		$sql .= " AND type=\"$type_eval\" ";
	}
	if ($login_rapp!="")
	{
		$sql .= " AND rapporteur=\"$login_rapp\" ";
	}
	$sql .= " AND id>0 ";

	$sql .= sortCriteriaToSQL($sortCrit);
	$sql .= ";";
	//echo $sql;
	$result=mysql_query($sql);
	if($result == false)
		echo "Failed to process query <br/>".$sql."<br/>";

	$rows = array();
	echo $sql."<br/>".count($rows)." rows ".mysql_num_rows($result)." sqlrows<br/>";
	
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
		{
			$result[$crit]= "ASC";
		}
		else if ($firstChar=="-")
		{
			$result[$crit]= "DESC";
		}
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
	
	$rows = filterSortReports(-1,-1, "", "", "", $id_origine);
	if(count($rows)<0)
		return;
	$row = $rows[0];
	
	$specialRule = array(
			"auteur"=>0,
			"date"=>0,
			"avis"=>0,
			"rapport"=>0,
	);
	$fields = "auteur,id_session,id_origine,avis,rapport";
	$values = "\"".mysql_real_escape_string($_SESSION["login"])."\",".$row->id_session.",".$row->id_origine.",\"".mysql_real_escape_string(trim($avis))."\",\"".mysql_real_escape_string(trim($rapport))."\"";
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

function addReport($request)
{
	if(!isReportCreatable())
	{
		echo "Le compte ".$login." n'a pas la permission de créer un rapport.<br/>";
		return false;
	}
	$newid = update(0,$request);
	
	
	$sql = "UPDATE evaluations SET id_origine=$newid WHERE id=$newid;";
	mysql_query($sql);
	
	return $newid;
};

function addVirginReport($type,$unite,$nom,$prenom,$grade,$rapporteur)
{
	if(!isReportCreatable())
	{
		echo "Le compte ".$login." n'a pas la permission de créer un rapport.<br/>";
		return false;
	}
	
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

function isReportEditable($rapport)
{
	return isSecretaire() || ( ($rapport->rapporteur == getLogin())  && ($rapport->statut == 'vierge' || $rapport->statut == 'prerapport'));
}

function isReportDeletable($rapport)
{
	return isSecretaire() || ( ($rapport->rapporteur == getLogin())  && ($rapport->statut == 'prerapport'));
}

function isReportCreatable()
{
	return isSecretaire();
}

function deleteReport($id_rapport)
{
	$report = getReport($id_rapport);
	if(!isReportDeletable($report))
		return "Le compte ".getLogin()." n'a pas la permission d'effacer ce rapport.<br/>";
		
	$report = getReport($id_rapport);
	$sql = "UPDATE evaluations SET id=-".$id_rapport." WHERE id=".$id_rapport.";";
	$sql = "UPDATE evaluations SET id_origine=-".$id_rapport." WHERE id_origine=".$id_rapport.";";

	if(mysql_query($sql) != false)
		return "Deleted report ".$id_rapport." <br/>";
	else
		return "Failed to delete report: failed to process sql query ".$sql.".<br/>";
};

function update($id_origine, $request)
{
	$report = getReport($id_origine);
	if($report && !isReportEditable($report))
	{
		echo "Le compte ".getLogin()." n'a pas la permission de mettre à jour ce rapport.<br/>";
		echo getLogin(). " " .$report->rapporteur ." ". $report->statut."<br/>";
		unknonw();
		return false;
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


?>