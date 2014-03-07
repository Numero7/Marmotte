<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.collect_vars', 'on');
ini_set('xdebug.collect_params', '4');
ini_set('xdebug.dump_globals', 'on');
ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');

require_once("db.inc.php");

require_once("export.inc.php");


session_start();

$dbh = db_connect($servername,$dbname,$serverlogin,$serverpassword);

if($dbh!=0)
{
	if (authenticate())
	{
		try {
			$action = isset($_REQUEST["action"]) ? mysql_real_escape_string($_REQUEST["action"]) : "single";
			$id= isset($_REQUEST["id"]) ? mysql_real_escape_string($_REQUEST["id"]) : "-1";

			
			
			
			
			switch($action)
			{//Processing
				case 'viewpdf':
					$option = isset($_REQUEST["option"]) ? mysql_real_escape_string($_REQUEST["option"]) : "";
					viewReportAsPdf($id,$option); break;
				case 'viewhtml':
					viewReportAsHtml($id);	break;
				case 'download':
					downloadReport($id);
					break;
				case 'export':
					{
						if (isset($_REQUEST["save"]) and isset($_REQUEST["avis"]) and isset($_REQUEST["rapport"]))
						{
							$idtosave = intval(mysql_real_escape_string($_REQUEST["save"]));
							$avis = mysql_real_escape_string($_REQUEST["avis"]);
							$rapport = mysql_real_escape_string($_REQUEST["rapport"]);
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
						$type = mysql_real_escape_string($_REQUEST["type"]);


						$id_edit = isset($_REQUEST["id_edit"]) ? mysql_real_escape_string($_REQUEST["id_edit"]) : -1;

						$login = getLogin();


						switch($type)
						{
							case "pdf":

								if(!isSecretaire())
									throw new Exception("Zip and pdf exports are only for secretaray and president");

								$xml_reports = getReportsAsXML(getFilterValues(), getSortingValues());
								$filename = "";
								$xml_reports->formatOutput = true;

								$files = glob('reports/*'); // get all file names
								foreach($files as $file){ // iterate files
									if(is_file($file))
										unlink($file); // delete file
								}

								create_dir_if_needed("reports");
								$result = $xml_reports->save('reports/reports.xml');

								if($result === false)
									throw new Exception("Failed to save file reports/reports.xml");

								if($type =="pdf")
									echo "<script>window.location = 'create_reports.php'</script>";

								if($type=="zip")
									echo "<script>window.location = 'create_reports.php?zip_files=oui'</script>";
								break;
							case "csvbureau":
								export_current_selection_as_single_csv("attribution_rapporteurs");
								break;
							case "releveconclusions":
								export_current_selection_as_single_csv("releveconclusions");
								break;
							case "csvsingle":
								export_current_selection_as_single_csv();
								break;
							case "text":
								export_current_selection_as_single_txt();
								break;
							case "csv":
							case "xml":
								export_current_selection($type);
								break;
							case "jad":
								generate_jad_reports();
								break;
							case "jadhtml":
								display_jad_reports();
								break;
							case "exempleimportcsv":
								$fields = array();
								if(isset($_POST['fields']))
								{
									if(!isset($_POST['types']))
									{
										throw new Exception("Sélectionnez au moins un type de rapport");
									}
									else	
									{
										generate_exemple_csv($_POST['types'], $_POST['fields']);
									}
								}
								else
									throw new Exception("No fields provided,, cannot genrate exemple csv");
								break;
							default:
								{
									if(!isset($typeExports[$type]))
										throw new Exception("Unknown type ".$type);

									$conf = $typeExports[$type];
									$mime = $conf["mime"];

									$filter_values = getFilterValues();
									$filter_values['id_edit'] = $id_edit;
									$xml = getReportsAsXML($filter_values, getSortingValues(), false);
									header("Content-type: $mime; charset=utf-8");
									$xslpath = $conf["xsl"];
									$xsl = new DOMDocument("1.0","utf-8");
									$xsl->load($xslpath);
									$proc = new XSLTProcessor();
									$proc->importStyleSheet($xsl);
									foreach ($typesRapportToAvis as $key => $val)
									{
										$proc->setParameter('', $key, implode_with_keys($val));
									}
									echo $proc->transformToXML($xml);
									break;
								}
						}

					}
					break;
				default:
					throw new Exception("Unknown action ".$action);
			}
		}
		catch(Exception $e)
		{
			include("header.inc.php");
			
			echo $e->getMessage();
		}
	}
}
/* activit */
?>