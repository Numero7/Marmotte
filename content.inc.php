
<?php 

require_once("header.inc.php");
require_once("authbar.inc.php");
require_once('display_report.inc.php');
require_once('display_reports.inc.php');
require_once('manage_filters_and_sort.inc.php');
require_once('manage_concours.inc.php');
require_once('export.inc.php');

?>

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


function keepAlive(){
	$.ajax({
    url: 'index.php?action=ping', 
    complete: function() {
		// Rappel au bout de 5 minutes
		setTimeout(keepAlive, 30*60000);
    }
  });
}


var dirty = false;


 
$(function() {
	// Intialisation du timer pour contourner les fermetures de session
	keepAlive();
	
	// Ajout d'un style spécifique + 'dirty bit' à 'true' en cas de modifs d'un champs
	$( "#editReport" ).find(":input").change(
		function() {
			dirty = true;
			$(this).addClass("modifiedField");
		}
	);
	
	// Réinitialisation du 'dirty bit' en cas de sauvegarde
	// TODO : Proposer authentification (eg via popup) si la session est fermée
	$('[name="submitandkeepediting"]').click(function(e) {
		dirty = false;
	});	
	
	// Demande de confirmation de fermeture de page en présence de données modifiées/non sauvegardées
	window.onbeforeunload = function() {
		 if(dirty) {
			 return "You have made unsaved changes. Would you still like to leave this page?";
		 }
	 }

	// Barre d'action/titre "flottante"
	var nav = $('#toolbar');
    var navHomeY = nav.offset().top;
    var isFixed = false;
    var $w = $(window);
    $w.scroll(function() {
        var scrollTop = $w.scrollTop();
        var shouldBeFixed = scrollTop > navHomeY;
        if (shouldBeFixed && !isFixed) {
            nav.css({
                position: 'fixed',
                top: 0,
                left: nav.offset().left,
                width: nav.width(),
				"border-width" : '3px',
				"border-color" : '#63C3DC',
				"border-style" : 'solid'
            });
            isFixed = true;
        }
        else if (!shouldBeFixed && isFixed)
        {
            nav.css({
                "position" : 'static',
				"border-width": 0
            });
            isFixed = false;
        }
    });
});
</script>

