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
}
if(!isSuperUser()) {
?>
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
			    <?php } ?>
							</ul>
						</td>
						<?php 
						if(!isSuperUser())
						{
						?>
				<td valign="top">
								<ul>
<li><B>Rapports</B></li>
								<li><a href="index.php?action=view&amp;reset_filter=">Tous</a></li>
								<li><a href="index.php?action=view">Sélection en cours</a></li>
									<?php
						    echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_rapporteur=".urlencode(getLogin())."\">Mes rapports</a></li>";
						  echo "<li><a href=\"index.php?action=view&amp;reset_filter=&amp;filter_avancement=todo&amp;filter_rapporteur=".urlencode(getLogin())."\">A faire</a></li>";
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
		if(!is_current_session_concours()) unset($typeExports["sousjurys"]);
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
		    if(!is_current_session_concours())
		      {
?>
<li><a onclick="return confirm('Veuillez confirmez la transmission des avis de la section au SGCN. Les avis ne seront alors plus modifiables (mais les rapports oui).')" href="?action=change_statut&amp;new_statut=avistransmis">Transmettre les avis</a></li>
<?php
		      }
else
		      {
?>

<li><a onclick="return confirm('Veuillez confirmez la transmission définitive des avis du jury au SCC. Ne pas effectuer cette opération avant la fin du jury d'admissibilité. Les avis ne seront alors plus modifiables (mais les rapports oui).')" href="?action=change_statut&amp;new_statut=avistransmis">Transmettre les avis</a></li>

<?php
		      }

		  }
if(isSecretaire() && !isACN() && !isSuperUser())
	      {
		if(!is_current_session_concours())
		  {
?>
<li><a onclick="return confirm('Veuillez confirmer la transmission des rapports de la section au SGCN. Les rapports ne seront alors plus modifiables.')" href="?action=change_statut&amp;new_statut=publie">Transmettre les rapports</a></li>
		      <?php		  }
		else
		  {
?>

<li><a onclick="return confirm('Veuillez confirmer la transmission des rapports du jury au SCC. NE pas effectuer cette opération avant la fin du jury d'admissibilité. Les rapports ne seront alors plus modifiables.')" href="?action=change_statut&amp;new_statut=publie">Transmettre les rapports</a></li>

		      <?php		  }

}
if(isSecretaire() && !isSuperUser() && !is_current_session_concours())
		{
		?>
<li><a href="index.php?action=displayimportexport">Import/Ajout</a></li>
						<?php 
		}
 ?>
</ul>
<td valign="top">
<ul>
<?php
if(!is_current_session_concours())
  {
?>
<li><a href="?action=see_people">Chercheurs section</a></li>
<?php }
  if(is_current_session_concours())
    {
      echo "<li><a href=\"?action=see_concours\">Concours</a></li>";
    }
?>

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
</li>
<li><a href="http://www.cnrs.fr/comitenational/outils/projet_marmotte.htm">Aide</a></li>
<li><a href="#" id="contributeurs_clickable">A propos</a></li>
</ul>
</td>
</tr>
					</table>
	</div>

<div class="content">
  <span hidden id="contributeurs">
  <p>Marmotte est le fruit d&acute;un <a href="https://github.com/Numero7/Marmotte">
				     projet collaboratif open-source</a> réunissant secrétaires scientifiques, SGCN, DSI et DSI-concours (version 2.3 "RainSeason") repris en TMA par <a href="https://www.globalis-ms.com">Globalis</a> depuis la version 2.4.</p>
<table><tr>
<td>
<img src="http://s.scifi-universe.com/galeries/images-old/abominable/jour_sans_fin_01.jpg">
</td>
<td>
<B>Secrétaires scientifiques</B>
  <ul>
				     <li>Hugo Gimbert (d&eacute;veloppeur, coordinateur technique, section 6)</li>
				     <li>Yann Ponty (d&eacute;veloppeur, section 6)</li>
				     <li>Matias Velazquez (&beta;-testeur, coordinateur d&eacute;ploiement SSC, section 15)</li>
				     <li>Caroline Strube (&beta;-testeuse, formatrice, section 25)</li>
				     <li>Sophie Achard (&beta;-testeuse, formatrice, section 7)</li>
				     <li>Guillaume Lapeyre (&beta;-testeur, formateur, section 19)</li>
				     <li>Santiago Pita (&beta;-testeur, section 1)</li>
</ul>
<B>SGCN</B>
<ul>
		    <li>Marie-Claude Labastie (ambassadrice)</li>
	   <li>Laurent Chazaly (coordinateur SGCN et formation)</li>
		    <li>Mich&egrave;le Desumeur (&beta;-testeuse ACN)</li>
</ul>
<B>DSI</B>
<ul>
  <li>Ren&eacute; Pelfresne (chef de projet DSI)</li>
  <li>Dominique Naude (chargée de projet GFI)</li>
</ul>
<B>DGDS</B>
<ul><li>Philippe Baptiste</li></ul>
</td>
 </ul>
 </td>
 </tr>
 </table>
<hr/>
</span>
<script>
		    var display = false;
		      document.getElementById('contributeurs_clickable').onclick 
= function (event){
			if(!display)
    $('#contributeurs').show();
			else 
    $('#contributeurs').hide();
			display = !display;
}
</script>
</div>
</div>