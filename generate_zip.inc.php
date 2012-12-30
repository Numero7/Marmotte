<?php 

require_once('generate_pdf.inc.php');
require_once('generate_xml.inc.php');
require_once('config.inc.php');


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

/**
 * Zip files to an archive
 * @param filenames array(string -> string) <p>
 * Collection of pairs of loclfilename and name in the archive.
 * </p>
 * @param localname string[optional] <p>
 * If supplied, this is the local name inside the ZIP archive that will override the filename.
 * </p>
 * @param zipfilename string[optional] <p>
 * the name of the zip file
 * @return bool Returns zip filename on success or false on failure.
 */
function zip_files($filenames,$zipfilename = "reports.zip")
{
	$zip = new ZipArchive();
	if($zip->open($zipfilename,ZipArchive::OVERWRITE | ZipArchive::CREATE) == true)
	{
		foreach($filenames as $localfilename => $zipfilename)
		{
			if(!$zip->addFile($zipfilename, $localfilename))
			{
				echo "Failed to add file ".$zipfilename." to zip file<br/>";
				return false;
			}
		}
		$zip->close();
		return $zipfilename;
	}
	return false;
}

function xmls_to_pdfs($docs)
{
	$xsl = new DOMDocument();
	$xsl->load("xslt/html2.xsl");
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($xsl);

	$filenames = array();

	echo "Processing ".count($docs)." docs<br/>";
	foreach($docs as $doc)
	{
		//it takes time so we tell the server the script is still alive
		set_time_limit(0);
		$nodes =$doc->getElementsByTagName("rapport");
		if($nodes)
		{
			$node = $nodes->item(0);
			$filename = replace_accents(filename_from_node($node)).".pdf";
			$local_filename = replace_accents("reports/".$filename);
			$type = type_from_node($node);
			$html = $proc->transformToXML($node);
			$pdf = HTMLToPDF($html);
			$pdf->Output($local_filename,"F");
			$filenames[$local_filename] = $filename;
		}
	}
	return $filenames;
}
?>
