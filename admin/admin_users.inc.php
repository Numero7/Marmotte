<h2 id="membres">Membres de la section</h2>

<hr />
<!--
<h3 id="infosrapporteur">Memebres de la section/CID</h3>

   <p>Le statut d&#39;un membre peut-être modifié temporairement pour la journée en cours
en utilisant le menu ci-dessous.<br/>
Le statut revient à sa valeur initiale (renseignée par le SGCN dans l&#39;application Ambre)
quotidiennement à 4h du matin.</p>
-->
<p>Droits des différents statuts:</p>
<ul>
	<li><b>Rapporteur</b>: peut voir tous les rapports et éditer les
		rapports et candidats dont il/elle est rapporteur.</li>
	<li><b>Bureau</b>: peut changer les rapporteurs et éditer les infos
		des chercheurs et candidats.</li>
<li><b>ACN</b>: attribution des rapporteurs, correction des rapports de section, changement de statuts.
	</li>
		<li><b>Secrétaire et président</b>: tous les droits sur tout dans la
		section, y compris le changement de statut des rapports.</li>
	<li><b>Admin</b>: tous les droits sur la configuration des membres et
		des unités de toutes les sections, ne peut voir aucun rapport.</b>
</ul>
<p>En cas de changement d email, de changement de statut ou 
	  si une création d&#39;un compte supplémentaire est nécessaire,
veuillez contacter votre ACN.</p>

<table>
	<?php
$password = genere_motdepasse();
	global $sous_jurys;
	global $concours_ouverts;

	$users = listUsers();
function cmp($a, $b)
{
    if($a->section_code != $b->section_code) 
      return ($a->section_code < $b->section_code) ? -1 : 1;
    else if($a->CID_code != $b->CID_code)
      return ($a->CID_code < $b->CID_code) ? -1 : 1;
    else
      return strcmp($a->description,$b->description);;

}
uasort($users,"cmp");

	foreach($users as $user => $data)
	{
		if ($data->permissions <= getUserPermissionLevel() || ($data->permissions  < NIVEAU_PERMISSION_SUPER_UTILISATEUR && isSecretaire()))
		{
			echo "\n<tr><td><b>".ucfirst($data->description)."</b></td><td> [".$user."]</td>\n";
			  if(isSuperUser())
			    {
			      /*
				echo "Section <input style=\"width:1cm;\" name=\"section_code\" value=\"".$data->section_code."\"></input>";
				echo "section_role <input style=\"width:2cm;\" name=\"section_role\" value=\"".$data->section_role_code."\"></input>";
				echo "CID <input style=\"width:1cm;\" name=\"CID_code\" value=\"".$data->CID_code."\"></input>";
				echo "CID_role <input style=\"width:2cm;\" name=\"CID_role\" value=\"".$data->CID_role_code."\"></input>";
			      */
			      echo "<td>"; if($data->section_code){ echo "Section <b> ".$data->section_code."</b>";} echo "</td>";
			      echo "<td>";if($data->section_role_code){ echo " <b> ".$data->section_role_code."</b>";} echo "</td>";
				echo "<td>";if($data->CID_code){ echo "CID <b> ".$data->CID_code."</b>";} echo"</td>";
				echo "<td>"; if($data->CID_role_code) echo " <b> ".$data->CID_role_code."</b>";echo "</td>";
			      echo "<td><form method=\"post\" action=\"index.php\">";
			      echo "Sections <input style=\"width:5cm;\" name=\"sections\" value=\"".$data->sections."\"></input>";
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
			    }

			else
			  {
			  foreach($permission_levels as $val => $level)
			  if ($val==$data->permissions)
			  		echo "<td>".ucfirst($level)."</td>\n";
			  }

			if(!isSuperUser() && is_current_session_concours())
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
			if(isSuperUser() || is_current_session_concours())
			echo " <input type=\"submit\" value=\"Valider\"/>\n";
			echo "</form></td></tr>\n";
		}
	}
	?>
</table>

<?php
if(isSuperUser())
{
?>

<hr />
<h3 id="adminnewaccount">Création nouveau membre</h3>
	  <p>Ce formulaire permet de créer un nouveau compte Marmotte pour un nouveau membre ne possédant pas encore de compte Janus (accès à e-valuation).<br/>
	  A ne pas utiliser si le membre possède déjà un accès à e-valuation via son login Janus, dans ce cas rapprochez vous de votre ACN pour intégrer le nouveau membre à Marmotte.</p>
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

<hr />
<?php
			      }
?>

<!--
<hr /><h3 id="importaccounts">Synchronisation des membres avec e-valuation</h3>

<p>Synchroniser les comptes Marmotte et les comptes e-valuation.</p>
<form method="post" action="index.php">
	<input type="hidden" name="admin_users"></input> <input type="hidden"
		name="action" value="importaccountsfromJanus" /> <input type="submit"
		value="Synchroniser" />
</form>
<br/>

-->
<?php
if(isACN())
{
?>
<hr/>
<h3>Fusionner deux comptes</h3>
<p> Ce menu permet de fusionner deux comptes: transmission des dossiers de l'ancien compte au nouveau compte et suppression de l'ancien compte.</p>

<form method="post" action="index.php">
	<input type="hidden" name="admin_users"></input> <input type="hidden"
		name="action" value="mergeUsers" /> <input type="submit"
		value="Fusionner les comptes" />
		Ancien login
		 <select name="old_login">
		<?php
		$users = listUsers();
ksort($users);
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

		foreach($users as $user => $data)
		{
			if ($data->permissions <= getUserPermissionLevel() || (isSecretaire() && $data->permissions == NIVEAU_PERMISSION_PRESIDENT))
				echo "<option value=\"$user\">".$user."</option>";
		}
		?>
	</select>
</form>

<?php
if(isSuperUser())
{
?>
<hr />
<h3 id="admindeleteaccount">Suppression d&#39;un membre</h3>
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

<?php
}
}
?>
<br />


<hr />




<!--
<hr />
<h3 id="admindeleteallaccounts">Suppression de tous les membres de la section</h3>
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
	<h3 id="adminnewpwd">Création / modification d&#39;un mot de passe</h3>
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
