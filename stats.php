<?php 
require_once('manage_sessions.inc.php');
require_once('manage_rapports.inc.php');
require_once('utils.inc.php');
require_once('config.php');


function fieldsToSQL($columnFilters, $rowFilter)
{
	$result = $rowFilter;
	foreach($columnFilters as $fieldname => $title)
	{
		$result .= ", "; 
		$result .= $fieldname;
	}
	return $result;
}

function computeArrays($filters, $columnFilters, $rowFilter)
{
	global $fieldsRapportAll;
	global $fieldsIndividualAll;
	
	$fieldsSQL = fieldsToSQL($columnFilters, $rowFilter);
	/*
	if(isset($fieldsRapportAll[$filter]))
		$pref = reports_db.".";
	else if(isset($fieldsIndividualAll[$filter]))
		$pref = people_db.".";
	else
		throw new Exception("Filter criterion ".$filter." is neither in the list of rapport fields nor in the list of individual fields");
	*/
	
//	$sql = "SELECT *, ".people_db.".nom AS people_nom, ".people_db.".prenom AS people_prenom, ".reports_db.".nom AS nom, ".reports_db.".prenom AS prenom FROM ".reports_db." left join ".people_db." on ".reports_db.".nom=".people_db.".nom AND ".reports_db.".prenom=".people_db.".prenom WHERE ".reports_db.".id=".reports_db.".id_origine AND ".reports_db.".statut!=\"supprime\"";
//	$sql .= " AND ". (isset($data['sql_col']) ?  $data['sql_col'] : ($pref.$filter))."=\"$filter_values[$filter]\" ";
	
  	$sql = "SELECT $fieldsSQL,COUNT(*) AS \"total\" FROM ".reports_db." left join ".people_db." on ".reports_db.".nom=".people_db.".nom AND ".reports_db.".prenom=".people_db.".prenom WHERE id = id_origine AND statut!=\"supprime\"";
	$sql .= filtersCriteriaToSQL(getCurrentFiltersList(),$filters);
	$sql .= "GROUP BY ".$fieldsSQL;
	$sql .= ";";
	
	$result = sql_request($sql);

	if($result == false)
		throw new Exception("Echec de l'execution de la requete <br/>".$sql."<br/>");

	$finresult = array();
	while($truc = mysql_fetch_object($result))
	{
		$finresult[] = $truc;
	}
	return $finresult;
}

function displayArrays($filters, $columnFilters, $rowFilters,$format = "",$rowtitles = array())
{
	$tabs = array();
	$processedTab = array();
	foreach($rowFilters as $rowFilter => $titleRow){
		$tabs[$rowFilter] = computeArrays($filters, $columnFilters, $rowFilter);
	}
	foreach($tabs as $fieldcatname => $elem)
	{
		foreach($elem as $i => $truc)
		{
			$current =& $processedTab;
			foreach($columnFilters as $fieldname => $title)
			{
				$val = $truc->$fieldname;
				if (!isset($current[$val]))
				{
					$current[$val] = array();
				}
				$current =& $current[$val];
			}
			if (!isset($current[$truc->$fieldcatname]))
			{
				$current[$truc->$fieldcatname] = array();				
			}
			$current[$truc->$fieldcatname][$fieldcatname] = $truc->total;
		}
	}	
	displayArraysAux($processedTab,0,$columnFilters,$rowFilters,$rowtitles);	
}

