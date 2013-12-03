<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.collect_vars', 'on');
ini_set('xdebug.collect_params', '4');
ini_set('xdebug.dump_globals', 'on');
ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');

require_once("utils.inc.php");
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
require_once('manage_users.inc.php');
require_once('manage_rapports.inc.php');
require_once('generate_xml.inc.php');
require_once('generate_csv.inc.php');
require_once('generate_pdf.inc.php');
require_once('generate_zip.inc.php');
require_once("db.inc.php");


function send_file($local_filename, $remote_filename)
{


	if(!is_file($local_filename))
		throw new Exception("Cannot find file .$local_filename");

	$size = filesize($local_filename);
	if($size === false)
		throw new Exception("Cannot get size of file .$local_filename");

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"$remote_filename\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: $size");

	ob_clean();
	flush();

	if(readfile($local_filename) === false)
		throw new Exception("Failed to read file .$local_filename");


}


function create_dir_if_needed($dir)
{
	if(!is_dir($dir))
		$result = mkdir($dir,0700, true);
}

function export_reports_as_txt($reports, $dir)
{
	if(count($reports) == 0)
	{
		throw new Exception("No reports to export");
	}
	
	create_dir_if_needed($dir);

	global $mandatory_export_fields;
	$file = "reports";
	$file = $dir."/".$file.".txt";
	$data = compileObjectsAsTXT($reports);
	if($handle = fopen($file, 'w'))
	{
		fwrite ($handle, $data);
		fclose($handle);
	}
	else
		throw new Exception("Cant create txt file ".$file);
	return $file;
}

function export_reports_as_csv($reports, $dir, $type = "")
{
	create_dir_if_needed($dir);
	
	if(count($reports) == 0)
	{
		throw new Exception("No reports to export");
	}


	$file = "reports.csv";

	$activefields = array();
	if($type == "attribution_rapporteurs")
	{
		$activefields =
		array('type','nom','prenom','rapporteur','rapporteur2',
				"grade_rapport",
				"unite",
				"theme1",
				"theme2",
				"theme3",
				'id'
		);
	}
	else if($type == "releveconclusions")
	{
		$activefields =
		array('type','nom','prenom',
				"grade_rapport",
				"unite",
				"avis"
		);
	}
	else
	{
	global $mandatory_export_fields;
		$activefields =
	array_unique(
			array_merge($mandatory_export_fields, get_editable_fields($reports[0]))
	);
	}

	$file = $dir."/".$file;

	$texte= array();
	$texte[] = array();
	
	$text = array();
	$text[] = strval(count($reports)) . " rapports";
	$texte[] = $text;

	$texte[] = array();
	
	if($type == "attribution_rapporteurs")
	{
		$texte[] = array("Pour attribuer les rapporteurs");
		$texte[] = array("copier-coller les login correspondants");
		$texte[] = array("(liste ci-dessous) dans les colonnes");
		$texte[]= array("'rapporteur' et 'rapporteur2'");
		$texte[]=array("");
		$users = listUsers();		
		foreach($users as $login => $value)
			$texte[] = array($login);
		$texte[] = array();
		$texte[] = array();
	}
	
	$data = compileObjectsAsCSV($activefields, $reports, $texte);
	if($handle = fopen($file, 'w'))
	{
		fwrite ($handle, $data);
		fclose($handle);
	}
	else
		throw new Exception("Cant create csv file ".$file);
	return $file;

}

function export_report($report, $export_format, $dir)
{
	create_dir_if_needed($dir);
	
	global $mandatory_export_fields;

	$file = filename_from_doc($report);

	$activefields =
	array_unique(
			array_merge($mandatory_export_fields, get_editable_fields($report))
	);

	if($export_format == "csv")
	{
		$file = $dir."/".$file.".csv";
		$data = compileObjectsAsCSV($activefields, array($report));
		if($handle = fopen($file, 'w'))
		{
			fwrite ($handle, $data);
			fclose($handle);
		}
		else
			throw new Exception("Cant create csv file ".$file);
	}
	if($export_format == "xml")
	{
		$file = $dir."/".$file.".txt";
		exportReportAsXML($report,$activefields, $file);
	}

	return $file;

}

