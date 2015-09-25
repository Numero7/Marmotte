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
<?php 
	}
	?>
<h3>Synchronisation avec e-valuation</h3>
<p>
Le lien suivant permet de synchroniser Marmotte avec les bases de donnees d&#39;e-valuation.<br/>
<a href="index.php?action=synchronize_with_dsi&amp;admin_maintenance=">Synchroniser avec e-valuation.</a>
</p>
<p>
<p>
<a href="index.php?action=check_missing_data&amp;admin_maintenance=">Vérifier les données manquantes.</a>
</P>
<p>
<a href="index.php?action=fix_missing_data&amp;admin_maintenance=">Reparer les données manquantes.</a>
</P>
<a href="index.php?action=sync_colleges&amp;admin_maintenance=">Synchroniser les collèges de membres.</a>
</P>