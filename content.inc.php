
<script type="text/javascript">
function alertSize() {
	var myWidth = 0, myHeight = 0;
	if( typeof( window.innerWidth ) == 'number' ) {
		myWidth = window.innerWidth; myHeight = window.innerHeight;
	} else if( document.documentElement && ( document.documentElement.clientWidth ||document.documentElement.clientHeight ) ) {
		myWidth = document.documentElement.clientWidth; myHeight = document.documentElement.clientHeight;
	} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
		myWidth = document.body.clientWidth; myHeight = document.body.clientHeight;
	}
	window.alert( 'Width = ' + myWidth + ' and height = ' + myHeight );
}
function getScrollXY() {
	var scrOfX = 0, scrOfY = 0;
	if( typeof( window.pageYOffset ) == 'number' ) {
		scrOfY = window.pageYOffset; scrOfX = window.pageXOffset;
	} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
		scrOfY = document.body.scrollTop; scrOfX = document.body.scrollLeft;
	} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
		scrOfY = document.documentElement.scrollTop; scrOfX = document.documentElement.scrollLeft;
	}
	window.alert( 'Horizontal scrolling = ' + scrOfX + '\nVertical scrolling = ' + scrOfY );
}

</script>

<div class="large">

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
		require_once('manage_candidates.inc.php');
		require_once('db.inc.php');

				
		$id_rapport = isset($_REQUEST["id"]) ? $_REQUEST["id"] : -1;
		$id_origine = isset($_REQUEST["id_origine"]) ? $_REQUEST["id_origine"] : 0;
		$id_toupdate = isset($_REQUEST["id_toupdate"]) ? $_REQUEST["id_toupdate"] : 0;

		$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";

		if(isset($_REQUEST["reset_filter"]))
			resetFilterValuesExceptSession();

		if(isset($_REQUEST["reset_tri"]))
			resetOrder();

		function displayReports($centralid = 0)
		{
			displaySummary(getCurrentFiltersList(), getFilterValues(), getSortingValues());
			echo('
					<script type="text/javascript">
					document.getElementById("t'.$centralid.'").scrollIntoView();
					</script>');

		};

		try
		{
			switch($action)
			{
				case 'delete':
					$next = nextt($id_rapport);
					$before = deleteReport($id_rapport);
					echo "<p>Deleted report ".$id_rapport."</p>\n";
					unset($_REQUEST['id']);
					unset($_REQUEST['id_origine']);
					displayReports( ($before != -1) ? $before : $next);
					break;

				case 'change_statut':
					if(isset($_REQUEST["new_statut"]))
					{
						$filterValues = getFilterValues();
						$new_statut =  $_REQUEST["new_statut"];
						change_statuts($new_statut, $filterValues);
						$filterValues['statut']	 = $new_statut;
						displaySummary(getCurrentFiltersList(), $filterValues, getSortingValues());
					}
					break;
				case 'view':
					displayReports();
					break;
				case 'details':
					if(isset($_REQUEST["detailsnext"]))
					{
						displayReport(nextt($id_rapport));
					}
					else if(isset($_REQUEST["detailsprevious"]))
					{
						displayReport(previouss($id_rapport));
					}
					else if(isset($_REQUEST["retourliste"]))
					{
						displayReports();
					}
					else
					{
						displayReport($id_rapport);
					}
					break;
				case 'edit':
					editReport($id_rapport);
					break;
				case 'history':
					historyReport($id_origine);
					break;
				case 'update':

					$next = nextt($id_origine);
					$previous = previouss($id_origine);


					if(isset($_REQUEST["editnext"]))
					{
						//Hugo: tant qu'on est en dev je préfère laisser les exceptions remonter jusqu'à
						//l'utilisateur/testeur
						//mais je vosis l'idée j'ai modifié editReport en conséquence
						//try{editReport(nextt($id_origine));}
						//catch(Exception $e)
						//{displayReport(nextt($id_origine));}
						editReport($next);
					}
					else if(isset($_REQUEST["editprevious"]))
					{
						//Hugo: tant qu'on est en dev je préfère laisser les exceptions remonter jusqu'à
						//l'utilisateur/testeur
						//try{editReport(previouss($id_origine));}
						//catch(Exception $e)
						//{displayReport(previouss($id_origine));}
						editReport($previous);
					}
					else if(isset($_REQUEST["retourliste"]))
					{
						unset($_REQUEST["id_origine"]);
						unset($_REQUEST["id"]);
						displayReports($id_origine);
					}
					else if(isset($_REQUEST["deleteandeditnext"]))
					{
						$before = deleteReport($id_origine);
						if($before != -1)
							editReport($before);
						else if($next != -1)
							editReport($next);
						else
							displayReports();
					}
					else if(isset($_REQUEST['ajoutfichier']))
					{
						include("upload.inc.php");
						editReport($id_origine);
					}
					else
					{
						$done = false;
						
						foreach($concours_ouverts as $concours => $nom)
						{
							if(isset($_REQUEST['importconcours'.$concours]))
							{
								$done = true;
								$newreport = update_report_from_concours($id_origine,$concours, getLogin());
								editReport($newreport->id);
								break;
									
							}
						}


						if(!$done)
						{
							$report = addReportFromRequest($id_origine,$_REQUEST);

							if(isset($_REQUEST["submitandeditnext"]))
							{
								editReport($next);
							}
							else if(isset($_REQUEST["submitandview"]))
							{
								displayReport($report->id);
							}
							else if(isset($_REQUEST["submitandkeepediting"]))
							{
								editReport($report->id);
							}
						}
					}

					break;
				case 'new':
					if (isset($_REQUEST["type"]))
					{
						$type = $_REQUEST["type"];
						$report = newReport($type);
						displayEditableReport($report,isReportEditable($report));
					}
					else
					{
						throw new Exception("Cannot create new document because no type_eval provided");
					}
					break;
				case'newpwd':
				case 'adminnewpwd':
					if (isset($_REQUEST["oldpwd"]) and isset($_REQUEST["newpwd1"]) and isset($_REQUEST["newpwd2"]) and isset($_REQUEST["login"]))
					{
						$old = $_REQUEST["oldpwd"];
						$pwd1 = $_REQUEST["newpwd1"];
						$pwd2 = $_REQUEST["newpwd2"];
						$login = $_REQUEST["login"];
						$envoiparemail = isset($_REQUEST["envoiparemail"])  ? $_REQUEST["envoiparemail"] : false;

						if (($pwd1==$pwd2))
						{
							if (changePwd($login,$old,$pwd1,$pwd2,$envoiparemail))
								echo "<p><strong>Mot de passe modifié avec succès.</strong></p>";
						}
						else
							throw new Exception("Erreur :</strong> Les deux saisies du nouveau mot de passe  diffèrent, veuillez réessayer.</p>");
					}
					else
						throw new Exception("Erreur :</strong> Vous n'avez fourni les informations nécessaires pour modifier votre mot de passe, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>");
					include 'admin.inc.php';

					break;
				case 'admin':
					if (isSecretaire())
						include "admin.inc.php";
					else
						throw new Exception("<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>");
					break;
				case 'admindeleteaccount':
					if (isSecretaire())
					{
						if (isset($_REQUEST["login"]))
						{
							$login = $_REQUEST["login"];
							deleteUser($login);
							include "admin.inc.php";
						}
						else
							throw new Exception("<p><strong>Erreur :</strong> Vous n'avez fourni toutes les informations nécessaires pour créer un utilisateur, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>");
					}
					else
						throw new Exception("<p>Vous n'avez pas les droits nécessaires pour effectuer cette action, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>");
				case 'adminnewpermissions':
					if (isSecretaire())
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
				case 'checkpwd':
					if(isset($_REQUEST["password"]))
					{
						$password = $_REQUEST["password"];
						checkPasswords($password);
					}
					break;
				case 'adminnewaccount':
					if (isSecretaire())
					{
						if (isset($_REQUEST["email"]) and isset($_REQUEST["description"]) and isset($_REQUEST["newpwd1"]) and isset($_REQUEST["newpwd2"]) and isset($_REQUEST["login"]))
						{
							$desc = $_REQUEST["description"];
							$pwd1 = $_REQUEST["newpwd1"];
							$pwd2 = $_REQUEST["newpwd2"];
							$login = $_REQUEST["login"];
							$email = $_REQUEST["email"];
							$envoiparemail = isset($_REQUEST["envoiparemail"])  ? $_REQUEST["envoiparemail"] : false;

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
					break;
				case 'admindeletesession':
					if (isset($_REQUEST["sessionid"]))
						deleteSession($_REQUEST["sessionid"]);
					else
						throw new Exception("Vous n'avez fourni toutes les informations nécessaires pour supprimer une session, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.");
					include "admin.inc.php";
					break;
				case 'changepwd':
					include "changePwd.inc.php";
					break;
				case 'ajoutlabo':
					if(isset($_REQUEST["nickname"]) and isset($_REQUEST["code"]) and isset($_REQUEST["fullname"]) and isset($_REQUEST["directeur"]))
					{
						addUnit($_REQUEST["nickname"], $_REQUEST["code"], $_REQUEST["fullname"], $_REQUEST["directeur"]);
						echo "Added unit \"".$_REQUEST["nickname"]."\"<br/>";
					}
					else
					{
						echo "Cannot process action ajoutlabo: missing data<br/>";
					}
					include "admin.inc.php";
					break;
				case 'deletelabo':
					if(isset($_REQUEST["unite"]))
					{
						deleteUnit($_REQUEST["unite"]);
						echo "Deleted unit \"".$_REQUEST["unite"]."\"<br/>";
					}
					else
					{
						echo "Cannot process action ajoutlabo: missing data<br/>";
					}
					include "admin.inc.php";
					break;
				case 'sqlrequest':
					if(isset($_REQUEST['formula']))
					{
						sql_request($_REQUEST['formula']);
						echo "Successfully processed <br/>".$_REQUEST['formula'];
					}
					else
					{
						echo "Empty formula";
					}
					include "admin.inc.php";
					break;
				case 'mailing':
				case 'email_rapporteurs':
					include 'mailing.inc.php';
					break;
				case 'createhtpasswd':
					createhtpasswd();
					displayReports();
					include "admin.inc.php";
					break;
				case 'trouverfichierscandidats':
					link_files_to_candidates("dossiers/Dossiers/");
					include "admin.inc.php";
					break;
				case 'creercandidats':
					creercandidats();
					include "admin.inc.php";
					break;
				case "displayunits":
					include "unites.inc.php";
					break;
				case "";
				default:
					if(substr($action,0,3)=="set")
					{
						$fieldId = substr($action,3);
						$newvalue = isset($_REQUEST['new'.$fieldId]) ? $_REQUEST['new'.$fieldId] : "";
						$newid = change_report_property($id_toupdate, $fieldId, $newvalue);
						displayReports($newid);
					}
					else
					{
						echo get_config("welcome_message");
						displayReports();
					}
					break;
			}
		}
		catch(Exception $exc)
		{
			$text = 'Impossible d\'exécuter l\'action "'.$action.'"<br/>Exception: '.$exc->getMessage();
			echo '<p><b>'.$text.'<br/></b></p>';
			echo '<script type="text/javascript">window.alert('.$text.');</script>';
		}
		?>
	</div>
</div>
