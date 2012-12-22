<?php
require_once("utils.inc.php");
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');

function viewReportAsPdf($id_rapport)
{
	$row = getReport($id_rapport);
	
	if(!$row)
	{
		echo 'Pas de rapport avec id '.$id_rapport;
		return;
	}
	
	$doc = rowToXMLDoc($row);
	$html = XMLToHTML($doc);
	
	$pdf = HTMLToPDF($html);
	
	$pdf->Output('rapport.pdf', 'I');
};

$dbh = db_connect($servername,$dbname,$serverlogin,$serverpassword);
if($dbh!=0)
{
	if (authenticate())
	{
		$action="single";
		if (isset($_REQUEST["action"]))
		{
			$action = $_REQUEST["action"];
		}

		if($action=="single")
		{
			$id="-1";
			if (isset($_REQUEST["id"]))
			{
				$id = $_REQUEST["id"];
			}
			viewReportAsPdf($id);
		}
		else if($action=="group")
		{
			$type = "xml";
			if (isset($_REQUEST["type"]))
			{
				$type = $_REQUEST["type"];
			}
			if (isset($typeExports[$type]))
			{
				$id_session = -1;
				if (isset($_REQUEST["id_session"]))
				{
					$id_session = $_REQUEST["id_session"];
				}
				$type_eval = "";
				if (isset($_REQUEST["type_eval"]))
				{
					$type_eval = $_REQUEST["type_eval"];
				}
				$login_rapp = "";
				if (isset($_REQUEST["login_rapp"]))
				{
					$login_rapp = $_REQUEST["login_rapp"];
				}

				$sort_crit = "";
				if (isset($_REQUEST["sort"]))
				{
					$sort_crit = $_REQUEST["sort"];
				}
				$xml = getReportsAsXML($id_session,$type_eval,$sort_crit,$login_rapp);

				$conf = $typeExports[$type];
				$mime = $conf["mime"];
				$xslpath = $conf["xsl"];

				if($type=="xml")
				{
					header("Content-type: $mime; charset=utf-8");
					$xsl = new DOMDocument();
					$xsl->load($xslpath);
					$proc = new XSLTProcessor();
					$proc->importStyleSheet($xsl);
					echo $proc->transformToXML($xml);
				}
				else if($type=="latex" || $type=="pdf")
				{
					$filename = "";
					if($type=="latex")
						$filename=xml_to_zipped_tex($xml);
					else
						$filename=xml_to_zipped_pdf($xml);
						
					$filepath="./";

					header("Pragma: public");
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Cache-Control: public");
					header("Content-Description: File Transfer");
					header("Content-type: application/octet-stream");
					header("Content-Disposition: attachment; filename=\"".$filename."\"");
					header("Content-Transfer-Encoding: binary");
					header("Content-Length: ".filesize($filename));
					ob_clean();
					flush();
					readfile($filename);
				}
				
			}
			?>
<html>
<head>
<title>Erreur : Format indisponible</title>
</head>
<body>
	<strong>Error : <?php echo $type;?>
	</strong> n'est pas un type d'export valide.
</body>
</html>
<?php				
		}

	}
}
?>