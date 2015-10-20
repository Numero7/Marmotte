<?php 
require_once("config.inc.php");
require_once('manage_sessions.inc.php');
require_once("manage_users.inc.php");
require_once("manage_sessions.inc.php");

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
<?php 
if(!isSuperUser())
{
?>				
						<li>
						Section/CID
									<select onchange="window.location='index.php?reset_filter=&amp;action=change_section&amp;filter_section=' + this.value;">
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
									    Session
									<select onchange="window.location='index.php?reset_filter=&amp;action=view&amp;filter_id_session=' + this.value;">
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
									</li>
									<?php 
}?>
			<li>
			Mode
			<select onchange="window.location='index.php?action=change_role&amp;role=' + this.value;">
			<?php 
			if(!isACN("",false))
			{
						$levels = array(
								NIVEAU_PERMISSION_PRESIDENT => "Président",
								NIVEAU_PERMISSION_SECRETAIRE => "Secrétaire",
								NIVEAU_PERMISSION_BUREAU => "Bureau",
								NIVEAU_PERMISSION_BASE => "Rapporteur");
			}
			else
			{
						$levels = array(
								NIVEAU_PERMISSION_ACN => "ACN",
								NIVEAU_PERMISSION_BASE => "Normal");
			}
						foreach($levels as $level => $name)
						{
							if(getUserPermissionLevel("",false) >= $level )
							{
								$selected = (isset($_SESSION["permission_mask"]) && $_SESSION["permission_mask"] == $level) ? "selected=\"selected\"" : "";
								echo "<option ".$selected." value=\"".$level."\">".$name."</option>\n";
							}
						}
			?>
			</select>
			</li>
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
</ul>
									</td>
									<?php 
						}?>
									<td valign="top">
									<ul>
<li><B>Exports</B></li>
								<?php 
								if(!isSuperUser())
								{
		global $typeExports;
		foreach($typeExports as $idexp => $exp)
		{
			$expname= $exp["name"];
			$level = $exp["permissionlevel"];
			if (getUserPermissionLevel()>=$level)
			  echo "<li><a href=\"export.php?action=export&amp;type=".$idexp."\">".$exp["name"]."</a></li>\n";
		}
?>
							</ul>
													<?php 
						}
						?>
</td>
<td valign="top">
<ul>
		<?php 
	    if(isSecretaire() && !isSuperUser())
		  {
?>
<li><a onclick="return confirm('Veuillez confirmez la transmission des avis de la section au SGCN. Les avis ne seront alors plus modifiables (mais les rapports oui).')" href="?action=change_statut&amp;new_statut=avistransmis">Transmettre les avis</a></li>
<?php
		  }
	    if(isSecretaire() && !isACN() && !isSuperUser())
	      {
?>
<li><a onclick="return confirm('Veuillez confirmer la transmission des rapports de la section au SGCN. Les rapports ne seront alors plus modifiables.')" href="?action=change_statut&amp;new_statut=publie">Transmettre les rapports</a></li>
<?php		  }
		if(isSecretaire() && !isSuperUser())
		{
		?>
<li><a href="index.php?action=displayimportexport">Import/Ajout</a></li>
						<?php 
		}
		if(isSecretaire())
		{?>
<?php 
		}
		if(isBureauUser("", false) && !isSuperUser())
		{
					}
				?>
</ul>
<td valign="top">
<ul>
<li><a href="?action=see_people">Chercheurs section</a></li>
<li><a href="?action=see_units">Unités section</a></li>
</ul>
</td>
<td valign="top">
<ul>
<?php
if(!is_authenticated_with_JANUS())
{
?>
							<li>
 								<a href="index.php?action=changepwd">Mot de passe</a>
								</li>
								<?php 
}
if(isSecretaire())
  {
?>
						<li>
						<a href="index.php?action=admin">Administration</a>
</li>						
<?php 
      }
?>
<li><a href="index.php?action=logout">
Déconnexion
</a>
</li></ul>
</td>
</tr>
					</table>
	</div>
</div>
