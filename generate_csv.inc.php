<?php


function compileObjectsAsTXT($rows)
{
	$result = "";

	$first = true;

	$result .= "****************************************************\n";
	$result .= "Pour un bon affichage des accents:  sélectionner 'UTF-8' n";
	$result .= "****************************************************\n";
	
	global $id_rapport_to_label;
	global $dont_export_fields;
	foreach($rows as $row)
	{
		$result .= "*****************************************************\n";
		$result .= "".$id_rapport_to_label[$row->type]."\n";
		$result .= "*****************************************************\n";

		$first = true;
		if(is_rapport_chercheur($row) || is_rapport_concours($row))
		{
			$candidat = get_or_create_candidate($row);
			foreach($candidat as $field => $value)
			  if(is_field_visible($row, $field) && !in_array($field, $dont_export_fields))
					$result.= $field.":\n\t". str_replace(array('"',"<br />"), array('#',''), $value)."\n";
		}

		foreach($row as $field => $value)
		  if(is_field_visible($row, $field) && $value != "" && !in_array($field, $dont_export_fields))
				$result.= $field.":\n\t". str_replace(array('"',"<br />"), array('#',''), $value)."\n";

		$result.="\n\n\f";
	}
	return $result;

}

function compileObjectsAsCSV($fields, $rows, $text=array(), $sep =";" , $enc='"', $del="\n")
{
	global $csv_composite_fields;
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
					$result.=(isset($row->$subfield) ? str_replace('"', '#', $row->$subfield) :"")." ";
				$result.= $enc;
			}
			else
			{
				$result.= ($first ? "" : $sep).$enc.(isset($row->$field) ? str_replace('"', '#', $row->$field) :"").$enc;
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