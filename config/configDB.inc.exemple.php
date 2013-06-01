<?php

	set_error_handler("error_handler");
	set_exception_handler("exception_handler");
	
	$servername = "127.0.0.1";
	$dbname = "marmot";
	$serverlogin = "root";
	$serverpassword = "";
	
	$rootdir = "";
	
	define("reports_db","reports");
	define("users_db","users");
	define("sessions_db","sessions");
	define("units_db","units");
	define("people_db","people");
	
?>