<?php


function compileFieldAsTxt($row,$field,$value)
{
  if($value == "") return "";
	global $id_rapport_to_label;
	global $dont_export_doc_fields;
	global $fieldsTypes;
	global $fieldsAll;
	global $tous_avis;
	global $export_doc_fields;
	$title = compute_title($row, $field);

	if(isset($fieldsTypes[$field]) && $fieldsTypes[$field] == "avis" && isset($tous_avis[$value]))
	  {
	    $value = $tous_avis[$value];
	  }
	if(isset($fieldsTypes[$field]) && $fieldsTypes[$field] == "treslong")
	  {
	    return "<B>".$title."</B><br/><table border=\"1\"><tr><td>".html_entity_decode($value)."</td></tr></table><br/><br/>";
	  }
	else
	  {
	    return "<B>".$title."</B>:".html_entity_decode($value)."<br/>";
	  }
}

function compileObjectAsTXT($row)
{
  return "hello";
}

function compileObjectsAsTXT($rows)
{
	$result = "";

	$first = true;
	$result .= "<head> <meta charset=\"UTF-8\"> </head><br/>";
	/*
	$result .= "****************************************************\n";
	$result .= "Pour un bon affichage des accents:  sélectionner 'UTF-8' n";
	$result .= "****************************************************\n";
	*/

	global $id_rapport_to_label;
	global $dont_export_doc_fields;
	global $fieldsTypes;
	global $fieldsAll;
	global $tous_avis;
	global $export_doc_fields;

	foreach($rows as $row)
	{
		$result .= "<h2>".$id_rapport_to_label[$row->type]."</h2>";

		if(is_rapport_chercheur($row) || is_rapport_concours($row))
		  $result .= "<h3>".$row->nom." ".$row->prenom."</h3>";

		foreach( $export_doc_fields as $field)
		  $result .= compileFieldAsTxt($row,$field,$row->$field);

		if(is_rapport_chercheur($row) || is_rapport_concours($row))
		{
			$candidat = get_or_create_candidate($row);
			foreach($candidat as $field => $value)
			  if(is_field_visible($row, $field) && !in_array($field, $dont_export_doc_fields) && !in_array($field,$export_doc_fields))
			      $result .= 
				compileFieldAsTxt($row, $field,$candidat->$field);
		}
		foreach($row as $field => $value)
		  if(is_field_visible($row, $field) && !in_array($field, $dont_export_doc_fields) && !in_array($field,$export_doc_fields))
		    $result .= compileFieldAsTxt($row, $field,$value);

		$result.="<br/>\f";
	}
	return $result;

}

function compileObjectsAsCSV($fields, $rows, $text=array(), $sep =";" , $enc='"', $del="\n")
{
	global $csv_composite_fields;
	global $typesRapportsAll;
	$result = "";

	$first = true;

	foreach($text as $mots)
	{
		foreach($mots as $mot)
			$result.=$sep.$enc.$mot.$enc;
		$result.=$del;
	}

	foreach($fields as $field)
	{
		$result.= ($first ? "" : $sep) .$enc.$field.$enc;
		$first = false;
	}

	$result.=$del;

	foreach($rows as $row)
	{
		$first = true;
		foreach($fields as $field)
		{
			if(isset($csv_composite_fields[$field]))
			{
				$subfields = $csv_composite_fields[$field];
				$result.= ($first ? "" : $sep).$enc;
				foreach($subfields as $subfield)
					$result.=(isset($row->$subfield) ? str_replace($enc, '#', $row->$subfield) :"")." ";
				$result.= $enc;
			}
			else
			{
			  $data = isset($row->$field) ? str_replace($enc, '#', $row->$field) : "";
			  if($field == "type" && isset($row->$field) && isset($typesRapportsAll[$data]))
			    $data = $typesRapportsAll[$data];

			  $result .= ($first ? "" : $sep).$enc.$data.$enc;
			}
			$first = false;

		}
		$result.=$del;
	}
	return $result;
}

function compileReportsAsCSV($rows, $fields = null)
{
	global $mandatory_export_fields;
	$activefields = $fields;
	if($activefields == null)
		$activefields = array_unique(array_merge($mandatory_export_fields, get_editable_fields($rows[0])));
	if(count($rows) < 1)
	{
		$fakerow = (object) array();
		foreach($fields as $field)
			$fakerow->$field = "à remplacer par ".$field;
		$fakerow->type = "EvalVague";
		$rows[] = $fakerow;
	}
	return compileObjectsAsCSV($activefields, $rows);
}

function compileUnitsAsCSV($rows)
{
	global $fieldsUnitsDB;
	$fields = array();
	foreach($fieldsUnitsDB as $field => $value)
		$fields[] = $field;

	return compileObjectsAsCSV($fields, $rows);
}


?>