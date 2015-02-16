<?php 

$configs = array(
		"section_shortname"=> array("intitulé court de la section ou CID","Section 6"),
		"section_intitule"=> array("intitulé long de la section","Sciences de l\'information : fondements de l\'informatique, calculs, algorithmes, représentations, exploitations"),
		"president_titre" => array("titre du président, utilisé pour signer les rapports", "Président de la Section 6"),
		"president" => array("nom du président, utilisé pour signer les rapports", "Alan Türing"),
		"webmaster" => array("adresse email de l'expéditeur des emails", "alan.turing@cnrs.fr"),
		"webmaster_nom" => array("signataire des emails et pdfs", "Alan Türing"),
		"welcome_message" => array("message d'accueil", "Bienvenue sur le site de la section 6")
);

function init_config()
{
	global $dbh;
	global $configs;
	
	if(!isset($_SESSION['filter_section']))
		throw new Exception("Cannot init config, unknown section");
	$section = $_SESSION['filter_section'];

	$sql = "SELECT * FROM ".config_db." WHERE `section`='".$section."';";
	$query = mysqli_query($dbh,$sql);
	if(!$query)
		throw new Exception("Failed to process sql query '".$sql."'");
	$_SESSION["config"] = array();
	while($result = mysqli_fetch_object($query))
		$_SESSION["config"][$result->key] = $result->value;
	
	/* default config */
	foreach($configs as $key => $config)
		if(!isset($_SESSION["config"][$key]))
		set_config($key, $config[1]);
}

function set_config($key,$value)
{
	global $dbh;
	if(!isset($_SESSION['filter_section']))
		throw new Exception("Cannot set config, section unknown");
	$section = $_SESSION['filter_section'];

	if($section == "") $section = 0;
	
	$sql = "DELETE FROM ".config_db." WHERE `section`='".$section."' and `key`='".$key."';";
	mysqli_query($dbh,$sql);
	
	$sql = "INSERT INTO ".config_db."(`section`, `key`, `value`)";
	$sql .= " VALUES ('".mysqli_real_escape_string($dbh,$section)."','".mysqli_real_escape_string($dbh,$key)."','".mysqli_real_escape_string($dbh,$value)."')";
	
	$result = mysqli_query($dbh,$sql);
	
	if(!$result)
		throw new exception("Failed to add default_value to config key '".$key." for section '".$section."':<br/>".mysqli_error($dbh));

	$_SESSION["config"][mysqli_real_escape_string($dbh,$key)] = $value;
}

function save_config_from_request()
{
	foreach($_REQUEST as $key => $value)
		if(isset($_SESSION["config"][$key]) && $_SESSION["config"][$key] != $value)
			set_config($key,$value);
}

function get_config($key,$default_value="", $create_if_needed=true)
{	
	if($key == "")
		throw new Exception("No config with empty key");
		
	if(!isset($_SESSION["config"]))
		init_config();

	if(!isset($_SESSION["config"][$key]))
	{
		if(!$create_if_needed)
			throw exception("No config key '".$key." for section '".$section."' in database");
		else
			set_config($key,$default_value);
	}
	return $_SESSION["config"][$key];
}

function get_array_config($key,$delimiter ="|")
{
	if($key == "")
		throw new Exception("No config with empty key");

	if(!isset($_SESSION["config"]))
		init_config();

	if(!isset($_SESSION["config"][$key]))
			set_config($key,"");

	$to_parse = explode($delimiter, $_SESSION["config"][$key]);
	$result = array();
	for($i = 0; $i < count($to_parse) -1; $i+=2)
		$result[$to_parse[$i]] = $to_parse[$i+1];
	
/*	if($key=="topics")
		rr();
		*/
	return $result;
}

function set_array_config($key,$value,$delimiter ="|")
{
	
	if($key == "")
		throw new Exception("No config with empty key");
	if(!is_array($value))
		throw new Exception("Cannot set array config with non array value");
	ksort($value);
	$linear = "";
	foreach($value as $index => $val)
		$linear .= str_replace($delimiter," ",$index).$delimiter.str_replace($delimiter," ",$val).$delimiter;
	set_config($key, $linear);
}

function get_configs()
{
	if(!isset($_SESSION["config"]))
		init_config();
	return $_SESSION["config"];
}

function add_topic($index, $topic)
{
	$topics = get_topics();
	$topics[$index] = $topic;
	set_topics($topics);
}

function remove_topic($index)
{
	$topics = get_topics();
	if(isset($topics[$index]))
	{
	unset($topics[$index]);
	set_topics($topics);
	}
}

function set_topics($topics)
{
	foreach($topics as $key => $value)
	{
	while( strpos($value,  $key . " - ") !== FALSE )
		$value = substr($value, strlen($key . " - "));
	$topics[$key] = $value;
	}
	set_array_config("topics", $topics);
}

function get_topics()
{
	$result = get_array_config("topics");
	foreach($result as $key => $value)
		$result[$key] = str_replace("\\", "", $value);
	return $result;
}

function get_rubriques($type)
{
	global $rubriques_supplementaires;
	$key = $rubriques_supplementaires[$type][0];
	return get_array_config($key);
}

function set_rubriques($rubriques, $type)
{
	global $rubriques_supplementaires;
	$key = $rubriques_supplementaires[$type][0];	
	set_array_config($key, $rubriques);
}

function add_rubrique($index, $rubrique, $type)
{
	global $rubriques_supplementaires;
	/*
	$pref = $rubriques_supplementaires[$type][1];
	*/
	$rubriques = get_rubriques($type);
	$rubriques[/*$pref.*/$index] = $rubrique;
	set_rubriques($rubriques, $type);
}


function remove_rubrique($index, $people = true)
{
	$rubriques = get_rubriques($people);
	if(isset($rubriques[$index]))
	{
		unset($rubriques[$index]);
		set_rubriques($rubriques, $people);
	}
}



?>