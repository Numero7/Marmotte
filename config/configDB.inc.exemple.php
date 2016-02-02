<?php
	
	$servername = "127.0.0.1";

	
	$rootdir = "";
	$rootdir = "";
        $dsirootdir = "evaluation";

	$tmpdir = "";
	
define("marmottedbname","panda");
$serverlogin = "monlogin";
$serverpassword = "monmotdepasse";


//path to the dir where storage/ is created 
	$rootdir = "";

//path to the dir containing dsi files
        $dsirootdir = "evaluation";

//path to the dir where tmp/ is created 
	$tmpdir = "";

//senders of emails
define("email_sgcn","laurent.chazaly@cnrs-dir.fr");
define("email_admin","Rene.PELFRESNE@dsi.cnrs.fr");


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


define("dsi_GOC","GOC_CC_concours");
define("dsi_rapp_conc","INTER_CC_emailpro_numconc");
define("celcc_candidats","CEL_CC_candidat");
define("celcc_candidatures","CEL_CC_candidatures");
define("celcc_docs","CEL_CC_documents");
define("celcc_statuts","INTER_CC_statuts_candidatures");


	define("reports_db","reports");
	define("users_db","users");
	define("sessions_db","sessions");
	define("units_db","units");
	define("people_db","people");
	define("config_db","config");
	define("concours_db","concours");
	define("adresse_du_site","https://marmotte.cnrs.fr");
        define("certif_janus","/home/gimbert/Panda/PMSP/pmsp.pub");

define("debug",false);
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
