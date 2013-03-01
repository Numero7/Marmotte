<?php
require_once('config/configDB.inc.php');

function db_from_scratch()
{
	$msg = "Please properly configure the databse access in the file config/configDB.inc.php<br/>";
	$msg .= "and/or initialize the database with <h1><a href=\"marmotte.sql\">this SQL script</a></h1></br></br></br>.";
}

function db_connect($serverName,$dbname,$login,$password)
{
	$dbh = @mysql_connect($serverName, $login, $password);
	
	if(!$dbh)
		throw new Exception("Could not connect to the server '".$serverName."<br/>".mysql_error());

	if(!@mysql_select_db($dbname, $dbh))
		throw new Exception("Could not connect to the database '".$dbname."<br/>".mysql_error());
		
	mysql_query("SET NAMES utf8;");
	
	$databases = array(reports_db, users_db, sessions_db, units_db, people_db);
	foreach($databases as $database)	
	{
		$result = mysql_query("SHOW TABLES LIKE '$database'");
		if($result == false)
			throw new Exception("Cannot count the number of databases with name ".$database."<br/>".mysql_error());
		if(mysql_fetch_array($result) === false)
			throw new Exception("No database with name ".$database);
	}
		
	return $dbh;
} ;

function db_disconnect(&$dbh)
{
	mysql_close($dbh);
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
?>