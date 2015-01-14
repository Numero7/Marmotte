<?php 

require_once('config.inc.php');
require_once('manage_sessions.inc.php');
require_once('manage_files.php');

/*
 function generateKey($annee, $nom,$prenom)
 {
return mb_strtolower(replace_accents(trim($annee.$nom.$prenom," '-")));
}
*/
function candidateExists($nom,$prenom)
{

	$sql = "SELECT * FROM ".people_db.' WHERE nom="'.$nom.'" AND prenom="'.$prenom.'";';

	$result = sql_request($sql);
	return	(mysqli_num_rows($result) > 0);
}

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
	global $fieldsIndividualAll;

	$data = (object) array();


	foreach($fieldsIndividualAll as  $field => $value)
		if (isset($request["field".$field]))
		$data->$field = nl2br(trim($request["field".$field]),true);

	
	if(	isset($request['previousnom']) && isset($request['previousprenom']))
			{
	$ppnom = real_escape_string($request['previousnom']);
	$ppprenom = real_escape_string($request['previousprenom']);
	
	$candidate = get_or_create_candidate($data );
	
	if($ppnom != $candidate->nom || $ppprenom != $candidate->prenom)
	{
		if(($request['previousnom']!= "" || $request['previousprenom'] != ""))
		{
			$sql = "UPDATE ".reports_db." SET nom=\"".$candidate->nom."\", prenom=\"".$candidate->prenom."\" WHERE nom =\"".$ppnom."\" AND prenom=\"".$ppprenom."\"";
			sql_request($sql);
			$sql = "DELETE FROM ".people_db." WHERE nom =\"".$ppnom."\" AND prenom=\"".$ppprenom."\"";
			sql_request($sql);
		}
	}
	else
	{
		$candidate = updateCandidateFromData($data);
	}
			}
			
	
	return $candidate;
}

function updateCandidateFromData($data)
{
	global $fieldsIndividualAll;
	
	
	$candidate = get_or_create_candidate($data );

	$sqlcore = "";

	$first = true;
	foreach($data as  $field => $value)
	{
		if(key_exists($field, $fieldsIndividualAll))
		{
			$sqlcore.=$first ? "" : ",";
			$sqlcore.=$field.'="'.real_escape_string($value).'" ';
			$first = false;
		}
	}
	$sql = "UPDATE ".people_db." SET ".$sqlcore." WHERE nom=\"".$data->nom."\" AND prenom=\"".$data->prenom."\";";

	sql_request($sql);

	return get_or_create_candidate($data );



}

function getAllCandidates()
{
	$sql = "SELECT * FROM ".people_db." WHERE ";
	$sql .= " `section`='". real_escape_string($_SESSION['filter_section'])."'";
	$sql .= ";";
	$result=sql_request($sql);
	if($result == false)
		throw new Exception("Failed to process sql query ".$sql);
	$rows = array();

	while ($row = mysqli_fetch_object($result))
		$rows[] = $row;

	return $rows;
}

function add_candidate_to_database($data)
{
	global $fieldsIndividualAll;
	$sqlvalues = "";
	$sqlfields = "";
	$first = true;

	global $empty_individual;
	foreach($fieldsIndividualAll as $field => $desc)
				     if($field != "fichiers")
	{
		$sqlfields .= ($first ? "" : ",") ."`".$field."`";
		$sqlvalues .= ($first ? "" : ",") .'"'.(isset($data->$field) ? $data->$field : ( isset($empty_individual[$field]) ? $empty_individual[$field] : "") ).'"';
		$first = false;
	}
	
	$sqlfields .= ",section";
	$sqlvalues .= ",".$_SESSION['filter_section'];
	
	$sql = "INSERT INTO ".people_db." ($sqlfields) VALUES ($sqlvalues);";
	sql_request($sql);

	$sql2 = 'SELECT * FROM '.people_db.' WHERE `nom`="'.$data->nom.'" AND `prenom`="'.$data->prenom.'";';
	$result = sql_request($sql2);
	$candidate = mysqli_fetch_object($result);

	if($candidate == false)
		throw new Exception("Failed to add candidate with request <br/>".$sql2);

	return $candidate;
}

/*
 * This function will always return a candidate,
* created if needed,
* or throw an exception
*/
function get_or_create_candidate_from_nom($nom, $prenom)
{
	try
	{
		sql_request("LOCK TABLES ".people_db." WRITE;");
		$sql = "SELECT * FROM ".people_db.' WHERE nom="'.$nom.'" AND prenom="'.$prenom.'" ;';
		$result = sql_request($sql);

		$cdata = mysqli_fetch_object($result);
		if($cdata == false)
		{
			$data = (object) array();
			$data->nom = $nom;
			$data->prenom = $prenom;
			add_candidate_to_database($data);
			$result = sql_request($sql);
			$cdata = mysqli_fetch_object($result);
			if($cdata == false)
				throw new Exception("Failed to find candidate previously added<br/>".$sql);
		}

		sql_request("UNLOCK TABLES");
		return normalizeCandidat($cdata);
	}
	catch(Exception $exc)
	{
		sql_request("UNLOCK TABLES;");
		throw new Exception("Failed to add candidate from report:<br/>".$exc->getMessage());
	}
}

function get_or_create_candidate($data)
{
	$data = normalizeCandidat($data);
	return get_or_create_candidate_from_nom($data->nom,$data->prenom);
}




function norm_name($nom)
{
	$nom = replace_accents($nom);
	return strtoupper(str_replace(array(" ","'","-"), array("_","_","_"),$nom));
}



?>