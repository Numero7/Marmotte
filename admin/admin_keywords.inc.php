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
