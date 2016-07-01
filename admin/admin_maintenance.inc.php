<?php
if(isSuperUser())
{
?>
<p>
Statut maintenance: '<?php echo get_config("maintenance", "off", true, 0); ?>'</p>
<p>
<a href="index.php?action=maintenance_on&amp;admin_maintenance=">Commencer la maintenance (et fermer le site).</a>
</p>
<p>
<a href="index.php?action=maintenance_off&amp;admin_maintenance=">Terminer la maintenance.</a>
</p>
<p>
Synchroniser Marmotte avec les bases de donnees d&#39;e-valuation.<br/>
<a href="index.php?action=synchronize_with_dsi&amp;admin_maintenance=">Synchroniser avec e-valuation.</a>
</p>
<p>
<a href="index.php?action=export_to_evaluation&amp;admin_maintenance=">Exporter les avis vers e-valuation.</a>
</p>
<p>
<a href="index.php?action=synchronizeConcours&amp;admin_maintenance=">Synchroniser les concours (attention risque faible de perte de données des utilisateurs connectés)</a>
</p>
<!--
<p>
<a href="index.php?action=synchronizeStatutsConcours&amp;admin_maintenance=">Supprimer toutes les données concours.</a>
-->
<p>
<a href="index.php?action=synchronizeStatutsConcours&amp;admin_maintenance=">Synchroniser les statuts concours.</a>
</p>
<!--
<form method="get" action="index.php"
		onsubmit="return confirm('Etes vous sur de vouloir supprimer tous les prérapports de la session?');">
  <select name="sessionid">
		<?php 
		$sessions =  showSessions();
		foreach($sessions as $session)
		  {
			$id = $session["id"];
			echo "<option value=\"$id\">".$id."</option>";
		}
		?>
    </select>
						<input type="hidden" name="action" value="admindeleteprerapports" />
						<input type="submit" value="Supprimer les prérapports (irréversible)" />
</form>
-->
<?php 
	}
	?>

