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
	if(!isSecretaire())
		throw new Exception("Vous n'avez pas les permissions nécessaires pour importer des données.");

	ini_set('auto_detect_line_endings',TRUE);
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
			throw new Exception("Failed to open file ".$filename." for reading");
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
		{
			throw new Exception("Trop de lignes blanches au début du csv");
		}
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
			$fields[] = real_escape_string($field);
		}

		$with_id = in_array("id",$fields);
		$id_rank = array_search("id",$fields);


		$nbfields = count($fields);
		$nb = 0;
		$errors = "";

		while(($data = fgetcsv ( $file, 0, $sep , $enc ,$esc)) != false)
		{

			if($is_utf8)
				for($i = 0 ; $i < count($data); $i++)
				$is_utf8 = $is_utf8 && mb_check_encoding($data[$i],"UTF-8");

			if(!$is_utf8)
				for($i = 0 ; $i < count($data); $i++)
				$data[$i] = utf8_encode($data[$i]);

			try
			{
				set_time_limit(0);
				if($type == 'evaluations')
				{
					/* First case we update data about an already existing report */
				  /*
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
							$nb++;

							//$output .= "Line ".$nb." : updated data of report ".$id_origine . " (new report has id ".$report->id.")<br/>";
						}
					}
					else
				  */
					{
						/* Second case we create report */
						$properties = array();
						for($i = 0; $i < $nbfields && $i < count($data); $i++)
							if($fields[$i] != "id")
							$properties[$fields[$i]] =  $data[$i];
						$oldsubtype = $subtype;
						$subtype = checkTypeIsSpecified($properties);
						if($subtype == "" && $oldsubtype != "")
							$subtype = $oldsubtype;
						addCsvReport($subtype, $properties);
						$nb++;
					}
				}
				else if ($type == 'unites')
				{
					$properties = array();
					for($i = 0; $i < $nbfields && $i < count($data); $i++)
						$properties[$fields[$i]] =  $data[$i];
					addCsvUnite($properties);
					unset($_SESSION['all_units']);
					$nb++;
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
			return $nb." rapports ou unités ont été ajoutés dans la base.";
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

		if($subtype == "")
			throw new Exception("Cannot add csv report, no type specified, please specify a type in the importation menu");
		else
			$properties["type"] = $subtype;

		global $sgcn_keywords_to_eval_types;

		foreach($sgcn_keywords_to_eval_types as $key => $value)
			if(strcontains($properties["type"],$key))
				$properties["type"] = $value;

			if(!isset($properties["type"]) || $properties["type"] =="")
				throw new Exception("Unimplemented report type '" . $type."'");

			global $typesRapportsAll;
			if(!isset($typesRapportsAll[$properties["type"]]))
			{
				foreach($properties as $key => $value)
					if($key == "Chercheur" || $key == "Nom" || $key == "Prenom")
					{
						$properties["type"] = 'DEChercheur';
						break;
					}
					$properties["type"] = 'Generique';
			}

			global $copies;
			foreach($copies as $old => $new)
				if(isset($properties[$old]) )
				{
					$properties[$new] = $properties[$old];
					if($old == "PUBCONC" || $old =="CONCOURS")
						$properties[$new] = str_replace(" ","",str_replace("/","",$properties[$old]));
					unset($properties[$old]);
				}

				if(isset($properties["unite"]))
					$properties["code"] = $properties["unite"];

				if(isset($properties["SIGLE"]))
					$properties["genre"] =  (strpos($properties["SIGLE"],"Mr") !== false) ? "homme" : ( (strpos($properties["SIGLE"],"Mme") !== false) ? "femme" : "");


				if(isset($properties["grade"]) && !isset($properties["grade_rapport"]))
					$properties["grade_rapport"] = $properties["grade"];

				$properties["rapport"] = "";
				foreach($properties as $key => $value)
					if($value != "")
					$properties["rapport"] .= $key . " : " . $value."\n\n";

				$report = (object) array();

				foreach($properties as $key => $value)
					addToReport($report,$key, replace_accents($value));

				$report->statut = 'doubleaveugle';
				$report->id_session = current_session_id();

				global $typesRapportsChercheurs;

				if( in_array($report->type, $typesRapportsChercheurs) )
				{
					if(isset($report->nom) && isset($report->prenom) && $report->nom != "")
					{					  
						updateCandidateFromData((object) $properties);
						addReport($report,false);
						//						if(isset($report->unite))
						//	updateUnitData($report->unite, (object) $report);
					}
				}
				else
				{
					addReport($report,false);
					//					if(isset($report->unite))
					//	updateUnitData($report->unite, (object) $report);
				}
}



function addCsvUnite($properties)
{
	//no need to process units with empty code list
	if(isSuperUser() && empty($properties["Liste section(s)"]))
		return;

	$code = "";
	$directeur = "";
	$fullname = "";
	$nickname = "";

	$labels = array("Code unité", "code", "Code Unité","Code");

	foreach($properties as $key => $value)
		if(strpos($key,"Code un") == 0 || strpos($key,"code") == 0)
		$code = $value;

	foreach($labels as $label)
		if(isset($properties[$label]) && $properties[$label] != "")
		$code = $properties[$label];

	$code = trim($code);
	if($code =="")
		throw new Exception("Cannot add unit with empty code");

	if(isset($properties["Intitulé unité"]))
		$fullname = trim($properties["Intitulé unité"]);

	if(isset($properties["Responsable prénom"]))
		$directeur .= trim($properties["Responsable prénom"]);

	if(isset($properties["Responsable nom"]))
		$directeur.= " " . trim($properties["Responsable nom"]);

	if(isset($properties["Sigle unité"]))
		$nickname = trim($properties["Sigle unité"]);

	if($nickname == "")
		$nickname = $code;

	//Super user makes global imports
	if(isSuperUser())
	{
		$sql = "DELETE FROM ".units_db." WHERE `code` = \"".real_escape_string($code)."\";";
		sql_request($sql);
		$sections = explode(",", trim($properties["Liste section(s)"]));
		foreach($sections as $section)
		{
				$section = trim($section);
				if(is_numeric($section))
				{
				  /* Todo: We keep nicknames and fullname already set 
				  $sql = "SELECT * FROM ".units_db." WHERE `code`= \"".real_escape_string($code)."\" AND `section`= \"".$section."\";";
				  $result = sql_request($sql);
				  if($row = mysqli_fetch_object($result))
				    {
				    $nickname = $row->nickname; 
				    $fullname = $row->fullname; 
				    }*/
					$values = "\"".real_escape_string($nickname)."\",";
					$values .= "\"".real_escape_string($code)."\",";
					$values .= "\"".real_escape_string($fullname)."\",";
					$values .= "\"".real_escape_string($directeur)."\",";
					$values .= "\"".$section."\"";
					$sql = "INSERT INTO ".units_db." (nickname, code, fullname, directeur, section) VALUES ($values);";
					sql_request($sql);
				}
		}
	}
	//Secretary makes section imports
	else if(isSecretaire())
		addUnit($nickname, $code, $fullname, $directeur);
}


?>