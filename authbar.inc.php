<?php 
require_once("config.inc.php");
require_once('manage_sessions.inc.php');
require_once("manage_users.inc.php");
require_once("manage_sessions.inc.php");

global $typesRapportsConcours;
global $typesRapportsIndividuels;

?>


<div class="footer">
	<div id="authbar">
		<table class="toptable">
			<tr>
				<td>
					<table>
						<tr>
							<td>Utilisateur : <span class='login'><?php echo getLogin();?> </span>
							</td>
						</tr>
						<tr>
							<td>
								<p></p>
							</td>
						</tr>
						<tr>
							<td>Session : <span class='login'><?php echo current_session();?>
							</span>
							</td>
						</tr>
					</table>
				</td>
				<td>
					<table>
						<tr>
							<td valign=top>
								<form method="get"
									style="display: inline;; margin-left: 5px; border-left: 2px solid #FFF; padding: 8px;"
									action="index.php">
									<input type="hidden" name="action" value="logout" /> <input
										type="submit" name="logout" value="Se Déconnecter" />
								</form>
							</td>
						</tr>
						<tr>
							<td><form method="get"
									style="display: inline;; margin-left: 5px; border-left: 2px solid #FFF; padding: 8px;"
									action="index.php">
									<input type="hidden" name="action" value="changepwd" /> <input
										type="submit" value="Mot de Passe" />
								</form> <?php 
								if (isSecretaire())
								{
									?>
							</td>
						</tr>
						<tr>
							<td>
								<form method="get"
									style="display: inline; margin-left: 5px; border-left: 2px solid #FFF; padding: 8px;"
									action="index.php">
									<input type="hidden" name="action" value="admin" /> <input
										type="submit" value="Administration" />
								</form> <?php
								}

								?>
							</td>
						</tr>
						<tr>
							<td>
								<form method="get"
									style="display: inline; margin-left: 5px; border-left: 2px solid #FFF; padding: 8px;"
									action="index.php">
									<input type="hidden" name="action" value="displayunits" /> <input
										type="submit" value="Unités" />
								</form>

								<form method="get"
									style="display: inline; margin-left: 5px; border-left: 2px solid #FFF; padding: 8px;"
									action="index.php">
									<input type="hidden" name="action" value="displaystats" /> <input
										type="submit" value="Stats" />
								</form>

							</td>
							</tr>
					</table>
				</td>
				<td valign=top>

					<table>
						<tr>
							<td valign=top>Rappports
								<ul>

									<li><a href="?action=view&amp;reset_filter=">Tous</a></li>
									<li><a href="?action=view">Sélection en cours</a>
									</li>

									<?php
									if(is_current_session_concours())
									{

										foreach($typesRapportsConcours as $typeEval => $value)
										{

											echo "<li><a href=\"?action=view&amp;reset_filter=&amp;filter_id_session=".current_session_id()."&amp;filter_type=".urlencode($value)."\">".$value."s</a>";
											if(isSecretaire())
												echo " <a href=\"?action=new&amp;type=".$typeEval."\">+</a>";
											echo "</li>";
										}
									}
									else
									{
										foreach($typesRapportsIndividuels as $typeEval => $value)
										{
											?>
									<li><a
										href="?action=view&amp;filter_type=<?php echo $typeEval ?>"><?php echo $value?>
									</a> <?php 
									if(isSecretaire())
										echo " <a href=\"?action=new&amp;type=".$typeEval."\">+</a>";
									?>
									</li>
									<?php
										}
										?>
							
							</td>
							<td valign=top>
								<ul>
									<?php
									foreach($typesRapportsUnites as $typeEval => $value)
									{
										?>
									<li><a
										href="?action=view&amp;filter_type=<?php echo $typeEval ?>"><?php echo $value?>
									</a> <?php 
									if(isSecretaire())
										echo " <a href=\"?action=new&amp;type=".$typeEval."\">+</a>";
									?>
									</li>
									<?php
									}
									
								 } ?>
								</ul>
							</td>
							<td valign=top>
								<ul>

									<?php
									echo "<li><a href=\"?action=view&amp;reset_filter=&amp;filter_login_rapp=".getLogin()."&amp;filter_id_session=".current_session_id()."\">Mes rapports</a></li>";
									echo "<li><a href=\"?action=view&amp;reset_filter=&amp;filter_avancement=todo&amp;filter_login_rapp=".getLogin()."&amp;filter_id_session=".current_session_id()."\">Mes rapports à faire</a></li>";
									echo "<li><a href=\"?action=view&amp;reset_filter=&amp;filter_avancement=done&amp;filter_login_rapp=".getLogin()."&amp;filter_id_session=".current_session_id()."\">Mes rapports faits</a></li>";

									if(is_current_session_concours())
									{
										foreach($typesRapportsConcours as $typeEval => $value)
										{
											echo "<li><a href=\"?action=view&amp;reset_filter=&amp;filter_login_rapp=".getLogin()."&amp;filter_id_session=".current_session_id()."&amp;filter_type=".urlencode($value)."\">Mes ".$value."s</a>";
											echo "</li>";
										}
											
									}
									?>

								</ul>
							</td>
							<td valign=top>Sessions
								<ul>
									<?php

									$sessions = sessionArrays();
									foreach($sessions as $id => $nom)
									{
										//$typesRapports = getTypesEval($s["id"]);
										echo "<li><a href=\"?action=view&amp;reset_filter=&amp;filter_id_session=".strval($id)."\">".$nom."</a></li>";
										/*			?>
										 <!--
										<ul>
										<?php
										foreach($typesRapports as $typeEval)
											echo "\t\t<li><a href=\"?action=view&amp;id_session=".$s["id"]."&amp;type_eval=".urlencode($typeEval)."\">$typeEval</a></li>\n";
										?>
										</ul>
										-->
										<?php
										*/
									}
									?>
								</ul>
							</td>
							<td valign=top>Export <?php displayExport();?>
							</td>

							<td valign=top>Import <?php displayImport();?>
							<?php 
							if (getUserPermissionLevel()>= NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE)
							{
								global $statutsRapports;

								echo '<td valign=top>Statut

		<form method="post"  action="index.php">
		<table><tr><td>
		<select name="new_statut">';
								foreach ($statutsRapports as $val => $nom)
								{
									$sel = "";
									echo "<option value=\"".$val."\" $sel>".$nom."</option>\n";
								}
								echo '
									</select>
									</td></tr>
									<tr><td>
									<input type="hidden" name="action" value="change_statut"/>
									<input type="submit" value="Changer statut"/>
									</form></td></tr></table>';
							}
							?>
							</td>

						</tr>
					</table>
				</td>
			</tr>
		</table>


	</div>
</div>