function displayCounts($vals,$rowFilters)
{
	$first = 1;
	foreach($rowFilters as $st => $title)
	{
		if (isset($vals[$st]))
		{
			$val = $vals[$st];
			//echo "[".$st."]";
			if ($first)
			{echo $val;}
			else
			{echo " (".$val.")";}
		}
		$first = 0;
	}
}
function displayArraysAux($tab,$currLevel, $columnFilters,$rowFilters,$rowtitles = array())
{
	if ($currLevel >= count($columnFilters)-1){
		echo '<table class="stats">';
		$colcounts = array();
		$rowscounts = array();
		$rows = array();
		foreach($tab as $keyrow => $tabrow )
		{
			$rows[] = $keyrow;
			if  (!isset($rowcounts[$keyrow]))
			{ $rowcounts[$keyrow] = array(); }

			foreach($tabrow as $keycol => $vals )
			{
				if  (!isset($colcounts[$keycol]))
				{ $colcounts[$keycol] = array(); }
				foreach($vals as $st => $val)
				{
					if (!isset($colcounts[$keycol][$st]))
					{ $colcounts[$keycol][$st] = 0; }
					if (!isset($rowcounts[$keyrow][$st]))
					{ $rowcounts[$keyrow][$st] = 0; }
					$colcounts[$keycol][$st] += $val;
					$rowcounts[$keyrow][$st] += $val;
				}
			}
		}
		$cols = array_keys($colcounts);
		sort($rows);
		sort($cols);
		$titles = array_values($columnFilters);
		$rowtitle = isset($titles[$currLevel-1]) ? $titles[$currLevel-1] : "";

		echo "<tr><td></td><td></td>";
		foreach($cols as $col)
		{
			if ($col=="")
			{ $col = "Aucun"; }
			echo "<th>".$col."</th>";			
		}
		echo "</tr>";

		echo "<tr><th>".$rowtitle."</th><th>Total</th>";
		foreach($cols as $col)
		{
			echo "<th>";
			$vals = $colcounts[$col];
			displayCounts($vals,$rowFilters);
			echo "</th>";
		}
		echo "</tr>";

		foreach($rows as $keyrow)
		{
			$tabrow = $tab[$keyrow];
			echo "<tr><td>";
			if(isset($rowtitles[$keyrow]))
				echo $rowtitles[$keyrow];
			else
					echo $keyrow;
				
			echo "</td><th>";
			$vals = $rowcounts[$keyrow];
			displayCounts($vals,$rowFilters);
			echo "</th>";
			foreach($cols as $col)
			{
				$vals  = array();
				if (isset($tabrow[$col]))
				{ $vals = $tabrow[$col]; }
				echo "<td>";
				displayCounts($vals,$rowFilters);
				echo "</td>";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
	else
	{
		foreach($tab as $key => $val)
		{
			$tag = "h".($currLevel+2)."";
			$titles = array_values($columnFilters);
			$title = $titles[$currLevel];
			echo "<$tag>".$title." ".$key."</$tag>";
			displayArraysAux($val,$currLevel+2, $columnFilters,$rowFilters,$rowtitles);
		}
	}
}



function countReports($filters, $or = true)
{

	$filters["id_session"] = current_session_id();
	$sql = "SELECT COUNT(*) AS \"total\" FROM ".reports_db." WHERE id = id_origine AND statut!=\"supprime\"";

	$sql .= filtersCriteriaToSQL(getCurrentFiltersList(),$filters,$or);
	$sql .= ";";

	//echo $sql."<br/>\n";

	$result = sql_request($sql);

	if($result == false)
		throw new Exception("Echec de l'execution de la requete <br/>".$sql."<br/>");

	$truc = mysql_fetch_object($result);

	if($truc == false || ! isset($truc->total))
		throw new Exception("Empty answer from sql count request");

	return $truc->total;
}

function statBaseAvancement($filters)
{
	$total  = countReports($filters);
	$filters["avancement"] = "done";
	$dones = 	countReports($filters);
	if($total  > 0)
	{
		$coef = intval(100.0 * $dones /$total);
		return $coef ."% (".$dones."/".$total.")";
	}
	else
		return "";
}

function sous_jury_row($used_topics, $jurys, $code, $login, $data)
{
	$filters = array('type' => "Candidature", 'concours' => $code, 'avis' => "oral", 'rapporteur' => $login, 'rapporteur2' => "tous");
	$total1 = countReports($filters,false);

	$filters['rapporteur2'] = $login;
	$filters['rapporteur'] = "tous";
	$total2 = countReports($filters,false);

	if($total1 >0 || $total2 > 0)
	{
		echo '<tr><td>'.$data->description.'</td>';
		echo "<td>$total1 ($total2)</td>";

		foreach($jurys as $jury => $total)
			echo (isSousJury($jury,$login)? "<td>X</td>\n" : "<td></td>\n");

		foreach($used_topics as $topic => $total	)
		{
			$filters['theme1'] = $topic;
			$filters['rapporteur'] = $login;
			$filters['rapporteur2'] = "tous";
			$total1 = countReports($filters, false);
			$filters['rapporteur2'] = $login;
			$filters['rapporteur'] = "tous";
			$total2 = countReports($filters, false);
			$filters['avis'] = "oral";

			if($total1 > 0 || $total2 > 0)
				echo "<td>$total1 ($total2)</td>";
			else
				echo "<td></td>";
		}
	}


	echo '</tr>';
}


?>

<h1>Avis Candidatures</h1>

<?php 


if(is_current_session_concours())
{
	$with_topics = false;
	foreach($concours_ouverts as $code => $concours)
	{
		?>
<div style="float: top">
	<h2>
		<?php  echo $concours; ?>
	</h2>
	<table>
		<tr>
			<?php 

			foreach(array("avis" => "Avis section","avis1" => "Avis rapp1","avis2" => "Avis rapp2") as $typeavis => $nomavis)
			{
				?>
			<td>
				<table class="stats">
					<tr>
						<th><?php  echo $nomavis;?></th>
						<th>Total</th>
						<th>%</th>
						<?php 

						/*
						 $topics = get_config("topics");
						foreach($topics as $id => $topic)
							echo "<th>$id</th>";
						*/
						?>
					</tr>
					<?php
					$filters = array("concours" => $code, "type" => "Candidature");
					$total = countReports($filters);
					foreach($avis_candidature_short as $avis => $nom)
					{
						if($avis != "tous")
						{
							echo "<tr>";
							$filters = array("concours" => $code,  "type" => "Candidature");
							$filters[$typeavis] = $avis;
							$count = countReports($filters);
							echo "<td>$nom</td><td> ".$count." </td>";
							if($total > 0 && $count > 0)
								echo "<td> ".(int) (100.0 * $count / $total)."% </td>";
							else
								echo "<td></td>";

							/*
							 foreach($topics as $id => $topic)
							 {
							$filters["theme1"] = $id;
							echo "<td>".countReports($filters)."</td>";
							}
							*/
							echo "</tr>";
						}
					}
					echo "<tr><td></td><td>".$total ."</td></tr>";

					?>

				</table>
			</td>
			<?php 
			}
			?>
		</tr>
	</table>
</div>
<?php 
	}
}
?>

<h1>Stats rapporteurs</h1>
<p>
Statistiques sur le nombre de rapports de candidats auditionnés par rapporteur et par thème,
en premier rapporteur et entre parenthèses en second rapporteur.
</p>
<?php 

if(is_current_session_concours())
{
	$filters = array(
		'type' => "Candidature",
		'avis' => "oral",
	);

	$columnFilters = array(
		"concours" => "Concours",	
		'rapporteur' => 'Rapporteur',
	);
	$rowFilters = array(
		'theme1' => 'Theme principal',
		'theme2' => 'Theme secondaire',
	);
	displayArrays($filters, $columnFilters, $rowFilters);
	
}

?>

<h1>Stats theses</h1>
<p>
Statistiques sur le nombre de rapports de candidats par localisation de these
</p>

<p>Candidats</p>
	
	<?php 
	global $theseslocs;
	$filters = array(
			'type' => "Candidature",
	);
	
	$columnFilters = array(
			"concours" => "Concours",
	);
	$rowFilters = array(
			'theseloc' => 'Localisation de la thèse'
	);
	
	displayArrays($filters, $columnFilters, $rowFilters,$theseslocs);
?>

<p>Candidats auditionnés</p>
<?php

if(is_current_session_concours())
{
	global $theseslocs;
	$filters = array(
			'type' => "Candidature",
			'avis' => "oral",
	);

	$columnFilters = array(
			"concours" => "Concours",
	);
	$rowFilters = array(
			'theseloc' => 'Localisation de la thèse'
	);

	displayArrays($filters, $columnFilters, $rowFilters,$theseslocs);

	
}


if(is_current_session_concours())
{
?>

<h1>Stats candidats</h1>
<p>
Statistiques sur la répartition thématique par candidats.
</p>
<?php 

	$filters = array(
		'type' => "Candidature",
	);

	$columnFilters = array(
		"concours" => "Concours",	
		'theme1' => 'Theme principal',
	);
	$rowFilters = array(
		'avis' => 'Avis section'
	);
global $topics;
	displayArrays($filters, $columnFilters, $rowFilters,"",$topics);

	

?>

<p>
Statistiques sur la répartition de genre des candidats.
</p>
<?php 

	$filters = array(
		'type' => "Candidature",
	);

	$columnFilters = array(
		"concours" => "Concours",	
		'genre' => 'Genre',
	);
	$rowFilters = array(
		'avis' => 'Avis section'
	);
global $topics;
	displayArrays($filters, $columnFilters, $rowFilters,"",$topics);

	
}
?>

<h1>Avancement prérapports</h1>
<table class="stats">
	<tr>
		<th>Nom</th>
		<th>Total</th>
		<?php 
		if(is_current_session_concours())
		{
			echo "<th>Equivalence</th>\n";
			foreach($concours_ouverts as $code => $nom)
				echo '<th>'.$nom."</th>\n";
		}
		?>
	</tr>
	<?php

	$users = listUsers();

	$filters = array();
	if(countReports($filters) > 0)
	{
		echo "<tr>\n<td></td><td></td><td>".statBaseAvancement($filters)."</td>\n";

		if(is_current_session_concours())
		{
			$filters['type'] = "Equivalence";
			echo '<td>'.statBaseAvancement($filters)."</td>\n";
			$filters['type'] = "Candidature";
			foreach($concours_ouverts as $code => $nom)
			{
				$filters['concours'] = $code;
				echo '<td>'.statBaseAvancement($filters)."</td>\n";
			}
		}
		echo "</tr>\n";
	}

	foreach($users as $login => $data)
	{
		$filters = array("rapporteur" => $login);
		if(countReports($filters) > 0)
		{
			echo '<tr><td>'.$data->description.'</td><td>'.statBaseAvancement($filters)."</td>\n";

			if(is_current_session_concours())
			{
				$filters['type'] = "Equivalence";
				echo '<td>'.statBaseAvancement($filters)."</td>\n";
				$filters['type'] = "Candidature";
				foreach($concours_ouverts as $code => $nom)
				{
					$filters['concours'] = $code;
					echo '<td>'.statBaseAvancement($filters)."</td>\n";
				}
			}
			echo "</tr>\n";
		}
			
	}

	?>
</table>

<h2>Todo</h2>


<div id="horizontalContainer" style="float: none">
	<?php 

	foreach($concours_ouverts as $code => $nom)
	{
		echo '<div style="float: left">';
		echo '<table class="stats">';

		$filters = array("concours" => $code, "avis1" => "", "type"=>"Candidature");
		$missing1 = filterSortReports(getCurrentFiltersList(),$filters);
		$filters = array("concours" => $code, "avis2" => "", "type"=>"Candidature");
		$missing2 = filterSortReports(getCurrentFiltersList(),$filters);

		foreach($missing1 as $report)
			echo "<tr><td>".$report->concours . " ". $report->nom . " " . $report->prenom."</td><td>(rapp1 ". $report->rapporteur. ")</td></tr>\n";
		foreach($missing2 as $report)
			echo "<tr><td>".$report->concours . " ". $report->nom . " " . $report->prenom."</td><td>(rapp2 ". $report->rapporteur2. ")</td></tr>\n";
		echo '</table></div>';
	}
	?>
</div>
