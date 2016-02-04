<?php
if(isSuperUser())
{
	?>
   <p>Ce menu permet de supprimer les prérapports d une session.</p>
<?php
       $sql = "SELECT DISTINCT id_session FROM reports";
       $result = sql_request($sql);
       while($row = mysqli_fetch_object($result))
	   $all_concours[]=$row->id_session;
       ?>
<form method="post" onsubmit="return confirm('Etes vous complètement sur de vouloir supprimer lesprérapports de cette session pour toutes les sections?);">
       <form>
<select name="sessionid">
<?php
   foreach($all_concours as $concours)
   echo "<option value=\"".$concours."\">".$concours."</option>";
?>
</select>
<br/>
	<input type="hidden" name="supprimerdossiers"></input>
	<input type="hidden" name="admin_maintenance"></input>
       <input  type="hidden" name="action" value="delete_prerapports" />
	<input type="submit" value="Supprimer les prerapports de la base de données" />
</form>

<br/>
<hr/>
<br/>

    
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
<p>
<a href="index.php?action=synchronizeConcours&amp;admin_maintenance=">Synchroniser les concours (attention risque faible de perte de données des utilisateurs connectés)</a>
</p>
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

