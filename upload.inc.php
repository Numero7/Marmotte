<?php

require_once 'config.inc.php';
require_once 'import.inc.php';
require_once 'manage_people.inc.php';


function process_upload($create = false, $directory = null)
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
					case UPLOAD_ERR_NO_FILE: throw new Exception("Choissisez un fichier avec 'Parcourir...' avant de cliquer sur 'Ajouter fichier'"); break;
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
				$type = isset($_REQUEST['type']) ? real_escape_string($_REQUEST['type']) : "";
				$subtype = isset($_REQUEST['subtype']) ? real_escape_string($_REQUEST['subtype']) : "";
				switch($type)
				{
					case "evaluations":
					case "unites":
						{
							if(array_key_exists($suffix,$typeImports))
							{
								$dir = dossier_temp();
								$target_path = $dir.$type.".".$suffix;
								if(move_uploaded_file($tmpname,$target_path))
									return process_import($type,$suffix,$target_path,$subtype,$create)."<br/>";
								else
									throw new Exception('Failed to store uploaded file "'.$tmpname.'" of size '.$_FILES['uploadedfile']['size'].' to '.$target_path);
							}
							else
								throw new Exception("File type *.'".$suffix."' not available for import, only *.csv and *.xml are accepted at the time");
						}
						break;
					case "signature":
						{
							global $dossier_stockage;
							$dir = $dossier_stockage."/img";
							create_dir_if_needed($dir);
							if(move_uploaded_file($tmpname,$dossier_stockage."/".signature_file))
								echo "<p>Nouvelle signature enregistrée</p>";
							else
								throw new Exception("Failed to store siganture as file ".signature_file);
						}
						break;
					case "candidatefile":
						{
							$new_name = isset($_REQUEST['ajoutphoto']) ? "id.jpg" : $files['name'];
								if(!move_uploaded_file($tmpname, $directory."/".$new_name ))
									throw new Exception("Failed to add file");
								return ("Fichier ".$files['name']." ajouté au candidat ");
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