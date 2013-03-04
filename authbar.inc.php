<?php 
require_once("config.inc.php");
require_once('manage_sessions.inc.php');
require_once("manage_users.inc.php");
require_once("manage_sessions.inc.php");

global $typesRapportsConcours;
global $typesRapportsChercheurs;



?>


<div class="footer">
	<div id="authbar">
		<table class="toptable">
			<tr>
				<td>
					<table>
						<tr>
							<td><span class='login'><?php echo getLogin(). " - ".current_session();?>
							</span>
							</td>
						</tr>
						<tr>
							<td valign=top style="padding-top: 20px">
								<form method="get" style="display: inline;" action="index.php">
									<input type="hidden" name="action" value="logout" /> <input
										type="submit" name="logout" value="Logout" />
								</form>
								<form method="get" style="display: inline;" action="index.php">
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
								<form method="get" style="display: inline;" action="index.php">
									<input type="hidden" name="action" value="admin" /> <input
										type="submit" value="Admin" />
								</form> <?php
								}

								?>
								<form method="get" style="display: inline;" action="index.php">
									<input type="hidden" name="action" value="displayunits" /> <input
										type="submit" value="Unités" />
								</form>

								<form method="post" style="display: inline;" action="index.php">
									<input type="hidden" name="action" value="displaystats" /> <input
										type="submit" value="Stats" />
								</form>
							</td>
						</tr>
						<tr>
							<td>
								<form method="post" style="display: inline;" action="index.php">
									<input type="hidden" name="action" value="displayimportexport" />
									<input type="submit" value="Import/Export" />
								</form>


							</td>
						</tr>

					</table>
				</td>
				<td valign=top>

					<table>
						<tr>
							<td valign=top>Dossiers
								<ul>

									<li><a href="index.php?action=view&amp;reset_filter=">Tous</a></li>
									<li><a href="index.php?action=view">Sélection en cours</a>
									</li>

									<?php
									if(is_current_session_concours())
									{
										foreach($typesRapportsConcours as $typeEval => $value)
										{

											echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_id_session=".current_session_id()."&amp;filter_type=".urlencode($value)."\">".$value."s</a>";
											if(isSecretaire())
												echo " <a href=\"index.php?action=new&amp;type=".$typeEval."\">+</a>";
											echo "</li>";
										}

									}
									?>
									</ul>
									</td>
									<td valign=top>
								Mes rapports
								<ul>

									<?php
									echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."\">Mes rapports</a></li>";
									echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_avancement=todo&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."\">Mes rapports à faire</a></li>";
									echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_avancement=done&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."\">Mes rapports faits</a></li>";

									if(is_current_session_concours())
									{
										foreach($typesRapportsConcours as $typeEval => $value)
										{
											echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."&amp;filter_type=".urlencode($value)."\">Mes ".$value."s</a></li>";
										}
										echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."&amp;filter_type=Candidature; filter_avis=oral\">Mes Auditions</a></li>";
									}
									?>

								</ul>

							</td>
							<?php 
							if(is_current_session_concours())
							{
								?>
							<td valign=top>Auditions
								<ul>
									<?php
									echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_id_session=".current_session_id()."&amp;filter_type=Candidature &amp;filter_avis=oral\">Auditions</a></li>";

									global $concours_ouverts;
									foreach($concours_ouverts as $code => $intitule)
									{
										echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_id_session=".current_session_id()."&amp;filter_type=Candidature&amp;filter_concours=$code&amp;filter_avis=oral\">$intitule</a></li>";
									}
									?>
								</ul>
							</td>
							<?php 				
							}
							else
							{
								?>
							<td valign=top>Chercheurs
								<ul>
									<?php 
									foreach($typesRapportsChercheursShort as $typeEval => $value)
									{
										?>
									<li><a
										href="index.php?action=view&amp;filter_type=<?php echo $typeEval ?>"><?php echo $value?>
									</a> <?php 
									if(isSecretaire())
										echo " <a href=\"index.php?action=new&amp;type=".$typeEval."\">+</a>";
									?>
									</li>
									<?php
									}
							?>
								</ul>
							</td>
							<td valign=top>Unités
								<ul>
									<?php
									foreach($typesRapportsUnitesShort as $typeEval => $value)
									{
										?>
									<li><a
										href="index.php?action=view&amp;filter_type=<?php echo $typeEval ?>"><?php echo $value?>
									</a> <?php 
									if(isSecretaire())
										echo " <a href=\"index.php?action=new&amp;type=".$typeEval."\">+</a>";
									?>
									</li>

									<?php
									}
									?>
																	</ul>
							</td>
																	<?php 
								
														}
									
									?>
							<td valign=top>Sessions
								<ul>
									<?php

									$sessions = sessionArrays();
									foreach($sessions as $id => $nom)
									{
										//$typesRapports = getTypesEval($s["id"]);
										echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_id_session=".strval($id)."\">".$nom."</a></li>";
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
								</ul> <?php 
								if ( isSecretaire())
								{
									global $statutsRapports;

									echo '
		Statut
		<form method="post"  action="index.php">
		<select name="new_statut">';
									foreach ($statutsRapports as $val => $nom)
									{
										$sel = "";
										echo "<option value=\"".$val."\" $sel>".$nom."</option>\n";
									}
									echo '
									</select>
									<input type="hidden" name="action" value="change_statut"/>
									<input type="submit" value="Changer statut"/>
									</form>';
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
