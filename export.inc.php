<?php
require_once("utils.inc.php");
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
require_once('manage_users.inc.php');
require_once('manage_concours.inc.php');
require_once('manage_rapports.inc.php');
require_once('manage_sessions.inc.php');
require_once('generate_xml.inc.php');
require_once('generate_csv.inc.php');
require_once('generate_pdf.inc.php');
require_once('generate_zip.inc.php');

function send_file($local_filename, $remote_filename, $dsi = false)
{


	global $dossier_stockage;
	global $dossier_stockage_dsi;

	$toto = realpath($dossier_stockage);
	$sub3  = substr(realpath($local_filename),0, strlen($toto) );
	$ok3 = ($sub3 == $toto);


	$dossier_temp = dossier_temp();	
	$dossier_stockage = ($dsi ? realpath($dossier_stockage_dsi) : realpath($dossier_stockage) );
	$dossier_temp = realpath($dossier_temp);
	
	$sub  = substr(realpath($local_filename),0, strlen($dossier_stockage) );
	$sub2 = substr(realpath($local_filename),0, strlen($dossier_temp) );

	if( ($sub != $dossier_stockage) &&  ($sub2 != $dossier_temp) && !$ok3)
	  throw new Exception("Forbidden access to file".realpath($local_filename));//."<br/>".$sub."<br/>".$dossier_stockage."<br/>".$sub2."<br/>".$dossier_temp);		
	
	if(!is_file($local_filename))
		throw new Exception("Cannot find file .$local_filename");
	
	$size = filesize($local_filename);
	if($size === false)
		throw new Exception("Cannot get size of file .$local_filename");
	
	

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	if( strpos(strtolower($local_filename),"pdf") !== FALSE)
	  {
	    header('Content-type: application/pdf');
	    header("Content-Disposition: inline; filename=\"$remote_filename\"");
	  }
	else
	  {
	    header("Content-Description: File Transfer");
	    header("Content-type: application/octet-stream");
	    header("Content-Disposition: attachment; filename=\"$remote_filename\"");
	  }
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
		$result = mkdir($dir,0770, true);
}

function export_reports_as_txt($reports, $dir, $prefix = "rapports")
{
	if(count($reports) == 0)
		throw new Exception("No reports to export");
	
	create_dir_if_needed($dir);

	global $mandatory_export_fields;
	$filename = $dir."/".$prefix.".txt";
	$data = compileObjectsAsTXT($reports);
	if($handle = fopen($filename, 'w'))
	{
		fwrite ($handle, $data);
		fclose($handle);
	}
	else
		throw new Exception("Cant create txt file ".$filename);
	return $filename;
}

function export_reports_as_csv($reports, $dir, $type = "")
{
	create_dir_if_needed($dir);
	if(count($reports) == 0)
		throw new Exception("No reports to export");

	$file = "reports.csv";

	$activefields = array();
	if($type == "attribution_rapporteurs")
	{
		$activefields =
		array('type','nom','prenom','rapporteur','rapporteur2','rapporteur3',
				"grade_rapport",
				"unite",
				"theme1",
				"theme2",
				"theme3",
				'id'
		);
		if(is_current_session_concours())
		{
			$activefields[] = "concours";
		}
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
			array_merge(
					$mandatory_export_fields,
					 get_readable_fields($reports[0])
					)
			       );
		$useful_fields = array();
		foreach($activefields as $field)
		  {
		    $ok = false;
		    foreach($reports as $report)
		      {
			if(isset($report->$field) && $report->$field != "")
			  {
			    $ok = true;
			    break;
			  }
		      }
		    if($ok)
			$useful_fields[] = $field;
		  }
		$activefields = $useful_fields;
	}

	$file = $dir."/".$file;

	$texte= array();
	$texte[] = array();
	
	$text = array();
	$text[] = strval(count($reports)) . " rapports";
	$texte[] = $text;

	$texte[] = array();
	
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
	else if($export_format == "xml")
	{
		$file = $dir."/".$file.".txt";
		exportReportAsXML($report,$activefields, $file);
	}
	else if($export_format == "txt")
	{
		$file = $dir."/".$file.".doc";
		$data = compileObjectsAsTXT(array($report));
		if($handle = fopen($file, 'w'))
		{
			fwrite ($handle, $data);
			fclose($handle);
		}
		else
			throw new Exception("Imposible de creer le fichier doc ".$file);
	}

	return $file;

}

