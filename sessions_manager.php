<?php 
require_once('config_tools.inc.php');
require_once('manage_sessions.inc.php');
?>



<p>
<h3>Session courante</h3>

<form method="post" action="index.php">
		<table class="inputreport">
					<tr>
						<td style="width: 20em;">Nom de session</td>
			
						<td>
										 <select
				name="sessionname">
					<?php
					$curid = current_session_id();
					
					$sessions =  showSessions();						
						
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
			</td></tr>
								<tr>
						<td><input type="hidden" name="action" value="sessioncourante" />
						</td>
						<td><input type="submit" value="Changer session courante" />
						</td>
					</tr>
			

</table>
</form>
			<h3>Ajout d'une session</h3>
			<form method="post" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir ajouter cette session ?');">
				<table class="inputreport">
					<tr>
						<td style="width: 20em;">Nom de session</td>
						<td>
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
			</td>
			</tr>
					<tr>
						<td style="width: 20em;">Année
						</td>
						<td style="width: 20em;"><input name="sessionannee" />
						</td>
					</tr>
					<tr>
						<td><input type="hidden" name="action" value="adminnewsession" />
						</td>
						<td><input type="submit" value="Ajouter session" />
						</td>
					</tr>
				</table>
			</form>
			<h3>Suppression d'une session</h3>
			<form method="get" action="index.php"
				onsubmit="return confirm('Etes vous sur de vouloir supprimer cette session ?');">
				<table class="inputreport">
					<tr>
						<td style="width: 20em;">Nom de session</td>
						<td><select name="sessionid">
								<?php 
								$sessions =  showSessions();
								foreach($sessions as $session)
								{
									$id = $session["id"];
									echo "<option value=\"$id\">".$id."</option>";
								}
								?>
						</select>
						<input type="checkbox" name="supprimerdossiers" unchecked
									style="width: 10px;" /> Supprimer définitivement les dossiers
						</td>
					</tr>
					<tr>
						<td><input type="hidden" name="action" value="admindeletesession" />
						</td>
						<td><input type="submit" value="Supprimer session" />
						</td>
					</tr>
				</table>
			</form>
