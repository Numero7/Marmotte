<?php

	set_error_handler("error_handler");
	set_exception_handler("exception_handler");
	
	$servername = "127.0.0.1";
	$dbname = "marmot";
	$serverlogin = "root";
	$serverpassword = "";
	
	$rootdir = "";
	
	define("evaluations_db","evaluations");
	define("users_db","users");
	define("sessions_db","sessions");
	define("units_db","units");
	define("candidates_db","candidats");
	
?>