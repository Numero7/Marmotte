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
							$xml_reports = getReportsAsXML(getFilterValues(), getSortCriteria());
							$filename = "";

							if($type =="pdf")
							{
								$xml_reports->save('reports/reports.xml');
								echo "<script>window.location = 'create_reports.php'</script>";
							}

							if($type=="zip")
							{
								$xml_reports->save('reports/reports.xml');
								echo "<script>window.location = 'create_reports.php?zip_files='</script>";

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
		}
		break;
	}
}
?>