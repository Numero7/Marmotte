<h2 id="sessions">Sessions</h2>
<?php 
require_once('config_tools.inc.php');

					$curid = current_session_id();
					$sessions =  showSessions();						

require_once('manage_sessions.inc.php');

?>


<h3>Session courante</h3>
<?php 
if(isSecretaire())
{

?>
<p> Ce menu permet de sélectionner la session courante,
    c&#39;est à dire la session qui sera automatiquement sélectionnée quand les membres de votre section se connectent à Marmotte. 
</p>
<?php 
}
else
{
?>
  <p> Ce menu permet de sélectionner la session courante, c&#39;est à dire la session qui sera automatiquement créée si nécessaire.
</p>
<?php 
}
?>
<form method="post" action="index.php">
<B>Session courante</B>
						<select	name="sessionname">
					<?php
					foreach($sessions as $session)
					{
						$id = $session["id"];
						$sel = "";
						if ($curid == $id)
							$sel = " selected=\"selected\"";
						echo '<option value="'.$id."\" ".$sel.">".$id.'</option>';
					}
						?>
			</select>
			<input type="hidden" name="action" value="sessioncourante" /><input type="submit" value="Changer session courante" />
</form>
			<br/>
<hr />

<!--			<h3>Synchronisation des sessions avec e-valuation</h3>
	<p>
  R&eacute;cup&egrave;re la liste des sessions disponibles dans e-valuation.
</p><p>
<a href="index.php?action=synchronize_sessions_with_dsi&amp;admin_maintenance=">Synchroniser les sessions avec e-valuation.</a>
</p>
<hr />
-->

			<h3>Ajout d&#39;une session</h3>
			<p>Ce menu permet de créer une nouvelle session.</p>
			<form method="post" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir ajouter cette session ?');">
<B>Type</B>
																 <select
				name="sessionname">
					<?php
						echo  '<option value="Delegations">Delegations</option>';
						echo  '<option value="PES">PES</option>';
						echo  '<option value="Printemps">Printemps</option>';
						echo  '<option value="Automne">Automne</option>';
						echo  '<option value="Generique">Generique</option>';
						?>
			</select>
				Année
						<input name="sessionannee" />
									<input type="hidden" name="action" value="adminnewsession" />
						<input type="submit" value="Ajouter session" />
			</form>
			<br/>
<?php
						  if(isSecretaire() && !isACN())
						    {

?>
<hr/>
			<h3>Suppression des prérapports</h3>
<p>Ce menu permet de supprimer les prérapports d&#39;une session.</p>
			<form method="get" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir supprimer les prérapports de cette session?');">
  <select name="sessionid">
		<?php 
		$sessions =  showSessions();
		foreach($sessions as $session)
		  {
			$id = $session["id"];
			if(strpos($id,"Concours")===FALSE)
			echo "<option value=\"$id\">".$id."</option>";
		}
		?>
    </select>
						<input type="hidden" name="action" value="admindeleteprerapports" />
						<input type="submit" value="Supprimer les prérapports" />
</form>
<?php
						    }

?>
<hr />
			<h3>Suppression d&#39;une session</h3>
			<p>Ce menu permet de supprimer une session.</p>

				<B>Session</B>
			<form method="get" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir supprimer cette session?');">
  <select name="sessionid">
		<?php 
		$sessions =  showSessions();
		foreach($sessions as $session)
		  {
			$id = $session["id"];
			if(strpos($id,"Concours")===FALSE)
			  echo "<option value=\"$id\">".$id."</option>";
		}
		?>
    </select>
						<input type="hidden" name="action" value="admindeletesession" />
					<input type="checkbox" name="supprimerdossiers" 
									style="width: 10px;"> Supprimer définitivement les dossiers et rapports
</input>
						<input type="submit" value="Supprimer la session"></input>
			</form>
