<?php

require_once('generate_xml.inc.php');

function getReportAsHtml($id_rapport)
{
	$row = getReport($id_rapport);
	
	if(!$row)
	{
		echo 'Pas de rapport avec id '.$id_rapport;
		return;
	}
	
	$doc = rowToXMLDoc($row);
	if(!$doc)
	{
		echo 'Impossible de convertir la requete en xml';
		return;
	}
	
	$html = XMLToHTML($doc);
	
	return $html;
}

function viewReportAsHtml($id_rapport)
{
	$html = getReportAsHtml($id_rapport);
	
	echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr"><head><meta http-equiv="content-type" content="text/html; charset=UTF-8"/></head><body>';
	echo $html;
	echo '</body></html>';
}

function viewReportAsPdf($id_rapport)
{

	$html = getReportAsHtml($id_rapport);
	
	$pdf = HTMLToPDF($html);

	$pdf->Output('rapport.pdf', 'I');
};

?>