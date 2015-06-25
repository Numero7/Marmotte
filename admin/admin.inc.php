<?php
$password = genere_motdepasse();
require_once('config_tools.inc.php');
require_once('generate_csv.inc.php');
require_once('manage_unites.inc.php');

$admin_sessions = isset($_REQUEST["admin_sessions"]) && isSecretaire();
$admin_maintenance = isset($_REQUEST["admin_maintenance"]) && isSecretaire();
$admin_users = isset($_REQUEST["admin_users"]) && isSecretaire();
$admin_concours = isset($_REQUEST["admin_concours"]) && isSecretaire() && !isSuperUser();
$admin_config = isset($_REQUEST["admin_config"]) && isSecretaire();
$admin_keywords = isset($_REQUEST["admin_keywords"]) && isSecretaire();
$admin_rubriques = isset($_REQUEST["admin_rubriques"]) && isSecretaire() && !isSuperUser();
$admin_migration = isset($_REQUEST["admin_migration"]) && isSuperUser();
$admin_unites = isset($_REQUEST["admin_unites"]) && isSecretaire();


if(isSecretaire())
{
	?>
<h1>Interface d'administration</h1>
<ul>
	<?php 
	if(isSecretaire() && !isSuperUser())
	{
		?>
	<li><a href="index.php?action=admin&amp;admin_sessions=">Sessions</a></li>
	<?php 
	}
	if(isSecretaire())
	{
		?>
	<li><a href="index.php?action=admin&amp;admin_users=">Membres</a></li>
	<li><a href="index.php?action=admin&amp;admin_unites">Unités</a>
	
	<li><a href="index.php?action=admin&amp;admin_config=">Configuration</a>
	</li>
	<?php 
	}
	if(isSecretaire() && !isSuperUser())
	{
 if( is_current_session_concours() )
{
		?>
	<li>
	<a href="index.php?action=admin&amp;admin_concours=">Concours</a></li>
<?php
}
?>

	<li><a href="index.php?action=admin&amp;admin_rubriques=">Rubriques</a>
	</li>
	<li><a href="index.php?action=admin&amp;admin_keywords=">Mots-clés</a>
	</li>
	<?php 
	}
	if(isSuperUser())
	{
		?>
	<li><a href="index.php?action=admin&amp;admin_migration=">Migration</a>
	</li>
	<?php 
	}
	if(isSecretaire())
	{
	?>
	<li><a href="index.php?action=admin&amp;admin_maintenance=">Synchronisation</a></li>
	<?php 
	}
?>
</ul>

<hr />
<hr />

<?php 	

if($admin_maintenance)
{
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
Le lien suivant permet de synchroniser Marmotte avec les bases de donnees d'e-valuation.<br/>
<a href="index.php?action=synchronize_with_dsi&amp;admin_maintenance=">Synchroniser avec e-valuation.</a>
</p>
<?php
}

if($admin_sessions)
{
	?>
<h2 id="sessions">Sessions</h2>
<?php 
include 'admin/admin_sessions.php';
?>
<hr />
<?php 
}

if($admin_users)
	include "admin_users.inc.php";

if($admin_unites)
	include "admin_units.php";

if($admin_concours)
{
	if( is_current_session_concours() )
	{
		?>
<h2 id="concours">Concours</h2>
<hr />

<h3>Liste des concours</h3>
<table>
	<?php 
	$concours = getConcours();
	echo "<tr><th> Code </th><th> Intitule </th><th>Postes</th>";
	echo "<th>SousJury1</th><th>President1</th><th>SousJury2</th><th>President2</th><th>SousJury3</th><th>President3</th><th>SousJury4</th><th>President4</th>";
	echo "</tr>";
	foreach($concours as $conc)
	{
		echo "<tr>";
		echo "<td><b>".$conc->code . "</b></td><td>". $conc->intitule. "</td><td>".$conc->postes;
		for($i = 1; $i <= 4; $i++)
		{
			$suff = "sousjury".$i;
			$suffp = "president".$i;
			$suffm = "membressj".$i;
			echo "</td><td>".$conc->$suff. "</td><td>".$conc->$suffp;
		}
		echo "</td></tr>";
	}
	?>
</table>

<br />
<hr />
<h3>Ajouter ou mettre à jour un concours</h3>
<p>
	Ce menu permet d'ajouter ou de mettre à jour un concours.<br /> Le code
	du concours doit être numérique, par exempe "0602", les caractères
	non-numériques seront supprimés automatiquement.</br> L'intitulé du
	concours doit être court, par exemple "CR2" ou "CR2_Coloriage".<br />
	Si le jury est plénier ou si vous ne connaissez pas encore la liste de
	vos sous-jurys, laisser les champs "SousJury*" et "President*" vides.<br />

</p>
<form method="post" action="index.php">
	<input type="hidden" name="admin_concours"></input>
	<table>
		<tr>
			<td>code <input name="code" value="0601"></input>
			</td>
			<td>niveau <select name="niveau">
					<option value="CR">CR</option>
					<option value="DR">DR</option>
			</select>
			</td>
			<td>intitule <input name="intitule" value="DR2"></input>
			</td>
			<td>postes <select name="postes">
					<?php for($i = 0 ; $i < 100; $i++) echo "<option value=\"".$i."\">".$i."</option>"; ?>
			</select>
			</td>
		</tr>
		<tr>
			<?php 

			for($i = 1; $i <= 4; $i++)
			{
				$suff = "sousjury".$i;
				$suffp = "president".$i;
				$suffm = "membressj".$i;
				?>
			<td>SousJury<?php echo $i;?> <input name="sousjury<?php echo $i;?>" />
			</td>
			<td>President<?php echo $i;?> <select
				name="president<?php echo $i;?>">
					<option value=""></option>
					<?php 
					$users = listUsers();
					foreach($users as $user => $data)
						echo "<option value=\"$user\">".ucfirst($data->description)."</option>";
					?>
			</select>
			</td>
			<?php 
			if($i == 2) echo "</tr><tr>";
			}

			?>

		</tr>
	</table>
	<input type="hidden" name="admin_concours"></input> <input
		type="hidden" name="action" value="add_concours" />
	<input type="submit" value="Ajouter / Mettre à jour" />
</form>
<br />
<hr />
<h3>Changer le statut du concours</h3>
<p>Cette fonction permet de changer le statut du concours au fur et à
	mesure de son avancement.</p>
<ul>
	<li>IE: avant et pendant l'IE</li>
	<li>JAD: avant et pendant le JAD</li>
	<li>audition: avant et pendant les auditions</li>
	<li>admissibilité: avant et pendant le jury d'admissibilité</li>
	<li>rapports: préparation des rapports sur les candidats classés et
		auditionnés</li>
	<li>transmis: rapports transmis au jury d'admission</li>
</ul>
<?php
$concours = getConcours();
foreach($concours as $conc)
{
	echo "<B>".$conc->intitule."</B>";
	?>
<form method="post" action="index.php">
	<input type="hidden" name="admin_concours" value="" /> <input
		type="hidden" name="action" value="statutconcours" /> <input
		type="hidden" name="code" value="<?php echo $conc->code; ?>" /> <select
		name="statut">
		<?php 
		global $statuts_concours;
		foreach($statuts_concours as $code => $intitule)
		{
			$visible = ($conc->statut == $code) ? " selected=\"selected\" " : "";
			echo "<option value=\"".$code."\" ".$visible." >".$intitule."</option>";
		}
		?>
	</select> <input type="submit" value="Changer statut" />
</form>
<?php 
}
?>
<h3>Affecter les sous-jurys</h3>
<p>Cette fonction affecte automatiquement chaque candidat au sous-jury
	auquel appartient son premier rapporteur.</p>

<form method="post" action="index.php"
	onsubmit="return confirm('Affecter les sous-jurys?');">
	<input type="hidden" name="action" value="affectersousjurys" /> <input
		type="submit" value="Affecter sous-jurys" /> <input type="hidden"
		name="admin_concours"></input>
</form>
<br />
<hr />

<h3>Supprimer un concours</h3>
<p>Ce menu permet de supprimer un concours.</p>
<form method="post" action="index.php">
	<input type="hidden" name="admin_concours"></input>
	<?php 
	$concours = getConcours();
	echo " Concours <select name=\"code\">\n";
	foreach($concours as $conc)
		echo "<option value=\"$conc->code\">".$conc->code." ".$conc->intitule."</option>\n";
	echo "</select>\n";

	?>
	<input type="hidden" name="action" value="delete_concours" /> <input
		type="submit" value="Supprimer" />
</form>
<br />
<hr />
<h3>Rapports JAD</h3>
<form method="post" action="export.php">
	<input type="submit" value="Générer les rapports de JAD" /> <input
		type="hidden" name="action" value="export" /> <input type="hidden"
		name="type" value="jad">
	<table>
		<tr>
			<th>Concours</th>
			<th>Preambule JAD</th>
		</tr>
		<?php 
		foreach($concours as $conc)
		{
			?>
		<tr>
			<td><?php  echo "<b>".$conc->code."</b>"; ?>
			</td>
			<td><?php 
			$key = "preambule_jad_".$conc->code;
			$text = remove_br(get_config($key));
			if($text == "")
				$text = "Renseigner ici le preambule du rapport de JAD pour le concours ".$conc->code.". Laisser vide si un rapport de JAD n'est pas nécessaire.";
			echo '<textarea  rows="25" cols="60" name="'.$key.'">'.$text ."</textarea>";
			?>
			</td>
			<?php 
		}
		?>
	
	</table>
</form>

<?php 
	}
}

if($admin_config)
{
	?>
<h2 id="config">Configuration</h2>
<form>
	<input type="hidden" name="admin_config"></input>
	<table>
		<tr>

			<?php 
			echo "<tr><th>Clé</th><th>Valeur</th><th>Description</th></tr>\n";
			$configs = array(
					"section_shortname"=> array("intitulé court de la section ou CID","Section 6"),
					"section_intitule"=> array("intitulé long de la section","Sciences de l\'information : fondements de l\'informatique, calculs, algorithmes, représentations, exploitations"),
					"president_titre" => array("titre du président, utilisé pour signer les rapports", "Président de la Section 6"),
					"president" => array("nom du président, utilisé pour signer les rapports", "Alan Türing"),
					"webmaster" => array("adresse email de l'expéditeur des emails", "alan.turing@cnrs.fr"),
					"webmaster_nom" => array("signataire des emails et pdfs", "Alan Türing"),
					"welcome_message" => array("message d'accueil", "Bienvenue sur le site de la section 6")
			);
			foreach($configs as $key => $data)
			{
				$value = $data[1];
				if(isset($_SESSION["config"][$key]))
					$value = $_SESSION["config"][$key];
				echo "<tr><td>$key</td><td><input style=width:500px value=\"".$value."\" name=\"".$key."\"></input></td><td>".$data[0]."</tr>\n";
			}
			?>
		
		
		<tr>
			<td><input type="hidden" name="action" value="updateconfig" /> <input
				type="submit" value="Enregistrer configuration" />
			</td>
		</tr>
	</table>
</form>
<hr />
<?php 
if(!isSuperUser())
{
?>
<h2>Signature président</h2>
<p>Le formulaire ci-dessous permet d'uploader la signature du président
	sous forme d'un fichier image au format jpeg.</p>
<p>La signature du président est automatiquement incorporée dans un pdf
	si le statut du rapport est "rapport publié" et si le rapport n'est pas
	un rapport de concours (contraintes légales).</p>

<form enctype="multipart/form-data" action="index.php" method="post">
	<input type="hidden" name="type" value="signature" /> <input
		type="hidden" name="action" value="upload" /> <input type="hidden"
		name="MAX_FILE_SIZE" value="100000" /> Fichier de signature: <input
		name="uploadedfile" type="file" /> <input type="submit"
		value="Uploader signature" />
</form>
<hr />
<?php 
}
}
if($admin_keywords)
{
	?>
<h2 id="motscles">Mots-clés de la section</h2>
<table>
	<?php 
	$configs = get_topics();
	echo '<tr><th>Index</th><th>Mot-clé</th><th></th></tr>';
	foreach($configs as $key => $value)
		echo '<tr><td>'.$key.'</td><td>'.$value.'</td></tr>';
	?>
</table>
<form>
	<input type="hidden" name="admin_keywords"></input>
	<table>
		<tr>
			<td>Index primaire
			<select name="index_primaire">
			<?php 
			for($i = 1; $i < 50; $i++)
				echo '<option value="'.$i.'">'.$i.'</option>';
				?>
			</select>
			</td>
			<td>Index secondaire
			<select name="index_secondaire">
			<?php 
				echo '<option value=""></option>';
			foreach(range('a', 'z') as $i)
				echo '<option value="'.$i.'">'.$i.'</option>';
				?>
			</select>
			</td>
			<td>Mot-clé <input name="motcle"></input>
			</td>
			<td><input type="hidden" name="action" value="addtopic" /> <input
				type="submit" value="Ajouter mot-clé" />
			</td>
		</tr>
	</table>
</form>
<form>
	<input type="hidden" name="admin_keywords"></input>
	<table>
		<tr>
			<td><select name='index'>
					<?php 
					foreach($configs as $key => $value)
					{
						if(strlen($value)> 30)
							$value = substr($value,0,30);
						echo '<option value="'.$key.'">'.$key.' '.$value.'</option>';
					}
					?>
			</select>
			</td>
			<td><input type="hidden" name="action" value="removetopic" /> <input
				type="submit" value="Supprimer mot-clé" />
			</td>
		</tr>
	</table>
</form>
<hr />
<?php 
}

if($admin_rubriques)
{
	?>
<h2 id="rubriques">Rubriques supplémentaires</h2>
<?php 
global $rubriques_supplementaires;
foreach($rubriques_supplementaires as $field => $intitule)
{
	?>
<h3 <?php echo "id=\"rubriques".$field."\"";?>>
	Rubriques
	<?php echo $intitule[2];?>
</h3>
<table>
	<?php 
	$rubriques = get_rubriques($field);
	if(count($rubriques) > 0)
	{
		echo '<tr><th>Index</th><th>Rubrique</th></tr>';
		foreach($rubriques as $index => $rubrique)
			echo '<tr><td>'.$index.'</td><td>'.$rubrique.'</td></tr>';
	}
	?>
</table>
<br />
<form>
	<input type="hidden" name="admin_rubriques"></input>
	<table>
		<tr>
			<td>Index <select name="index">
					<?php 
					for($i = 0; $i <= 10;$i++)
						echo "<option value=\"".$i."\">".$i."</option>"
						?>
			</select>
			
			<td>Rubrique <input name="rubrique"></input>
			</td>
			<td><input type="hidden" name="type" value="<?php echo $field;?>" />
				<input type="hidden" name="action" value="addrubrique" /> <input
				type="submit" value="Ajouter rubrique <?php echo $intitule[2];?>" />
			</td>
		</tr>
	</table>
</form>
<?php 
if(count($rubriques) > 0)
{
	?>
<form>
	<input type="hidden" name="admin_rubriques"></input>
	<table>
		<tr>
			<td><select name='index'>
					<?php 
					foreach($rubriques as $index => $value)
						echo '<option value='.$index.'>'.$index.' '.$value.'</option>';
					?>
			</select>
			</td>
			<td><input type="hidden" name="type" value="<?php echo $field;?>" />
				<input type="hidden" name="action" value="removerubrique" /> <input
				type="submit" value="Supprimer rubrique <?php echo $intitule[2];?>" />
			</td>
		</tr>
	</table>
</form>
<hr />
<?php 
}
?>
<br />
<br />
<?php 
}
}
}


