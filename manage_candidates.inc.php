<?php 

require_once('config.inc.php');
require_once('manage_sessions.inc.php');

function checkIfCandidateExists($nom,$prenom)
{
	$sql = "SELECT * FROM ".candidates_db.' WHERE nom="'.$nom.'" AND prenom="'.$prenom.'";';
	$result=mysql_query($sql);
	
	if($result == false)
		throw new Exception("Failed to process sql query ".$sql);
	return	(mysql_num_rows($result) > 0);
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

function add_candidate($data)
{
	global $fieldsCandidat;

	$sqlvalues = "";
	$sqlfields = "";
	$first = true;

	foreach($fieldsCandidat as $field)
	{
		if(isset($data->$field))
		{
			$sqlfields .= ($first ? "" : ",") .$field;
			$sqlvalues .= ($first ? "" : ",") .'"'.$data->$field.'"';
			$first = false;
		}

	}

	$sql = "INSERT INTO ".candidates_db." ($sqlfields) VALUES ($sqlvalues);";

	$result=mysql_query($sql);
	if($result == false)
		throw new Exception("Failed to process sql query ".$sql);

	$new_id = mysql_insert_id();
	return $new_id;
}

function extraction_candidats()
{

	$nb = 0;
	try
	{
		$reports = getAllReportsOfType("Equivalence", current_session_id() );
		foreach($reports as $report)
			if(!checkIfCandidateExists($report->nom,$report->prenom))
			{
				$nb++;
				add_candidate($report);
			}
	}
	catch(Exception $exc)
	{
		throw new Exception("Failed to extract candidates from equivalence reports" . $exc->getMessage());
	}

	return "Added ".$nb." new candidates";
}

?>