<?php 

require_once('xml_tools.inc.php');


function load_config($force = false)
{
	if(!$force && isset($_SESSION['config']))
		return;
	$doc = new DOMDocument('1.0','utf-8');
	if(!$doc->load(config_file))
	{
		$doc->load(config_file_save);
		$doc->save(config_file);
	}

	$root = $doc->getElementsByTagName("config")->item(0);
	$config = xml_node_to_array($root,"_");
	$_SESSION['config'] = $config;
	return $config;
}

function save_config()
{
	
	$doc = new DOMDocument('1.0','UTF-8');
	
	$root = $doc->createElement("config");
	$doc->appendChild($root);

	if(!isset($_SESSION['config']))
		throw new Exception("No config to save");

	thing_to_xml_node($_SESSION['config'],$root, $doc,"_");

	$doc->formatOutput = true;
	
	$doc->save(config_file);
}

function get_config($name)
{
	load_config();
	if(isset($_SESSION['config']))
	{
		$config = $_SESSION['config'];
		if(isset($config[trim($name,"_")]))
			return $config[trim($name,"_")];
		else
			throw new Exception("No config item with name '".$name."'");
	}
	else
	{
		throw new Exception("No config loaded yet!!");
	}
}

function set_config($name,$value)
{
	if(isset($_SESSION['config']) && isset($_SESSION['config']['name']))
	{
		$_SESSION['config']['name'] = $value;
		save_config();
	}
	else
	{
		throw new Exception("No config item with name '".$name."'");
	}
}

?>