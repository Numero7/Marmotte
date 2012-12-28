
<?php 
include_once("config.inc.php");
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
		<hr>
				<h1>Afficher rapports</h1>
		<?php
		$sessions = showSessions();
		foreach($sessions as $s)
		{
			$typesRapports = getTypesEval($s["id"]);
			?>
		<h2>
			<?php echo "<a href=\"?action=view&amp;id_session=".$s["id"]."\">".$s["nom"]." ".date("Y",strtotime($s["date"]))."</a>"; ?>
		</h2>
		<!-- 
		<ul>
			<?php
			foreach($typesRapports as $typeEval)
			{
				echo "\t\t<li><a href=\"?action=view&amp;id_session=".$s["id"]."&amp;type_eval=".urlencode($typeEval)."\">$typeEval</a></li>\n";
			}
			?>
		</ul>
		 -->
		<?php
		}
		?>
		<h2><a href="?action=view">Tous les rapports</a></h2>
		<hr>
		<h1>Ajouter Rapport</h1>
		<h2>Rapport Chercheur</h2>
		<ul>
		<?php 
		foreach($typesRapportsIndividuels as $typeEval => $value)
		{
			?>
		<li><a href="?action=new&type_eval=<?php echo $typeEval ?>"><?php echo $value?>
		</a></li>
		<?php
		}
		?>
		</ul>
		<hr>
		<h2>Rapport Unité</h2>
		<ul>
		<?php 
		foreach($typesRapportsUnites as $typeEval => $value)
		{
			?>
			<li>
		<a href="?action=new&type_eval=<?php echo $typeEval ?>"><?php echo $value?>
		</a></li>
		<?php
		}
		?>
		</ul>

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