function download_my_reports()
{
  $my = filterSortReports(getCurrentFiltersList(), 
			  array(
				"rapporteur" => getLogin(), 
				"id_session" => current_session_id()),
			  getSortingValues()
			  );
  if(count($my) == 0)
    return;

  $filenames = array();
  foreach($my as $report)
    {
      $files = aggregate_files($report);
      //      $dir = trim($report->concours." ".$report->nom." ".$report->prenom." ".$report->unite,"/ ");
      $dir = trim(str_replace("/","",/*$report->concours." ".*/$report->nom." ".$report->prenom." ".$report->unite));
      foreach($files as $path => $file)
	{
	  $filenames[$path] = $dir."/".$file;
	  //	  echo $dir."/".$file."<br/>";
	}
    }
		$remote_filename = dossier_temp()."/dossiers_".getLogin().".zip";

		//		gg();
		$filename = zip_files($filenames,$remote_filename);

		if($filename == false)
			throw new Exception("Failed to zip files");
		send_file($remote_filename, "dossiers ".getLogin().".zip");
}

function aggregate_files($report)
{
  $filenames = array();
  $dir = dossier_temp();
	//		$file = export_reports_as_txt(array($report), $dir, $prefix);
		$file = export_report($report,"txt",$dir);
		$pref = substr($file,strrpos($file,'/')+1);
		$filenames[$file] = $pref;
		
		if( isset($report->type) && $report->type == REPORT_CANDIDATURE)
		{
			$extra_files = 
			  array_merge(
				      find_files($report, $report->id_session,true,"celcc"),
				      find_files($report, $report->id_session,true,"marmotte"),
				      find_files($report, $report->id_session,true,"marmotte","avis")
				      );
			foreach($extra_files as $file)
			  {
			    $short = substr($file,strrpos($file,'/')+1);
			    $filenames[$file] = $short;
			  }
		}
		return $filenames;

}

function downloadReport($id_rapport)
{
  //  echo $id_rapport;
  	try
	{
		$report = getReport($id_rapport);
		$filenames = aggregate_files($report);			

		$prefix = filename_from_doc($report);
		echo $prefix."<br/>";
		$remote_filename = dossier_temp()."/".$prefix.'.zip';
		echo $remote_filename."<br/>";

		$filename = zip_files($filenames,$remote_filename);

		if($filename == false)
			throw new Exception("Failed to zip files");
		send_file($remote_filename, $prefix.".zip");
	}
	catch(Exception $exc)
	{
		throw new Exception("Echec du téléchargement du rapport:\n ".$exc->getMessage());
	}
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
		$reports = filterSortReports(getCurrentFiltersList(),  $filter, $sorting);
	}
	else
	{
		$reports = filterSortReports(getCurrentFiltersList(),  $filter, getSortingValues());
	}
	
	if(count($reports) > 0)
	{
		$dir = dossier_temp();
		$file = export_reports_as_csv($reports, $dir, $type);
		$filenames[$file] = substr($file,strlen($dir."/"));
		$remote_filename = 'rapports_marmotte_'.$login.'.csv';
		send_file($file, $remote_filename);
		/*
		$remote_filename = 'marmotte_reports_'.$login.'.zip';
		$filename = zip_files($filenames,$dir.'/'.$remote_filename);
		if($filename == false)
			throw new Exception("Failed to zip files");
		send_file($filename, $remote_filename);
		*/
	}
}
function export_current_selection_as_single_xls($type = "")
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
		$reports = filterSortReports(getCurrentFiltersList(),  $filter, $sorting);
	}
	else
	{
		$reports = filterSortReports(getCurrentFiltersList(),  $filter, getSortingValues());
	}
	
	if(count($reports) > 0)
	{
		$dir = dossier_temp();
		$file = export_reports_as_csv($reports, $dir, $type);
		require_once("PHPExcel/Classes/PHPExcel.php");
		// Create new PHPExcel object

		$objReader = PHPExcel_IOFactory::createReader('CSV')->setDelimiter(';')
		  ->setEnclosure('"')
		  ->setSheetIndex(0);
		$objPHPExcelFromCSV = $objReader->load($file);
		$objWriter2007 = PHPExcel_IOFactory::createWriter($objPHPExcelFromCSV, 'Excel5');
		$xlsfile = str_replace('.csv', '.xls', $file);
		$objWriter2007->save($xlsfile);

		$remote_filename = 'rapports_marmotte'.$login.'.xls';
		send_file($xlsfile, $remote_filename);
		/*
		$filenames[$file] = substr($xlsfile,strlen($dir."/"));
		$remote_filename = 'rapports_marmotte'.$login.'.zip';
		
		$filename = zip_files($filenames,$dir.'/'.$remote_filename);
		if($filename == false)
			throw new Exception("Failed to zip files");
		send_file($filename, $remote_filename);
		*/
	}
}

