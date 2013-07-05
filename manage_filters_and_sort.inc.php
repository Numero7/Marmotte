<?php


function getFilterValue($filter_name)
{
	global $filtersAll;
	$filters = $filtersAll;
	$answer = $filters[$filter_name]['default_value'];
	if(isset($_REQUEST["filter_".$filter_name]))
		$answer = mysql_real_escape_string($_REQUEST["filter_".$filter_name]);
		//$answer = $_REQUEST["filter_".$filter_name] != "" ? $_REQUEST["filter_".$filter_name] : $filters[$filter_name]['default_value'];
	else if(isset($_SESSION["filter_".$filter_name]))
		$answer =   $_SESSION["filter_".$filter_name];
	$_SESSION["filter_".$filter_name] = $answer;
	return $answer;
}


function setSortingValue($filter_name, $value)
{

	$_REQUEST["tri_".$filter_name] = $value;
	$_SESSION["tri_".$filter_name] = $value;
}

function getSortingValue($filter_name)
{
	global $filtersAll;
	$filters = $filtersAll;
	$answer = "";
	if(isset($_REQUEST["tri_".$filter_name]))
		$answer = mysql_real_escape_string($_REQUEST["tri_".$filter_name]);
	else if(isset($_SESSION["tri_".$filter_name]))
		$answer =   mysql_real_escape_string($_SESSION["tri_".$filter_name]);

	$last = substr($answer,strlen($answer) -1,1);
	if( $last != "+" && $last != "-")
	{
		if(isset($_SESSION["tri_".$filter_name]))
		{
			$current = $_SESSION["tri_".$filter_name];
			if(strlen($current) > 0 && substr($current,strlen($current)-1,1) == "+")
				$answer .= "-";
			else if(strlen($current) > 0 && substr($current,strlen($current)-1,1) == "-")
				$answer .= "+";
			else
				$answer .= "+";
		}
		else
		{
			$answer .= "+";
		}
	}

	$_SESSION["tri_".$filter_name] = $answer;


	return $answer;
}

function resetOrder()
{
	$filters = getCurrentSortingList();
	foreach($filters as $filter)
		if(!isset($_REQUEST["tri_".$filter]))
		$_REQUEST["tri_".$filter] = strval(count($filters) + 10)."+";
}

function resetFilterValues()
{
	$filters = getCurrentFiltersList();
	foreach($filters as $filter => $data)
		if(!isset($_REQUEST["filter_".$filter]))
		$_REQUEST["filter_".$filter] = $data['default_value'];
	resetOrder();
}

function resetFilterValuesExceptSession()
{
	$filters = getCurrentFiltersList();
	foreach($filters as $filter => $data)
		if($filter != 'id_session' && !isset($_REQUEST["filter_".$filter]))
		$_REQUEST["filter_".$filter] = $data['default_value'];
	resetOrder();
}

function getCurrentFiltersList()
{
	global $filtersConcours;
	global $filtersReports;
	global $fieldsTypes;
	
	if(is_current_session_concours())
		$filters = $filtersConcours;
	else
		$filters = $filtersReports;
	
	if(isset($filters['rapporteur']))
		$filters['rapporteur']['liste'] = simpleListUsers();

	
	$units = simpleUnitsList();
	foreach($fieldsTypes as $field => $type)
		if($type=='unit' && isset($filters[$field]))
		$filters[$field]['liste'] = $units;
	
	if(isSecretaire())
	{
		global $statutsRapports;
		$filters['statut'] =  array('name'=>"Statut" , 'liste' => $statutsRapports, 'default_value' => "tous", 'default_name' => "");
	}
	
	return $filters;
	
}

function getCurrentSortingList()
{
	global $fieldsSummary;
	global $fieldsTriConcours;
	if(is_current_session_concours())
		return $fieldsTriConcours;
	else
		return $fieldsSummary;
}

function getFilterValues()
{
	$filters = getCurrentFiltersList();
	$filter_values = array();
	foreach($filters as $filter => $data)
		$filter_values[$filter] =  getFilterValue($filter);
	return $filter_values;
}

function getSortingValues()
{
	$filters = getCurrentSortingList();
	$filter_values = array();
	foreach($filters as $filter)
		$filter_values[$filter] =  getSortingValue($filter);

	$sorted = array();
	$max = 0;
	foreach($filter_values as $field => $value)
	{
		$index = intval(substr($value,0,strlen($value) -1));
		$max = max( $max, $index);
		while(key_exists($index, $sorted))
			$index++;
		$sorted[$index] = $field;
	}

	ksort($sorted);

	$result = array();
	$index = 1;
	foreach($sorted as $key => $field)
	{
		$value = $filter_values[$field];
		if($key < $max)
			$result[$field] = $index.substr($value,strlen($value) -1,1);
		else
			$result[$field] = $max."+";
		setSortingValue($field, $result[$field]);
		$index++;
	}
	return $result;
}



function showCriteria($sortCrit, $crit)
{
	$order = "";
	$index = -1;
	if (isset($sortCrit[$crit]))
	{
		$order = $sortCrit[$crit];
		$index = array_search($crit, array_keys($sortCrit))+1;
	}
	if ($order=="ASC")
	{
		return "<img src=\"img/sortup.png\" alt=\"$crit sorted ascendently\"/><span style=\"text-decoration:none;\">($index)</span>";
	}
	else if ($order=="DESC")
	{
		return "<img src=\"img/sortdown.png\" alt=\"$crit sorted descendently\"/><span style=\"text-decoration:none;\">($index)</span>";
	}
	else
	{  return "<img src=\"img/sortneutral.png\" alt=\"$crit sorted neutrally\"/>";
	}
}

function dumpEditedCriteria($sortCrit, $edit_crit)
{
	$result = "";
	$order = "";
	if (isset($sortCrit[$edit_crit]))
	{
		$order = $sortCrit[$edit_crit];
	}
	if ($order=="ASC")
	{
		$sortCrit[$edit_crit] = "DESC";
	}
	else if ($order=="")
	{
		$sortCrit[$edit_crit] = "ASC";
	}
	else if ($order=="DESC")
	{
		//We want at least one sort criterion
		//also removes bug
		//if(count($sortCrit) > 1)
		unset($sortCrit[$edit_crit]);
		//else
		//$sortCrit[$edit_crit] = "ASC";
	}
	foreach($sortCrit as $crit => $order)
	{
		if ($order=="ASC")
		{
			$order = "*";
		}
		else if ($order=="DESC")
		{
			$order = "-";
		}
		if ($result != "")
		{
			$result .= ";";
		}
		$result .=  $order.$crit;
	}
	return urlencode($result);
}


?>