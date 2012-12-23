<?php
require_once("utils.inc.php");
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
require_once('manage_users.inc.php');
require_once('generate_xml.inc.php');
require_once('generate_pdf.inc.php');
require_once('generate_zip.inc.php');

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
			if (isset($_REQUEST["save"]) and isset($_REQUEST["avis"]) and isset($_REQUEST["rapport"]))
			{
				$idtosave = $_REQUEST["save"];
				$avis = $_REQUEST["avis"];
				$rapport = $_REQUEST["rapport"];
				updateRapportAvis($idtosave,$avis,$rapport);
			}
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
				$id_edit = -1;
				if (isset($_REQUEST["id_edit"]))
				{
					$id_edit = $_REQUEST["id_edit"];
				}

				$conf = $typeExports[$type];
				$mime = $conf["mime"];
				$xslpath = $conf["xsl"];

				if($type=="latex" || $type=="pdf")
				{
					$xmls = getReportsAsXMLArray($id_session,$type_eval,$sort_crit,$login_rapp);
					
					$filename = "";
					if($type=="latex")
						$filename=xmls_to_zipped_tex($xmls);

					if($type=="pdf")
						$filename=xmls_to_zipped_pdf($xmls);

					if($filename == "")
					{
						echo "Failed to generate zip file";
						return;
					}
					
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
				else
				{
					$xml = getReportsAsXML($id_session,$type_eval,$sort_crit,$login_rapp,$id_edit);
					header("Content-type: $mime; charset=utf-8");
					$xsl = new DOMDocument();
					$xsl->load($xslpath);
					$proc = new XSLTProcessor();
					$proc->importStyleSheet($xsl);
					echo $proc->transformToXML($xml);
				}
			}
			else
			{
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
}
?>