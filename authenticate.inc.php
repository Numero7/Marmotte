<?php 
require_once('manage_users.inc.php');
createAdminPasswordIfNeeded();

$firstlogin = authenticateBase('admin','password');

			?>

<div class="large">
	<div class="header">
		<h2><span>Comité National de la Recherche Scientifique</span></h2>
		<br/>
		<br/>
		<br/>
		<br/>
		<br/>
		<h3>"We can only see a short distance ahead, but we can see plenty there that needs to be done." Alan Turing.
		</h3>
		</div>

	<div class="content"> 
 <p>
   Veuillez vous authentifier.
 </p>
 <form method="POST">
	<table>
		<tr>
			<td><span class="label">Login</span></td>
			<td><input name="login" type="text" value="<?php if($firstlogin) echo 'admin';?>"></input></td>
			<td rowspan="2"><input type="submit" value="Valider">
			<input type="hidden" name="action" value="auth"></td>
		</tr>
		<tr>
			<td><span class="label">Mot de passe</span></td>
			<td><input name="password" type="password" value="<?php if($firstlogin) echo 'password';?>"></input></td>
		</tr>
	</table>
	<?php 
		if ($errorLogin)
		{
			echo "<b>Couple login/mot de passe invalide ! </b>";
		}
	?>
 </form>
 	</div>
</div>
