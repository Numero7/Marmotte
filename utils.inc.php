<?php
session_start();
require_once('config.inc.php');
require_once('db.inc.php');
require_once('manage_users.inc.php');
require_once('manage_unites.inc.php');
require_once('manage_rapports.inc.php');

//set_exception_handler('exception_handler');
//set_error_handler('error_handler');


function getFilterValue($filter_name)
{
	global $filtersAll;
	$filters = $filtersAll;
	$answer = $filters[$filter_name]['default_value'];
	if(isset($_REQUEST[$filter_name]))
		$answer = $_REQUEST[$filter_name] != "" ? $_REQUEST[$filter_name] : $filters[$filter_name]['default_value'];
	else if(isset($_SESSION[$filter_name."_filter"]))
		$answer =   $_SESSION[$filter_name."_filter"];
	$_SESSION[$filter_name.'_filter'] = $answer;
	return $answer;
}

function resetFilterValues()
{
	$filters = getCurrentFiltersList();
	foreach($filters as $filter => $data)
		if(!isset($_REQUEST[$filter]))
		$_REQUEST[$filter] = $data['default_value'];
}

function resetFilterValuesExceptSession()
{
	$filters = getCurrentFiltersList();
	foreach($filters as $filter => $data)
		if($filter != 'id_session' && !isset($_REQUEST[$filter]))
		$_REQUEST[$filter] = $data['default_value'];
}

function getCurrentFiltersList()
{
	global $filtersConcours;
	global $filtersReports;
	if(is_current_session_concours())
		return $filtersConcours;
	else
		return $filtersReports;
}

function getFilterValues()
{
	$filters = getCurrentFiltersList();
	$filter_values = array();
	foreach($filters as $filter => $data)
		$filter_values[$filter] =  getFilterValue($filter);
	return $filter_values;
}

function getSortCriteria()
{
	if(isset($_REQUEST['sort']))
	{
		$_SESSION['$sorting_criteria'] = $_REQUEST['sort'];
		return $_REQUEST['sort'];
	}
	else
	{
		return isset($_SESSION['$sorting_criteria']) ? $_SESSION['$sorting_criteria'] : "";
	}
}



function showCriteria($sortCrit, $crit)
{
	$order = "";
	$index = -1;
	if (isset($sortCrit[$crit]))
	{
		$order = $sortCrit[$crit];
		$index = array_search($crit, array_keys($sortCrit))+1;
	}
	if ($order=="ASC")
	{
		return "<img src=\"img/sortup.png\" alt=\"$crit sorted ascendently\"/><span style=\"text-decoration:none;\">($index)</span>";
	}
	else if ($order=="DESC")
	{
		return "<img src=\"img/sortdown.png\" alt=\"$crit sorted descendently\"/><span style=\"text-decoration:none;\">($index)</span>";
	}
	else
	{  return "<img src=\"img/sortneutral.png\" alt=\"$crit sorted neutrally\"/>";
	}
}

function dumpEditedCriteria($sortCrit, $edit_crit)
{
	$result = "";
	$order = "";
	if (isset($sortCrit[$edit_crit]))
	{
		$order = $sortCrit[$edit_crit];
	}
	if ($order=="ASC")
	{
		$sortCrit[$edit_crit] = "DESC";
	}
	else if ($order=="")
	{
		$sortCrit[$edit_crit] = "ASC";
	}
	else if ($order=="DESC")
	{
		//We want at least one sort criterion
		//also removes bug
		//if(count($sortCrit) > 1)
		unset($sortCrit[$edit_crit]);
		//else
		//$sortCrit[$edit_crit] = "ASC";
	}
	foreach($sortCrit as $crit => $order)
	{
		if ($order=="ASC")
		{
			$order = "*";
		}
		else if ($order=="DESC")
		{
			$order = "-";
		}
		if ($result != "")
		{
			$result .= ";";
		}
		$result .=  $order.$crit;
	}
	return urlencode($result);
}


function getTypesEval($id_session)
{
	$finalResult = array();
	$sql = "SELECT DISTINCT type FROM (SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM ".evaluations_db." tt INNER JOIN ( SELECT id, MAX(date) AS date FROM ".evaluations_db." GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, ".sessions_db." ss WHERE ss.id=tt.id_session) difftypes WHERE id_session=$id_session ORDER BY type DESC;";
	$result=mysql_query($sql);
	while ($row = mysql_fetch_object($result))
	{
		if ($row->type)
			$finalResult[] = $row->type;
	}
	return $finalResult;
}

