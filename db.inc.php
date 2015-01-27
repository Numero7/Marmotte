<?php
require_once('config/configDB.inc.php');

function db_from_scratch()
{
	$msg = "Please properly configure the databse access in the file config/configDB.inc.php<br/>";
	$msg .= "and/or initialize the database with <h1><a href=\"marmotte.sql\">this SQL script</a></h1></br></br></br>.";
}

$dbh = NULL;

function db_connect($serverName,$dbname,$login,$password)
{
	global $dbh;
	$dbh = mysqli_connect($serverName, $login, $password, $dbname) or die("Could not connect to the server '".$serverName."<br/>".mysqli_error($dbh));
	
	mysqli_query($dbh, "SET NAMES utf8;");
	
	$databases = array(reports_db, users_db, sessions_db, units_db, people_db, config_db,concours_db);
	$new_columns = array(people_db => array("conflits" => "text") );

	/*
	foreach($databases as $database)	
	{
		$result = mysqli_query($dbh, "SHOW TABLES LIKE '$database'");
		if($result == false)
			throw new Exception("Cannot count the number of databases with name ".$database."<br/>".mysql_error());
		if(mysqli_fetch_array($result) === false)
			throw new Exception("No database with name ".$database);

		if(key_exists($database, $new_columns))
		{
			foreach($new_columns[$database] as $name => $type)
			{
				$sql="alter table ".$database." add ".$name." ".$type." NOT NULL";
				$result = mysqli_query($dbh, $sql);
			}
		}
	}
	*/
		
	return $dbh;
} ;

function db_disconnect()
{
	global $dbh;
	
	mysqli_close($dbh);
	$dbh=0;
} ;


function export_db($tablename)
{
	global $servername;
	global $serverlogin;
	global $serverpassword;
	global $dbname;
	
	$filename = $tablename.'.toto.sql';
	$worked = 0;

	$command='mysqldump --opt -h' .$servername .' -u' .$serverlogin .($serverpassword == "" ? "" : ' -p' .$serverpassword) .' ' .$dbname;
	
	$output=array();
	
	exec($command,$output);
	
	return implode("\n",$output);
	
	switch($worked){
		case 0:
			return 'Database <b>' .$dbname .'</b> successfully exported to <b>~/' .$filename .'</b>';
		case 1:
			return 'There was a warning during the export of <b>' .$dbname .'</b> to <b>~/' .$filename .'</b>'.implode("\n",$output);
		case 2:
			return 'There was an error during export. Please check your values.';
	}
}

function import_db($dbname)
{
	$filename = 'db/'.$dbname.'.sql';
	
	
	$command='mysql -h' .$servername .' -u' .$serverlogin .' -p' .$serverpassword .' ' .$dbname .' < ' .$filename;
	exec($command,$output=array(),$worked);
	switch($worked)
	{
		case 0:
			return 'Import file <b>' .$mysqlImportFilename .'</b> successfully imported to database <b>' .$mysqlDatabaseName .'</b>';
			break;
		case 1:
			return 'There was an error during import. Please make sure the import file is saved in the same folder as this script and check your values:<br/><br/><table><tr><td>MySQL Database Name:</td><td><b>' .$mysqlDatabaseName .'</b></td></tr><tr><td>MySQL User Name:</td><td><b>' .$mysqlUserName .'</b></td></tr><tr><td>MySQL Password:</td><td><b>NOTSHOWN</b></td></tr><tr><td>MySQL Host Name:</td><td><b>' .$mysqlHostName .'</b></td></tr><tr><td>MySQL Import Filename:</td><td><b>' .$mysqlImportFilename .'</b></td></tr></table>';
			break;
	}
}


