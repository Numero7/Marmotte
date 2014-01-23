<?php 
require_once('config.php');
require_once('generate_csv.inc.php');
require_once('manage_unites.inc.php');
require_once('manage_sessions.inc.php');


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
		type="file" /> <br /> <input type="submit" value="Mettre à jour le ou les rapports" />
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
		<h2>Création de rapports (pour Secrétaires)</h2>
		<p>Le formulaire ci-dessous permet d'importer la liste des rapports fournis par le SGCN avant un bureau.<br/>
		Demandez à votre ACN une extraction au format csv.<br/>
		Si votre ACN ne connaît pas la procédure, dites-lui de se rapprocher de Florence Colombo.
		</p>
<!-- 	csvbureau
	<p>
	Le formulaire ci-dessous permet d'importer plusieurs rapports vierges dans Marmotte, en partant d'un fichier
	excel fourni par le SGCN.<br /> Ces rapports pourront ensuite être édités en ligne par les rapporteurs.<br />
	
	La procédure est la suivante.
		</p>
	
	<form enctype="multipart/form-data" action="export.php" method="post">
		<ul>
	<li>Choisissez les types de rapports vierges à importer dans la base</br>
	global $typesRapports;
	foreach($typesRapports as $type => $name)
		echo '<input type="checkbox" name="types[]" value="'.$type.'">'.$name.'</input><br/>'."\n";
	</li>
	<li>Choisissez les champs à importer</br>
	<input type="checkbox" name="fields[]" value="nomprenom" checked>Nom et prénom (dans le même champ)</input><br/>
	<input type="checkbox" name="fields[]" value="nom">Nom</input><br/>
	<input type="checkbox" name="fields[]" value="prenom">Prénom</input><br/>
	<input type="checkbox" name="fields[]" value="unite" checked>Code unité</input><br/>
	<input type="checkbox" name="fields[]" value="directeur" checked>DU</input><br/>
	<input type="checkbox" name="fields[]" value="grade_rapport" checked>Grade (rapport)</input><br/>
	<input type="checkbox" name="fields[]" value="rapporteur" checked>Rapporteur</input><br/>
	<input type="checkbox" name="fields[]" value="rapporteur2" checked>Rapporteur2</input><br/>
	</li>
	</ul>
	<p>
	<input type="submit" name="bouton" value="Télécharger trame" />
	<input type="hidden" name="type" value="exempleimportcsv"/> 
	<input type="hidden" name="action" value="export"/> 
	</p>
	</form>
	<p>Pour chaque type de rapport, copiez les données depuis le fichier du SGCN dans la trame téléchargée.<br/>
	Si vous utilisez les champs "rapporteur" ou "rapporteur2", renseignez les avec les logins des rapporteurs.<br/>
	Importez dans Marmotte la liste de rapports ainsi obtenue en utilisant le menu suivant.
	</p>
	Enfin utiliser de préférence l'encodage utf-8 pour les caractères accentués.<br/>
	 -->
<form enctype="multipart/form-data" action="index.php" method="post">
<table>
<tr><td>
	<input type="hidden" name="type" value="evaluations"></input>
	<input	type="hidden" name="action" value="upload" />
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
		</td></tr>
		<tr><td>Fichier</td><td> <input name="uploadedfile"
		type="file" />
		</td></tr>
		<tr><td>Choix du type de rapport</td><td>
		<select name="subtype">
		<option value ="">Autodétection</option>
<?php   
$types = array();
if(is_current_session_concours())
{
	global $typesRapportsConcours;  
	$types = $typesRapportsConcours;
}
else if(is_current_session_delegation())
{
	$types = array('Delegation');
}
else
{
	global $typesRapportsChercheurs;
	global $typesRapportsUnites;
	$types = array_merge($typesRapportsChercheurs, $typesRapportsUnites);
}
foreach($types as $type => $name)  
echo '<option value='.$type.'>'.$name.'</option><br/>'."\n";  
?> 
</select>
		</td></tr>
				<tr><td><input type="submit" value="Créer rapports" /></td></tr>
		
	</table>
	
</form>
<!--  
	<p>
	Vous pouvez supprimer les colonnes inutiles mais il est indispensable de
	laisser les intitulés des colonnes restantes tels quels.<br />
	</p>
	-->
<hr />

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
	
<h2>Mise à jour d'un ou plusieurs rapports</h2>
<p>
Le formulaire suivant vous permet d'importer un ou plusieurs rapports édités offline.<br/>
Le fichier à importer doit avoir été récupéré au préalable via la fonction Export au format csv.
</p>
<?php 
	displayImport();
	?>

	
<?php 
if(isSecretaire())
{
	?>
	<hr/>


<?php 
/*
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
*/

?>
	
	<?php 
	displaySecretaryImport();
	?>


<h2>Ajout et mise à jour des unités  (pour Secrétaires)</h2>
<p>
<p>
	Le formulaire ci-dessous permet d'injecter des unités dans la base de
	donnée.<br /> Les rapports sont envoyés sous forme de fichier csv fournis par votre ACN.<br />
	Si votre ACN ne connaît pas la procédure, dites-lui de se rapprocher de Florence Colombo.<br/>
	Les données des labos déjà renseignés dans Marmotte seront remplacées.
	
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
	?>

