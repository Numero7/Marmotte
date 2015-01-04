<?php

require_once('manage_rapports.inc.php');

function affectersousjurys()
{
	$rows = get_current_selection();
	$users = listUsers();
	global $concours_ouverts;
	global $sous_jurys;

	foreach($users as $login => $data)
	{
		if(isset($data->sousjury))
		{
			foreach($sous_jurys as $concours => $sj)
				foreach($sj as $code => $data)
			{
				$nom = $data["nom"];
				if($code != "")
				{
					if(strpos( $data->sousjury , $code) !== false)
						$user[$login]->sousjurys[$concours] = $code;
				}
			}
		}
	}
	
	
	foreach($rows as $row)
	{
		if(isset($row->rapporteur) && isset($row->concours))
		{
			$rapp = $row->rapporteur;
			$concours = $row->concours;
		
			if(isset($user[$rapp]->sousjurys[$concours]))
			{
				$sj = $user[$rapp]->sousjurys[$concours];
				if(!isset($row->sousjury) || ( isset($row->sousjury) && $row->sousjury != $sj))
					change_report_property($row->id, "sousjury", $sj);
			}
		}
	}
	
	
}

?>