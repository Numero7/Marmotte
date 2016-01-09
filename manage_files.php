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

function find_celcc_files($row, $session)
{
  $concours = getConcours();
  if(isset($concours[$row->concours]))
    {
      $grade = substr($concours[$row->concours]->intitule,0,2);
      $corps_grade = $concours[$row->concours]->grade_conc;
    }
  else
    {
       $grade = "";
       $corps_grade = "";
    }

  if(!isset($row->concoursid) || $row->concoursid=="")
    return array();
  $sql = "SELECT * FROM ".dsidbname.".".celcc_docs." ";
  $sql .="WHERE user_id='".real_escape_string($row->concoursid)."' ";
  $sql .= "AND (num_conc='' OR num_conc='".real_escape_string($row->concours)."') ";
  $sql .= "AND (corps_grade='' OR corps_grade='".$corps_grade."' OR corps_grade='".real_escape_string($grade)."') ";
  $result = sql_request($sql);
  $files = array();
		global  $dossier_stockage_dsi;

  while($doc = mysqli_fetch_object($result))
    {
      $pretty_name = $doc->type_doc." ".$doc->num_conc." ".$doc->corps_grade." ".$doc->annee_conc." - ";
      //      $pretty_name = str_replace(array("_",strtoupper($row->nom),strtoupper($row->prenom),$row->concoursid),array(""), $doc->nom_doc);
      $pretty_name .= str_replace(array("_",$row->concoursid),array(" "), $doc->nom_doc);
      $files[$pretty_name]=$dossier_stockage_dsi."/".$doc->path_sas.$doc->nom_doc;
    }  
  return $files;
}

function find_evaluation_files($row, $session)
{
	global $typesRapportsAll;
	$dsifiles = array();
	$sql= "";
	if(isset($row->NUMSIRHUS) && ($row->NUMSIRHUS != ""))
	  {
    	    $sql = "SELECT * FROM ".dsidbname.".".dsi_docs_liens_db." AS t1 ";
	    $sql .="JOIN ".dsidbname.".".dsi_docs_db." AS t2 ON t1.dkeydoc=t2.dkey WHERE t1.numsirhus=\"".$row->NUMSIRHUS."\"";
	  }
	else if(isset($row->unite) && ($row->unite != ""))
	  {
	    //	    echo "Looking for files of unit ".$row->unite."<br/>";
	    $unite = $row->unite;
    	    $sql = "SELECT * FROM ".dsidbname.".".dsi_docs_liens_unites_db." AS t1 ";
	    $sql .="JOIN ".dsidbname.".".dsi_docs_db." AS t2 ON t1.dkeydoc=t2.dkey WHERE t1.UNITE_EVAL=\"".$unite."\"";
	  }
	if($sql != "")
	  {
	    global $dossier_stockage_dsi;
	    $result = sql_request($sql);
	    global $typesdocs;
	    while($roww = mysqli_fetch_object($result))
	      {
		//		echo count($files)." Files<br/>";
		//annee_doc		// code_tye_doc		// dkey		// nom_document		// path_sas		// session_doc
		$code = $roww->code_type_doc;
		$label = isset($typesdocs[$code]) ? $typesdocs[$code] : ("Inconnu ".$code);
		$sess = ($roww->session_doc == "NULL") ? "" : $roww->session_doc;
		$label = $roww->annee_doc." - " . $sess. " - " . $label. " - ". $roww->nom_document;
		$dsifiles[$label] =  $dossier_stockage_dsi."/".$roww->path_sas."/".$roww->nom_document;
	      }	
		ksort($dsifiles);
		return $dsifiles;
	  }
}

function find_marmotte_files($row, $session, $create_directory_if_nexists = false, $subtype = "")
{
    global $typesRapportsAll;
    $files = array();
	$marmotte_files = array();
	$dir = "";
	if(  is_rapport_unite($row) )
	{
		if(isset($row->unite) && $row->unite == "") return $files;
		$marmotte_files = find_unit_files($row,true, $session, true);
		$dir = get_unit_directory($row, $session, false);
	}
	else
	{
	  $marmotte_files = find_people_files($row,true, $session, $subtype, true);
	  $dir = get_people_directory($row, $session, false,$subtype);
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
	$files[$prettyfile] = $dir."/".$file;
	}
	return $files;
}

function find_files($row, $session, $create_directory_if_nexists = false,$type, $subtype = "")
{
	if($type == "e-valuation")
	  return find_evaluation_files($row,$session);
	else if($type == "marmotte")
	  return find_marmotte_files($row,$session,$create_directory_if_nexists, $subtype);
	else if($type == "celcc")
	  return find_celcc_files($row,$session);
	return array();
}

function find_people_files($candidate, $force, $session, $type, $create_directory_if_nexists = false, $directories = NULL)
{
  $files = array();
	if($candidate->nom == "" && $candidate->prenom == "")
		return array();

	/*

	if($force && !is_dir($basedir))
	{
		if($directories == NULL)
			$directories = get_directories_list($session);
		foreach($directories as $directory)
		{
			if( is_associated_directory_people($candidate, $directory) )
			{
//				echo "Renaming '".$directory . "' to '". $basedir."'<br/>";
				rename($directory,$basedir);
				break;
			}
		}
	}*/

	$basedir = get_people_directory($candidate, $session, $create_directory_if_nexists, $type);

	if ( is_dir($basedir) )
	{
	  //echo "Find marmotte files ".$candidate->concoursid." ".$session." ".$create_directory_if_nexists." sub ".$type."<br/>";

		$handle = opendir($basedir);
		if($handle != false)
		{
		  //		echo $basedir."<br/>";
			while(1)
			{
				$file = readdir($handle);
				//				echo $file."<br/>";
				if($file === false)
				  break;
				if(is_dir($basedir."/".$file))
					continue;
					$filenames[] = $file;
					foreach($filenames as $file)
					{
						$timestamp = filemtime($basedir."/".$file);
						if($timestamp != false)
							$files[date("d/m/Y - h:i:s",$timestamp).$file]=$file;
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
	  //		echo "Creating directory ".$basedir."<br/>";
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

function get_people_directory($candidate, $session, $create_directory_if_nexists = false, $subtype = "")
{
	$basedir = get_dir($session, $candidate->nom, $candidate->prenom);
	if($create_directory_if_nexists)
		create_dir_if_needed2($basedir."/".$subtype);
	return $basedir."/".$subtype;
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
