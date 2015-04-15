<?php



function getFilterValue($filter_name)
{
	global $filtersAll;
	$filters = $filtersAll;
	$answer = $filters[$filter_name]['default_value'];

	if(isset($_REQUEST["filter_".$filter_name]))
	{
		$answer = $_REQUEST["filter_".$filter_name];
//	echo "Setting filter_value from request ".$filter_name." ".$answer."<br/>";
	}
	else if(isset($_SESSION["filter_".$filter_name]))
	{
		$answer =   $_SESSION["filter_".$filter_name];
	//	echo "Setting filter_value from session ".$filter_name." ".$answer."<br/>";
	}
	$_SESSION["filter_".$filter_name] = $answer;
	return $answer;
}


function resetOrder($field = "")
{
	if($field == "")
		unset($_SESSION["tri"]);
	else
	{
	$crit = isset($_SESSION["tri"]) ? $_SESSION["tri"]["crit"] : "";
	if($crit == $field)
		$ord = ($_SESSION["tri"]["ord"] =="+") ? "-" : "+";
	else 
		$ord = "+";
	$_SESSION["tri"] = array("crit" => $field, "ord" => $ord);
	}
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
	global $filtersDelegation;
	global $fieldsTypes;
	
	if(is_current_session_concours())
		$filters = $filtersConcours;
	else if(is_current_session_delegation())
		$filters = $filtersDelegation;
	else
		$filters = $filtersReports;
	
	if(isset($filters['rapporteur']))
		$filters['rapporteur']['liste'] = simpleListUsers();

	
	$units = simpleUnitsList();
	foreach($fieldsTypes as $field => $type)
		if($type=='unit' && isset($filters[$field]))
		$filters[$field]['liste'] = $units;
	
	$filters["id_session"] = current_session_id();
	return $filters;
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
	$sortc = array();
	if(isset($_SESSION["tri"]))
		$sortc[ $_SESSION["tri"]["crit"]] =  $_SESSION["tri"]["ord"];

	if(!isset($sortc["nom"]))
		$sortc["nom"] = "+";
	if(!isset($sortc["labo1"]))
		$sortc["labo1"] = "+";
	
	return $sortc;
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



?>