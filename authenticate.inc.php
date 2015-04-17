<?php 
//require_once('manage_user

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
	<!-- 
	<h3><a href="index.php?action=auth_janus">Authentification Janus</a></h3>
	 -->
	 <h2><a href="index.php?action=auth_janus">Authentification Janus</a></h3>
	<br/>
	<br/>
	 
	<h3>Authentification Marmotte</h3>
	<form method="POST">
	<table>
		<tr>
			<td><span class="label">Login</span></td>
			<td><input name="login" type="text" value="<?php if($firstlogin) echo 'admin';?>"></input></td>
			<td rowspan="2"><input type="submit" value="Valider">
			<input type="hidden" name="action" value="auth_marmotte"></td>
		</tr>
		<tr>
			<td><span class="label">Mot de passe</span></td>
			<td><input name="password" type="password" value="<?php if($firstlogin) echo 'password';?>"></input></td>
		</tr>
	</table>
	<br/>
	<br/>
 </form>
 	</div>
</div>
