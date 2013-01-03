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
		{//Processing
			case 'viewpdf':
				viewReportAsPdf($id); break;
			case 'viewhtml':
				viewReportAsHtml($id);	break;
			case 'group':
				{
					if (isset($_REQUEST["save"]) and isset($_REQUEST["avis"]) and isset($_REQUEST["rapport"]))
					{
						$idtosave = intval($_REQUEST["save"]);
						$avis = $_REQUEST["avis"];
						$rapport = $_REQUEST["rapport"];
						//rrr();
						if (!isset($_REQUEST["cancel"]))
							try
							{
								updateRapportAvis($idtosave,$avis,$rapport);
							}
							catch(Exception $exc)
							{
								echo "<p><B>Echec de la mise a jour du rapport: ".$exc->getMessage()."<br/></B></p>";
							}
					}
					$type = isset($_REQUEST["type"]) ? $_REQUEST["type"] :  "xml";
					
					if ( array_key_exists($type, $typeExports))
					{
						$id_edit = isset($_REQUEST["id_edit"]) ? $_REQUEST["id_edit"] : -1;

						$conf = $typeExports[$type];
						$mime = $conf["mime"];
						$xslpath = $conf["xsl"];

						if($type=="latex" || $type=="pdf" || $type=="zip")
						{
							$xmls = getReportsAsXMLArray(getFilterValues(), getSortCriteria());
							
							array_map('unlink', glob("reports/*.tex"));
							array_map('unlink', glob("reports/*.pdf"));
							array_map('unlink', glob("reports/*.zip"));
								
							$filename = "";
							if($type=="latex")
								$filename=xmls_to_zipped_tex($xmls);

							
							if($type == "zip" || $type =="pdf")
								$filenames = xmls_to_pdfs($xmls);

							if($type=="zip")
							{
								$filename= zip_files($filenames);

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
								echo '<script type="text/javascript">								
								window.location ="reports/"
								</script>
										';
							}
						}
						else
						{
							$filter_values = getFilterValues();
							$filter_values['id_edit'] = $id_edit; 
							$xml = getReportsAsXML($filter_values, getSortCriteria(), false);
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