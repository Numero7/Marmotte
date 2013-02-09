<?php
require_once("utils.inc.php");
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
require_once('manage_users.inc.php');
require_once('generate_xml.inc.php');
require_once('generate_csv.inc.php');
require_once('generate_pdf.inc.php');
require_once('generate_zip.inc.php');

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.collect_vars', 'on');
ini_set('xdebug.collect_params', '4');
ini_set('xdebug.dump_globals', 'on');
ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');

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

						$login = getLogin();
						
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
								$filtervalues1['rapporteur'] = getLogin();
								$filtervalues1['rapporteur2'] = 'tous';
								$filtervalues2['rapporteur2'] = getLogin();
								$filtervalues2['rapporteur'] = 'tous';

								$filenames = array();
								$items = array();

								$filters = array();

								if(isSecretaire())
								{
									$filters[] = $filtervalues;
								}
								else
								{
									$filters[] = $filtervalues1;
									$filters[] = $filtervalues2;
								}
									
								$filenames = array();
								

								foreach($filters as $filter)
								{
									$reports = filterSortReports(getCurrentFiltersList(),  $filter, getSortingValues(),false);
									$dir = "csv/".$login;
									if(!mkdir($dir))
										throw new Exception("Failed to create directory ".$dir);
									
									foreach($reports as $report)
									{
										$file = filename_from_doc($report);
										if($type == "csv")
										{
											$file = $dir."/".$file.".csv";
										echo $file."<br/>";
											$data = compileReportAsCSV($report);
											if($handle = fopen($file, 'w'))
											{
												fwrite ($handle, $data);
												fclose($handle);
											}
											else
												throw new Exception("Cant create csv file ".$file);
										}
										if($type == "xml")
										{
											$file = $dir."/".$file.".xml";
											exportReportAsXML($report,$file);
										}
										$filenames[$file] = substr($file,strlen($dir));
									}
								}

								$filename = zip_files($filenames,$dir.'/reports.zip');

										
								if($filename == false)
									throw new Exception("Failed to zip files");

								header("Pragma: public");
								header("Expires: 0");
								header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
								header("Cache-Control: public");
								header("Content-Description: File Transfer");
								header("Content-type: application/octet-stream");
								header("Content-Disposition: attachment; filename=\"marmotte_reports_".$login.".zip\"");
								header("Content-Transfer-Encoding: binary");
								header("Content-Length: ".filesize($filename));

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
		}
	}
	break;
}
?>