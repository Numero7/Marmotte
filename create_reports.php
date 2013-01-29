<?php header('Content-type: text/html; charset=utf-8');?>
<?php
require_once 'utils.inc.php';
require_once 'generate_pdf.inc.php';
require_once 'generate_zip.inc.php';

//load xml file
$doc = new DOMDocument("1.0","utf-8");
$doc->load('reports/reports.xml');

$root = $doc->getElementsByTagName("rapports")->item(0);
$reports = $root->childNodes;

$zip = isset($_REQUEST['zip_files']);


$next_report = NULL;
$filenames = array();

$html  = "<p><table><tr>";

foreach($reports as $report)
{
	if(!isset($report->hasAttribute))
	{
		continue;
	}
	
	$is_done = $report->hasAttribute('done');

	$filename = $report->getAttribute('filename').".pdf";
	$filenames['reports/'.$filename] = $filename;
		
	if(!$is_done)
	{
		$html .= '<tr><td>'.$filename.'</td>';
		if($next_report == NULL)
		{
			$next_report = $report;
			$html .= '<td><font color="red">Processing...</font></td></tr>';
		}
		else
		{
			$html .= '<td>Todo</td></tr>';
		}

	}
	//echo if($report->attributes->getNamedItem('status') == '')
}

$html .="</tr></table></p>";
$html .="<a href=\"index.php?action=view\">Retour au site</a>";



if($next_report != NULL)
{
	echo $html;

	$xsl = new DOMDocument("1.0","UTF-8");
	$xsl->load("xslt/html2.xsl");
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($xsl);

	$filename = 'reports/'.$next_report->getAttribute('filename').".pdf";

	$subreport = new DOMDocument("1.0","UTF-8");
	$node = $subreport->importNode($next_report,true);
	$subreport->appendChild($node);
	$html = $proc->transformToXML($subreport);

	
	$pdf = HTMLToPDF($html);
	$pdf->Output($filename,"F");

	$next_report->setAttribute('done','');

	$doc->save('reports/reports.xml');
	
	?>
<script>window.location = 'create_reports.php<?php if($zip) echo "?zip_files=";?>'</script>
<?php
}
else if($zip)
{
	$filenames = array();

	foreach($reports as $report)
	{
		if($report->hasAttribute('done'))
		{
			$filename = $report->getAttribute('filename').".pdf";
			$filenames['reports/'.$filename] = $filename;
		}
	}


	try
	{
		$filename= zip_files($filenames,'zips/reports.zip');

		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: public");
		header("Content-Description: File Transfer");
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"reports.zip\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize("zips/reports.zip"));

		ob_clean();
		flush();

		readfile('zips/reports.zip');

	}
	catch(Exception $exc)
	{
		echo "Failed to generate zip file: ".$exc->getMessage();
	}
}
else
{
	?>
<script>window.location = 'reports/'</script>
<?php
}
?>