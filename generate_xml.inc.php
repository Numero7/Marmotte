<?php 

require_once('manage_sessions.inc.php');
require_once('manage_unites.inc.php');
require_once('manage_users.inc.php');
require_once('utils.inc.php');
require_once('xml_tools.inc.php');

/*
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.collect_vars', 'on');
ini_set('xdebug.collect_params', '4');
ini_set('xdebug.dump_globals', 'on');
ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');
*/
function implode_with_keys($assoc,$inglue=':',$outglue=','){
	$res=array();
	foreach($assoc as $tk=>$tv){
		$res[] = $tk.$inglue.$tv;
	}
	return implode($outglue,$res);
}


function exportReportAsXML($report,$activefields,$filename)
{
	global $typesRapportToAvis;
	global $fieldsTypes;

	$result = "";
	$doc = new DOMDocument("1.0","UTF-8");
	$node = $doc->createElement("evaluation");

	$type = $report->type;
	$avis = $typesRapportToAvis[$type];
	foreach($activefields as $field)
	{
		if(!isSecretaire() && $fieldsTypes[$field] == "avis" && $report->$field =="")
		{
			$report->$field = "Avis possibles (supprimer avis inutiles)\n";
			foreach($avis as $key => $value)
				$report->$field .= $key."\n";
		}
	}

	thing_to_xml_node($report,$node, $doc, "", $activefields);

	$doc->appendChild($node);

	$doc->formatOutput = true;
	$ret = $doc->save($filename);
	if($ret == false)
		throw string("Failed to save reports as xml");
	else
		return $ret;
}

/*
 *
* For xsl processing (should converge with upper)
*/

function getReportsAsXML($filter_values, $sort_criteria = array(), $keep_br = true)
{
	global $report_types_with_multiple_exports;

	$doc = new DOMDocument("1.0","UTF-8");
	$root = $doc->createElement("rapports");
	$rows = filterSortReports(getCurrentFiltersList(), $filter_values, $sort_criteria);;

	if(isset($filter_values['id_edit']))
		$root->setAttribute('id_edit',$filter_values['id_edit']);

	//	kk();
	foreach($rows as $row)
	{
			$types = array();
			if($row->type == REPORT_CANDIDATURE)
			{
			  //			  throw new Exception("rr");
				global $concours_ouverts;
				if(needs_audition_report($row))
				  {
					$types[] = "Audition";
				  }
				if(is_classe($row))
					$types[] = "Classement";
			}
			else
			{
			   $types = array($row->type);
			}
	       
		foreach($types as $type)
		{
			$row->type = $type;
			$elem = createXMLReportElem($row,$doc,$keep_br);
			$root->appendChild($elem);
		}
	}

	$doc->appendChild($root);
	return $doc;
}


function appendLeaf($fieldname, $fieldvalue, DOMDocument $doc, DOMElement $node)
{
	$leaf = $doc->createElement($fieldname);
	//$leaf->appendChild($doc->createCDATASection ( stripInvalidXml($fieldvalue)));
	$stripped = stripInvalidXml($fieldvalue);
	//echo $stripped;

	$before = array();
	$after = array();

	global $html_tags;
	foreach($html_tags as $tag)
	  {
	    $before[] = "<".$tag.">";
	    $before[] = "</".$tag.">";
	    $after[] = "#".$tag."#";
	    $after[] = "#/".$tag."#";
	  }
	$before[] = "<br/>";
	$after[] = "#br/#";

	$before[] = "<br />";
	$after[] = "#br /#";

	$stripped = str_replace($before, $after, $stripped);

	$stripped = str_replace(
				array("<", ">"),
				array("&lt;", "&gt;"),
			$stripped);
	$stripped = str_replace($after, $before, $stripped);

	$leaf->appendChild($doc->createCDATASection($stripped));
	$node->appendChild($leaf);
}

