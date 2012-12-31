<h2>Modifier votre mot de passe</h2>
<p>
Les mots de passes sont stockés sous forme cryptée et ne sont pas consultables en clair par les administrateurs du site.
</p>
<p>
<form method="post">
<table class="inputreport">
	<tr>
		<td style="width:20em;">Mot de passe actuel</td>
		<td><input name="oldpwd" type="password"></td>
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
	    <td></td>
		<td ><input type="hidden" name="action" value="newpwd">
		<input type="hidden" name="login" value="<?php echo $_SESSION['login']; ?>"><input type="submit" value="Valider modification"></td>
	</tr>
</table>
</form>
</p>
