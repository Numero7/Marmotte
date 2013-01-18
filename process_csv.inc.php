<?php

require_once 'config.inc.php';
require_once('manage_unites.inc.php');
require_once('manage_rapports.inc.php');

/*
 * Upload de fichier csv avec séparateur ; entrées encadrées par des "", encodé en utf-8
et champs dans l'ordre CodeUnite/NomUnite/Nickname/Directeur.
Les données d'un labo avec le même code seront remplacées.
*/
function process_csv($type,$filename, $subtype, $fields="")
{
	global $type_rapport_to_csv_fields;

	if($fields == "")
	{
		if(!isset($type_rapport_to_csv_fields[$subtype]))
			throw new Exception("No fields provided and no default csv import fields for report typ \'".$type."\'");
		else
			$fields = $type_rapport_to_csv_fields[$subtype];
	}
	$nbfields = count($fields);

	if($file = fopen ( $filename , 'r') )
	{
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
				$error .= "\t".$exc."\n";
			}
		}
		if($errors != "")
			return "Uploaded ".$nb." reports of type ".$type."/".$subtype." to database with errors:\n\t".$errors;
		else
			return "Uploaded ".$nb." labs information to units database";
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

	if(isset($csv_composite_fields[$field]))
	{
		$subfields = $csv_composite_fields[$field];
		$pieces = explode(" ",$data, count($subfields));
		for($i = 0; $i < count($pieces); $i++)
			addToReport($report, $subfields[i], $pieces[i]);
	}
	else
	{
		if(isset($report->$field))
		{
			if(isset($csv_preprocessing[$field]))
				$data = $csv_preprocessing[$field]($data);
			$report->$field .= $data;
		}
	}
}

function getDocFromCsv($data, $fields)
{
	global $empty_report;

	$report = (object) $empty_report;

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
	$report->type = $type;
	addReport($report,false);
}

function addCsvUnite($type, $data, $fields)
{
	$unite = getDocFromCsv($data,$fields);
	insertUniteInDatabase($report);
}



?>