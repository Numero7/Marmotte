<?php
session_start();
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
require_once('config.inc.php');
require_once('manage_users.inc.php');
require_once('manage_unites.inc.php');
require_once('manage_rapports.inc.php');

//set_exception_handler('exception_handler');
//set_error_handler('error_handler');

function db_connect($serverName,$dbname,$login,$password)
{
	$dbh = @mysql_connect($serverName, $login, $password);
	if ($dbh)
	{
		@mysql_select_db($dbname, $dbh) or die ("<strong>Error: Could not access the required table!</strong>");
		mysql_query("SET NAMES utf8;");
	}
	return $dbh;
} ;

function db_disconnect(&$dbh)
{
	mysql_close($dbh);
	$dbh=0;
} ;


function getDescription($login)
{
	$sql = "SELECT * FROM users WHERE login='$login';";
	$result=mysql_query($sql);
	if ($row = mysql_fetch_object($result))
	{
		return $row->description;
	}
	return NULL;
} ;


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
		return "<img src=\"img/sortup.png\" alt=\"$crit sorted ascendently\"><span style=\"text-decoration:none;\">($index)</span>";
	}
	else if ($order=="DESC")
	{
		return "<img src=\"img/sortdown.png\" alt=\"$crit sorted descendently\"><span style=\"text-decoration:none;\">($index)</span>";
	}
	else
	{  return "<img src=\"img/sortneutral.png\" alt=\"$crit sorted neutrally\">";
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
		unset($sortCrit[$edit_crit]);
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


function getTypesEval($id_session){
	$finalResult = array();
	$sql = "SELECT DISTINCT type FROM (SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt INNER JOIN ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, sessions ss WHERE ss.id=tt.id_session) difftypes WHERE id_session=$id_session ORDER BY type DESC;";
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
	global $fieldsAll;
	global $actions;
	$specialRule = array(
			"nom"=>0,
			"prenom"=>0,
			"grade"=>0,
			"unite"=>0,
			"type"=>0,
			"nom_session"=>0,
			"date_session"=>0,
	);
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
	<?php echo $row->type;?>
	<?php echo $row->nom_session." ".date("Y",strtotime($row->date_session));?>
</h2>
<dl>
	<?php
	foreach($fieldsAll as  $fieldID => $title)
	{
		if (!isset($specialRule[$fieldID]))
		{
			?>
	<dt>
		<?php echo $title;?>
	</dt>
	<dd>
		<?php echo $row->$fieldID;?>
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

	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt, sessions ss WHERE tt.id=$id_rapport AND tt.id_session=ss.id;";
	$result=mysql_query($sql);
	if($result == false)
	{
		echo "Request <br/>".$sql ."<br/> failed.<br/>";
	}
	else if ($row = mysql_fetch_object($result))
	{
		if(array_key_exists($row->type,$typesRapportsUnites))
		{
			displayUnitReport($row);
		}
		else if(array_key_exists($row->type,$typesRapportsIndividuels))
		{
			displayIndividualReport($row);
		}
		else
		{
			echo "Unknown report type : ".$row->type. "(id) ".$id_rapport."<br/>";
		}
	}
	else
	{
		echo "No report with id ".$is_rapport;
	}

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
				echo "<td><a href=\"$page?action=$action&amp;id=$id&amp;id_origine=$id_origine\"><img class=\"icon\" width=\"24\" height=\"24\" src=\"$icon\" alt=\"$title\"></a></td>";
		}
}

function displayUnitReport($row)
{
	global $fieldsAll;
	global $fieldsUnites;
	global $actions;
	$specialRule = array(
				"nom"=>0,
				"prenom"=>0,
				"grade"=>0,
				"unite"=>0,
				"type"=>0,
				"nom_session"=>0,
				"date_session"=>0,
		);
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
	<?php echo $row->type;?>
	<?php echo $row->nom_session." ".date("Y",strtotime($row->date_session));?>
</h2>
<dl>
	<?php
	foreach($fieldsAll as  $fieldID => $title)
	{
		if (!isset($specialRule[$fieldID]) && in_array($fieldID,$fieldsUnites))
		{
			?>
	<dt>
		<?php echo $title;?>
	</dt>
	<dd>
		<?php echo $row->$fieldID;?>
	</dd>
	<?php
		}
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

function displayExport($statut, $id_session, $type_eval, $sort_crit, $login_rapp)
{
	global $typeExports;
	global $statutsRapports;
	echo '
		<h2>Exporter/Editer tous</h2>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		';
	foreach($typeExports as $idexp => $exp)
	{
		$expname= $exp["name"];
		$level = $exp["permissionlevel"];
		if (getUserPermissionLevel()>=$level)
		{
			echo " <a href=\"export.php?action=group&amp;statut=$statut&amp;id_session=$id_session&amp;type_eval=$type_eval&amp;sort_crit=$sort_crit&amp;login_rapp=$login_rapp&amp;type=$idexp\"><img class=\"icon\" width=\"50\" height=\"50\" src=\"img/$idexp-icon-50px.png\" alt=\"$expname\"></a>";
		}
	}
	echo '</td>';
	if (getUserPermissionLevel()>= NIVEAU_PERMISSION_PRESIDENT_SECRETAIRE)
	{
		echo '
				<td align="center">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<form method="post">
				<select name="new_statut">';
		foreach ($statutsRapports as $val => $nom)
		{
			$sel = "";
			if ($val==$statut)
			{
				$sel = " selected=\"selected\"";
			}
			echo "<option value=\"".$val."\" $sel>".$nom."</option>\n";
		}
		echo '
				</select>
				<input type="hidden" name="action" value="change_statut">
				<input type="hidden" name="sort_crit" value="'.$sort_crit.'">
				<input type="hidden" name="statut" value="'.$statut.'">
				<input type="hidden" name="id_session" value="'.$id_session.'">
				<input type="hidden" name="type_eval" value="'.$type_eval.'">
				<input type="hidden" name="login_rapp" value="'.$login_rapp.'">
				<input type="submit" value="Changer statut">
				</form>';
	}
}

function displaySummary($statut = "", $id_session =-1, $type_eval = "", $sort_crit = "", $login_rapp = "")
{
	global $fieldsSummary;
	global $fieldsAll;
	global $actions;
	global $typesRapports;
	global $statutsRapports;


	$sortCrit = parseSortCriteria($sort_crit);
	$rapporteurs = array();
	$sessions = showSessions();

	$rows = filterSortReports($statut, $id_session, $type_eval, $sort_crit, $login_rapp);
	if($rows == false)
	{
		echo 'Failed to process request';
		return;
	}
	
	foreach($rows as $row)
		$rapporteurs[$row->rapporteur] = 1;

	$krapp = array_keys($rapporteurs);
	natcasesort($krapp);
	$rapporteurs = $krapp;

	?>
<table>
	<tr>
		<td>
			<h2>Filtrage</h2>
			<form method="get">
				<table class="inputreport">
					<tr>
						<td style="width: 20em;">Statuts</td>
						<td><select name="statut">
								<option value="">Tous les statuts</option>
								<?php
								foreach ($statutsRapports as $val => $nom)
								{
									$sel = "";
									if ($val==$statut)
									{
										$sel = " selected=\"selected\"";
									}
									echo "<option value=\"".$val."\" $sel>".$nom."</option>\n";
								}
								?>
						</select></td>
					</tr>
					<tr>
						<td style="width: 20em;">Session</td>
						<td><select name="id_session">
								<option value="-1">Toutes les sessions</option>
								<?php
								foreach ($sessions as $val)
								{
									$sel = ($val["id"]==$id_session) ? " selected=\"selected\"" : "";
									echo "<option value=\"".$val["id"]."\" $sel>".ucfirst($val["nom"])." ".date("Y",strtotime($val["date"]))."</option>\n";
								}
								?>
						</select></td>
					</tr>
					<tr>
						<td>Rapporteur</td>
						<td><select name="login_rapp">
								<option value="">Tous les rapporteurs</option>
								<?php
								foreach ($rapporteurs as $rapp)
								{
									$sel = ($rapp==$login_rapp) ? " selected=\"selected\"" : "";
									echo "<option value=\"$rapp\"$sel>".ucfirst($rapp)."</option>\n";
								}
								?>
						</select></td>
					</tr>
					<tr>
						<td>Type évaluation</td>
						<td><select name="type_eval">
								<option value="">Tous les types</option>
								<?php
								foreach ($typesRapports as $ty => $value)
								{
									$sel = ($ty==$type_eval) ? " selected=\"selected\"": "";
									echo "<option value=\"$ty\"$sel>".$value."</option>\n";
								}
								?>
						</select></td>
					</tr>
					<tr>
						<td></td>
						<td><input type="hidden" name="sort_crit"
							value="<?php echo $sort_crit;?>"> <input type="hidden"
							name="action" value="view"> <input type="submit" value="Filtrer">
						</td>
					</tr>
				</table>
			</form>
		</td>
		<td><p>&nbsp;</p></td>
		<td align="center"><?php displayExport($statut, $id_session, $type_eval, $sort_crit, $login_rapp);?>
		</td>

	</tr>
</table>
<hr>
<table class="summary">
	<tr>
		<?php
		foreach($fieldsSummary as $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			?>
		<th><span class="nomColonne"><?php
		echo "<a href=\"?action=view";
		echo "&amp;statut=$statut";
		echo "&amp;id_session=$id_session";
		echo "&amp;type_eval=$type_eval";
		echo "&amp;login_rapp=$login_rapp";
		echo "&amp;sort=".dumpEditedCriteria($sortCrit, $fieldID)."\">";
		echo $title.showCriteria($sortCrit, $fieldID);
		echo "</a>";?> </span>
		</th>
		<?php
		}
		foreach($actions as $action => $actiondata)
			echo '<th></th>';
		echo '</tr>';

		$prettyunits = unitsList();
	foreach($rows as $row)
	{
		?>
	<tr>
		<?php
		foreach($fieldsSummary as $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			?>
		<td><span class="valeur"> <?php
		if($fieldID != "unite")
		{
			echo $row->$fieldID;
		}
		else
		{
			if(array_key_exists($row->unite,$prettyunits)) echo $prettyunits[$row->unite]->nickname;
			else echo $row->unite;
		}
		?>
		</span>
		</td>
		<?php
		}
		displayActionsMenu($row->id, $row->id_origine);
		?>
	
	
	<tr>
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
	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt, sessions ss WHERE tt.id_session=ss.id AND tt.id_origine=$id_origine ORDER BY date DESC;";
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
		Version modifiée le
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


function displayEditableReport($row, $actioname)
{
	global $fieldsAll;
	global $fieldsIndividual;
	global $fieldsUnites;
	global $fieldsEcoles;
	global $fieldsTypes;
	global $fieldsCandidat;
	global $actions;
	global $typesRapportsUnites;
	global $typesRapports;
	global $grades;
	global $notes;
	global $avis_eval;
	global $typesRapportToAvis;
	global $concours_ouverts;
	global $statutsRapports;

	$eval_type = $row->type;
	$is_unite = array_key_exists($eval_type,$typesRapportsUnites);
	$is_ecole = ($eval_type == 'Ecole');
	$is_candidat = ($eval_type == 'Candidature');

	$statut = $row->statut;

	$eval_name = $eval_type;
	if(array_key_exists($eval_type, $typesRapports))
		$eval_name = $typesRapports[$eval_type];

	$avis_possibles = array();
	if(array_key_exists($eval_type, $typesRapportToAvis))
		$avis_possibles = $typesRapportToAvis[$eval_type];

	?>
<h1>
	<?php 
	echo ($statutsRapports[$statut])." / ".($is_unite ? "Unite / " : "Chercheur / ").$eval_name;
	?>
</h1>

<form method="post" action="index.php" style="width: 100%">
	<input type="hidden" name="action" value=<?php echo $actioname?>> <input
		type="hidden" name="id_origine" value="<?php echo $row->id_origine;?>">
	<input type="hidden" name="fieldrapporteur"
		value="<?php echo getLogin();?>">

	<?php 
	if(!isSecretaire())
		echo '<input type="hidden" name="fieldstatut" value="'.$row->statut.'>';
	?>

	<input type="submit"
		value="<?php echo (($actioname == "add") ? "Ajouter" : "Enregistrer");?>">
	<table class="inputreport">
		<?php 
		if(isSecretaire())
		{
			echo "<tr><td>Statut</td><td><select name=\"fieldstatut\" style=\"width: 100%;\">";
			foreach($statutsRapports as $val => $nom)
			{
				$sel = ($row->statut==$val) ? "selected=\"selected\"" : "";
				echo  "\t\t\t\t\t<option value=\"$val\" $sel>$nom</option>\n";
			}
			echo "</select></td></tr>";

		}
		if($eval_type == "Evaluation-Vague" || $eval_type == "Evaluation-MiVague" )
		{
			echo "<tr><td>Evaluation</td><td><select name=\"fieldtype\" style=\"width: 100%;\">";
			$typesRapportsEvals = array(
				 "Evaluation-Vague" => $typesRapports['Evaluation-Vague'],
					"Evaluation-MiVague" => $typesRapports['Evaluation-MiVague']
		);

		foreach($typesRapportsEvals as $val => $nom)
		{
			$sel = ($eval_type==$val) ? "selected=\"selected\"" : "";
			echo  "\t\t\t\t\t<option value=\"$val\" $sel>$nom</option>\n";
		}
		echo "</select></td></tr>";
		}
		else
		{
			echo '<input type="hidden" name="fieldtype" value="'.$row->type.'">';
		}

		?>
		<tr>
			<td>Session</td>
			<td><select name="fieldid_session" style="width: 100%;">
					<?php 		
					$sessions = showSessions();
					foreach($sessions as $s)
					{
						$sel = ($row->id_session==$s["id"]) ? "selected=\"selected\"" : "";
						echo  "\t\t\t\t\t<option value=\"".$s["id"]."\" $sel>".$s["nom"]." ".date("Y",strtotime($s["date"]))."</option>\n";
					}
					?>
			</select>
			</td>
		</tr>
		<?php 

		$active_fields = $fieldsIndividual;
		if($is_unite)
			$active_fields = $fieldsUnites;
		if($is_ecole)
			$active_fields = $fieldsEcoles;
		if($is_candidat)
			$active_fields = $fieldsCandidat;



		foreach($active_fields as  $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			if(!in_array($fieldID,$active_fields))
				continue;
			$type = isset($fieldsTypes[$fieldID]) ?  $fieldsTypes[$fieldID] : "";
			?>
		<tr>
			<td style="width: 17em;"><span><?php echo $title;?> </span></td>
			<?php
			switch($type)
			{
				case "long":
					{
						echo '
		<td colspan="2"><span class="examplevaleur">'.getExample($fieldID).'
		</span>
		</td>
		</tr>
		<tr>
		<td colspan="3">
		<textarea name="field'.$fieldID.'" style="width: 100%;">'.strip_tags($row->$fieldID).'</textarea>
		</td>
		';
					}
					break;
				case "treslong":
					{
						echo '
		<td colspan="2"><span class="examplevaleur">'.getExample($fieldID).'
			</span>
			</td>
			</tr>
			<tr>
			<td colspan="3">
			<textarea rows=10 name="field'.$fieldID.'" style="width: 100%;">'.strip_tags($row->$fieldID).'</textarea>
				</td>
				';
					}break;
				case "short":
					{
						?>
			<td style="width: 30em;"><input name="field<?php echo $fieldID;?>"
				value="<?php echo $row->$fieldID;?>" style="width: 100%;">
			</td>
			<td><span class="examplevaleur"><?php echo getExample($fieldID);?> </span>
			</td>
			<?php
					}break;
case "evaluation":
	{
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
	break;
case "avis":
	{
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
	break;
case "unit":
	{
		?>
			<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
				style="width: 100%;">
					<?php
					$units = prettyUnitsList();
					foreach($units as $unite)
					{
						$sel = (($row->unite) == ($unite->code)) ? "selected=\"selected\"" : "";
						echo  "\t\t\t\t\t<option value=\"".($unite->code)."\"".$sel.">".($unite->nickname)."</option>\n";
					}
					?>
			</select>
			</td>
			<?php
	}
	break;
case "grade":
	{
		echo '<td><select name="fieldgrade" style="width: 100%;">';
		foreach($grades as $idg => $txtg)
		{
			$sel = ($row->grade==$idg) ? 'selected="selected"' : "";
			echo  "\t\t\t\t\t<option value=\"$idg\" $sel>$txtg</option>\n";
		}
		echo '</select></td>';
	}
	break;
case "concours":
	{
		echo '<td><select name="fieldconcours" style="width: 100%;">';
		foreach($concours_ouverts as $concours)
		{
			$sel = ($row->concours==$concours) ? "selected=\"selected\"" : "";
			echo  "\t\t\t\t\t<option value=\"$concours\" $sel>$concours</option>\n";
		}
		echo '</select></td>';
	}
	break;
case "ecole":
	echo '<td colspan="3"><input name="fieldecole" value="<?php echo $row->ecole ?>" style="width: 100%;"> </td>';
	break;
			}
			?>
		</tr>
		<?php
		}
		?>
		<tr>
			<td colspan="2"><input type="submit"
				value="<?php echo (($actioname == "add") ? "Ajouter" : "Enregistrer");?>">
			</td>
		</tr>
	</table>

</form>
<?php 
}


function editReport($id_rapport)
{
	$row = getReport($id_rapport);
	if($row)
		displayEditableReport($row, "update");
};

function newReport($type_rapport)
{
	global $empty_report;
	$row = (object) $empty_report;
	$row->type = $type_rapport;
	$row->id_session = current_session_id();
	displayEditableReport($row, "add");
} ;


function message_handler($subject,$body)
{
	$headers = 'From: '.webmaster. "\r\n" . 'Reply-To: '.webmaster. "\r\n" .'X-Mailer: PHP/' . phpversion()."\r\n";
	mail(webmaster, $subject, "\r\n".$body."\r\n", $headers);
}

function email_handler($recipient,$subject,$body)
{
	$headers = 'From: '.webmaster. "\r\n" . 'Reply-To: '.webmaster. "\r\n".'Content-Type: text/plain; charset="UTF-8"\r\n'.'X-Mailer: PHP/' . phpversion()."\r\n";

	return mail($recipient, $subject, "\r\n".$body."\r\n", $headers);
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

?>