function displayIndividualReport($row)
{
	global $fieldsIndividual;
	global $fieldsAll;

	global $actions;
	global $typesRapportsIndividuels;
	$specialRule = array(
			"nom"=>0,
			"prenom"=>0,
			"grade"=>0,
			"unite"=>0,
			"type"=>0,
			"nom_session"=>0,
			"date_session"=>0,
	);
	$sessions = sessionArrays();
	?>
<div class="tools">
	<?php
	displayActionsMenu($row->id, $row->id_origine, "details");
	?>
</div>
<h1>
	<?php echo $row->prenom;?>
	<?php echo strtoupper($row->nom);?>
	(
	<?php echo $row->grade;?>
	) -
	<?php echo $row->unite;?>
</h1>
<h2>
	<?php echo $typesRapportsIndividuels[$row->type];?>
	<?php echo $sessions[$row->id_session];?>
</h2>
<dl>
	<?php
	foreach($fieldsIndividual as  $fieldID)
	{
		if (!isset($specialRule[$fieldID]))
		{
			?>
	<dt>
		<?php echo $fieldsAll[$fieldID];?>
	</dt>
	<dd>
		<?php echo remove_br($row->$fieldID);?>
	</dd>
	<?php
		}
	}
	?>
</dl>
<div></div>
<?php
} ;

function displayConcoursReport($row)
{
	global $fieldsConcours;
	global $fieldsAll;
	global $actions;
	global $typesRapportsConcours;
	$specialRule = array(
			"nom"=>0,
			"prenom"=>0,
			"grade"=>0,
			"unite"=>0,
			"type"=>0,
			"nom_session"=>0,
			"date_session"=>0,
	);
	$sessions = sessionArrays();
	?>
<div class="tools">
	<?php
	displayActionsMenu($row->id, $row->id_origine, "details");
	?>
</div>
<h1>
	<?php echo $row->prenom;?>
	<?php echo strtoupper($row->nom);?>
	(
	<?php echo $row->grade;?>
	) -
	<?php echo $row->concours;?>
</h1>
<h2>
	<?php echo $typesRapportsConcours[$row->type];?>
</h2>
<dl>
	<?php
	foreach($fieldsConcours as  $fieldID)
	{
		if (!isset($specialRule[$fieldID]))
		{
			?>
	<dt>
		<?php echo $fieldsAll[$fieldID];?>
	</dt>
	<dd>
		<?php echo remove_br($row->$fieldID);?>
	</dd>
	<?php
		}
	}
	?>
</dl>
<div></div>
<?php
} ;

function displayReport($id_rapport)
{
	global $typesRapportsUnites;
	global $typesRapportsIndividuels;
	global $typesRapportsConcours;

	$row = getReport($id_rapport);
	if(array_key_exists($row->type,$typesRapportsUnites))
		displayUnitReport($row);
	else if(array_key_exists($row->type,$typesRapportsIndividuels))
		displayIndividualReport($row);
	else if(array_key_exists($row->type,$typesRapportsConcours))
		displayConcoursReport($row);
	else
		echo "Unknown report type : ".$row->type. " (id) ".$id_rapport."<br/>";
}

function displayActionsMenu($id,$id_origine, $excludedaction = "")
{
	global $actions;
	foreach($actions as $action => $actiondata)
		if ($action!=$excludedaction)
		{
			$title = $actiondata['title'];
			$icon = $actiondata['icon'];
			$page = $actiondata['page'];
			$level = $actiondata['level'];
			if(getUserPermissionLevel() >= $level)
			{
				echo "<td>\n<a href=\"$page?action=$action&amp;id=$id&amp;id_origine=$id_origine\">\n";
				echo "<img class=\"icon\" width=\"24\" height=\"24\" src=\"$icon\" alt=\"$title\"/>\n</a>\n</td>\n";
			}
		}
}

