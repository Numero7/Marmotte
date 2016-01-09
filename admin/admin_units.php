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

<p>Les champs suivants sont nécessaires: "Code unité", "Intitulé unité",
	"Responsable nom", "Responsable prénom", "Sigle unité", "Liste
	section(s)"</p>
<form enctype="multipart/form-data" action="index.php" method="post"
	onsubmit="return confirm('Etes vous sur de vouloir uploader ce fichier labos?');">
	<p>
		<input type="hidden" name="admin_unites"> <input type="hidden"
			name="type" value="unites" /> <input type="hidden" name="action"
			value="upload" /> <input type="hidden" name="MAX_FILE_SIZE"
			value="50000000" /> Fichier csv: <input name="uploadedfile"
			type="file" /> <br /> <input type="submit" value="Ajouter/MAJ unités" />
	</p>
</form>
<hr />
<form enctype="multipart/form-data" action="index.php" method="post"
	onsubmit="return confirm('Etes vous sur de vouloir supprimer toutes les unites?');">
	<p>
		<input type="hidden" name="action" value="delete_units" /> <input
			type="submit" value="Supprimer toutes les unités" />
	</p>
</form>

<?php 
}
if(isSecretaire() && !isSuperUser())
{
	?>
<hr />
<h2 id="ajout">Ajout ou mise-à-jour d'une unité existante</h2>
<p>Permet d'importer une unité depuis la liste de toutes les unités.</p>
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
<hr />
<h2 id="ajout">Ajout ou mise-à-jour d'une unité</h2>
<p>Si une unité avec le même code existe déjà, ses données seront mises
	à jour sans que l'unité ne soit dupliquée.</p>
<p>Si l'unite est une UMR vous pouvez renseigner uniquement le champ "code" et les infos restantes
(acronyme, nom, directeur) seront automatiquement recuperees.</p>
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
<hr />
<h2>Suppression d'une unité</h2>
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
}?>
<br />
<hr />
<table>
<tr>
	<?php 



	foreach($fieldsUnitsDB as $field => $intitule)
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
			echo "<td>".(isset($data->$field) ? $data->$field : "")."</td>";
		echo "</tr>";
	}
	?>
</table>
