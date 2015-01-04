<?php
$password = genere_motdepasse();
require_once('config_tools.inc.php');
require_once('generate_csv.inc.php');
require_once('manage_unites.inc.php');
?>

<?php 
if(isSecretaire())
{
	?>
<h1>Interface d'administration</h1>
<ul>
<li><a href="#membres">Membres</a></li>
<li><a href="#sessions">Sessions</a></li>
<li><a href="#config">Configuration</a></li>
</ul>


<hr/>
	<h2 id="membres">Membres de la section</h2>

	<h3 id="adminnewaccount">Création nouveau rapporteur</h3>
<table>
	<tr>
		<td>
			<form method="post" action="index.php">
				<table class="inputreport">
					<tr>
						<td style="width: 20em;">Identifiant</td>
						<td style="width: 20em;"><input name="login"
							value="<?php if(isset($login)) echo $login; ?>" />
						</td>
					</tr>
					<tr>
						<td style="width: 20em;">Description</td>
						<td style="width: 20em;"><input name="description"
							value="<?php if(isset($description)) echo $description; ?>" />
						</td>
					</tr>
					<tr>
						<td style="width: 20em;">Email</td>
						<td style="width: 20em;"><input name="email"
							value="<?php if(isset($email)) echo $email; ?>" />
						</td>
					</tr>
					<tr>
						<td>Nouveau mot de passe</td>
						<td><input name="newpwd1"
							value="<?php if(isset($password)) echo $password; ?>" />
						</td>
					</tr>
					<tr>
						<td>Confirmer mot de passe</td>
						<td><input name="newpwd2"
							value="<?php if(isset($password)) echo $password; ?>" />
						</td>
					</tr>
						<tr>
						<td></td>
						<td>
								<input type="checkbox" name="envoiparemail" checked='checked'
									style="width: 10px;" /> Prévenir par email
						</td>
						</tr>
					<tr>
						<td><input type="hidden" name="oldpwd" value="" /> <input
							type="hidden" name="action" value="adminnewaccount" />
						</td>
						<td><input type="submit" value="Ajouter rapporteur" />
						</td>
					</tr>
				</table>
			</form>

		</td>
		<td valign="top">
			<h3 id="admindeleteaccount">Suppression d'un rapporteur</h3>
			<form method="post" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir supprimer cet utilisateur ?');">
				<table class="inputreport">
					<tr>
						<td><select name="login">
								<?php 
								$users = listUsers();
								foreach($users as $user => $data)
								{
									if ($data->permissions <= getUserPermissionLevel())
										echo "<option value=\"$user\">".ucfirst($data->description)."</option>";
								}
								?>
						</select>
						</td>
					</tr>
					<tr>
						<td><input type="hidden" name="action" value="admindeleteaccount" />
							<input type="submit" value="Supprimer rapporteur" />
						</td>
					</tr>
				</table>
			</form>
		</td>
		</tr>
		<tr>
		<td>
			<h3 id="adminnewpwd">Modifier un mot de passe</h3>
			<form method="post" action="index.php">
				<table class="inputreport">
					<tr>
						<td style="width: 20em;">Utilisateur</td>
						<td><select name="login">
								<?php 
								$users = listUsers();
								foreach($users as $user => $data)
								{
									echo "<option value=\"$user\">".ucfirst($data->description)."</option>";
								}
								?>
						</select>
						</td>
					</tr>
					<tr>
						<td>Nouveau mot de passe</td>
						<td><input name="newpwd1"
							value="<?php if(isset($password)) echo $password; ?>" />
						</td>
					</tr>
					<tr>
						<td>Confirmer nouveau mot de passe</td>
						<td><input name="newpwd2"
							value="<?php if(isset($password)) echo $password; ?>" />
						</td>
					</tr>
					<tr>
					<td></td>						<td><input type="checkbox" name="envoiparemail" checked='checked'
							style="width: 10px;" /> Prévenir par email</td>
					
					</tr>
					<tr>
						<td><input type="hidden" name="oldpwd" value="" /> <input
							type="hidden" name="action" value="adminnewpwd" />
						</td>
						<td><input type="submit" value="Valider modification" />
						</td>
					</tr>
				</table>
			</form>
		</td>
		<td>
			<h3>Vérifier un mot de passe</h3>
			<form method="post" action="index.php">
				<table class="inputreport">
					<tr>
						<td>Mot de passe</td>
						<td><input name="password" />
						</td>
					</tr>
					<tr>
						<td><input type="hidden" name="action" value="checkpwd" />
						
						<input type="submit" value="Vérifier" />
						</td>
					</tr>
				</table>
			</form>
		</td>

	</tr>
</table>


<h3 id="infosrapporteur">Statut des membres</h3>
	<table>
		<?php 
		global $sous_jurys;
		global $concours_ouverts;

		$users = listUsers();
		echo '<table>';
		foreach($users as $user => $data)
		{
			if ($data->permissions <= getUserPermissionLevel())
			{
				echo "<tr><td >".ucfirst($data->description)."</td><td> [".$user."]</td>\n";
				echo "<td>\n";
				echo '<form method="post" action="index.php">';
				echo "<select name=\"permissions\">\n";
				foreach($permission_levels as $val => $level)
				{
					if ($val<=getUserPermissionLevel())
					{
						$sel = "";
						if ($val==$data->permissions)
							$sel = " selected=\"selected\"";
						echo "<option value=\"$val\"$sel>".ucfirst($level)."</option>\n";
					}
				}
				echo "</select>\n";
				if(is_current_session_concours())
				{
					foreach($concours_ouverts as $concours => $nom)
					{
						if(isset($sous_jurys[$concours]) && count($sous_jurys[$concours]) > 0)
						{
						echo "<select name=\"sousjury".$concours."\">\n";
						echo "<option value=\"\"$sel></option>\n";

							foreach($sous_jurys[$concours] as $val => $nom)
							{
								//echo $data->sousjury."\n".$val."\n";
								$sel = "";
								if (($val != "") && ($data->sousjury != ""))
								{
									$test = strpos($data->sousjury,$val);
									if($test === 0 || $test != false)
										$sel = " selected=\"selected\"";
								}
								echo "<option value=\"".$val."\"$sel>".$concours." ".$nom."</option>\n";
							}
						echo "</select>\n";
						}
					}
				}

				echo "<input type=\"hidden\" name=\"login\" value=\"$user\"/>\n";
				echo "<input type=\"hidden\" name=\"action\" value=\"infosrapporteur\"/>\n";
				echo " <input type=\"submit\" value=\"Valider\"/>\n";
				echo "</form>\n";
			}
			echo '</table>';
		}
		?>
	</table>

<?php 	
	if(isSecretaire())
{
	?>
<br>
<hr />

<h2 id="sessions">Sessions</h2>

<?php 
include 'sessions_manager.php';
?>

<hr />
<?php 
}	
?>

<hr />
<h2 id="config">Configuration</h2>
<?php
include("config_manager.php");
?>

	



<?php 
}