function displayUnitReport($row)
{
	global $fieldsAll;
	global $fieldsUnites;
	global $actions;
	global $typesRapportsUnites;
	$specialRule = array(
				"nom"=>0,
				"prenom"=>0,
				"grade"=>0,
				"unite"=>0,
				"type"=>0,
				"id_session"=>0,
				"date_session"=>0,
		);
	$sessions = sessionArrays();
	?>
<div class="tools">
	<?php
	displayActionsMenu($row->id, $row->id_origine, "details");
	?>
</div>
<h1>
	<?php echo $row->unite;?>
</h1>
<h2>
	<?php echo $typesRapportsUnites[$row->type];?>
	<?php echo $sessions[$row->id_session];?>
</h2>
<dl>
	<?php
	foreach($fieldsAll as  $fieldID => $title)
		if (!isset($specialRule[$fieldID]) && in_array($fieldID,$fieldsUnites))
		{
			?>
	<dt>
		<?php echo $title;?>
	</dt>
	<dd>
		<?php echo remove_br($row->$fieldID);?>
	</dd>
	<?php
		}
		?>
</dl>
<div></div>
<?php

} ;

function getExample($type)
{
	global $examples;
	$tmp = "Exemple : ";
	if (isset($examples[$type]))
	{
		$tmp .= $examples[$type];
	}
	return $tmp;
}

function highlightDiff(&$prevVals,$key,$val)
{
	if (isset($prevVals[$key]))
	{
		if ($prevVals[$key]==$val)
		{
			$prevVals[$key] = $val;
			return "<span class=\"faded\">$val</span>";
		}
		else
		{
			$prevVals[$key] = $val;
			return "<span class=\"highlight\">$val</span>";
		}
	}
	$prevVals[$key] = $val;
	return $val;
}

function displayExport($filter_values)
{
	global $typeExports;
	global $statutsRapports;
	global $filters;

	echo '<h2>Export</h2>';
	if (getUserPermissionLevel()>= NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE)
		echo '<table><tr><td>';

	foreach($typeExports as $idexp => $exp)
	{
		$expname= $exp["name"];
		$level = $exp["permissionlevel"];
		if (getUserPermissionLevel()>=$level)
		{
			echo "<a href=\"export.php?action=export&amp;type=$idexp\">";
			echo "<img class=\"icon\" width=\"40\" height=\"40\" src=\"img/$idexp-icon-50px.png\" alt=\"$expname\"/></a>";
		}
	}
	if (getUserPermissionLevel()>= NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE)
	{
		echo '
		</td></tr><tr><td align="center">
		<form method="post"  action="index.php">
		<select name="new_statut">';
		foreach ($statutsRapports as $val => $nom)
		{
			$sel = "";
			echo "<option value=\"".$val."\" $sel>".$nom."</option>\n";
		}
		echo '
			</select>
			<input type="hidden" name="action" value="change_statut"/>
			<input type="submit" value="Changer statut"/>
			</form>';
		echo '</td></tr></table>';
	}
}


function displaySummary($filters, $filter_values, $sort_crit)
{
	global $fieldsSummary;
	global $fieldsSummaryCandidates;
	global $typesRapports;
	global $statutsRapports;
	global $filtersReports;


	$rows = filterSortReports($filters, $filter_values, $sort_crit);
	$sortCrit = parseSortCriteria($sort_crit);

	$filters['login_rapp']['liste'] = simpleListUsers();

	$units = simpleUnitsList();
	if(array_key_exists('labo1', $filters))
		$filters['labo1']['liste'] = $units;
	if(array_key_exists('labo2', $filters))
		$filters['labo2']['liste'] = $units;
	if(array_key_exists('labo3', $filters))
		$filters['labo3']['liste'] = $units;
	if(array_key_exists('unite', $filters))
		$filters['unite']['unite'] = $units;

	$fields = is_current_session_concours() ? $fieldsSummaryCandidates : $fieldsSummary;
	displayRows($rows,$fields, $filters, $filter_values, $sortCrit);
}

