
<?php 
require_once("config.inc.php");
require_once("manage_users.inc.php");
?>
<div class="right">
	<div class="round">
		<div class="roundtl">
			<span></span>
		</div>
		<div class="roundtr">
			<span></span>
		</div>
		<div class="clearer">
			<span></span>
		</div>
	</div>
	<div class="subnav">
		<h1>
			<a href="?">Accueil</a>
		</h1>
		<hr/>
		<h1>Afficher</h1>
		<h2>
			<a href="?action=view">Sélection en cours</a>
		</h2>
		<h2>
			<a href="?action=view&amp;reset_filter=">Tous les rapports</a>
		</h2>
		<?php
		$sessions = showSessions();
		foreach($statutsRapportsPluriel as $statut => $nom)
			echo "<h2><a href=\"?action=view&amp;reset_filter=&amp;login_rapp=".getLogin()."&amp;id_session=".current_session_id()."&amp;statut=".$statut."\">Mes ".$nom."</a></h2>";
		foreach($sessions as $s)
		{
			$typesRapports = getTypesEval($s["id"]);
			echo "<h2><a href=\"?action=view&amp;reset_filter=&amp;id_session=".$s["id"]."\">".$s["nom"]." ".date("Y",strtotime($s["date"]))."</a></h2>";
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
		if(isSecretaire())
		{
			?>
		<hr/>
		<h1>Ajouter</h1>
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
		<hr/>
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
		?>

	</div>
	<div class="round">
		<div class="roundbl">
			<span></span>
		</div>
		<div class="roundbr">
			<span></span>
		</div>
		<span class="clearer"></span>
	</div>
</div>