function EnteteDroit($row, $units)
{
	global $enTetesDroit;
	global $avis_classement;
	global $avis_candidature_short;

	$result = "";

	$bloc_unite = "";
	if(isset($units[$row->unite]))
	{
		if($row->unite != $units[$row->unite]->nickname && $units[$row->unite]->nickname != "")
			$bloc_unite .= " ".$row->unite." (".$units[$row->unite]->nickname.", ".$units[$row->unite]->directeur.")";
		else if($units[$row->unite]->directeur != "")
			$bloc_unite .= " ".$row->unite.", (".$units[$row->unite]->directeur.")";
		else
			$bloc_unite .= " ".$row->unite;
	}
	else
	{
		$bloc_unite .= " ".$row->unite;
	}

	if(is_avis_classement($row->avis))
	{
		$result .= '<B>Classement, nom et unité :</B><br/>';
		$result .= '<B>'.classement_from_avis($row->avis)."</B><br/>";
		$result .= $row->nom." ".$row->prenom."<br/>";
		$result .= $bloc_unite;
	}
	else if(is_equivalence_type($row->type))
	{
		$result .= $row->nom." ".$row->prenom."<br/>";
	}
	else if(is_rapport_chercheur($row))
	{
		$result = '<B>Nom, prénom et affectation du chercheur :</B><br/>';
		$result .= $row->nom." ".$row->prenom."<br/>";
		$result .= $bloc_unite;
	}
	else if(is_ecole_or_colloque_type($row->type))
	{
		$unit = $row->unite;
		if(array_key_exists($row->unite,$units) && $units[$row->unite]->nickname != "")
			$unit = $units[$row->unite]->nickname;
		//		if($row->intitule != "")
		//	$result .= $row->intitule." "."<br/>";
		$result .= $row->nom." "."<br/>".$row->prenom." (".$unit.") ";
	}
	else if(is_rapport_unite($row))
	{
	  //	  throw new Exception("roger");
		$result .= $bloc_unite;
	}
	else
	{
		$result = "Avis";
	}
	return $result;
}

function EnteteGauche($row)
{
	global $type_avis_classement;
	$result = '<B>Objet de l’évaluation :</B><br/><I>'.$row->intitule.'</I>';

	if(in_array($row->type, $type_avis_classement))
		$result .= "<br/>".$row->grade_rapport;
	else if($row->type == REPORT_CANDIDATURE)
		$result .= "<br/>".$row->concours;
	/*
	 else if( is_equivalence_type($row->type) )
		$result .= "<br/>pour les concours ".(isset($row->grade) ? $row->grade : "");
	$result .= $row->intitule;
	*/

	return $result;
}

function type_to_xsl($type)
{
	global $typesRapportsToXSL;
	if(isset($typesRapportsToXSL[$type]))
	{
		return $typesRapportsToXSL[$type];
	}
	else  {
		return $typesRapportsToXSL[''];
	}
}

