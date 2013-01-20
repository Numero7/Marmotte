<?php

require_once 'config.inc.php';
require_once('manage_unites.inc.php');
require_once('manage_rapports.inc.php');

/*
 * Upload de fichier csv avec séparateur ; entrées encadrées par des "", encodé en utf-8
et champs dans l'ordre CodeUnite/NomUnite/Nickname/Directeur.
Les données d'un labo avec le même code seront remplacées.
*/
function process_csv($type,$filename, $subtype)
{
	global $fieldsAll;
	global $csv_composite_fields;
	
	
	if($file = fopen ( $filename , 'r') )
	{

		$fields = fgetcsv ( $file, 0, ',' , '"' );
		foreach($fields as $field)
			if($field != "" && !key_exists($field, $fieldsAll) && !key_exists($field, $csv_composite_fields))
			throw new Exception("No field with name ". $field." in evaluations or in composite fields list");
		$nbfields = count($fields);
		
		$nb = 0;
		$errors = "";
		while(($data = fgetcsv ( $file, 0, ',' , '"' )) != false)
		{
			$nb++;
			try
			{
				set_time_limit(0);
				if($type == 'rapports')
					addCsvReport($subtype, $data, $fields);
				else if ($type == 'unite')
					addCsvUnite($data, $fields);
				else
					throw new Exception("Unknown generic csv report type \'".$type."\'");
			}
			catch(Exception $exc)
			{
				$errors .= "\t".$exc."\n";
			}
		}
		if($errors != "")
			return "Uploaded ".$nb." reports of type ".$type."/".$subtype." to database with errors:\n\t".$errors;
		else
			return "Uploaded ".$nb." reports of type ".$type."/".$subtype." to database";
	}
	else
	{
		throw new Exception("Failed to open file ".$filename." for reading");
	}
}

function addToReport($report, $field, $data)
{
	global $csv_composite_fields;
	global $csv_preprocessing;
	global $fieldsAll;
	global $fieldsTypes;

	if(isset($csv_composite_fields[$field]))
	{
		$subfields = $csv_composite_fields[$field];
		$pieces = explode(" ",$data, count($subfields));
		$i;
		for($i = 0; $i < count($pieces); $i++)
			addToReport($report, $subfields[$i], $pieces[$i]);
	}
	else
	{
		if(key_exists($field, $fieldsAll))
		{
			$preproc = preprocess($field, $data);
			$report->$field .= $preproc;
		}
	}
}

function preprocess($field, $data)
{
	global $csv_preprocessing;
	global $fieldsTypes;
	
	$result = $data;
	
	if(isset($csv_preprocessing[$field]))
	{
		$result = call_user_func($csv_preprocessing[$field],$data);
	}
	else if(isset($fieldsTypes[$field]))
	{
		$type = $fieldsTypes[$field];
		if(isset($csv_preprocessing[$type]))
			$result =  call_user_func($csv_preprocessing[$type],$data);
	}
	else
	{
		$result = $data;
	}
	return $result;	
}


function getDocFromCsv($data, $fields)
{

	$report = (object) array();

	$m = min(count($data), count($fields));
	for($i = 0; $i < $m; $i++)
		addToReport($report,$fields[$i], $data[$i]);
	
	return $report;
	
}

function addCsvReport($type, $data, $fields)
{
	global $report_prototypes;
	
	$report = getDocFromCsv($data,$fields);
	$report->statut = 'vierge';
	if($type != "")
		$report->type = $type;
	addReport($report,false);
}

function addCsvUnite($type, $data, $fields)
{
	$unite = getDocFromCsv($data,$fields);
	insertUniteInDatabase($report);
}



?>