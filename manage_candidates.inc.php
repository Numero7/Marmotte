<?php 

require_once('config.inc.php');
require_once('manage_sessions.inc.php');

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

	$data2 = $data;

	foreach($candidat_prototypes as $field => $value)
		if(isset($data->$field))
		if($data->$field=="")
		$data2->$field = $value;

	return $data2;
}

function updateCandidateFromRequest($request, $oldannee="")
{
	//rrr();
	global $fieldsIndividualAll;

	$data = (object) array();


	foreach($fieldsIndividualAll as  $field => $value)
		if (isset($request["field".$field]))
		$data->$field = nl2br(trim($request["field".$field]),true);

	$candidate = get_or_create_candidate($data );

	$sqlcore = "";

	$first = true;
	foreach($data as  $field => $value)
	{
		$sqlcore.=$first ? "" : ",";
		$sqlcore.=$field.'="'.mysql_real_escape_string($value).'" ';
		$first = false;
	}
	$sql = "UPDATE ".people_db." SET ".$sqlcore." WHERE nom=\"".$data->nom."\" AND prenom=\"".$data->prenom."\";";

	sql_request($sql);

	$candidate = get_or_create_candidate($data );


	if(isset($request['previousnom']) && isset($request['previousprenom']))
	{
		if($request['previousnom'] != $candidate->nom || $request['previousprenom'] != $candidate->prenom)
		{
			$sql = "UPDATE ".reports_db." SET nom=\"".$candidate->nom."\", prenom=\"".$candidate->prenom."\" WHERE nom =\"".$request['previousnom']."\" AND prenom=\"".$request['previousprenom']."\"";
			sql_request($sql);
			$sql = "DELETE FROM ".people_db." WHERE nom =\"".$request['previousnom']."\" AND prenom=\"".$request['previousprenom']."\"";
			sql_request($sql);
		}
	}


	return $candidate;

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
		$sqlvalues .= ($first ? "" : ",") .'"'.(isset($data->$field) ? $data->$field : $empty_individual[$field]).'"';
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
function get_or_create_candidate($data)
{
	$data = normalizeCandidat($data);

	try
	{
		mysql_query("LOCK TABLES ".people_db." WRITE;");

		$sql = "SELECT * FROM ".people_db.' WHERE nom="'.$data->nom.'" AND prenom="'.$data->prenom.'" ;';

		$result = sql_request($sql);

		$cdata = mysql_fetch_object($result);
		if($cdata == false)
		{
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



function change_candidate_property($annee,$nom,$prenom, $property_name, $newvalue)
{

	$data = (object) array($property_name => $newvalue);

	change_candidate_properties($annee,$nom,$prenom, $data);
}

function change_candidate_properties($annee,$nom,$prenom, $data)
{
	$data = (object) $data;
	$sql = "SELECT * FROM ".people_db.' WHERE nom="'.$nom.'" AND prenom="'.$prenom.'";';
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

	$sql = "UPDATE ".people_db." SET ";
	foreach($candidate as  $field => $value)
	{
		if (isset($candidate->$field) && isset($data->$field))
		{
			$sql .=$first ? "" : ",";
			$sql .= " ".$field.'="'.mysql_real_escape_string(trim($data->$field)).'" ';
			$first = false;
		}
	}

	$sql .= ' WHERE nom="'.$candidate->nom.'" AND prenom="'.$candidate->prenom.'";';
	
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


?>