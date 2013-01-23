<?php

require_once 'config.inc.php';
require_once 'process_csv.inc.php';

global $uploaded_csv_files;

try
{
	if (isset($_FILES['uploadedfile']))
	{
		if (isset($_FILES['uploadedfile']['tmp_name']))
		{
			$filename = $_FILES['uploadedfile']['tmp_name'];
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