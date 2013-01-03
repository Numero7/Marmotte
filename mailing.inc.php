<?php 

require_once('manage_users.inc.php');
require_once('manage_rapports.inc.php');
require_once('utils.inc.php');


function mailRapporteur($titre, $body, $email_fin, $rapporteur)
{
	$reports  = getVirginReports($rapporteur);
	if(count($reports) > 0)
	{
		echo 'Envoi d\'un email pour '.count($reports).' rapports pour "'.$rapporteur->description.'" ('.$rapporteur->email.')<br/>';
		foreach($reports as $report)
		{
			$body .= reportShortSummary($report)."\r\n";
			$body .= $email_fin;
			email_handler($rapporteur->email, $titre, $body);
		}
	}
	else
	{
		echo 'Pas d\'email pour "'.$rapporteur->description.'"<br/>';
	}
}


function mailAll($titre, $body, $email_fin )
{
	$users = listUsers();
	foreach($users as $rapporteur)
		mailRapporteur($titre,$body,$email_fin, $rapporteur);
}

function mailIndividualRapporteur($email_titre, $email_body, $email_fin, $rapporteur)
{
	$users = listUsers();
	if(isset($users[$rapporteur]))
		mailRapporteur($email_titre, $email_body, $email_fin, $users[$rapporteur]);
	else
		throw new Exception("Could not send email, no rapporteur with name \"".$rapporteur."\"");
}


$email_titre = "[conrs section ".section_nb."] liste de vos rapports";

$email_body ="Bonjour,\r\n\r\n\r\n\t veuillez trouver ci-dessous la liste des rapports pour lesquels ";
$email_body .= "vous avez été désigné comme rapporteur et qui ne sont pas encore édités.\r\n\r\n";

$email_fin = "\r\n\r\nMerci de vous connecter à l'application Marmotte\r\n";
$email_fin .= "\t".addresse_du_site."\r\npour y éditer vos rapports ";
$email_fin .= "avant le 01/01/2013. Bon courage!\r\n\r\nAmicalement, le bureau de la section.\r\n";


$users = simpleListUsers();

$action = isset($_REQUEST["action"]) ? $_REQUEST["action"] : "";


	if($action == 'email_rapporteurs')
	{
		$rapporteur = isset($_REQUEST['rapporteur']) ? $_REQUEST['rapporteur'] : "";
		$email_body = isset($_REQUEST['email_body']) ? $_REQUEST['email_body'] : "";
		$email_fin = isset($_REQUEST['email_fin']) ? $_REQUEST['email_fin'] : "";
		$email_titre = isset($_REQUEST['email_titre']) ? $_REQUEST['email_titre'] : "";
	
		try
		{
			if($rapporteur == 'allusers')
				mailAll($email_titre, $email_body, $email_fin);
			else
				mailIndividualRapporteur($email_titre, $email_body, $email_fin, $rapporteur);
		}
		catch(Exception $exc)
		{
			echo 'Error when sending emails: '.$exc->getMessage().'<br/>';
		}
	}
	//no break on purpose
			
		?>

		<h2>Rapports en attente</h2>
		<p>
		<?php
		 	echo listOfAllVirginReports();
		?>
		</p>
		<h2>Mailing</h2>
<form enctype="multipart/form-data" action="index.php" method="post">
	<input type="hidden" name="action" value="email_rapporteurs" />
	<p>
	
	
	<table border="0">
		<td>Recipients</td>
		<td style="width: 30em;"><select name="rapporteur"
			style="width: 100%;">
				<?php
				echo  "\t\t\t\t\t<option value=\"allusers\" selected=\"selected\" >All Users</option>\n";
				foreach($users as $user => $data)
					echo  "\t\t\t\t\t<option value=\"".($user)."\">".$data."</option>\n";
				?>
		</select>
		</td>
		<tr>
			<td>Subject</td>
			<td><input type="text" name="email_titre" size="80"	value="<?php echo $email_titre;?>" /></td>
		</tr>
		<tr>
			<td>Debut</td>
			<td><textarea rows="6" cols="80" name="email_body"><?php echo $email_body;?></textarea></td>
		</tr>
		<tr>
			<td>Fin</td>
			<td><textarea rows="6" cols="80" name="email_fin"><?php echo $email_fin;?></textarea></td>
		</tr>
		</table>
	</td>
	</p>
	<p>
		<input type="submit" value="Envoyer les emails"> </input>
	</p>
</form>


