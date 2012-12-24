<?php


/*
 * Upload de fichier csv avec séparateur ; entrées encadrées par des "", encodé en utf-8
et champs dans l'ordre CodeUnite/NomUnite/Nickname/Directeur.
Les données d'un labo avec le même code seront remplacées.
*/
function process_labos_csv($filename)
{
	if($file = fopen ( $filename , 'r') )
	{
		$fields = "code, fullname, nickname, directeur";
		$insertcoma = false;
		$nb = 0;
		while(($data = fgetcsv ( $file, 0, ',' , '"' )) != false)
		{
			$nb++;
			$values ="";
			$num = count($data);
			if($num != 4)
				continue;
			$sql = "DELETE FROM units WHERE code = \"".$data[0]."\";";
			mysql_query($sql);

			for($i = 0; $i <4; $i++)
			{
				$values .= "\"".mysql_real_escape_string($data[$i])."\"";
				if($i < 3)
					$values .= ",";
			}
			
			$sql = "INSERT INTO units ($fields) VALUES ($values);";
			$result = mysql_query($sql);
			
			if($result = false)
				return "Failed to process query ".$sql;
		}
		return "Uploaded ".$nb." labs information to units database";
	}
	else
	{
		return "Failed to open file ".$filename." for reading";
	}
}

?>