<?php 

require_once('config.inc.php');
require_once('utils.inc.php');
require_once('manage_users.inc.php');
require_once('manage_unites.inc.php');
require_once('manage_rapports.inc.php');

function display_sousjury($row, $fieldId, $readonly)
{
	global $sous_jurys;
	$sousjurys = (isset($row->concours) && isset($sous_jurys[substr($row->concours,-4,4)])) ? $sous_jurys[$row->concours] : array();

	display_select($row, $fieldId, $sousjurys,$readonly);
}

function display_type($row, $fieldID, $readonly)
{
	global $typesRapports;
	if(isset($row->type))
	{
		$type = $row->type;

		if( !$readonly )
		{
			global $typesRapportsChercheurs;
			global $typesRapportsUnites;
			global $typesRapportsConcours ;

			if(isset($typesRapportsChercheurs[$type]))
				display_select($row, $fieldID, $typesRapportsChercheurs,$readonly);
			else if(isset($typesRapportsUnites[$type]))
				display_select($row, $fieldID, $typesRapportsUnites,$readonly);
			else if(isset($typesRapportsConcours[$type]))
				display_select($row, $fieldID, $typesRapportsConcours,$readonly);

		}
	}
}

function displaySessionField($row, $fieldID, $readonly)
{
	?>
<td><input type="hidden" name="fieldid_session"
	value="<?php echo $row->id_session;?>" />
</td>
<?php 
}

function display_long($row, $fieldID, $readonly)
{
	if($readonly)
		echo '<td colspan="3">'.$row->$fieldID.'</td>';
	else
		echo '
		<td colspan="3">
		<textarea  rows="5" cols="60" name="field'.$fieldID.'" >'.remove_br($row->$fieldID).'</textarea>
		</td>
		';
}

function display_treslong($row, $fieldID, $readonly)
{
	if($readonly)
		echo '<td colspan="3">'.$row->$fieldID.'</td>';
	else
		echo '
		<td colspan="3">
		<textarea  rows="25" cols="60" name="field'.$fieldID.'" >'.remove_br($row->$fieldID).'</textarea>
		</td>
		';
}

function display_short($row, $fieldID, $readonly)
{
	echo "<td>\n";
	if(!$readonly)
	{
		?>
<input name="field<?php echo $fieldID;?>"
	value="<?php echo $row->$fieldID;?>" style="width:90%;" />
<?php
	}
	else
		echo $row->$fieldID;
	echo "</td>\n";
}

function display_avis($row, $fieldID, $readonly)
{
	global $typesRapportToAvis;
	if(isset($row->type) && array_key_exists($row->type, $typesRapportToAvis))
		display_select($row, $fieldID, $typesRapportToAvis[$row->type], !isSecretaire() && $readonly);
	else
		echo '<td></td>';
}

function display_rapporteur($row, $fieldID, $readonly)
{
	display_select($row, $fieldID, listNomRapporteurs(),$readonly);
}

function display_unit($row, $fieldID, $readonly)
{
	display_select($row, $fieldID, simpleUnitsList(true),$readonly);
}

function display_select($row, $fieldID, $liste,$readonly)
{
	echo "<td>\n";
	$current_value = isset($row->$fieldID) ? $row->$fieldID : '';

	if($readonly)
	{
		echo isset($liste[$current_value]) ? $liste[$current_value] : $current_value;
	}
	else
	{
		?>
<select  name="field<?php echo $fieldID;?>">
	<?php
	foreach($liste as $value => $text)
	{
		$sel = ($value == $current_value) ? "selected=\"selected\"" : "";
		echo  "\t\t\t\t\t<option value=\"".($value)."\" ".$sel.">".substr($text, 0,50)."</option>\n";
	}
	?>
</select>
<?php
	}
	echo "</td>\n";
}

function display_enum($row, $fieldID, $readonly)
{
	global $enumFields;

	if(!isset($enumFields[$fieldID]))
		throw new Exception("Enum field ".$fieldId." should be indexed in list enumFields");
	display_select($row, $fieldID,$enumFields[$fieldID],$readonly);
}

function display_topic($row, $fieldID, $readonly)
{
	global $topics;
	display_select($row, $fieldID,$topics,$readonly);
}

function display_statut2($row, $fieldID, $readonly)
{
	global $statutsRapports;
	display_select($row, $fieldID,$statutsRapports,$readonly);
}

