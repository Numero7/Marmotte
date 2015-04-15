<?php

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('xdebug.collect_vars', 'on');
ini_set('xdebug.collect_params', '4');
ini_set('xdebug.dump_globals', 'on');
ini_set('xdebug.dump.SERVER', 'REQUEST_URI');
ini_set('xdebug.show_local_vars', 'on');

ini_set("session.gc_maxlifetime", 3600);
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
	
	global $dbh;
	if($dbh)
	{
		if(!isset($_SESSION['checked_admin_password']))
		{
			createAdminPasswordIfNeeded();
			$_SESSION['checked_admin_password'] = true;
		}
		/*
		if(authenticateBase('admin','password'))
			echo "The 'admin' password is 'password', please change it right after login.";
			*/
		
		$action = isset($_REQUEST["action"]) ? mysqli_real_escape_string($dbh, $_REQUEST["action"]) : "";
		$errorLogin = 0;
		if($action == "auth_marmotte")
		{
			if(isset($_REQUEST["login"]) and isset($_REQUEST["password"]))
			{
				$login =  mysqli_real_escape_string($dbh, $_REQUEST["login"]);
				$pwd =  mysqli_real_escape_string($dbh, $_REQUEST["password"]);
				addCredentials($login,$pwd);
			}
		}

		if($action == "auth_janus" && ( !isset($_SERVER['REMOTE_USER'])  || $_SERVER['REMOTE_USER'] =="" ) )
		{			
			
			require_once("PMSP/Pmsp.php");
			try {
				# Fabrique un objet PMSP
				$pmsp = new Pmsp(
						"https://vigny.dr15.cnrs.fr/secure/pmsp-server.php",
						"/home/gimbert/Panda/PMSP/pmsp.pub",
						"Marmotte",
						"http://127.0.0.1/index.php?action=auth_janus",
						false);
			# Effectue l'authentification
			$pmsp->authentify('mail,cn,ou,givenname,displayname');		

			if(isset($_SERVER['REMOTE_USER']) && ($_SERVER['REMOTE_USER'] != ''))
			{
				echo "Adding credentials for user " + $_SERVER['REMOTE_USER'];
				addCredentials($_SERVER['REMOTE_USER'], "",true);
			}
			
			} catch (Exception $e) {
				removeCredentials();
				Header("Content-type: text/plain");
				echo $e->getMessage();
				echo "\n";
				echo $e->getTraceAsString();
				exit (0);
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
			require_once("config_tools.inc.php");
			$_SESSION['filter_id_session'] = get_config("current_session");
			
			require_once("utils.inc.php");
			require_once("manage_users.inc.php");
			if(isSecretaire() && !isset($_SESSION["htpasswd"]))
			{
				createhtpasswd();
				$_SESSION["htpasswd"] = "done";
			}
				
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
				case 'change_role':
					$role = isset($_REQUEST["role"]) ? $_REQUEST["role"] : 0;
					$role = min( $role, getUserPermissionLevel("",false));
					$_SESSION["permission_mask"] = $role;
				break;
					
			}

		try{
			/* should not be here but ... */
			if(isset($_REQUEST['filter_section']))
				change_current_section($_REQUEST['filter_section']);
			
			$id = current_session_id();
			
			if($id == "" && !isSuperUser())
			{
				echo "<p>Aucune session courante n'est configurée, veuillez créer une session via le menu Admin/Sessions<br/>";
			}
			else
			{
				if(!check_current_session_exists() && !isSuperUser() && isSecretaire())
				{
					echo "<p>La session courante intitulée '".$id."' n'existe pas dans la base de données<br/>";
					echo "<p>Veuillez créer une session intitulée '".$id."' ou changer de session courante</p>";
				}
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
	removeCredentials();
	include("header.inc.php");
	echo $e->getMessage();
	include("index.php");
}
?>
</body>
</html>
