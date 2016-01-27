<?php 
	$configus = array(
		"welcome_message" => array("Message d'accueil", "Bienvenue sur le site de la section 6"),
		"section_shortname"=> array("Intitulé court de la section ou CID","Section 6"),
		"section_intitule"=> 
   array("intitulé long de la section","Sciences de l\'information : fondements de l\'informatique, calculs, algorithmes, représentations, exploitations"),
		"president_titre" => array("Titre du président, utilisé pour signer les rapports", "Président de la Section 6"),
		"president" => array("Nom du président, utilisé pour signer les rapports", "Alan Türing"),
		"webmaster" => array("Adresse email de l'expéditeur des emails", "alan.turing@cnrs.fr"),
		"webmaster_nom" => array("Signataire des emails et pdfs", "Alan Türing"),
		"email_scc"=>array("Email utilisé pour informer le SCC d'un changement de rapporteur sur une candidature",""),
			  "bur_can_affect"=> array("Les membres du bureau peuvent affecter les rapporteurs","true"),
			  "bur_can_meta"=> array("Les membres du bureau peuvent modifier les rubriques chercheurs/candidats","false"),
			  "bur_can_keywords"=> array("Les membres du bureau peuvent modifier les mots-clés","true"),
			  "sec_can_edit"=> array("Le secrétaire et le président peuvent éditer les prérapports","false"),
		"acn_can_edit_avis"=> array("L'ACN peut éditer les avis","true"),
		"acn_can_edit_reports"=> array("L'ACN peut éditer les rapports de section","true"),
		"acn_can_edit_rapporteurs"=> array("L'ACN peut modifier les rapporteurs","true"),
		"sec_can_edit_valid"=> array("En plus du président, le secrétaire peut éditer les rapports en mode 'validation'","true"),
		"acn_can_edit_valid"=> array("En plus du président, l'ACN peut éditer les rapports en mode 'validation'","true"),
		"B_can_view_DR1_promo"=> array("Les rangs B ont accès aux dossiers de promo DR1","true"),
		"C_can_view_DR1_promo"=> array("Les rangs C ont accès aux dossiers de promo DR1","false"),
		"show_rapporteur3"=> array("Afficher le raporteur3 et son avis dans l'écran principal","false"),
		"show_theme1"=> array("Afficher le mot-clé1 dans l'écran principal","true"),
		"show_theme2"=> array("Afficher le mot-clé2 dans l'écran principal","true"),
		"show_theme3"=> array("Afficher le mot-clé3 dans l'écran principal","false"),
		"formule_standard_Promotion_oui"=>array("Formule standard pour les promotions avec avis 'oui' (typiquement CR1)",""),
		"formule_standard_Promotion_non"=>array("Formule standard pour les promotions avec avis 'non'",""),
		"formule_standard_Titularisation_tres_favorable"=>array("Formule standard pour les titularisations avec avis TF","")
			  );
		

function init_config()
{
  global $configus;

	if(!isset($_SESSION['filter_section']))
	{
		removeCredentials();
		throw new Exception("Cannot init config, unknown section");
	}
	$section = $_SESSION['filter_section'];

	if($section == "0")
	    $configus["sessions_synchro"]= array("Liste des sessions à synchroniser, séparées par des ';'", "Printemps2015;Automne2015");

	$sql = "SELECT * FROM ".config_db." WHERE `section`='".real_escape_string($section)."';";
	$query = sql_request($sql);
	$_SESSION["config"] = array();
	while($result = mysqli_fetch_object($query))
		$_SESSION["config"][$result->key] = $result->value;
	
	/* default config */
	foreach($configus as $key => $config)
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

function get_option($key)
{
  return (get_config($key,"true",false) == "true");
}

function get_config($key,$default_value="", $create_if_needed=true, $section = "")
{	
	if($key == "")
		throw new Exception("No config with empty key");
		
if($section === "")
{
	if(!isset($_SESSION["config"]))
		init_config();

	if(!isset($_SESSION["config"][$key]))
	{
		if(!$create_if_needed)
			throw new Exception("No config key '".$key."' for section '".$section."' in database");
		else
			set_config($key,$default_value);
	}
	return $_SESSION["config"][$key];
}
else
{
	$sql = "SELECT * FROM ".config_db." WHERE `section`='".real_escape_string($section)."' AND `key`='".real_escape_string($key)."';";
	$query = sql_request($sql);
	while($result = mysqli_fetch_object($query))
		return $result->value;
	return $default_value;
}
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
	else
	{
		echo "Failed to delete keyword with index '" + $index + "'<br/>"; 
		foreach($topics as $index => $mot)
		{
			echo "Index '".$index."' keyword '".$mot."'<br/>";
		}
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