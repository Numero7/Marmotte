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
require_once('authenticate_tools.inc.php');

session_start();

global $dbh;

db_connect($servername,marmottedbname,$serverlogin,$serverpassword);

if (authenticate())
	{
		require_once("export.inc.php");
		
		try {
			$action = isset($_REQUEST["action"]) ? real_escape_string($_REQUEST["action"]) : "single";
			$id= isset($_REQUEST["id"]) ? real_escape_string($_REQUEST["id"]) : "-1";
			
			switch($action)
			{//Processing
				case 'viewpdf':
					$option = isset($_REQUEST["option"]) ? real_escape_string($_REQUEST["option"]) : "";
					viewReportAsPdf($id,$option); break;
				case 'viewhtml':
					viewReportAsHtml($id);	break;
				case 'download':
					downloadReport($id);
					break;
				case 'get_file':
					if(isset($_REQUEST["filename"]) && isset($_REQUEST["path"]))
					{
						$localpath = urldecode(($_REQUEST["path"]));
						$remotepath = urldecode(($_REQUEST["filename"]));
						send_file($localpath,$remotepath, isset($_REQUEST["evaluation"]));
					}
					break;
				break;
					case 'export':
					{
						if (isset($_REQUEST["save"]) and isset($_REQUEST["avis"]) and isset($_REQUEST["rapport"]))
						{
							$idtosave = intval(real_escape_string($_REQUEST["save"]));
							$avis = real_escape_string($_REQUEST["avis"]);
							$rapport = real_escape_string($_REQUEST["rapport"]);
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
						$type = real_escape_string($_REQUEST["type"]);
						$id_edit = isset($_REQUEST["id_edit"]) ? real_escape_string($_REQUEST["id_edit"]) : -1;

						switch($type)
						{
							case "pdf":
							  //							  rr();
								if(!isSecretaire())
									throw new Exception("Zip and pdf exports are only for secretaray and president");

								$xml_reports = getReportsAsXML(getFilterValues(), getSortingValues());
								$filename = "";
								$xml_reports->formatOutput = true;

								$dir = dossier_temp();
								$files = glob($dir."/*");
								foreach($files as $file)
									if(is_file($file))
										unlink($file);

								$result = $xml_reports->save($dir."/reports.xml");

								if($result === false)
									throw new Exception("Failed to save file reports/reports.xml");

								if($type =="pdf")
									echo "<script>window.location = 'create_reports.php'</script>";
								if($type=="zip")
									echo "<script>window.location = 'create_reports.php?zip_files=oui'</script>";
								break;
						case "my":
						  download_my_reports();
								break;
							case "csvbureau":
								export_current_selection_as_single_xls("attribution_rapporteurs");
								break;
							case "releveconclusions":
								export_current_selection_as_single_xls("releveconclusions");
								break;
							case "csvsingle":
								export_current_selection_as_single_csv();
								break;
						case "sousjurys":
						  export_sous_jurys();
						  break;
							case "xlssingle":
								export_current_selection_as_single_xls();
								break;
							case "text":
							  export_current_selection("txt");
							  //								export_current_selection_as_single_txt();
								break;
							case "csv":
							case "xml":
								export_current_selection($type);
								break;
							case "jad":
								$preambules = array();
								foreach($_REQUEST as $key => $value)
								{
									$res = strpos($key,"preambule_jad_");
									if( $res !== false )
									{
										$code = substr($key,strlen("preambule_jad_"));
										$text = nl2br(trim($value));
										$preambules[$code] = $text; 
										set_config($key, $text);
									}									
								}
								generate_jad_reports($preambules);
								break;
							case "jadhtml":
								display_jad_reports();
								break;
							case "exempleimportcsv":
								$fields = array();
								if(isset($_POST['fields']))
								{
									if(!isset($_POST['types']))
										throw new Exception("Sélectionnez au moins un type de rapport");
									else	
										generate_exemple_csv($_POST['types'], $_POST['fields']);
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
									  //$proc->setParameter('', $key, implode_with_keys($val));
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

?>