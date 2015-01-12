<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.collect_vars', 'on');
ini_set('xdebug.collect_params', '4');
ini_set('xdebug.dump_globals', 'on');
ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');

session_start();

require_once("db.inc.php");
require_once('authenticate_tools.inc.php');

try
{
	try
	{		
		db_connect($servername,$dbname,$serverlogin,$serverpassword);		
	}
	catch(Exception $e)
	{
		include("header.inc.php");
		echo "<h1>Failed to connect to database: ".$e."</h1>";
		db_from_scratch();
	}
	
	
	if($dbh)
	{

		//create the admin/admin initial password if needed
		createAdminPasswordIfNeeded();
		if(authenticateBase('admin','password'))
			echo "The 'admin' password is 'password', please change it right after login.";
		
		$action = isset($_REQUEST["action"]) ? mysqli_real_escape_string($dbh, $_REQUEST["action"]) : "";

		$errorLogin = 0;
		
		if($action == "auth")
		{
			if(isset($_REQUEST["login"]) and isset($_REQUEST["password"]))
			{
				$login =  mysqli_real_escape_string($dbh, $_REQUEST["login"]);
				$pwd =  mysqli_real_escape_string($dbh, $_REQUEST["password"]);
				addCredentials($login,$pwd);
				if (!authenticate())
				{
					$errorLogin = 1;
				}
				else
				{
					require_once("config_tools.inc.php");
					$_SESSION['filter_id_session'] = get_config("current_session");
					ini_set("session.gc_maxlifetime", 3600);
				}
			}
		}
		
		
		if(!authenticate() || $action == 'logout' || ($errorLogin == 1))
		{
			removeCredentials();
			include("header.inc.php");
			include("authenticate.inc.php");
		}
		else
		{			
			require_once("utils.inc.php");
			require_once("manage_users.inc.php");

			
			switch($action)
			{
				case 'adminnewsession':
					if (isset($_REQUEST["sessionname"]) and isset($_REQUEST["sessionannee"]))
					{						
						$name = real_escape_string($_REQUEST["sessionname"]);
						$annee = real_escape_string($_REQUEST["sessionannee"]);
						require_once('manage_sessions.inc.php');
						createSession($name,$annee);
						$_REQUEST["action"] = 'admin';
					}
					else
						echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer une session, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
					break;
				case 'sessioncourante':
					if(isset($_REQUEST["sessionname"]))
					{
						require_once('config_tools.inc.php');
						$id = real_escape_string($_REQUEST["sessionname"]);
						set_config('current_session',$id);
						set_current_session_id($id);
						$_REQUEST["action"] = 'admin';
					}
					break;
			}

		try{
			/* should not be here but ... */
			if(isset($_REQUEST['filter_section']))
				change_current_section($_REQUEST['filter_section']);
			
			$id = current_session_id();
			
				if(!check_current_session_exists() && !isSuperUser())
				{
					echo "<p>La session courante intitulée '".$id."' n'existe pas dans la base de données<br/>";
					echo "<p>Veuillez créer une session intitulée '".$id."' ou changer de session courante</p>";
				}
					include("content.inc.php");
			}
			catch(Exception $exc)
			{
				echo '<p>Erreur: '.$exc.'</p>';
			}
		}
	db_disconnect();
	}
}
catch(Exception $e)
{
	include("header.inc.php");
	echo $e->getMessage();
}
?>
</body>
</html>