if($admin_migration)
{
	?>
	<!-- 
<h2>Migration depuis Marmotte 1.0</h2>
<form method="post" action="index.php">
	<table>
		<?php 
		global $serverlogin;
		global $serverpassword;

		$inputs = array("section" => "6", "db_ip" => "127.0.0.1", "db_name" => "cn6", "db_user" => $serverlogin, "db_pass" => $serverpassword);
		foreach($inputs as $input => $val)
			echo "<tr><td>".$input."</td><td><input name=\"".$input."\" value=\"".$val."\"></input></td></tr>";
		?>
		<tr>
			<td><?php 
			$types = array("users","reports","people","sessions","units");
			foreach($types as $type)
				echo $type.'<input type="checkbox" name="'.$type.'"envoiparemail" />';
			?> <input type="hidden" name="action" value="migrate" /> <input
				type="submit" value="Migrer" />
			</td>
		</tr>
	</table>
</form>
 -->	
<h2>Migration depuis Marmotte 2.1</h2>
<p>
<a href="index.php?action=migrate_to_eval_codes&amp;admin_maintenance=">Migrer les codes d'évaluation.</a>
<p/>
<!-- 
<h2>Purge dossiers</h2>
<h3>Purge historique</h3>
<h3>Purge session</h3>
-->
<?php 
	}
	?>
