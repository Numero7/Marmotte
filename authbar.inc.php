<?php 
require_once("config.inc.php");
require_once('manage_sessions.inc.php');
?>


<div class="footer">
	<div id="authbar">
		Utilisateur	: <span class='login'><?php echo $_SESSION["login"];?> </span>
		Session	: 
		<form method="post" style="display:inline;" action="index.php">
		<input type="hidden" name="action" value="change_current_session">
		<select name="current_session" >
					<?php 		
					$sessions = sessionArrays();
					foreach($sessions as $id => $nom)
					{
						$sel = "";
						if (current_session() == $nom)
							$sel = "selected=\"selected\"";

						echo  "\t\t\t\t\t<option value=\"".$nom."\" $sel>".$nom."</option>";
					}
					?>
			</select>
			<input type="submit" value="Changer session">
			</form>
		<form method="get" style="display:inline;;margin-left:5px; border-left:2px solid #FFF;padding:8px;">
		<input type="hidden" name="action" value="logout">
		<input type="submit" name="logout" value="Se Déconnecter">
		</form>
		<form method="get" style="display:inline;;margin-left:5px; border-left:2px solid #FFF;padding:8px;">
		<input type="hidden" name="action" value="changepwd">
		<input type="submit" value="Mot de Passe">
		</form>
		<?php 
		  if (isSecretaire())
		  {
		  ?>
		  <form method="get" style="display:inline;margin-left:5px; border-left:2px solid #FFF;padding:8px;">
		  <input type="hidden" name="action" value="admin">
		  <input type="submit" value="Administration"></form>
		  <?php
		  }
		?>
	</div>
</div>
