<?php

require_once('manage_unites.inc.php');

/*
 * Upload de fichier csv avec séparateur ; entrées encadrées par des "", encodé en utf-8
et champs dans l'ordre CodeUnite/NomUnite/Nickname/Directeur.
Les données d'un labo avec le même code seront remplacées.
*/
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
			
			if(ajout_unite($data[2],$data[0],$data[1],$data[3]) == false)
				return "Failed to add unit ".$data[0];
		}
		return "Uploaded ".$nb." labs information to units database";
	}
	else
	{
		return "Failed to open file ".$filename." for reading";
	}
}

?>