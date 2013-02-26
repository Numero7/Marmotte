<?php 
require_once('config.inc.php');
require_once('manage_unites.inc.php');

global $fieldsUnitsDB;

	?>
	<table>
	<tr>
	<td>
	
	<h2>Ajout d'une unité</h2>
			<form enctype="multipart/form-data" action="index.php" method="post">
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
				<input type="hidden" name="type" value="labo" /> <input
					type="hidden" name="action" value="ajoutlabo" /> <input
					type="submit" value="Ajouter unité" />
			</form>
	
		</td>
		<td>
		<?php 
		if(isSecretaire())
{
		?>
			<h2>Suppression d'une unité</h2>
				<form method="post" action="index.php">
				<table class="inputreport">
				<tr>
				<td><select name="unite">
				<?php
				$units = unitsList();
	foreach($units as $unit => $data)
		echo "<option value=\"$unit\">".$data->prettyname."</option>";
				?>
							</select>
							</td>
						</tr>
						<tr>
							<td><input type="hidden" name="action" value="deletelabo" /> <input
								type="submit" value="Supprimer unité" />
							</td>
						</tr>
					</table>
				</form>
			</td>
	
			</td>
		</tr>
	</table>
	<?php 
}?>
	<hr />
		<?php 


echo '<table>';

foreach($fieldsUnitsDB as $field => $intitule)
	echo "<th>".$intitule."</th>";

echo "\n";

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