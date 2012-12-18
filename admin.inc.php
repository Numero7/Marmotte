<h1>Interface d'administration</h1>
<h2>Créer nouvel utilisateur</h2>
<p>
<form method="post">
<table class="inputreport">
	<tr>
		<td style="width:20em;">Identifiant</td>
		<td style="width:20em;"><input name="login"></td>
		<td><td><span class="examplevaleur">Exemple : jdoe</span></td></td>
	</tr>
	<tr>
		<td style="width:20em;">Description</td>
		<td style="width:20em;"><input name="description"></td>
		<td><td><span class="examplevaleur">Exemple : The honourable John Doe, PhD</span></td></td>
	</tr>
	<tr>
		<td>Nouveau mot de passe</td>
		<td><input name="newpwd1" type="password"></td>
	</tr>
	<tr>
		<td>Confirmer mot de passe</td>
		<td><input name="newpwd2" type="password"></td>
	</tr>
	<tr>
	    <td><input type="hidden" name="oldpwd" value=""><input type="hidden" name="action" value="adminnewaccount">
		</td>
		<td><input type="submit" value="Valider modification"></td>
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
			foreach($users as $user)
			{
			  echo "<option value=\"$user\">".ucfirst($user)."</option>";
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

<h2>Suppression d'un utilisateur</h2>
<p>
<form method="post"  onsubmit="return confirm('Etes vous sur de vouloir supprimer cet utilisateur ?');">
<table class="inputreport" >
	<tr>
		<td style="width:20em;">Utilisateur</td>
		<td><select name="login"> 
		<?php 
			$users = listUsers();
			foreach($users as $user)
			{
				if (!isSuperUser($user))
				echo "<option value=\"$user\">".ucfirst($user)."</option>";
			}
		?>
		</select></td>
	</tr>
	<tr>
	    <td><input type="hidden" name="action" value="admindeleteaccount">
		</td>
		<td><input type="submit" value="Valider modification"></td>
	</tr>
</table>
</form>
</p>
