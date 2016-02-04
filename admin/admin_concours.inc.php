
<h2 id="concours">Concours</h2>
<hr />
<?php
   if(isSuperUser())
     {
?>
   <p>Ce menu permet de supprimer entièrement un concours de la base de données, y compris les fichiers pdf.</p>
<?php
       $all_concours=array();
       $sql = "SELECT DISTINCT id_session FROM reports";
       $result = sql_request($sql);
       while($row = mysqli_fetch_object($result))
	 if(strpos($row->id_session,"Concours") !== false)
	   $all_concours[]=$row->id_session;
       ?>
<form method="post" onsubmit="return confirm('Etes vous complètement sur de vouloir supprimer les données de ce concours pour toutes les sections?);">
       <form>
<select name="sessionid">
<?php
   foreach($all_concours as $concours)
   echo "<option value=\"".$concours."\">".$concours."</option>";
?>
</select>
<br/>
	<input type="hidden" name="supprimerdossiers"></input>
	<input type="hidden" name="admin_concours"></input>
       <input  type="hidden" name="action" value="delete_concours" />
	<input type="submit" value="Supprimer entièrement ce concours de la base de données" />
</form>
<?php
     }
   else
     {
?>
<h3>Liste des concours</h3>
<table class="stats">
	<?php 
	$concours = getConcours();
	echo "<tr><th> Code </th><th> Intitule </th><th>Postes</th><th>Institut</th><th>Intitule Complet";
	echo "<th>SecJury1</th><th>President1</th><th>SecJury2</th><th>President2</th><th>SecJury3</th><th>President3</th><th>SecJury4</th><th>President4</th>";
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
    Le champ 'intitulé' permet d associer un mot clé avec un concours, utilisé pour affichage dans Marmotte.<br />
	Si le jury est plénier ou si vous ne connaissez pas encore la liste de
	vos sections de jurys, laisser les champs "SecJury*" et "President*" vides.<br />

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
			<td>Section de Jury<?php echo $i;?> <input name="sousjury<?php echo $i;?>" />
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
<h3>Affecter les sections de jurys aux candidats</h3>
<p>Cette fonction affecte automatiquement chaque candidat à la section de jury
	auquel appartient son premier rapporteur.</p>

<form method="post" action="index.php"
	onsubmit="return confirm('Affecter les sous-jurys?');">
	<input type="hidden" name="action" value="affectersousjurys" /> <input
		type="submit" value="Affecter sections de jurys" /> <input type="hidden"
		name="admin_concours"></input>
</form>
<br />
<h3>Reinitialiser les conflits</h3>
    <p>Cette fonction permet de supprimer tous les conflits d&apos;intérêts.</p>
<form method="post" action="index.php"
	onsubmit="return confirm('Réinitialiser les conflits?');">
	<input type="hidden" name="action" value="reinitialiserconflits" /> <input
		type="submit" value="Reinitialiser les conflits" /> <input type="hidden"
		name="admin_concours"></input>
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
<?php
	    }
?>