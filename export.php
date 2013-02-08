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

								if(!isSecretaire())
									throw new Exception("Zip and pdf exports are only for secretaray and president");

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


								$filtervalues = getFilterValues();
								$filtervalues1 = $filtervalues;
								$filtervalues2 = $filtervalues;
								$filtervalues1['login_rapp'] = getLogin();
								$filtervalues1['login_rapp2'] = 'tous';
								$filtervalues2['login_rapp2'] = getLogin();
								$filtervalues2['login_rapp'] = 'tous';
								$filename = "csv/reports.".$type;
								$filename1 = "csv/reports_rapporteur1.".$type;
								$filename2 = "csv/reports_rapporteur2.".$type;

								
								$filenames = array();
								if(isSecretaire())
								{
									$items[$filename] = $filtervalues;
								}
								else
								{
									$items[$filename1] = $filtervalues1;
									$items[$filename2] = $filtervalues2;
								}
									
								$filenames = array();
								
								
								foreach($items as $file => $filter)
								{
									$reports = filterSortReports(getCurrentFiltersList(),  $filter, getSortingValues(),false);
									$filenames[$file] = substr($file,4);
									if($type == "csv")
									{
										$data = compileReportsAsCSV($reports);
										if($handle = fopen($file, 'w'))
										{
											fwrite ($handle, $data);
											fclose($handle);
										}
										else
										{
											throw new Exception("Cant create csv file ".$file);
										}
										$size = strlen($data);
									}
									if($type == "xml")
									{
										$size = exportReportsAsXML($reports,$file);
									}
								}

								
								$filename = zip_files($filenames,'zips/reports.zip');


								if($filename == false)
									throw new Exception("Failed to zip files");
								
								header("Pragma: public");
								header("Expires: 0");
								header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
								header("Cache-Control: public");
								header("Content-Description: File Transfer");
								header("Content-type: application/octet-stream");
								header("Content-Disposition: attachment; filename=\"reports.zip\"");
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