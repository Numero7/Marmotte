<?php 
require_once('config.php');
require_once('manage_sessions.inc.php');
?>


<h2>Session courante</h2>
<?php 
 $sessions = sessionShortArray();
?>

<table>
			<form method="post" action="index.php"
				<table class="inputreport">
					<tr>
						<td style="width: 20em;">Nom de session</td>
			
						<td>
										 <select
				name="sessionname">
					<?php
					foreach($sessions as $id => $nom)
						echo '<option value="'.$id.'">'.$id.'</option>';
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
<table>
	<tr>
		<td>
			<h2>Ajout d'une session</h2>
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
						echo  '<option value="Printemps">Printemps</option>';
						echo  '<option value="Automne">Automne</option>';
						?>
			</select>
			</td>
			</tr>
					<tr>
						<td style="width: 20em;">Année
						</td>
						<td style="width: 20em;"><input name="sessionannee" />
						</td>
						<td><span class="examplevaleur">2014</span>
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

		</td>
		<td>
			<h2>Suppression d'une session</h2>
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
									$nom = $session["nom"];
									date_default_timezone_set("Europe/Paris");
									$date = strtotime($session["date"]);
									echo "<option value=\"$id\">".ucfirst($nom)." ".date("Y",$date)."</option>";
								}
								?>
						</select>
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
		</td>
	</tr>
</table>