function displayRows($rows, $fields, $filters, $filter_values, $sortCrit)
{
	global $fieldsAll;
	global $actions;
	global $fieldsTypes;
	global $specialtr_fields;
	global $start_tr_fields;
	global $end_tr_fields;
	
	?>
<table>
	<tr>
		<td style="padding: 2.25em;">
			<h2>
				Filtrage (<a href="index.php?action=view&amp;reset_filter=">Reset</a>)
			</h2>
			<form method="get" action="index.php">
				<table class="inputreport">
					<?php 
					foreach($filters as $filter => $data)
						if(isset($data['liste']))
				{
					if(in_array($filter, $start_tr_fields))
						echo '<tr><td></td><td><table><tr>';
					if(!in_array($filter, $specialtr_fields))
						echo '<tr>';
				?>
						<td style="width: 20em;"><?php echo $data['name'];?></td>
						<td><select name="<?php echo $filter?>">
								<option value="<?php echo $data['default_value']; ?>">
									<?php echo $data['default_name']; ?>
								</option>
								<?php
								foreach ($data['liste'] as $value => $nomitem)
								{
									$sel = "";
									if ($value == $filter_values[$filter])
										$sel = " selected=\"selected\"";
									echo "<option value=\"".$value."\" $sel>".$nomitem."</option>\n";
								}
								?>
						</select></td>
					<?php 
					if(!in_array($filter, $specialtr_fields))
						echo '</tr>';
					if(in_array($filter, $end_tr_fields))
						echo '</tr></table></td></tr>';
				}?>
					<tr>
						<td></td>
						<td><input type="hidden" name="action" value="view" /> <input
							type="submit" value="Filtrer" />
						</td>
					</tr>
				</table>
			</form>
		</td>
		<td align="center" style="padding: 2.5em;"><?php displayExport(getFilterValues());?>
		</td>
		<td style="padding: 2.25em;"></td>
	</tr>
</table>
<hr />
<table class="summary">
	<tr>
		<?php
		foreach($fields as $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			?>
		<th><span class="nomColonne"><?php
		echo "<a href=\"?action=view&amp;sort=".dumpEditedCriteria($sortCrit, $fieldID)."\">";
		echo $title.showCriteria($sortCrit, $fieldID);
		echo "</a>\n";?> </span>
		</th>
		<?php
		}
		foreach($actions as $action => $actiondata)
			echo '<th></th>';
		echo '</tr>';

		$prettyunits = unitsList();
		$users = listRapporteurs();

		foreach($rows as $row)
		{
			?>
	
	
	<tr id="t<?php echo $row->id;?>">
		<?php
		foreach($fields as $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			?>
		<td><?php
		$data = $row->$fieldID;
		$type = isset($fieldsTypes[$fieldID]) ?  $fieldsTypes[$fieldID] : "";
		if( ($type == "unit") && isset($prettyunits[$row->$fieldID]))
			$data = $prettyunits[$row->$fieldID]->nickname;

		if(isSecretaire() &&  isset($fieldsTypes[$fieldID]) && $fieldsTypes[$fieldID]=="rapporteur")
		{
			?>
			<form method="post" action="index.php">
				<table width="10">
					<tr>
						<td><input type="hidden" name="action"
							value="set<?php echo $fieldID;?>" /> <input type="hidden"
							name="id_toupdate" value="<?php echo $row->id;?>" /> <select
							name="new<?php echo $fieldID;?>">
								<?php
								foreach($users as $user => $data)
								{
									$sel = (($row->$fieldID) == ($user)) ? "selected=\"selected\"" : "";
									echo  "\t\t\t\t\t<option value=\"".($user)."\" ".$sel.">".($data->description)."&nbsp;</option>\n";
								}
								?>
						</select>
						</td>
						<td><input type="submit" value="OK"></input>
						</td>
					</tr>
				</table>
			</form> <?php 
		}
		else if($data != "")
		{
			if($fieldID=="nom")
			{
			echo "<a href=\"?action=edit&amp;id=".($row->id)."&amp;id_origine=".$row->id_origine."\">";
			echo '<span class="valeur">'.$data.'</span>';
			echo '</a>';
			}
			else
			{
				echo '<span class="valeur">'.$data.'</span>';
			}
		}
		?>
		</td>
		<?php
		}
		displayActionsMenu($row->id, $row->id_origine);
		?>
	</tr>
	<?php
		}
		?>

</table>
<?php
} ;


function fieldDiffers($prevVals,$key,$val)
{
	if(isset($prevVals[$key]))
	{
		if ($prevVals[$key]==$val)
		{
			return false;
		}
		else {
 		return true;
		}
	} return true;
}

