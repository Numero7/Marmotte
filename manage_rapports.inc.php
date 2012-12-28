<?php


function getReport($id_rapport)
{
	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt, sessions ss WHERE tt.id=$id_rapport ORDER BY date DESC LIMIT 1;";
	$result=mysql_query($sql);
	return mysql_fetch_object($result);
}


function updateRapportAvis($id_origine,$avis,$rapport)
{
	$result = filterSortReports(-1, "", "", "", $id_origine);
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
	$newid = update(0,$request, $login);
	$sql = "UPDATE evaluations SET id_origine=$newid WHERE id=$newid;";
	mysql_query($sql);
	return $newid;
};

function deleteReport($id_rapport, $login)
{
	$report = getReport($id_rapport);
	if(($report->rapporteur == $login) || (isSuperUser($login)))
	{
		$sql = "DELETE FROM evaluations WHERE id=$id_rapport;";
		if(mysql_query($sql) != false)
			return "Deleted report ".$id_rapport." <br/>";
		else
			return "Failed to delete report: failed to process sql query for report ".$id_rapport;
	}
	else
	{
		return "You dont own enough permissions to delete this report from ".$login ." <br/>";
	}
		
};

function update($id_origine, $request, $login)
{
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
			{
				$values.="\"".mysql_real_escape_string(nl2br(trim($request["field".$fieldID]), true))."\"";
			}
			else
			{
				$values.="\"".mysql_real_escape_string("")."\"";
			}
		}
	}
	$sql = "INSERT INTO evaluations ($fields) VALUES ($values);";
	mysql_query($sql);
	return mysql_insert_id();
}


?>