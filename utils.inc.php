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

function addCredentials($login,$pwd)
{
	$_SESSION['login'] = $login;
	$_SESSION['pass'] = $pwd;
} ;

function removeCredentials()
{
	unset($_SESSION['login']);
	unset($_SESSION['pass']);
} ;

function authenticateBase($login,$pwd)
{
	$realPassHash = getPassHash($login);
	if ($realPassHash != NULL)
	{
		if (crypt($pwd, $realPassHash) == $realPassHash)
		{
			return true;
		}
	}
	return false;
}

function authenticate()
{
	if (isset($_SESSION['login']) and isset($_SESSION['pass']))
	{
		$login  = $_SESSION['login'];
		$pwd = $_SESSION['pass'];
		return authenticateBase($login,$pwd);
	}
	return false;
} ;

function getPassHash($login)
{
	$sql = "SELECT * FROM users WHERE login='$login';";
	$result=mysql_query($sql);
	if ($row = mysql_fetch_object($result))
	{
		return $row->passHash;
	}
	return NULL;
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
			echo "<a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\">$actionTitle <img class=\"icon\" src=\"img/$action-icon.png\" alt=\"$action\"></a>";
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
	global $typesEvalUnit;
	global $typesEvalIndividual;
	
	$sql = "SELECT tt.*, ss.nom AS nom_session, ss.date AS date_session FROM evaluations tt, sessions ss WHERE tt.id=$id_rapport AND tt.id_session=ss.id;";
	$result=mysql_query($sql);
	if ($row = mysql_fetch_object($result))
	{
		if(array_key_exists($row->type,$typesEvalUnit))
		{
			displayUnitReport($row);
		}
		else if(array_key_exists($row->type,$typesEvalIndividual))
		{
			displayIndividualReport($row);
		}
		else
		{			
			echo "Unknown report type ".$row->type;
		}
	}
	else
	{
		echo "No report with if ".$is_rapport;
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
			echo "<a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\">$actionTitle <img class=\"icon\" src=\"img/$action-icon.png\" alt=\"$action\"></a>";
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
	global $typesEval;
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
					foreach ($typesEval as $ty => $value)
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
		echo " <a href=\"export.php?action=group&amp;id_session=$id_session&amp;type_eval=$type_eval&amp;sort_crit=$sort_crit&amp;login_rapp=$login_rapp&amp;type=$idexp\"><img class=\"icon\" width=\"50\" height=\"50\" src=\"img/$idexp-icon.png\" alt=\"$expname\"></a>";
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
			echo "<td><a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\"><img class=\"icon\" width=\"24\" height=\"24\" src=\"img/$action-icon.png\" alt=\"$actionTitle\"></a></td>";
		}
		echo "<td><a href=\"export.php?action=single&amp;id=".$row->id."&amp;id_origine=".$row->id_origine."\"><img class=\"icon\" width=\"24\" height=\"24\" src=\"img/pdf-icon.png\" alt=\"$actionTitle\"></a></td>";
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
			echo "<a href=\"?action=$action&amp;id=".$row->id."&amp;id_origine=".$id_origine."\">$actionTitle <img class=\"icon\" src=\"img/$action-icon.png\" alt=\"$action\"></a>";
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
	global $evaluations;
	global $actions;
	global $empty_report;

	$row = (object) $empty_report;
	$row->type = $type_rapport;
	displayEditableReport($row, "add");
} ;

