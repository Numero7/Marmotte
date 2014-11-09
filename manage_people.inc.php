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
	return	(mysql_num_rows($result) > 0);
}

function normalizeCandidat($data)
{
	global $candidat_prototypes;

	$data2 = (object) $data;

	if(!isset($data2->nom))
		$data2->nom = "";
	if(!isset($data2->prenom))
		$data2->prenom = "";

	foreach($candidat_prototypes as $field => $value)
		if(isset($data2->$field))
		if($data2->$field=="")
		$data2->$field = $value;

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

	$candidate = updateCandidateFromData($data);
	
	if(isset($request['previousnom']) && isset($request['previousprenom']) && ($request['previousnom']!= "" || $request['previousprenom'] != "") )
	{
		if(mysql_real_escape_string($request['previousnom']) != $candidate->nom || mysql_real_escape_string($request['previousprenom']) != $candidate->prenom)
		{
			$sql = "UPDATE ".reports_db." SET nom=\"".$candidate->nom."\", prenom=\"".$candidate->prenom."\" WHERE nom =\"".mysql_real_escape_string($request['previousnom'])."\" AND prenom=\"".mysql_real_escape_string($request['previousprenom'])."\"";
			sql_request($sql);
			$sql = "DELETE FROM ".people_db." WHERE nom =\"".mysql_real_escape_string($request['previousnom'])."\" AND prenom=\"".mysql_real_escape_string($request['previousprenom'])."\"";
			sql_request($sql);
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
			$sqlcore.=$field.'="'.mysql_real_escape_string($value).'" ';
			$first = false;
		}
	}
	$sql = "UPDATE ".people_db." SET ".$sqlcore." WHERE nom=\"".$data->nom."\" AND prenom=\"".$data->prenom."\";";

	sql_request($sql);

	return get_or_create_candidate($data );



}

function getAllCandidates()
{
	$sql = "SELECT * FROM ".people_db.";";
	$result=mysql_query($sql);
	if($result == false)
		throw new Exception("Failed to process sql query ".$sql);
	$rows = array();

	while ($row = mysql_fetch_object($result))
		$rows[] = $row;

	return $rows;
}

function annee_from_data($data, $pref = "")
{
	$annee = session_year(current_session_id());

	$champ1 = $pref."anneecandidature";
	$champ2 = $pref."annee_recrutement";
	if(isset($data->$champ1))
		$annee = $data->$champ1;
	else if(isset($data->$champ2))
		$annee = $data->$champ2;

	return $annee;
}

function add_candidate_to_database($data)
{
	global $fieldsIndividualAll;

	$sqlvalues = "";
	$sqlfields = "";
	$first = true;

	global $empty_individual;

	foreach($fieldsIndividualAll as $field => $desc)
	{
		$sqlfields .= ($first ? "" : ",") .$field;
		$sqlvalues .= ($first ? "" : ",") .'"'.(isset($data->$field) ? $data->$field : ( isset($empty_individual[$field]) ? $empty_individual[$field] : "") ).'"';
		$first = false;
	}


	
	$sql = "INSERT INTO ".people_db." ($sqlfields) VALUES ($sqlvalues);";
	sql_request($sql);

	$sql2 = 'SELECT * FROM '.people_db.' WHERE nom="'.$data->nom.'" AND prenom="'.$data->prenom.'";';
	$result = sql_request($sql2);
	$candidate = mysql_fetch_object($result);

	if($candidate == false)
	{
		throw new Exception("Failed to add candidate with request <br/>".$sql2);
	}

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

		mysql_query("LOCK TABLES ".people_db." WRITE;");


		$sql = "SELECT * FROM ".people_db.' WHERE nom="'.$nom.'" AND prenom="'.$prenom.'" ;';

		$result = sql_request($sql);

		$cdata = mysql_fetch_object($result);
		if($cdata == false)
		{
			$data = (object) array();
			$data->nom = $nom;
			$data->prenom = $prenom;
			add_candidate_to_database($data);
			$result = sql_request($sql);
			$cdata = mysql_fetch_object($result);
			if($cdata == false)
				throw new Exception("Failed to find candidate previously added<br/>".$sql);
		}

		mysql_query("UNLOCK TABLES");
		return normalizeCandidat($cdata);
	}
	catch(Exception $exc)
	{
		mysql_query("UNLOCK TABLES;");
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