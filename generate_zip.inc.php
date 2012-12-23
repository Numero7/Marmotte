<?php 

require_once('generate_pdf.inc.php');
require_once('generate_xml.inc.php');
require_once('config.inc.php');

//Returns the name of the zip file
function filename_from_node(DOMNode $node)
{
	global $typesRapportsUnites;
	
	$nom = "";
	$prenom = "";
	$grade = "";
	$unite = "";
	$type = "";
	$session = "Session";

	foreach($node->childNodes as $child)
	{
		if($child->nodeName == "nom")
			$nom = $child->nodeValue;
		else if($child->nodeName == "prenom")
			$prenom = $child->nodeValue;
		else if($child->nodeName == "grade")
			$grade = $child->nodeValue;
		else if($child->nodeName == "unite")
			$unite = $child->nodeValue;
		else if($child->nodeName == "type")
			$type = $child->nodeValue;
		else if($child->nodeName == "session")
			$session = $child->nodeValue;
	}

	if(array_key_exists($type,$typesRapportsUnites))
		return $session."-".$type."-".$unite;
	else
		return $session." - ".$type." - ".$grade." - ".$nom."_".$prenom;
}

function type_from_node(DOMNode $node)
{
	foreach($node->childNodes as $child)
		if($child->nodeName == "type")
		return $child->nodeValue;
	return "";
}

function xmls_to_zipped_tex($docs)
{
	$xsl = new DOMDocument();
	$xsl->load("xslt/latex_eval.xsl");
	$proc_eval = new XSLTProcessor();
	$proc_eval->importStyleSheet($xsl);

	$proc = $proc_eval;
	$processors = array(
			'Evaluation-Vague' => $proc_eval,
			'Evaluation-MiVague' => $proc_eval,
			'Promotion' => $proc,
			'Candidature' => $proc,
			'Suivi-PostEvaluation' => $proc,
			'Titularisation' => $proc,
			'Confirmation-Affectation' => $proc,
			'Changement-Direction' => $proc,
			'Renouvellement' => $proc,
			'Expertise' => $proc,
			'Ecole' => $proc,
			'Comité-Evaluation' => $proc,
			'' => $proc
	);

	$zip = new ZipArchive();
	if($zip->open('reports_latex.zip',ZipArchive::OVERWRITE))
	{

		$zip->addFromString("compile.bat", "for /r %%x in (*.tex) do pdflatex \"%%x\"\r\ndel *.log\r\ndel *.aux");
		$zip->addFile("latex/CN.png","CN.png");
		$zip->addFile("latex/CNRSlogo.png","CNRSlogo.png");
		$zip->addFile("latex/signature.jpg","signature.jpg");

		foreach($docs as $doc)
		{
			set_time_limit(0);
			$nodes =$doc->getElementsByTagName("rapport");
			if($nodes)
			{
				$node = $nodes->item(0);
				$filename = "reports/".filename_from_node($node).".tex";
				$type = type_from_node($node);
				$zip->addFromString($filename,$processors[$type]->transformToXML($node));
			}
		}

		$zip->close();
		return "reports_latex.zip";

	}
	return "";
}

function xmls_to_zipped_pdf($docs)
{
	$xsl = new DOMDocument();
	$xsl->load("xslt/html.xsl");
	$proc_eval = new XSLTProcessor();
	$proc_eval->importStyleSheet($xsl);

	$proc = $proc_eval;

	$processors = array(
			'Evaluation-Vague' => $proc_eval,
			'Evaluation-MiVague' => $proc_eval,
			'Promotion' => $proc,
			'Candidature' => $proc,
			'Suivi-PostEvaluation' => $proc,
			'Titularisation' => $proc,
			'Confirmation-Affectation' => $proc,
			'Changement-Direction' => $proc,
			'Renouvellement' => $proc,
			'Expertise' => $proc,
			'Ecole' => $proc,
			'Comité-Evaluation' => $proc,
			'' => $proc
	);

	$zip = new ZipArchive();
	if($zip->open('reports_pdf.zip',ZipArchive::OVERWRITE | ZipArchive::CREATE) == true)
	{
		foreach($docs as $doc)
		{
			//it takes time so we tell the server the script is still alive
			set_time_limit(0);
			$nodes =$doc->getElementsByTagName("rapport");
			if($nodes)
			{
				$node = $nodes->item(0);
				$filename = filename_from_node($node).".pdf";
				$local_filename = "reports/".$filename;
				$type = type_from_node($node);
				$html = $processors[$type]->transformToXML($node);
				$pdf = HTMLToPDF($html);
				$pdf->Output($local_filename,"F");
				$zip->addFromString($filename, $pdf->Output($local_filename,"S"));
			}
		}

		$zip->close();
		return "reports_pdf.zip";

	}
	return "";
}
?>
