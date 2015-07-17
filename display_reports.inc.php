<?php
require_once('utils.inc.php');
require_once('manage_filters_and_sort.inc.php');
require_once('manage_sessions.inc.php');
require_once('synchro.inc.php');

function displaySummary($filters, $filter_values, $sorting_values)
{
	global $fieldsSummary;
	global $fieldsSummaryConcours;
	global $statutsRapports;
	global $filtersReports;
	global $fieldsTypes;

	global $avis_classement;

	$rows = filterSortReports($filters, $filter_values, $sorting_values);

	$rows_id = array();
	foreach($rows as $row)
		$rows_id[] = $row->id;
	$_SESSION['rows_id'] = $rows_id;
	
	$_SESSION['current_id'] = 0;

	if(is_current_session_concours())
		$fields = $fieldsSummaryConcours;
	else
		$fields = $fieldsSummary;

	if( isset($filter_values["type"]) && $filter_values["type"] == "Promotion")
	{
		$filters["avis"]["liste"] = $avis_classement;
		$filters["avis1"]["liste"] = $avis_classement;
		//	reset_tri	$filters["avis2"]["liste"] = $avis_classement;
	}

	if(isSecretaire())
	  $fields[] = "statut";

	if($filter_values['type'] != $filters['type']['default_value'] )
	{
		$new_field = array();
		foreach($fields as $field)
			if($field != 'type')
			$new_field[] = $field;
		$fields = $new_field;
	}

	displayRows($rows,$fields, $filters, $filter_values);
}


function displayFiltrage($rows, $fields, $filters, $filter_values)
{
	global $fieldsAll;
	global $actions;
	global $fieldsTypes;
	global $specialtr_fields;
	global $start_tr_fields;
	global $end_tr_fields;

	?>
<!--  Menu filtrage -->
<table>
	<tr>
		<td>
			<table class="inputreport">
				<tr>
					<?php
					$count = 0;
					foreach($filters as $filter => $data)
						if(isset($data['liste']))
						{
							$count++;
							?>
					<td><?php echo $data['name'];?></td>
					<td><select
						onchange="window.location='index.php?action=view&amp;filter_<?php echo $filter?>=' + this.value;">
							<option value="<?php echo $data['default_value']; ?>">
								<?php echo $data['default_name']; ?>
							</option>
							<?php
							foreach ($data['liste'] as $value => $nomitem)
							{
								if(is_numeric($value))
									$value = strval($value);
								$cur_val = $filter_values[$filter];
								if(is_numeric($cur_val))
									$cur_val = strval($cur_val);
								$sel = "";
								if ($value === $cur_val)
									$sel = " selected=\"selected\"";
								echo "<option value=\"".$value."\" $sel>".$nomitem."</option>\n";
							}
							?>
					</select></td>
					<?php 
					if($count %3 == 0)
						echo '</tr><tr>';
						}
						?>
					<td></td>
					<td style="width: 10em;"><h3>
							<a href="index.php?action=view&amp;reset_filter=">Réinitialiser
								filtres</a>
						</h3>
					</td>

				</tr>
			</table>
		</td>
	</tr>
</table>

<!-- END  Menu filtrage -->

<?php
}

function showIconAvis($fieldID,$data)
{
	global $icones_avis;
	if ((substr( $fieldID,0,4)==="avis") and isset($icones_avis[$data]))
	{
		$url = $icones_avis[$data];
		echo "<img class=\"iconeAvis\" src=\"".$url."\">&nbsp;";
	}
}

function displayStatsConcours()
{
	$stats = get_bureau_stats();
	$roles = array("rapporteur","rapporteur2","rapporteur3");
	?>
<center>
	<table>
		<tr>
			<?php
			foreach($stats as $niveau => $data)
				echo "<th>".$niveau."</th>";
			?>
		</tr>
		<tr valign="top">
			<?php
			foreach($stats as $niveau => $data)
			{
				?>
			<td>
				<table class="stats">
					<tr>
						<th>login</th>
						<th>rapp</th>
						<th>rapp 2</th>
						<th>rapp 3</th>
						<th>Total</th>
					</tr>
					<?php
					foreach($data as $login => $data_rapporteur)
					{
						$nom= isset($rapporteurs[$login])? $rapporteurs[$login] : $login;
						echo "<tr ><td>".$nom."</td>";
						$total = 0;
						foreach($roles as $role)
						{
							if(isset($data_rapporteur[$role]))
							{
								$stat = $data_rapporteur[$role]["counter"];
								echo "<td>".$stat."</td>";
								$total += $stat;
							}
							else
								echo "<td></td>";
						}
						echo "<td>".$total."</td>";
						echo "</tr>";
					}
					?>
				</table>
			</td>
			<?php
			}
			?>
		</tr>
	</table>
</center>
<?php
}

