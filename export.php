<?php
	include("utils.inc.php");
	$dbh = db_connect($servername,$dbname,$serverlogin,$serverpassword);
    if($dbh!=0)
    {
		if (authenticate())
		{
			$xml = getReportsAsXML();
			header('Content-type: text/xml; charset=utf-8');
			echo $xml->saveXML() . "\n";
		}
	}
?>