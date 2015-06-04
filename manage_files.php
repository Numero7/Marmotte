<?php

require_once('config.inc.php');
require_once('manage_sessions.inc.php');

function dossier_temp()
{
	global $dossier_temp;
	$dir = $dossier_temp.getLogin()."/";
	create_dir_if_needed2($dir);
	return $dir;
}


function is_associated_directory_people($candidate, $directory)
{
	return ($candidate->nom == "" || strpos(norm_name($directory), norm_name($candidate->nom) ) != false ) && ( $candidate->prenom == "" || strpos(norm_name($directory), norm_name($candidate->prenom) )  != false );
}

function is_associated_directory_unit($unit, $directory)
{
	return (isset($unit->unite) && $unit->unite != "" && strpos(norm_name($directory), norm_name($unit->unite) ) != false );
}

function find_files($row, $session, $create_directory_if_nexists = false)
{
	$files = array();
	$sql = "SELECT * FROM ".dsidbname.".".dsi_docs_db." WHERE dkey=\"DKEY\"";
	$result = sql_request($sql);
	global $typesRapportsAll;
	while($roww = mysqli_fetch_object($result))
	{
		//annee_doc		// code_tye_doc		// dkey		// nom_document		// path_sas		// session_doc
		$code = $roww->code_type_doc;
		$label = isset($typesRapportsAll[$code]) ? $typesRapportsAll[$code] : ("Inconnu ".$code);
		$label = $roww->annee_doc." - " . $roww->session_doc. " - " . $label. " - ". $roww->nom_document;
		$files[$label] =  $roww->path_sas."/".$roww->nom_document;
	}	
	
	$marmotte_files = array();
	$dir = "";
	if(  is_rapport_unite($row) )
	{
		$marmotte_files = find_unit_files($row,true, $session, true);
		$dir = get_unit_directory($row, $session, false);
	}
	else
	{
		if(isset($row->unite) && $row->unite == "") return;
		$marmotte_files = find_people_files($row,true, $session, true);
		$dir = get_people_directory($row, $session, false);
	}
	
	foreach($marmotte_files as $file)
	{
	$prettyfile = str_replace("_", " ", $file);
	if(strlen($file) > 20)
	{
		$arr = array(strtolower($row->nom), strtolower($row->prenom));
		$arr2 = array("","");
		$prettyfile = str_replace($arr, $arr2, $prettyfile);
	}
	$files[$pretty_file] = $dir."/".$file;
	}
	
	return $files;
}

function find_people_files($candidate, $force, $session, $create_directory_if_nexists = false, $directories = NULL)
{
	if($candidate->nom == "" && $candidate->prenom == "")
		return array();
	$basedir = get_people_directory($candidate, $session, false);

		/* rename if the directory name is not marmotte style (happens when imported from GETCC) */
	if($force && !is_dir($basedir))
	{
		if($directories == NULL)
			$directories = get_directories_list($session);
		foreach($directories as $directory)
		{
			if( is_associated_directory_people($candidate, $directory) )
			{
				echo "Renaming '".$directory . "' to '". $basedir."'<br/>";
				rename($directory,$basedir);
				break;
			}
		}
	}

	$basedir = get_people_directory($candidate, $session, $create_directory_if_nexists);

	if ( is_dir($basedir) )
	{
		$handle = opendir($basedir);
		if($handle != false)
		{
			while(1)
			{
				$file = readdir($handle);
				if($file === false)
					break;
				if($file != "." && $file != "..")
				{
					$filenames[] = $file;
					foreach($filenames as $file)
					{
						$timestamp = filemtime($basedir."/".$file);
						if($timestamp != false)
							$files[date("d/m/Y - h:i:s",$timestamp).$file]=$file;
					}
				}
			}
			closedir($handle);
		}
	}
	return $files;
}

function create_dir_if_needed2($basedir)
{
	if(!is_dir($basedir))
	{
		echo "Creating directory ".$basedir."<br/>";
		$result = mkdir($basedir,0770, true);
		if(!$result)
			echo "Failed to create directory ".$basedir."<br/>";
	}
}
function get_dir($session,$nom,$prenom)
{
	global $dossier_stockage_short;
	$session = str_replace( "..", "" ,$session);
	$nom = str_replace("..", "",$nom);
	$prenom = str_replace("..", "",$prenom);
	return  $dossier_stockage_short."/".$session."/".$nom."_".$prenom."/";
}

function get_people_directory($candidate, $session, $create_directory_if_nexists = false)
{
	$basedir = get_dir($session, $candidate->nom, $candidate->prenom);
	if($create_directory_if_nexists)
		create_dir_if_needed2($basedir);
	return $basedir;
}

function rename_people_directory($session, $nom,$prenom,  $pnom,$pprenom)
{
	$basedir = get_dir($session, $nom, $prenom);
	$pbasedir = get_dir($session, $pnom, $pprenom);
	rename($pbasedir,$basedir);	
}

function get_unit_directory($unit, $session, $create_directory_if_nexists = false)
{
	global $dossier_stockage_short;
	$basedir = $dossier_stockage_short."/".$session."/".$unit->unite."/";
	if($create_directory_if_nexists)
		create_dir_if_needed2($basedir);
	return $basedir;
}


function get_directories_list($session)
{
	global $dossier_stockage;
	$directories = array();
	$files = glob($dossier_stockage."/".$session."/*" );
	foreach($files as $file)
		if(is_dir($file))
			$directories[]= $file;
	return $directories;
}


function find_unit_files($unit, $force, $session, $create_directory_if_nexists = false, $directories = NULL)
{
  if($unit->unite == "")
    return array();

	$basedir = get_unit_directory($unit, $session, false);
	if($force && !is_dir($basedir))
	{
		if($directories == NULL)
			$directories = get_directories_list($session);
		foreach($directories as $directory)
		{
			if( is_associated_directory_unit($unit, $directory) )
			{
				echo "Renaming '".$directory . "' to '". $basedir."'<br/>";
				rename($directory,$basedir);
				break;
			}
		}
	}
	
	$basedir = get_unit_directory($unit, $session, $create_directory_if_nexists);
	
	if ( is_dir($basedir) )
	{
		$handle = opendir($basedir);
		if($handle != false)
		{
			$files = array();
			while(1)
			{
				$file = readdir($handle);
				if($file === false)
					break;
				if($file != "." && $file != "..")
				{
					$filenames[] = $file;
					foreach($filenames as $file)
					{
						$timestamp = filemtime($basedir."/".$file);
						if($timestamp != false)
							$files[date("d/m/Y - h:i:s",$timestamp).$file]=$file;
					}
				}
			}
			closedir($handle);
			return $files;
		}
	}
	else
		echo "No directory found<br/>";
	return array();
}
