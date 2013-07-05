<?php


function compileObjectsAsTXT($rows)
{
	global $typesRapportsChercheurs;
	global $typesRapportsConcours;
	$result = "";

	$first = true;

	foreach($rows as $row)
	{
		$result .= "******************************************************************************\n";
		$result .= "****************$row->type ****************************************************\n";
		$result .= "******************************************************************************\n";
		
		$first = true;
			if(isset($row->type) && (in_array($row->type, $typesRapportsChercheurs) || in_array($row->type, $typesRapportsConcours) ))
		{
			$candidat = get_or_create_candidate($row);
			foreach($candidat as $field => $value)
			{
				if(is_field_visible($row, $field))
				{
					$result.= $field.":\n\t". str_replace('"', '#', $value)."\n";
				}
			}
		}
		
		foreach($row as $field => $value)
		{
				
			if(is_field_visible($row, $field))
			{
				$result.= $field.":\n\t". str_replace('"', '#', $value)."\n";
			}
		}

		$result.="\n\n";
	}
	return $result;

}

function compileObjectsAsCSV($fields, $rows,$sep =";" , $enc='"', $del="\n")
{
	global $csv_composite_fields;
	$result = "";

	$first = true;

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
		$fakerow->type = "Evaluation-Vague";
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