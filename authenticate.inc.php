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
		<!-- 
		<br/>
		<br/>
		<h3>"We can only see a short distance ahead, but we can see plenty there that needs to be done." Alan Turing.
		</h3>
		 -->
		</div>

	<div class="content"> 
	 <?php 
	 if(isset($_REQUEST["action"]) && ($_REQUEST["action"] == "auth_marmotte"))
	 {
	 ?>
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
	 <?php 
	 }
	 else
	 {
	 ?>
	 <h2><a href="index.php?action=auth_janus">Authentification Janus (login e-valuation)</a></h2>
	 <h2><a href="index.php?action=auth_marmotte">Authentification Marmotte (anciens logins)</a></h2>
	 <?php 
	 }
	 ?>
	<br/>
	<br/>
 	</div>
</div>
<!-- <iframe width="90%" height="20%" src="https://lejournal.cnrs.fr/"></iframe>  -->
