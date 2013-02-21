<?php

require_once 'config.inc.php';
require_once 'import.inc.php';
require_once 'manage_candidates.inc.php';


function process_upload()
{
	global $typeImports;

	try
	{
		if (isset($_FILES['uploadedfile']))
		{
			$files = $_FILES['uploadedfile'];
			if (isset($files['tmp_name']) && isset($files['name']))
			{

				switch($files['error'])
				{
					case UPLOAD_ERR_OK: break;
					case UPLOAD_ERR_INI_SIZE: throw new Exception("Error: UPLOAD_ERR_INI_SIZE"); break;
					case UPLOAD_ERR_FORM_SIZE: throw new Exception("Error: UPLOAD_ERR_FORM_SIZE"); break;
					case UPLOAD_ERR_PARTIAL: throw new Exception("Error: UPLOAD_ERR_PARTIAL"); break;
					case UPLOAD_ERR_NO_FILE: throw new Exception("Error: UPLOAD_ERR_NO_FILE"); break;
					case UPLOAD_ERR_NO_TMP_DIR: throw new Exception("Error: UPLOAD_ERR_NO_TMP_DIR"); break;
					case UPLOAD_ERR_CANT_WRITE: throw new Exception("Error: UPLOAD_ERR_CANT_WRITE"); break;
					case UPLOAD_ERR_EXTENSION: throw new Exception("Error: UPLOAD_ERR_EXTENSION"); break;
					default: throw new Exception("Unknown error  : ". intval($files['error']));
				}
				$filename = $files['name'];
				$tmpname = $files['tmp_name'];

				if(strlen($filename) <3)
					throw new Exception("Filename '"+ $filename+"' too short");

				$suffix = substr($filename,strlen($filename) -3, 3);
				$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : "";
				$subtype = isset($_REQUEST['subtype']) ? $_REQUEST['subtype'] : "";

				switch($type)
				{
					case "evaluations":
						{
							if(array_key_exists($suffix,$typeImports))
							{
								$target_path = "uploads/".$type.".".$suffix;
								if(move_uploaded_file($tmpname,$target_path))
								{
									echo "File uploaded and stored as ".$target_path."</br>";
									return process_import($type,$suffix,$target_path,$subtype)."<br/>";
								}
								else
									throw new Exception('Failed to store uploaded file "'.$tmpame.'" of size '.$_FILES['uploadedfile']['size'].' to '.$target_path);
							}
							else
								throw new Exception("File type *.'".$suffix."' not available for import, only *.csv and *.xml are accepted at the time");

						}
						break;
					case "config":
						{
							if(move_uploaded_file($tmpname,config_file))
							{
								load_config(true);
								save_config();
								return "New config file saved and loaded";
							}
							else
								throw new Exception("Failed to store uploaded file as config file ".config_file);
						}
						break;
					case "signature":
						{
							if(move_uploaded_file($tmpname,signature_file))
								echo "<p>New signature saved<br/>";
							else
								throw new Exception("Failed to store siganture as file ".signature_file);
						}
						break;
					case "candidatefile":
						{
							if(isset($_REQUEST["candidatekey"]))
							{
								$key = $_REQUEST["candidatekey"];
								$candidate = get_candidate_from_key($key);
								if(!move_uploaded_file($tmpname, $candidate->fichiers."/".$files['name'] ))
									throw new Exception("Failed to add file to candidate ".$key);
								return ("Fichier ".$files['name']." ajouté au candidat ".$key);
							}
							else
								throw new Exception("Cannot add file: no candidate provided");
						}
						break;
					default:
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
		throw new Exception("Failed to upload data:<br/>".$exc->getMessage());
	}
}

?>