function displayStatsSession()
{
	$stats = get_bureau_stats();
	$roles = array("rapporteur","rapporteur2","rapporteur3");
	?>
<center>
	<table class="stats">
		<tr>
			<th>Rapporteur</th>
			<th>total</th>
			<th>1</th>
			<th>2</th>
			<th>3</th>
		</tr>
		<?php
		foreach($stats as $rapporteur => $compteurs)
		{
			echo "<tr><td>".$rapporteur."</td>";
			echo "<td>".$compteurs["total"]."</td>";
			echo "<td>".$compteurs["rapporteur"]."</td>";
			echo "<td>".$compteurs["rapporteur2"]."</td>";
			echo "<td>".$compteurs["rapporteur3"]."</td>";
			echo "</tr>\n";
		}
		?>
	</table>
</center>
<?php 
}

function displayStats()
{
	if(is_current_session_concours())
		displayStatsConcours();
	else
		displayStatsSession();
}

function displayRowCell($row, $fieldID)
{
	global $fieldsAll;
	    global $typesRapportsAll;
	$bur = isBureauUser();
	$sec = isSecretaire() || ( $bur && isSecretaire(getLogin() , false));

	$concours = getConcours();	
	$rapporteurs = listNomRapporteurs();
	

	$title = $fieldsAll[$fieldID];
	echo '<td>';
	$data = $row->$fieldID;

	global $fieldsTypes;
	$type = isset($fieldsTypes[$fieldID]) ?  $fieldsTypes[$fieldID] : "";

	if($type=="rapporteur")
	{
	  if(is_field_editable($row, $fieldID))
		{
			?>
	<select
		onchange="window.location='index.php?action=set_property&property=<?php echo $fieldID; ?>&all_reports=&id_origine=<?php echo $row->id_origine; ?>&value=' + this.value;">
		<?php
		foreach($rapporteurs as $rapporteur => $nom)
		{
			$selected = ($rapporteur == $row->$fieldID) ? "selected=on" : "";
			echo "<option ".$selected." value=\"".$rapporteur."\">".$nom."</option>\n";
		}
		?>
	</select>
	<?php
		}
		else
			echo (isset($rapporteurs[$row->$fieldID]) ? $rapporteurs[$row->$fieldID] : $row->$fieldID);
	}
	else if($type=="avis")
	{
		global $typesRapportToAvis;
		global $tous_avis;

		$listeavis = isset($typesRapportToAvis[$row->type]) ? $typesRapportToAvis[$row->type] : array();
		
		if(isset($filters['avis']) && isset($data['avis']['liste']))
			$avis = $data['avis']['liste'];
		
		if(is_field_editable($row, $fieldID))
		{
			?><select onchange="window.location='index.php?action=set_property&property=<?php echo $fieldID; ?>&id_origine=<?php echo $row->id_origine; ?>&value=' + encodeURIComponent(this.value);">
		<?php
		foreach($listeavis as $key => $value)
		{
			$selected = (strval($key) === $row->$fieldID) ? "selected=on" : "";
			echo "<option ".$selected." value=\"".$key."\">".$value."</option>\n";
		}
		?>
	</select>
	<?php
		}
		else if($fieldID == "avis" || $sec || !isset($row->statut) || $row->statut != "doubleaveugle")
		{
			showIconAvis($fieldID,$data);
			echo isset($tous_avis[$data]) ? $tous_avis[$data] : $data;
		}
	}
	else if($fieldID == "concours")
	{
		echo isset($concours[$row->$fieldID]) ? $concours[$row->$fieldID]->intitule : "";
	}
	else if($fieldID=="sousjury")
	{
		echo $row->$fieldID;
	}
	else if($fieldID=="nom")
	{
		echo "<a href=\"?action=edit&amp;id=".($row->id)."\">";
		echo '<span class="valeur">'.$data.'</span>';
		echo '</a>';
	}
	else if( $type == "unit")
	{
		$prettyunits = unitsList();
		$data = isset($prettyunits[$row->$fieldID]) ? ($prettyunits[$row->$fieldID]->nickname." (".$row->$fieldID.")") : $row->$fieldID;
		echo '<span class="valeur">'.$data.'</span>';
	}
	else if($fieldID == "type" && isset($typesRapportsAll[$data]))
	  {
	    $label = $typesRapportsAll[$data];
	    $num = 25;
	    if(strlen($label) > $num)
	      {
		$arr = explode(" ",$label);
		$tot = 0;
		$lab = "";
		foreach($arr as $piece)
		  {
		  $tot += strlen($piece);
		  $lab .= $piece." ";
		  if($tot > $num) 
		    {
		    $lab .= "<br/>"; 
		    $tot = 0;
		    }
		  }
		$label = $lab;
	      }
	    echo '<span class="valeur">'.$label.'</span>';
	  }
	else
		echo '<span class="valeur">'.$data.'</span>';
		
	echo "</td>\n";
}

