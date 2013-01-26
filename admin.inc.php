<?php
$password = genere_motdepasse();
require_once('config.php');
require_once('generate_csv.inc.php');
require_once('manage_unites.inc.php');
?>

<?php 
if(isSecretaire())
{
	?>
<h1>Interface d'administration</h1>
<hr />
<h2>Config</h2>
<p>
Le fichier de config permet de configurer le numéro de la section, les thèmes de la section,
le nom du président, etc..
</p>
<p>
	Télécharger et éditer <a href="<?php echo config_file;?>">le fichier de
		configuration</a> en faisant (click droit + enregistrer la cible du
	lien sous...).<br /> En cas de souci télécharger <a
		href="<?php echo config_file_save;?>">la config de secours</a>.<br />
	Après édition, le formulaire ci-dessous permet d'uploader la nouvelle
	configuration.
<form enctype="multipart/form-data" action="index.php" method="post"
	onsubmit="return confirm('Etes vous sur d'écraser la config existante?');">
	<input type="hidden" name="type" value="config" /> <input type="hidden"
		name="action" value="upload" /> <input type="hidden"
		name="MAX_FILE_SIZE" value="100000" /> Fichier de config: <input
		name="uploadedfile" type="file" /> <br /> <input type="submit"
		value="Uploader config" />
</form>

	Le formulaire ci-dessous permet d'uploader la signature du président sous forme d'un fichier image
	au format jpeg.
<form enctype="multipart/form-data" action="index.php" method="post">
	<input type="hidden" name="type" value="signature" /> <input type="hidden"
		name="action" value="upload" /> <input type="hidden"
		name="MAX_FILE_SIZE" value="100000" /> Fichier de config: <input
		name="uploadedfile" type="file" /> <br /> <input type="submit"
		value="Uploader signature" />
</form>
<p />

<hr />

<h2>Import de rapports</h2>
<?php 
try
{
	$sql = "SELECT * FROM ".evaluations_db." LIMIT 0,5";
	$result = sql_request($sql);

	$rows = array();
	while ($row = mysql_fetch_object($result))
		$rows[] = $row;
	
	$csv_reports = compileReportsAsCSV($rows);
	$filename = "csv/exemple.csv";
	if($handle = fopen($filename, 'w'))
	{
		fwrite ($handle, $csv_reports);
		fclose($handle);
	}
	else
	{
		echo("Watchout: couldn't create exemple csv file ".$filename);
	}
}
catch(Exception $e)
{
	echo("Watchout: couldn't create exemple csv file ".$e);
}

?>
<p>Le formulaire ci-dessous permet d'injecter des rapports dans la base de donnée en les envoyant sous forme de fichier csv.<br/>
Vous pouvez partir de <a href="csv/exemple.csv">ce fichier exemple</a>.<br/>
Vous pouvez supprimer les colonnes inutiles
mais il est indispensable de laisser les intitulés des colonnes restantes tels quels.<br/>
Les différentes entrées sont encadrées par des guillemets par conséquent les champs ne doivent pas contenir
des guillements non échappés: il faut au préalabale de l'envoi remplacer chaque " par \".<br/>
<!--  Enfin utiliser de préférence l'encodage utf-8 pour les caractères accentués.<br/> -->
<form enctype="multipart/form-data" action="index.php" method="post"
	onsubmit="return confirm('Etes vous sur de creer ces rapports vierges?');">
	<p>
	Type de rapports
		<select name="subtype">
			<?php
			global $typesRapports;
			echo "<option value=\"\">Spécifié dans le csv</option>\n";
			foreach ($typesRapports as $ty => $value)
				echo "<option value=\"$ty\">".$value."</option>\n";
			?>
		</select> <input type="hidden" name="type" value="rapports" /> <input
			type="hidden" name="action" value="upload" /> <input type="hidden"
			name="MAX_FILE_SIZE" value="10000000" /> Fichier csv: <input
			name="uploadedfile" type="file" /> <br /> <input type="submit"
			value="Créer rapports" />
	</p>
</form>

<hr />


<table>
	<tr>
		<td>

			<h2>Ajout d'une unité</h2>
			<form enctype="multipart/form-data" action="index.php" method="post">
				<table class="inputreport">
					<tr>
						<td style="width: 20em;">Acronyme</td>
						<td style="width: 20em;"><input name="nickname" />
						</td>
						<td><span class="examplevaleur">Exemple : LaBRI</span>
						</td>
					</tr>
					<tr>
						<td style="width: 20em;">Code</td>
						<td style="width: 20em;"><input name="code" />
						</td>
						<td><span class="examplevaleur">Exemple : UMR5800</span>
						</td>
					</tr>
					<tr>
						<td style="width: 20em;">Nom Complet</td>
						<td style="width: 20em;"><input name="fullname" />
						</td>
						<td><span class="examplevaleur">Exemple : Labratoire Bordelais de
								Recherche en Informatique</span>
						</td>
					</tr>
					<tr>
						<td style="width: 20em;">Directeur</td>
						<td style="width: 20em;"><input name="directeur" />
						</td>
						<td><span class="examplevaleur">Exemple : Pascal Weil</span>
						</td>
					</tr>
				</table>
				<input type="hidden" name="type" value="labo" /> <input
					type="hidden" name="action" value="ajoutlabo" /> <input
					type="submit" value="Ajouter unité" />
			</form>

		</td>
		<td>
			<h2>Suppression d'une unité</h2>
			<form method="post" action="index.php">
				<table class="inputreport">
					<tr>
						<td><select name="unite">
								<?php 
								$units = unitsList();
								foreach($units as $unit => $data)
									echo "<option value=\"$unit\">".$data->prettyname."</option>";
								?>
						</select>
						</td>
					</tr>
					<tr>
						<td><input type="hidden" name="action" value="deletelabo" /> <input
							type="submit" value="Supprimer unité" />
						</td>
					</tr>
				</table>
			</form>
		</td>

		</td>
		</tr>
		</table>
		<?php 
try
{
	$sql = "SELECT * FROM ".units_db." LIMIT 0,5";
	$result = sql_request($sql);

	$rows = array();
	while ($row = mysql_fetch_object($result))
		$rows[] = $row;
	
	$csv_reports = compileUnitsAsCSV($rows);
	$filename = "csv/exemple_unites.csv";
	if($handle = fopen($filename, 'w'))
	{
		fwrite ($handle, $csv_reports);
		fclose($handle);
	}
	else
	{
		echo("Watchout: couldn't create exemple csv file ".$filename);
	}
}
catch(Exception $e)
{
	echo("Watchout: couldn't create exemple csv file ".$e);
}

?>
		
			<h2>Ajout de plusieurs unités</h2>
			<p>
			<p>Le formulaire ci-dessous permet d'injecter des unités dans la base de donnée.<br/>
Les rapports sont envoyés sous forme de fichier csv.<br/>
Vous pouvez partir de <a href="csv/exemple_unites.csv">ce fichier exemple</a>.<br/>
Vous pouvez supprimer les colonnes inutiles
mais il est indispensable de laisser les intitulés des colonnes restantes tels quels.<br/>
Les différentes entrées sont encadrées par des guillemets par conséquent les champs ne doivent pas contenir
des guillements non échappés: il faut au préalabale de l'envoi remplacer chaque " par \".<br/>
<!--  Enfin utiliser de préférence l'encodage utf-8 pour les caractères accentués.<br/> -->
Les données d'un labo avec le même code seront remplacées.
			</p>
			<form enctype="multipart/form-data" action="index.php" method="post"
				onsubmit="return confirm('Etes vous sur de vouloir uploader ce fichier labos?');">
				<p>
					<input type="hidden" name="type" value="unites" /> <input
						type="hidden" name="action" value="upload" /> <input type="hidden"
						name="MAX_FILE_SIZE" value="100000" /> Fichier csv: <input
						name="uploadedfile" type="file" /> <br /> <input type="submit"
						value="Ajouter unités" />
				</p>
			</form>

<?php 
}

