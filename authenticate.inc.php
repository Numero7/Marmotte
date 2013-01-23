 <div class="large">
	<div class="header">
		<h2><span>Comité National de la Recherche Scientifique</span></h2>
		<h1><a href="index.html">Section 6 : </a>Interface de saisie des prérapports</h1>
	</div>

	<div class="content"> 
 <p>
   Veuillez vous authentifier.
 </p>
 <form method="POST">
	<table>
		<tr>
			<td><span class="label">Login</span></td>
			<td><input name="login" type="text"></td>
			<td rowspan="2"><input type="submit" value="Valider">
			<input type="hidden" name="action" value="auth"></td>
		</tr>
		<tr>
			<td><span class="label">Mot de passe</span></td>
			<td><input name="password" type="password"></td>
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
