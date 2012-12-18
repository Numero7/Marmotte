<div class="footer">
	<div id="authbar">
		Utilisateur	: <span class='login'><?php echo $_SESSION["login"];?> </span>
		<form method="get" style="display:inline;"><input type="hidden" name="action" value="logout"><input type="submit" name="logout" value="Se Déconnecter"></form>
		<form method="get" style="display:inline;;margin-left:5px; border-left:2px solid #FFF;padding:8px;"><input type="hidden" name="action" value="changepwd"><input type="submit" value="Modifier Mot de Passe"></form>
		<?php 
		  if (isSuperUser())
		  {
		  ?>
		  <form method="get" style="display:inline;margin-left:5px; border-left:2px solid #FFF;padding:8px;"><input type="hidden" name="action" value="admin"><input type="submit" value="Administration"></form>
		  <?php
		  }
		?>
	</div>
</div>
