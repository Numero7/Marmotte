<?php 

require_once('config.inc.php');
require_once('manage_sessions.inc.php');
require_once('manage_files.php');


function normalizeCandidat($data)
{
	$data2 = (object) $data;

	if(!isset($data2->nom))
		$data2->nom = "";
	if(!isset($data2->prenom))
		$data2->prenom = "";

	return $data2;
}

function is_classe($report)
{
	return is_numeric($report->avis);
}

function is_auditionne($report)
{
	return is_classe($report) || $report->avis=="oral" || $report->avis=="nonclasse";
}

function needs_audition_report($report)
{
  return true;
  global $concours_ouverts;
  global $tous_sous_jury;
  return (isset($concours_ouverts[$report->concours]) && substr($concours_ouverts[$report->concours],0,2)=="CR")
    && isset($tous_sous_jury[$report->concours])
    && isset($tous_sous_jury[$report->concours]["sj2"])
    && ($tous_sous_jury[$report->concours]["sj2"] != "")
    &&(is_classe($report) || $report->avis=="oral" || $report->avis=="nonclasse");
}

function is_auditionneCR($report)
{
	global $concours_ouverts;
	return (isset($concours_ouverts[$report->concours]) && substr($concours_ouverts[$report->concours],0,2)=="CR")
	&&(is_classe($report) || $report->avis=="oral" || $report->avis=="nonclasse");
}


function is_in_conflict($login, $candidat)
{
	//	echo "conflits '".$candidat->conflits."' login '".$login."'";
	return isset($candidat->conflits) && (strpos($candidat->conflits,$login) !== false);
}

function add_conflit_to_report($login, $id_origine)
{
	$report = getReport($id_origine);
	$row = normalizeReport($report);
	$candidat = get_or_create_candidate($row);
	if(isset($candidat->conflits))
	{
		$conflits = $candidat->conflits;
		if(strpos($conflits,$login) === false)
		{
			$conflits .= ";".$login;
			if(isset($candidat->nom) && isset($candidat->prenom) && $candidat->nom != "")
				$candidat->conflits = $conflits;
			updateCandidateFromData($candidat);
		}
	}
}

function updateCandidateFromRequest($request, $oldannee="")
{

	global $fieldsIndividualDB;

	$data = (object) array();


	foreach($fieldsIndividualDB as  $field => $value)
		if (isset($request["field".$field]))
		$data->$field = nl2br(trim($request["field".$field]),true);


	if(	isset($request['previousnom']) && isset($request['previousprenom']))
	{
		$ppnom = $request['previousnom'];
		$ppprenom = $request['previousprenom'];

		if( (isset($data->nom) && $ppnom != "" && $data->nom != $ppnom) || (isset($data->prenom) && $ppprenom != "" && $data->prenom != $ppprenom) )
		{
			$sql = "UPDATE ".reports_db." SET nom=\"".$data->nom."\", prenom=\"".$data->prenom."\" WHERE nom =\"".$ppnom."\" AND prenom=\"".$ppprenom."\"  AND section=\"".currentSection()."\"";
			sql_request($sql);
			$sql = "UPDATE ".people_db." SET nom=\"".$data->nom."\", prenom=\"".$data->prenom."\" WHERE nom =\"".$ppnom."\" AND prenom=\"".$ppprenom."\"  AND section=\"".currentSection()."\"";
			sql_request($sql);
			rename_people_directory(current_session_id(), $data->nom, $data->prenom, $ppnom, $ppprenom);
		}

	}
	$candidate = updateCandidateFromData($data);

	return $candidate;
}

function updateCandidateFromData($data)
{
	global $fieldsIndividualDB;

	$candidate = get_or_create_candidate($data );
	$sqlcore = "";

	$first = true;
	foreach($data as  $field => $value)
	{
		if(key_exists($field, $fieldsIndividualDB))
		{
			$sqlcore.=$first ? "" : ",";
			$sqlcore.=$field.'="'.real_escape_string($value).'" ';
			$first = false;
		}
	}
	$sql = "UPDATE ".people_db." SET ".$sqlcore." WHERE nom=\"".$data->nom."\" AND prenom=\"".$data->prenom."\" AND section=\"".currentSection()."\" ;";

	sql_request($sql);

	return get_or_create_candidate($data );
}

function getAllCandidates()
{
	$sql = "SELECT * FROM ".people_db." WHERE section=\"".currentSection()."\" ;";
	$result=sql_request($sql);
	if($result == false)
		throw new Exception("Failed to process sql query ".$sql);
	$rows = array();

	while ($row = mysqli_fetch_object($result))
		$rows[] = $row;

	return $rows;
}

