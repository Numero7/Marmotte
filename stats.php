<?php 
require_once('manage_sessions.inc.php');
require_once('manage_rapports.inc.php');
require_once('utils.inc.php');

function fieldsToSQL($columnFilters, $rowFilter)
{
	$result = $rowFilter;
	foreach($columnFilters as $title => $fieldname)
	{
		$result .= ", "; 
		$result .= $fieldname;
	}
	return $result;
}

function computeArrays($filters, $columnFilters, $rowFilter)
{
	$fieldsSQL = fieldsToSQL($columnFilters, $rowFilter);

  	$sql = "SELECT $fieldsSQL,COUNT(*) AS \"total\" FROM ".evaluations_db." WHERE id = id_origine AND statut!=\"supprime\"";
	$sql .= filtersCriteriaToSQL(getCurrentFiltersList(),$filters);
	$sql .= "GROUP BY ".$fieldsSQL;
	$sql .= ";";

	$result = sql_request($sql);

	if($result == false)
		throw new Exception("Echec de l'execution de la requete <br/>".$sql."<br/>");

	$result = array();
	while($truc = mysql_fetch_object($result))
	{
		$current = $result;
		foreach($columnFilters as $title => $fieldname)
		{
			if (!isset($current[$truc->$fieldname]))
			{
				$current[$truc->$fieldname] = array();
			}
			$current = $current[$truc->$fieldname];
		}
		if (!isset($current[$truc->$rowFilter]))
		{
			$current[$truc->$rowFilter] = $truc->total;
		}
	}
	return $result;
}

function displayArrays($filters, $columnFilters, $rowFilters)
{
	$tabs = array();
	foreach($rowFilters as $rowFilter){
		$tab = computeArrays($filters, $columnFilters, $rowFilter);
		$tabs[$rowFilter] = $tab;
	}
	//displayArraysAux($tabs,$currLevel,$limitLevel);
}

function displayArraysAux($tabs,$currLevel,$limitLevel)
{
	if ($currLevel == $limitLevel){
		foreach($tabs as $key )
		{
		}
	}
	else
	{
		foreach($tabs as $key => $tab)
		{
			echo $key."<br>";
			displayArraysAux($tab,$currLevel+1,$limitLevel);
		}		
	}
}

function countReports($filters)
{

	$sql = "SELECT COUNT(*) AS \"total\" FROM ".evaluations_db." WHERE id = id_origine AND statut!=\"supprime\"";

	$sql .= filtersCriteriaToSQL(getCurrentFiltersList(),$filters);
	$sql .= ";";

	//echo $sql."</br>\n";

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

?>

<h1>Avis Candidatures</h1>

<?php 

if(is_current_session_concours())
{
	?>
<div id="horizontalContainer" style="float: none">
	<?php 
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
							<?php 

							/*
							 $topics = get_config("topics");
							foreach($topics as $id => $topic)
								echo "<th>$id</th>";
							*/
							?>
						</tr>
						<?php 
						$total = 0;
						foreach($avis_candidature_short as $avis => $nom)
						{
							if($avis != "tous")
							{
								echo "<tr>";
								$filters = array("concours" => $code, "id_session=" => current_session_id());
								$filters[$typeavis] = $avis;
								$count = countReports($filters);
								$total += $count;
								echo "<td>$nom</td><td>".$count."</td>";
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
	?>
</div>
<?php 
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
			echo '<th>Equivalence</th>';
			foreach($concours_ouverts as $code => $nom)
				echo '<th>'.$nom.'</th>';
		}
		?>
	</tr>
	<?php

	$users = listUsers();

	$filters = array("id_session=" => current_session_id());
	if(countReports($filters) > 0)
	{
		echo '<tr><td></td><td>'.statBaseAvancement($filters).'</td>';

		if(is_current_session_concours())
		{
			$filters['type'] = "Equivalence";
			echo '<td>'.statBaseAvancement($filters).'</td>';
			$filters['type'] = "Candidature";
			foreach($concours_ouverts as $code => $nom)
			{
				$filters['concours'] = $code;
				echo '<td>'.statBaseAvancement($filters).'</td>';
			}
		}
		echo '</tr>';
	}

	foreach($users as $login => $data)
	{
		$filters = array("login_rapp" => $login, "id_session=" => current_session_id());
		if(countReports($filters) > 0)
		{
			echo '<tr><td>'.$data->description.'</td><td>'.statBaseAvancement($filters).'</td>';

			if(is_current_session_concours())
			{
				$filters['type'] = "Equivalence";
				echo '<td>'.statBaseAvancement($filters).'</td>';
				$filters['type'] = "Candidature";
				foreach($concours_ouverts as $code => $nom)
				{
					$filters['concours'] = $code;
					echo '<td>'.statBaseAvancement($filters).'</td>';
				}
			}
			echo '</tr>';
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
		echo '<table class="stats"><tr><td>';
		$filters = array("concours" => $code, "avis1" => "", "type"=>"Candidature");
		$missing1 = filterSortReports(getCurrentFiltersList(),$filters);
		$filters = array("concours" => $code, "avis2" => "", "type"=>"Candidature");
		$missing2 = filterSortReports(getCurrentFiltersList(),$filters);

		foreach($missing1 as $report)
			echo "<tr><td>".$report->concours . " ". $report->nom . " " . $report->prenom."</td><td>(rapp1 ". $report->rapporteur. ")</tr>";
		foreach($missing2 as $report)
			echo "<tr><td>".$report->concours . " ". $report->nom . " " . $report->prenom."</td><td>(rapp2 ". $report->rapporteur2. ")</tr>";
			echo '</td></tr></table></div>';
		}
?>
</div>