<?php 
function alertText($text)
{
	echo $text."\n";
	echo
	"<script>
		alert(\"".str_replace(array("\"","<br/>","<p>","</p>"),array("'","\\n","\\n","\\n"), $text)."\")
			</script>";
}
?>

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
		require_once('manage_people.inc.php');
		require_once('db.inc.php');
		require_once("upload.inc.php");



		$id_rapport = isset($_REQUEST["id"]) ? real_escape_string($_REQUEST["id"]) : -1;
		$id_origine = isset($_REQUEST["id_origine"]) ? real_escape_string($_REQUEST["id_origine"]) : 0;
		$id_toupdate = isset($_REQUEST["id_toupdate"]) ? real_escape_string($_REQUEST["id_toupdate"]) : 0;

		$action = isset($_REQUEST["action"]) ? real_escape_string($_REQUEST["action"]) : "";

		if(isset($_REQUEST["reset_filter"]))
			resetFilterValuesExceptSession();

		if(isset($_REQUEST["reset_tri"]))
			resetOrder($_REQUEST["reset_tri"]);

		function scrollToId($id)
		{
			echo('<script type="text/javascript">');
			echo('document.getElementById("'.$id.'").scrollIntoView();');
			echo('</script>');
		}
		function displayReports($centralid = 0)
		{

			if(isSuperUser())
				return ;
			//reset_tri
			displaySummary(getCurrentFiltersList(), getFilterValues(), getSortingValues());

			if($centralid != 0 && $centralid != -1)
			{
				$id  = getIDOrigine($centralid);
				scrollToId('t'.$id);
			}

		};

		function editWithRedirect($id)
		{
			?>
			<script type="text/javascript">
			window.location = "index.php?action=edit&id=<?php echo $id;?>"
			</script>
			<?php 
		}

		function viewWithRedirect($id)
		{
			?>
					<script type="text/javascript">
					window.location = "index.php?action=read&id=<?php echo $id;?>"
					</script>
					<?php 
		}
				

		function displayWithRedirects($id = 0)
		{
			?>
							<script type="text/javascript">
							window.location = "index.php?action=view&id=<?php echo $id;?>"
							</script>
							here
							<?php
				}
				
		
		try
		{
			/* checking permissions */
			global $actions_level;
			if(isset($actions_level[$action]) && getUserPermissionLevel() < $actions_level[$action])
				throw new Exception("Vous n'avez pas le niveau de permission suffisant pour exécuter l'action '".$action."'");

			switch($action)
			{
				case 'delete_units':
					delete_all_units();
					include "unites.php";
					break;
				case 'set_property':
					$property = $_REQUEST["property"];
					$id_origine = $_REQUEST["id_origine"];
					$value = $_REQUEST["value"];
					set_property($property,$id_origine, $value, isset($_REQUEST['all_reports']));
					displayReports($id_origine);
					break;
				case 'change_section':
					displayReports();
					break;
				case 'migrate':
					$types = array("users","reports","people","sessions","units");
					foreach($types as $type)
						if(isset($_REQUEST[$type]) && $_REQUEST[$type]=="on")
						migrate( $_REQUEST["section"], $_REQUEST["db_ip"], $_REQUEST["db_name"],$_REQUEST["db_user"],  $_REQUEST["db_pass"], $type);
					break;
				case 'addrubrique':
					add_rubrique($_REQUEST["index"], $_REQUEST["rubrique"], $_REQUEST["type"]);
					include 'admin.inc.php';
					scrollToId('rubriques');					
					break;
				case 'removerubrique':
					remove_rubrique($_REQUEST["index"], $_REQUEST["type"]);
					include 'admin.inc.php';
					scrollToId('rubriques');					
					break;
				case 'addtopic':
					add_topic($_REQUEST["index"], $_REQUEST["motcle"]);
					global $topics;
					include 'admin.inc.php';
					scrollToId('config');					
					break;
				case 'removetopic':
					remove_topic($_REQUEST["index"]);
					global $topics;
					include 'admin.inc.php';
					scrollToId('config');					
					break;
				case 'updateconfig':
					save_config_from_request();
					include 'admin.inc.php';
					scrollToId('config');					
					break;
				case 'delete':
					$next = next_report($id_rapport);
					$before = deleteReport($id_rapport, true);
					echo "<p>Deleted report ".$id_rapport."</p>\n";
					unset($_REQUEST['id']);
					unset($_REQUEST['id_origine']);
//					displayWithRedirects( ($before != -1) ? $before : $next);
					if($next != -1)
						displayWithRedirects($next);
					else
						displayReports();
					break;

				case 'change_statut':
					if(isset($_REQUEST["new_statut"]))
					{
						$new_statut =  real_escape_string($_REQUEST["new_statut"]);
						change_statuts($new_statut);
						displayReports();
					}
					break;
				case 'view':
					displayReports(isset($_REQUEST["id"])?$_REQUEST["id"]:0);
					break;
				case 'deleteCurrentSelection':
					deleteCurrentSelection();
					displayReports();
					break;
				case 'affectersousjurys':
					affectersousjurys();
					include 'admin.inc.php';
					break;
				case 'affectersousjurys2':
					affectersousjurys();
					displayReports();
					break;
				case 'edit':
					editReport($id_rapport);
					break;
				case 'read':
					viewReport($id_rapport);
					break;
				case 'upload':
					$create = isset($_REQUEST["create"]);
					$result= process_upload($create);
					alertText($result);
					displayReports();
					break;
				case 'update':
					$next = next_report($id_origine);
					$rows_id = $_SESSION['rows_id'];
					$current_id = $_SESSION['current_id'];
					$previous = previous_report($id_origine);
					if(isset($_REQUEST["read"]))
						viewWithRedirect($id_origine);
					else if(isset($_REQUEST["edit"]))
						editWithRedirect($id_origine);
					else if(isset($_REQUEST["editnext"]))
						editWithRedirect($next);
					else if(isset($_REQUEST["viewnext"]))
						viewWithRedirect($next);
					else if(isset($_REQUEST["editprevious"]))
						editWithRedirect($previous);
					else if(isset($_REQUEST["viewprevious"]))
						viewWithRedirect($previous);
					else if(isset($_REQUEST["retourliste"]))
					{
						unset($_REQUEST["id_origine"]);
						unset($_REQUEST["id"]);
						displayWithRedirects($id_origine);
					}
					else if(isset($_REQUEST["deleteandeditnext"]))
					{
						$before = deleteReport($id_origine, false);
						if($before != -1)
							editWithRedirect($before);
						else if($next != -1)
							editWithRedirect($next);
						else
							displayWithRedirects();
					}
					else if(isset($_REQUEST["conflit"]))
					{
						add_conflit_to_report(getLogin(), $id_origine);
						viewWithRedirect($id_origine);
					}
					else if(isset($_REQUEST['ajoutfichier']) && isset($_REQUEST['uploaddir']))
					{
						$directory = $_REQUEST['uploaddir'];
						echo process_upload(true,	$directory);
						editReport($id_origine);
					}
					else if(isset($_REQUEST['suppressionfichier']))
					{
						if(isset($_REQUEST['deletedfile']))
						{
							$file = $_REQUEST['deletedfile'];
							if(!isSecretaire() && !is_picture($file))
								throw new Exception("You are allowed to delete images only, not documents of type '".$suffix."'");
							unlink($file);
						}
						editReport($id_origine);
					}
					else
					{
						$done = false;
						foreach($concours_ouverts as $concours => $nom)
							if(isset($_REQUEST['importconcours'.$concours]))
							{
								$done = true;
								$newreport = update_report_from_concours($id_origine,$concours, getLogin());
								editWithRedirect($newreport->id);
								break;
							}

						if(!$done)
						{
							$report = addReportFromRequest($id_origine,$_REQUEST);
							if(isset($_REQUEST["submitandeditnext"]))
								editWithRedirectReport($next);
							else if(isset($_REQUEST["submitandviewnext"]))
								viewWithRedirect($next);
							else if(isset($_REQUEST["submitandkeepediting"]))
								editWithRedirect($report->id);
							else if(isset($_REQUEST["submitandkeepviewing"]))
								viewWithRedirect($report->id);
							else
							{
								displayWithRedirects($report->id);
							}
							
						}
					}
					break;
				case 'change_current_session':
					if(isset($_REQUEST["current_session"]))
						$_SESSION['current_session'] = $_REQUEST["current_session"];
					displayWithRedirects();
					break;
				case 'new':
					if (isset($_REQUEST["type"]))
					{
						$type = $_REQUEST["type"];
						$report = newReport($type);
						$report->id_origine = $id_origine;
						displayEditableReport($report);
					}
					break;
				case'newpwd':
				case 'adminnewpwd':
					if (isset($_REQUEST["oldpwd"]) and isset($_REQUEST["newpwd1"]) and isset($_REQUEST["newpwd2"]) and isset($_REQUEST["login"]))
					{
						$old = real_escape_string($_REQUEST["oldpwd"]);
						$pwd1 = real_escape_string($_REQUEST["newpwd1"]);
						$pwd2 = real_escape_string($_REQUEST["newpwd2"]);
						$login = real_escape_string($_REQUEST["login"]);
						$envoiparemail = isset($_REQUEST["envoiparemail"])  ? real_escape_string($_REQUEST["envoiparemail"]) : false;

						if (($pwd1==$pwd2))
						{
							if (changePwd($login,$old,$pwd1,$pwd2,$envoiparemail))
								echo "<p><strong>Mot de passe modifié avec succès.</strong></p>";
						}
						else
							throw new Exception("Erreur :</strong> Les deux saisies du nouveau mot de passe  diffèrent, veuillez réessayer.</p>");
					}
					include 'admin.inc.php';
					scrollToId("membres");
					break;
				case 'admin':
					include "admin.inc.php";
					break;
				case 'admindeleteaccount':
						if (isset($_REQUEST["login"]))
						{
							$login = $_REQUEST["login"];
							deleteUser($login);
							include "admin.inc.php";
							scrollToId("membres");
						}
					break;
				case 'infosrapporteur':
						if (isset($_REQUEST["login"]) and isset($_REQUEST["permissions"]))
						{
							global  $concours_ouverts;
							$login = $_REQUEST["login"];
							$permissions = $_REQUEST["permissions"];
							$sections = $_REQUEST["sections"];
							foreach($concours_ouverts as $concours => $nom)
								if(isset($_REQUEST["sousjury".$concours]))
								addSousJury($concours, $_REQUEST["sousjury".$concours], $login);
							changeUserInfos($login,$permissions,$sections);
						include "admin.inc.php";
						scrollToId('infosrapporteur');
					}
					break;
				case 'checkpwd':
					if(isset($_REQUEST["password"]))
					{
						$password = $_REQUEST["password"];
						checkPasswords($password);
					}
					include "admin.inc.php";
					scrollToId("membres");
					break;
				case 'adminnewaccount':
						if (isset($_REQUEST["email"]) and isset($_REQUEST["description"]) and isset($_REQUEST["newpwd1"]) and isset($_REQUEST["newpwd2"]))
						{
							$desc = $_REQUEST["description"];
							$pwd1 = $_REQUEST["newpwd1"];
							$pwd2 = $_REQUEST["newpwd2"];
							$login = $_REQUEST["email"];
							$email = $_REQUEST["email"];
							$permissions = $_REQUEST["permissions"];
							$sections = "";
							if(isSuperUser())
								$sections = $_REQUEST["sections"];
							$envoiparemail = isset($_REQUEST["envoiparemail"]) && ($_REQUEST["envoiparemail"] === 'on');
							if (($pwd1==$pwd2))
								echo "<p><strong>".createUser($login,$pwd2,$desc, $email, $sections,$permissions, $envoiparemail)."</p></strong>";
							else
								echo "<p><strong>Erreur :</strong> Les deux saisies du nouveau mot de passe  diffèrent, veuillez réessayer.</p>";
						}
						include "admin.inc.php";
						scrollToId("membres");
					break;
				case 'admindeletesession':
					if (isset($_REQUEST["sessionid"]))
					{
						deleteSession(real_escape_string($_REQUEST["sessionid"]), isset($_REQUEST["supprimerdossiers"]));
						include "admin.inc.php";
						scrollToId("sessions");
					}
					break;
				case 'changepwd':
					include "changePwd.inc.php";
					break;
				case 'add_concours':
					$concours = (object) array();
					$fields = array("code", "niveau", "intitule","postes",
							 "sousjury1","sousjury2", "sousjury3", "sousjury4",
							 "president1", "president2", "president3", "president4"
							);
					foreach($fields as $field)
						$concours->$field = isset($_REQUEST[$field]) ? $_REQUEST[$field] : "";
					setConcours($concours);
					include "admin.inc.php";
					scrollToId('concours');
					break;
				case 'delete_concours':
					deleteConcours($_REQUEST["code"]);
					include "admin.inc.php";
					scrollToId('concours');
					break;
				case "statutconcours":
					$code = isset($_REQUEST["code"]) ? $_REQUEST["code"] : "";
					$statut = isset($_REQUEST["statut"]) ? $_REQUEST["statut"] : "";
					setConcoursStatut($code, $statut);
					include "admin.inc.php";
					scrollToId('concours');
					break;
				case 'ajoutlabo':
					if(isset($_REQUEST["nickname"]) and isset($_REQUEST["code"]) and isset($_REQUEST["fullname"]) and isset($_REQUEST["directeur"]))
					{
						addUnit(
						real_escape_string($_REQUEST["nickname"]),
						 real_escape_string($_REQUEST["code"]),
						 real_escape_string($_REQUEST["fullname"]),
						 real_escape_string($_REQUEST["directeur"])
						 );
						echo "Added unit \"".real_escape_string($_REQUEST["nickname"])."\"<br/>";
					}
					include "unites.php";
					break;
				case 'deletelabo':
					if(isset($_REQUEST["unite"]))
					{
						deleteUnit(real_escape_string($_REQUEST["unite"]));
						echo "Deleted unit \"".real_escape_string($_REQUEST["unite"])."\"<br/>";
					}
					include "unites.php";
					break;
				case 'mailing':
				case 'email_rapporteurs':
					include 'mailing.inc.php';
					break;
				case 'createhtpasswd':
					createhtpasswd();
					displayWithRedirects();
					include "admin.inc.php";
					break;
				case 'trouverfichierscandidats':
					link_files_to_candidates();
					include "admin.inc.php";
					break;
				case 'creercandidats':
					creercandidats();
					include "admin.inc.php";
					break;
				case 'injectercandidats':
					injectercandidats();
					include "admin.inc.php";
					break;
				case "displayimportexport":
					include "import_export.php";
					break;
				case "";
				default:
					if(substr($action,0,3)=="set")
					{
						$fieldId = substr($action,3);
						$newvalue = isset($_REQUEST['new'.$fieldId]) ? real_escape_string($_REQUEST['new'.$fieldId]) : "";
						$newid = change_report_property($id_toupdate, $fieldId, $newvalue);
						displayWithRedirects($newid);
					}
					else
					{
						if(isSuperUser())
						{
							include "admin.inc.php";
						}
							else
							{
						echo get_config("welcome_message");
						displayWithRedirects();
							}
					}
					break;
			}
		}
		catch(Exception $exc)
		{
			$text = 'Impossible d\'exécuter l\'action "'.$action.'"<br/>Exception: '.$exc->getMessage();
			alertText($text);
		}
		?>
	</div>
</div>
