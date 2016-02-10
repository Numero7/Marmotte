<?php

require_once('manage_rapports.inc.php');

function reinitializeCponflicts()
{
  $sql = "UPDATE ".marmottedbname.".".people_db." SET conflits='' WHERE section='".currentSection()."'";
  sql_request($sql);
  return "Conflits remis à zéro";
}

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

function getAdmisAPoursuivre($num_conc)
{
  $sql = "SELECT user_id FROM dsi.INTER_CC_statuts_candidatures4 WHERE num_conc='".real_escape_string($num_conc)."' AND admis_concourir_code='1' AND retrait_candidature_code!='1'";
  $res = sql_request($sql);
  $result = array();
  while($row = mysqli_fetch_object($res))
    $result[] = $row->user_id;
  return $result;
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

function myConcours()
{
  $sql = "SELECT numconc FROM ".dsidbname.".".dsi_rapp_conc." WHERE emailpro=\"".$_SESSION["login"]."\"";
  $myc =array();
  $result = sql_request($sql);
  while ($row = mysqli_fetch_object($result))
    $mys[$row->numconc] = $row->numconc;

  if(isSecretaire())
    {
      $sql = "SELECT code FROM ".marmottedbname.".".concours_db." ";
      $sql .= "WHERE section='".real_escape_string(currentSection()). "' and session='".real_escape_string(current_session_id())."'";
      while ($row = mysqli_fetch_object($result))
	$mys[$row->code] = $row->code;
    }
  return $mys;
}

function getConcours()
{
	{
	$concours = array();
	$sql = "SELECT * FROM ".marmottedbname.".".concours_db." conc JOIN ".dsidbname.".".dsi_GOC." goc ON conc.code=goc.n_public ";
	$sql .= " WHERE conc.section='".real_escape_string(currentSection()). "' and conc.session='".real_escape_string(current_session_id())."'";
	$sql .= ";";
	$result = sql_request($sql);

	global $my_conc;
	while ($row = mysqli_fetch_object($result))
	  {
	    if(!isset($my_conc[$row->code]))
	      continue;
	    $row->postes = $row->nb_prop;
	    $row->grade=$row->grade_conc;
	    $row->intitule = $row->grade_conc." ".$row->code." ".$row->intitule;
	    $row->jures = array();
		$concours[ $row->code ] = $row;
		$sql = "SELECT * FROM ".dsidbname.".".dsi_rapp_conc." WHERE numconc=\"".$row->code."\"";
		  $result2 = sql_request($sql);
		while($row2 = mysqli_fetch_object($result2))
		  $row->jures[] = $row2->emailpro;
	  }
	

	$_SESSION["allconcours"] = $concours;
	}
	return $_SESSION["allconcours"];
}

function getPresidentSousJury($sousjury)
{

}

function setConcours($conc)
{
	$sql = "UPDATE `concours` SET ";
	$fields = array("intitule",
"sousjury1","sousjury2","sousjury3","sousjury4",
			"president1","president2","president3","president4",
			"membressj1","membressj2","membressj3","membressj4"
);
	$first = true;
	foreach($fields as $field)
	  {
	    if(isset($conc->$field))
	      {
	    if(!$first) $sql.=",";
	  $sql.=$field."=\"".real_escape_string($conc->$field)."\" ";
	  $first = false;
	  }
	  }
	$sql .= "WHERE code=\"".$conc->code."\" AND section=\"".currentSection()."\" AND session=\"".current_session_id()."\"";

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
  echo $code." ".$sousjury." " .$login."<br/>";
	$concours = getConcours();
	if(isset($concours[$code]))
	{
		$liste = array();
		$row = new stdClass();;//new object();
		$row->code = $code;
		for($i = 1; $i <=4 ; $i++)
		{
			$field = "membressj".$i;
			$row->$field = str_replace($login,"",$concours[$code]->$field);
			$row->field = str_replace(";;",";",$row->$field);
			if($sousjury == $i)
				$row->$field.= ";".$login;
		}
	}
	unset($row->intitule);
	setConcours($row);
}





?>