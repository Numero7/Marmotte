<?php 
require_once('config_tools.inc.php');

					$curid = current_session_id();
					$sessions =  showSessions();						
echo "Current session:'" . $curid."'<br/>";
echo "#".count($sessions)."<br/>";

require_once('manage_sessions.inc.php');
?>


<h3>Session courante</h3>
<p> Ce menu permet de sélectionner la session courante, c'est à dire la session qui sera automatiquement sélectionnée quand les membres de votre section se connectent à Marmotte. 
</p>
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
			
			<h3>Ajout d'une session</h3>
			<p>Ce menu permet de créer une nouvelle session.</p>
			<form method="post" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir ajouter cette session ?');">
<B>Type</B>
																 <select
				name="sessionname">
					<?php
						echo  '<option value="Concours">Concours</option>';
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
<hr />
			<h3>Suppression d'une session</h3>
			<p>Ce menu permet de supprimer une session.</p>
			<form method="get" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir supprimer cette session ?');">
				<B>Session</B>
<select name="sessionid">
								<?php 
								$sessions =  showSessions();
								foreach($sessions as $session)
								{
									$id = $session["id"];
									echo "<option value=\"$id\">".$id."</option>";
								}
								?>
						</select>
<input type="hidden" name="action" value="admindeletesession" />
						<input type="submit" value="Supprimer session" />
												<input type="checkbox" name="supprimerdossiers" 
									style="width: 10px;" /> Supprimer définitivement les dossiers
			</form>