if(isSecretaire())
{
	?>
	<!-- 
<h2>Stats rapporteurs</h2>
<p>Envoi d'emails de rappel aux rapporteurs ayant encore des rapports
	attribués et à faire.</p>
<form enctype="multipart/form-data" action="index.php" method="post">
	<p>
		<input type="hidden" name="action" value="mailing" /> <input
			type="submit" value="Mailing rapporteurs" />
	</p>
</form>
 -->
<!-- 
	<hr />

<h2>Candidats</h2>
<p>Extrait tous les candidats des rapports de candidature et
	d'équivalence et de les injecter dans la base des candidats.</p>
<form action="index.php" method="post">
	<input type="hidden" name="action" value="creercandidats" /> <input
		type="submit" value="Créer tous les candidats" />
</form>
<form action="index.php" method="post">
	<input type="hidden" name="action" value="injectercandidats" /> <input
		type="submit" value="Injecter données candidats" />
</form>
<p />
<p>Cherche les fichiers associés aux candidats.</p>
<form action="index.php" method="post">
	<input type="hidden" name="action" value="trouverfichierscandidats" />
	<input type="submit" value="Trouver les fichiers des candidats" />
</form>

<p />
<hr />
 -->

<!-- 
<h2>Requete sql générique</h2>
<form enctype="multipart/form-data" action="index.php" method="post">
	<table class="inputreport">
		</tr>
		<tr>
			<textarea name="formula" rows=15 cols=100>A utiliser avec précaution</textarea>
			</td>
		</tr>
	</table>
	<input type="hidden" name="action" value="sqlrequest" /> <input
		type="submit" value="Executer la requete" />
</form>
<p>
 -->
 <!--
<form method="post" action="index.php">
	<input type="hidden" name="action" value="createhtpasswd" /> <input
		type="submit" value="Créer htpasswd" />
</form>
</p>
-->
<?php 
}

?>