function historyReport($id_origine)
{
	global $fieldsAll;
	global $actions;
	$specialRule = array( "nom"=>0, "prenom"=>0, "grade"=>0, "unite"=>0, "type"=>0, "nom_session"=>0, "date_session"=>0, "date"=>0, "auteur"=>0);
	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM ".evaluations_db." tt, ".sessions_db." ss WHERE tt.id_session=ss.id AND tt.id_origine=$id_origine ORDER BY date DESC;";
	$result=mysql_query($sql);
	$prevVals = array();
	$first = true;
	while ($row = mysql_fetch_object($result))
	{
		if ($first)
	 { ?>
<div class="tools">
	<?php
	displayActionsMenu($row->id, $row->id_origine, "history");
	?>
</div>
<?php
$first = false;
	 }

	 ?>
<div class="history">
	<h3>
		Version
		<?php echo ($row->statut=="supprime") ? "<span class=\"highlight\">supprimée</span>" : "modifiée"; ?>
		le
		<?php echo $row->date;?>
		par
		<?php 
		$desc = getDescription($row->auteur);
		if (!$desc)
			$desc = $row->auteur;
		echo highlightDiff($prevVals,"auteur",$desc);
		?>
	</h3>
	<?php
	if (fieldDiffers($prevVals,"prenom",$row->prenom)
				or fieldDiffers($prevVals,"nom",strtoupper($row->nom))
				or fieldDiffers($prevVals,"grade",$row->grade)
				or fieldDiffers($prevVals,"unite",$row->unite))
			{
				?>
	<h1>
		<?php echo highlightDiff($prevVals,"prenom",$row->prenom);?>
		<?php echo highlightDiff($prevVals,"nom",strtoupper($row->nom));?>
		(
		<?php echo highlightDiff($prevVals,"grade",$row->grade);?>
		) -
		<?php echo highlightDiff($prevVals,"unite",$row->unite);?>
	</h1>
	<?php
			}
			if (fieldDiffers($prevVals,"type",$row->type)
				or fieldDiffers($prevVals,"nom_session",$row->nom_session." ".date("Y",strtotime($row->date_session))))
			{
				?>
	<h2>
		<?php echo highlightDiff($prevVals,"type",$row->type);?>
		<?php echo highlightDiff($prevVals,"nom_session",$row->nom_session." ".date("Y",strtotime($row->date_session)));?>
	</h2>
	<?php
			}
			?>
	<dl>
		<?php
		foreach($fieldsAll as  $fieldID => $title)
		{
			if (!isset($specialRule[$fieldID]) 	and !(isset($prevVals[$fieldID])and ($prevVals[$fieldID]==$row->$fieldID)))
			{
				?>
		<dt>
			<?php echo $title;?>
		</dt>
		<dd>
			<?php echo highlightDiff($prevVals,$fieldID,$row->$fieldID);?>
		</dd>
		<?php
			}
		}
		?>
	</dl>
</div>
<?php
	}
}

function remove_br($str)
{
	return str_replace("<br />","",$str);
}

function get_active_fields($row)
{
	global $fieldsIndividual;
	global $fieldsUnites;
	global $fieldsEcoles;
	global $fieldsConcours;
	global $fieldsGeneric;
	global $fieldsEquivalence;

	global $typesRapportsUnites;
	global $typesRapportsIndividuels;

	$eval_type = $row->type;

	if($eval_type == 'Ecole')
		return $fieldsEcoles;
	else if(array_key_exists($eval_type,$typesRapportsUnites))
		return $fieldsUnites;
	else if(array_key_exists($eval_type,$typesRapportsIndividuels))
		return $fieldsIndividual;
	else if($eval_type == 'Candidature')
		return $fieldsConcours;
	else if($eval_type == 'Equivalence')
		return $fieldsEquivalence;
	else
		return $fieldsGeneric;
}

function statut_to_choose($row)
{
	return isSecretaire();
}

function displayStatusField($row)
{
	global $statutsRapports;

	if(statut_to_choose($row))
	{
		echo "<tr><td>Statut</td><td><select name=\"fieldstatut\" style=\"width: 100%;\">";
		foreach($statutsRapports as $val => $nom)
		{
			$sel = ($row->statut==$val) ? "selected=\"selected\"" : "";
			echo  "\t\t\t\t\t<option value=\"$val\" $sel>$nom</option>\n";
		}
		echo "</select></td></tr>";
	}
}

function type_to_choose($row)
{
	$eval_type = $row->type;
	return $eval_type == "Evaluation-Vague" || $eval_type == "Evaluation-MiVague";
}
function displayTypeField($row)
{
	global $typesRapports;
	$eval_type = $row->type;

	if( type_to_choose($row) )
	{

		echo "<tr><td>Evaluation</td><td><select name=\"fieldtype\" style=\"width: 100%;\">";
		$typesRapportsEvals = array(
			"Evaluation-Vague" => $typesRapports['Evaluation-Vague'],
			"Evaluation-MiVague" => $typesRapports['Evaluation-MiVague']
);
	foreach($typesRapportsEvals as $val => $nom)
	{
		$sel = ($eval_type==$val) ? " selected=\"selected\"" : "";
		echo  "\t\t\t\t\t<option value=\"$val\" $sel>$nom</option>\n";
	}
	echo "</select></td></tr>";
	}

}

