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
Le lien suivant permet de synchroniser Marmotte avec les bases de donnees d&#39;e-valuation.<br/>
<a href="index.php?action=synchronize_with_dsi&amp;admin_maintenance=">Synchroniser avec e-valuation.</a>
</p>
<p>
<a href="index.php?action=fix_missing_data&amp;admin_maintenance=">Reparer les données manquantes.</a>
</p>
<p>
<a href="index.php?action=check_missing_data&amp;admin_maintenance=">Vérifier les données manquantes.</a>
</p>
<p>
<a href="index.php?action=sync_colleges&amp;admin_maintenance=">Synchroniser les collèges des membres.</a>
</p>
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
</p>
<?php 
	}
	?>