function migrate( $section, $serverName, $dbname, $login, $password, $type)
{
	$remote_dbh = mysqli_connect($serverName, $login, $password, $dbname) or die("Could not connect to the server '".$serverName."<br/>");
	mysqli_query($remote_dbh, "SET NAMES utf8;");

	global $dbh;
	
	switch($type)
	{
		case "units":
			$sql = "SELECT * FROM `".units_db."` WHERE 1;";
			$result = mysqli_query($remote_dbh, $sql);
			if($result == false)
				throw new Exception("Cannot perform remote request\n".mysql_error());
			
			while($data = mysqli_fetch_object($result))
			{
				try
				{
					echo "<b>Importing code '".$data->code."' named '".$data->nickname."' of section ".$section."</b><br/>";
					$sql = "DELETE FROM ".units_db." WHERE `code`=\"".$data->code."\" AND section=\"".$section."\";";
					sql_request($sql);
					
					$sqlvalues = '"'.$section.'"';
					$sqlfields = "section";
					foreach($data as $field => $value)
					{
							$sqlfields .= ",`".$field."`";
							$sqlvalues .= ',"'.$value.'"';
					}
					$sql = "INSERT INTO ".units_db." ($sqlfields) VALUES ($sqlvalues);";
					sql_request($sql);
				}
				catch(Exception $e)
				{
					echo "Failed to import unit code '".$data->code."' named '".$data->nickname."' of section ".$section.":<br/>".$e->getMessage()."<br/>";
				}
				}
			break;
				case "users":
			$sql = "SELECT * FROM `".users_db."` WHERE 1;";
			$result = mysqli_query($remote_dbh, $sql);
			if($result == false)
				throw new Exception("Cannot perform remote request\n".mysql_error());
			$fields = array("login", "passHash", "description", "permissions", "email", "tel");
			unset($_SESSION['all_users']);
			while($data = mysqli_fetch_object($result))
			{
				try
				{
					echo "<b>Importing user '".$data->login." of section ".$section."'</b><br/>";
					if(existsUser($data->login))
						throw new Exception("Failed to create user: le login '".$data->login."' est déja utilisé.");
					$sqlvalues = '"'.$section.'"';
					$sqlfields = "sections";
					foreach($fields as $field)
					{
							$sqlfields .= ",`".$field."`";
							$sqlvalues .= ',"'.(isset($data->$field) ? mysqli_escape_string($dbh, $data->$field) : "" ).'"';
							$first = false;
					}
					$sql = "INSERT INTO ".users_db." ($sqlfields) VALUES ($sqlvalues);";
					sql_request($sql);
				}
				catch(Exception $e)
				{
					echo "Failed to import user '".$data->login."' of section ".$section.":<br/>".$e->getMessage()."<br/>";
				}
			}
			unset($_SESSION['all_users']);
			break;
		case "reports":
			$sql = "SELECT * FROM `".reports_db."` WHERE id=id_origine and statut!=\"supprime\";";
			$result = mysqli_query($remote_dbh, $sql);
			if($result == false)
				throw new Exception("Cannot perform remote request\n".mysql_error());
			
			global $fieldsRapportAll;
			$forbid = array("DU","international","finalisationHDR","national","id","id_origine");
			while($data = mysqli_fetch_object($result))
			{
				try
				{
					echo "<b>Importing report '".$data->nom." ".$data->prenom."' of section ".$section."</b><br/>";
					$sqlvalues = '"'.$section.'"';
					$sqlfields = "section";
					foreach($fieldsRapportAll as $field => $desc)
					{
						if($field != "fichiers" && $field != "" && isset($data->$field) && !in_array($field, $forbid))
						{
							$sqlfields .= ",`".$field."`";
							$sqlvalues .= ',"'.(isset($data->$field) ? mysqli_escape_string($dbh, $data->$field) : "" ).'"';
							$first = false;
						}
					}
			
					$sql = "INSERT INTO ".reports_db." ($sqlfields) VALUES ($sqlvalues);";
					sql_request($sql);
					
					$new_id = mysqli_insert_id($dbh);
					$sql = "UPDATE ".reports_db." SET id_origine=".intval($new_id)." WHERE id=".intval($new_id).";";
					sql_request($sql);
				}
				catch(Exception $e)
				{
					echo "Failed: ".$e->getMessage()."<br/>";
					rr();
				}
			}
			break;
		case "people":
			$sql = "SELECT * FROM `".people_db."` WHERE 1;";
			$result = mysqli_query($remote_dbh, $sql);
			if($result == false)
				throw new Exception("Cannot perform remote request\n".mysql_error());

			global $fieldsIndividualAll;
			while($data = mysqli_fetch_object($result))
			{
				try
				{
					echo "<b>Importing people '".$data->nom." ".$data->prenom."' of section ".$section."</b><br/>";
					$sqlvalues = "";
					$sqlfields = "";
					$first = true;
					foreach($fieldsIndividualAll as $field => $desc)
					{
						if($field != "fichiers" && $field != "" && isset($data->$field))
						{
							$sqlfields .= ($first ? "" : ",") ."`".$field."`";
							$sqlvalues .= ($first ? "" : ",") .'"'.(isset($data->$field) ? $data->$field : ( isset($empty_individual[$field]) ? $empty_individual[$field] : "") ).'"';
							$first = false;
						}
					}
					
						$sqlfields .= ",section";
						$sqlvalues .= ",".$section;
					
						$sql = "INSERT INTO ".people_db." ($sqlfields) VALUES ($sqlvalues);";
						sql_request($sql);
				}
				catch(Exception $e)
				{
					echo "Failed: ".$e->getMessage()."<br/>";
				}
			}
			break;
		case "sessions":
				$sql = "SELECT * FROM `".sessions_db."` WHERE 1;";
			$result = mysqli_query($remote_dbh, $sql);
			if($result == false)
				throw new Exception("Cannot perform remote request\n".mysql_error());
			while($row = mysqli_fetch_object($result))
			{
				try
				{
					if(strlen($row->id) > 4)
					{
					echo "<b>Importing session '".$row->id."' of section ".$section."</b><br/>";
						$year = substr($row->id, strlen($row->id) -4, 4);
					createSession($row->nom, $year,$section);
					}
					else
						echo "<b>Skipping session '".$row->id."' of section ".$section."</b><br/>";
						
				}
				catch(Exception $e)
				{
					echo "Failed to create session:<br/>".$e->getMessage()."<br/>";
				}
			}
			break;
			
	}
	mysqli_close($remote_dbh);
}

?>