function session_to_choose($row)
{
	return isSecretaire();
}

function displaySessionField($row)
{
	if(session_to_choose($row))
	{
		?>
<input type="hidden"
	name="fieldid_session" value="<?php echo $row->id_session;?>" />
<?php 
	}
}

function display_long($row, $fieldID)
{
	echo '
		</tr>
		<tr>
		<td colspan="3">
		<textarea name="field'.$fieldID.'" style="width: 100%;">'.remove_br($row->$fieldID).'</textarea>
		</td>
		';
}

function display_treslong($row, $fieldID)
{
	echo '
		<td colspan="2">
		</td>
		</tr>
		<tr>
		<td colspan="3">
		<textarea rows="15" cols="100" name="field'.$fieldID.'" >'.remove_br($row->$fieldID).'</textarea>
			</td>
			';
}

function display_short($row, $fieldID)
{
	?>
<td style="width: 30em;"><input name="field<?php echo $fieldID;?>"
	value="<?php echo $row->$fieldID;?>" style="width: 100%;" />
</td>
<?php
}


function display_evaluation($row, $fieldID)
{
	global $notes;

	?>
<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
	style="width: 100%;">
		<?php
		foreach($notes as $val)
		{
			$sel = ($row->$fieldID==$val) ? $sel = "selected=\"selected\"" : "";
			echo  "\t\t\t\t\t<option value=\"$val\" $sel>$val</option>\n";
		}
		?>
</select>
</td>
<?php
}


function display_avis($row, $fieldID)
{
	global $typesRapportToAvis;
	$eval_type = $row->type;
	$avis_possibles = array();

	if(array_key_exists($eval_type, $typesRapportToAvis))
		$avis_possibles = $typesRapportToAvis[$eval_type];

	?>
<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
	style="width: 100%;">
		<?php
		foreach($avis_possibles as $avis => $prettyprint)
		{
			$sel = ($row->$fieldID==$avis) ? $sel = "selected=\"selected\"" : "";
			echo  "\t\t\t\t\t<option value=\"$avis\" $sel>$prettyprint</option>\n";
		}
		?>
</select>
</td>
<?php
}

function rapporteur_to_choose($row)
{
	return isBureauUser();
}

function display_rapporteur($row, $fieldID)
{
	$users = listRapporteurs();
	if(rapporteur_to_choose($row))
	{
		?>
<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
	style="width: 100%;">
		<?php
		foreach($users as $user => $data)
		{
			$sel = (($row->$fieldID) == ($user)) ? "selected=\"selected\"" : "";
			echo  "\t\t\t\t\t<option value=\"".($user)."\" ".$sel.">".($data->description)."</option>\n";
		}
		?>
</select>
</td>
<?php
	}
	else
	{
		echo "<td>".$users[$row->$fieldID]->description."</td>\n";
	}
}

function display_unit($row, $fieldID)
{
	?>
<td><select name="field<?php echo $fieldID;?>">
		<?php
		$units = unitsList();
		foreach($units as $unite =>$valeur)
		{
			$sel = (($row->$fieldID) == ($valeur->code)) ? "selected=\"selected\"" : "";
			echo  "\t\t\t\t\t<option value=\"".($valeur->code)."\"".$sel.">".$valeur->prettyname."&nbsp;</option>\n";
		}
		?>
</select>
</td>
<?php
}

function display_topic($row, $fieldID)
{
	global $topics;
	?>
<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
	style="width: 100%;">
		<?php
		$units = unitsList();
		foreach($topics as $id =>$topic)
		{
			$sel = (($row->$fieldID) == ($id)) ? "selected=\"selected\"" : "";
			echo  "\t\t\t\t\t<option value=\"".($id)."\"".$sel.">".$id.". ".$topic."</option>\n";
		}
		?>
</select>
</td>
<?php
}

function display_grade($row, $fieldID)
{
	global $grades;

	echo '<td><select name="fieldgrade" style="width: 100%;">';
	foreach($grades as $idg => $txtg)
	{
		$sel = ($row->grade==$idg) ? 'selected="selected"' : "";
		echo  "\t\t\t\t\t<option value=\"$idg\" $sel>$txtg</option>\n";
	}
	echo '</select></td>';
}


