<?php
	header('Content-type: text/xml; charset=utf-8');
	include("utils.inc.php");
	$dbh = db_connect($servername,$dbname,$serverlogin,$serverpassword);
    if($dbh!=0)
    {
		if (authenticate())
		{
			$xml = getReportsAsXML();
			echo $xml->saveXML() . "\n";
		}
	}
?>