function createXMLReportElem($row, DOMDocument $doc, $keep_br = true)
{
	global $enTetesDroit;
	global $typesRapportsToCheckboxes;
	global $typesRapportsToCheckboxesTitles;
	global $typesRapportsToFormula;

	$sessions = sessionArrays();
	$units = unitsList();

	$rapportElem = $doc->createElement("rapport");
	$rapportElem->setAttribute("id",$row->id);
	$rapportElem->setAttribute("id_origine",$row->id_origine);

	$fieldsspecial = array('unite','date','type','signataire');

	global $typesRapportsAll;
	if(trim($row->intitule) == "" && isset($typesRapportsAll[$row->type]))
		$row->intitule = $typesRapportsAll[$row->type];
	
	//On ajoute une formule à la fin du rapport si nécessaire
	if(array_key_exists($row->type,$typesRapportsToFormula))
	{
		$formulas = $typesRapportsToFormula[$row->type];
		if(array_key_exists($row->avis,$formulas))
		{
		  //		  throw new Exception("Test");
			$formula = $formulas[$row->avis];
			$row->rapport .= "<br/><br/>".stripInvalidXml($formula);
		}
	}

	global $fieldsRapportAll;
	//On ajoute tous les fields pas spéciaux
	foreach ($fieldsRapportAll as $fieldID => $title)
		if(isset($row->$fieldID) && !in_array($fieldID,$fieldsspecial))
		appendLeaf($fieldID,   $keep_br ? $row->$fieldID : remove_br($row->$fieldID) , $doc, $rapportElem);



	if($row->type == "Audition")
	{
		global $concours_ouverts;
		global $liste_sous_jurys;
			
		global $tous_sous_jury;
		$concours = $row->concours;
		$sousjury = $row->sousjury;

		if(isset($tous_sous_jury[$concours]) &&  isset($tous_sous_jury[$concours][$sousjury]))
		{

			$users = listUsers();
			$login = isset($tous_sous_jury[$concours][$sousjury]["president"]) ? $tous_sous_jury[$concours][$sousjury]["president"] : "";
			$description = isset($users[$login]) ? $users[$login]->description : "";
			//			$description = "";

			appendLeaf("signataire",$description, $doc, $rapportElem);
			appendLeaf("signature", "", $doc, $rapportElem);
		}
		else
		{
			appendLeaf("signataire","", $doc, $rapportElem);
			appendLeaf("signature", "", $doc, $rapportElem);
		}

		  appendLeaf("signataire_titre", "Président(e) de section de jury", $doc, $rapportElem);
			
		$candidat = get_or_create_candidate($row);
		appendLeaf("audition", $candidat->audition, $doc, $rapportElem);
		appendLeaf("grade_concours", substr($concours_ouverts[$row->concours],0,3), $doc, $rapportElem);
			
		$conc = $sessions[$row->id_session];
		appendLeaf("annee_concours", substr($conc,strlen($conc)-4,4), $doc, $rapportElem);
			
		$conc = $row->concours;
		if(strlen($conc) == 4)
			appendLeaf("nom_concours", (substr($conc,0,2)."/".substr($conc,2,4)), $doc, $rapportElem);
		else
			appendLeaf("nom_concours", $conc, $doc, $rapportElem);
	}
	else
	{
		if(isset($row->signataire) && $row->signataire != "")
		  {
		  appendLeaf("signataire", $row->signataire, $doc, $rapportElem);
		  appendLeaf("signataire_titre", "Président(e) par interim", $doc, $rapportElem);
		  }
		else
		  {
		  appendLeaf("signataire", get_config("president"), $doc, $rapportElem);
		  appendLeaf("signataire_titre", get_config("president_titre"), $doc, $rapportElem);
		  }
		global $dossier_stockage;
		global $rootdir;
		global $dossier_stockage_short;
		if($row->signataire == "" 
		   && !is_rapport_concours($row) 
		   && isset($row->statut) 
		   && $row->statut=="publie" 
		   && file_exists($dossier_stockage.signature_file)
		   )
		{
			appendLeaf("signature", "/".$dossier_stockage_short.signature_file, $doc, $rapportElem);
			appendLeaf("signature_source", "/".$dossier_stockage_short.signature_file, $doc, $rapportElem);
			//			echo $dossier_stockage.signature_file;
			//die(0);

		}
		else
		{
			appendLeaf("signature", $rootdir.signature_blanche, $doc, $rapportElem);
			appendLeaf("signature_source", $rootdir.signature_blanche, $doc, $rapportElem);
		}
	}

	//	throw new Exception($row->type);
	if( $row->type == "Classement" || is_concours($row->type))
	{
	  global $avis_candidature_short;
		appendLeaf("pretty_avis", $avis_candidature_short[$row->avis], $doc, $rapportElem);

		global $concours_ouverts;
		if(isset($concours_ouverts[$row->concours]))
			appendLeaf("grade_concours", substr($concours_ouverts[$row->concours],0,3	), $doc, $rapportElem);
		else
			appendLeaf("grade_concours", "CR", $doc, $rapportElem);

		$conc = $sessions[$row->id_session];
		appendLeaf("annee_concours", substr($conc,strlen($conc)-4,4), $doc, $rapportElem);
			


		$conc = $row->concours;
		if(strlen($conc) == 4)
			appendLeaf("nom_concours", (substr($conc,0,2)."/".substr($conc,2,4)), $doc, $rapportElem);
		else
			appendLeaf("nom_concours", $conc, $doc, $rapportElem);
	}

	//On ajoute les entete gauche et droit
	appendLeaf("entetegauche", EnteteGauche($row), $doc, $rapportElem);
	appendLeaf("entetedroit", EnteteDroit($row,$units), $doc, $rapportElem);

	$cid = ($row->section >= 50);
	if($cid)
	  {
	appendLeaf("typesectioncid", "CID", $doc, $rapportElem);
	appendLeaf("typesectioncidlong", "CID", $doc, $rapportElem);
	appendLeaf("typesectioncidtreslong", "COMMISSION INTERDISCIPLINAIRE", $doc, $rapportElem);
	  }
	else
	  {
	appendLeaf("typesectioncidlong", "Section du Comité national", $doc, $rapportElem);
	appendLeaf("typesectioncid", "section", $doc, $rapportElem);
	appendLeaf("typesectioncidtreslong", "SECTION", $doc, $rapportElem);
	  }


	//On ajoute le nickname du labo
	$value = "";
	if(array_key_exists($row->unite,$units))
		$value = $units[$row->unite]->nickname." (".$row->unite.")";
	else
		$value = $row->unite;
	appendLeaf("unite", $value, $doc, $rapportElem);

	//On ajoute la date du jour
	date_default_timezone_set('Europe/Paris');
	setlocale (LC_TIME, 'fr_FR.utf8','fra', 'fra_fra');
	//date("j/F/Y")
	if(strpos($_SERVER['SERVER_SOFTWARE'],"IIS") === false)
		appendLeaf("date", strftime("%#d %B %Y",  time()), $doc, $rapportElem);
	else
		appendLeaf("date",  utf8_encode(strftime("%#d %B %Y", time())), $doc, $rapportElem);

	//On ajoute les cases à choix multiple, si nécessaires
	//pour les classements, le classement figure dans l'en tête
	global $typesRapportsPromotion;
	if(!is_classement($row->type) && !is_avis_classement($row->avis) && !in_array($row->type,$typesRapportsPromotion))
	{
		global $labelCheckboxes;
		global $evalCheckboxes;

		$boxes = is_eval_type($row->type) ? $evalCheckboxes : $labelCheckboxes;
		global $typesRapportToAvis;
		$checkBoxes = array();
		if(isset($typesRapportToAvis[$row->type]))
		{
			$aviss = $typesRapportToAvis[$row->type];
			foreach($aviss as $avis => $label)
			{
				if(isset($boxes[$avis]))
				  $checkBoxes[$avis] = $cid ? str_replace("section","CID",$boxes[$avis]) : $boxes[$avis];
			}
		}

		if(count($checkBoxes) > 0)
		{
			$leaf = $doc->createElement("checkboxes");
			$checkBoxesTitle = isset($typesRapportsToCheckboxesTitles[$row->type]) ? $typesRapportsToCheckboxesTitles[$row->type] : "<B>Avis de la section</B>";
			if($cid)
			  $checkBoxesTitle = str_replace("section","CID",$checkBoxesTitle);
			$leaf->setAttribute("titre", $checkBoxesTitle);
			foreach($checkBoxes as $avis => $intitule)
			{
				$subleaf = $doc->createElement("checkbox");
				$subleaf->appendChild($doc->createCDATASection ($intitule));
				if($avis == $row->avis)
					$subleaf->setAttribute("mark", "checked");
				else
					$subleaf->setAttribute("mark", "unchecked");
				$leaf->appendChild($subleaf);
			}
			$rapportElem->appendChild($leaf);
		}
	}


	//On ajoute le nickname de la session
	if(isset($sessions[$row->id_session]))
	{
	  $session = $row->id_session;
	  if( (strlen($session) > 4) 
	     && is_numeric(substr($session,strlen($session)-4)) )
	     $session = substr($session, 0, strlen($session) -4)." ".substr($session,strlen($session)-4);
	appendLeaf("session", $session, $doc, $rapportElem);
	$row->session = $sessions[$row->id_session];
	}
	else
	{
		appendLeaf("session", "Session inconnue (".$row->id_session.")", $doc, $rapportElem);
		$row->session = "Session inconnue (".$row->id_session.")";
	}


	//On ajoute le numero de la section
	appendLeaf("section_nb", currentSection(), $doc, $rapportElem);

	//On ajoute l'intitulé de la section
	appendLeaf("section_intitule", get_config("section_intitule"), $doc, $rapportElem);




	$ltype = isset($id_rapport_to_label[$row->type]) ? $id_rapport_to_label[$row->type] :"";
	//On ajoute le type du rapport et le nom de fichier
	appendLeaf("type", $ltype, $doc, $rapportElem);
	/*$filename = filename_from_node($nodes->item(0)).".pdf";*/

	$filename = filename_from_doc($row);
	$rapportElem->setAttribute('filename', $filename);
	$rapportElem->setAttribute('type', $row->type);

	return $rapportElem;
}

