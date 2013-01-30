<?php
require_once("utils.inc.php");
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
require_once('manage_users.inc.php');
require_once('generate_xml.inc.php');
require_once('generate_csv.inc.php');
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
			case 'export':
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

					if(!isset($_REQUEST["type"]))
						throw new Exception("No type specified for exportation");
					$type = $_REQUEST["type"];

						
					if ( array_key_exists($type, $typeExports))
					{
						$id_edit = isset($_REQUEST["id_edit"]) ? $_REQUEST["id_edit"] : -1;

						$conf = $typeExports[$type];
						$mime = $conf["mime"];
						$xslpath = $conf["xsl"];

						switch($type)
						{
							case "pdf":
							case "zip":
								$xml_reports = getReportsAsXML(getFilterValues(), getSortingValues());
								$filename = "";
									
								$xml_reports->formatOutput = true;
								$xml_reports->save('reports/reports.xml');

								if($type =="pdf")
									echo "<script>window.location = 'create_reports.php'</script>";

								if($type=="zip")
									echo "<script>window.location = 'create_reports.php?zip_files='</script>";
								break;

							case "csv":
							case "xml":
									
								$size = 0;
								$filename = "csv/reports.".$type;

								if($type == "csv")
								{
									$data = getReportsAsCSV(getFilterValues(), getSortingValues());
									if($handle = fopen($filename, 'w'))
									{
										fwrite ($handle, $data);
										fclose($handle);
									}
									else
									{
										throw new Exception("Cant create csv file ".$filename);
									}
									$size = strlen($data);
								}
								if($type == "xml")
								{
									$size = exportReportsAsXML(filterSortReports(getCurrentFiltersList(),  getFilterValues(), getSortingValues()),$filename);
								}



								header("Pragma: public");
								header("Expires: 0");
								header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
								header("Cache-Control: public");
								header("Content-Description: File Transfer");
								header("Content-type: application/octet-stream");
								header("Content-Disposition: attachment; filename=\"reports.".$type."\"");
								header("Content-Transfer-Encoding: binary");
								header("Content-Length: ".$size);

								ob_clean();
								flush();


								readfile($filename);
								break;
							default:
								{
									$filter_values = getFilterValues();
									$filter_values['id_edit'] = $id_edit;
									$xml = getReportsAsXML($filter_values, getSortingValues(), false);
									header("Content-type: $mime; charset=utf-8");
									$xsl = new DOMDocument("1.0","utf-8");
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
}
?>