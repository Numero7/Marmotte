<?php
	
	$servername = "127.0.0.1";
	$dbname = "panda";
	$serverlogin = "monlogin";
	$serverpassword = "monmotdepasse";
	
	$rootdir = "";
	$rootdir = "";
        $dsirootdir = "evaluation";

	$tmpdir = "";
	
define("marmottedbname","panda");

define("dsidbname", "dsi");
define("dsi_users_db","membres");
define("dsi_docs_db","documents");
define("dsi_docs_liens_db","liendocagent");
define("dsi_docs_liens_unites_db","liendocunite");
define("dsi_people_db","chercheurs");
define("dsi_units_db","unites");
define("dsi_evaluation_db","evaluation");
define("dsi_evaluation_units_db","eval_unites");	
define("dsi_marmotte_db","marmotte");

	define("reports_db","reports");
	define("users_db","users");
	define("sessions_db","sessions");
	define("units_db","units");
	define("people_db","people");
	define("config_db","config");
	define("concours_db","concours");
	define("adresse_du_site","https://marmotte.cnrs.fr");
        define("certif_janus","/home/gimbert/Panda/PMSP/pmsp.pub");
define("debug",true);

if(debug)
  {	
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.collect_vars', 'on');
ini_set('xdebug.collect_params', '4');
ini_set('xdebug.dump_globals', 'off');
ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');
ini_set('xdebug.halt_level', E_WARNING|E_NOTICE|E_USER_WARNING|E_USER_NOTICE);
  }
?>
