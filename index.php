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

		$action = isset($_REQUEST["action"]) ? mysqli_real_escape_string($dbh, $_REQUEST["action"]) : "";
		$errorLogin = 0;
		$errorMsg = "";
		if($action == "auth_marmotte")
		{
			if(isset($_REQUEST["login"]) and isset($_REQUEST["password"]))
			{
				$login =  mysqli_real_escape_string($dbh, $_REQUEST["login"]);
				$pwd =  mysqli_real_escape_string($dbh, $_REQUEST["password"]);
				addCredentials($login,$pwd);
				if(!authenticate())
				{
					$errorLogin = 1;
					$errorMsg = "Mauvaise paire login/mot de passe";
				}
			}
		}

		if($action == "auth_janus")
		{
			if(empty($_SESSION['pmsp_client_random']) && !empty($_POST['pmsp_server_signature']))
				unset($_POST['pmsp_server_signature']);
			require_once("PMSP/Pmsp.php");
			try {
				# Fabrique un objet PMSP
				$pmsp = new Pmsp(
						"https://vigny.dr15.cnrs.fr/secure/pmsp-server.php",
						"/home/gimbert/Panda/PMSP/pmsp.pub",
						"Marmotte",
						adresse_du_site."/index.php?action=auth_janus",
						false);
			# Effectue l'authentification
			$pmsp->authentify('mail,cn,ou,givenname,displayname');
			$_SESSION['REMOTE_USER'] = $_SERVER['REMOTE_USER'];
			} catch (Exception $e) {
				removeCredentials();
				$errorLogin = 1;
				$errorMsg  = $e->getMessage();
			}
		}

		if(isset($_SESSION['REMOTE_USER']) && ($_SESSION['REMOTE_USER'] != ''))
			addCredentials($_SESSION['REMOTE_USER'], "",true);

		if(!authenticate() || $action == 'logout' || ($errorLogin == 1))
		{
			removeCredentials();
			include("header.inc.php");
			include("authenticate.inc.php");
			if($errorLogin)
				echo "<p><alert>".$errorMsg."</alert></p></br>";
		}
		else
		{
			require_once("config_tools.inc.php");
			if(!isset($_SESSION['filter_id_session']))
			{
				$ok = $_SESSION['filter_section'];
				$id = get_config("current_session");
				$sql = "SELECT * FROM ".sessions_db." WHERE `id`='".$id."' AND `section`='". real_escape_string($ok)."' ORDER BY date DESC;";
				$result = sql_request($sql);
				if(mysqli_num_rows($result) == 0)
				{
					$sql = "SELECT * FROM ".sessions_db." WHERE `section`='". real_escape_string($ok)."' ORDER BY date DESC;";
					$result = sql_request($sql);
					if(mysqli_num_rows($result) == 0)
					{
						$sql = "SELECT * FROM ".sessions_db." WHERE `section`='0' ORDER BY date DESC;";
						$result = sql_request($sql);
						if(mysqli_num_rows($result) == 0)
						{
							createSession("Printemps", "2015", 0);
							$result = sql_request($sql);
						}
						if(	$row = mysqli_fetch_object($result))
						{
							createSession($row->nom, date('Y', strtotime($row->date)), $ok);
							$sql = "SELECT * FROM ".sessions_db." WHERE `section`='". real_escape_string($ok)."' ORDER BY date DESC;";
							$result = sql_request($sql);
							$row = mysqli_fetch_object($result);
							set_config("current_session", $row->id);
							$id = get_config("current_session");
						}
						else
							rr();
					}
					$id = $row->id;
				}
				$_SESSION['filter_id_session'] = $id;
			}
				
				
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
						$id = $_REQUEST["sessionname"];
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
	//Header("Content-type: text/plain");
	echo "<html><head><script>alert(\"".$e->getMessage()."\");";
	echo "window.location = \"index.php\";";
	echo "</script></head></body></html>";

	//	Header("Content-type: text/plain");
	//	include("index.php");
}
?>
</body>
</html>