function display_updates()
{
	if(isSecretaire() && !isset($_SESSION["update_performed"]))
	{
	  synchronizeWithDsiMembers($currentSection());
		$_SESSION["update_performed"] = true;
	}
}


function displayStatutMenu()
{
	?>
<td>
	<table>
		<tr>
			<td>
				<form
					onsubmit="return confirm('Changer les statuts des rapports?');"
					method="post" action="index.php">
					<table>
						<tr>
							<td><input type="submit" value="Changer statuts" />
							</td>
							<td><select name="new_statut">
									<?php
									global $statutsRapports;
									global $statutsRapportsACN;
									$statuts = isACN() ? $statutsRapportsACN : $statutsRapports;									

									foreach ($statuts as $val => $nom)
									{
									  if($val == "avistransmis" || $val == "publie")
									    continue;
										$sel = "";
										echo "<option value=\"".$val."\" $sel>".$nom."</option>\n";
									}
									?>
							</select> <input type="hidden" name="action"
								value="change_statut" />
							</td>
						</tr>
					</table>
				</form>
			</td>
		</tr>
		<tr>
			<td>
				<form onsubmit="return confirm('Supprimer ces rapports?');"
					method="post" action="index.php">
					<input type="hidden" name="action" value="deleteCurrentSelection" />
					<input type="submit" value="Supprimer rapports" />
				</form>
			</td>
		</tr>
		<?php
		if(is_current_session_concours())
		{
		?> 
		<tr>
			<td>
				<form method="post" action="index.php"
					onsubmit="return confirm('Affecter les sous-jurys?');">
					<input type="hidden" name="action" value="affectersousjurys2" /> <input
						type="submit" value="Affecter sous-jurys" /> <input type="hidden"
						name="admin_concours"></input>
				</form>
			</td>
		</tr>
		<?php 
		}
		?>
		
	</table>
</td>
<?php 
}

function displayRows($rows, $fields, $filters, $filter_values)
{
  //	display_updates();
	
	global $fieldsAll;

	?>
<table>
	<tr>
		<td>
		<?php displayFiltrage($rows, $fields, $filters, $filter_values); ?>
		</td>
   <?php if(isSecretaire()) displayStatutMenu(); ?>
	</tr>
</table>
<hr />
<p>
	<?php  echo count($rows); ?>
	rapports
</p>

<?php 
$rapporteurs = listNomRapporteurs();
$bur = isBureauUser();

if($bur)
	displayStats();
?>
<table class="summary">
	<tr>
		<th class="oddrow"><span class="nomColonne"></span></th>
		<?php

		foreach($fields as $fieldID)
			if(isset($fieldsAll[$fieldID]))
			{
				$title = $fieldsAll[$fieldID];
				$style = getStyle("",true);
				?>
		<th class="<?php echo $style;?>"><span class="nomColonne"> <?php 
		echo '<a href="?action=view&amp;reset_tri='.$fieldID."\">".$title.'</a>';
		?>
		</span>
		</th>
		<?php
			}
			?>
	</tr>
	<?php 
	global $actions1;
	global $actions2;

	$odd = false;
	foreach($rows as $row)
	{
		$conflit = is_in_conflict_efficient($row, getLogin());
		$style = getStyle("",$odd,$conflit);
		$odd = !$odd;
	?>
	<tr id="t<?php echo $row->id;?>" class="<?php echo $style;?>">
		<td>
		<?php displayActionsMenu($row,"", $actions1); ?>
		</td>
		<?php 
		foreach($fields as $fieldID)
			displayRowCell($row, $fieldID);
		?>
		<td>
		<?php displayActionsMenu($row,"", $actions2); ?>
		</td>
	</tr>
	<?php 
	}
}
	?>
