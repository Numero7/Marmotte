<?php

require_once('manage_rapports.inc.php');

function affectersousjurys()
{
	$rows = get_current_selection();
	$users = listUsers();
	global $concours_ouverts;
	
	foreach($rows as $row)
	{
		$sousjury = "";
		if(isset($row->sousjury))
			$sousjury = $row->sousjury;

		$rapp = "";
		if(isset($row->rapporteur))
			$rapp = $row->rapporteur;
		
		$sousjury2 = "";
		if( isset($users[$rapp]) && isset($users[$rapp]["sousjury"]))
			$sousjury2 = $users[$rapp]["sousjury"];
		
	rrr();	
	}
}

?>