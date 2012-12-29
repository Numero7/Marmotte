<?php

require_once 'config.inc.php';
require_once('manage_unites.inc.php');
require_once('manage_rapports.inc.php');

/*
 * Upload de fichier csv avec séparateur ; entrées encadrées par des "", encodé en utf-8
et champs dans l'ordre CodeUnite/NomUnite/Nickname/Directeur.
Les données d'un labo avec le même code seront remplacées.
*/
function process_csv($type,$filename,$subtype = "")
{
	switch($type)
	{
		case 'labos':
			return process_labos_csv($filename); break;
		case 'rapporteurs':
			return process_rapporteurs_csv($filename,$subtype); break;
	}
}

function process_labos_csv($filename)
{
	if($file = fopen ( $filename , 'r') )
	{
		
		$nb = 0;
		while(($data = fgetcsv ( $file, 0, ',' , '"' )) != false)
		{
			$nb++;
			$num = count($data);
			if($num != 4)
				continue;
			
			if(addUnit($data[2],$data[0],$data[1],$data[3]) == false)
				return "Failed to add unit ".$data[0];
		}
		return "Uploaded ".$nb." labs information to units database";
	}
	else
	{
		return "Failed to open file ".$filename." for reading";
	}
}


function process_rapporteurs_csv($filename,$subtype)
{
	global $typesRapportsUnites;
	
	$is_unite = array_key_exists($subtype, $typesRapportsUnites);
	if($file = fopen ( $filename , 'r') )
	{
		$unite = "";
		$nom = "";
		$prenom = "";
		$rapporteur = "";
		$grade = "";
		$nb = 0;
		
		
		while(($data = fgetcsv ( $file, 0, ',' , '"' )) != false)
		{
			set_time_limit(0);
			$nb++;
			$num = count($data);
			if($is_unite)
			{
				if($num != 2) continue;
				else
				{
					$unite = $data[0];
					$rapporteur = $data[1];
				}
			}
			else
			{
				if($num != 4)
				{
					echo "Wrong number ".$num." of data cols in csv for people entry.<br/>";
					continue;
				}
				else
				{
					$pieces = explode(" ",$data[0]);
					$unite = $data[1];
					$grade = $data[2];
					$rapporteur = $data[3];
					if(count($pieces) >0)
						$nom = normalizeName($pieces[0]);
					if(count($pieces) >1)
						$prenom = normalizeName($pieces[1]);
				}
			}
				
			if(addVirginReport($subtype,$unite,$nom,$prenom,$grade,$rapporteur) == false)
				return "Failed to add report ".$subtype." ".$unite." ".$nom." ".$prenom;
		}
		return "Uploaded ".$nb." new virgin reports to evaluations database";
	}
	else
	{
		return "Failed to open file ".$filename." for reading";
	}
}

?>