function export_current_selection_as_single_csv($type = "")
{


	$size = 0;

	$login = getLogin();

	$filter = getFilterValues();

	$filenames = array();
	$items = array();

	$filenames = array();

	if($type == "releveconclusions")
	{
		$sorting = 	array(
		'type' => '1+',
		'grade_rapport' => '2+',
		'avis' => '3+');
		$reports = filterSortReports(getCurrentFiltersList(),  $filter, $sorting,false);
	}
	else
	{
		$reports = filterSortReports(getCurrentFiltersList(),  $filter, getSortingValues(),false);
	}

	if(count($reports) > 0)
	{
		$dir = "csv/".$login;
		if(!is_dir($dir) && !mkdir($dir))
			throw new Exception("Failed to create directory ".$dir);

		$file = export_reports_as_csv($reports, $dir, $type);
		$filenames[$file] = substr($file,strlen($dir."/"));

		$remote_filename = 'marmotte_reports_'.$login.'.zip';
		$filename = zip_files($filenames,$dir.'/'.$remote_filename);


		if($filename == false)
			throw new Exception("Failed to zip files");

		send_file($filename, $remote_filename);

	}


}

function export_current_selection_as_single_txt()
{


	$size = 0;

	$login = getLogin();

	$filter = getFilterValues();

	$filenames = array();
	$items = array();

	$filenames = array();

	$reports = filterSortReports(getCurrentFiltersList(),  $filter, getSortingValues(),false);

	if(count($reports) > 0)
	{
		$dir = "csv/".$login;
		if(!is_dir($dir) && !mkdir($dir))
			throw new Exception("Failed to create directory ".$dir);

		$file = export_reports_as_txt($reports, $dir);

		send_file($file, "rapports_marmotte.txt");

	}


}

function export_current_selection($export_format)
{

	$size = 0;

	$login = getLogin();

	$filter = getFilterValues();

	$filenames = array();
	$items = array();


	$filenames = array();

	$reports = filterSortReports(getCurrentFiltersList(),  $filter, getSortingValues(),false);

	if(count($reports) > 0)
	{
		$dir = "csv/".$login;
		if(!is_dir($dir) && !mkdir($dir))
			throw new Exception("Failed to create directory ".$dir);
			
		foreach($reports as $report)
		{
			$file = export_report($report, $export_format, $dir);
			$filenames[$file] = substr($file,strlen($dir."/"));
		}

		$remote_filename = 'marmotte_reports_'.$login.'.zip';
		$filename = zip_files($filenames,$dir.'/'.$remote_filename);


		if($filename == false)
			throw new Exception("Failed to zip files");

		send_file($filename, $remote_filename);
	}

}

function generate_jad_reports()
{
	global $concours_ouverts;
	$docs = array();
	foreach($concours_ouverts as $concours => $code)
	{
			$docs[$code] = generate_jad_report($concours);
	}

	$login = getLogin();

	$dir = "csv/".$login;
	if(!is_dir($dir) && !mkdir($dir))
		throw new Exception("Failed to create directory ".$dir);

	$filenames = array();
	foreach($docs as $code => $doc)
	{
		$doc->formatOutput = true;

		$html = XMLToHTML($doc,'xslt/jad.xsl');
		$pdf = HTMLToPDF($html);
		$filename = "rapport_jad_$code.pdf";
		$pdf->Output($dir."/".$filename,'F');
		$filenames[$dir."/".$filename] = $filename;
	}

	$remote_filename = 'jad_reports.zip';
	$filename = zip_files($filenames,$dir."/".$remote_filename);

	if($filename == false)
		throw new Exception("Failed to zip files");

	send_file($filename, $remote_filename);

}

function display_jad_reports()
{
	global $concours_ouverts;
	$docs = array();
	foreach($concours_ouverts as $concours => $code)
		$docs[$code] = generate_jad_report($concours);

	$login = getLogin();

	$dir = "csv/".$login;
	if(!is_dir($dir) && !mkdir($dir))
		throw new Exception("Failed to create directory ".$dir);

	$filenames = array();
	foreach($docs as $code => $doc)
	{
		$doc->formatOutput = true;

		echo XMLToHTML($doc,'xslt/jad.xsl');
	}
}