function displayEditableReport($row, $actioname)
{
	global $fieldsAll;
	global $fieldsUnites;
	global $fieldsTypes;
	global $actions;
	global $typesEvalUnit;
	global $typesEval;
	global $grades;
	global $evaluations;
	global $avis_eval;
	global $typesEvalToAvis;

	$specialRule = array(
			"auteur"=>0,
			"date"=>0,
			"nom_session"=>0,
			"date_session"=>0,
			"type"=>0,
			"grade" => 0,
		);

			$eval_type = $row->type;
			$is_unite = in_array($eval_type,$typesEvalUnit);
			$eval_name = "";
			$eval_name = $typesEval[$eval_type];

			$avis_possibles = $typesEvalToAvis[$eval_type];

			?>
<h1>
	<?php 
	echo ($is_unite ? "Unite : " : "Chercheur : ").$eval_name;
	?>
</h1>

<form method="post" action="index.php" style="width: 100%">
	<tr>
		<td colspan="2">
		<input type="submit"
			value="<?php echo (($actioname == "add") ? "Ajouter" : "Modifier")." ".$eval_type;?>">
			<input type="hidden" name="action" value=<?php echo $actioname?>>
			<input type="hidden" name="id_origine" value="<?php echo $row->id_origine;?>">
			</td>
	</tr>

	<table class="inputreport">
		<tr>
			<td>Type d'évaluation</td>
			<td><select name="fieldtype" style="width: 100%;">
					<?php
					echo "\t\t\t\t\t<option value=\"$eval_type\">$eval_name</option>";
					?>
			</select></td>
		</tr>
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
		if(!$is_unite)
		{
			?>
		<tr>
			<td>Grade</td>
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
		</tr>
		<?php
		}
		foreach($fieldsAll as  $fieldID => $title)
		{
			if (!isset($specialRule[$fieldID]))
			{
				$type = "";
				if (isset($fieldsTypes[$fieldID]))
				{
					$type = $fieldsTypes[$fieldID];
				}
				if($is_unite && !in_array($fieldID,$fieldsUnites))
					continue;
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
			else if ($type=="short")
			{
				?>
			<td style="width: 30em;"><input name="field<?php echo $fieldID;?>"
				value="<?php echo $row->$fieldID;?>" style="width: 100%;">
			</td>
			<td><span class="examplevaleur"><?php echo getExample($fieldID);?> </span>
			</td>
			<?php
			}
			else if ($type=="evaluation")
			{
				?>
			<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
				style="width: 100%;">
					<?php
					foreach($evaluations as $val)
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
			else if ($type=="rapporteur")
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
			else if ($type=="avis")
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
			else if ($type=="unit")
			{
				?>
			<td style="width: 30em;"><select name="field<?php echo $fieldID;?>"
				style="width: 100%;">
					<?php
					$units = unitsList();
					foreach($units as $code => $nickname)
					{
						$sel = "";
						if ($row->$fieldID==$nickname)
						{
							$sel = "selected=\"selected\"";
						}
						echo  "\t\t\t\t\t<option value=\"$code\" $sel>$nickname</option>";
					}
					?>
			</select>
			</td>
			<?php
			}

			else
			{
				?>
			<td colspan="2"><span class="examplevaleur"><?php echo getExample($fieldID);?>
			</span>
			</td>
		</tr>
		<tr>
			<td><textarea name="field<?php echo $fieldID;?>">
					<?php echo $row->$fieldID;?>
				</textarea>
			</td>
			<?php
			}
			?>
		</tr>
		<?php
			}
		}
		?>
		<tr>
			<td colspan="2"><input type="submit"
				value="Ajouter <?php echo $eval_type;?>"> <input type="hidden"
				name="action" value=<?php echo $actioname?>></td>
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
return mysql_insert_id ();
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
	echo $sql."<br>";
	mysql_query($sql);		
}


function addReport()
{
	$newid = update(0);
	$sql = "UPDATE evaluations SET id_origine=$newid WHERE id=$newid;";
	mysql_query($sql);
	return $newid;
};

function isSuperUser($login = "")
{
	if ($login=="")
	{
		if (isset($_SESSION["login"]))
		{
			$login = $_SESSION["login"];
		}
		else return false;
	}
	return $login == "admin";
};

function listUsers()
{
	$users = array();
	$sql = "SELECT login FROM users ORDER BY login ASC;";
	if($result=mysql_query($sql))
	{
		while ($row = mysql_fetch_object($result))
		{
			$users[] = $row->login;
		}
	}
	return $users;
}

function unitsList()
{
	$units = array();
	$sql = "SELECT * FROM units ORDER BY nickname ASC;";
	if($result=mysql_query($sql))
	{
		while ($row = mysql_fetch_object($result))
		{
			$units[$row->code] = $row->nickname;
		}
	}
	return $units;
}

function sessionArrays()
{
	$sessions = array();
	$sql = "SELECT * FROM sessions ORDER BY id ASC;";
	if($result=mysql_query($sql))
	{
		while ($row = mysql_fetch_object($result))
		{
			$sessions[$row->id] = $row->nom." ".date("Y", strtotime($row->date));
		}
	}
	return $sessions;
}


function changePwd($login,$old,$new1,$new2)
{
	if (isset($_SESSION["login"]))
	{
		$currLogin = $_SESSION["login"];
		if ($currLogin==$login or isSuperUser())
		{
			if (authenticateBase($login,$old) or isSuperUser())
			{
				$oldPassHash = getPassHash($login);
				if ($oldPassHash != NULL)
				{
					$newPassHash = crypt($new1, $oldPassHash);
					$sql = "UPDATE users SET passHash='$newPassHash' WHERE login='$login';";
					mysql_query($sql);
					return true;
				}
			}
			else
			{
				echo "<p><strong>Erreur :</strong> La saisie du mot de passe courant est incorrecte, veuillez réessayer.</p>";
				return false;
			};
		}
		else
		{
			echo "<p><strong>Erreur :</strong> Seuls les administrateurs du site peuvent modifier les mots de passes d'autres utilisateurs, veuillez nous contacter (Yann ou Hugo) en cas de difficultés.</p>";
			return false;
		}
	}
	else
	{
		echo "<p><strong>Erreur :</strong> Login manquant, veuillez vous reconnecter.</p>";
		return false;
	}
}
function createUser($login,$pwd,$desc)
{
	if (isSuperUser())
	{
		$passHash = crypt($pwd);
		$sql = "INSERT INTO users(login,passHash,description) VALUES ('".mysql_real_escape_string($login)."','".mysql_real_escape_string($passHash)."','".mysql_real_escape_string($desc)."');";
		mysql_query($sql);
		return true;
	}
}

function deleteUser($login)
{
	if (isSuperUser())
	{
		$sql = "DELETE FROM users WHERE login='".mysql_real_escape_string($login)."';";
		mysql_query($sql);
	}
}

function createSession($name,$date)
{
	if (isSuperUser())
	{
		echo $date."<br>";
		echo strtotime($date)."<br>";
		echo date("Y-m-d h:m:s",strtotime($date));
		$sql = "INSERT INTO sessions(nom,date) VALUES ('".mysql_real_escape_string($name)."','".date("Y-m-d h:m:s",strtotime($date))."');";
		mysql_query($sql);
		return true;
	}
}

function deleteSession($id)
{
	if (isSuperUser())
	{
		$sql = "DELETE FROM sessions WHERE id=$id;";
		mysql_query($sql);
	}
}


function getReportsAsXMLArray($id_session=-1, $type_eval="", $sort_crit="", $login_rapp="")
{
	global $fieldsAll;
	$result = filterSortReports($id_session, $type_eval, $sort_crit, $login_rapp);

	//to map id_session s to session nicknames
	$sessions = sessionArrays();
	$units = unitsList();

	$docs = array();
	while ($row = mysql_fetch_object($result))
	{
		$doc = new DOMDocument();
		$root = $doc->createElement("rapports");
		$elem = appendRowToXMLDoc($row, $sessions,$units,$doc);
		$doc->appendChild($elem);
		$docs[] = $doc;
	}

	return $docs;
}

function getReportsAsXML($id_session=-1, $type_eval="", $sort_crit="", $login_rapp="",$id_edit="")
{
	global $fieldsAll;
	$doc = new DOMDocument();
	$root = $doc->createElement("rapports");
	$root->setAttribute("id_session",$id_session);
	$root->setAttribute("type_eval",$type_eval);
	$root->setAttribute("sort_crit",$sort_crit);
	$root->setAttribute("login_rapp",$login_rapp);
	$root->setAttribute("id_edit",$id_edit);
	$result = filterSortReports($id_session, $type_eval, $sort_crit, $login_rapp);

	//to map id_session s to session nicknames
	$sessions = sessionArrays();
	$units = unitsList();

	while ($row = mysql_fetch_object($result))
	{
		$elem = appendRowToXMLDoc($row, $sessions,$units,$doc);
		$root->appendChild($elem); 
	}
	$doc->appendChild($root);

	return $doc;
}

//Returns the name of the zip file
function filename_from_node(DOMNode $node)
{
	$nom = "";
	$prenom = "";
	$grade = "";
	$unite = "";
	$type = "";

	foreach($node->childNodes as $child)
	{
		if($child->nodeName == "nom")
			$nom = $child->nodeValue;
		else if($child->nodeName == "prenom")
			$prenom = $child->nodeValue;
		else if($child->nodeName == "grade")
			$grade = $child->nodeValue;
		else if($child->nodeName == "unite")
			$unite = $child->nodeValue;
		else if($child->nodeName == "type")
			$type = $child->nodeValue;
	}

	if($unite != "")
		return $type."_".$unite;
	else
		return $type."_".$grade." ".$nom." ".$prenom.".tex";
}

function type_from_node(DOMNode $node)
{
	foreach($node->childNodes as $child)
		if($child->nodeName == "type")
		return $child->nodeValue;
	return "";
}


function xml_to_zipped_tex($docs)
{
	$xsl = new DOMDocument();
	$xsl->load("xslt/latex_eval.xsl");
	$proc_eval = new XSLTProcessor();
	$proc_eval->importStyleSheet($xsl);
	$proc = $proc_eval;

	$processors = array(
			'Evaluation-Vague' => $proc_eval,
			'Evaluation-MiVague' => $proc_eval,
			'Promotion' => $proc,
			'Candidature' => $proc,
			'Suivi-PostEvaluation' => $proc,
			'Titularisation' => $proc,
			'Confirmation-Affectation' => $proc,
			'Changement-Direction' => $proc,
			'Renouvellement' => $proc,
			'Expertise' => $proc,
			'Ecole' => $proc,
			'Comité-Evaluation' => $proc,
			'' => $proc
	);

	$zip = new ZipArchive();
	if($zip->open('reports_latex.zip',ZipArchive::OVERWRITE))
	{

		$zip->addFromString("compile.bat", "for /r %%x in (*.tex) do pdflatex \"%%x\"\r\ndel *.log\r\ndel *.aux");
		$zip->addFile("latex/CN.png","CN.png");
		$zip->addFile("latex/CNRSlogo.png","CNRSlogo.png");
		$zip->addFile("latex/signature.jpg","signature.jpg");

		foreach($docs as $doc)
		{
			$node =$doc->getElementsByTagName("rapport");
			if($node)
				$filename = filename_from_node($node->item(0)).".tex";		
			$type = type_from_node($doc);
			$zip->addFromString($filename,$processors[$type]->transformToXML($doc));
		}

		$zip->close();
		return "reports_latex.zip";

	}
	return "";
}

function xml_to_zipped_pdf($docs)
{
	$xsl = new DOMDocument();
	$xsl->load("xslt/html.xsl");
	$proc_eval = new XSLTProcessor();
	$proc_eval->importStyleSheet($xsl);

	$proc = $proc_eval;

	$processors = array(
			'Evaluation-Vague' => $proc_eval,
			'Evaluation-MiVague' => $proc_eval,
			'Promotion' => $proc,
			'Candidature' => $proc,
			'Suivi-PostEvaluation' => $proc,
			'Titularisation' => $proc,
			'Confirmation-Affectation' => $proc,
			'Changement-Direction' => $proc,
			'Renouvellement' => $proc,
			'Expertise' => $proc,
			'Ecole' => $proc,
			'Comité-Evaluation' => $proc,
			'' => $proc
	);

	$zip = new ZipArchive();
	if($zip->open('reports_pdf.zip',ZipArchive::OVERWRITE))
	{
		$i = 0;
		foreach($docs as $doc)
		{
			set_time_limit(0);
			$node =$doc->getElementsByTagName("rapport");
			if($node)
				$filename = filename_from_node($node->item(0)).".pdf";
			$type = type_from_node($doc);
			$html = $processors[$type]->transformToXML($doc);
			$pdf = HTMLToPDF($html);
			$zip->addFromString($filename,$pdf->Output("","S"));
			$i++;
		}

		$zip->close();
		return "reports_pdf.zip";

	}
	return "";
}

function appendRowToXMLDoc($row, $sessions, $units, DOMDocument $doc)
{
	global $fieldsAll;
	global $typesEval;
	global $typesEvalUpperCase;

	if(!$sessions)
		$sessions = sessionArrays();

	if(!$units)
		$units = unitsList();


	$rapportElem = $doc->createElement("rapport");

	$fieldsspecial = array('unite','date','type');
	
	$rapportElem->setAttribute("id",$row->id_origine);

	foreach ($fieldsAll as $fieldID => $title)
	{
		if(!in_array($fieldID,$fieldsspecial))
		{
			$contentElem = $doc->createElement($fieldID);
			$data = $doc->createCDATASection ($row->$fieldID);
			$contentElem->appendChild($data);
			$rapportElem->appendChild($contentElem);
		}
	}

	//On ajoute le nickname du labo
	$contentElem = $doc->createElement("unite");
	if(array_key_exists($row->unite,$units))
	{
		$data = $doc->createCDATASection($row->unite." (".$units[$row->unite].")");
		$contentElem->appendChild($data);
	}
	else
	{
		$data = $doc->createCDATASection($row->unite);
		$contentElem->appendChild($data);
	}

	$rapportElem->appendChild($contentElem);

	//On ajoute la date du jour
	$contentElem = $doc->createElement("date");
	setlocale(LC_TIME, "fr_FR");
	$data = $doc->createCDATASection(strftime("%e %B %Y",time()));
	$contentElem->appendChild($data);
	$rapportElem->appendChild($contentElem);

	//On ajoute la date du jour
	$contentElem = $doc->createElement("type");
	$data = $doc->createCDATASection($typesEval[$row->type]);
	$contentElem->appendChild($data);
	$rapportElem->appendChild($contentElem);

	//On ajoute la date du jour
	$contentElem = $doc->createElement("UPPERCASETYPE");
	$data = $doc->createCDATASection($typesEvalUpperCase[$row->type]);
	$contentElem->appendChild($data);
	$rapportElem->appendChild($contentElem);

	//On ajoute le nickname de la session
	$contentElem = $doc->createElement("session");
	$data = $doc->createCDATASection ($sessions[$row->id_session]);
	$contentElem->appendChild($data);
	$rapportElem->appendChild($contentElem);

	//On ajoute le numero de la section
	$contentElem = $doc->createElement("section_nb");
	$data = $doc->createCDATASection (section_nb);
	$contentElem->appendChild($data);
	$rapportElem->appendChild($contentElem);

	//On ajoute l'intitulé de la section
	$contentElem = $doc->createElement("section_intitule");
	$data = $doc->createCDATASection (section_intitule);
	$contentElem->appendChild($data);
	$rapportElem->appendChild($contentElem);

	//On ajoute le nom du signataire
	$contentElem = $doc->createElement("signataire");
	$data = $doc->createCDATASection (president);
	$contentElem->appendChild($data);
	$rapportElem->appendChild($contentElem);

	$contentElem = $doc->createElement("signataire_titre");
	$data = $doc->createCDATASection (president_titre);
	$contentElem->appendChild($data);
	$rapportElem->appendChild($contentElem);

	return $rapportElem;

}

function rowToXMLDoc($row, $sessions = null, $units = null)
{
	$doc = new DOMDocument();
	$root = $doc->createElement("rapports");
	$elem = appendRowToXMLDoc($row, $sessions, $units, $doc);
	$root->appendChild($elem);
	$doc->appendChild($root);
	return $doc;
}

function XMLToHTML(DOMDocument $doc)
{
	$xsl = new DOMDocument();
	$xsl->load('xslt/html.xsl');
	$proc = new XSLTProcessor();
	$proc->importStyleSheet($xsl);
	$html = $proc->transformToXML($doc);
	return $html;
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
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

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