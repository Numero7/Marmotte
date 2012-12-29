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
		$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "single";
		$id= isset($_REQUEST["id"]) ? $_REQUEST["id"] : "-1";
		
		switch($action)
		{
			case 'viewpdf':
					viewReportAsPdf($id); break;
			case 'viewhtml':
					viewReportAsHtml($id);	break;
			case 'group':
				{
					if (isset($_REQUEST["save"]) and isset($_REQUEST["avis"]) and isset($_REQUEST["rapport"]))
					{
						$idtosave = $_REQUEST["save"];
						$avis = $_REQUEST["avis"];
						$rapport = $_REQUEST["rapport"];
						if (!isset($_REQUEST["cancel"]))
						{
							updateRapportAvis($idtosave,$avis,$rapport);
						}
					}
					$type = isset($_REQUEST["type"]) ? $_REQUEST["type"] :  "xml";
					if (isset($typeExports[$type]))
					{
						$statut = isset($_REQUEST["statut"]) ? $_REQUEST["statut"] : "";
						$id_session = isset($_REQUEST["id_session"]) ? $_REQUEST["id_session"] : -1;
						$type_eval = isset($_REQUEST["type_eval"]) ? $_REQUEST["type_eval"] : "";
						$login_rapp = isset($_REQUEST["login_rapp"]) ? $_REQUEST["login_rapp"] : "";
						$sort_crit = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "";
						$id_edit = isset($_REQUEST["id_edit"]) ? $_REQUEST["id_edit"] : -1;

						$conf = $typeExports[$type];
						$mime = $conf["mime"];
						$xslpath = $conf["xsl"];

						if($type=="latex" || $type=="pdf")
						{
							$xmls = getReportsAsXMLArray($statut, $id_session,$type_eval,$sort_crit,$login_rapp);

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
							$xml = getReportsAsXML($statut,$id_session,$type_eval,$sort_crit,$login_rapp,$id_edit);
							header("Content-type: $mime; charset=utf-8");
							$xsl = new DOMDocument();
							$xsl->load($xslpath);
							$proc = new XSLTProcessor();
							$proc->importStyleSheet($xsl);
							foreach ($typesRapportToAvis as $key => $val)
							{
								$proc->setParameter('', $key, implode_with_keys($val));
							}
							echo $proc->transformToXML($xml);
						}
					}
				}
				break;
			default:
				break;
		}
	}
}
?>