<?php 

require_once('manage_sessions.inc.php');
require_once('manage_unites.inc.php');
require_once('manage_users.inc.php');
require_once('utils.inc.php');
require_once('xml_tools.inc.php');

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.collect_vars', 'on');
ini_set('xdebug.collect_params', '4');
ini_set('xdebug.dump_globals', 'on');
ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');

function implode_with_keys($assoc,$inglue=':',$outglue=','){
	$res=array();
	foreach($assoc as $tk=>$tv){
		$res[] = $tk.$inglue.$tv;
	}
	return implode($outglue,$res);
}

/*
 *
* For exportation
*/
function exportReportsAsXML($reports,$filename)
{
	global $typesRapportToAvis;
	global $fieldsTypes;
	global $mandatory_export_fields;

	$result = "";

	$doc = new DOMDocument("1.0","UTF-8");
	$root = $doc->createElement("rapports");

	$first = true;
	$activefields = array();

	foreach($reports as $report)
	{
		$node = $doc->createElement("evaluation");

		if($first)
		{
			$activefields = array_unique(array_merge($mandatory_export_fields, get_editable_fields($report)));
			$first = false;
		}

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
		$root->appendChild($node);
	}

	$doc->appendChild($root);

	$doc->formatOutput = true;
	$ret = $doc->save($filename);
	if($ret == false)
		throw string("Failed to save reports as xml");
	else
		return $ret;
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
	global $fieldsAll;
	global $report_types_with_multiple_exports;
	

	$doc = new DOMDocument("1.0","UTF-8");
	$root = $doc->createElement("rapports");
	$rows = filterSortReports(getCurrentFiltersList(), $filter_values, $sort_criteria);;
	
	if(isset($filter_values['id_edit']))
		$root->setAttribute('id_edit',$filter_values['id_edit']);

	foreach($rows as $row)
	{
		$types = array($row->type);
		if(isset($report_types_with_multiple_exports[$row->type]))
		{
			$types = array();
			if($row->type == "Candidature")
			{
				global $concours_ouverts;
				if(is_auditionneCR($row))
					$types[] = "Audition";
				if(is_classe($row))
					$types[] = "Classement";
			}
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
	$leaf->appendChild($doc->createCDATASection (normalizeFieldCDATA($fieldvalue)));
	$node->appendChild($leaf);
}

function EnteteDroit($row, $units)
{
	global $enTetesDroit;
	global $typesRapportsToEnteteDroit;
	global $avis_classement;
	global $avis_candidature;

	$result = "";

	if( ($row->type == "Promotion") && ($row->grade != "CR2"))
	{
		$result = $enTetesDroit['PromotionDR'];
		$result .= $avis_classement[$row->avis].'<br/>';
		$result .= $row->nom." ".$row->prenom.'<br/>';
		if(array_key_exists($row->unite,$units))
			$result .= " ".$row->unite." (".$units[$row->unite]->nickname.")";
		else
			$result .= " ".$row->unite;
		return $result;
	}

	if(array_key_exists($row->type, $typesRapportsToEnteteDroit))
	{
		$type = $typesRapportsToEnteteDroit[$row->type];
		if(array_key_exists($type, $enTetesDroit))
		{
			$result = $enTetesDroit[$type];
			if($type == 'Individu')
			{
				$result .= $row->nom." ".$row->prenom."<br/>";
				if(array_key_exists($row->unite,$units))
					$result .= " ".$row->unite." (".$units[$row->unite]->nickname.")";
				else
					$result .= " ".$row->unite;
			}
			else if($type == 'Equivalence')
			{
				$result .= $row->nom." ".$row->prenom."<br/>";
			}
			else if($type == 'Concours')
			{
				$result .= $avis_candidature[$row->avis];
				$result .= "<br/>";
				$result .= $row->nom." ".$row->prenom;
			}
			else if($type == 'Unite')
			{
				if(array_key_exists($row->unite,$units))
				{
					$unit = $units[$row->unite];
					$result .= " ".$row->unite." (".$unit->nickname.")<br/>".$unit->directeur;
				}
				else
				{
					$result .= " ".$row->unite;
				}
			}
			else if($type == 'Ecole')
			{
				$unit = $row->unite;
				if(array_key_exists($row->unite,$units))
					$unit = $units[$row->unite]->nickname;
				$result .= $row->ecole." "."<br/>".$row->prenom." ".$row->nom." (".$unit.") ";
			}
			return $result;
		}
	}
	return "";
}

function EnteteGauche($row)
{
	global $typesRapportsToEnteteGauche;
	$result = isset($typesRapportsToEnteteGauche[$row->type]) ? $typesRapportsToEnteteGauche[$row->type] : "";
	if($row->type == "Promotion")
	{
		$oldgrade = $row->grade;
		$newgrade = "CR1";
		if( $oldgrade == "DR2")
			$newgrade = "DR1";
		if( $oldgrade == "DR1")
			$newgrade = "DRCE1";
		if( $oldgrade == "DRCE1")
			$newgrade = "DRCE2";
		$result .= "<br/>".$newgrade;
	}
	else if($row->type == "Candidature")
	{
		$result .= "<br/>".$row->concours;
	}
	else if($row->type == "Equivalence")
	{
		$result .= "<br/>pour les concours ".$row->grade;
	}

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
	global $typesRapports;
	global $typesRapportsToEnteteGauche;
	global $enTetesDroit;
	global $typesRapportsToEnteteDroit;
	global $typesRapportsToCheckboxes;
	global $typesRapportsToCheckboxesTitles;
	global $typesRapportsToFormula;

	$sessions = sessionArrays();
	$units = unitsList();

	$rapportElem = $doc->createElement("rapport");
	$rapportElem->setAttribute("id",$row->id);
	$rapportElem->setAttribute("id_origine",$row->id_origine);

	$fieldsspecial = array('unite','date','type');

	//On ajoute une formule à la fin du rapport si nécessaire
	if(array_key_exists($row->type,$typesRapportsToFormula))
	{
		$formulas = $typesRapportsToFormula[$row->type];
		if(array_key_exists($row->avis,$formulas))
		{
			$formula = $formulas[$row->avis];
			$row->rapport .= "<br/><br/>".normalizeField($formula);
		}
	}

	global $fieldsRapportAll;
	//On ajoute tous les fields pas spéciaux
	foreach ($fieldsRapportAll as $fieldID => $title)
		if(isset($row->$fieldID) && !in_array($fieldID,$fieldsspecial))
		appendLeaf($fieldID,   $keep_br ? $row->$fieldID : remove_br($row->$fieldID) , $doc, $rapportElem);

	global $presidents_sousjurys;
	
	if($row->type == "Audition")
	{
		if(isset($presidents_sousjurys[$row->sousjury]))
		{
			
			global $concours_ouverts;
			
			if(isset($presidents_sousjurys[$row->sousjury]["nom"]) && isset($presidents_sousjurys[$row->sousjury]["signature"]))
			{
				appendLeaf("signataire",$presidents_sousjurys[$row->sousjury]["nom"], $doc, $rapportElem);
				appendLeaf("signature", $presidents_sousjurys[$row->sousjury]["signature"], $doc, $rapportElem);
			}

			$candidat = get_or_create_candidate($row);
			appendLeaf("parcours", $candidat->parcours, $doc, $rapportElem);
			appendLeaf("projet", $candidat->projetrecherche, $doc, $rapportElem);
			appendLeaf("productionResume", $candidat->productionResume, $doc, $rapportElem);
			appendLeaf("grade_concours", substr($concours_ouverts[$row->concours],0,2), $doc, $rapportElem);
				
		}
	}
	else
	{
		appendLeaf("signataire", get_config("president"), $doc, $rapportElem);
	}

	if($row->type == "Classement")
	{
		global $concours_ouverts;
		appendLeaf("grade_concours", substr($concours_ouverts[$row->concours],0,2), $doc, $rapportElem);
		/*
		echo "appended leaf ".substr($concours_ouverts[$row->concours],0,2);
		return;
		*/
	}

	//On ajoute les entete gauche et droit
	appendLeaf("entetegauche", EnteteGauche($row), $doc, $rapportElem);
	appendLeaf("entetedroit", EnteteDroit($row,$units), $doc, $rapportElem);


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
	
	/*
	 date_default_timezone_set('Europe/Paris');

	appendLeaf("date", date("j/n/Y",strtotime($row->date)), $doc, $rapportElem);
	*/

	//On ajoute les cases à choix multiple, si nécessaires
	if(array_key_exists($row->type,$typesRapportsToCheckboxes))
	{
		$checkBoxes = $typesRapportsToCheckboxes[$row->type];
		$checkBoxesTitle = $typesRapportsToCheckboxesTitles[$row->type];

		$leaf = $doc->createElement("checkboxes");
		$leaf->setAttribute("titre", $checkBoxesTitle);
			
		foreach($checkBoxes as $avis => $intitule)
		{
			$subleaf = $doc->createElement("checkbox");
			$subleaf->appendChild($doc->createCDATASection ($intitule));
			$subleaf->setAttribute("mark", ($avis == $row->avis) ? "checked" : "unchecked");
			$leaf->appendChild($subleaf);
		}

		$rapportElem->appendChild($leaf);
	}


	//On ajoute le nickname de la session
	appendLeaf("session", $sessions[$row->id_session], $doc, $rapportElem);


	//On ajoute le numero de la section
	appendLeaf("section_nb", get_config("section_nb"), $doc, $rapportElem);

	//On ajoute l'intitulé de la section
	appendLeaf("section_intitule", get_config("section_intitule"), $doc, $rapportElem);

	//On ajoute le nom et le tire du signataire
	appendLeaf("signataire_titre", get_config("president_titre"), $doc, $rapportElem);

	if(isSecretaire())
		appendLeaf("signature_source", "img/signature.jpg", $doc, $rapportElem);
	else
		appendLeaf("signature_source", "img/signatureX.jpg", $doc, $rapportElem);


	$row->session = $sessions[$row->id_session];

	//On ajoute le type du rapport et le nom de fichier
	appendLeaf("type", $row->type, $doc, $rapportElem);
	/*$filename = filename_from_node($nodes->item(0)).".pdf";*/
	
	$rapportElem->setAttribute('filename', filename_from_doc($row));
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
	global $typesRapportsUnites;

	$nom = "";
	$prenom = "";
	$grade = "";
	$unite = "";
	$type = "";
	$session = "Session";
	$avis = "";
	$concours = "";

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
		}
	}

	return filename_from_params($nom, $prenom, $grade, $unite, $type, $session, $avis, $concours);
}



?>