function generate_jad_report($code)
{
	$doc = new DOMDocument("1.0","UTF-8");
	$root = $doc->createElement("jad");
	$doc->appendChild($root);


	$filters = array();

	$filters["concours"] = $code;
	$filters["type"] = "Candidature";
	$candidats = filterSortReports(getCurrentFiltersList(), $filters, array("nom" => "1+"));

	$filters["avis"] = "oral";
	$admissibles = filterSortReports(getCurrentFiltersList(), $filters, array("nom" => "1+"));

	global $concours_ouverts;
	$nom_concours = $concours_ouverts[$code];

	$grade_concours = substr($nom_concours, 0,3);
	appendLeaf("grade_concours", $grade_concours, $doc, $root);

	$n = strlen($nom_concours);
	$num_concours = substr($nom_concours, $n - 4, 2) ."/" .substr($nom_concours, $n - 2, 2);
	appendLeaf("code_concours", $num_concours, $doc, $root);

	global $postes_ouverts;
	appendLeaf("postes_ouverts", $postes_ouverts[$code], $doc, $root);

	$avis = get_config("avis_jad");
	if(isset($avis[$code]))
		appendLeaf("avis_jad", $avis[$code], $doc, $root);
	else
		appendLeaf("avis_jad", "", $doc, $root);
	appendLeaf("date_jad", get_config("date_jad"), $doc, $root);
	
	appendLeaf("examines", strval(count($candidats)), $doc, $root);
	$leaf = $doc->createElement("candidats");
	$root->appendChild($leaf);
	foreach($candidats as $key => $candidat)
	{
		$subleaf = $doc->createElement("candidat");
		appendLeaf("nom", $candidat->nom, $doc, $subleaf);
		appendLeaf("prenom", $candidat->prenom, $doc, $subleaf);
		$leaf->appendChild($subleaf);
	}

	appendLeaf("auditionnes", strval(count($admissibles)), $doc, $root);
	$leaf = $doc->createElement("admissibles");
	$root->appendChild($leaf);
	foreach($admissibles as $key => $candidat)
	{
		$subleaf = $doc->createElement("candidat");
		appendLeaf("nom", $candidat->nom, $doc, $subleaf);
		appendLeaf("prenom", $candidat->prenom, $doc, $subleaf);
		$leaf->appendChild($subleaf);
	}

	date_default_timezone_set('Europe/Paris');
	setlocale (LC_TIME, 'fr_FR.utf8','fra');
	//date("j/F/Y")
	appendLeaf("date", utf8_encode(strftime("%#d %B %Y")), $doc, $root);

	appendLeaf("signataire", get_config("president"), $doc, $root);
	appendLeaf("signataire_titre", get_config("president_titre"), $doc, $root);

	if(isSecretaire())
		appendLeaf("signature_source", "img/signature.jpg", $doc, $root);
	else
		appendLeaf("signature_source", "img/signatureX.jpg", $doc, $root);

	return $doc;
}

function generate_exemple_csv($types,$fields)
{
	try
	{
		/*
		if( !in_array('nomprenom', $fields) && ( !in_array('nom', $fields) || !in_array('prenom', $fields) ))
			throw new Exception("Check either the 'nomprenom' checkbox or both the 'nom' and the 'prenom' checkbox");
*/
		/*
		$sql = "SELECT * FROM ".reports_db." LIMIT 0,5";
		$result = sql_request($sql);

		$rows = array();
		while ($row = mysql_fetch_object($result))
			$rows[] = $row;

*/

		$rows = array();

		foreach($types as $type)
		{
		$row = (object) array();
		$row->type = $type;
		foreach($fields as $field)
			$row->$field = "copiez/collez vos données";
		$rows[] = $row;
		
		$row1 = (object) array();
		$row1->type = $type;
		foreach($fields as $field)
			$row1->$field = "supprimez les lignes inutiles";
		$rows[] = $row1;
		
		$row2 = (object) array();
		$row2->type = $type;
		foreach($fields as $field)
			$row2->$field = "ajoutez des lignes si nécessaire";
		$rows[] = $row2;
		
		$row3 = (object) array();
		$row3->type = $type;
		foreach($fields as $field)
			$row3->$field = "";
		$rows[] = $row3;
		$rows[] = $row3;

				$row = (object) array();
		$rows[] = $row;
		}
		
		$fields =  array_merge(array("type"), $fields);
		
		$csv_reports = compileReportsAsCSV($rows,$fields);
		$filename = "csv/trame_rapport_vierges.csv";
		if($handle = fopen($filename, 'w'))
		{
			fwrite ($handle, $csv_reports);
			fclose($handle);
			send_file($filename, "trame_rapport_vierges.csv");
		}
		else
			throw new Exception("Cannot generate file ".$filename);
	}
	catch(Exception $e)
	{
		include("header.inc.php");
		echo $e->getMessage();
	}
}

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

						echo $type;
						rr();

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