function rowToXMLDoc($row)
{
	$doc = new DOMDocument("1.0","UTF-8");
	$elem = createXMLReportElem($row, $doc);
	$doc->appendChild($elem);
	$doc->formatOutput = TRUE;
	return $doc;
}

function XMLToHTML(DOMDocument $doc,$xsl_file)
{
	$xsl = new DOMDocument("1.0","UTF-8");
	$xsl->load($xsl_file);

	$proc = new XSLTProcessor();
	$proc->importStyleSheet($xsl);
	$html = $proc->transformToXML($doc);
	return $html;
}

//Returns the name of the file
function filename_from_node(DOMNode $node)
{
	$nom = "";
	$prenom = "";
	$grade = "";
	$unite = "";
	$type = "";
	$session = "Session";
	$avis = "";
	$concours = "";
	//	$ecole = "";
	$intitule = "";

	foreach($node->childNodes as $child)
	{
		switch($child->nodeName)
		{
			case "nom": $nom = mb_convert_case(replace_accents($child->nodeValue), MB_CASE_TITLE); break;
			case "prenom": $prenom = mb_convert_case(replace_accents($child->nodeValue), MB_CASE_TITLE); break;
			case "grade": $grade = $child->nodeValue; break;
			case "unite": $unite = $child->nodeValue; break;
			case "type": $type = $child->nodeValue; break;
			case "session": $session = $child->nodeValue; break;
			case "avis": $avis = $child->nodeValue; break;
			case "concours": $concours = $child->nodeValue; break;
			//		case "ecole": $ecole = $child->nodeValue; break;
			case "intitule": $intitule = $child->nodeValue; break;
		}
	}

	return filename_from_params($nom, $prenom, $grade, $unite, $type, $session, $avis, $concours);
}



?>