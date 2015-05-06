<h2 id="membres">Membres de la section</h2>

<hr />
<h3 id="infosrapporteur">Statut des membres</h3>
<p>Droits des différents statuts:</p>
<ul>
	<li><b>Rapporteur</b>: peut voir tous les rapports et éditer les
		rapports et candidats dont il/elle est rapporteur.</li>
	<li><b>Bureau</b>: peut changer les rapporteurs et éditer les infos
		candidats.</li>
	<li><b>Secrétaire et président</b>: tous les droits sur tout dans la
		section.</li>
	<li><b>Admin</b>: tous les droits sur la configuration des membres et
		des unités de toutes les sections, ne peut voir aucun rapport.</b>

</ul>

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
			{
				echo "Section <input style=\"width:1cm;\" name=\"section_code\" value=\"".$data->section_code."\"></input>";
				echo "section_role <input style=\"width:2cm;\" name=\"section_role\" value=\"".$data->section_role_code."\"></input>";
				echo "CID <input style=\"width:1cm;\" name=\"CID_code\" value=\"".$data->CID_code."\"></input>";
				echo "CID_role <input style=\"width:2cm;\" name=\"CID_role\" value=\"".$data->CID_role_code."\"></input>";
				echo "Sections <input style=\"width:5cm;\" name=\"sections\" value=\"".$data->sections."\"></input>";
			}
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
						echo "$concours->intitule <select name=\"sousjury".$code."\">\n";
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

<hr />
<h3 id="adminnewaccount">Création nouveau membre</h3>
<p>Ce formulaire permet de créer un nouveau rapporteur</p>
<form method="post" action="index.php">
	<input type="hidden" name="admin_users"></input>
	<table class="inputreport">
		<tr>
			<td style="width: 20em;">Nom prenom</td>
			<td style="width: 20em;"><input name="description"
				value="Alan Turing" />
			</td>
		</tr>
		<tr>
			<td style="width: 20em;">Email JANUS</td>
			<td style="width: 20em;"><input name="email"
				value="<?php if(isset($email)) echo $email; ?>" />
			</td>
		</tr>
		<tr>
			<td>Statut</td>
			<td><select name="permissions">
					<?php
					foreach($permission_levels as $val => $level)
						if ($val<=getUserPermissionLevel()  || (isSecretaire() && $val == NIVEAU_PERMISSION_PRESIDENT))
						echo "<option value=\"$val\">".ucfirst($level)."</option>\n";
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
			<td>Confirmer mot de passe</td>
			<td><input name="newpwd2"
				value="<?php if(isset($password)) echo $password; ?>" />
			</td>
		</tr>
		<tr>
			<td><input type="submit" value="Ajouter rapporteur" /> <input
				type="hidden" name="oldpwd" value="" /> <input type="hidden"
				name="action" value="adminnewaccount" />
			</td>
			<td><input type="checkbox" name="envoiparemail" checked='checked'
				style="width: 10px;" /> Prévenir par email</td>
		</tr>
	</table>
</form>
<br />

<!--
<hr />

<hr /><h3 id="importaccounts">Import des membres</h3>

<p>Importer automatiquement les comptes des membres de la section, et
	les ajouter à la liste des comptes existant et/ou mettre à jour les comptes correspondants.</p>
<form method="post" action="index.php">
	<input type="hidden" name="admin_users"></input> <input type="hidden"
		name="action" value="importaccountsfromJanus" /> <input type="submit"
		value="Importer les comptes" />
</form>
<p>Fusionner un ancien compte Marmotte avec un nouveau compte Janus.</p>
<form method="post" action="index.php">
	<input type="hidden" name="admin_users"></input> <input type="hidden"
		name="action" value="mergeUsers" /> <input type="submit"
		value="Fusionner les comptes" />
		Ancien login
		 <select name="old_login">
		<?php
		$users = listUsers();
		foreach($users as $user => $data)
		{
			if ($data->permissions <= getUserPermissionLevel() || (isSecretaire() && $data->permissions == NIVEAU_PERMISSION_PRESIDENT))
				echo "<option value=\"$user\">".$user."</option>";
		}
		?>
	</select>
	Nouveau login
	 <select name="new_login">
		<?php
		$users = listUsers();
		foreach($users as $user => $data)
		{
			if ($data->permissions <= getUserPermissionLevel() || (isSecretaire() && $data->permissions == NIVEAU_PERMISSION_PRESIDENT))
				echo "<option value=\"$user\">".$user."</option>";
		}
		?>
	</select>
</form>
<form method="post" action="index.php">
	<input type="hidden" name="admin_users"></input> <input type="hidden"
		name="action" value="mergeUsers" /> <input type="submit"
		value="Fusionner les comptes" />
		Ancien login
		<input name="old_login"/>
	Nouveau login
	 <select name="new_login">
		<?php
		$users = listUsers();
		foreach($users as $user => $data)
		{
			if ($data->permissions <= getUserPermissionLevel() || (isSecretaire() && $data->permissions == NIVEAU_PERMISSION_PRESIDENT))
				echo "<option value=\"$user\">".$user."</option>";
		}
		?>
	</select>
</form>
<br />

-->
<hr />
<h3 id="admindeleteaccount">Suppression d'un membre</h3>
<form method="post" action="index.php"
	onsubmit="return confirm('Etes vous sur de vouloir supprimer cet utilisateur ?');">
	<input type="hidden" name="admin_users"></input>
	 <select name="login">
		<?php
		$users = listUsers();
		foreach($users as $user => $data)
		{
			if ($data->permissions <= getUserPermissionLevel() || (isSecretaire() && $data->permissions == NIVEAU_PERMISSION_PRESIDENT))
				echo "<option value=\"$user\">".ucfirst($data->description)." [".$user."]"."</option>";
		}
		?>
	</select>
	 <input type="hidden" name="action" value="admindeleteaccount" />
	<input type="submit" value="Supprimer" />
</form>
<br />
<!--
<hr />
<h3 id="admindeleteallaccounts">Suppression de tous les membres de la
	section</h3>
<form method="post" action="index.php"
	onsubmit="return confirm('Etes vous vraiment sûr de vouloir supprimer TOUS LES MEMBRES DE LA SECTION?');">
	<input type="hidden" name="admin_users"></input> <input type="hidden"
		name="action" value="admindeleteallaccounts" /> <input type="submit"
		value="Supprimer tous les membres" />
</form>
<br />

-->
<?php 
if(isSuperUser())
{
	?>

<hr />
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
			<td><input type="hidden" name="oldpwd" value="" /> <input
				type="hidden" name="action" value="adminnewpwd" /> <input
				type="submit" value="Modifier mot de passe" />
			</td>
			<td><input type="checkbox" name="envoiparemail" checked='checked'
				style="width: 10px;" /> Prévenir par email</td>

		</tr>
	</table>
</form>
<hr />
<?php
}