function export_sous_jurys()
{
		require_once("PHPExcel/Classes/PHPExcel.php");
  global $tous_sous_jury;
  $objPHPExcel = new PHPExcel();
  $objPHPExcel->setActiveSheetIndex(0);
  $sheet =  $objPHPExcel->getActiveSheet();
$sheet->SetCellValue('A1', 'Sections de jury');
$i=3;

  foreach($tous_sous_jury as $code => $sj)
    {
      if(count($sj) > 0)
	$sheet->SetCellValue("A".$i++, 'Sections de jury du concours '.$code);
      else
	$sheet->SetCellValue("A".$i++, 'Pas de section de jury pour le concours '.$code);

      foreach($sj as $sjj => $liste)
	{
      $sheet->SetCellValue("B".$i++, 'Section de jury: '.$sjj);
      $sheet->SetCellValue("B".$i++, 'President: '.$liste["president"]);
      foreach($liste["membres"] as $login)
	       $sheet->SetCellValue("C".$i++, $login);
      $sheet->SetCellValue("A".$i++, "");
	}
      $sheet->SetCellValue("A".$i++, "");
    }

  $objWriter2007 = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
  $xlsfile = "sousjurys.xls";
  $objWriter2007->save(dossier_temp()."/".$xlsfile);
  send_file(dossier_temp()."/".$xlsfile, $xlsfile);
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
		$dir = dossier_temp();
		$file = export_reports_as_txt($reports, $dir);
		send_file($file, "rapports.doc");
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
		$dir = dossier_temp();
		foreach($reports as $report)
		{
			$file = export_report($report, $export_format, $dir);
			$filenames[$file] = substr($file,strlen($dir."/"));
		}

		$remote_filename = 'rapports_marmotte_'.$login.'.zip';
		$filename = zip_files($filenames,$dir.'/'.$remote_filename);

		if($filename == false)
			throw new Exception("Failed to zip files");

		send_file($filename, $remote_filename);
	}
}

