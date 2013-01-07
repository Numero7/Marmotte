<?php 
require_once("config.inc.php");
require_once('manage_sessions.inc.php');
?>


<div class="footer">
	<div id="authbar">
		Utilisateur	: <span class='login'><?php echo $_SESSION["login"];?> </span>
		Session	: <span class='login'><?php echo current_session();?> </span>
		<form method="get" style="display:inline;;margin-left:5px; border-left:2px solid #FFF;padding:8px;" action="index.php">
		<input type="hidden" name="action" value="logout"/>
		<input type="submit" name="logout" value="Se Déconnecter"/>
		</form>
		<form method="get" style="display:inline;;margin-left:5px; border-left:2px solid #FFF;padding:8px;"  action="index.php">
		<input type="hidden" name="action" value="changepwd"/>
		<input type="submit" value="Mot de Passe"/>
		</form>
		<?php 
		  if (isSecretaire())
		  {
		  ?>
		  <form method="get" style="display:inline;margin-left:5px; border-left:2px solid #FFF;padding:8px;"  action="index.php">
		  <input type="hidden" name="action" value="admin"/>
		  <input type="submit" value="Administration"/></form>
		  <?php
		  }
		?>
	</div>
</div>
