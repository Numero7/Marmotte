<?php

require_once 'config.inc.php';
require_once 'import.inc.php';
require_once('manage_unites.inc.php');
require_once('manage_rapports.inc.php');

/*
 * Upload de fichier csv avec séparateur ; entrées encadrées par des "", encodé en utf-8
Les données d'un labo avec le même code seront remplacées.
*/

function fixEncoding($in_str)
{
	$cur_encoding = mb_detect_encoding($in_str) ;
	if($cur_encoding == "UTF-8" && mb_check_encoding($in_str,"UTF-8"))
		return $in_str;
	else
		return utf8_encode($in_str);
}

function import_csv($type,$filename, $subtype = "", $create = false, $sep="?", $del="\n",$enc='"', $esc='\\')
{
	ini_set('auto_detect_line_endings',TRUE);
	global $fieldsAll;
	global $csv_composite_fields;
	global $fieldsUnitsDB;

	$output = "";
	
	
	if($sep == "?")
	{
		/* auto detecte if data are separataed by ; or \t */
		if($file = fopen ( $filename , 'r') )
		{
			$tabnb = 0;
			$comanb = 0;
			while(($line = fgets($file)) !== false){
				$tabnb += substr_count($line,"\t");
				$comanb += substr_count($line,";");
			}
			if($comanb > 10 * $tabnb)
				$sep = ";";
			else if ($tabnb > 10 * $comanb)
				$sep = "\t";
			else
				$sep = ";";
			fclose($file);
		}
			else
			{
				throw new Exception("Failed to open file ".$filename." for reading");
			}
	}
	
	
	
	if($file = fopen ( $filename , 'r') )
	{
		$is_utf8 = true;

		
		
		/* skip lines starting with empty fields */
		$rawfields = array();
		$max = 100;
		while( $max > 1 && ( (count($rawfields)==0 || $rawfields[0]=="") || ($type == "unites" && isset($rawfields[0]) && $rawfields[0] != "Obs") ) )
		{
			$rawfields = fgetcsv ( $file, 0, $sep , $enc, $esc );
			$max --;
		}
		if($max <=2)
			throw new Exception("Trop de lignes blanches au début du csv");
		
		if($is_utf8)
			foreach($rawfields as $field)
		 $is_utf8 = $is_utf8 && mb_check_encoding($field,"UTF-8");
		
		if(!$is_utf8)
		{
			for($i = 0 ; $i < count($rawfields); $i++)
				$rawfields[$i] = utf8_encode($rawfields[$i]);
		}
		
		$fields = array();
		foreach($rawfields as $field)
		{
			if($field == "")
				break;
			$fields[] = mysql_real_escape_string($field);
		}

		$with_id = in_array("id",$fields);
		$id_rank = array_search("id",$fields);

		if(!isSecretaire())
		{
			if(!$with_id)
				throw new Exception("Vous n'avez pas les permissions nécessaires pour importer des rapports au format csv sans fournir un champ préciser l'id du rapport original.");
			if($type != 'evaluations')
				throw new Exception("Vous n'avez pas les permissions nécessaires pour importer des données autres que des évaluations.");
		}


		$nbfields = count($fields);

		$nb = 0;
		$errors = "";


		while(($data = fgetcsv ( $file, 0, $sep , $enc ,$esc)) != false)
		{
			$nb++;

			if($is_utf8)
				for($i = 0 ; $i < count($data); $i++)
				$is_utf8 = $is_utf8 && mb_check_encoding($data[$i],"UTF-8");

			if(!$is_utf8)
			{
				for($i = 0 ; $i < count($data); $i++)
				$data[$i] = utf8_encode($data[$i]);
			}


			try
			{
				set_time_limit(0);
				if($type == 'evaluations')
				{
					/* First case we update data about an already existing report */
					if(!$create && $with_id)
					{
						if(count($data) != $nbfields)
							$errors .= "Line ".$nb." : failed to process : wrong number of data fields ".count(data)." instead of ".$nbfields." like the first line<br/>";
						else
						{
							$id_origine = $data[$id_rank];
							$properties = array();
							for($i = 0; $i < $nbfields; $i++)
								$properties[$fields[$i]] =  $data[$i];
							$report = change_report_properties($id_origine, $properties);
							//$output .= "Line ".$nb." : updated data of report ".$id_origine . " (new report has id ".$report->id.")<br/>";
						}
					}
					else
					{			
					/* Second case we create report */
						$properties = array();
						for($i = 0; $i < $nbfields && $i < count($data); $i++)
						{
							if($fields[$i] != "id")
								$properties[$fields[$i]] =  $data[$i];
						}
						$oldsubtype = $subtype;
						$subtype = checkTypeIsSpecified($properties);
						if($subtype == "" && $oldsubtype != "")
							$subtype = $oldsubtype;
//						if($subtype == "" )
	//						throw new Exception("No type specified in the csv, please specify the type of the evaluation to import");
						addCsvReport($subtype, $properties);
					}
				}
				else if ($type == 'unites')
				{
					$properties = array();
					for($i = 0; $i < $nbfields && $i < count($data); $i++)
						$properties[$fields[$i]] =  $data[$i];
					addCsvUnite($properties);
				}
				else
					throw new Exception("Unknown generic csv report type \'".$type."\'");
			}
			catch(Exception $exc)
			{
				$errors .= "Line ".$nb." : failed to process : ". $exc->getMessage()."<br/>";
			}
		}
		fclose($file);
		if($errors != "")
			return $nb." rapports ou unités ont été ajoutés dans la base.<br/> Erreurs:<br/>\n\t".$errors."<br/> and output <br/>".$output;
		else if($output != "")
			return $nb." rapports ou unités ont été ajoutés dans la base <br/>".$output;
		else
			return $nb." rapports ont été ajoutés dans la base.";
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
			if(isset($report->$field))
				$report->$field .= $preproc;
			else
				$report->$field = $preproc;
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
		addToReport($report,$fields[$i], replace_accents($data[$i]));

	return $report;

}

function strcontains($haystack, $needle)
{
	return (strpos($haystack,$needle) !== false);	
}

/*
 * Check type is psecified and return this type
* returns "" is no type
*/

function checkTypeIsSpecified($properties)
{
	global $possible_type_labels;

	foreach($possible_type_labels as $label)
	if( isset( $properties[$label] ) &&  $properties[$label] != "" )
	{
		return $properties[$label];
	}
	
	return "";
	
}

function addCsvReport($subtype, $properties)
{
	//Check emptiness
	$non_empty = false;
	foreach($properties as $key => $value)
		if($value != "")
		$non_empty = true;
	
	if(!$non_empty)
		return;
	
	//first we try to get the type of the evaluation
	$grade = "";

	if($subtype == "")
	{
		foreach($properties as $key => $value)
		{
			if($key == "Code Colloque")
			{
				$subtype = "Colloque";
				break;
			}
			if($key == "Titre" || $key == "TITRE")
			{
				$subtype = "Ecole";
				break;
			}
		}
	}
	
	if($subtype == "")
		throw new Exception("Cannot add csv report, no type specified, please specify a type in the importation menu");
	else
		$properties["type"] = $subtype;

	global $sgcn_keywords_to_eval_types;
	
	foreach($sgcn_keywords_to_eval_types as $key => $value)
		if(strcontains($properties["type"],$key))
		{
			
			if($key == "promotion")
			{
				if(strcontains($properties["type"],"CR1"))
					$properties["grade_rapport"] = "CR1";
				if(strcontains($properties["type"],"DR1"))
					$properties["grade_rapport"] = "DR1";
				if(strcontains($properties["type"],"DRCE1"))
					$properties["grade_rapport"] = "DRCE1";
				if(strcontains($properties["type"],"DRCE2"))
					$properties["grade_rapport"] = "DRCE2";
				$properties["type"] = "Promotion";
			}
			else if($key == "Evaluation")
			{
				if(isset($properties["Phase évaluation"]) && ($properties["Phase évaluation"] =="mi-vague"))
					$properties["type"] = 'Evaluation-MiVague';
				else
					$properties["type"] = 'Evaluation-Vague';
			}
			else
				$properties["type"] = $value;
		}		

	
	if(!isset($properties["type"]) || $properties["type"] =="")	
	{
		throw new Exception("Unimplemented report type '" . $type."'");
	}

	global $typesRapports;
	if(!isset($typesRapports[$properties["type"]]))
	{
		foreach($properties as $key => $value)
		{
			if($key == "Chercheur" || $key == "Nom" || $key == "Prenom")
			{
				$properties["type"] = 'GeneriqueChercheur';
				break;
			}
		}
		$properties["type"] = 'Generique';
	}
	
	
	
//dirty, should be a parameter
	$copies = array(
			"Nom" => "nom",
			"NOMUSUEL" => "nom",
			"Prénom" => "prenom",
			"PRENOM" => "prenom",
			"GRAD_CONC" => "grade_rapport",
			"Grade" => "grade",
			"Directeur" => "directeur",
			"Affectation #1" => "unite",
			"Code Unité" => "unite",
			"Code unité" => "unite",
			"Code Colloque" => "unite",
			"Affectation #1" => "unite",
			"Titre" => "nom",
			"Responsable principal" => "prenom",
			"EXPERIENC" => "anneesequivalence",
			"PUBCONC" => "concours"
	);
			
	foreach($copies as $old => $new)
		if(isset($properties[$old]) )
	{
		$properties[$new] = $properties[$old];
		if($old == "PUBCONC")
			$properties[$new] = str_replace("/","",$properties[$old]);
			
		unset($properties[$old]);
	}

	
	if(isset($properties["unite"]))
		$properties["code"] = $properties["unite"];
	if(isset($properties["grade"]) && !isset($properties["grade_rapport"]))
		$properties["grade_rapport"] = $properties["grade"];
	
	
	$properties["rapport"] = "";
	foreach($properties as $key => $value)
		if($value != "")
		$properties["rapport"] .= $key . " : " . $value."\n\n";
	
	$report = (object) array();
	
	foreach($properties as $key => $value)
		addToReport($report,$key, replace_accents($value));

	$report->statut = 'vierge';
	$report->id_session = current_session_id();
	
	global $typesRapportsChercheurs;
	global $typesRapportsConcours;
	
	if( in_array($report->type, $typesRapportsChercheurs) || in_array($report->type, $typesRapportsConcours) )
	{
		if(isset($report->nom) && isset($report->prenom) && $report->nom != "")
		{
		updateCandidateFromData((object) $properties);
		addReport($report,false);
	
		if(isset($report->unite))
			updateUnitData($report->unite, (object) $report);
		}
	}
	else
	{
		addReport($report,false);
		
		if(isset($report->unite))
			updateUnitData($report->unite, (object) $report);
		
	}
	
}


/*
function addCsvReport($type, $data, $fields)
{

	if(isset($data["code"]))
		$data["unite"] = $data["code"];
	if(isset($data["unite"]))
		$data["code"] = $data["unite"];

	$non_empty = false;
	foreach($data as $d)
		if($d != "")
		$non_empty = true;

	if(!$non_empty)
		return;

	$report = getDocFromCsv($data,$fields);
	$report->statut = 'vierge';

	if(isset($report->type) && $report->type != "")
	{
		$type = $report->type;
	}
	else if($type != "")
	{
		$report->type = $type;
	}
	else
	{
		echo "Skipping report</br>";
		return;
	}

	$report->id_session = current_session_id();

	global $typesRapportsChercheurs;
	global $typesRapportsConcours;

	if( in_array($report->type, $typesRapportsChercheurs) || in_array($report->type, $typesRapportsConcours) )
		updateCandidateFromData((object) $data);

	addReport($report,false);

	if(isset($data->unite))
		updateUnitData($data->unite, (object) $data);
}
*/

function addCsvUnite($properties)
{
	$code = "";
	$directeur = "";
	$fullname = "";
	$nickname = "";
	
	$labels = array("Code unité", "code", "Code Unité","Code");
	
	foreach($properties as $key => $value)
		if(strpos($key,"Code") == 0 || strpos($key,"code") == 0)
		$code = $value;
	
	foreach($labels as $label)
	{		
		if(isset($properties[$label]) && $properties[$label] != "")
		{
			$code = $properties[$label];
		}
	}
	
	if($code =="")
		throw new Exception("Cannot add unit with empty code");
	
	if(isset($properties["Intitulé unité"]))
		$fullname = $properties["Intitulé unité"];
	
	if(isset($properties["Responsable prénom"]))
		$directeur .= $properties["Responsable prénom"];
	
	if(isset($properties["Responsable nom"]))
		$directeur.= " " . $properties["Responsable nom"];

	if(isset($properties["Sigle unité"]))
		$nickname = " " . $properties["Sigle unité"];
	
	addUnit($nickname, $code, $fullname, $directeur);
}



?>