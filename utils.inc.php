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
	if(isset($_REQUEST["filter_".$filter_name]))
		$answer = $_REQUEST["filter_".$filter_name] != "" ? $_REQUEST["filter_".$filter_name] : $filters[$filter_name]['default_value'];
	else if(isset($_SESSION["filter_".$filter_name]))
		$answer =   $_SESSION["filter_".$filter_name];
	$_SESSION["filter_".$filter_name] = $answer;
	return $answer;
}


function setSortingValue($filter_name, $value)
{
	
	$_REQUEST["tri_".$filter_name] = $value;
	$_SESSION["tri_".$filter_name] = $value;
}

function getSortingValue($filter_name)
{
	global $filtersAll;
	$filters = $filtersAll;
	$answer = "";
	if(isset($_REQUEST["tri_".$filter_name]))
		$answer = $_REQUEST["tri_".$filter_name];
	else if(isset($_SESSION["tri_".$filter_name]))
		$answer =   $_SESSION["tri_".$filter_name];

	$last = substr($answer,strlen($answer) -1,1);
	if( $last != "+" && $last != "-")
		$answer .= "+";

	$_SESSION["tri_".$filter_name] = $answer;
	
	
	return $answer;
}

function resetOrder()
{
	$filters = getCurrentSortingList();
	foreach($filters as $filter)
		if(!isset($_REQUEST["tri_".$filter]))
			$_REQUEST["tri_".$filter] = strval(count($filters) + 10)."+";
}

function resetFilterValues()
{
	$filters = getCurrentFiltersList();
	foreach($filters as $filter => $data)
		if(!isset($_REQUEST["filter_".$filter]))
		$_REQUEST["filter_".$filter] = $data['default_value'];
	resetOrder();
}