if(isSecretaire())
{
	?>
<hr />
<table>
	<h2>Création nouveau rapporteur</h2>
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
						<td><input type="hidden" name="oldpwd" value="" /> <input
							type="hidden" name="action" value="adminnewaccount" />
						</td>
						<td><input type="submit" value="Ajouter rapporteur" />
						</td>
						<td>
							<p>
								<input type="checkbox" name="envoiparemail" checked='checked'
									style="width: 10px;" /> Prévenir par email
							</p>
						</td>
					</tr>
				</table>
			</form>

		</td>
		<td>
			<h2>Suppression d'un rapporteur</h2>
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
		<td>
			<h2>Modifier un mot de passe</h2>
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
						<td><input name="newpwd1" type="password" />
						</td>
					</tr>
					<tr>
						<td>Confirmer nouveau mot de passe</td>
						<td><input name="newpwd2" type="password" />
						</td>
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
	</tr>
</table>

<hr />

<h2>Modifier les droits</h2>
<form method="get" action="index.php">
	<table class="inputreport">
		<?php 
		$users = listUsers();
		foreach($users as $user => $data)
		{
			if ($data->permissions <= getUserPermissionLevel())
			{
				echo "<tr><td style=\"width:20em;\">".ucfirst($data->description)."</td>";
				echo "<td><form><select name=\"permissions\">";
				foreach($permission_levels as $val => $level)
				{
					if ($val<=getUserPermissionLevel())
					{
						$sel = "";
						if ($val==$data->permissions)
						{
							$sel = " selected=\"selected\"";
						}
						echo "<option value=\"$val\"$sel>".ucfirst($level)."</option>";
					}
				}
				echo "</select>";
				echo "<input type=\"hidden\" name=\"login\" value=\"$user\"/>";
				echo "<input type=\"hidden\" name=\"action\" value=\"adminnewpermissions\"/>";
				echo " <input type=\"submit\" value=\"Valider\"/>";
				echo "</form></td></tr>";
			}
		}
		?>
	</table>
</form>
<?php 
}
if(isSecretaire())
{
	?>
<br>
<hr />
<table>
	<tr>
		<td>

			<h2>Ajout d'une session</h2>
			<form method="post" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir ajouter cette session ?');">
				<table class="inputreport">
					<tr>
						<td style="width: 20em;">Nom de session</td>
						<td><input name="sessionname" />
						</td>
						<td><span class="examplevaleur">Exemple : Automne</span>
						</td>
					</tr>
					<tr>
						<td style="width: 20em;">Date <strong>Complète</strong> (Important
							!)
						</td>
						<td style="width: 20em;"><input name="sessiondate" />
						</td>
						<td><span class="examplevaleur">Exemple : 01/03/2014</span>
						</td>
					</tr>
					<tr>
						<td><input type="hidden" name="action" value="adminnewsession" />
						</td>
						<td><input type="submit" value="Ajouter session" />
						</td>
					</tr>
				</table>
			</form>

		</td>
		<td>
			<h2>Suppression d'une session</h2>
			<form method="get" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir supprimer cette session ?');">
				<table class="inputreport">
					<tr>
						<td style="width: 20em;">Nom de session</td>
						<td><select name="sessionid">
								<?php 
								$sessions =  showSessions();
								foreach($sessions as $session)
								{
									$id = $session["id"];
									$nom = $session["nom"];
									$date = strtotime($session["date"]);
									echo "<option value=\"$id\">".ucfirst($nom)." ".date("Y",$date)."</option>";
								}
								?>
						</select>
						</td>
					</tr>
					<tr>
						<td><input type="hidden" name="action" value="admindeletesession" />
						</td>
						<td><input type="submit" value="Supprimer session" />
						</td>
					</tr>
				</table>
			</form>
		</td>
	</tr>
</table>

<hr />
<?php 
}?>


<?php 
if(isSecretaire())
{
	?>
<h2>Mailing rapporteurs</h2>
<p>
Envoi d'emails de rappel aux rapporteurs ayant encore des rapports attribués
et pas édités.
</p>
<form enctype="multipart/form-data" action="index.php" method="post">
	<p>
		<input type="hidden" name="action" value="mailing" /> <input
			type="submit" value="Mailing rapporteurs" />
	</p>
</form>
<hr />


<h2>Ajout de candidats</h2>
<p>
Extrait tous les candidats des rapports de candidature et d'équivalence et
de les injecter dans la base des candidats.
</p>
<form action="index.php" method="post">
	<input type="hidden" name="action" value="creercandidats" /> <input
		type="submit" value="Créer tous les candidats" />
</form>

<p/>
<hr/>

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

	<form method="post" action="index.php">
		<input type="hidden" name="action" value="createhtpasswd" /> <input
			type="submit" value="Créer htpasswd" />
	</form>
</p>
<?php 
}

?>

