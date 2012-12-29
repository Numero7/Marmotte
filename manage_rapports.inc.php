<?php


function getReport($id_rapport)
{
	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt, sessions ss WHERE tt.id=$id_rapport ORDER BY date DESC LIMIT 1;";
	$result=mysql_query($sql);
	return mysql_fetch_object($result);
}

function filterSortReports($statut, $id_session, $type_eval, $sort_crit, $login_rapp, $id_origin=-1)
{
	$sortCrit = parseSortCriteria($sort_crit);

	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt INNER JOIN ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, sessions ss WHERE ss.id=tt.id_session ";
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
	$sql .= " AND tt.id>0 ";
	
	$sql .= sortCriteriaToSQL($sortCrit);
	$sql .= ";";
	//echo $sql;
	$result=mysql_query($sql);
	if($result == false)
		echo "Failed to process query <br/>".$sql."<br/>";
	return $result;
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
	$result = filterSortReports(-1,-1, "", "", "", $id_origine);
	global $fieldsAll;
	$tab = mysql_fetch_array($result);
	$specialRule = array(
			"auteur"=>0,
			"date"=>0,
			"avis"=>0,
			"rapport"=>0,
	);
	$fields = "auteur,id_session,id_origine,avis,rapport";
	$values = "\"".mysql_real_escape_string($_SESSION["login"])."\",".$tab["id_session"].",".$tab["id_origine"].",\"".mysql_real_escape_string(trim($avis))."\",\"".mysql_real_escape_string(trim($rapport))."\"";
	foreach($fieldsAll as  $fieldID => $title)
	{
		if (!isset($specialRule[$fieldID]))
		{
			$fields.=",";
			$fields.=$fieldID;
			$values.=",";
			$values.="\"".mysql_real_escape_string(trim($tab[$fieldID]))."\"";
		}
	}
	$sql = "INSERT INTO evaluations ($fields) VALUES ($values);";
	//echo $sql."<br>";
	mysql_query($sql);
}

function addReport($request, $login)
{
	if(!isSuperUser($login))
	{
		echo "Le compte ".$login." n'a pas la permission de créer un rapport.<br/>";
		return false;
	}
	$newid = update(0,$request, $login);
	$sql = "UPDATE evaluations SET id_origine=$newid WHERE id=$newid;";
	mysql_query($sql);
	return $newid;
};

function getStatus($id_rapport)
{
	$report = getReport($id_rapport);
	return $report->statut;
}

function isReportEditable($rapport, $login)
{
	return isSuperUser($login) || ( ($report->rapporteur == $login)  && ($report->statut == 'vierge' || $report->statut == 'prerapport'));
}


function deleteReport($id_rapport, $login)
{
	$report = getReport($id_rapport);
	if(!isReportEditable($report,$login))
		return "Le compte ".$login." n'a pas la permission d'effacer ce rapport.<br/>";
			
	$report = getReport($id_rapport);
	if(($report->rapporteur == $login) || (isSuperUser($login)))
	{
		$sql = "UPDATE evaluations SET id=-".$id_rapport." WHERE id=".$id_rapport.";";
		if(mysql_query($sql) != false)
			return "Deleted report ".$id_rapport." <br/>";
		else
			return "Failed to delete report: failed to process sql query ".$sql.".<br/>";
	}
	else
	{
		
	}
		
};

function update($id_origine, $request, $login)
{
	$report = getReport($id_origine);
	if($report && !isReportEditable($report,$login))
	{
		echo "Le compte ".$login." n'a pas la permission de mettre à jour ce rapport.<br/>";
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
	$values .= ",\"".mysql_real_escape_string($login)."\"";

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
	return mysql_insert_id();
}


?>