function resetFilterValuesExceptSession()
{
	$filters = getCurrentFiltersList();
	foreach($filters as $filter => $data)
		if($filter != 'id_session' && !isset($_REQUEST["filter_".$filter]))
		$_REQUEST["filter_".$filter] = $data['default_value'];
	resetOrder();
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

function getCurrentSortingList()
{
	global $fieldsSummary;
	global $fieldsTriCandidates;
	if(is_current_session_concours())
		return $fieldsTriCandidates;
	else
		return $fieldsSummary;
}

function getFilterValues()
{
	$filters = getCurrentFiltersList();
	$filter_values = array();
	foreach($filters as $filter => $data)
		$filter_values[$filter] =  getFilterValue($filter);
	return $filter_values;
}

function getSortingValues()
{
	$filters = getCurrentSortingList();
	$filter_values = array();
	foreach($filters as $filter)
		$filter_values[$filter] =  getSortingValue($filter);

	$sorted = array();
	$max = 0;
	foreach($filter_values as $field => $value)
	{
		$index = intval(substr($value,0,strlen($value) -1));
		$max = max( $max, $index);
		while(key_exists($index, $sorted))
			$index++;
		$sorted[$index] = $field;
	}

	ksort($sorted);

	$result = array();
	$index = 1;
	foreach($sorted as $key => $field)
	{
		$value = $filter_values[$field];
		if($key < $max)
			$result[$field] = $index.substr($value,strlen($value) -1,1);
		else
			$result[$field] = $max."+";
		setSortingValue($field, $result[$field]);
		$index++;
	}
	return $result;
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
	displayActionsMenu($row, "details", $actions);
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


function valueFromField($field,$value,$units,$users,$themes)
{
	global $fieldsTypes;
	if(isset($fieldsTypes[$field]))
	{
	switch($fieldsTypes[$field])
	{
		case 'unit':
			return $units[$value]->prettyname;
			break;
		case 'rapporteur':
			return $users[$value]->description;
			break;
		case 'topic':
			return $themes[$value];
			break;
	}
	}
	return $value;
}

function displayConcoursReport($row)
{
	global $fieldsRapportsCandidat;
	global $fieldsAll;
	global $actions;
	global $typesRapportsConcours;
	
	global $fieldsCandidat;
	global $fieldsCandidatAll;
	global $topics;
	
	$units = unitsList();
	$users = listRapporteurs();
	$themes = $topics;
	
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
	$candidat = get_or_create_candidate($row);
	
	?>
<div class="tools">
	<?php
	displayActionsMenu($row, "details", $actions);
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
<table>
	<?php
	foreach($fieldsCandidat as  $fieldID)
	{
?>
	<tr><th style="text-align:LEFT">
		<?php echo $fieldsCandidatAll[$fieldID];?>
	</th>
	<td>
		<?php echo valueFromField($fieldID, remove_br($candidat->$fieldID), $units,$users,$themes);?>
		</td>
	</tr>
	
	
<?php 		
	}
	$fields = get_editable_fields($row);
	
	foreach($fields as  $fieldID)
	{
		if (!isset($specialRule[$fieldID]))
		{
			?>
	<tr><th style="text-align:LEFT">
		<?php echo $fieldsAll[$fieldID];?>
	</th>
	<td>
		<?php echo valueFromField($fieldID, remove_br($row->$fieldID), $units,$users,$themes);?>
	</td></tr>
	<?php
		}
	}
	?>
	</table>
</dl>
<div></div>
<?php
} ;

function displayReport($id_rapport)
{
	global $typesRapportsUnites;
	global $typesRapportsIndividuels;
	global $typesRapportsConcours;

	?>
<form method="post" action="index.php">
	<input type="hidden" name="action" value="details" /> <input
		type="hidden" name="id" value="<?php echo$id_rapport;?>" /> <input
		type="submit" name="detailsprevious"
		value="<<" >
		<input 
		
		
		 type="submit" name="detailsnext"
		value=">>">
</form>
<?php 

$row = getReport($id_rapport);
if(array_key_exists($row->type,$typesRapportsUnites))
	displayUnitReport($row);
else if(array_key_exists($row->type,$typesRapportsIndividuels))
	displayIndividualReport($row);
else if(array_key_exists($row->type,$typesRapportsConcours))
	displayConcoursReport($row);
else
	throw new Exception("Cannot display report with id".$id_rapport.": unknown report type : '".$row->type."'<br/>");
}

function displayActionsMenu($row, $excludedaction = "", $actions)
{
	$id = $row->id;
	$id_origine = $row->id_origine;
	
	foreach($actions as $action => $actiondata)
		if ($action!=$excludedaction)
		{
			$title = $actiondata['title'];
			$icon = $actiondata['icon'];
			$page = $actiondata['page'];
			$level = $actiondata['level'];
			if(getUserPermissionLevel() >= $level || ($action == 'edit' && isReportEditable($row)) )
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
	displayActionsMenu($row, "details", $actions);
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

	if (getUserPermissionLevel()>= NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE)
		echo '<table><tr><td style="width: 10em;"><h2>Export</h2> </td><td>';

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
				</td><td align="center">
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


function displaySummary($filters, $filter_values, $sorting_values)
{
	global $fieldsSummary;
	global $fieldsSummaryCandidates;
	global $typesRapports;
	global $statutsRapports;
	global $filtersReports;
	global $fieldsTypes;

	$rows = filterSortReports($filters, $filter_values, $sorting_values);


	$filters['login_rapp']['liste'] = simpleListUsers();

	$units = simpleUnitsList();
	foreach($fieldsTypes as $field => $type)
		if($type=='unit' and array_key_exists($field, $filters))
		$filters[$field]['liste'] = $units;

	$fields = is_current_session_concours() ? $fieldsSummaryCandidates : $fieldsSummary;

	if($filter_values['type'] != $filters['type']['default_value'] )
	{
		$new_field = array();
		foreach($fields as $field)
			if($field != 'type')
			$new_field[] = $field;
		$fields = $new_field;
	}


	displayRows($rows,$fields, $filters, $filter_values, getCurrentSortingList(), $sorting_values);
}

function displayRapporteurMenu($fieldID,$row,$users)
{
	if(isSecretaire())
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
</form>
<?php 
	}
	else
	{
		echo $users[$row->$fieldID]->description;
	}
}

function displayAvisMenu($fieldId,$row)
{
	global $typesRapportToAvis;
	if(isset($typesRapportToAvis[$row->type]))
	{
		$aviss = $typesRapportToAvis[$row->type];
		?>
<form method="post" action="index.php">
	<table>
		<tr>
			<td><input type="hidden" name="action"
				value="set<?php echo $fieldId;?>" /> <input type="hidden"
				name="id_toupdate" value="<?php echo $row->id;?>" /> <select
				name="new<?php echo $fieldId;?>">
					<?php
					foreach($aviss as $avis => $pretty)
					{
						$sel = (($row->$fieldId) == ($avis)) ? "selected=\"selected\"" : "";
						echo  "\t\t\t\t\t<option value=\"".($avis)."\" ".$sel.">".$pretty."&nbsp;</option>\n";
					}
					?>
			</select>
			</td>
			<td><input type="submit" value="OK"></input>
			</td>
		</tr>
	</table>
</form>
<!-- <a href="javascript:getScrollXY();">OK</a>  -->
<?php 
	}
	else
	{
		echo $row->$fieldId;
	}
}

function displayTri($rows, $sortFields, $sorting_values)
{
	global $fieldsAll;

	$tritypes = getTriTypes($sorting_values);

	?>
<table>
	<tr>
		<td style="width: 10em;"><h2>Tri</h2>
		</td>
		<td>
			<table class="inputreport">
				<tr>

					<?php
					$count = 0;
					foreach($sortFields as $criterion)
					{
						if(isset($sorting_values[$criterion]))
						{
							$count++;
							?>
					<td><?php echo $fieldsAll[$criterion];?></td>
					<td><select name="tri_<?php echo $criterion;?>" style="width: 4em;">
							<?php
							foreach ($tritypes as $value => $nomitem)
							{
								$sel = "";
								if ($value == $sorting_values[$criterion])
									$sel = " selected=\"selected\"";
								echo "<option value=\"".$value."\" $sel>".$nomitem."</option>\n";
							}
							?>
					</select></td>
					<?php 
					if($count %6 == 0)
						echo '</tr><tr>';
						}
					}
					?>
				</tr>
			</table>
		</td>
	</tr>
</table>
<?php

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
		<td style="width: 10em;"><h2>Filtrage</h2>
		</td>
		<td>
			<table class="inputreport">
				<tr>

					<?php
					$count = 0;
					foreach($filters as $filter => $data)
						if(isset($data['liste']))
						{
							$count++;
							/*
							 if(in_array($filter, $start_tr_fields))
								echo '<tr><td></td><td><table><tr>';
							if(!in_array($filter, $specialtr_fields))
								echo '<tr>';
							*/
							?>
					<td><?php echo $data['name'];?></td>
					<td><select name="filter_<?php echo $filter?>">
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
					if($count %3 == 0)
						echo '</tr><tr>';
					/*
					 if(!in_array($filter, $specialtr_fields))
						echo '</tr>';
					if(in_array($filter, $end_tr_fields))
						echo '</tr></table></td></tr>';
					*/
						}
						?>
				</tr>
			</table>
		</td>
	</tr>
</table>
<!-- END  Menu filtrage -->

<?php
}

function displayRows($rows, $fields, $filters, $filter_values, $sort_fields, $sorting_values)
{
	global $fieldsAll;
	global $actions;
	global $fieldsTypes;
	global $specialtr_fields;
	global $start_tr_fields;
	global $end_tr_fields;

	?>
			<form method="post" action="index.php">
	<table>
<tr>
		<td>
				<table>
					<tr>
						<td><hr /> <?php 
						displayTri($rows, $sort_fields, $sorting_values);
						?>
						</td>
					</tr>
					<tr>
						<td><hr /> <?php 
						displayFiltrage($rows, $fields, $filters, $filter_values);
						?>
						</td>
					</tr>
				</table>

		</td>
		<td><?php displayExport(getFilterValues());?>
		</td>
	</tr>
	<tr>
		<td><hr /><input type="hidden" name="action" value="view" /> <input
			type="submit" value="Rafraîchir" /> <?php 	echo "(".count($rows)." rapports)";?>
		</td>
	</tr>
	
</table>
				</form>
<hr />
<table class="summary">
	<tr>
		<th></th>
		<?php
		foreach($fields as $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			?>
		<th><span class="nomColonne"> <?php 
		echo '<a href="?action=view&amp;reset_tri=&amp;tri_'.$fieldID."=1\">".$title.'</a>';
		?>
		</span>
		</th>
		<?php
		}
		echo '</tr>';

		$prettyunits = unitsList();
		$users = listRapporteurs();

		global $actions1;
		global $actions2;

		foreach($rows as $row)
		{

			?>
	
	
	<tr id="t<?php echo $row->id;?>">
		<?php
			
		echo '<td><table><tr>';
		displayActionsMenu($row,"", $actions1,$row->rapporteur, $row->rapporteur);
		echo '</tr></table></td>';

		foreach($fields as $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			echo '<td>';
			$data = $row->$fieldID;
			$type = isset($fieldsTypes[$fieldID]) ?  $fieldsTypes[$fieldID] : "";

			if($type=="rapporteur")
			{
				displayRapporteurMenu($fieldID,$row,$users);
			}
			else if(isSecretaire() &&  $type=="avis")
			{
				displayAvisMenu($fieldID,$row);
			}
			else if($data != "")
			{
				if($fieldID=="nom")
				{
					echo "<a href=\"?action=details&amp;id=".($row->id)."\">";
					echo '<span class="valeur">'.$data.'</span>';
					echo '</a>';
				}
				else
				{
					if( ($type == "unit") && isset($prettyunits[$row->$fieldID]))
						$data = $prettyunits[$row->$fieldID]->nickname;
					echo '<span class="valeur">'.$data.'</span>';
				}
			}
			echo '</td>';
		}
		displayActionsMenu($row,"", $actions2);
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
	displayActionsMenu($row, "history", $actions);
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

function get_editable_fields($row)
{
	global $fieldsIndividual;
	global $fieldsUnites;
	global $fieldsEcoles;
	global $fieldsRapportsCandidat;
	global $fieldsGeneric;
	global $fieldsEquivalence;

	global $typesRapportsUnites;
	global $typesRapportsIndividuels;

	global $fieldsRapportsCandidat0;
	global $fieldsRapportsCandidat1;
	global $fieldsRapportsCandidat2;
	
	$eval_type = $row->type;

	if($eval_type == 'Ecole')
		return $fieldsEcoles;
	else if(array_key_exists($eval_type,$typesRapportsUnites))
		return $fieldsUnites;
	else if(array_key_exists($eval_type,$typesRapportsIndividuels))
		return $fieldsIndividual;
	else if($eval_type == 'Candidature')
	{
		$f0 = $fieldsRapportsCandidat0;
		$f1 = $fieldsRapportsCandidat1;
		$f2 = $fieldsRapportsCandidat2;
		
		if(getLogin() == $row->rapporteur)
			return array_merge($f0,$f1);
		else if(getLogin() == $row->rapporteur2)
			return array_merge($f0,$f2);
		else
			return array_merge($f0, $f1, $f2);
	}
	else if($eval_type == 'Equivalence')
		return $fieldsEquivalence;
	else
		return $fieldsGeneric;
}


function statut_to_choose($row)
{
	return isSecretaire();
}


function type_to_choose($row)
{
	$eval_type = $row->type;
	return $eval_type == "Evaluation-Vague" || $eval_type == "Evaluation-MiVague";
}

function displayTypeField($row)
{
	global $typesRapports;
	if(isset($row->type))
	{
		$eval_type = $row->type;

		if( type_to_choose($row) )
		{

			echo "<tr><td>Evaluation</td><td><select name=\"fieldtype\" style=\"width: 100%;\">";
			$typesRapportsEvals["Evaluation-Vague"]  = $typesRapports['Evaluation-Vague'];
			$typesRapportsEvals["Evaluation-MiVague"] = $typesRapports['Evaluation-MiVague'];

			foreach($typesRapportsEvals as $val => $nom)
			{
				$sel = ($eval_type==$val) ? " selected=\"selected\"" : "";
				echo  "\t\t\t\t\t<option value=\"$val\" $sel>$nom</option>\n";
			}
			echo "</select></td></tr>";
		}
	}
}

function session_to_choose($row)
{
	return false;
}

function displaySessionField($row)
{
	if(session_to_choose($row))
	{
		?>
<input
	type="hidden" name="fieldid_session"
	value="<?php echo $row->id_session;?>" />
<?php 
	}
}

function display_long($row, $fieldID)
{
	echo '
		<td colspan="3">
		<textarea name="field'.$fieldID.'" rows="5" cols="50" style="width: 100%;">'.remove_br($row->$fieldID).'</textarea>
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
		<textarea rows="15" cols="50" name="field'.$fieldID.'" >'.remove_br($row->$fieldID).'</textarea>
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

function display_statut($row, $fieldID)
{
	echo "<tr><td>".$fieldID.'<select name="field'.$fieldID.'" >';
	foreach($statutsRapports as $val => $nom)
	{
		$sel = ($row->$fieldID==$val) ? "selected=\"selected\"" : "";
		echo  "\t\t\t\t\t<option value=\"$val\" $sel>$fieldID</option>\n";
	}
	echo "</select></td></tr>";
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
	foreach($concours_ouverts as $code => $value)
	{
		$sel = ($row->concours==$code) ? "selected=\"selected\"" : "";
		echo  "\t\t\t\t\t<option value=\"$code\" $sel>$value</option>\n";
	}
	echo '</select></td>';
}

function display_ecole($row, $fieldID)
{
	echo '<td colspan="3"><input name="fieldecole" value="'.$row->ecole.'" style="width: 100%;"/> </td>';
}

function display_fichiers($row, $fieldID)
{
	echo '<td colspan="3"><a href="docs/f.doc">test</a></td>';
}

function nextt($id)
{
	if(isset($_SESSION['rows_id']))
	{
		$rows_id = $_SESSION['rows_id'];
		$id;
		$n = count($rows_id) ;
		for($i = 0; $i < $n; $i++)
		{
			if($rows_id[$i] == $id)
			{
				if($i < $n - 1)
					return $rows_id[$i+1];
				else
					return $rows_id[0];
			}
		}
	}
	return -1;
}

function previouss($id)
{
	if(isset($_SESSION['rows_id']))
	{
		$rows_id = $_SESSION['rows_id'];
		$n = count($rows_id) ;
		for($i = 0; $i < $n; $i++)
		{
			if($rows_id[$i] == $id)
			{
				if($i > 0)
					return $rows_id[$i-1];
				else
					return $rows_id[$n-1];
			}
		}
	}
	return -1;
}


function displayEditableCandidate($candidate,$report = NULL)
{

	global $fieldsCandidatAll;
	global $fieldsCandidat;

	$fields = $fieldsCandidat;

	global $avis_candidature_necessitant_pas_rapport_sousjury;
	global $fieldsCandidatAvantAudition;
	
	$hidden = array("action" => "update");
	
	$hidden["previouscandidatekey"] = $candidate->cle;
	
	if($report != NULL)
	{
		$hidden["id_origine"] = $report->id_origine;
		$hidden["fieldanneecandidature"] = session_year($report->id_session);
		$hidden["type"] = $report->type;
		$hidden["id_session"] = $report->id_session;
		if(in_array($report->avis, $avis_candidature_necessitant_pas_rapport_sousjury))
			$fields = $fieldsCandidatAvantAudition;
		
	}
	
	
	echo '<h1>Candidat: '.$candidate->nom." ".$candidate->prenom." ".'</h1>';




	displayEditionFrameStart("",$hidden,array());

	displayEditableObject("", $candidate, $fields);

	displayEditionFrameEnd("Données candidat");

}

function displayEditionFrameStart($titlle, $hidden, $submit)
{
	echo "<!-- displayEditableObject ".$titlle." -->\n";

	if($titlle != "")
		echo '<span  style="font-weight:bold;" >'.$titlle.'</span>';

	foreach($hidden as $key => $value)
		echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />'."\n";
	foreach($submit as $key => $value)
		echo '<input type="submit" name="'.$key.'" value="'.$value.'" />'."\n";

}

function displayEditionFrameEnd($titlle)
{
	echo "<!-- Fin de displayEditableObject ".$titlle." -->\n";
}

function displayEditableObject($titlle, $row, $fields)
{
	global $fieldsAll;


	if($titlle != "")
		echo '<table><tr><td><span  style="font-weight:bold;" >'.$titlle.'</span></td></tr>';
	else
		echo '<table>';

	displayTypeField($row);

	global $specialtr_fields;
	global $start_tr_fields;
	global $end_tr_fields;
	global $fieldsAll;
	global $fieldsTypes;

	$inline = false;

	foreach($fields as  $fieldID)
	{
		$title = $fieldsAll[$fieldID];
		if(!in_array($fieldID,$fields))
			continue;
		$type = isset($fieldsTypes[$fieldID]) ?  $fieldsTypes[$fieldID] : "";
		if(!in_array($fieldID, $specialtr_fields) || in_array($fieldID, $start_tr_fields))
			echo '<tr>';
		?>
<td style="width: 10em;"><span><?php echo $title;?> </span>
</td>
<?php

if(in_array($fieldID, $start_tr_fields))
	echo '<td><table><tr>';

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
	case "files":
		display_fichiers($row, $fieldID);
		break;
	case "":
		break;
	default:
		throw new Exception("Unnown data ttype ".$type);
}
if(!in_array($fieldID, $specialtr_fields))
	echo '</tr>';
if(in_array($fieldID, $end_tr_fields))
	echo '</tr></table></td></tr>';
	}

	?>
</table>
<?php 

}

function displayEditableReport($row)
{
	global $fieldsAll;
	global $fieldsTypes;
	global $actions;
	global $typesRapportsUnites;
	global $avis_eval;

	global $typesRapports;
	global $statutsRapports;

	global $typesRapportsIndividuels;
	global $typesRapportsConcours;

	$create_new = true;

	$eval_type = $row->type;
	$is_unite = array_key_exists($eval_type,$typesRapportsUnites);
	$editable_fields = get_editable_fields($row);
	$statut = $row->statut;

	$eval_name = $eval_type;
	if(array_key_exists($eval_type, $typesRapports))
		$eval_name = $typesRapports[$eval_type];

	$next = nextt($row->id_origine);
	$previous = previouss($row->id_origine);

	if($next == 0)
		echo "arrgh";

	echo '<form method="post" action="index.php" style="width: 100%">'."\n";


	$submits = array();
	$submits["editprevious"] = "<<";
	$submits["submitandkeepediting"] = "Enregistrer";
	if(isSecretaire())
		$submits["deleteandeditnext"] = "Supprimer";
	$submits["submitandview"] = "Enregistrer et voir";
	$submits["retourliste"] = "Retour à la liste";
	$submits["editnext"] = ">>";

	displayEditionFrameStart("",array(),$submits);

	if(in_array($eval_type, $typesRapportsConcours))
	{
		$candidate = get_or_create_candidate($row);
		displayEditableCandidate($candidate,$row);
		echo "<br/><hr/><br/>";
	}
	else if(in_array($eval_type,$typesRapportsIndividuels))
	{
		$chercheur = chercheur_of_report($row);
		echo "<hr/>";
		displayEditableCandidate($candidate);
	}



	$hidden = array(
				"action" => "update",
				"create_new" => $create_new,
				"id_origine" => $row->id_origine);
			if(!type_to_choose($row))
				$hidden["fieldtype"] = $row->type;
			if(! session_to_choose($row))
				$hidden["fieldid_session"] = $row->id_session;
			if(! session_to_choose($row))
				$hidden["fieldstatut"] = $row->statut;
			if(! rapporteur_to_choose($row))
			{
				$hidden["fieldrapporteur"] = $row->rapporteur;
				$hidden["fieldrapporteur2"] = $row->rapporteur2;
			}
			$hidden["next_id"] = strval($next);
			$hidden["previous_id"] = strval($previous);



			if(statut_to_choose($row))
				$fields[] = "statut";




			if(in_array($eval_type, $typesRapportsConcours))
			{
				global $fieldsRapportsCandidat0;
				global $fieldsRapportsCandidat1;
				global $fieldsRapportsCandidat2;

				
				?>
<h1>
	<?php 
	echo $eval_name. ": ". $row->nom." ".$row->prenom.(isset($row->concours) ? (" / concours ".$row->concours) : ""). " (#".(isset($row->id) ? $row->id : "New").")";
	?>
</h1>

<?php 


displayEditionFrameStart("",$hidden,array());

echo'<table><tr><td VALIGN="top">';
displayEditableObject("Rapport section", $row,$fieldsRapportsCandidat0);

if(isSecretaire())
{
	if($row->rapporteur != "")
	{
		echo'</td><td VALIGN="top">';
		displayEditableObject("Prérapport 1", $row,$fieldsRapportsCandidat1);
	}
	if($row->rapporteur2 != "")
	{
		echo'</td><td VALIGN="top">';
		displayEditableObject("Prérapport 2",$row,$fieldsRapportsCandidat2);
	}
}
else if(getLogin() == $row->rapporteur)
{
	echo "<hr/>";
	echo'</td><td VALIGN="top">';
	displayEditableObject("Prérapport 1",$row,$fieldsRapportsCandidat1);
}
else if(getLogin() == $row->rapporteur2)
{
	echo "<hr/>";
	echo'</td><td VALIGN="top">';
	displayEditableObject("Prérapport 2", $row,$fieldsRapportsCandidat2);
}
echo'</td></tr></table>';


			}
			else
			{
				echo "<hr/>";
				displayEditionFrameStart("Données rapport",$hidden,$submits);
				displayEditableObject("Prérapport", $row,$editable_fields,$hidden,$submits);
			}

			displayEditionFrameEnd("Données rapport");

			echo "</form>\n";


}


function editReport($id_rapport)
{
	try
	{
		$report = getReport($id_rapport);
		checkReportIsEditable($report);
		$row = normalizeReport($report);
		displayEditableReport($row);
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
	echo $sql."<br>";
	$result = mysql_query($sql);
	if($result == false)
		throw new Exception("Failed to process sql query: <br/>\t".mysql_error()."<br/>".$sql);
	else
		return $result;
}

?>