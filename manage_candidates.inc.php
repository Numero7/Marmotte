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

	$annee = annee_from_data($data);
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

function annee_from_data($data, $pref = "")
{
	$annee = session_year(current_session_id());

	$champ = $pref."anneecandidature";
	if(isset($data->$champ))
		$annee = $data->$champ;
	else
		$data->$champ = $annee;

	return $annee;
}

function add_candidate_to_database($data)
{
	global $fieldsCandidatAll;
		
	$annee = annee_from_data($data);
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
		throw new Exception("Failed to add candidate from report:<br/>".$exc->getMessage());
	}
}

function get_candidate_from_key($key)
{

	try
	{
		mysql_query("LOCK TABLES candidates WRITE;");
		$sql = "SELECT * FROM ".candidates_db.' WHERE cle="'.$key.'";';

		$result = sql_request($sql);

		$cdata = mysql_fetch_object($result);
		if($cdata == false)
			throw new Exception("No candidate from key ".$key);

		mysql_query("UNLOCK TABLES");
		return normalizeCandidat($cdata);
	}
	catch(Exception $exc)
	{
		mysql_query("UNLOCK TABLES;");
		throw new Exception("Failed to add candidate from report:<br/>".$exc->getMessage());
	}
}

/*
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
$candidat = get_or_create_candidate($report);
	
change_report_property($report->id, "clecandidat", $candidat->cle);
	
rrr();
}


}
catch(Exception $exc)
{
throw new Exception("Failed to extract candidates from equivalence reports" . $exc->getMessage());
}

return "Added ".$nb." new candidates";
}
*/

function change_candidate_property($annee,$nom,$prenom, $property_name, $newvalue)
{

	$data = (object) array($property_name => $newvalue);

	change_candidate_properties($annee,$nom,$prenom, $data);
}

function change_candidate_properties($annee,$nom,$prenom, $data)
{
	$data = (object) $data;
	$key = generateKey($annee, $nom, $prenom);

	$sql = "SELECT * FROM ".candidates_db.' WHERE cle="'.$key.'";';
	$result = sql_request($sql);

	$candidate = mysql_fetch_object($result);
	if($candidate == false)
	{
		$data = (object) array();
		$data->nom = $nom;
		$data->prenom = $prenom;
		$data->anneecandidature = $annee;
		$candidate = get_or_create_candidate($data);
	}

	foreach($data as $property_name => $newvalue)
		if(!property_exists($candidate,$property_name))
		throw new Exception("No property '".$property_name."' in candidate object");

	$sqlcore = "";
	$first = true;

	$sql = "UPDATE ".candidates_db." SET ";
	foreach($candidate as  $field => $value)
	{
		if (isset($candidate->$field) && isset($data->$field))
		{
			$sql .=$first ? "" : ",";
			$sql .= " ".$field.'="'.mysql_real_escape_string(trim($data->$field)).'" ';
			$first = false;
		}
	}

	$sql .= ' WHERE cle="'.$key.'"';
	sql_request($sql);

}

function link_files_to_candidates($directory)
{
	echo "Linking files to candidates<br/>";

	$candidates = getAllCandidates();

	$directories=array();
	$files = glob($directory . "*" );
	echo "Looking for directories in '".$directory."'<br/>";

	foreach($files as $file)
	{
		if(is_dir($file))
		{
			// "Found directory '".$file."'<br/>";
			$directories[]= $file;
		}
		else
		{
			//echo "Found file '".$file."'<br/>";
		}
	}

	$nb = 0;
	foreach($candidates as $candidate)
		if(find_files($candidate, $directories)) $nb++;

	echo "Found files for ".$nb. "/".count($candidates)."  candidates<br/>";
}

function norm_name($nom)
{
	$nom = replace_accents($nom);
	return strtoupper(str_replace(array(" ","'","-"), array("_","_","_"),$nom));
}

function find_files($candidate , $directories)
{
	if($candidate->nom == "" || $candidate->prenom == "")
		return;

	foreach($directories as $directory)
	{
		if( strpos(norm_name($directory), norm_name($candidate->nom) ) != false && strpos(norm_name($directory), norm_name($candidate->prenom) ) != false)
		{
			echo "Adding directory ".$directory ." to candidate ". $candidate->nom . " " . $candidate->prenom."<br/>";
			change_candidate_property($candidate->anneecandidature, $candidate->nom, $candidate->prenom, "fichiers",$directory);
			return true;
		}
	}
	echo "NO DIRECTORY FOR CANDIDATE ". $candidate->nom . " " . $candidate->prenom."<br/>";
	return false;
}

function injectercandidats()
{
	if(isSecretaire())
	{
		global $fieldsRapportsCandidat;
		global $fieldsCandidatAll;

		/*
		$fieldsToImport = array();

		foreach( $fieldsCandidatAll as $field => $desc)
			if( in_array($field, $fieldsRapportsCandidat))
		{
			echo 'Join '.$field.'<br/>';
				$fieldsToImport[] = $field;
		}
*/
			$reports = getAllReportsOfType("Candidature");
			foreach($reports as $report)
			{
				$values = array();
				$candidate = get_or_create_candidate($report);
				$values["cleindividu"] = $candidate->cle;
				$values['grade'] = $candidate->grade;
				
/*				
				foreach($fieldsToImport as $field)
	*/

				change_report_properties($report->id, $values);
			}

	}
}

function creercandidats()
{
	if(isSecretaire())
	{
		$reports = getAllReportsOfType("Candidature");
		foreach($reports as $report)
		{
			$candidate = get_or_create_candidate($report);
			if($report->cleindividu != $candidate->cle)
				change_report_property($report->id, "cleindividu", $candidate->cle);

			$concours = $candidate->concourspresentes;
			if($concours =="" || $report->concours=="")
				continue;
			$ok = strpos($concours, $report->concours);
			if($ok === false)
			{
				echo 'Adding concours "'.$report->concours.'" to candidate '.$candidate->cle.' with concours = "'.$concours.'"<br/>';
				$annee = $annee = session_year($report->id_session);
				$nom = $candidate->nom;
				$prenom = $candidate->prenom;
				$concours .= " ".$report->concours;
				change_candidate_property($annee, $nom,$prenom,"concourspresentes",$concours);
			}
		}
		$reports = getAllReportsOfType("Equivalence");
		foreach($reports as $report)
		{
			$candidate = get_or_create_candidate($report);
			if($report->cleindividu != $candidate->cle)
				change_report_property($report->id, "cleindividu", $candidate->cle);
		}
	}
}

?>