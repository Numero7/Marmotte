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
  <br/>
  <h3>"Marmotte", une interface de gestion des rapports pour les sections du Comit&eacute; National.</h3>
  <br/>
  <br/>
<br/>
		<!-- 
		<br/>
		<br/>
		<h3>"We can only see a short distance ahead, but we can see plenty there that needs to be done." Alan Turing.
		</h3>
		 <h3>
<b>Ned:</b> So what are you doing for dinner?<br/>
<b>Phil:</b> Umm... something else. <br/>
 --<I>Groundhog Day</I>.
		 -->
 		<br/>
		<br/>
</h3>
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
<!--
<p><font color="red">
[21/04/2015 11:29] Le service d'authentification Janus fourni par la DSI n'est actuellement pas fonctionnel.
En cas de besoin, merci de contacter hugo.gimbert@labri.fr afin d'obtenir un mot de passe provisoire.
</font>
</p>
-->
	 <h2><a href="index.php?action=auth_janus">Authentification par login e-valuation</a></h2>
	 <h3><a href="index.php?action=auth_marmotte">Authentification Marmotte (anciens logins)</a></h3>
	 <?php 
	 }
	 ?>
	<br/>
	<br/>
 <br/>
  <br/>
<br/>
  <br/>
  <h3>Contributeurs Marmotte 2.0</h3>
  <ul>
  <li>Hugo Gimbert (d&eacute;veloppeur, coordinateur technique, section 6)</li>
  <li>Yann Ponty (d&eacute;veloppeur, section 6)</li>
  <li>Mathias Velazquez (beta-testeur, coordinateur d&eacute;ploiement SCC, section 25)</li>
  <li>Caroline Strube (beta-testeuse, formatrice, section 15)</li>
  <li>Sophie Achard (beta-testeuse, formatrice, section 7)</li>
  <li>Laurent Chazaly (beta-testeur, formateur, SGCN)</li>
  <li>Guillaume Lapeyre (beta-testeur, section 19)</li>
<li>Mich&egrave;le Desumeur (beta-testeuse ACN, SGCN)</li>
 </ul>
 	</div>
</div>
<!-- <iframe width="90%" height="20%" src="https://lejournal.cnrs.fr/"></iframe>  -->
