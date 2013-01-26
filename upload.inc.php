<?php

require_once 'config.inc.php';
require_once 'process_csv.inc.php';
require_once 'manage_candidates.inc.php';
		
global $uploaded_csv_files;

try
{
	if (isset($_FILES['uploadedfile']))
	{
		$files = $_FILES['uploadedfile'];
		if (isset($files['tmp_name']))
		{
			$filename = $files['tmp_name'];
			$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
			$subtype = isset($_REQUEST['subtype']) ? $_REQUEST['subtype'] : "";
			if(array_key_exists($type,$uploaded_csv_files))
			{
				$target_path = $uploaded_csv_files[$type];
				if(move_uploaded_file($filename,$target_path))
				{
					echo "<p>File uploaded and stored as ".$target_path."</p>";
					echo "<p>".process_csv($type,$target_path,$subtype)."</p>";
				}
				else
					throw new Exception('Failed to store uploaded file "'.$filename.'" of size '.$_FILES['uploadedfile']['size'].' to '.$target_path);
			}
			else if($type=="config")
			{
				if(move_uploaded_file($filename,config_file))
				{
					load_config(true);
					save_config();
					echo "<p>New config file saved and loaded<br/>";
				}
				else
				{
					throw new Exception("Failed to store uploaded file as config file ".config_file);
				}
			}
			else if($type=="signature")
			{
				if(move_uploaded_file($filename,signature_file))
				{
					echo "<p>New signature saved<br/>";
				}
				else
				{
					throw new Exception("Failed to store siganture as file ".signature_file);
				}
			}
			else if($type=="candidatefile")
			{
				if(isset($_REQUEST["candidatekey"]))
				{
					$key = $_REQUEST["candidatekey"];
					$candidate = get_candidate_from_key($key);
					if(!move_uploaded_file($filename, $candidate->fichiers."/".$files['name'] ))
						throw new Exception("Failed to add file to candidate ".$key);
					echo "Fichier ".$files['name']." ajouté<br/>";						
				}
				else
				{
					throw new Exception("Cannot add file: no candidate provided");
				}
			}
			else
			{
				throw new Exception('Unknown action '.$type.", aborting");
			}
		}
		else
		{
			throw new Exception('No uploaded file name, aborting');
		}
	}
	else
	{
		throw new Exception('No uploaded file, aborting');
	}
}
catch(Exception $exc)
{
	echo "Failed to upload data:<br/>".$exc;
}

?>