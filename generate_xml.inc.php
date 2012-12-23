<?php 

require_once('manage_sessions.inc.php');

function getReportsAsXMLArray($id_session=-1, $type_eval="", $sort_crit="", $login_rapp="")
{
	global $fieldsAll;
	$result = filterSortReports($id_session, $type_eval, $sort_crit, $login_rapp);

	//to map id_session s to session nicknames
	$sessions = sessionArrays();
	$units = unitsList();

	$docs = array();
	while ($row = mysql_fetch_object($result))
	{
		$docs[] = rowToXMLDoc($row, $sessions,$units);
	}

	return $docs;
}

function getReportsAsXML($id_session=-1, $type_eval="", $sort_crit="", $login_rapp="")
{
	global $fieldsAll;
	$doc = new DOMDocument();
	$root = $doc->createElement("rapports");
	$result = filterSortReports($id_session, $type_eval, $sort_crit, $login_rapp);

	//to map id_session s to session nicknames
	$sessions = sessionArrays();
	$units = unitsList();

	while ($row = mysql_fetch_object($result))
		appendRowToXMLDoc($row, $sessions,$units,$doc);

	return $doc;
}


function appendLeaf($fieldname, $fieldvalue, DOMDocument $doc, DOMElement $node)
{
	$leaf = $doc->createElement($fieldname);
	$data = $doc->createCDATASection ($fieldvalue);
	$leaf->appendChild($data);
	$node->appendChild($leaf);
}

function appendRowToXMLDoc($row, $sessions, $units, DOMDocument $doc)
{
	global $fieldsAll;
	global $typesEval;
	global $typesEvalUpperCase;

	if(!$sessions)
		$sessions = sessionArrays();

	if(!$units)
		$units = unitsList();


	$rapportElem = $doc->createElement("rapport");

	$fieldsspecial = array('unite','date','type');

	foreach ($fieldsAll as $fieldID => $title)
		if(!in_array($fieldID,$fieldsspecial))
			appendLeaf($fieldID, $row->$fieldID, $doc, $rapportElem);

	//On ajoute le nickname du labo
	$value = "";
	if(array_key_exists($row->unite,$units))
		$value = $row->unite." (".$units[$row->unite].")";
	else
		$value = $row->unite;
	appendLeaf("unite", $value, $doc, $rapportElem);

	//On ajoute la date du jour
	appendLeaf("data", date("j/m/Y"), $doc, $rapportElem);

	
	//On ajoute le type de rapport, et en pretty print
	//et en version longue en majuscule
	
	appendLeaf("type", $row->type, $doc, $rapportElem);
	
	if(array_key_exists($row->type,$typesEval))
		$value = $typesEval[$row->type];
	else
		$value = $row->type;
	appendLeaf("prettytype", $value, $doc, $rapportElem);
	
	if(array_key_exists($row->type,$typesEvalUpperCase))
		$value = $typesEvalUpperCase[$row->type];
	else
		$value = $row->type;
	appendLeaf("uppercasetype", $value, $doc, $rapportElem);
	
	//On ajoute le nickname de la session
	appendLeaf("session", $sessions[$row->id_session], $doc, $rapportElem);
	

	//On ajoute le numero de la section
	appendLeaf("section_nb", section_nb, $doc, $rapportElem);
	
	//On ajoute l'intitulé de la section
	appendLeaf("section_intitule", section_intitule, $doc, $rapportElem);
	
	//On ajoute le nom et le tire du signataire
	appendLeaf("signataire", president, $doc, $rapportElem);
	appendLeaf("signataire_titre", president_titre, $doc, $rapportElem);

	$doc->appendChild($rapportElem);

}

function rowToXMLDoc($row, $sessions = null, $units = null)
{
	$doc = new DOMDocument();
	appendRowToXMLDoc($row, $sessions, $units, $doc);
	return $doc;
}

function XMLToHTML(DOMDocument $doc)
{
	$xsl = new DOMDocument();
	$xsl->load('xslt/html.xsl');
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($xsl);
	$html = $proc->transformToXML($doc);
	return $html;
}

?>