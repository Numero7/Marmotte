<h2 id="config">Configuration</h2>
<form>
	<input type="hidden" name="admin_config"></input>
	<table>
		<tr>
			<?php 
			echo "<tr><th>Description</th><th>Valeur</th></tr>\n";
global $configus;

$configs = $configus;

//if(isSuperUser())
//{
//$configs["sessions_synchro"]= array("Liste des sessions à synchroniser, séparées par des ';'", "Printemps2015;Automne2015");
//}

			foreach($configs as $key => $data)
			{
				$value = $data[1];
				if(isset($_SESSION["config"][$key]))
					$value = $_SESSION["config"][$key];
				if($value != "true" && $value != "false")
				  {
				  echo "<tr><td>".$data[0]."</td><td><input style=width:500px value=\"".$value."\" name=\"".$key;
				  echo "\"></input></td></tr>\n";
				  }
				else
				  {
				    $checked_true = ($value == "true") ? "checked" : "";
				    $checked_false = ($value == "false") ? "checked" : "";
				    echo "<tr><td>".$data[0]."</td><td>";
				    echo "&nbsp;&nbsp;<input type=\"radio\" value=\"true\" name=\"".$key."\" ".$checked_true.">Oui</input>&nbsp;&nbsp;";
				    echo "<input type=\"radio\" value=\"false\" name=\"".$key."\" ".$checked_false.">Non</input>";
				  echo "</td></tr>\n";
				  }
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
si le statut du rapport est "rapport publié" et si le rapport n&#39;est pas
	un rapport de concours (contraintes légales).</p>

<form enctype="multipart/form-data" action="index.php" method="post">
	<input type="hidden" name="type" value="signature" /> <input
		type="hidden" name="action" value="upload" /> <input type="hidden"
		name="MAX_FILE_SIZE" value="100000" /> Fichier de signature: <input
		name="uploadedfile" type="file" /> <input type="submit"
		value="Uploader signature" />
</form>
<?php
}
?>
<hr />