function display_concours($row, $fieldID)
{
	global $concours_ouverts;

	echo '<td><select name="fieldconcours" style="width: 100%;">';
	foreach($concours_ouverts as $concours)
	{
		$sel = ($row->concours==$concours) ? "selected=\"selected\"" : "";
		echo  "\t\t\t\t\t<option value=\"$concours\" $sel>$concours</option>\n";
	}
	echo '</select></td>';
}

function display_ecole($row, $fieldID)
{
	echo '<td colspan="3"><input name="fieldecole" value="'.$row->ecole.'" style="width: 100%;"/> </td>';
}

function displayEditableReport($row, $actioname, $create_new = true)
{
	global $fieldsAll;
	global $fieldsTypes;
	global $actions;
	global $typesRapportsUnites;
	global $avis_eval;

	global $typesRapports;
	global $statutsRapports;

	$eval_type = $row->type;
	$is_unite = array_key_exists($eval_type,$typesRapportsUnites);
	$active_fields = get_active_fields($row);
	$statut = $row->statut;

	$eval_name = $eval_type;
	if(array_key_exists($eval_type, $typesRapports))
		$eval_name = $typesRapports[$eval_type];

	$next = -1;
	$previous = -1;
	if(isset($_SESSION['rows_id']) && ($actioname != "add"))
	{
		$rows_id = $_SESSION['rows_id'];
		$id_origine = $row->id_origine;
		$n = count($rows_id) ;
		for($i = 0; $i < $n; $i++)
		{
			if($rows_id[$i] == $id_origine)
			{
				if($i < $n - 1)
					$next = $rows_id[$i+1];
				else
					$next = $rows_id[0];
				if($i > 0)
					$previous = $rows_id[$i-1];
				else
					$previous = $rows_id[$n-1];
				break;
				
			}
		}
	}


	?>
<h1>
	<?php 
	$sessions = showSessions();
	echo $sessions[$row->id_session]['prettyprint']." / ".($is_unite ? "Unite / " : "Chercheur / ").$eval_name. " / #".(isset($row->id) ? $row->id : "New");
	?>
</h1>

<form method="post" action="index.php" style="width: 100%">
	<input type="hidden" name="action" value="<?php echo $actioname?>" /> <input
		type="hidden" name="create_new" value="<?php echo $create_new?>" /> <input
		type="hidden" name="id_origine" value="<?php echo $row->id_origine;?>" />
	<?php if(! type_to_choose($row))
		{?>
	<input type="hidden" name="fieldtype" value="<?php echo $row->type;?>" />
	<?php 
		}
		if(! session_to_choose($row))
		{
			?>
	<input type="hidden" name="fieldid_session"
		value="<?php echo $row->id_session;?>" />
	<?php 
		}
		if(! statut_to_choose($row))
		{
			?>
	<input type="hidden" name="fieldstatut"
		value="<?php echo $row->statut; ?>" />
	<?php 
		}
		if(! rapporteur_to_choose($row))
		{
			?>
	<input type="hidden" name="fieldrapporteur"
		value="<?php echo $row->rapporteur; ?>" /> <input type="hidden"
		name="fieldrapporteur2" value="<?php echo $row->rapporteur2; ?>" />
	<?php 
		}
		if(session_to_choose($row))
			displaySessionField($row);
		?>



	<input type="hidden" name="next_id" value="<?php echo $next; ?>" />
	<input type="hidden" name="previous_id" value="<?php echo $previous; ?>" />
		<input
		type="submit" name="editprevious"
		value="<<" />
	<input type="submit" name="submitandkeepediting"
		value="<?php echo (($actioname == "add") ? "Ajouter" : "Enregistrer");?>" />
		<?php 
		if(isSecretaire())
		{
		?>
	<input type="submit" name="deleteandeditnext"
		value="Supprimer" />
		<?php 
		}
		?>
	<input type="submit" name="submitandview"
		value="<?php echo (($actioname == "add") ? "Ajouter et voir" : "Enregistrer et voir");?>" />
	 <input
		type="submit" name="editnext"
		value=">>" />
		<table class="inputreport">
		<?php 


		displayStatusField($row);

		displayTypeField($row);


		global $specialtr_fields;
		global $start_tr_fields;
		global $end_tr_fields;

		foreach($active_fields as  $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			if(!in_array($fieldID,$active_fields))
				continue;
			$type = isset($fieldsTypes[$fieldID]) ?  $fieldsTypes[$fieldID] : "";
			if(in_array($fieldID, $start_tr_fields))
				echo '<tr><td></td><td><table><tr>';
			if(!in_array($fieldID, $specialtr_fields))
				echo '<tr>';
			?>
		<td style="width: 10em;"><span><?php echo $title;?> </span>
		</td>
		<?php
		switch($type)
		{
			case "topic":
				display_topic($row, $fieldID);
				break;
			case "long":
				display_long($row, $fieldID);
				break;
			case "treslong":
				display_treslong($row, $fieldID);
				break;
			case "short":
				display_short($row, $fieldID);
				break;
			case "evaluation":
				display_evaluation($row, $fieldID);
				break;
			case "avis":
				display_avis($row, $fieldID);
				break;
			case "rapporteur":
				display_rapporteur($row, $fieldID);
				break;
			case "unit":
				display_unit($row, $fieldID);
				break;
			case "grade":
				display_grade($row, $fieldID);
				break;
			case "concours":
				display_concours($row, $fieldID);
				break;
			case "ecole":
				display_ecole($row, $fieldID);
				break;
		}
		if(!in_array($fieldID, $specialtr_fields))
			echo '</tr>';
		if(in_array($fieldID, $end_tr_fields))
			echo '</tr></table></td></tr>';
		?>
		<?php
		}
		?>
		<tr>
			<td colspan="2"><input type="submit"
				value="<?php echo (($actioname == "add") ? "Ajouter" : "Enregistrer");?>" />
				<?php 
				if($next != -1)
				{
					?> <input type="submit" name="submitandeditnext"
				value="<?php echo (($actioname == "add") ? "Ajouter et éditer le suivant" : "Enregistrer et éditer le suivant");?>" />
				<?php 
				}
				?>
			</td>
		</tr>
	</table>

</form>
<?php 
}


