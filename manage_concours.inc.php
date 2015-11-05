<?php

require_once('manage_rapports.inc.php');

function affectersousjurys()
{
	$rows = get_current_selection();
	$users = listUsers();
	global $concours_ouverts;
	global $tous_sous_jury;

	$user = getSousJuryMap();

	foreach($rows as $row)
	{
		if(isset($row->rapporteur) && isset($row->concours))
		{
			$rapp = $row->rapporteur;
			$concours = $row->concours;

			if(isset($user[$rapp][$concours]))
			{
				$sj = $user[$rapp][$concours];
				if(!isset($row->sousjury) || ( isset($row->sousjury) && $row->sousjury != $sj))
					change_report_property($row->id, "sousjury", $sj);
			}
		}
	}
	unset($_SESSION["allconcours"]);
	
}

/* map from login * concours to sousjury */
function getSousJuryMap()
{
	$user = array();
	$users = listUsers();
	global $tous_sous_jury;
	
	foreach($users as $login => $data)
	{
		$user[$login] = array();
		foreach($tous_sous_jury as $concours => $sj)
		{
			foreach($sj as $code => $data)
			{
				if($code != "")
				{
					if( in_array($login, $data["membres"]) )
						$user[$login][$concours] = $code;
				}
			}
		}
	}
	return $user;
}

function getConcours()
{
  //	if(!isset($_SESSION["allconcours"]))
	{
	$concours = array();
	$sql = "SELECT * FROM ".concours_db." WHERE section='".real_escape_string(currentSection()). "' and session='".real_escape_string(current_session_id())."'";
	$sql.= ";";
	$result = sql_request($sql);
	while ($row = mysqli_fetch_object($result))
		$concours[ $row->code ] = $row;
	$_SESSION["allconcours"] = $concours;
	}
	return $_SESSION["allconcours"];
}

function getPresidentSousJury($sousjury)
{

}

function setConcours($conc)
{
	if( isset($conc->niveau) && strpos($conc->intitule,$conc->niveau) !==0)
		$conc->intitule = $conc->niveau.$conc->intitule;

	if(!isset($conc->membressj1))
	{
		for($i = 1; $i <= 4; $i++)
		{
			$suff = "membressj".$i;
			$conc->$suff = "";
		}

		$conc->code = preg_replace('/\D/', '', $conc->code);

		$sql = "SELECT * FROM ".concours_db;
		$sql .=" WHERE section='".real_escape_string(currentSection())."'";
		$sql .= " and session='".real_escape_string(current_session_id())."'";
		$sql .= " and code='".real_escape_string($conc->code)."';";
		sql_request($sql);

		$result = sql_request($sql);

		while($row = mysqli_fetch_object($result))
		{
			for($i = 1; $i <= 4; $i++)
			{
				$suff = "membressj".$i;
				$conc->$suff = $row->$suff;
			}
			break;
		}
	}


	for($i = 1; $i <= 4; $i++)
	{
		$suff = "membressj".$i;
		while(strpos($conc->$suff,";;")!==false)
			$conc->$suff = str_replace(";;",";",$conc->$suff);
	}


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
	unset($_SESSION["allconcours"]);
}

function setConcoursStatut($code, $statut)
{
	$sql = "UPDATE ".concours_db." SET statut=\"".real_escape_string($statut)."\" WHERE code=\"".real_escape_string($code)."\"";
	sql_request($sql);
	unset($_SESSION["allconcours"]);
	
}

function deleteConcours($code)
{
	$sql = "DELETE FROM ".concours_db;
	$sql .=" WHERE section='".real_escape_string(currentSection())."'";
	$sql .= " and session='".real_escape_string(current_session_id())."'";
	$sql .= " and code='".real_escape_string($code)."';";
	sql_request($sql);
	unset($_SESSION["allconcours"]);
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