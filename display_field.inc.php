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
		$eval_type = $row->type;

		if( !$readonly )
		{

			$typesRapportsEvals = array();
			$typesRapportsEvals["Evaluation-Vague"]  = $typesRapports['Evaluation-Vague'];
			$typesRapportsEvals["Evaluation-MiVague"] = $typesRapports['Evaluation-MiVague'];

			display_select($row, $fieldID, $typesRapportsEvals,$readonly);
			echo "</tr>";
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
	value="<?php echo $row->$fieldID;?>" style="width: 100%;" />
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
		display_select($row, $fieldID, $typesRapportToAvis[$row->type],$readonly);
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
<select name="field<?php echo $fieldID;?>" style="width: 100%;">
	<?php
	foreach($liste as $value => $text)
	{
		$sel = ($value == $current_value) ? "selected=\"selected\"" : "";
		echo  "\t\t\t\t\t<option value=\"".($value)."\" ".$sel.">".$text."</option>\n";
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

function display_fichiers($row, $fieldID, $readonly)
{

	//echo "<td colspan=\"3\">";

	if($row->$fieldID != "")
	{
		$handle = false;
		if (is_dir(get_config("people_files_root").$row->$fieldID))
		{ $handle = opendir(get_config("people_files_root").$row->$fieldID); } 
		if($handle === false)
		{
			echo '<td><a href="'.get_config("people_files_root").$row->$fieldID."\">Fichiers candidats</a></td>\n";
		}
		else
		{
			$filenames = array();
			while(1)
			{
				$file = readdir($handle);
				if($file === false)
					break;
				else if($file != "." && $file != "..")
					$filenames[] = $file;
			}

			$i = 0;
			echo "<td><table>\n";
			foreach($filenames as $file)
			{
				if($i % 3	 ==0)
					echo '<tr>';
				$prettyfile = str_replace("_", " ", $file);
				if(strlen($file) > 20)
				{
					$arr = array(strtolower($row->nom), strtolower($row->prenom));
					$arr2 = array("","");
					$prettyfile = str_replace($arr, $arr2, $prettyfile);
				}
				echo '<td style="padding-right: 10px"><a href="'.get_config("people_files_root").$row->$fieldID."/".$file.'">'.$prettyfile."</a></td>\n";
				if($i % 3 ==3)
					echo '</tr>';
				$i++;
			}
			echo "</table></td>\n";

			closedir($handle);
		}
	}

	if(!$readonly)
	{
		?>

<input type="hidden" name="type"
	value="candidatefile" />
<input
	type="hidden" name="MAX_FILE_SIZE" value="10000000" />
<input name="uploadedfile"
	type="file" />
<input
	type="submit" name="ajoutfichier" value="Ajouter fichier" />
<?php 
	}
	//echo "</td>";

}
?>