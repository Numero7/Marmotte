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
		else
		  {
		    global $typesRapportsAll;
		    if(isset($typesRapportsAll[$row->type]))
		      echo "&nbsp;&nbsp;".$typesRapportsAll[$row->type];
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
		echo '<td>'.$row->$fieldID.'</td>';
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
	value="<?php echo $row->$fieldID;?>"/>
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
		display_select($row, $fieldID, $typesRapportToAvis[$row->type], $readonly);
	else
		echo '<td></td>';
}

function display_rapporteur($row, $fieldID, $readonly)
{
  $liste = array(""=>"");
  $users = listUsers();
 $concours_ouverts = getConcours();
 
 foreach($users as $user => $data)
    {
      /*
		  if(isset($row->concours) 
		     && $row->concours != "" 
		     && isset($concours_ouverts[$row->concours])		     
		     && !in_array($row->rapporteur,$concours_ouverts[$row->concours]->jures))
		    continue;
      */
      if(is_rapporteur_allowed($data,$row))
	{
	  $liste[$user] = $data->description;
	}
    }
  display_select($row, $fieldID, $liste,$readonly);
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
			  "AVIS",
			  "COMMENTAIRE_RC1",
			  "COMMENTAIRE_RC2",
			  "COMMENTAIRE_RC3",
			  "DATE_ENVOI_AR",
			  "DATE_DROIT_REPONSE",
			  "DKEY",
			  "DATE_MODIFICATION",
			  "DATE_CREATION",
			  "LIB_SESSION",
			  "ANNEE",
			  "CADRE_EVAL")
			  ;
  $report = null;
  if(isset($row->DKEY))
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
	$statuts = $statutsRapports;
	/*
	if($row->$fieldID == "avistransmis")
	    $statuts = array(
			     "avistransmis" => $statuts["avistransmis"],
			     "publie"=>$statuts["publie"]
			     );
	if($row->$fieldID == "publie")
	    $statuts = array(
			     "publie"=>$statuts["publie"]
			     );
	*/
	if( ($row->$fieldID == "avistransmis" || $row->$fieldID == "publie" ) && !isACN())
	  echo "&nbsp;&nbsp;".$statuts[$row->$fieldID];
	else if(isACN())
	  {
	global $statutsRapportsACN;
	    $statuts = $statutsRapportsACN;
	   if(!is_current_session_concours())
	     unset($statuts["audition"]);
	  display_select($row, $fieldID, $statuts, $readonly);
	  }
	else
	  {
	    global $statutsRapportsIndiv;
	    $statuts = $statutsRapportsIndiv;
	   if(!is_current_session_concours())
	     unset($statuts["audition"]);
	  display_select($row, $fieldID,$statuts,$readonly);
	  }
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
	
	$conc = is_current_session_concours();

	if($is_unit)
		$reports = isset($row->unite) ? find_unit_reports($row->unite) : array();
	else
		$reports = 
		  (isset($row->nom) && isset($row->prenom)) ?
		  find_people_reports($row->nom, $row->prenom)
		  : array();

	for($i = 0; $i < count($reports) ; $i++)
		{
		  global $typesRapportsAll;
		  $report = $reports[$i];
			if( ( !isset($row->id) || $report->id != $row->id ) )
			{
				if(isset($typesRapportsAll[$report->type]))
					$type = $typesRapportsAll[$report->type];
				else
					$type = "Unknown";
				if($conc && $report->type != REPORT_CANDIDATURE)
				  continue;
				if($report->type == REPORT_CANDIDATURE)
				  $reports[$i]->label =
				    $report->id_session. " - " .$report->concours;				
				else if( is_equivalence_type($report->type) )
				  $reports[$i]->label =
				    $report->id_session. " - " .$type." - " . $report->grade_rapport;
				else  if(is_rapport_unite($report))
				  $reports[$i]->label =
				    $report->id_session." - ".$report->unite. " - " .$type;
				else
				  $reports[$i]->label =
			         $report->id_session." - ".$report->unite." - ".$report->nom."  ".$report->prenom. " - " .$type." - ".$report->id." - ".$row->id;
				
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
		  echo '<tr><td><a href="index.php?action=edit&amp;id='.$report->id.'">';
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


function display_fichiers($row, $fieldID, $session, $readonly, $type, $subtype = "")
{
	if(!isset($row->type))
		return;
	
	if( $type == "marmotte" && is_rapport_unite($row) && ( !isset($row->unite) || $row->unite == "")) {
	    echo "Veuillez associer une unité à cette demande pour pouvoir ajouter des fichiers.";
	    return ;
	  }

	$files = find_files($row, $session, false,$type, $subtype);	

	echo "<td><table><tr>\n";
	
	if(count($files)== 0) {
	  echo "<td></td>\n";
	} else {
		$i = -1;
		echo "<td><table>\n";
		echo '<tr><td style="padding-right: 10px">';

		$nb = intval((count($files) + 2)/ 3);

		foreach($files as $label => $path)
		{
			echo '<a  target="_blank" href="export.php?evaluation=&amp;';
			echo 'action=get_file&amp;path=';
			echo urlencode($path);
			echo '&amp;filename='.urlencode($label).'">'.$label."</a><br/>\n";
			$i++;
		}
		echo '</td></tr>';
		echo "</table>\n";
		echo "</td><td>";


		foreach($files as $label => $file) {
		    if(strcontains($file,"jpg") && $type=="marmotte")
			  {
		   		  echo '<img class="photoid" src="'.$file.'" alt="'.$file.'" />';
			  }
		}
		echo "</td>";
	}
	echo "</tr>";
				
		if(!$readonly)
		{
			echo "<tr><td>";
			
			$dir = is_rapport_unite($row) ?  get_unit_directory($row, $session, false) :  get_people_directory($row, $session, true, $subtype);
			?>
			<table><tr><td>
<input

	type="hidden" name="MAX_FILE_SIZE" value="50000000" />
	<input type="hidden" name="uploaddir<?php echo $fieldID;?>" value="<?php echo $dir;?>"/>
	<input name="uploadedfile<?php echo $fieldID;?>[]"
	type="file" multiple />
			<input
	type="submit" name="ajout<?php echo $fieldID;?>" value="Ajouter fichier" />
			</td></tr><tr><td>
<input type="hidden" name="type"
	value="candidatefile" />
		</td></tr></table>
<?php 
		echo "</td></tr>";
		}

		
		if(!$readonly && isSecretaire() && (count($files) > 0))
		{
			echo "<tr><td>";
						
			?>
			<table><tr><td>
<input
	type="submit" name="suppression<?php echo $fieldID;?>" value="Supprimer fichier" />
	</td></tr><tr><td>
	<input type="hidden" name="type"
	value="candidatefile" />
	<table><tr><td>
<select name="deleted<?php echo $fieldID;?>">
	<?php
	foreach($files as $label => $path)
	{
		echo  "<option value=\"".$path."\" >".$label."</option>\n";
	}
	?>
</select>
	</td></tr></table>

<?php 
echo "</td></tr>";

		}
		echo "</table></td>\n";
}
?>