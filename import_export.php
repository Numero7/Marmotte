<?php 
require_once('config.php');
require_once('generate_csv.inc.php');
require_once('manage_unites.inc.php');


function displayImport()
{
	global $typeImports;

	?>
<form enctype="multipart/form-data" action="index.php" method="post">
<table>

<tr><td>
	<input type="hidden" name="type" value="evaluations"></input>
	<input	type="hidden" name="action" value="upload" />
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
		</td></tr><tr><td> <input name="uploadedfile"
		type="file" /> <br /> <input type="submit" value="Importer" />
		</td></tr>
	</table>
</form>

<?php 

}

function displaySecretaryImport()
{
	if(isSecretaire())
{
?>
<form enctype="multipart/form-data" action="index.php" method="post">
<table>
<tr><td>
	<input type="hidden" name="type" value="evaluations"></input>
	<input	type="hidden" name="action" value="upload" />
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
		</td></tr><tr><td> <input name="uploadedfile"
		type="file" /> <br /> <input type="submit" value="Importer trame remplie" />
		</td></tr>
	</table>
</form>
		<?php 
}
		
}

function displayExport()
{
	global $typeExports;

	echo "<ul>";

	foreach($typeExports as $idexp => $exp)
	{
		$expname= $exp["name"];
		$level = $exp["permissionlevel"];
		if (getUserPermissionLevel()>=$level)
		{
			echo "<li><a href=\"export.php?action=export&amp;type=$idexp\">";
			//echo "<img class=\"icon\" width=\"40\" height=\"40\" src=\"img/$idexp-icon-50px.png\" alt=\"$expname\"/></a>";
			echo "$expname</a></li>";
		}
	}
	echo "</ul>";
}
?>


<h2>Export</h2>
<p>Ce menu permet d'exporter l'ensemble des rapports de la sélection en
	cours dans différents formats. Pour une édition des rapports
	hors-ligne, choisir le format "csv".</p>

<?php displayExport();?>
	<hr/>
<h2>Import d'un unique rapport</h2>
<p>
Le formulaire suivant vous permet d'importer un rapport édité offline.
</p>
<?php 
	displaySecretaryImport();
	?>

	
<?php 
if(isSecretaire())
{
	?>
	<hr/>
	
	<h2>Import d'une liste de rapports vierges</h2>
	
<p>
	Le formulaire ci-dessous permet d'importer plusieurs rapports vierges dans Marmotte, en partant d'un fichier
	excel fourni par le SGCN.<br /> Ces rapports pourront ensuite être édités en ligne par les rapporteurs.<br />
	
	La procédure est la suivante.
		</p>
	
	<form enctype="multipart/form-data" action="export.php" method="post">
		<ul>
	<li>Choisissez les types de rapports vierges à importer dans la base</br>
	<?php 
	global $typesRapports;
	foreach($typesRapports as $type => $name)
		echo '<input type="checkbox" name="types[]" value="'.$type.'">'.$name.'</input><br/>'
	?>
	</li>
	<li>Choisissez les champs à importer</br>
	<input type="checkbox" name="fields[]" value="nomprenom" checked>Nom et prénom (dans le même champ)</input><br/>
	<input type="checkbox" name="fields[]" value="nom">Nom</input><br/>
	<input type="checkbox" name="fields[]" value="prenom">Prénom</input><br/>
	<input type="checkbox" name="fields[]" value="unite" checked>Code unité</input><br/>
	<input type="checkbox" name="fields[]" value="directeur" checked>DU</input><br/>
	<input type="checkbox" name="fields[]" value="grade" checked>Grade</input><br/>
	<input type="submit" name="bouton" value="Télécharger trame" />
	<input type="hidden" name="type" value="exempleimportcsv"/> 
	<input type="hidden" name="action" value="export"/> 
	.</li>
	<li>Pour chaque type de rapport, copiez les données depuis le fichier du SGCN dans la trame téléchargée.</li>
	<li>Importez la trame asinsi remplie dans Marmotte en utilisant le menu suivant:<br />
	</ul>
	</form>
	<!--  Enfin utiliser de préférence l'encodage utf-8 pour les caractères accentués.<br/> -->
	<?php 
	displaySecretaryImport();
	?>
	<p>
	Vous pouvez supprimer les colonnes inutiles mais il est indispensable de
	laisser les intitulés des colonnes restantes tels quels.<br />
	</p>
<hr />


<?php 
try
{
	$sql = "SELECT * FROM ".units_db." LIMIT 0,5";
	$result = sql_request($sql);

	$rows = array();
	while ($row = mysql_fetch_object($result))
		$rows[] = $row;

	$csv_reports = compileUnitsAsCSV($rows);
	$filename = "csv/exemple_unites.csv";
	if($handle = fopen($filename, 'w'))
	{
		fwrite ($handle, $csv_reports);
		fclose($handle);
	}
	else
	{
		echo("Watchout: couldn't create exemple csv file ".$filename);
	}
}
catch(Exception $e)
{
	echo("Watchout: couldn't create exemple csv file ".$e->getMessage());
}

?>

<h2>Import d'unités</h2>
<p>
<p>
	Le formulaire ci-dessous permet d'injecter des unités dans la base de
	donnée.<br /> Les rapports sont envoyés sous forme de fichier csv.<br />
	Vous pouvez partir de <a href="csv/exemple_unites.csv">ce fichier
		exemple</a>.<br /> Vous pouvez supprimer les colonnes inutiles mais il
	est indispensable de laisser les intitulés des colonnes restantes tels
	quels.<br /> Les différentes entrées sont encadrées par des guillemets
	par conséquent les champs ne doivent pas contenir des guillements non
	échappés: il faut au préalabale de l'envoi remplacer chaque " par \".<br />
	<!--  Enfin utiliser de préférence l'encodage utf-8 pour les caractères accentués.<br/> -->
	Les données d'un labo avec le même code seront remplacées.
</p>
<form enctype="multipart/form-data" action="index.php" method="post"
	onsubmit="return confirm('Etes vous sur de vouloir uploader ce fichier labos?');">
	<p>
		<input type="hidden" name="type" value="unites" /> <input
			type="hidden" name="action" value="upload" /> <input type="hidden"
			name="MAX_FILE_SIZE" value="100000" /> Fichier csv: <input
			name="uploadedfile" type="file" /> <br /> <input type="submit"
			value="Ajouter unités" />
	</p>
</form>

<?php 
}
else
{
	?>

<p>Ce menu permet d'importer ou de mettre à jour des rapports.</p>

<?php displayImport();
}
?>

