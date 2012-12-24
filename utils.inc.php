<?php
session_start();
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
require_once('config.inc.php');

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

function parseSortCriteria($sort_crit)
{
	$result = array();
	$pieces = explode(";", $sort_crit);
	foreach($pieces as $crit)
	{
		$firstChar = substr($crit,0,1);
		$crit = substr($crit,1);
		if ($firstChar=="*")
		{
			$result[$crit]= "ASC";
		}
		else if ($firstChar=="-")
		{
			$result[$crit]= "DESC";
		}
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

function sortCriteriaToSQL($sortCrit)
{
	$sql = "";
	foreach($sortCrit as $crit => $order)
	{
		if ($sql == "")
		{
			$sql = "ORDER BY ";
		}
		else
		{ $sql .= ", ";
		}
		$sql .= $crit." ".$order;
	}
	return $sql;
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
	foreach($actions as $action => $actionTitle)
	{
		if ($action!="details")
		{
			echo "<a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\">$actionTitle <img class=\"icon\" src=\"img/$action-icon-24px.png\" alt=\"$action\"></a>";
		}
	}
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
	if ($row = mysql_fetch_object($result))
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
	foreach($actions as $action => $actionTitle)
	{
		if ($action!="details")
		{
			echo "<a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\">$actionTitle <img class=\"icon\" src=\"img/$action-icon-24px.png\" alt=\"$action\"></a>";
		}
	}
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

function filterSortReports($id_session, $type_eval, $sort_crit, $login_rapp, $id_origin=-1)
{
	$sortCrit = parseSortCriteria($sort_crit);

	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt INNER JOIN ( SELECT id, MAX(date) AS date FROM evaluations GROUP BY id_origine) mostrecent ON tt.date = mostrecent.date, sessions ss WHERE ss.id=tt.id_session ";
	if ($id_session!=-1)
	{
		$sql .= " AND id_session=$id_session ";
	}
	if ($id_origin!=-1)
	{
		$sql .= " AND id_origine=$id_origin ";
	}
	if ($type_eval!="")
	{
		$sql .= " AND type=\"$type_eval\" ";
	}
	if ($login_rapp!="")
	{
		$sql .= " AND rapporteur=\"$login_rapp\" ";
	}
	$sql .= sortCriteriaToSQL($sortCrit);
	$sql .= ";";
	//echo $sql;
	$result=mysql_query($sql);
	return $result;
}

function displaySummary($id_session, $type_eval, $sort_crit, $login_rapp)
{
	global $fieldsSummary;
	global $fieldsAll;
	global $actions;
	global $typesRapports;
	global $typeExports;

	$result = filterSortReports($id_session, $type_eval, $sort_crit, $login_rapp);
	$sortCrit = parseSortCriteria($sort_crit);
	$rapporteurs = array();
	$sessions = showSessions();
	$rows = array();
	while ($row = mysql_fetch_object($result))
	{
		$rows[] = $row;
		$rapporteurs[$row->rapporteur] = 1;
	}
	$krapp = array_keys($rapporteurs);
	natcasesort($krapp);
	$rapporteurs = $krapp;

	?>
<h2>Filtrage</h2>
<form method="get">
	<table class="inputreport">
		<tr>
			<td style="width: 20em;">Session</td>
			<td><select name="id_session">
					<option value="-1">Toutes les sessions</option>
					<?php
					foreach ($sessions as $val)
					{
						$sel = "";
						if ($val["id"]==$id_session)
						{
							$sel = " selected=\"selected\"";
						}
						echo "<option value=\"".$val["id"]."\" $sel>".ucfirst($val["nom"])." ".date("Y",strtotime($val["date"]))."</option>";
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
						$sel = "";
						if ($rapp==$login_rapp)
						{
							$sel = " selected=\"selected\"";
						}
						echo "<option value=\"$rapp\"$sel>".ucfirst($rapp)."</option>";
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
						$sel = "";
						if ($ty==$type_eval)
						{
							$sel = " selected=\"selected\"";
						}
						echo "<option value=\"$ty\"$sel>".ucfirst($value)."</option>";
					}
					?>
			</select></td>
		</tr>
		<tr>
			<td></td>
			<td><input type="hidden" name="sort_crit"
				value="<?php echo $sort_crit;?>"> <input type="hidden" name="action"
				value="view"> <input type="submit" value="Filtrer">
			</td>
		</tr>
	</table>
</form>
<br>
<br>
<h2>Exporter :</h2>
<p>
	<?php
	foreach($typeExports as $idexp => $exp)
	{
		$expname= $exp["name"];
		echo " <a href=\"export.php?action=group&amp;id_session=$id_session&amp;type_eval=$type_eval&amp;sort_crit=$sort_crit&amp;login_rapp=$login_rapp&amp;type=$idexp\"><img class=\"icon\" width=\"50\" height=\"50\" src=\"img/$idexp-icon-50px.png\" alt=\"$expname\"></a>";
	}
	?>
</p>
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
		echo "&amp;id_session=$id_session";
		echo "&amp;type_eval=$type_eval";
		echo "&amp;login_rapp=$login_rapp";
		echo "&amp;sort=".dumpEditedCriteria($sortCrit, $fieldID)."\">";
		echo $title.showCriteria($sortCrit, $fieldID);
		echo "</a>";?> </span></th>
		<?php
		}
		foreach($actions as $action => $actionTitle)
{?>
		<th></th>
		<?php
}
?>
	</tr>
	<?php
	foreach($rows as $row)
	{
		?>
	<tr>
		<?php
		foreach($fieldsSummary as $fieldID)
		{
			$title = $fieldsAll[$fieldID];
			?>
		<td><span class="valeur"><?php echo $row->$fieldID;?> </span></td>
		<?php
		}
		foreach($actions as $action => $actionTitle)
		{
			echo "<td><a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\"><img class=\"icon\" width=\"24\" height=\"24\" src=\"img/$action-icon-24px.png\" alt=\"$actionTitle\"></a></td>";
		}
		echo "<td><a href=\"export.php?action=viewpdf&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\"><img class=\"icon\" width=\"24\" height=\"24\" src=\"img/pdf-icon-24px.png\" alt=\"$actionTitle\"></a></td>";
		echo "<td><a href=\"export.php?action=viewhtml&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\"><img class=\"icon\" width=\"24\" height=\"24\" src=\"img/html-icon-24px.png\" alt=\"$actionTitle\"></a></td>";
		?>
	
	
	<tr>
		<?php
	}
	?>

</table>
<?php
} ;

function showSessions()
{
	$finalResult = array(); $sql = "SELECT * FROM sessions ORDER BY date DESC;"; $result=mysql_query($sql);
	while ($row = mysql_fetch_object($result))
	{
		$finalResult[] = array( "id" => $row->id, "nom" => $row->nom, "date" => $row->date, );
	}
	return	$finalResult;
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
	foreach($actions as $action => $actionTitle)
	{
		if ($action!="history")
		{
			echo "<a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$id_origine."\">$actionTitle <img class=\"icon\" src=\"img/$action-icon-24px.png\" alt=\"$action\"></a>";
		}
	}
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

function getReport($id_rapport)
{
	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt, sessions ss WHERE tt.id=$id_rapport ORDER BY date DESC LIMIT 1;";
	$result=mysql_query($sql);
	return mysql_fetch_object($result);
}

function editReport($id_rapport)
{
	$row = getReport($id_rapport);
	if($row)
		displayEditableReport($row, "update");
};

function newReport($type_rapport)
{
	global $fieldsAll;
	global $fieldsTypes;
	global $grades;
	global $actions;
	global $empty_report;

	$row = (object) $empty_report;
	$row->type = $type_rapport;
	displayEditableReport($row, "add");
} ;

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


	$eval_type = $row->type;
	$is_unite = array_key_exists($eval_type,$typesRapportsUnites);
	$is_ecole = ($eval_type == 'Ecole');
	$is_candidat = ($eval_type == 'Candidature');

	$eval_name = $eval_type;
	if(array_key_exists($eval_type, $typesRapports))
		$eval_name = $typesRapports[$eval_type];

	$avis_possibles = array();
	if(array_key_exists($eval_type, $typesRapportToAvis))
		$avis_possibles = $typesRapportToAvis[$eval_type];

	?>
<h1>
	<?php 
	echo ($is_unite ? "Unite : " : "Chercheur : ").$eval_name;
	?>
</h1>

<form method="post" action="index.php" style="width: 100%">
	<input type="hidden" name="action" value=<?php echo $actioname?>> <input
		type="hidden" name="id_origine" value="<?php echo $row->id_origine;?>">
	<input type="hidden" name="fieldtype" value="<?php echo $row->type;?>">

	<tr>
		<td colspan="2"><input type="submit"
			value="<?php echo (($actioname == "add") ? "Ajouter" : "Modifier")." ".$eval_type;?>">
		</td>
	</tr>

	<table class="inputreport">
		<tr>
			<td>Session</td>
			<td><select name="fieldid_session" style="width: 100%;">
					<?php 		
					$sessions = showSessions();
					foreach($sessions as $s)
					{
						$sel = "";
						if ($row->id_session==$s["id"])
						{
							$sel = "selected=\"selected\"";
						}

						echo  "\t\t\t\t\t<option value=\"".$s["id"]."\" $sel>".$s["nom"]." ".date("Y",strtotime($s["date"]))."</option>";
					}
					?>
			</select></td>
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

			$type = "";
			if (isset($fieldsTypes[$fieldID]))
			{
				$type = $fieldsTypes[$fieldID];
			}
			?>
		<tr>
			<td style="width: 17em;"><span><?php echo $title;?> </span>
			</td>
			<?php
			if ($type=="long")
			{
				?>
			<td colspan="2"><span class="examplevaleur"><?php echo getExample($fieldID);?>
			</span>
			</td>
		</tr>
		<tr>
			<td colspan="3"><textarea name="field<?php echo $fieldID;?>"
					style="width: 100%;">
					<?php echo $row->$fieldID;?>
				</textarea>
			</td>
			<?php
			}
			elseif ($type=="treslong")
			{
				?>
			<td colspan="2"><span class="examplevaleur"><?php echo getExample($fieldID);?>
			</span>
			</td>
		</tr>
		<tr>
			<td colspan="3"><textarea rows=10 name="field<?php echo $fieldID;?>"
					style="width: 100%;">
					<?php echo $row->$fieldID;?>
				</textarea>
			</td>
			<?php
			}
			elseif ($type=="short")
			{
				?>
			<td style="width: 30em;"><input name="field<?php echo $fieldID;?>"
				value="<?php echo $row->$fieldID;?>" style="width: 100%;">
			</td>
			<td><span class="examplevaleur"><?php echo getExample($fieldID);?> </span>
			</td>
			<?php
			}
			elseif ($type=="evaluation")
			{
				?>
			<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
				style="width: 100%;">
					<?php
					foreach($notes as $val)
					{
						$sel = "";
						if ($row->$fieldID==$val)
						{
							$sel = "selected=\"selected\"";
						}
						echo  "\t\t\t\t\t<option value=\"$val\" $sel>$val</option>";
					}
					?>
			</select>
			</td>
			<?php
			}
			elseif ($type=="rapporteur")
			{
				?>
			<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
				style="width: 100%;">
					<?php
					$users = listUsers();
					foreach($users as $val)
					{
						if($val!="admin")
						{
							$sel = "";
							if ($row->$fieldID==$val)
							{
								$sel = "selected=\"selected\"";
							}
							echo  "\t\t\t\t\t<option value=\"$val\" $sel>$val</option>";
						}
					}
					?>
			</select>
			</td>
			<?php
			}
			elseif ($type=="avis")
			{
				?>
			<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
				style="width: 100%;">
					<?php
					foreach($avis_possibles as $avis => $prettyprint)
					{
						$sel = "";
						if ($row->$fieldID==$avis)
						{
							$sel = "selected=\"selected\"";
						}
						echo  "\t\t\t\t\t<option value=\"$avis\" $sel>$prettyprint</option>";
					}
					?>
			</select>
			</td>
			<?php
			}
			elseif ($type=="unit")
			{
				?>
			<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
				style="width: 100%;">
					<?php
					$units = unitsList();
					foreach($units as $unite)
					{
						$sel = "";
						if (($row->unite) == ($unite->code))
						{
							$sel = "selected=\"selected\"";
						}
						echo  "\t\t\t\t\t<option value=\"".($unite->code)."\"".$sel.">".($unite->code)." - ".($unite->nickname)."</option>";
					}
					?>
			</select>
			</td>
			<?php
			}
			elseif ($type =="grade")
			{
				?>


			<td><select name="fieldgrade" style="width: 100%;">
					<?php
					foreach($grades as $idg => $txtg)
					{
						$sel = "";
						if ($row->grade==$idg)
						{
							$sel = "selected=\"selected\"";
						}
						echo  "\t\t\t\t\t<option value=\"$idg\" $sel>$txtg</option>";
					}
					?>
			</select></td>
			<?php
			}
			elseif ($type =="concours")
			{
				?>
			<td><select name="fieldconcours" style="width: 100%;">
					<?php
					foreach($concours_ouverts as $concours)
					{
						$sel = "";
						if ($row->concours==$concours)
						{
							$sel = "selected=\"selected\"";
						}
						echo  "\t\t\t\t\t<option value=\"$concours\" $sel>$concours</option>";
					}
					?>
			</select></td>
			<?php
			}elseif($type =="ecole")
			{
				?>
			<td colspan="3"><input name="fieldecole"
				value="<?php echo $row->ecole ?>" style="width: 100%;">
			</td>
			<?php
			}
			?>
		</tr>
		<?php
		}
		?>
		<tr>
			<td colspan="2"><input type="submit"
				value="<?php echo (($actioname == "add") ? "Ajouter" : "Modifier")." ".$eval_type;?>">
			</td>
		</tr>
	</table>

</form>
<?php 
}

function update($id_origine)
{
	global $fieldsAll;
	$specialRule = array(
			"auteur"=>0,
			"date"=>0,
			"nom_session"=>0,
			"date_session"=>0,
		);

$fields = "id_session, id_origine, auteur";
foreach($fieldsAll as  $fieldID => $title)
{
	if (!isset($specialRule[$fieldID]))
	{
		$fields.=",";
		$fields.=$fieldID;
	}
}
$values = mysql_real_escape_string($_REQUEST["fieldid_session"]);
$values .= ",".mysql_real_escape_string($id_origine);
$values .= ",\"".mysql_real_escape_string($_SESSION["login"])."\"";

foreach($fieldsAll as  $fieldID => $title)
{
	if (!isset($specialRule[$fieldID]) )
	{
		$values.=",";
		if(isset($_REQUEST["field".$fieldID]))
		{
			$values.="\"".mysql_real_escape_string($_REQUEST["field".$fieldID])."\"";
		}
		else
		{
			$values.="\"".mysql_real_escape_string("")."\"";
		}
	}
}
$sql = "INSERT INTO evaluations ($fields) VALUES ($values);";
mysql_query($sql);
return mysql_insert_id();
}

function updateRapportAvis($id_origine,$avis,$rapport)
{
	$result = filterSortReports(-1, "", "", "", $id_origine);
	global $fieldsAll;
	$tab = mysql_fetch_array($result);
	$specialRule = array(
			"auteur"=>0,
			"date"=>0,
			"avis"=>0,
			"rapport"=>0,
		);
	$fields = "auteur,id_session,id_origine,avis,rapport";
	$values = "\"".mysql_real_escape_string($_SESSION["login"])."\",".$tab["id_session"].",".$tab["id_origine"].",\"".mysql_real_escape_string($avis)."\",\"".mysql_real_escape_string($rapport)."\"";
	foreach($fieldsAll as  $fieldID => $title)
	{
		if (!isset($specialRule[$fieldID]))
		{
			$fields.=",";
			$fields.=$fieldID;
			$values.=",";
			$values.="\"".mysql_real_escape_string($tab[$fieldID])."\"";
		}
	}
	$sql = "INSERT INTO evaluations ($fields) VALUES ($values);";
	//echo $sql."<br>";
	mysql_query($sql);
}

function addReport()
{
	$newid = update(0);
	$sql = "UPDATE evaluations SET id_origine=$newid WHERE id=$newid;";
	mysql_query($sql);
	return $newid;
};


function unitsList()
{
	$units = array();
	$sql = "SELECT * FROM units ORDER BY nickname ASC;";
	if($result=mysql_query($sql))
	{
		while ($row = mysql_fetch_object($result))
		{
			$units[$row->code] = $row;
		}
	}
	return $units;
}









function HTMLToPDF($html)
{
	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(secretaire);
	$pdf->SetAuthor(section_fullname);
	$pdf->SetTitle('Rapport de la '.section_fullname);
	$pdf->SetSubject('Rapport de la '.section_fullname);
	$pdf->SetKeywords('Rapport de la '.section_fullname);

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	//set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, "15", PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, "15");

	//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	$l = Array(
			'a_meta_charset' => 'UTF-8',
			'a_meta_dir' => 'ltr',
			'a_meta_language' => 'fr',
			'w_page' => 'page'
	);
	//set some language-dependent strings
	$pdf->setLanguageArray($l);

	// ---------------------------------------------------------

	// set default font subsetting mode
	$pdf->setFontSubsetting(true);

	// Set font
	// dejavusans is a UTF-8 Unicode font, if you only need to
	// print standard ASCII chars, you can use core fonts like
	// helvetica or times to reduce file size.
	$pdf->SetFont('dejavusans', '', 11, '', true);

	// Add a page
	// This method has several options, check the source code documentation for more information.
	$pdf->AddPage();


	$pdf->writeHTML($html);

	$pdf->Close();
	return $pdf;
}
?>