function  set_people_property($property,$numsirhus, $value)
{
  $sql = "UPDATE ".people_db." SET ".$property."=\"".$value."\" WHERE NUMSIRHUS=\"".$numsirhus."\";";
  sql_request($sql);
  echo $sql;
  //  throw new Exception($sql);
}


function add_candidate_to_database($data,$section="")
{
	if($section == "")
		$section = currentSection();

	global $fieldsIndividualDB;
	$sqlvalues = "";
	$sqlfields = "";
	$first = true;

	global $empty_individual;
	foreach($fieldsIndividualDB as $field => $desc)
		if($field != "fichiers")
		{
			$sqlfields .= ($first ? "" : ",") ."`".$field."`";
			$sqlvalues .= ($first ? "" : ",");
			$sqlvalues .= '"'.(isset($data->$field) ? $data->$field : ( isset($empty_individual[$field]) ? $empty_individual[$field] : "") );
			$sqlvalues .= '"';
			$first = false;
		}

		$sqlfields .= ",section";
		$sqlvalues .= ",\"".$section."\"";
		$sqlfields .= ",NUMSIRHUS";
		$sqlvalues .= ",".(isset($data->NUMSIRHUS) ? ("\"".$data->NUMSIRHUS ."\"") : "\"\"");

		$sql = "INSERT INTO ".people_db." ($sqlfields) VALUES ($sqlvalues);";
		sql_request($sql);

		$sql2 = 'SELECT * FROM '.people_db.' WHERE `nom`="'.$data->nom.'" AND `prenom`="'.$data->prenom.'" AND section="'.$section.'";';
		$result = sql_request($sql2);
		$candidate = mysqli_fetch_object($result);

		if($candidate == false)
			throw new Exception("Failed to add candidate with request <br/>".$sql2);

		return $candidate;
}

function get_or_create_candidate($data)
{
	$data = normalizeCandidat($data);
	$data->nom = ucwords(strtolower($data->nom));
	$data->prenom = ucwords(strtolower($data->prenom));
	$section = currentSection();
//	echo("Getting candidate of SIRHUS '".$data->NUMSIRHUS."'<br/>");
	try
	{
		$sql = "SELECT * FROM ".people_db.' WHERE nom="'.$data->nom.'" AND prenom="'.$data->prenom.'" AND section="'.$section.'" ;';
		$result = sql_request($sql);

		$cdata = mysqli_fetch_object($result);
		if($cdata == false)
		{
			add_candidate_to_database($data,$section);
			$result = sql_request($sql);
			$cdata = mysqli_fetch_object($result);
			if($cdata == false)
				throw new Exception("Failed to find candidate previously added<br/>".$sql);
		}
		else if(isset($data->NUMSIRHUS) && $data->NUMSIRHUS != "")
	      {
		$cdata->NUMSIRHUS = $data->NUMSIRHUS;
		$cand = get_candidate_from_SIRHUS($data->NUMSIRHUS);
		if($cand != null)
		  {
		    global $fieldsDSIChercheurs;
		    global $refposition;
		    $cdata->infos_evaluation = "";
		    foreach($fieldsDSIChercheurs as $key => $data)
		      {
			    if(is_array($data))
			      {
				$loc = "";
				foreach($data as $key2 => $data2)
				  {
				    if(!isset($cand->$key2) || $cand->$key2 == "") break;
				    if($key2 == "codeposition" && isset($refposition[$cand->$key2]))
				      $loc.= $data2." ".$refposition[$cand->$key2]." ";
				    else
				      $loc.= $data2." ".$cand->$key2." ";
				  }
				if($loc != "")
				  $cdata->infos_evaluation.= $loc."<br/>";
			      }
			    else if(isset($cand->$key) && $cand->$key != "")
			      $cdata->infos_evaluation.= $data." ".$cand->$key."<br/>";
		      }
		    $cdata->nom = $cand->nom;
		    $cdata->prenom = $cand->prenom;
		  }
	      }

		return normalizeCandidat($cdata);
	}
	catch(Exception $exc)
	{
		throw new Exception("Failed to add candidate from report:<br/>".$exc->getMessage());
	}
}


function get_candidate_from_SIRHUS($sirhus)
{
	$sql = "SELECT * FROM ".dsidbname.".".dsi_people_db." WHERE numsirhus=\"".$sirhus."\";";
	$res = sql_request($sql);
	while($row = mysqli_fetch_object($res))
		return $row;
	return null;
}

function norm_name($nom)
{
	$nom = replace_accents($nom);
	return strtoupper(str_replace(array(" ","'","-"), array("_","_","_"),$nom));
}



?>
