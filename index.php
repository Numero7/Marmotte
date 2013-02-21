

	<?php

	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	ini_set('xdebug.collect_vars', 'on');
	ini_set('xdebug.collect_params', '4');
	ini_set('xdebug.dump_globals', 'on');
	ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
	ini_set('xdebug.show_local_vars', 'on');
	
	require_once("utils.inc.php");
	require_once("db.inc.php");
	require_once("manage_users.inc.php");


	try
	{
		$dbh = db_connect($servername,$dbname,$serverlogin,$serverpassword);
		$errorLogin = 0;
		$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";
		switch($action)
		{
			case 'logout':
				removeCredentials();
				break;
					
			case 'auth':
				if(isset($_REQUEST["login"]) and isset($_REQUEST["password"]))
				{
					$login =  $_REQUEST["login"];
					$pwd =  $_REQUEST["password"];
					addCredentials($login,$pwd);
					if (!authenticate())
					{
						$errorLogin = 1;
					}
					else
					{
						init_session();
					}
				}
				break;
		}

		if (authenticate())
		{
			include("content.inc.php");
		}
		else
		{
			include("header.inc.php");
			include("authenticate.inc.php");
		}
		db_disconnect($dbh);
	}
	catch(Exception $e)
	{
		include("header.inc.php");
		echo $e->getMessage();
	}
	?>
</body>
</html>
