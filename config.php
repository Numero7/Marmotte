<?php 

require_once('xml_tools.inc.php');
require_once('config.inc.php');


function load_config($force = false)
{
	if(!$force && isset($_SESSION['config']))
		return;
	$doc = new DOMDocument('1.0','utf-8');
	if(file_exists(config_file) === false ||  $doc->load(config_file) === false)
	{
		$doc->load(config_file_save);
		$doc->save(config_file);
	}

	$root = $doc->getElementsByTagName("config")->item(0);
	$config = xml_node_to_array($root,"_");
	$_SESSION['config'] = $config;
	return $config;
}

function get_raw_config()
{
	$result = file_get_contents(config_file);
	if($result === false)
		throw new Exception("Failed to read config file");
	$result = str_replace(array(">\n"),array("magictempkey"),$result);
	$result = str_replace(array(">","</","magictempkey"),array(">\n","\n  </",">\n"),$result);
	return $result;
	//return $result;
}

function put_raw_config($data)
{
	$result = file_put_contents(config_file,$data);
	if($result === false)
		throw new Exception("Failed to write config file");
	load_config(true);
	save_config();
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

	rrr();
	
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
			return "";
		//throw new Exception("No config item with name '".$name."'");
	}
	else
	{
		throw new Exception("No config loaded yet!!");
	}
}

function set_config($name,$value)
{
	load_config();
	$_SESSION['config'][$name] = $value;
	save_config();
}

?>