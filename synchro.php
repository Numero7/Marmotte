<?php
require_once("db.inc.php");
require_once('authenticate_tools.inc.php');

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.collect_vars', 'on');
ini_set('xdebug.collect_params', '4');
ini_set('xdebug.dump_globals', 'off');
ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');
ini_set('xdebug.halt_level', E_WARNING|E_NOTICE|E_USER_WARNING|E_USER_NOTICE);

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
        authenticate();
        init_filter_session();

//        $_SESSION['filter_section'] = '0';

require_once('synchro.inc.php');
$email = false;
synchronize_with_evaluation("",true,$email);

	db_disconnect();
?>