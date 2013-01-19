
<?php 
require_once("config.inc.php");
require_once("manage_users.inc.php");
require_once("manage_sessions.inc.php");

global $typesRapportsConcours;
global $typesRapportsIndividuels;

?>
		<h2>Raccourcis</h2>
		<ul>
		<?php 	echo "<li><a href=\"?action=view&amp;reset_filter=&amp;login_rapp=".getLogin()."&amp;id_session=".current_session_id()."\">Mes rapports</a></li>";
		?>
		 						<li>
			<a href="?action=view">Sélection en cours</a>
		</li>
		<li>
			<a href="?action=view&amp;reset_filter=">Tous</a>
		</li>
		 
		<?php
		if(is_current_session_concours())
		{
			foreach($typesRapportsConcours as $typeEval => $value)
		 	echo "<li><a href=\"?action=view&amp;reset_filter=&amp;id_session=".current_session_id()."&amp;type_eval_concours=".urlencode($value)."\">".$value."s</a></li>";
		}
		else
		{
		foreach($typesRapportsIndividuels as $typeEval => $value)
		{
			?>
		<li>
			<a href="?action=new&amp;type_eval=<?php echo $typeEval ?>"><?php echo $value?>
			</a>
		</li>
		<?php
		}
		}
		?>
			</ul>
			</td><td>
			<ul>
					<h2>Sessions</h2>
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
		<?php 
		if(isSecretaire())
		{
			?>
		<hr />
		<h1>Ajouter</h1>
		<?php 
		if(is_current_session_concours())
		{
			?>
		<h2>Rapport Concours</h2>
		<ul>
			<?php 
			foreach($typesRapportsConcours as $typeEval => $value)
			{
				?>
			<li><a href="?action=new&amp;type_eval=<?php echo $typeEval ?>"><?php echo $value?>
			</a></li>
			<?php
			}
			?>
		</ul>
		<?php 
		}
		else
		{
			?>
		<h2>Rapport Chercheur</h2>
		<ul>
			<?php 
			foreach($typesRapportsIndividuels as $typeEval => $value)
			{
				?>
			<li><a href="?action=new&amp;type_eval=<?php echo $typeEval ?>"><?php echo $value?>
			</a></li>
			<?php
			}
			?>
		</ul>
		<hr />
		<h2>Rapport Unité</h2>
		<ul>
			<?php 
			foreach($typesRapportsUnites as $typeEval => $value)
			{
				?>
			<li><a href="?action=new&amp;type_eval=<?php echo $typeEval ?>"><?php echo $value?>
			</a></li>
			<?php
			}
			?>
		</ul>
		<?php 
		}
		}
		?>
