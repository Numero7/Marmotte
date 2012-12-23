<?php

require_once('generate_xml.inc.php');


function viewReportAsPdf($id_rapport)
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

	$pdf = HTMLToPDF($html);

	$pdf->Output('rapport.pdf', 'I');
};

?>