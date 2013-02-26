

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

require_once("utils.inc.php");

require_once("db.inc.php");
require_once("manage_users.inc.php");


try
{
	$dbh = db_connect($servername,$dbname,$serverlogin,$serverpassword);
	$errorLogin = 0;
	$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";
	try
	{
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
		case 'adminnewsession':
			if (isset($_REQUEST["sessionname"]) and isset($_REQUEST["sessionannee"]))
			{
				$name = $_REQUEST["sessionname"];
				$annee = $_REQUEST["sessionannee"];
				require_once('manage_sessions.inc.php');
				createSession($name,$annee);
			}
			else
			{
				echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer une session, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
			}
			break;
		case 'sessioncourante':
			if(isset($_REQUEST["sessionname"]))
			{
				require_once("config.php");

				$id = $_REQUEST["sessionname"];
				set_config('current_session',$id);
				set_current_session_id($id);
			}
			break;

	}
	}
	catch(Exception $exc)
	{
	$text = 'Impossible d\'exécuter l\'action "'.$action.'"<br/>Exception: '.$exc->getMessage();
	echo "<p>".$text."</p>";
	}
	

	if (authenticate())
	{
		try
		{
			if(check_current_session_exists())
			{
				include("content.inc.php");
			}
			else
			{
				include("header.inc.php");
				$id = current_session_id();
				if(strlen($id) > 0)
				{
				echo "<p>La session courante intitulée '".$id."' n'existe pas dans la base de données<br/>";
				echo "<p>Veuillez créer une session intitulée '".$id."' ou changer de session courante</p>";
				}
				else
				{
				echo "<p>Aucune session courante n'est configurée<br/>";
					echo "Veuillez créer et ou sélectionner la session courante</p>";
				}
				
				?>
<div class="large">
	<div class="content">
		<?php 
		include("sessions_manager.php");
		include("config_manager.php");
		?>
	</div>
</div>
<?php 
			}
		}
		catch(Exception $exc)
		{
			echo '<p>Erreur: '.$exc.'</p>';	
		}
	}
	else
	{
		//create the admin/admin initial password if needed
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
