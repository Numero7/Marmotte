<?php 
require_once("config.inc.php");
require_once('manage_sessions.inc.php');
require_once("manage_users.inc.php");
require_once("manage_sessions.inc.php");

global $typesRapportsConcours;
global $typesRapportsChercheurs;

$login = getLogin();
$sections = getSections($login);
if(isset($_REQUEST['filter_section']))
	$cur_section = $_REQUEST['filter_section'];
else
	$cur_section = $_SESSION['filter_section'];


?>
<div class="footer">
	<div id="authbar">
		<table class="toptable">
			<tr>
				<td>
				<ul>
								<li>
<span class='login'>&nbsp;&nbsp;&nbsp;<?php echo getLogin();?>&nbsp;&nbsp;&nbsp;	</span>
						</li>
								<li>&nbsp;</li>	
				
						<li>
						Section/CID
									<select id="session" onchange="window.location='index.php?reset_filter=&action=change_section&filter_section=' + this.value;">
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
							</li>
							<li>
									<select id="session" onchange="window.location='index.php?reset_filter=&action=view&filter_id_session=' + this.value;">
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
							</ul>
						</td>
						<?php 
						if(!isSuperUser())
						{
						?>
				<td valign="top">
								<ul>
								<li><a href="index.php?action=view&amp;reset_filter=">Tous les dossiers</a></li>
								<li><a href="index.php?action=view">Sélection</a></li>
									<?php
									echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."\">Mes rapports</a></li>";
									echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_avancement=todo&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."\">A faire</a></li>";
//									echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_avancement=done&amp;filter_rapporteur=".getLogin()."&amp;filter_id_session=".current_session_id()."\">Faits</a></li>";
									?>
									</td>
									<td>
<ul>
<li>
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
		</li>
		<?php 
		if(isSecretaire())
		{
		?>
						<li>
				<form method="post" style="display: inline;" action="index.php">
				<input type="hidden" name="action" value="displayimportexport" />
				<input type="submit" value="Import/Ajout" />
				</form>
				<form method="get" style="display: inline;" action="index.php">
				<input type="hidden" name="action" value="admin" />
				<input type="submit" value="Admin" />
				</form>
				</li>
				<li>
				<form method="get" style="display: inline;" action="index.php">
				<input type="hidden" name="action" value="displayunits" />
				<input type="submit" value="Unités" />
				</form>
				</li>
</ul>
								
							</td>
											<?php 
		}
						}
						else
						{
						?>
						<td>
				<form method="get" style="display: inline;" action="index.php">
				<input type="hidden" name="action" value="admin" />
				<input type="submit" value="Admin" />
				</form>
						</td>
						<?php 
						}
						?>
							<td>
							<ul>
																<li>
								<a href="index.php?action=logout">Déconnexion</a></li>
							<li>
								<a href="index.php?action=changepwd">Mot de passe</a>
								</li>
							</ul>
</td>
						</tr>
					</table>
	</div>
</div>
