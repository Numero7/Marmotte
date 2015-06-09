<?php 

require_once('config.inc.php');
require_once('utils.inc.php');
require_once('manage_users.inc.php');
require_once('manage_unites.inc.php');
require_once('manage_rapports.inc.php');

function display_sousjury($row, $fieldId, $readonly)
{
	global $tous_sous_jury;
	$souj = array();
	if( isset($row->concours) && isset($tous_sous_jury[$row->concours] ) )
		foreach($tous_sous_jury[$row->concours] as $code => $president)
			$souj[$code] = $code;

	display_select($row, $fieldId, $souj,$readonly);
}

function display_type($row, $fieldID, $readonly)
{
	if(isset($row->type))
	{
		$type = $row->type;

		if( !$readonly )
		{
			global $report_types_to_class;

			global $typesRapportsChercheurs;
			global $typesRapportsUnites;
			global $typesRapportsConcours ;
			
			if(isset($report_types_to_class[$type]))
			{
				switch($report_types_to_class[$type])
				{
					case REPORT_CLASS_CHERCHEUR:
						display_select($row, $fieldID, $typesRapportsChercheurs,$readonly);
						break;
					case REPORT_CLASS_UNIT:
						display_select($row, $fieldID, $typesRapportsUnites,$readonly);
					 	break;
					case REPORT_CLASS_CONCOURS:
						display_select($row, $fieldID, $typesRapportsConcours,$readonly);
						break;
					case REPORT_CLASS_DELEGATION:
						break;
					}
			}
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
		echo '<td>'.$row->$fieldID.'</td>';
	else
		echo '
		<td>
		<textarea  rows="5" cols="60" name="field'.$fieldID.'" >'.remove_br($row->$fieldID).'</textarea>
		</td>
		';
}

function display_treslong($row, $fieldID, $readonly)
{
	if($readonly)
		echo '<td>'.insert_br($row->$fieldID).'</td>';
	else
		echo '
		<td>
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
	$first = true;
	foreach($liste as $value => $text)
	{
		if(is_numeric($value))
			$value = strval($value);
		if(is_numeric($current_value))
			$current_value = strval($current_value);		
		if($first && $value != "")
			echo  "<option value=\"\"></option>\n";
		if($text == "") $text="   ";
		$sel = ($value === $current_value) ? "selected=\"selected\"" : "";
		echo  "\t\t\t\t\t<option value=\"".($value)."\" ".$sel.">".substr($text, 0,150)."</option>\n";
		$first = false;
	}
	
	?>
</select>
<?php
	}
	echo "</td>\n";
}

function display_dsi($row, $fieldID, $readonly)
{
  $ignored_fields = array(
			  "TYPE_EVAL",
			  "UNITE_EVAL",
			  "RAPPORTEUR",
			  "AVIS")
			  ;
	$report = getDSIReport($row->DKEY);
	echo "<br/>";
	if($report != null)
	{
		foreach($report as $field => $value)
		  if($value != "")
		    {
		      $ok = true;
		  foreach($ignored_fields as $f)
		    {
		    if(strcontains($field,$f))
		      $ok = false;
		    }
		  if($ok)
		    echo $field.":".$value."<br/>";
		    }
	}
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
	$conc = $concours_ouverts;
	$conc[""] = "";
	display_select($row, $fieldID,$conc,$readonly);
}

/*
function display_ecole($row, $fieldID, $readonly)
{
	echo '<td><input name="fieldecole" value="'.$row->ecole.'" style="width: 100%;"/> </td>';
}
*/

function display_rapports($row, $fieldId)
{
	$is_unit= is_rapport_unite($row);
	
	if($is_unit)
		$reports = isset($row->unite) ? find_unit_reports($row->unite) : array();
	else
		$reports = 
		  (isset($row->nom) && isset($row->prenom)) ?
		  find_people_reports($row->nom, $row->prenom)
		  : array();

	for($i = 0; $i < count($reports) ; $i++)
		{
		  $report = $reports[$i];
			if( ( !isset($row->id) || $report->id != $row->id ) )
			{
				if(isset($id_rapport_to_label[$report->type]))
					$type = $id_rapport_to_label[$report->type];
				else
					$type = "Unknown";
				if($type == REPORT_CANDIDATURE && isset($report->concours) )
				  $reports[$i]->label =
				    $report->id_session. " - " .$type." - " . $report->concours;
				else if( is_equivalence_type($type) )
				  $reports[$i]->label =
				    $report->id_session. " - " .$type." - " . $report->grade_rapport;
				else  if(is_rapport_unite($report))
				  $reports[$i]->label =
				    $report->id_session." - ".$report->unite. " - " .$type;
				else
				  $reports[$i]->label =
			         $report->id_session." - ".$report->nom."  ".$report->prenom. " - " .$type;
			}
		}

	global $id_rapport_to_label;
	echo "<td>";
	if(count($reports) > 1 && count($reports) < 10)
	{
		echo "<table>";
		foreach($reports as $report)
		{
		  if(isset($report->label))
		    {
		  echo '<tr><td><a href="index.php?action=view&amp;id='.$report->id.'">';
		  echo $report->label;
		  echo "</a></td></tr>";
		    }
		}
		echo "</table>";
	}
	else if(count($reports) > 10)
	{
		?>
	  <select onchange="window.location='index.php?action=read&amp;id=' + this.value;">
	    <?php
		foreach($reports as $report)
		{
		  if(isset($report->label))
		    {
?>
		  <option value="<?php echo $report->id; ?>">
		  <?php  echo $report->label; ?>
		  </option>
<?php
		}
		}
		echo "</table>";
	}
	echo "</td>";
}


function display_fichiers($row, $fieldID, $session, $readonly)
{
	if(!isset($row->type))
		return;
	
	$files = find_files($row, $session, false);	


	echo "<td><table><tr>\n";
	
	if( (count($files["marmotte"]) == 0) && (count($files["evaluation"]) == 0))
	  echo "<td></td>\n";
	else
	{
//		ksort($files);
		$i = -1;
//	echo "<td><table><tr>";
		echo "<td><table>\n";
		echo '<tr><td style="padding-right: 10px">';

		$nb = intval((count($files["marmotte"]) + count($files["evaluation"]) + 2)/ 3);

/*		foreach($dsifiles as )
		{
			if($i % $nb	 == $n
b - 1 )
				echo '</td></tr><tr><td style="padding-right: 10px">';
			echo '<a  target="_blank" href="export.php?dsi=&amp;action=get_file&amp;filename='.urlencode($path).'&amp;path='.urlencode($label).'">'.$label."</a><br/>\n";
			$i++;
		}
	*/	
		global  $dossier_stockage_dsi;
		//		echo "dossier_stockage_dsi : '".$dossier_stockage_dsi."'<br/>";
		foreach($files["evaluation"] as $label => $path)
		{
		  	if($i % $nb	 == $nb - 1 )
				echo '</td></tr><tr><td style="padding-right: 10px">';
			echo '<a  target="_blank" href="export.php?evaluation=&amp;';
			echo 'action=get_file&amp;path=';
			echo urlencode($dossier_stockage_dsi."/".$path);
			echo '&amp;filename='.urlencode($label).'">'.$label."</a><br/>\n";
			$i++;
		}
		foreach($files["marmotte"] as $label => $path)
		{
			if($i % $nb	 == $nb - 1 )
				echo '</td></tr><tr><td style="padding-right: 10px">';
			echo '<a  target="_blank" href="export.php?action=get_file&amp;path=';
			echo urlencode($path).'&amp;filename='.urlencode($label).'">'.$label."</a><br/>\n";
			$i++;
		}
		echo '</td></tr>';
		echo "</table>\n";
		echo "</td><td>";

		foreach($files["marmotte"] as $label => $file)
			if(strcontains($file,"id.jpg"))//, $needle) is_picture($file))
			echo '<img class="photoid" src="'.$file.'" alt="'.$file.'" />';
		echo "</td>";
				}
				echo "</tr>";
				
		if(!$readonly)
		{
			echo "<tr><td>";
			
			$dir = is_rapport_unite($row) ?  get_unit_directory($row, $session, false) :  get_people_directory($row, $session, false);
			?>
			<table><tr><td>
<input

	type="hidden" name="MAX_FILE_SIZE" value="10000000" />
	<input type="hidden" name="uploaddir" value="<?php echo $dir;?>"/>
	<input name="uploadedfile"
	type="file" />
			<input
	type="submit" name="ajoutfichier" value="Ajouter fichier" />
				<input
	type="submit" name="ajoutphoto" value="Ajouter photo" />
			</td></tr><tr><td>
<input type="hidden" name="type"
	value="candidatefile" />
		</td></tr></table>
<?php 
		echo "</td></tr>";
		}

		
		if(isSecretaire() && (count($files) > 0))
		{
			echo "<tr><td>";
						
			?>
			<table><tr><td>
<input
	type="submit" name="suppressionfichier" value="Supprimer fichier" />
	</td></tr><tr><td>
	<input type="hidden" name="type"
	value="candidatefile" />
	<table><tr><td>
<select name="deletedfile">
	<?php
	foreach($files["marmotte"] as $label => $path)
	{
		echo  "<option value=\"".$path."\" >".$label."</option>\n";
	}
	?>
</select>
	</td></tr></table>

<?php 
echo "</td></tr>";

		}
		else
		{
			
			$pictures = array();
			foreach($files["marmotte"] as $date => $file)
			  if(strcontains($file,"id.jpg"))
				$pictures[] = $file;

			if(count($pictures)  > 0  )
			{
			echo "<tr><td>";
				?>
	<table><tr><td>
	<input type="hidden" name="type"
	value="candidatefile" />
	<select name="deletedfile">
	<?php
	foreach($pictures as $file)
		echo  "<option value=\"".$dir."\" >".$file."</option>\n";
	?>
</select>
</td></tr>
</table>
<?php 
echo "</td></tr>";

			}
		}
		echo "</table></td>\n";
}
?>