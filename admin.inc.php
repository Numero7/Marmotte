<?php
		$password = genere_motdepasse();
		include_once('config.inc.php');		
 ?>

<h1>Interface d'administration</h1>

<h2>Suppression d'un rapporteur</h2>
<p>
<form method="post"  onsubmit="return confirm('Etes vous sur de vouloir supprimer cet utilisateur ?');">
<table class="inputreport" >
	<tr>
		<td style="width:20em;">Utilisateur</td>
		<td><select name="login"> 
		<?php 
			$users = listUsers();
			foreach($users as $user => $data)
			{
				if ($data->permissions <= getUserPermissionLevel())
				echo "<option value=\"$user\">".ucfirst($data->description)."</option>";
			}
		?>
		</select></td>
	</tr>
	<tr>
	    <td><input type="hidden" name="action" value="admindeleteaccount">
		</td>
		<td><input type="submit" value="Supprimer rapporteur"></td>
	</tr>
</table>
</form>
</p>

<h2>Création nouveau rapporteur</h2>
<p>
<form method="post">
<table class="inputreport">
	<tr>
		<td style="width:20em;">Identifiant</td>
		<td style="width:20em;"><input name="login" value="<?php if(isset($login)) echo $login; ?>"></td>
		<td><span class="examplevaleur">Exemple : jdoe</span></td>
	</tr>
	<tr>
		<td style="width:20em;">Description</td>
		<td style="width:20em;"><input name="description" value="<?php if(isset($description)) echo $description; ?>"></td>
		<td><span class="examplevaleur">Exemple : The honourable John Doe, PhD</span></td>
	</tr>
	<tr>
		<td style="width:20em;">Email</td>
		<td style="width:20em;"><input name="email" value="<?php if(isset($email)) echo $email; ?>"></td>
	</tr>
	<tr>
		<td>Nouveau mot de passe</td>
		<td><input name="newpwd1" value="<?php if(isset($password)) echo $password; ?>"></td>
	</tr>
	<tr>
		<td>Confirmer mot de passe</td>
		<td><input name="newpwd2" value="<?php if(isset($password)) echo $password; ?>"></td>
	</tr>
	<tr>
	    <td>
	    <input type="hidden" name="oldpwd" value="">
	    <input type="hidden" name="action" value="adminnewaccount">
		</td>
		<td>
		<input type="submit" value="Ajouter rapporteur"></td><td>
		<p>		<input type="checkbox" name="envoiparemail" checked='checked' style="width: 10px;">
		Prévenir par email</p>
		</td>
	</tr>
</table>
</form>
</p>

<h2>Modifier un mot de passe</h2>
<p>
<form method="post">
<table class="inputreport">
	<tr>
		<td style="width:20em;">Utilisateur</td>
		<td><select name="login"> 
		<?php 
			$users = listUsers();
			foreach($users as $user => $data)
			{
			  echo "<option value=\"$user\">".ucfirst($data->description)."</option>";
			}
		?>
		</select></td>
	</tr>
	<tr>
		<td>Nouveau mot de passe</td>
		<td><input name="newpwd1" type="password"></td>
	</tr>
	<tr>
		<td>Confirmer nouveau mot de passe</td>
		<td><input name="newpwd2" type="password"></td>
	</tr>
	<tr>
	    <td><input type="hidden" name="oldpwd" value=""><input type="hidden" name="action" value="adminnewpwd">
		</td>
		<td><input type="submit" value="Valider modification"></td>
	</tr>
</table>
</form>
</p>

<h2>Modifier les droits</h2>
<p>
<form method="get">
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
				echo "<input type=\"hidden\" name=\"login\" value=\"$user\">";
				echo "<input type=\"hidden\" name=\"action\" value=\"adminnewpermissions\">";
				echo " <input type=\"submit\" value=\"Valider\">";
				echo "</form></td></tr>";
			  }
			}
		?>
</table>
</form>
</p>

<br>
<hr>
<h2>Ajout d'une session</h2>
<p>
<form method="post"  onsubmit="return confirm('Etes vous sur de vouloir ajouter cette session ?');">
<table class="inputreport" >
	<tr>
		<td style="width:20em;">Nom de session</td>
		<td><input name="sessionname"></td>
		<td><span class="examplevaleur">Exemple : Automne</span></td>
	</tr>
	<tr>
		<td style="width:20em;">Date <strong>Complète</strong> (Important !)</td>
		<td style="width:20em;"><input name="sessiondate"></td>
		<td><span class="examplevaleur">Exemple : 01/03/2014</span></td>
	</tr>
	<tr>
	    <td><input type="hidden" name="action" value="adminnewsession">
		</td>
		<td><input type="submit" value="Ajouter session"></td>
	</tr>
</table>
</form>
</p>

<h2>Suppression d'une session</h2>
<p>
<form method="get"  onsubmit="return confirm('Etes vous sur de vouloir supprimer cette session ?');">
<table class="inputreport" >
	<tr>
		<td style="width:20em;">Nom de session</td>
		<td ><select name="sessionid"> 
		<?php 
			$sessions = showSessions();
			foreach($sessions as $session)
			{
				$id = $session["id"];
				$nom = $session["nom"];
				$date = strtotime($session["date"]);
				echo "<option value=\"$id\">".ucfirst($nom)." ".date("Y",$date)."</option>";
			}
		?>
		</select></td>
	</tr>
	<tr>
	    <td><input type="hidden" name="action" value="admindeletesession"></td>
		<td><input type="submit" value="Supprimer session"></td>
	</tr>
</table>
</form>
</p>
<hr>
<h2>Ajout de labos par csv</h2>
<p>
Upload de fichier csv avec séparateur , entrées encadrées par des "", encodé en utf-8
et champs dans l'ordre CodeUnite/NomUnite/Nickname/Directeur.
Les données d'un labo avec le même code seront remplacées.
<!--
 onsubmit="return confirm('Etes vous sur de vouloir uploader ce fichier labos?');">
 -->
<p>
<form enctype="multipart/form-data" action="index.php" method="POST">
<input type="hidden" name="type" value=labos>
<input type="hidden" name="action" value=upload> <input
<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
Fichier csv: <input name="uploadedfile" type="file" /><br />
<input type="submit" value="Envoyer le fichier" />
</form>
</p>
