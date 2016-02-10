<?php 
require_once('config.inc.php');
require_once('manage_unites.inc.php');

global $fieldsUnitsDB;

if(isSuperUser())
{
	?>
<h2>Import et mise à jour globale de la liste des unités</h2>
<form>
<input type="hidden" name="admin_unites">
<input type="hidden" name="action" value="sync_units"> 
<input type="submit" value="Synchroniser la liste des unités">
</form>

<?php 
}
if(isBureauUser() && !isSuperUser())
{
	?>
<hr />
<h3 id="ajout">Ajout ou mise-à-jour d&apos;une unité d&apos;une autre section</h3>								  
<p>Permet d&apos;ajouter à la liste des unités de la section (dans Marmotte) une unité d&apos;une autre section.</p>
<form enctype="multipart/form-data" action="index.php" method="post">
	<input type="hidden" name="admin_unites"></input>
		<input type="hidden" name="admin_unites"></input>
		<select name="code">
		<?php
		$units = unitsList(true);
		foreach($units as $unit => $data)
			echo "<option value=\"$unit\">".$data->prettyname."</option>";
		?>
	</select>
	<input type="hidden" name="nickname" value="" />
	<input type="hidden" name="fullname" value="" />
	<input type="hidden" name="directeur" value="" />
	<input type="hidden" name="type" value="labo" />
	<input type="hidden" name="action" value="ajoutlabo" />
	<input type="submit" value="Ajouter ou mettre à jour" />
		
	
</form>
<br/>
<hr />
	    <h3 id="ajout">Ajout ou mise-à-jour d&apos;une unité</h3>
<p>Si une unité avec le même code existe déjà, ses données seront mises
	à jour sans que l&apos;unité ne soit dupliquée.
<br/>
Si l&apos;unité est une UMR vous pouvez renseigner uniquement le champ &quot;code&quot; et les infos restantes
(acronyme, nom, directeur) seront automatiquement récupèrées.</p>
<form enctype="multipart/form-data" action="index.php" method="post">
	<input type="hidden" name="admin_unites"></input>
	<table class="inputreport">
		<tr>
			<td style="width: 20em;">Acronyme</td>
			<td style="width: 20em;"><input name="nickname" />
			</td>
			<td><span class="examplevaleur">Exemple : LaBRI</span>
			</td>
		</tr>
		<tr>
			<td style="width: 20em;">Code</td>
			<td style="width: 20em;"><input name="code" />
			</td>
			<td><span class="examplevaleur">Exemple : UMR5800</span>
			</td>
		</tr>
		<tr>
			<td style="width: 20em;">Nom Complet</td>
			<td style="width: 20em;"><input name="fullname" />
			</td>
			<td><span class="examplevaleur">Exemple : Labratoire Bordelais de
					Recherche en Informatique</span>
			</td>
		</tr>
		<tr>
			<td style="width: 20em;">Directeur</td>
			<td style="width: 20em;"><input name="directeur" />
			</td>
			<td><span class="examplevaleur">Exemple : Pascal Weil</span>
			</td>
		</tr>
	</table>
	<input type="hidden" name="type" value="labo" />
	<input type="hidden" name="action" value="ajoutlabo" />
	<input type="submit" value="Ajouter ou mettre à jour" />
</form>


<br />
<?php
if(isSecretaire())
{
?>
<hr />
<h3>Suppression d&apos;une unité</h3>
<form method="post" action="index.php">
	<input type="hidden" name="admin_unites"></input> <select name="unite">
		<?php
		$units = unitsList();
		foreach($units as $unit => $data)
			echo "<option value=\"$unit\">".$data->prettyname."</option>";
		?>
	</select> <input type="hidden" name="action" value="deletelabo" /> <input
		type="submit" value="Supprimer unité" />
</form>
<?php 
	    }
}
?>
<br />
<hr />
<table>
<tr>
<h3>Liste des unités de la section dans Marmotte</h3>
	<?php 



	foreach($fieldsUnitsDB as $field => $intitule)
if(isSuperUser() || $field != "section")
		echo "<th>".$intitule."</th>";

	?>
	</tr>
	<tr>
	<?php 

	$units = unitsList();
	foreach($units as $unit => $data)
	{
		echo "<tr>";
		foreach($fieldsUnitsDB as $field => $intitule)
		  if(isSuperUser() || $field != "section")
			echo "<td>".(isset($data->$field) ? $data->$field : "")."</td>";
		echo "</tr>";
	}
	?>
</table>
