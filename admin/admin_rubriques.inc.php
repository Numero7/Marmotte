<h2 id="rubriques">Rubriques supplémentaires</h2>
<?php 
global $rubriques_supplementaires;
foreach($rubriques_supplementaires as $field => $intitule)
{
	?>
<hr/>
<br />
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
<?php 
}
?>
<br />
<?php 
   }
?>