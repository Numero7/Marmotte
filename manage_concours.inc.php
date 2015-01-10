<?php

require_once('manage_rapports.inc.php');

function affectersousjurys()
{
	$rows = get_current_selection();
	$users = listUsers();
	global $concours_ouverts;
	global $sous_jurys;

	foreach($users as $login => $data)
	{
		if(isset($data->sousjury))
		{
			foreach($sous_jurys as $concours => $sj)
				foreach($sj as $code => $data)
			{
				$nom = $data["nom"];
				if($code != "")
				{
					if(strpos( $data->sousjury , $code) !== false)
						$user[$login]->sousjurys[$concours] = $code;
				}
			}
		}
	}
	
	
	foreach($rows as $row)
	{
		if(isset($row->rapporteur) && isset($row->concours))
		{
			$rapp = $row->rapporteur;
			$concours = $row->concours;
		
			if(isset($user[$rapp]->sousjurys[$concours]))
			{
				$sj = $user[$rapp]->sousjurys[$concours];
				if(!isset($row->sousjury) || ( isset($row->sousjury) && $row->sousjury != $sj))
					change_report_property($row->id, "sousjury", $sj);
			}
		}
	}
}

function getConcours()
{
	$concours = array();
	$sql = "SELECT * FROM ".concours_db." WHERE section='".real_escape_string(currentSection()). "' and session='".real_escape_string(current_session_id())."'";
	$sql.= ";";
	$result = sql_request($sql);
	while ($row = mysqli_fetch_object($result))
		$concours[ $row->code ] = $row;
	return $concours;
}

function setConcours($conc)
{
		$sql = "DELETE FROM ".concours_db;
		$sql .=" WHERE section='".real_escape_string(currentSection())."'";
		$sql .= " and session='".real_escape_string(current_session_id())."'";
		$sql .= " and code='".real_escape_string($conc->code)."';";
		sql_request($sql);
		
		
		$sql = "INSERT INTO `concours` (`section`, `session`, `code`, `intitule`, `postes`, `sousjury1`, `sousjury2`, `sousjury3`, `sousjury4`, `president1`, `president2`, `president3`, `president4`,`membressj1`, `membressj2`, `membressj3`, `membressj4`)";
		$sql .= " VALUES ('".real_escape_string(currentSection())."','".real_escape_string(current_session_id());
		$sql .= "','".real_escape_string($conc->code)."','".real_escape_string($conc->intitule)."','".real_escape_string($conc->postes);
		$sql .= "','".real_escape_string($conc->sousjury1)."','".real_escape_string($conc->sousjury2)."','".real_escape_string($conc->sousjury3);
		$sql .= "','".real_escape_string($conc->sousjury4)."','".real_escape_string($conc->president1)."','".real_escape_string($conc->president2);
		$sql .= "','".real_escape_string($conc->president3)."','".real_escape_string($conc->president4);
		$sql .= "','".real_escape_string($conc->membressj1)."','".real_escape_string($conc->membressj2)."','".real_escape_string($conc->membressj3)."','".real_escape_string($conc->membressj4);
		$sql .= "')";

		sql_request($sql);
}

function deleteConcours($code)
{
	$sql = "DELETE FROM ".concours_db;
	$sql .=" WHERE section='".real_escape_string(currentSection())."'";
	$sql .= " and session='".real_escape_string(current_session_id())."'";
	$sql .= " and code='".real_escape_string($code)."';";
	sql_request($sql);
}

function addSousJury($code, $sousjury, $login)
{
	$concours = getConcours();
	if(isset($concours[$code]))
	{
	$liste = array();
	$row = $concours[$code];
	for($i = 1; $i <=4 ; $i++)
	{	
		$field = "membressj".$i;
		$row->$field = str_replace($login,"",$row->$field);
		$row->field = str_replace(";;","",$row->$field);
		if($sousjury == $i)
			$row->$field.= ";".$login;
	}
	}
	setConcours($row);
}





?>