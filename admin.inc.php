<?php
$password = genere_motdepasse();
require_once('config_tools.inc.php');
require_once('generate_csv.inc.php');
require_once('manage_unites.inc.php');

$admin_sessions = isset($_REQUEST["admin_sessions"]) && isSecretaire() && !isSuperUser();
$admin_users = isset($_REQUEST["admin_users"]) && isSecretaire();
$admin_concours = isset($_REQUEST["admin_concours"]) && isSecretaire() && !isSuperUser();
$admin_config = isset($_REQUEST["admin_config"]) && isSecretaire();
$admin_keywords = isset($_REQUEST["admin_keywords"]) && isSecretaire();
$admin_rubriques = isset($_REQUEST["admin_rubriques"]) && isSecretaire() && !isSuperUser();
$admin_migration = isset($_REQUEST["admin_migration"]) && isSuperUser();
$admin_unites = isset($_REQUEST["admin_unites"]) && isSecretaire();


if(isSecretaire())
{
	?>
<h1>Interface d'administration</h1>
<ul>
<?php 
if(isSecretaire() && !isSuperUser())
{	
?>
<li><a href="index.php?action=admin&amp;admin_sessions=">Sessions</a></li>
<?php 
}
if(isSecretaire())
{
?>
<li><a href="index.php?action=admin&amp;admin_users=">Membres</a></li>
<li><a href="index.php?action=admin&amp;admin_unites">Unités</a>
<?php 
if(isSecretaire() && !isSuperUser())
{
?>
<li><a href="index.php?action=admin&amp;admin_concours=">Concours</a></li>
<li><a href="index.php?action=admin&amp;admin_config=">Configuration</a></li>
<li><a href="index.php?action=admin&amp;admin_rubriques=">Rubriques</a></li>
<li><a href="index.php?action=admin&amp;admin_keywords=">Mots-clés</a></li>
<?php 
}
if(isSuperUser())
?>
<li><a href="index.php?action=admin&amp;admin_migration=">Migration</a></li>
<?php 
}	
?></ul>

<hr />
<hr />

<?php 	
	if($admin_sessions)
{
	?>
	<h2 id="sessions">Sessions</h2>
<?php 
include 'sessions_manager.php';
?>
<hr />
<?php 
}	
?>

<?php 
if($admin_users)
{
?>
	<h2 id="membres">Membres de la section</h2>

<hr/>
							<h3 id="infosrapporteur">Statut des membres</h3>
	<table>
		<?php 
		global $sous_jurys;
		global $concours_ouverts;

		$users = listUsers();
		foreach($users as $user => $data)
		{
			if ($data->permissions <= getUserPermissionLevel() || ($data->permissions  < NIVEAU_PERMISSION_SUPER_UTILISATEUR && isSecretaire()))
			{
				echo "\n<tr><td><b>".ucfirst($data->description)."</b></td><td> [".$user."]</td>\n";
				echo '<td><form method="post" action="index.php">';
				if(isSuperUser())
					echo "Sections <input name=\"sections\" value=\"".$data->sections."\"></input>";
				else
					echo "<input type=\"hidden\" name=\"sections\" value=\"".$data->sections."\"></input>";
				echo "<input type=\"hidden\" name=\"admin_users\"></input>";
				echo "<select name=\"permissions\">\n";
				foreach($permission_levels as $val => $level)
				{
					if ($val<=getUserPermissionLevel() || (isSecretaire() && $val == NIVEAU_PERMISSION_PRESIDENT))
					{
						$sel = "";
						if ($val==$data->permissions)
							$sel = " selected=\"selected\"";
						echo "<option value=\"$val\"$sel>".ucfirst($level)."</option>\n";
					}
				}
				echo "</select>";
				if(is_current_session_concours())
				{
					$concours_ouverts = getConcours();
					foreach($concours_ouverts as $code => $concours)
					{
						if($concours->sousjury1 != "")
						{
						echo "<td>$concours->intitule <select name=\"sousjury".$code."\">\n";
						echo "<option value=\"\"$sel></option>\n";
							$sel = strcontains($concours->membressj1,$user) ? " selected=\"selected\"" : ""; 
							echo "<option value=\"1\" 	$sel>".$concours->sousjury1."</option>\n";
						if($concours->sousjury2 != "")
						{
							$sel = strcontains($concours->membressj2,$user) ? " selected=\"selected\"" : ""; 
							echo "<option value=\"2\" $sel>".$concours->sousjury2."</option>\n";
						}
						if($concours->sousjury3 != "")
						{
							$sel = strcontains($concours->membressj3,$user) ? " selected=\"selected\"" : ""; 
							echo "<option value=\"3\" $sel>".$concours->sousjury3."</option>\n";
						}
						if($concours->sousjury4 != "")
						{
							$sel = strcontains($concours->membressj4,$user) ? " selected=\"selected\"" : ""; 
							echo "<option value=\"4\" $sel>".$concours->sousjury4."</option>\n";
						}
				echo "</select>\n";
						}
					}
				}
				
				echo "<input type=\"hidden\" name=\"login\" value=\"$user\"/>\n";
				echo "<input type=\"hidden\" name=\"action\" value=\"infosrapporteur\"/>\n";
				echo " <input type=\"submit\" value=\"Valider\"/>\n";
				echo "</form></td></tr>\n";
			}
		}
		?>
	</table>
	<hr/>
	<h3 id="adminnewaccount">Création nouveau membre</h3>
			<form method="post" action="index.php">
			<input type="hidden" name="admin_users"></input>
				<table class="inputreport">
					<tr>
						<td style="width: 20em;">Identifiant</td>
						<td style="width: 20em;"><input name="login"
							value="login" />
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
					<td>Statut</td>
					<td>
					<select name="permissions">
					<?php 
				foreach($permission_levels as $val => $level)
					if ($val<=getUserPermissionLevel())
						echo "<option value=\"$val\">".ucfirst($level)."</option>\n";
				?>
				</select>
				</td>
				</tr>
					<?php 
					if(isSuperUser())
					{
					?>
					<tr>
						<td style="width: 20em;">Sections</td>
						<td style="width: 20em;">
						<input name="sections" value="50;51;52"></input>
						</td>
					</tr>
					<?php 
					}?>
					<tr>
						<td>Nouveau mot de passe</td>
						<td>
						<input name="newpwd1" value="<?php if(isset($password)) echo $password; ?>" />
						</td>
					</tr>
					<tr>
						<td>Confirmer mot de passe</td>
						<td>
						<input name="newpwd2" value="<?php if(isset($password)) echo $password; ?>" />
						</td>
					</tr>
						<tr>
						<td>
												<input type="submit" value="Ajouter rapporteur" />
						<input type="hidden" name="oldpwd" value="" />
						<input type="hidden" name="action" value="adminnewaccount" />
												</td>
						<td>
						<input type="checkbox" name="envoiparemail" checked='checked' style="width: 10px;" /> Prévenir par email
						</td>
						</tr>
				</table>
			</form>
<br/>
			<hr/>
						<h3 id="admindeleteaccount">Suppression d'un membre</h3>
			<form method="post" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir supprimer cet utilisateur ?');">
<select name="login">
			<input type="hidden" name="admin_users"></input>
								<?php 
								$users = listUsers();
								foreach($users as $user => $data)
								{
									if ($data->permissions <= getUserPermissionLevel())
										echo "<option value=\"$user\">".ucfirst($data->description)."</option>";
								}
								?>
						</select>
<input type="hidden" name="action" value="admindeleteaccount" />
							<input type="submit" value="Supprimer" />
			</form>
<br/>
<hr/>
						<h3 id="adminnewpwd">Modification d'un mot de passe</h3>
			<form method="post" action="index.php">
						<input type="hidden" name="admin_users"></input>
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
					<td>
											<input type="hidden" name="oldpwd" value="" /> <input
							type="hidden" name="action" value="adminnewpwd" />
						<input type="submit" value="Modifier mot de passe" />
					</td>						<td><input type="checkbox" name="envoiparemail" checked='checked'
							style="width: 10px;" /> Prévenir par email</td>
					
					</tr>
				</table>
			</form>
		<!-- 
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
			 -->
<hr/>

	
<?php
}
if($admin_unites)
{
		include "unites.php";
}

if($admin_concours)
{
	if( is_current_session_concours() )
	{
		?>
		<h2 id="concours">Concours</h2>
				<hr/>
		
		<h3>Liste des concours</h3>
		<table class="inputreport">
		<?php 
		$concours = getConcours();
		echo "<tr><th> Code </th><th> Intitule </th><th>Postes</th>";
		echo "<th>SousJury1</th><th>President1</th><th>SousJury2</th><th>President2</th><th>SousJury3</th><th>President3</th><th>SousJury4</th><th>President4</th>";
		echo "</tr>";
		foreach($concours as $conc)
		{
			echo "<tr>";
			echo "<td><b>".$conc->code . "</b></td><td>". $conc->intitule. "</td><td>".$conc->postes;
			echo "</td><td>".$conc->sousjury1. "</td><td>".$conc->president1;
			echo "</td><td>".$conc->sousjury2. "</td><td>".$conc->president2;
			echo "</td><td>".$conc->sousjury3. "</td><td>".$conc->president3;
			echo "</td><td>".$conc->sousjury4. "</td><td>".$conc->president4;
			echo "</td></tr>";
		}
		?>
		</table>
		
		<br/>
		<hr/>
		<h3>Ajouter un concours</h3>
		<form method="post" action="index.php">
					<input type="hidden" name="admin_concours"></input>
		<table><tr><td>
		code <input name="code" value="0601"></input>
		</td><td>
		intitule <input name="intitule" value="DR2"></input>
		</td><td>
		postes <select  name="postes"><?php for($i = 0 ; $i < 100; $i++) echo "<option value=\"".$i."\">".$i."</option>"; ?></select>
				</td></tr><tr><td>
		SousJury1 <input name="sousjury1"></input>
			</td><td>
		President1<select name="president1">
				<option value=""></option>
		<?php 
								$users = listUsers();
								foreach($users as $user => $data)
									echo "<option value=\"$user\">".ucfirst($data->description)."</option>";
								?>
						</select>
						</td><td>
		SousJury2 <input name="sousjury2"></input>
		 </td><td>
		President2<select name="president2">
						<option value=""></option>
								<?php 
								$users = listUsers();
								foreach($users as $user => $data)
									echo "<option value=\"$user\">".ucfirst($data->description)."</option>";
								?>
						</select>
						</td></tr>
						<tr><td>
				SousJury3 <input name="sousjury3"></input></td><td>
				President3<select name="president3">
				<option value=""></option>
								<?php 
								$users = listUsers();
								foreach($users as $user => $data)
									echo "<option value=\"$user\">".ucfirst($data->description)."</option>";
								?>
						</select>
						</td><td>
				SousJury4 <input name="sousjury4"></input></td><td>
		President4<select name="president4">
						<option value=""></option>
								<?php 
								$users = listUsers();
								foreach($users as $user => $data)
									echo "<option value=\"$user\">".ucfirst($data->description)."</option>";
								?>
						</select>
								</td>
				</tr></table>
				<input type="hidden" name="action" value="add_concours" />
				<input type="submit" value="Ajouter" />
				</form>
		<br/>
				<hr/>
				<h3>Supprimer un concours</h3>
		<form method="post" action="index.php">
							<input type="hidden" name="admin_concours"></input>
		<?php 
		$concours = getConcours();
		echo " Concours <select name=\"code\">\n";
				foreach($concours as $conc)
						echo "<option value=\"$conc->code\">".$conc->code." ".$conc->intitule."</option>\n";
				echo "</select>\n";
				
				?>
				<input type="hidden" name="action" value="delete_concours" />
				<input type="submit" value="Supprimer" />
				</form>
		<hr/>
				<?php 
	}
}

if($admin_config)
{
	?>	
<h2 id="config">Configuration</h2>
<form>
					<input type="hidden" name="admin_config"></input>
<table>
<tr>
<?php 
echo "<tr><th>Clé</th><th>Valeur</th></tr>\n";
foreach($_SESSION["config"] as $key=> $value)
	if(substr($key, 0,6) != "topics" && substr($key, 0,7) != "formule" && $key!= "current_session" && substr($key, 0,8) != "rubrique" )
		echo "<tr><td>$key</td><td><input style=width:500px value=\"".$value."\" name=\"".$key."\"></input></td></tr>\n";
?>
<tr><td>
				<input type="hidden" name="action" value="updateconfig" />
				<input type="submit" value="Enregistrer configuration" />
</td></tr>
</table>	
</form>
<hr/>
<?php 
}
if($admin_keywords)
{
?>
<h2 id="motscles">Mots-clés de la section</h2>
<table>
<?php 
$configs = get_topics();
	echo '<tr><th>Index</th><th>Mot-clé</th><th></th></tr>';
foreach($configs as $key => $value)
	echo '<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
	?>
</table>
<form>
					<input type="hidden" name="admin_keywords"></input>
<table>
<tr>
<td>Index <input name="index"></input></td>
<td>Mot-clé <input name="motcle"></input></td>
<td>
<input type="hidden" name="action" value="addtopic" />
<input type="submit" value="Ajouter mot-clé" />
</td>
</tr>
</table>
</form>
<form>
					<input type="hidden" name="admin_keywords"></input>
<table>
<tr>
<td>
<select name='index'>
<?php 
foreach($configs as $key => $value)
{
	if(strlen($value)> 30)
		$value = substr($value,0,30);
	echo '<option value='.$key.'>'.$key.' '.$value.'</option>';
}
?>
</select>
</td>
<td>
<input type="hidden" name="action" value="removetopic" />
<input type="submit" value="Supprimer mot-clé" />
</td>
</tr>
</table></form>
	<hr/>
<?php 
}

if($admin_rubriques)
{
?>
<h2 id="rubriques">Rubriques supplémentaires</h2>
<?php 
global $rubriques_supplementaires;
foreach($rubriques_supplementaires as $field => $intitule)
{
?>
<h3 <?php echo "id=\"rubriques".$field."\"";?>>Rubriques <?php echo $intitule[2];?></h3>
<table>
<?php 
$rubriques = get_rubriques($field);
if(count($rubriques) > 0)
{
echo '<tr><th>Index</th><th>Rubrique</th></tr>';
foreach($rubriques as $index => $rubrique)
	echo '<tr><td>'.$index.'</td><td>'.$rubrique.'</td></tr>';
}
?>
</table>
<br/>
<form>
					<input type="hidden" name="admin_rubriques"></input>
<table>
<tr>
<td>
Index 
<select name="index">
<?php 
for($i = 0; $i <= 10;$i++)
	echo "<option value=\"".$i."\">".$i."</option>"
?>
</select>
<td>Rubrique <input name="rubrique"></input></td>
<td>
<input type="hidden" name="type" value="<?php echo $field;?>" />
<input type="hidden" name="action" value="addrubrique" />
<input type="submit" value="Ajouter rubrique <?php echo $intitule[2];?>" />
</td>
</tr>
</table>
</form>
<?php 
if(count($rubriques) > 0)
{
	?>
<form>
					<input type="hidden" name="admin_rubriques"></input>
<table>
<tr>
<td>
<select name='index'>
<?php 
foreach($rubriques as $index => $value)
	echo '<option value='.$index.'>'.$index.' '.$value.'</option>';
?>
</select>
</td>
<td>
<input type="hidden" name="type" value="<?php echo $field;?>" />
<input type="hidden" name="action" value="removerubrique" />
<input type="submit" value="Supprimer rubrique <?php echo $intitule[2];?>" />
</td>
</tr>
</table></form>
<hr/>	
<?php 
}
?>
<br/>
<br/>
<?php 
}
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
}


if($admin_migration)
{
	?>
	<h2>Migration depuis Marmotte 1.0</h2>
<form method="post" action="index.php">
<table>
<?php 
global $serverlogin;
global $serverpassword;

$inputs = array("section" => "6", "db_ip" => "127.0.0.1", "db_name" => "cn6", "db_user" => $serverlogin, "db_pass" => $serverpassword);
foreach($inputs as $input => $val)
echo "<tr><td>".$input."</td><td><input name=\"".$input."\" value=\"".$val."\"></input></td></tr>";	
?>
<tr><td>
<?php 
	$types = array("users","reports","people","sessions","units");
	foreach($types as $type)
echo $type.'<input type="checkbox" name="'.$type.'"envoiparemail" />';
?>
<input type="hidden" name="action" value="migrate" /> <input
		type="submit" value="Migrer" />
		</td></tr>
		</table>
	</form>
	<h2>Purge dossiers</h2>
	<h3>Purge historique</h3>
	<h3>Purge session</h3>
	<?php 
	}
	?>