function editReport($id_rapport, $create_new = true)
{
	try
	{
		$report = getReport($id_rapport);
		checkReportIsEditable($report);
		$row = normalizeReport($report);
		displayEditableReport($row, "update", $create_new);
	}
	catch(Exception $exc)
	{
		throw new Exception("Echec de l'édition du rapport:\n ".$exc->getMessage());
	}

};


function message_handler($subject,$body)
{
	$headers = 'From: '.webmaster. "\r\n" . 'Reply-To: '.webmaster. "\r\n" .'X-Mailer: PHP/' . phpversion()."\r\n";
	mail(webmaster, $subject, "\r\n".$body."\r\n", $headers);
}

function email_handler($recipient,$subject,$body)
{
	$headers = 'From: '.webmaster. "\r\n" . 'CC: ' .webmaster. "\r\n". 'Reply-To: '.webmaster. "\r\n".'Content-Type: text/plain; charset="UTF-8"\r\n'.'X-Mailer: PHP/' . phpversion()."\r\n";

	$result = mail($recipient, $subject, "\r\n".$body."\r\n", $headers);

	if($result == false)
		throw new Exception("Could not send email to ".$recipient." with subject ".$subject);
}

function exception_handler($exception)
{
	message_handler("Marmotte webpage :exception ",$exception->getMessage());
}


function error_handler($errno, $errstr, $errfile, $errline)
{
	$body= "Number:".$errno."\r\n String:".$errstr."\r\n File:".$errfile."\r\n Line:".$errline;
	message_handler("Marmotte webpage :error ",$body);
}

function replace_accents($string)
{
	return str_replace( array('à','á','â','ã','ä', 'ç', 'è','é','ê','ë', 'ì','í','î','ï', 'ñ', 'ò','ó','ô','õ','ö', 'ù','ú','û','ü', 'ý','ÿ', 'À','Á','Â','Ã','Ä', 'Ç', 'È','É','Ê','Ë', 'Ì','Í','Î','Ï', 'Ñ', 'Ò','Ó','Ô','Õ','Ö', 'Ù','Ú','Û','Ü', 'Ý'), array('a','a','a','a','a', 'c', 'e','e','e','e', 'i','i','i','i', 'n', 'o','o','o','o','o', 'u','u','u','u', 'y','y', 'A','A','A','A','A', 'C', 'E','E','E','E', 'I','I','I','I', 'N', 'O','O','O','O','O', 'U','U','U','U', 'Y'), $string);
}

function normalizeName($name)
{
	return str_replace('\' ', '\'', ucwords(str_replace('\'', '\' ', strtolower($name))));
}

function sql_request($sql)
{
	$result = mysql_query($sql);
	if($result == false)
		throw new Exception("Failed to process sql query: \n\t".mysql_error()."\n\n".$sql);
	else
		return $result;
}

?>