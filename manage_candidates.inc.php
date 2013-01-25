<?php 

require_once('config.inc.php');
require_once('manage_sessions.inc.php');

function generateKey($annee, $nom,$prenom)
{
	return mb_strtolower(replace_accents(trim($annee.$nom.$prenom," '-")));
}

function candidateExists($annee,$nom,$prenom)
{
	$key = generateKey($annee, $nom, $prenom);
	$sql = "SELECT * FROM ".candidates_db.' WHERE cle="'.$key.'";';

	$result = sql_request($sql);
	return	(mysql_num_rows($result) > 0);
}

function normalizeCandidat($data)
{
	global $candidat_prototypes;
	
	$data2 = $data;
	
	foreach($candidat_prototypes as $field => $value)
		if(isset($data->$field))
		if($data->$field=="")
		$data2->$field = $value;
	
	return $data2;
}

function updateCandidateFromRequest($request, $oldannee="")
{
	global $fieldsCandidatAll;
	
	$data = (object) array();
	
	
	foreach($fieldsCandidatAll as  $field => $value)
		if (isset($request["field".$field]))
		$data->$field = nl2br(trim($request["field".$field]),true);

	$annee = $data->anneecandidature;
	$nom = $data->nom;
	$prenom = $data->prenom;
	
	$cle = generateKey($annee,$nom ,$prenom );

	
	$candidate = get_or_create_candidate($data );
	
	$sqlcore = "";
	
	$first = true;
	foreach($data as  $field => $value)
	{
			$sqlcore.=$first ? "" : ",";
			$sqlcore.=$field.'="'.mysql_real_escape_string($value).'" ';
			$first = false;
	}
	$sql = "UPDATE ".candidates_db." SET ".$sqlcore." WHERE cle=\"".$cle."\";";

	sql_request($sql);
	
		
	$candidate = get_or_create_candidate($data );
	
	$previouskey = isset($request['previouscandidatekey']) ? $request['previouscandidatekey'] : $cle;
	if($previouskey != $candidate->cle)
		deleteCandidate($previouskey);
	
	return $candidate;
	
}

function deleteCandidate($key)
{
	sql_request("DELETE FROM ".candidates_db." WHERE cle=\"".$key."\";");
}

function getAllCandidates()
{
	$sql = "SELECT * FROM ".candidates_db.";";
	$result=mysql_query($sql);
	if($result == false)
		throw new Exception("Failed to process sql query ".$sql);
	$rows = array();

	while ($row = mysql_fetch_object($result))
		$rows[] = $row;

	return $rows;
}

function add_candidate_to_database($data)
{
	global $fieldsCandidatAll;

	$annee = "0";
	if(isset($data->id_session))
		$annee = session_year($data->id_session);
	if(isset($data->anneecandidature))
		$annee = $data->anneecandidature;
	
	$key = generateKey($annee, $data->nom, $data->prenom);
	$data->cle = $key;
	
	$sqlvalues = "";
	$sqlfields = "";
	$first = true;

	foreach($fieldsCandidatAll as $field => $desc)
	{
		if(isset($data->$field))
		{
			$sqlfields .= ($first ? "" : ",") .$field;
			$sqlvalues .= ($first ? "" : ",") .'"'.$data->$field.'"';
			$first = false;
		}
	}

	$sql = "INSERT INTO ".candidates_db." ($sqlfields) VALUES ($sqlvalues);";
	sql_request($sql);

	$sql2 = 'SELECT * FROM candidats WHERE cle="'.$key.'";';
	$result = sql_request($sql2);
	$candidate = mysql_fetch_object($result);
	if($candidate == false)
		throw new Exception("Failed to add candidate with request <br/>".$sql2);

	return $candidate;

}

/*
 * This function will always return a candidate,
 * created if needed,
 * or throw an exception
 */
function get_or_create_candidate($data)
{
	$data = normalizeCandidat($data);
	
	$annee = "0";
	if(isset($data->id_session))
		$annee = session_year($data->id_session);
	if(isset($data->anneecandidature))
		$annee = $data->anneecandidature;
	
	$key = generateKey($annee, $data->nom, $data->prenom);

	try
	{
		mysql_query("LOCK TABLES candidates WRITE;");
		$sql = "SELECT * FROM ".candidates_db.' WHERE cle="'.$key.'";';
		
		
		$result = sql_request($sql);

		$cdata = mysql_fetch_object($result);
		if($cdata == false)
		{
			add_candidate_to_database($data);
			$result = sql_request($sql);
			$cdata = mysql_fetch_object($result);
			if($cdata == false)
				throw new Exception("Failed to fetch object from request<br/>".$sql);
		}

		mysql_query("UNLOCK TABLES");
		return normalizeCandidat($cdata);
	}
	catch(Exception $exc)
	{
		mysql_query("UNLOCK TABLES;");
		throw new Exception("Failed to add candidate from report:<br/>".$exc);
	}
}

function extraction_candidats()
{

	$nb = 0;
	try
	{
		$reports = getAllReportsOfType("Equivalence", current_session_id() );
		$reports = array_merge($reports, getAllReportsOfType("Candidature", current_session_id() ));

		foreach($reports as $report)
		{
			if(!candidateExists(session_year($report->id_session), $report->nom, $report->prenom))
				$nb++;
			get_or_create_candidate($report);
		}
	}
	catch(Exception $exc)
	{
		throw new Exception("Failed to extract candidates from equivalence reports" . $exc->getMessage());
	}

	return "Added ".$nb." new candidates";
}

?>