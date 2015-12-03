<h2 id="concours">Concours</h2>
<hr />

<h3>Liste des concours</h3>
<table class="stats">
	<?php 
	$concours = getConcours();
	echo "<tr><th> Code </th><th> Intitule </th><th>Postes</th><th>Institut</th><th>Intitule Complet";
	echo "<th>SousJury1</th><th>President1</th><th>SousJury2</th><th>President2</th><th>SousJury3</th><th>President3</th><th>SousJury4</th><th>President4</th>";
	echo "</tr>";
	foreach($concours as $conc)
	{
		echo "<tr>";
		echo "<td><b>".$conc->code . "</b></td><td>". $conc->intitule. "</td><td>".$conc->postes."</td><td>".$conc->sigle_institut_conc;
		echo "</td><td>".$conc->intitule_conc_fr;
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
<h3>Configurer un concours</h3>
<p>
    Ce menu permet de configurer un concours.<br />
    L&#39;intitulé du concours doit être court et commencer par le grade, par exemple "CR2" ou "CR2_coloriage".<br />
	Si le jury est plénier ou si vous ne connaissez pas encore la liste de
	vos sous-jurys, laisser les champs "SousJury*" et "President*" vides.<br />

</p>
<form method="post" action="index.php">
	<input type="hidden" name="admin_concours"></input>
	<table>
		<tr>
			<td>
<select name="code">
<?php
	foreach($concours as $conc)
	{
	  echo "<option value=\"".htmlentities($conc->code)."\">".$conc->code."</option>\n";
	}

?>
</select>
			</td>
			<td>intitule <input name="intitule" value=""></input>
			</td>
<!--
			<td>postes <select name="postes">
					<?php for($i = 0 ; $i < 100; $i++) echo "<option value=\"".$i."\">".$i."</option>"; ?>
-->
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
	<input type="submit" value="Mettre à jour" />
</form>
<br />
<hr />
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
<!--
<hr />
<h3>Changer le statut du concours</h3>
<p>Cette fonction permet de changer le statut du concours au fur et à
	mesure de son avancement.</p>
<ul>
		    <li>IE: avant et pendant l&#39;IE</li>
	<li>JAD: avant et pendant le JAD</li>
	<li>audition: avant et pendant les auditions</li>
						      <li>admissibilité: avant et pendant le jury d&#39;admissibilité</li>
	<li>rapports: préparation des rapports sur les candidats classés et
		auditionnés</li>
   <li>transmis: rapports transmis au jury d&#39;admission</li>
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
<br/>

<hr/>

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
-->
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
			$key = "preambule_jad_".trim($conc->code,"\\/ ");
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
