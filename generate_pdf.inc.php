<?php
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
require_once('generate_xml.inc.php');

function getReportAsDOMDoc($id_rapport, $option="")
{
	$row = getReport($id_rapport);
	
	
	if(!$row)
	{
		echo 'Pas de rapport avec id '.$id_rapport;
		return;
	}
	
	if($option!="")
		$row->type = $option;

	$doc = rowToXMLDoc($row);
	if(!$doc)
		echo 'Impossible de convertir la requete en xml';

	return $doc;	
}

function getReportAsHtml($id_rapport)
{
	$doc = getReportAsDOMDoc($id_rapport);
		
	$html = XMLToHTML($doc,type_to_xsl(getReport($id_rapport)->type));
		
	return $html;
}

function viewReportAsHtml($id_rapport)
{
	$html = getReportAsHtml($id_rapport);
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n";
	echo $html;
}

function viewReportAsPdf($id_rapport,$option = "")
{
	$doc = getReportAsDOMDoc($id_rapport, $option);
	$report = getReport($id_rapport);
	
	$type = ($option != "") ? $option : $report->type;

	global $typesRapportsConcours;
	if(isset($typesRapportsConcours[$type]) && $type != "Audition")
		$type = "Classement";
	
	$xsl = type_to_xsl( $type);
	$html = XMLToHTML( $doc , $xsl );	
	$pdf = HTMLToPDF($html);

	$nodes =$doc->getElementsByTagName("rapport");
	if($nodes)
	{
		$filename = filename_from_doc(getReport($id_rapport)).".pdf";
		$pdf->Output($filename, 'D');
	}
};


function HTMLToPDF($html)
{
	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(get_config("secretaire"));
	$section = "Section ".currentSection();
	$pdf->SetAuthor($section);
	$pdf->SetTitle('Rapport de la '.$section);
	$pdf->SetSubject('Rapport de la '.$section);
	$pdf->SetKeywords('Rapport de la '.$section);

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, "15", PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, "15");

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	$l = Array(
			'a_meta_charset' => 'UTF-8',
			'a_meta_dir' => 'ltr',
			'a_meta_language' => 'fr',
			'w_page' => 'page'
	);
	//set some language-dependent strings
	$pdf->setLanguageArray($l);

	// ---------------------------------------------------------

	// set default font subsetting mode
	$pdf->setFontSubsetting(true);

	// Set font
	// dejavusans is a UTF-8 Unicode font, if you only need to
	// print standard ASCII chars, you can use core fonts like
	// helvetica or times to reduce file size.
	$pdf->SetFont('dejavusans', '', 11, '', true);

	// Add a page
	// This method has several options, check the source code documentation for more information.
	$pdf->AddPage();

	$pdf->writeHTML($html);

	$pdf->Close();
	return $pdf;
}

?>