function generate_jad_reports($preambules = array())
{
	global $concours_ouverts;
	$docs = array();

	foreach($concours_ouverts as $concours => $niveau)
	{
	  //	  echo $concours."<br/>";
	  if(!isset($preambules[$concours]) || trim($preambules[$concours]) == "")
	    continue;
		$preambule = isset($preambules[$concours]) ? $preambules[$concours] : "";
		$docs[$concours] = generate_jad_report($concours, $preambule);
	}


	$login = getLogin();

	$dir = dossier_temp();

	$filenames = array();
	foreach($docs as $code => $doc)
	{
	  //		echo $code;

		$doc->formatOutput = true;

		$html = XMLToHTML($doc,'xslt/jad.xsl');
		$pdf = HTMLToPDF($html);
		$filename = "rapport_jad_".preg_replace('/[^A-Za-z0-9\-]/', '', $code).".pdf";
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

	$dir = dossier_temp();
	$filenames = array();
	foreach($docs as $code => $doc)
	{
		$doc->formatOutput = true;
		echo XMLToHTML($doc,'xslt/jad.xsl');
	}
}

function generate_jad_report($code,$preambule="")
{

  $key = "preambule_jad_".trim($code,"\\/ ");
  set_config($key,$preambule);


	$doc = new DOMDocument("1.0","UTF-8");
	$root = $doc->createElement("jad");
	$doc->appendChild($root);

	$filters = array();

	$filters["concours"] = $code;
	$filters["type"] = REPORT_CANDIDATURE;
	$filters["section"] = currentSection();
	$filters["id_session"] = current_session_id();
	$filters["avis"] = avis_admis_a_concourir;
	
	$candidats = filterSortReports(getCurrentFiltersList(), $filters, array("nom" => "1+"));

	$filters["avis"] = avis_oral;	
	$admissibles = filterSortReports(getCurrentFiltersList(), $filters, array("nom" => "1+"));

	global $concours_ouverts;
	$nom_concours = $concours_ouverts[$code];

	$grade_concours = substr($nom_concours, 0,3);
	appendLeaf("grade_concours", $grade_concours, $doc, $root);

	$session = current_session_id();
	$annee_concours = substr($session,strlen($session) -4,4);
	appendLeaf("annee_concours", $annee_concours, $doc, $root);


	$num_concours = $code;
	/*	if(strlen($code) >= 4)
		$num_concours = substr($code,0,2) ."/".substr($code,2,2);
	else
		$num_concours = $strlen($code); // peut etre renseigné à la main par le président
	*/
	appendLeaf("code_concours", $num_concours, $doc, $root);
	
	global $postes_ouverts;
	appendLeaf("postes_ouverts", $postes_ouverts[$code], $doc, $root);

	appendLeaf("avis_jad", $preambule, $doc, $root);
		
	date_default_timezone_set('Europe/Paris');
	setlocale (LC_TIME, 'fr_FR.utf8','fra');
	//date("j/F/Y")
	appendLeaf("date_jad", utf8_encode(strftime("%#d %B %Y")), $doc, $root);
		
	$leaf = $doc->createElement("candidats");
	$root->appendChild($leaf);

	$admis = getAdmisAPoursuivre($num_concours);

	$examines = count($admis);

	foreach($candidats as $key => $candidat)
	{
	if(isset($candidat->concoursid) && in_array($candidat->concoursid,$admis))
	  {
	    $subleaf = $doc->createElement("candidat");
	    appendLeaf("nom", strtoupper($candidat->nom), $doc, $subleaf);
	    appendLeaf("prenom", ucfirst(strtolower($candidat->prenom)), $doc, $subleaf);
		$leaf->appendChild($subleaf);
	  }
	}

	
	$auditonnes = 0;
	$leaf = $doc->createElement("admissibles");
	$root->appendChild($leaf);
	foreach($admissibles as $key => $candidat)
	{
	if(isset($candidat->concoursid) && in_array($candidat->concoursid,$admis))
	    {
	      $auditonnes++;
		$subleaf = $doc->createElement("candidat");
		appendLeaf("nom", strtoupper($candidat->nom), $doc, $subleaf);
		appendLeaf("prenom", ucfirst(strtolower($candidat->prenom)), $doc, $subleaf);
		$leaf->appendChild($subleaf);
	    }
	}

	appendLeaf("examines", "<b>".strval($examines)."</b>", $doc, $root);
	$sn = "<b>".strval($auditonnes)."</b>";
	appendLeaf("auditionnes", $sn, $doc, $root);


	date_default_timezone_set('Europe/Paris');
	setlocale (LC_TIME, 'fr_FR.utf8','fra');
	//date("j/F/Y")
	appendLeaf("date", utf8_encode(strftime("%#d %B %Y")), $doc, $root);

	appendLeaf("signataire", get_config("president"), $doc, $root);
	appendLeaf("signataire_titre", get_config("president_titre"), $doc, $root);

	global $dossier_stockage;
	global $rootdir;
	if(isSecretaire() && file_exists($dossier_stockage.signature_file))
	{
		appendLeaf("signature_source", $dossier_stockage.signature_file, $doc, $root);
	}
	else
	{
		appendLeaf("signature_source", $rootdir.signature_blanche, $doc, $root);
	}

	return $doc;
}

function generate_exemple_csv($types,$fields)
{
	try
	{
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
		
		$dir = dossier_temp();
		$filename = $dir."trame_rapport_vierges.csv";
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


?>