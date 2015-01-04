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
							<td><span class='login'><?php echo getLogin(). " - ".$_SESSION['filter_section']." - ".current_session();?>
							</span>
							</td>
						</tr>
						<tr>
							<td valign="top" style="padding-top: 20px">
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
<!-- 
								<form method="post" style="display: inline;" action="index.php">
									<input type="hidden" name="action" value="displaystats" /> <input
										type="submit" value="Stats" />
								</form>
								 -->
							</td>
						</tr>

					</table>
				</td>
				<td valign="top">

					<table>
						<tr>
							<td valign="top">Dossiers
								<ul>

									<li><a href="index.php?action=view&amp;reset_filter=">Tous</a></li>
									<li><a href="index.php?action=view">Sélection</a>
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
									<?php 
									if(is_current_session_concours())
									{
									?>
									</td>
									<td valign="top">
									<?php 
									}
									?>
								Mes rapports
								<ul>

									<?php
									echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."\">Mes rapports</a></li>";
									echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_avancement=todo&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."\">A faire</a></li>";
									echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_avancement=done&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."\">Faits</a></li>";

									if(is_current_session_concours())
									{
										foreach($typesRapportsConcours as $typeEval => $value)
										{
											echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."&amp;filter_type=".urlencode($value)."\">Mes ".$value."s</a></li>";
										}
										echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."&amp;filter_type=Candidature&amp;filter_avis=oral\">Mes Auditions</a></li>";
									}
									?>

								</ul>

							</td>
							<td>
									<?php
									$login = getLogin();
									$sections = getSections($login);
									if(isset($_REQUEST['filter_section']))
										$cur_section = $_REQUEST['filter_section'];
									else
										$cur_section = $_SESSION['filter_section'];
										
									if(count($sections) >= 1)
									{
										?>
							<form method="post" action="index.php">
									<input type="submit" value="Section"/>
									<input type="hidden" name="reset_filter" value=""/>
									<input type="hidden" name="action" value="view"/>
									<select name="filter_section">
									<?php
									foreach($sections as $section)
									{
										$sel = "";
										if ($section == $cur_section)
											$sel = ' selected="selected"';
										echo '<option value="'.$section."\" $sel>".$section."</option>\n";
									}
											?>
									</select>
									</form>
									<?php 
						}
									?>
							
							<form method="post" action="index.php">
									<input type="submit" value="Session"/>
									<input type="hidden" name="reset_filter" value=""/>
									<input type="hidden" name="action" value="view"/>
									<select name="filter_id_session">
									<?php
									$sessions = sessionArrays();
									$cur = current_session_id();
									foreach($sessions as $id => $nom)
									{
										$sel = "";
										if ($id	 == $cur)
											$sel = ' selected="selected"';
										echo '<option value="'.strval($id)."\" $sel>".$nom."</option>\n";
									}
											?>
									</select>
									</form>
		
		<form method="post" action="export.php">
		<input type="submit" value="Exporter"/>
		<input type="hidden" name="action" value="export"/>
		<select name="type">
		<?php 
		global $typeExports;
		foreach($typeExports as $idexp => $exp)
		{
			$expname= $exp["name"];
			$level = $exp["permissionlevel"];
			if (getUserPermissionLevel()>=$level)
				echo '<option value="'.$idexp.'">'.$exp["name"]."</option>\n";
		}
		?>
		</select>
		</form>
		
								<?php 
		if ( isSecretaire())
		{
		global $statutsRapports;
?>
		<li>
		<form method="post"  action="index.php">
		<input type="submit" value="Changer statut"/>
		<select name="new_statut">
		<?php  
		foreach ($statutsRapports as $val => $nom)
		{
			$sel = "";
			echo "<option value=\"".$val."\" $sel>".$nom."</option>\n";
		}
		?>
		</select>
		<input type="hidden" name="action" value="change_statut"/>
		</form>
		</li>
				<li>
				<form onsubmit="return confirm('Etes vous sur de vouloir supprimer ces rapports?');"
method="post" action="index.php">
				<input type="hidden" name="action" value="deleteCurrentSelection" /> <input
				type="submit" value="Supprimer les rapports" />
					</form>
				</li>
				<li>
				<form method="post" style="display: inline;" action="index.php">
				<input type="hidden" name="action" value="displayimportexport" />
				<input type="submit" value="Import/Ajout" />
				</form>
				</li>
			<?php 
			}
			?>
</ul>
								
							</td>

						</tr>
					</table>
				</td>
			</tr>
		</table>


	</div>
</div>
