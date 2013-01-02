
<div class="left">

	<!-- 
	<div class="header">
		<h2><span>Comité National de la Recherche Scientifique</span></h2>
		<h1>Interface de saisie des prérapports</h1>
	</div>
 -->
	<div class="content">

		<?php 
		require_once('manage_sessions.inc.php');
		require_once('manage_unites.inc.php');
		require_once('manage_rapports.inc.php');
		require_once('db.inc.php');

		$id_rapport = isset($_REQUEST["id"]) ? $_REQUEST["id"] : "";
		$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";

		try
		{
			switch($action)
			{
				case 'delete':
					echo deleteReport($id_rapport);

				case 'change_statut':
					if(isset($_REQUEST["new_statut"]))
					{
						$new_statut =  $_REQUEST["new_statut"];
						$statut = isset($_REQUEST["statut"]) ? $_REQUEST["statut"] : "";
						$id_session = isset($_REQUEST["id_session"]) ? $_REQUEST["id_session"] : -1;
						$type_eval = isset($_REQUEST["type_eval"]) ? $_REQUEST["type_eval"] : "";
						$login_rapp = isset($_REQUEST["login_rapp"]) ? $_REQUEST["login_rapp"] : "";
						$sort_crit = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "";
						change_statuts($new_statut, $statut, $id_session,$type_eval,$login_rapp);
						displaySummary($new_statut, $id_session,$type_eval,$sort_crit,$login_rapp);
					}
					break;
				case 'view':
					$statut = isset($_REQUEST["statut"]) ? $_REQUEST["statut"] : "";
					$id_session = isset($_REQUEST["id_session"]) ? $_REQUEST["id_session"] : -1;
					$type_eval = isset($_REQUEST["type_eval"]) ? $_REQUEST["type_eval"] : "";
					$login_rapp = isset($_REQUEST["login_rapp"]) ? $_REQUEST["login_rapp"] : "";
					$sort_crit = isset($_REQUEST["sort"]) ? $_REQUEST["sort"] : "";
					displaySummary($statut, $id_session,$type_eval,$sort_crit,$login_rapp);
					break;
				case 'details':
					displayReport($id_rapport);
					break;
				case 'edit':
					editReport($id_rapport);
					break;
				case 'history':
					if (isset($_REQUEST["id_origine"]))
					{
						$id_origine = $_REQUEST["id_origine"];
						historyReport($id_origine);
					}
					break;
				case 'update':
					if (isset($_REQUEST["id_origine"]))
					{
						$id_origine = $_REQUEST["id_origine"];
						$id_nouveau = update($id_origine, $_REQUEST);
						if($id_nouveau == false)
							echo "Pas de rapport créé.<br/>";
						else
							displayReport($id_nouveau);
					}
					else
					{
						echo "Update action cannot do nothing because no id_origine provided";
					}
					break;
				case 'new':
					if (isset($_REQUEST["type_eval"]))
					{
						$type_eval = $_REQUEST["type_eval"];
						newReport($type_eval);
					}
					else
					{
						echo "Cannot create new document because no type_eval provided";
					}
					break;
				case 'add':
					$id_nouveau = addReport($_REQUEST);
					displayReport($id_nouveau);
					break;
				case'newpwd':
				case 'adminnewpwd':
					if (isset($_REQUEST["oldpwd"]) and isset($_REQUEST["newpwd1"]) and isset($_REQUEST["newpwd2"]) and isset($_REQUEST["login"]))
					{
						$old = $_REQUEST["oldpwd"];
						$pwd1 = $_REQUEST["newpwd1"];
						$pwd2 = $_REQUEST["newpwd2"];
						$login = $_REQUEST["login"];
						if (($pwd1==$pwd2))
						{
							if (changePwd($login,$old,$pwd1,$pwd2))
							{
								echo "<p><strong>Mot de passe modifié avec succès.</strong></p>";
								addCredentials($_SESSION["login"],$pwd1);
							}
						}
						else
						{
							echo "<p><strong>Erreur :</strong> Les deux saisies du nouveau mot de passe  diffèrent, veuillez réessayer.</p>";
						}
					}
					else
					{
						echo "<p><strong>Erreur :</strong> Vous n'avez fourni les informations nécessaires pour modifier votre mot de passe, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
					}
					break;
				case "newpwd":
					include "changePwd.inc.php";
					break;
				case "adminnewpwd":
					if(isSuperUser())
						include 'admin.inc.php';
					break;
				case 'exportdb':
					$dbname = isset($_REQUEST['dbname']) ? $_REQUEST['dbname'] : "";
					echo export_db($dbname) . "<br/>";
					break;
				case 'importdb':
					$dbname = isset($_REQUEST['dbname']) ? $_REQUEST['dbname'] : "";
					echo import_db($dbname) . "<br/>";
					break;
				case 'admin':
					if (isSecretaire())
						include "admin.inc.php";
					else
						echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
					break;
				case 'admindeleteaccount':
					if (isSuperUser())
					{
						if (isset($_REQUEST["login"]))
						{
							$login = $_REQUEST["login"];
							deleteUser($login);
						}
						else
						{
							echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer un utilisateur, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
						}
						include "admin.inc.php";
					}
					else
					{
						echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
					}
				case 'adminnewpermissions':
					if (isSuperUser())
					{
						if (isset($_REQUEST["login"]) and isset($_REQUEST["permissions"]))
						{
							$login = $_REQUEST["login"];
							$permissions = $_REQUEST["permissions"];
							changeUserPermissions($login,$permissions);
						}
						else
						{
							echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour modifier les droits de cet utilisateur, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
						}
						include "admin.inc.php";
					}
					else
					{
						echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
					}
					break;
				case 'adminnewaccount':
					if (isSuperUser())
					{
						if (isset($_REQUEST["envoiparemail"]) and isset($_REQUEST["email"]) and isset($_REQUEST["description"]) and isset($_REQUEST["newpwd1"]) and isset($_REQUEST["newpwd2"]) and isset($_REQUEST["login"]))
						{
							$desc = $_REQUEST["description"];
							$pwd1 = $_REQUEST["newpwd1"];
							$pwd2 = $_REQUEST["newpwd2"];
							$login = $_REQUEST["login"];
							$email = $_REQUEST["email"];
							$envoiparemail = $_REQUEST["envoiparemail"];

							if (($pwd1==$pwd2))
								echo "<p><strong>".createUser($login,$pwd2,$desc, $email, $envoiparemail)."</p></strong>";
							else
								echo "<p><strong>Erreur :</strong> Les deux saisies du nouveau mot de passe  diffèrent, veuillez réessayer.</p>";
						}
						else
						{
							echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer un utilisateur, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
						}
						include "admin.inc.php";
					}
					else
					{
						echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
					}
					break;
				case 'adminnewsession':
					if (isSuperUser())
					{
						if (isset($_REQUEST["sessionname"]) and isset($_REQUEST["sessiondate"]))
						{
							$name = $_REQUEST["sessionname"];
							$date = $_REQUEST["sessiondate"];
							createSession($name,$date);
						}
						else
						{
							echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer une session, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
						}
						include "admin.inc.php";
					}
					else
					{
						echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
					}
					break;
				case 'admindeletesession':
					if (isSuperUser())
					{
						if (isset($_REQUEST["sessionid"]))
						{
							$id = $_REQUEST["sessionid"];
							deleteSession($id);
						}
						else
						{
							echo "<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour supprimer une session, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
						}
						include "admin.inc.php";
					}
					else
					{
						echo "<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
					}
					break;
				case 'changepwd':
					include "changePwd.inc.php";
					break;
				case 'ajoutlabo':
					if(isset($_REQUEST["nickname"]) and isset($_REQUEST["code"]) and isset($_REQUEST["fullname"]) and isset($_REQUEST["directeur"]))
					{
						$result = addUnit($_REQUEST["nickname"], $_REQUEST["code"], $_REQUEST["fullname"], $_REQUEST["directeur"]);
						if($result == false)
							echo "Failed to add unit \"".$_REQUEST["nickname"]."\"<br/>";
						else
							echo "Added unit \"".$_REQUEST["nickname"]."\"<br/>";
					}
					else
					{
						echo "Cannot process action ajoutlabo: missing data<br/>";
					}
					break;
				case "";
				break;
				default:
					echo 'Unknown action "'.$action.'"<br/>';
					break;
			}
		}
		catch(Exception $exc)
		{
			echo '<p><b>Impossible d exécuter l action "'.$action.'"<br/>Exception: '.$exc->getMessage().'<br/></b></p>';
		}
		?>
		<p>Bienvenue sur le site de gestion des rapports de la section 6.</p>
		<p>
			Le <b>menu situé à droite de cette page</b> vous permettra de
			consulter ou éditer des rapports.
		</p>
		<p>N'hésitez pas à nous contacter (Yann ou Hugo) en cas de
			difficultés.</p>
	</div>
</div>
