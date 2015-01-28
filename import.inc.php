<?php 
require_once 'import_csv.inc.php';
require_once 'import_xml.inc.php';

function process_import($type,$suffix, $filename, $subtype = "",$create = false)
{
	$suffix = substr( $filename , strlen($filename)-3 , 3 );
	switch($suffix)
	{
		case "xml": 
		case "txt":
			return import_xml($type, $filename, $subtype);
			break;
		case "csv": 
			return import_csv($type, $filename, $subtype, $create);
			break;
		default:
			throw string("Cannot import file ".$filename." only xml and csv file types accepted.");
	}
}
?>