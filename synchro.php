<?php
require_once("db.inc.php");
require_once('authenticate_tools.inc.php');
require_once('synchro.inc.php');

ini_set("session.gc_maxlifetime", 3600);
session_start();

try
{
	db_connect($servername,marmottedbname,$serverlogin,$serverpassword);
}
catch(Exception $e)
{
	echo "Failed to connect to database: ".$e."</h1>";
	die(0);
}

	addCredentials("admin", "",true);
	$_SESSION['REMOTE_USER'] = "admin";
	
	echo synchronize_with_evaluation();

	db_disconnect();
?>