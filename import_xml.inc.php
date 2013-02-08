<?php 

require_once('xml_tools.inc.php');

function import_xml_report($node)
{
	$data = xml_node_to_array($node);
	$id_origine = isset($data['id']) ? $data['id'] : 0;
	return change_report_properties($id_origine, $data);
}

function import_xml($type, $filename)
{

	$output = "";

	$doc = new DOMDocument('1.0','utf-8');
	if(!$doc->load($filename))
		throw new Exception("Failed to parse xml file ".$filename);

	$reports = $doc->getElementsByTagName("evaluation");
	if(count($reports) < 1)
		throw new Exception("Failed to find node with tag <rapport> in file ".$filename);

	//echo("Importing ".count($reports)." reports...<br/>");


	$nb = 0;
	foreach($reports as $report)
	{
		$nb++;
		try
		{
			$newreport = import_xml_report($report);
			$output .= "Node ".$nb." : updated data (new report has id #".$newreport->id.")<br/>";
		}
		catch(Exception $e)
		{
			$output .= "Node ".$nb." : failed to process : ". $e->getMessage()."<br/>";
		}
	}
	
		return "Tried to upload ".$nb." reports to database.<br/>Output: <br/>".$output;
}

?>