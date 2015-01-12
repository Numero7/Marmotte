<?php 

$configs = array(
		"section_shortname"=> array("intitule court de la section ou CID","Section 6"),
		"section_intitule"=> array("intitule long de la section","Sciences de l'information : fondements de l'informatique, calculs, algorithmes, représentations, exploitations"),
		"president_titre" => array("titre du président, utilisé pour signer les rapports", "Président de la Section 6"),
		"president" => array("nom du président, utilisé pour signer les rapports", "Alan Türing"),
		"adresse_du_site" => array("adresse web du site","http://marmotte.cn6.fr"),
		"topics" => array("liste des mots clés et leurs codes séparés par des ';' ", "1a;graphes;1b;automates;2;calcul intensif;3;théorie des jeux"),
		"webmaster" => array("expéditeur des emails", "alan.turing@cnrs.fr"),
		"welcome_message" => array("mesage d'accueil", "Bienvenue sur le site de la section 6")
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

	$sql = "DELETE FROM ".config_db." WHERE `section`='".$section."' and `key`='".$key."';";
	mysqli_query($dbh,$sql);
	
	$sql = "INSERT INTO ".config_db."(`section`, `key`, `value`)";
	$sql .= " VALUES ('".mysqli_real_escape_string($dbh,$section)."','".mysqli_real_escape_string($dbh,$key)."','".mysqli_real_escape_string($dbh,$value)."')";
	$result = mysqli_query($dbh,$sql);
	
	if(!$result)
		throw exception("Failed to add default_value to config key '".$key." for section '".$section."'");

	$_SESSION["config"][mysqli_real_escape_string($dbh,$key)] = mysqli_real_escape_string($dbh,$value);
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
	$topics_str = "";
	foreach($topics as $code => $value)
	if($value != "")
		$topics_str .= str_replace(";",",",$code).";".str_replace(";",",",$value).";";
		
	set_config("topics", $topics_str);
}

function get_topics()
{
	$topics = array();
	$topexpl = explode(";",get_config("topics"));
	for($i = 0; $i + 1< count($topexpl); $i+=2)
		$topics[$topexpl[$i]] = $topexpl[$i+1]; 
	return $topics;
}

function get_rubriques($people = true)
{
	$rubriques = array();
	if($people)
		$topexpl = explode(";",get_config("rubriques_people"));
	else
		$topexpl = explode(";",get_config("rubriques_rapports"));
	for($i = 0; $i + 1< count($topexpl); $i+=2)
		$rubriques[$topexpl[$i]] = $topexpl[$i+1];
	ksort($rubriques);
	return $rubriques;
}

function set_rubriques($rubriques, $people = true)
{
	$rubrique_str = "";
	foreach($rubriques as $index => $rubrique)
		if($rubrique != "")
		$rubrique_str .= str_replace(";",",",$index).";".str_replace(";",",",$rubrique).";";
	if($people)
		set_config("rubriques_people", $rubrique_str);
	else
		set_config("rubriques_rapports", $rubrique_str);
}

function add_rubrique($index, $rubrique, $people = true)
{
	$rubriques = get_rubriques($people);
	$rubriques[$index] = $rubrique;
	set_rubriques($rubriques, $people);
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