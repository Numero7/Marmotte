
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
				<h1>
			<a href="?action=view">Tous les rapports</a>
		</h1>
		<hr>
		<h1>Ajouter Rapport Chercheur</h1>
		<ul>
		<?php 
		foreach($typesEvalIndividual as $typeEval => $value)
		{
			?>
		<li><a href="?action=new&type_eval=<?php echo $typeEval ?>"><?php echo $value?>
		</a></li>
		<?php
		}
		?>
		</ul>
		<hr>
		<h1>Ajouter Rapport Unité</h1>
		<ul>
		<?php 
		foreach($typesEvalUnit as $typeEval => $value)
		{
			?>
			<li>
		<a href="?action=new&type_eval=<?php echo $typeEval ?>"><?php echo $value?>
		</a></li>
		<?php
		}
		?>
		</ul>
		<hr>
		<?php
		$sessions = showSessions();
		foreach($sessions as $s)
		{
			$typesEval = getTypesEval($s["id"]);
			?>
		<h1>
			<?php echo "<a href=\"?action=view&amp;id_session=".$s["id"]."\">".$s["nom"]." ".date("Y",strtotime($s["date"]))."</a>"; ?>
		</h1>
		<ul>
			<?php
			foreach($typesEval as $typeEval)
			{
				echo "\t\t<li><a href=\"?action=view&amp;id_session=".$s["id"]."&amp;type_eval=".urlencode($typeEval)."\">$typeEval</a></li>\n";
			}
			?>
		</ul>
		<?php
		}
		?>
		<hr>
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
