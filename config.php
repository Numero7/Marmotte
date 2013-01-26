<?php 

function xml_node_to_array($node)
{
	if($node->hasChildNodes())
	{
		$result = array();
		foreach($node->childNodes as $subnode)
		{
			if($subnode->localName != "")
				$result[trim($subnode->localName,"_")] = xml_node_to_array($subnode);
		}
		if(count($result)>0)
			return $result;
	}
	return $node->nodeValue;
}

function thing_to_xml_node($thing,$node, $doc)
{
	if(is_array($thing))
	{
		foreach($thing as $field => $value)
		{
			if($field != "")
			{
				$subnode = $doc->createElement("_".$field);
				thing_to_xml_node($value,$subnode,$doc);
				$node->appendChild($subnode);
			}
			else
			{
				$node->nodeValue = $value;
			}
		}
	}
	else
	{
		$node->nodeValue = $thing;
	}
}

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
	$config = xml_node_to_array($root);
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

	thing_to_xml_node($_SESSION['config'],$root, $doc);

	$doc->formatOutput = true;
	
	$doc->save(config_file);
}

function get_config($name)
{
	load_config();
	if(isset($_SESSION['config']))
	{
		$config = $_SESSION['config'];
		if(isset($config[$name]))
			return $config[$name];
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