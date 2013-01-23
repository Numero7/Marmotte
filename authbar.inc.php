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
	<table class="toptable"><tr><td>
	<table>
	<tr><td>
		Utilisateur	: <span class='login'><?php echo $_SESSION["login"];?> </span>
		</td></tr>
		<tr><td>
		<p></p>
		</td></tr>
		<tr><td>
		Session	: <span class='login'><?php echo current_session();?> </span>
		</td></tr>
		</table>
		</td>
		<td>
		<table>
		<tr><td>
		<form method="get" style="display:inline;;margin-left:5px; border-left:2px solid #FFF;padding:8px;" action="index.php">
		<input type="hidden" name="action" value="logout"/>
		<input type="submit" name="logout" value="Se Déconnecter"/>
		</form>
		</td></tr><tr><td><form method="get" style="display:inline;;margin-left:5px; border-left:2px solid #FFF;padding:8px;"  action="index.php">
		<input type="hidden" name="action" value="changepwd"/>
		<input type="submit" value="Mot de Passe"/>
		</form>
		
		<?php 
		  if (isSecretaire())
		  {
		  ?>
		  </td></tr>
		  <tr><td>
		  <form method="get" style="display:inline;margin-left:5px; border-left:2px solid #FFF;padding:8px;"  action="index.php">
		  <input type="hidden" name="action" value="admin"/>
		  <input type="submit" value="Administration"/></form>
		  <?php
		  }
		?>		</td></tr></table>
		</td>
		<td>
		
		<table>
		<tr>
		<td>
		Raccourcis
		<ul>
		<?php 	echo "<li><a href=\"?action=view&amp;reset_filter=&amp;login_rapp=".getLogin()."&amp;id_session=".current_session_id()."\">Mes rapports</a></li>";
		?>
		<!-- 
		<?php
		 	echo "<li><a href=\"?action=view&amp;reset_filter=&amp;login_rapp=".getLogin()."&amp;id_session=".current_session_id()."&amp;statut=prerapport\">Mes prérapports</a></li>";
		?>
		<?php 	echo "<li><a href=\"?action=view&amp;reset_filter=&amp;login_rapp=".getLogin()."&amp;id_session=".current_session_id()."&amp;statut=vierge\">Mes rapports vierges</a></li>";
		?>
		 -->
		 						<li>
			<a href="?action=view">Sélection</a>
		</li>
		<li>
			<a href="?action=view&amp;reset_filter=">Tous</a>
		</li>
		</ul>
		</td>
		<td>

		Rappports
		<ul>
		 
		<?php
		if(is_current_session_concours())
		{
			foreach($typesRapportsConcours as $typeEval => $value)
			{

				echo "<li><a href=\"?action=view&amp;reset_filter=&amp;id_session=".current_session_id()."&amp;type_eval_concours=".urlencode($value)."\">".$value."s</a>";
				if(isSecretaire())
					echo " <a href=\"?action=new&amp;type_eval=".$typeEval."\">+</a>";
				echo "</li>";
			}
			
		}
		else
		{
		foreach($typesRapportsIndividuels as $typeEval => $value)
		{
			?>
		<li>
			<a href="?action=view&amp;type_eval=<?php echo $typeEval ?>"><?php echo $value?>
			</a>
								<?php 
									if(isSecretaire())
					echo " <a href=\"?action=new&amp;type_eval=".$typeEval."\">+</a>";
					?>
		</li>
		<?php
		}
		?>
		</td><td>
		<ul>
		<?php
		foreach($typesRapportsUnites as $typeEval => $value)
		{
			?>
					<li><a href="?action=view&amp;type_eval=<?php echo $typeEval ?>"><?php echo $value?>
					</a>
					<?php 
									if(isSecretaire())
					echo " <a href=\"?action=new&amp;type_eval=".$typeEval."\">+</a>";
					?></li>
					<?php
					}
					?>
				</ul>
			<?php 	
		}
		?>
			</ul>
			</td><td>
					Sessions
			<ul>
			<?php

		$sessions = sessionArrays();
		foreach($sessions as $id => $nom)
		{
			//$typesRapports = getTypesEval($s["id"]);
			echo "<li><a href=\"?action=view&amp;reset_filter=&amp;id_session=".strval($id)."\">".$nom."</a></li>";
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
				</tr>
		</table>
		</td>
		</tr>
		</table>
		
		
	</div>		
</div>