function display_grade($row, $fieldID, $readonly)
{
	global $grades;
	foreach( $grades as $title => $value)
		$grades[$title] = $title;
	display_select($row, $fieldID,$grades,$readonly);
}

function display_concours($row, $fieldID, $readonly)
{
	global $concours_ouverts;
	display_select($row, $fieldID,array_merge(array(""=>""), $concours_ouverts),$readonly);
}

function display_ecole($row, $fieldID, $readonly)
{
	echo '<td colspan="3"><input name="fieldecole" value="'.$row->ecole.'" style="width: 100%;"/> </td>';
}

function display_rapports($row, $fieldId)
{
	global $typesRapportsUnites;

	$is_unit= isset($typesRapportsUnites[$row->type]);

	if($is_unit)
		$reports = isset($row->unite) ? find_unit_reports($row->unite) : array();
	else
		$reports = (isset($row->nom) && isset($row->prenom)) ? find_people_reports($row->nom, $row->prenom) : array();



	global $typesRapports;

	echo "<td>";
	if(count($reports) > 1)
	{
		echo "<table>";

		foreach($reports as $report)
		{
			if((!isset($row->id) || $report->id != $row->id) && (!$is_unit || isset($typesRapportsUnites[$report->type])))
			{
				if(isset($typesRapports[$report->type]))
					$type = $typesRapports[$report->type];
				else
				{
					$type = "Unknown";
				}
				echo '<tr><td><a href="index.php?action=edit&amp;id='.$report->id.'">'.$report->id_session. " - " .$type."</a></td></tr>";
			}
		}

		echo "</table>";
	}

	echo "</td>";

}


function display_fichiers($row, $fieldID, $session, $readonly)
{
	global $dossiers_candidats;

	echo "<td>";
	$dir = get_people_directory($row, $session, isSecretaire());
	
	
	$files = find_people_files($row,true, $session, isSecretaire());
	if(count($files) > 0)
	{
		ksort($files);

		$i = -1;
		echo "<table><tr><td><table>\n";
		echo '<tr><td style="padding-right: 10px">';

		$nb = intval((count($files) + 2)/ 3);

		foreach($files as $date => $file)
		{
			if($i % $nb	 == $nb - 1 )
				echo '</td><td style="padding-right: 10px">';

			$prettyfile = str_replace("_", " ", $file);
			if(strlen($file) > 20)
			{
				$arr = array(strtolower($row->nom), strtolower($row->prenom));
				$arr2 = array("","");
				$prettyfile = str_replace($arr, $arr2, $prettyfile);
			}
			echo '<a href="'.$dir."/".$file.'">'.$prettyfile."</a><br/>\n";
			$i++;
		}
		echo '</td></tr>';
		echo "</table>\n";
		echo "</td><td>";

		foreach($files as $date => $file)
			if(is_picture($file))
			echo '<img class="photoid" src="'.$dir."/".$file.'" alt="'.$file.'" />';

		echo "</td></tr></table>";
	}
	
			?>
<input type="hidden" name="type"
	value="candidatefile" />
<input
	type="hidden" name="MAX_FILE_SIZE" value="10000000" />
	<input type="hidden" name="uploaddir" value=<?php echo $dir;?>/>
	<input name="uploadedfile"
	type="file" />
<input
	type="submit" name="ajoutfichier" value="Ajouter fichier" />
<?php 
		
		if(isSecretaire() && (count($files) > 0))
		{
			?>
<input type="hidden" name="type"
	value="candidatefile" />
<select name="deletedfile">
	<?php
	foreach($files as $date => $file)
	{
		echo  "<option value=\"".$dir."/".$file."\" >".$file."</option>\n";
	}
	?>
</select>
<input
	type="submit" name="suppressionfichier" value="Supprimer fichier" />
<?php 
		}
		else
		{
			$pictures = array();
			foreach($files as $date => $file)
				if(is_picture($file))
				$pictures[] = $file;

			if(count($pictures)  > 0  )
			{
				?>
<input type="hidden" name="type"
	value="candidatefile" />
<select name="deletedfile">
	<?php
	foreach($pictures as $file)
	{
		echo  "<option value=\"".$dir."\" >".$file."</option>\n";
	}
	?>
</select>
<input
	type="submit" name="suppressionfichier" value="Supprimer photo" />
<?php 
			}

		}

	

	echo "</td>";
}
?>